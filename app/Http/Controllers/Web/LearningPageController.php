<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Web\traits\LearningPageForumTrait;
use App\Http\Controllers\Web\traits\LearningPageItemInfoTrait;
use App\Http\Controllers\Web\traits\LearningPageMixinsTrait;
use App\Http\Controllers\Web\traits\LearningPageNoticeboardsTrait;
use App\Models\Certificate;
use App\Models\CourseLearningLastView;
use App\Models\CourseNoticeboard;
use Illuminate\Http\Request;
use App\Models\GroupMember;
use App\Models\CourseGroup;
use Illuminate\Support\Carbon;

class LearningPageController extends Controller
{
    use LearningPageMixinsTrait, LearningPageAssignmentTrait, LearningPageItemInfoTrait,
        LearningPageNoticeboardsTrait, LearningPageForumTrait;

    public function index(Request $request, $slug)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            $this->authorize("panel_webinars_learning_page");
        }

        $requestData = $request->all();

        $webinarController = new WebinarController();

        $data = $webinarController->course($slug, true);

        $course = $data['course'];
        $user = $data['user'];

        /* Check Not Active */
        if ($course->status != "active" and (empty($user) or (!$user->isAdmin() and !$course->canAccess($user)))) {
            $data = [
                'pageTitle' => trans('update.access_denied'),
                'pageRobot' => getPageRobotNoIndex(),
            ];
            return view('web.default.course.not_access', $data);
        }

        $installmentLimitation = $webinarController->installmentContentLimitation($user, $course->id, 'webinar_id');
        if ($installmentLimitation != "ok") {
            return $installmentLimitation;
        }


        if (!$data or (!$data['hasBought'] and empty($course->getInstallmentOrder()))) {
            abort(403);
        }

        if (!empty($requestData['type']) and $requestData['type'] == 'assignment' and !empty($requestData['item'])) {

            $assignmentData = $this->getAssignmentData($course, $requestData);

            $data = array_merge($data, $assignmentData);
        }

        if ($course->creator_id != $user->id and $course->teacher_id != $user->id and !$user->isAdmin()) {
            $unReadCourseNoticeboards = CourseNoticeboard::where('webinar_id', $course->id)
                ->whereDoesntHave('noticeboardStatus', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->count();

            if ($unReadCourseNoticeboards) {
                $url = $course->getNoticeboardsPageUrl();
                return redirect($url);
            }
        }

        if ($course->certificate) {
            $data["courseCertificate"] = Certificate::where('type', 'course')
                ->where('student_id', $user->id)
                ->where('webinar_id', $course->id)
                ->first();
        }

        $data['userLearningLastView'] = CourseLearningLastView::query()
            ->where('user_id', $user->id)
            ->where('webinar_id', $course->id)
            ->first();
        $groups = $this->getMyGroupsForWebinar($course->id);
        $data['groups'] = $groups;
        return view('web.default.course.learningPage.index', $data);
    }
    public function getMyGroupsForWebinar($webinarId)
    {
        $userId = auth()->id();

        $groups = CourseGroup::with('instructorFiles') // تحميل الملفات المرتبطة
            ->where('webinar_id', $webinarId)
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('student_id', $userId);
            })
            ->get();
        return $groups;
    }
    public function groupNextTime($courseGroup, &$joinUrl, &$meetingID, $role = 'teacher')
    {
        $joinURL = $role === 'teacher' ? 'start_url' : 'join_url';
        $nextStartTime = false;

        if ($courseGroup->meeting_json) {
            $decodedJson = json_decode($courseGroup->meeting_json, true);
            if ($decodedJson && isset($decodedJson['occurrences'])) {
                $joinUrl     = $decodedJson[$joinURL];
                $occurrences = $decodedJson['occurrences'];
                $meetingID   = $decodedJson['id'];

                // Get user's timezone and set default if not valid
                $userTimezone = auth()->user()->timezone ?? '';
                if ($userTimezone !== 'Asia/Dubai' && $userTimezone !== 'Africa/Cairo') {
                    $userTimezone = 'Asia/Dubai';
                }

                // Get current datetime in the user's (or default) timezone
                $currentDateTime = Carbon::now($userTimezone);

                // Filter occurrences to find the next closest session
                $nextSession = collect($occurrences)->filter(
                    function ($occurrence) use ($currentDateTime, $userTimezone) {
                        return Carbon::parse($occurrence['start_time'])->setTimezone($userTimezone)->greaterThan($currentDateTime);
                    }
                )->sortBy('start_time')->first(); // Sort by start_time and get the first one
                if ($nextSession) {
                    $nextStartTime = Carbon::parse($nextSession['start_time'])->setTimezone($userTimezone)->toIso8601String();
                }
            }
        }

        return $nextStartTime;
    }
}
