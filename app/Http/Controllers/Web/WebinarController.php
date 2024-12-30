<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Http\Controllers\Web\traits\InstallmentsTrait;
use App\Mixins\Cashback\CashbackRules;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\AdvertisingBanner;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Favorite;
use App\Models\File;
use App\Models\QuizzesResult;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\TextLesson;
use App\Models\CourseLearning;
use App\Models\WebinarChapter;
use App\Models\WebinarReport;
use App\Models\Webinar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\GroupMember;
use App\Models\CourseGroup;
use Carbon\Carbon;

class WebinarController extends Controller
{
    use CheckContentLimitationTrait;
    use InstallmentsTrait;

    public function getNearestDatetime(array $datetimes): ?string
    {
        $target = now(); // Use the current datetime as the target

        // Use array_reduce to find the nearest datetime
        $nearest = array_reduce($datetimes, function ($carry, $datetime) use ($target) {
            $current = Carbon::parse($datetime);
            if (!$carry || $target->diffInSeconds($current) < $target->diffInSeconds(Carbon::parse($carry))) {
                return $datetime;
            }
            return $carry;
        });

        return $nearest;
    }

    public function groupNextTime($courseGroup, &$joinUrl, &$meetingID, $role = 'teacher')
    {
        $joinURL = $role === 'teacher' ? 'start_url' : 'join_url';
        $nextStartTime = false;
        if ($courseGroup->meeting_json) {
            $decodedJson = json_decode($courseGroup->meeting_json, true);

            if ($decodedJson && isset($decodedJson['occurrences'])) {
                $joinUrl         = $decodedJson[$joinURL];
                $occurrences     = $decodedJson['occurrences'];
                $meetingID       = $decodedJson['id'];
                $currentDateTime = Carbon::now();

                // Filter occurrences to find the next closest session
                $nextSession = collect($occurrences)->filter(
                    function ($occurrence) use ($currentDateTime) {
                        return Carbon::parse($occurrence['start_time'])->greaterThan($currentDateTime);
                    }
                )->sortBy('start_time')->first(); // Sort by start_time and get the first one

                if ($nextSession) {
                    $nextStartTime = Carbon::parse($nextSession['start_time'])->toDateTimeString();
                }
            }
        }
        return $nextStartTime;
    }
    public function course($slug, $justReturnData = false)
    {
        $user = null;

        if (auth()->check()) {
            $user = auth()->user();
        }

        if (! $justReturnData) {
            $contentLimitation = $this->checkContentLimitation($user, true);
            if ($contentLimitation != 'ok') {
                return $contentLimitation;
            }
        }

        $course = Webinar::where('slug', $slug)
            ->with(
                array(
                    'quizzes'                 => function ($query) {
                        $query->where('status', 'active')
                            ->with(array( 'quizResults', 'quizQuestions' ));
                    },
                    'tags',
                    'prerequisites'           => function ($query) {
                        $query->with(
                            array(
                                'prerequisiteWebinar' => function ($query) {
                                    $query->with(
                                        array(
                                            'teacher' => function ($qu) {
                                                $qu->select('id', 'full_name', 'avatar');
                                            },
                                        )
                                    );
                                },
                            )
                        );
                        $query->orderBy('order', 'asc');
                    },
                    'relatedCourses'          => function ($query) {
                        $query->whereHas(
                            'course',
                            function ($query) {
                                $query->where('status', 'active');
                            }
                        );
                    },
                    'faqs'                    => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'webinarExtraDescription' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'chapters'                => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive);
                        $query->orderBy('order', 'asc');

                        $query->with(
                            array(
                                'chapterItems' => function ($query) {
                                    $query->orderBy('order', 'asc');
                                },
                            )
                        );
                    },
                    'files'                   => function ($query) use ($user) {
                        $query->join('webinar_chapters', 'webinar_chapters.id', '=', 'files.chapter_id')
                        ->select('files.*', DB::raw('webinar_chapters.order as chapterOrder'))
                        ->where('files.status', WebinarChapter::$chapterActive)
                        ->orderBy('chapterOrder', 'asc')
                        ->orderBy('files.order', 'asc')
                        ->with(
                            array(
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', ! empty($user) ? $user->id : null);
                                },
                            )
                        );
                    },
                    'textLessons'             => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive)
                        ->withCount(array( 'attachments' ))
                        ->orderBy('order', 'asc')
                        ->with(
                            array(
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', ! empty($user) ? $user->id : null);
                                },
                            )
                        );
                    },
                    'sessions'                => function ($query) use ($user) {
                        $query->where('status', WebinarChapter::$chapterActive)
                        ->orderBy('order', 'asc')
                        ->with(
                            array(
                                'learningStatus' => function ($query) use ($user) {
                                    $query->where('user_id', ! empty($user) ? $user->id : null);
                                },
                            )
                        );
                    },
                    'assignments'             => function ($query) {
                        $query->where('status', WebinarChapter::$chapterActive);
                    },
                    'tickets'                 => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                    'filterOptions',
                    'category',
                    'teacher',
                    'reviews'                 => function ($query) {
                        $query->where('status', 'active');
                        $query->with(
                            array(
                                'comments' => function ($query) {
                                    $query->where('status', 'active');
                                },
                                'creator'  => function ($qu) {
                                    $qu->select('id', 'full_name', 'avatar');
                                },
                            )
                        );
                    },
                    'comments'                => function ($query) {
                        $query->where('status', 'active');
                        $query->whereNull('reply_id');
                        $query->with(
                            array(
                                'user'    => function ($query) {
                                    $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                },
                                'replies' => function ($query) {
                                    $query->where('status', 'active');
                                    $query->with(
                                        array(
                                            'user' => function ($query) {
                                                $query->select('id', 'full_name', 'role_name', 'role_id', 'avatar', 'avatar_settings');
                                            },
                                        )
                                    );
                                },
                            )
                        );
                        $query->orderBy('created_at', 'desc');
                    },
                    'groups',
                )
            )
            ->withCount(
                array(
                    'sales' => function ($query) {
                        $query->whereNull('refund_at');
                    },
                    'noticeboards',
                )
            )
            // ->where('status', 'active')
            ->first();

        if (empty($course)) {
            return $justReturnData ? false : back();
        }
        $userGroup     = null;
        $nextStartTime = false;
        $joinUrl       = false;
        $meetingID     = false;
        $groups        = false;
        $nearstSession = false;
        if ($user) {
            if ($user->isTeacher()) {
                // Retrieve the groups associated with the webinar
                $groups = $course->groups;
                $nextSessions = [];
                foreach ($groups as $group) {
                    $group->nextStartTime = $this->groupNextTime($group, $joinUrl, $meetingID);
                    if ($group->nextStartTime) {
                        $nextSessions[] = $group->nextStartTime;
                    }
                }
                if (! empty($nextSessions)) {
                    $nearstSession = $this->getNearestDatetime($nextSessions);
                }
            } else {
                // Check if the user belongs to any group of the current webinar
                $userGroup = GroupMember::where('student_id', $user->id)
                    ->whereIn('group_id', $course->groups->pluck('id')) // Filter by the webinar's groups
                    ->with('group') // Load the group relationship
                    ->first();
                if ($userGroup && $userGroup->group) {
                    $nextStartTime = $this->groupNextTime($userGroup->group, $joinUrl, $meetingID, 'student');
                }
            }
        }
        if (! $justReturnData) {
            /* Check Not Active */
            if ($course->status != 'active' and ( empty($user) or ( ! $user->isAdmin() and ! $course->canAccess($user) ) )) {
                $data = array(
                    'pageTitle' => trans('update.access_denied'),
                    'pageRobot' => getPageRobotNoIndex(),
                );
                return view('web.default.course.not_access', $data);
            }

            /* Installment Check */
            $installmentLimitation = $this->installmentContentLimitation($user, $course->id, 'webinar_id');

            if ($installmentLimitation != 'ok') {
                return $installmentLimitation;
            }
        }

        $hasBought = $course->checkUserHasBought($user, true, true);
        $isPrivate = $course->private;

        if (! empty($user) and ( $user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin() )) {
            $isPrivate = false;
        }

        if ($isPrivate and $hasBought) { // check the user has bought the course or not
            $isPrivate = false;
        }

        if ($isPrivate) {
            return $justReturnData ? false : back();
        }

        $isFavorite = false;

        if (! empty($user)) {
            $isFavorite = Favorite::where('webinar_id', $course->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $webinarContentCount = 0;
        if (! empty($course->sessions)) {
            $webinarContentCount += $course->sessions->count();
        }
        if (! empty($course->files)) {
            $webinarContentCount += $course->files->count();
        }
        if (! empty($course->textLessons)) {
            $webinarContentCount += $course->textLessons->count();
        }
        if (! empty($course->quizzes)) {
            $webinarContentCount += $course->quizzes->count();
        }
        if (! empty($course->assignments)) {
            $webinarContentCount += $course->assignments->count();
        }

        $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', array( 'course', 'course_sidebar' ))
            ->get();

        $sessionsWithoutChapter = $course->sessions->whereNull('chapter_id');

        $filesWithoutChapter = $course->files->whereNull('chapter_id');

        $textLessonsWithoutChapter = $course->textLessons->whereNull('chapter_id');

        $quizzes = $course->quizzes->whereNull('chapter_id');

        if ($user) {
            $quizzes = $this->checkQuizzesResults($user, $quizzes);

            if (! empty($course->chapters) and count($course->chapters)) {
                foreach ($course->chapters as $chapter) {
                    if (! empty($chapter->chapterItems) and count($chapter->chapterItems)) {
                        foreach ($chapter->chapterItems as $chapterItem) {
                            if (! empty($chapterItem->quiz)) {
                                $chapterItem->quiz = $this->checkQuizResults($user, $chapterItem->quiz);
                            }
                        }
                    }
                }
            }

            if (! empty($course->quizzes) and count($course->quizzes)) {
                $course->quizzes = $this->checkQuizzesResults($user, $course->quizzes);
            }
        }

        $pageRobot = getPageRobot('course_show'); // index
        $canSale   = ( $course->canSale() and ! $hasBought );

        /* Installments */
        $showInstallments         = true;
        $overdueInstallmentOrders = $this->checkUserHasOverdueInstallment($user);

        if ($overdueInstallmentOrders->isNotEmpty() and getInstallmentsSettings('disable_instalments_when_the_user_have_an_overdue_installment')) {
            $showInstallments = false;
        }

        if ($canSale and ! empty($course->price) and $course->price > 0 and $showInstallments and getInstallmentsSettings('status') and ( empty($user) or $user->enable_installments )) {
            $installmentPlans = new InstallmentPlans($user);
            $installments     = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
        }

        /* Cashback Rules */
        if ($canSale and ! empty($course->price) and getFeaturesSettings('cashback_active') and ( empty($user) or ! $user->disable_cashback )) {
            $cashbackRulesMixin = new CashbackRules($user);
            $cashbackRules      = $cashbackRulesMixin->getRules('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
        }

        $instructorDiscounts = null;

        if (! empty(getFeaturesSettings('frontend_coupons_status'))) {
            $instructorDiscounts = Discount::query()
                ->where(
                    function (Builder $query) use ($course) {
                        $query->where('creator_id', $course->creator_id);
                        $query->orWhere('creator_id', $course->teacher_id);
                    }
                )
                ->where(
                    function (Builder $query) use ($course) {
                        $query->where('source', 'all');
                        $query->orWhere(
                            function (Builder $query) use ($course) {
                                $query->where('source', Discount::$discountSourceCourse);

                                $query->where(
                                    function (Builder $query) use ($course) {
                                        $query->whereHas(
                                            'discountCourses',
                                            function ($query) use ($course) {
                                                $query->where('course_id', $course->id);
                                            }
                                        );

                                        $query->whereDoesntHave('discountCourses');
                                    }
                                );
                            }
                        );
                    }
                )
                ->where('status', 'active')
                ->where('expired_at', '>', time())
                ->get();
        }

        $data = array(
            'pageTitle'                 => $course->title,
            'pageDescription'           => $course->seo_description,
            'pageRobot'                 => $pageRobot,
            'pageMetaImage'             => $course->getImage(),
            'course'                    => $course,
            'isFavorite'                => $isFavorite,
            'hasBought'                 => $hasBought,
            'user'                      => $user,
            'webinarContentCount'       => $webinarContentCount,
            'advertisingBanners'        => $advertisingBanners->where('position', 'course'),
            'advertisingBannersSidebar' => $advertisingBanners->where('position', 'course_sidebar'),
            'activeSpecialOffer'        => $course->activeSpecialOffer(),
            'sessionsWithoutChapter'    => $sessionsWithoutChapter,
            'filesWithoutChapter'       => $filesWithoutChapter,
            'textLessonsWithoutChapter' => $textLessonsWithoutChapter,
            'quizzes'                   => $quizzes,
            'installments'              => $installments ?? null,
            'cashbackRules'             => $cashbackRules ?? null,
            'instructorDiscounts'       => $instructorDiscounts,
            'userGroup'                 => $userGroup,
            'nextStartTime'             => $nearstSession ? $nearstSession : $nextStartTime,
            'joinUrl'                   => $joinUrl,
            'meetingID'                 => $meetingID,
            'groups'                    => $groups,
        );

        // check for certificate
        if (! empty($user)) {
            $course->makeCertificateForUser($user);
        }

        if ($justReturnData) {
            return $data;
        }

        return view('web.default.course.index', $data);
    }

    private function checkQuizzesResults($user, $quizzes)
    {
        $canDownloadCertificate = false;

        foreach ($quizzes as $quiz) {
            $quiz = $this->checkQuizResults($user, $quiz);
        }

        return $quizzes;
    }

    private function checkQuizResults($user, $quiz)
    {
        $canDownloadCertificate = false;

        $canTryAgainQuiz = false;
        $userQuizDone    = QuizzesResult::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if (count($userQuizDone)) {
            $quiz->user_grade   = $userQuizDone->first()->user_grade;
            $quiz->result_count = $userQuizDone->count();
            $quiz->result       = $userQuizDone->first();

            $status_pass = false;
            foreach ($userQuizDone as $result) {
                if ($result->status == QuizzesResult::$passed) {
                    $status_pass = true;
                }
            }

            $quiz->result_status = $status_pass ? QuizzesResult::$passed : $userQuizDone->first()->status;

            if ($quiz->certificate and $quiz->result_status == QuizzesResult::$passed) {
                $canDownloadCertificate = true;
            }
        }

        if (! isset($quiz->attempt) or ( count($userQuizDone) < $quiz->attempt and $quiz->result_status !== QuizzesResult::$passed )) {
            $canTryAgainQuiz = true;
        }

        $quiz->can_try                  = $canTryAgainQuiz;
        $quiz->can_download_certificate = $canDownloadCertificate;

        return $quiz;
    }

    private function checkCanAccessToPrivateCourse($course, $user = null): bool
    {
        if (empty($user)) {
            $user = auth()->user();
        }

        if (empty($user)) {
            $user = apiAuth();
        }

        $canAccess = ! $course->private;
        $hasBought = $course->checkUserHasBought($user);

        if (! empty($user) and ( $user->id == $course->creator_id or $user->organ_id == $course->creator_id or $user->isAdmin() or $hasBought )) {
            $canAccess = true;
        }

        return $canAccess;
    }

    public function downloadFile($slug, $file_id)
    {
        $webinar = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
            $file = File::where('webinar_id', $webinar->id)
                ->where('id', $file_id)
                ->first();

            if (! empty($file) and $file->downloadable) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }

                if ($canAccess) {
                    if (in_array($file->storage, array( 's3', 'external_link' ))) {
                        return redirect($file->file);
                    }

                    $filePath = public_path($file->file);

                    if (file_exists($filePath)) {
                        $extension = \Illuminate\Support\Facades\File::extension($filePath);

                        $fileName  = str_replace(' ', '-', $file->title);
                        $fileName  = str_replace('.', '-', $fileName);
                        $fileName .= '.' . $extension;

                        $headers = array(
                            'Content-Type: application/' . $file->file_type,
                        );

                        return response()->download($filePath, $fileName, $headers);
                    }
                } else {
                    $toastData = array(
                        'title'  => trans('public.not_access_toast_lang'),
                        'msg'    => trans('public.not_access_toast_msg_lang'),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }
            }
        }

        return back();
    }

    public function showHtmlFile($slug, $file_id)
    {
        $webinar = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
            $file = File::where('webinar_id', $webinar->id)
                ->where('id', $file_id)
                ->first();

            if (! empty($file)) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }

                if ($canAccess) {
                    $filePath = $file->interactive_file_path;

                    if (\Illuminate\Support\Facades\File::exists(public_path($filePath))) {
                        $data = array(
                            'pageTitle' => $file->title,
                            'path'      => url($filePath),
                        );
                        return view('web.default.course.learningPage.interactive_file', $data);
                    }

                    abort(404);
                } else {
                    $toastData = array(
                        'title'  => trans('public.not_access_toast_lang'),
                        'msg'    => trans('public.not_access_toast_msg_lang'),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }
            }
        }

        abort(403);
    }

    public function getFilePath(Request $request)
    {
        $this->validate(
            $request,
            array(
                'file_id' => 'required',
            )
        );

        $file_id = $request->get('file_id');

        $file = File::where('id', $file_id)
            ->first();

        if (! empty($file)) {
            $webinar = Webinar::where('id', $file->webinar_id)
                ->where('status', 'active')
                ->with(
                    array(
                        'files' => function ($query) {
                            $query->select('id', 'webinar_id', 'file_type')
                                ->where('status', 'active')
                                ->orderBy('order', 'asc');
                        },
                    )
                )
                ->first();

            if (! empty($webinar)) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }

                if ($canAccess) {
                    $path = $file->file;

                    if ($file->storage == 'upload') {
                        $path = url("/course/$webinar->slug/file/$file->id/play");
                    } elseif ($file->storage == 'upload_archive') {
                        $path = url("/course/$webinar->slug/file/$file->id/showHtml");
                    }

                    return response()->json(
                        array(
                            'code'           => 200,
                            'storage'        => $file->storage,
                            'path'           => $path,
                            'storageService' => $file->storage,
                        ),
                        200
                    );
                }
            }
        }

        abort(403);
    }

    public function playFile($slug, $file_id)
    {
        // this methode linked from video modal for play local video
        // and linked from file.blade for show google_drive,dropbox,iframe

        $webinar = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (! empty($webinar) and $this->checkCanAccessToPrivateCourse($webinar)) {
            $file = File::where('webinar_id', $webinar->id)
                ->where('id', $file_id)
                ->first();

            if (! empty($file)) {
                $canAccess = true;

                if ($file->accessibility == 'paid') {
                    $canAccess = $webinar->checkUserHasBought();
                }

                if ($canAccess) {
                    $notVideoSource = array( 'iframe', 'google_drive', 'dropbox' );

                    if (in_array($file->storage, $notVideoSource)) {
                        $data = array(
                            'pageTitle' => $file->title,
                            'iframe'    => $file->file,
                        );

                        return view('web.default.course.learningPage.interactive_file', $data);
                    } elseif ($file->isVideo()) {
                        return response()->file(public_path($file->file));
                    }
                }
            }
        }

        abort(403);
    }

    public function getLesson(Request $request, $slug, $lesson_id)
    {
        $user = null;

        if (auth()->check()) {
            $user = auth()->user();
        }

        $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->with(
                array(
                    'teacher',
                    'textLessons' => function ($query) {
                        $query->orderBy('order', 'asc');
                    },
                )
            )
            ->first();

        if (! empty($course) and $this->checkCanAccessToPrivateCourse($course)) {
            $textLesson = TextLesson::where('id', $lesson_id)
                ->where('webinar_id', $course->id)
                ->where('status', WebinarChapter::$chapterActive)
                ->with(
                    array(
                        'attachments'    => function ($query) {
                            $query->with('file');
                        },
                        'learningStatus' => function ($query) use ($user) {
                            $query->where('user_id', ! empty($user) ? $user->id : null);
                        },
                    )
                )
                ->first();

            if (! empty($textLesson)) {
                $canAccess = $course->checkUserHasBought();

                if ($textLesson->accessibility == 'paid' and ! $canAccess) {
                    $toastData = array(
                        'title'  => trans('public.request_failed'),
                        'msg'    => trans('cart.you_not_purchased_this_course'),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }

                $checkSequenceContent    = $textLesson->checkSequenceContent();
                $sequenceContentHasError = ( ! empty($checkSequenceContent) and ( ! empty($checkSequenceContent['all_passed_items_error']) or ! empty($checkSequenceContent['access_after_day_error']) ) );

                if (! empty($checkSequenceContent) and $sequenceContentHasError) {
                    $toastData = array(
                        'title'  => trans('public.request_failed'),
                        'msg'    => ( $checkSequenceContent['all_passed_items_error'] ? $checkSequenceContent['all_passed_items_error'] . ' - ' : '' ) . ( $checkSequenceContent['access_after_day_error'] ?? '' ),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }

                $nextLesson     = null;
                $previousLesson = null;
                if (! empty($course->textLessons) and count($course->textLessons)) {
                    $nextLesson     = $course->textLessons->where('order', '>', $textLesson->order)->first();
                    $previousLesson = $course->textLessons->where('order', '<', $textLesson->order)->first();
                }

                if (! empty($nextLesson)) {
                    $nextLesson->not_purchased = ( $nextLesson->accessibility == 'paid' and ! $canAccess );
                }

                $data = array(
                    'pageTitle'      => $textLesson->title,
                    'textLesson'     => $textLesson,
                    'course'         => $course,
                    'nextLesson'     => $nextLesson,
                    'previousLesson' => $previousLesson,
                );

                return view(getTemplate() . '.course.text_lesson', $data);
            }
        }

        abort(404);
    }

    public function free(Request $request, $slug)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (! empty($course)) {
                $checkCourseForSale = checkCourseForSale($course, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                if (! empty($course->price) and $course->price > 0) {
                    $toastData = array(
                        'title'  => trans('cart.fail_purchase'),
                        'msg'    => trans('cart.course_not_free'),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }

                Sale::create(
                    array(
                        'buyer_id'       => $user->id,
                        'seller_id'      => $course->creator_id,
                        'webinar_id'     => $course->id,
                        'type'           => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount'         => 0,
                        'total_amount'   => 0,
                        'created_at'     => time(),
                    )
                );

                $notifyOptions = array(
                    '[u.name]'    => $user->full_name,
                    '[c.title]'   => $course->title,
                    '[amount]'    => trans('public.free'),
                    '[time.date]' => dateTimeFormat(time(), 'j M Y H:i'),
                );
                sendNotification('new_course_enrollment', $notifyOptions, 1);

                $toastData = array(
                    'title'  => '',
                    'msg'    => trans('cart.success_pay_msg_for_free_course'),
                    'status' => 'success',
                );
                return back()->with(array( 'toast' => $toastData ));
            }

            abort(404);
        } else {
            return redirect('/login');
        }
    }

    public function reportWebinar(Request $request, $id)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $data = $request->all();

            $validator = Validator::make(
                $data,
                array(
                    'reason'  => 'required|string',
                    'message' => 'required|string',
                )
            );

            if ($validator->fails()) {
                return response()->json(
                    array(
                        'code'   => 422,
                        'errors' => $validator->errors(),
                    ),
                    422
                );
            }

            $webinar = Webinar::select('id', 'status')
                ->where('id', $id)
                ->where('status', 'active')
                ->first();

            if (! empty($webinar)) {
                WebinarReport::create(
                    array(
                        'user_id'    => $user->id,
                        'webinar_id' => $webinar->id,
                        'reason'     => $data['reason'],
                        'message'    => $data['message'],
                        'created_at' => time(),
                    )
                );

                $notifyOptions = array(
                    '[u.name]'       => $user->full_name,
                    '[content_type]' => trans('product.course'),
                );
                sendNotification('new_report_item_for_admin', $notifyOptions, 1);

                return response()->json(
                    array(
                        'code' => 200,
                    ),
                    200
                );
            }
        }

        return response()->json(
            array(
                'code' => 401,
            ),
            200
        );
    }

    public function learningStatus(Request $request, $id)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $course = Webinar::where('id', $id)->first();

            if (! empty($course) and $course->checkUserHasBought($user)) {
                $data = $request->all();

                $item    = $data['item'];
                $item_id = $data['item_id'];
                $status  = $data['status'];

                CourseLearning::where('user_id', $user->id)
                    ->where($item, $item_id)
                    ->delete();

                if ($status and $status == 'true') {
                    CourseLearning::create(
                        array(
                            'user_id'    => $user->id,
                            $item        => $item_id,
                            'created_at' => time(),
                        )
                    );
                }

                // check for certificate
                $course->makeCertificateForUser($user);

                return response()->json(array(), 200);
            }
        }

        abort(403);
    }

    public function buyWithPoint($slug)
    {
        if (auth()->check()) {
            $user = auth()->user();

            $course = Webinar::where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (! empty($course)) {
                if (empty($course->points)) {
                    $toastData = array(
                        'title'  => '',
                        'msg'    => trans('update.can_not_buy_this_course_with_point'),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }

                $availablePoints = $user->getRewardPoints();

                if ($availablePoints < $course->points) {
                    $toastData = array(
                        'title'  => '',
                        'msg'    => trans('update.you_have_no_enough_points_for_this_course'),
                        'status' => 'error',
                    );
                    return back()->with(array( 'toast' => $toastData ));
                }

                $checkCourseForSale = checkCourseForSale($course, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                Sale::create(
                    array(
                        'buyer_id'       => $user->id,
                        'seller_id'      => $course->creator_id,
                        'webinar_id'     => $course->id,
                        'type'           => Sale::$webinar,
                        'payment_method' => Sale::$credit,
                        'amount'         => 0,
                        'total_amount'   => 0,
                        'created_at'     => time(),
                    )
                );

                RewardAccounting::makeRewardAccounting($user->id, $course->points, 'withdraw', null, false, RewardAccounting::DEDUCTION);

                $toastData = array(
                    'title'  => '',
                    'msg'    => trans('update.success_pay_course_with_point_msg'),
                    'status' => 'success',
                );
                return back()->with(array( 'toast' => $toastData ));
            }

            abort(404);
        } else {
            return redirect('/login');
        }
    }

    public function directPayment(Request $request)
    {
        $user = auth()->user();

        if (! empty($user) and ! empty(getFeaturesSettings('direct_classes_payment_button_status'))) {
            $this->validate(
                $request,
                array(
                    'item_id'   => 'required',
                    'item_name' => 'nullable',
                )
            );

            $data = $request->except('_token');

            $webinarId = $data['item_id'];
            $ticketId  = $data['ticket_id'] ?? null;

            $webinar = Webinar::where('id', $webinarId)
                ->where('private', false)
                ->where('status', 'active')
                ->first();

            if (! empty($webinar)) {
                $checkCourseForSale = checkCourseForSale($webinar, $user);

                if ($checkCourseForSale != 'ok') {
                    return $checkCourseForSale;
                }

                $fakeCarts = collect();

                $fakeCart                   = new Cart();
                $fakeCart->creator_id       = $user->id;
                $fakeCart->webinar_id       = $webinarId;
                $fakeCart->ticket_id        = $ticketId;
                $fakeCart->special_offer_id = null;
                $fakeCart->created_at       = time();

                $fakeCarts->add($fakeCart);

                $cartController = new CartController();

                return $cartController->checkout(new Request(), $fakeCarts);
            }
        }

        abort(404);
    }
}
