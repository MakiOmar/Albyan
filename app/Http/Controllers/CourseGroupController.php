<?php

namespace App\Http\Controllers;

use App\Models\CourseGroup;
use App\Models\GroupMember;
use App\User;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\LOG;
use Illuminate\Support\MessageBag;
use App\Models\Sale;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Mail\SendNotifications;
use App\Models\Notification;
use App\Models\NotificationStatus;
use App\Models\Api\UserFirebaseSessions;
use App\Models\Role;
use Kreait\Firebase\Messaging\CloudMessage;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CourseGroupController extends Controller
{
    /**
     * Display the groups for a specific webinar.
     */
    public function getGroups(Request $request)
    {
        $instructorId = $request->query('instructor_id');

        $groups = CourseGroup::with('webinar')->where('instructor_id', $instructorId)->get();

        $instructors = User::where('role_id', 4)->get();
        $students    = User::where('role_id', 1)->get();

        return [
            'groups'      => $groups,
            'instructors' => $instructors,
            'students'    => $students,
        ];
    }

    /**
     * Display the groups for a specific webinar.
     */
    public function getWebinarGroups(Request $request, $webinarId)
    {
        $instructorId = $request->query('instructor_id');

        // تحميل الـ Webinar دائمًا
        $webinar = Webinar::findOrFail($webinarId);

        // تحميل الـ Groups فقط لو فيه instructor_id
        if ($instructorId) {
            $webinar->load([
                'groups' => function ($query) use ($instructorId) {
                    $query->where('instructor_id', $instructorId)
                        ->with(['members.student', 'instructor']);
                }
            ]);
        } else {
            // نحط مجموعة فاضية يدويًا
            $webinar->setRelation('groups', collect());
        }

        $instructors = User::where('role_id', 4)->get();
        $students    = User::where('role_id', 1)->get();

        return [
            'webinar'     => $webinar,
            'instructors' => $instructors,
            'students'    => $students,
        ];
    }
    public function view($id)
    {
        $group = CourseGroup::with(['webinar', 'instructor', 'members.student'])->findOrFail($id);
        $decodedMeeting = $group->meeting_json ? json_decode($group->meeting_json, true) : [];
        $occurrences = $decodedMeeting['occurrences'] ?? [];

        return view('course_groups.admin.view', compact('group', 'occurrences'));
    }
    // GroupController.php
    public function getInstructorGroups($instructor_id)
    {
        $groups = CourseGroup::with('webinar')->where('instructor_id', $instructor_id)->get();

        return view('admin.users.editTabs.groups', compact('groups'))->render();
    }
    /**
     * Display the groups for a specific webinar.
     */
    public function listWebinarGroups(Request $request, $webinarId)
    {
        $getWebinarGroups   = $this->getWebinarGroups($request, $webinarId);
        $webinar     = $getWebinarGroups['webinar'];
        $instructors = $getWebinarGroups['instructors']; // Replace 'role' with your actual logic
        $students    = $getWebinarGroups['students'];

        return view('course_groups.admin.index', compact('webinar', 'instructors', 'students'));
    }
    public function addStudent(Request $request, $groupId)
    {
        $validated = $request->validate(
            array(
                'student_id' => 'required|exists:users,id',
            )
        );

        // Check if the student is already in the group
        $existingMember = GroupMember::where('group_id', $groupId)
                                    ->where('student_id', $validated['student_id'])
                                    ->first();

        if ($existingMember) {
            return response()->json(
                array(
                    'success' => false,
                    'message' => 'Student is already in this group.',
                )
            );
        }

        // Add the student to the group
        GroupMember::create(
            array(
                'group_id'   => $groupId,
                'student_id' => $validated['student_id'],
            )
        );

        $student = User::find($validated['student_id']);

        return response()->json(
            array(
                'success'       => true,
                'group_id'      => $groupId,
                'student_id'    => $student->id,
                'student_name'  => $student->full_name,
                'student_email' => $student->email,
            )
        );
    }
    public function getStudents($webinarId)
    {
        // Get student IDs who purchased the webinar
        $purchasedStudents = Sale::where('webinar_id', $webinarId)
        ->pluck('buyer_id')
        ->toArray(); // Get an array of buyer IDs

        // Get student IDs who are already in a group for this webinar
        $groupedStudents = GroupMember::where('webinar_id', $webinarId)
        ->pluck('student_id')
        ->toArray(); // Get an array of students already assigned to a group

        // Get students who purchased the webinar but are NOT in any group
        $availableStudents = User::whereIn('id', $purchasedStudents)
        ->whereNotIn('id', $groupedStudents)
        ->select('id', 'full_name')
        ->get();

        return response()->json($availableStudents);
    }



    public function removeStudent($groupId, $studentId)
    {
        $groupMember = GroupMember::where('group_id', $groupId)
                                    ->where('student_id', $studentId)
                                    ->first();

        if ($groupMember) {
            $groupMember->delete();
            return redirect()->back()->with('success', 'Student removed from the group successfully.');
        }

        return redirect()->back()->withErrors('Failed to remove the student from the group.');
    }
    protected function validateGroupRequest(Request $request)
    {
        $baseRules = [
            'schedule_type'       => 'required|in:regular,variable',
            'webinar_id'          => 'required|exists:webinars,id',
            'meeting_start_date'  => 'required|date',
            'meeting_start_time'  => 'required',
            'meeting_end_date'    => 'required|date',
            'meeting_end_time'    => 'required',
            'meeting_duration'    => 'required|numeric|min:1',
            'session_type'        => 'required|in:zoom,offline',
            'teacher_id'          => 'required|exists:users,id',
            'student_ids'         => 'nullable|array',
            'student_ids.*'       => 'exists:users,id',
            'manual_occurrences'             => 'nullable|array',
            'manual_occurrences.*.type'     => 'required|in:date,day',
            'manual_occurrences.*.date'     => 'nullable|date',
            'manual_occurrences.*.day'      => 'nullable|string|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'manual_occurrences.*.time'     => 'required|date_format:H:i',
            'manual_occurrences.*.duration' => 'nullable|numeric|min:1',
            'participant_video'   => 'nullable|in:0,1',
            'host_video'          => 'nullable|in:0,1',
            'audio_option'        => 'nullable|in:both,voip,telephony',
        ];

        $validator = Validator::make($request->all(), $baseRules);

        $schedule = $request->input('schedule_type');
        $recurrenceType = $request->input('recurrence_type');

        if ($schedule === 'regular') {
            $validator->addRules([
                'meeting_recurring'   => 'required|in:0,1',
                'recurrence_type'     => 'required|in:1,2,3',
                'recurrence_interval' => 'required|integer|min:1',
                'end_times'           => 'required|integer|min:1',
            ]);

            if ($recurrenceType == 2) {
                $validator->addRules(['weekly_days' => 'required|array']);
            }

            if ($recurrenceType == 3) {
                $validator->addRules(['monthly_day' => 'required|integer|min:1|max:31']);
            }
        }

        $validator->validate();
        return $validator->validated();
    }

    // Add these protected methods to your controller
    protected function handleMeetingLogic(Request $request, User $instructor, ?string $meetingId = null): array
    {
        $validated = $this->validateGroupRequest($request);

        if ($validated['schedule_type'] === 'variable' && $validated['session_type'] === 'zoom') {
            return $this->buildVariableZoomMeetingJson($validated, $request->input('manual_occurrences'), $instructor);
        }

        if ($validated['session_type'] === 'zoom') {
            $zoomResponse = $meetingId
                        ? $this->updateZoomMeeting($meetingId, $instructor, $validated)
                        : $this->createZoomMeeting($instructor, $validated);

            if (!$zoomResponse['success']) {
                throw ValidationException::withMessages(['zoom_meeting' => $zoomResponse['error']]);
            }

            $meetingJson = $zoomResponse['data'];
            unset($meetingJson['settings']['global_dial_in_numbers']);
            return $meetingJson;
        }

        return $this->buildOfflineMeetingJson($validated, $request->input('manual_occurrences'));
    }

    protected function parseDates(array $validated): array
    {
        $startDateTime = Carbon::parse($validated['meeting_start_date'] . ' ' . $validated['meeting_start_time'], 'Asia/Dubai');

        $endDateTime = null;
        if (!empty($validated['meeting_end_date']) && !empty($validated['meeting_end_time'])) {
            $endDateTime = Carbon::parse($validated['meeting_end_date'] . ' ' . $validated['meeting_end_time'], 'Asia/Dubai');
        }

        return [$startDateTime, $endDateTime];
    }

    protected function syncGroupMembers(CourseGroup $group, array $studentIds, $webinarId): void
    {
        $group->members()->delete();

        if (!empty($studentIds)) {
            $members = array_map(function ($studentId) use ($group, $webinarId) {
                return [
                'group_id' => $group->id,
                'student_id' => $studentId,
                'webinar_id' => $webinarId,
                'created_at' => now(),
                'updated_at' => now()
                ];
            }, $studentIds);

            GroupMember::insert($members);
        }
    }

    protected function prepareGroupData(array $validated, $meetingJson, $startDateTime, $endDateTime): array
    {
        return [
        'webinar_id' => $validated['webinar_id'],
        'instructor_id' => $validated['teacher_id'],
        'meeting_start_time' => $startDateTime,
        'meeting_end_time' => $endDateTime,
        'meeting_duration' => $validated['meeting_duration'] * 60,
        'meeting_recurring' => $validated['meeting_recurring'] ?? 0,
        'meeting_json' => json_encode($meetingJson),
        'session_type' => $validated['session_type'],
        ];
    }

// Updated controller methods
    public function createGroup(Request $request)
    {
        [$validated, $instructor] = $this->validateAndResolveInstructor($request);

        try {
            [$startDateTime, $endDateTime] = $this->parseDates($validated);
            $meetingJson = $this->handleMeetingLogic($request, $instructor);

            $group = CourseGroup::create(array_merge(
                $this->prepareGroupData($validated, $meetingJson, $startDateTime, $endDateTime),
                ['meeting_id' => $meetingJson['id'] ?? time()]
            ));

            $this->syncGroupMembers($group, $validated['student_ids'] ?? [], $validated['webinar_id']);

            return redirect()->route('webinar-groups.all')->with('success', 'Group created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        [$validated, $instructor] = $this->validateAndResolveInstructor($request);
        $group = CourseGroup::findOrFail($id);

        try {
            [$startDateTime, $endDateTime] = $this->parseDates($validated);
            $meetingJson = $this->handleMeetingLogic($request, $instructor, $group->meeting_id);

            $group->update($this->prepareGroupData($validated, $meetingJson, $startDateTime, $endDateTime));
            $this->syncGroupMembers($group, $validated['student_ids'] ?? [], $validated['webinar_id']);

            return redirect()->back()->with('success', 'Group updated successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    protected function validateAndResolveInstructor(Request $request): array
    {
        $validated = $this->validateGroupRequest($request);
        $instructor = User::findOrFail($validated['teacher_id']);
        return [$validated, $instructor];
    }


    private function buildVariableZoomMeetingJson(array $validated, ?array $manualOccurrences, User $instructor): array
    {
        $accessToken = $this->getZoomAccessToken();
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');
        $zoomUrl     = "{$zoomBaseUrl}/users/{$instructor->email}/meetings";
        $occurrences = [];

        if (!is_array($manualOccurrences) || empty($manualOccurrences)) {
            throw new \Exception('No manual occurrences provided for variable schedule.');
        }

        $type = $manualOccurrences[0]['type'] ?? 'date';

        // Helpers
        $duration = fn($item) => isset($item['duration']) ? round((float) $item['duration'] * 60) : ($validated['meeting_duration'] * 60);
        $time     = fn($item) => $item['time'] ?? '10:00';
        // --- Handle date-based entries
        if ($type === 'date') {
            foreach ($manualOccurrences as $occurrence) {
                if (empty($occurrence['date']) || empty($occurrence['time'])) {
                    continue;
                }

                $startTime = Carbon::parse($occurrence['date'] . ' ' . $time($occurrence), 'Asia/Dubai');
                $durationMinutes = $duration($occurrence);

                $occurrences[] = $this->createSingleZoomMeeting(
                    $zoomUrl,
                    $startTime,
                    $durationMinutes,
                    $validated,
                    $instructor,
                    $validated['webinar_id']
                );
            }
        }

        // --- Handle day-based entries
        if ($type === 'day') {
            $startDate = Carbon::parse($validated['meeting_start_date'], 'Asia/Dubai');
            $endDate   = Carbon::parse($validated['meeting_end_date'], 'Asia/Dubai');
            $daysOfWeek = collect($manualOccurrences)->pluck('day')->unique()->all();

            $cursor = $startDate->copy();

            while ($cursor->lte($endDate)) {
                if (in_array($cursor->englishDayOfWeek, $daysOfWeek)) {
                    foreach ($manualOccurrences as $item) {
                        if ($item['day'] === $cursor->englishDayOfWeek && !empty($item['time'])) {
                            $startTime = $cursor->copy()->setTimeFromTimeString($time($item));
                            $durationMinutes = $duration($item);
                            $occurrences[] = $this->createSingleZoomMeeting(
                                $zoomUrl,
                                $startTime,
                                $durationMinutes,
                                $validated,
                                $instructor,
                                $validated['webinar_id']
                            );
                        }
                    }
                }
                $cursor->addDay();
            }
        }

        return [
        'type'        => 'variable',
        'timezone'    => 'Asia/Dubai',
        'occurrences' => $occurrences,
        ];
    }


    private function createSingleZoomMeeting(
        string $zoomUrl,
        Carbon $startTime,
        int $durationMinutes,
        array $validated,
        User $instructor,
        int $webinarId
    ): array {
        $meetingData = [
            'topic'      => "Session for Webinar ID {$webinarId}",
            'type'       => 2, // Scheduled meeting
            'start_time' => $startTime->format('Y-m-d\TH:i:s'),
            'duration'   => $durationMinutes,
            'timezone'   => 'Asia/Dubai',
            'settings'   => [
                'host_video'        => (bool) ($validated['host_video'] ?? false),
                'participant_video' => (bool) ($validated['participant_video'] ?? false),
                'audio'             => $validated['audio_option'] ?? 'both',
                'join_before_host'  => false,
                'mute_upon_entry'   => true,
                'approval_type'     => 0,
            ],
        ];

        $response = Http::withToken($this->getZoomAccessToken())->post($zoomUrl, $meetingData);

        if ($response->failed()) {
            throw new \Exception('Zoom API error while creating meeting: ' . $response->body());
        }

        $meeting = $response->json();

        return [
            'occurrence_id' => $meeting['id'],
            'start_time'    => $meeting['start_time'], // ISO 8601 format from Zoom
            'duration'      => $meeting['duration'] * 60, // Convert from minutes to seconds
            'join_url'      => $meeting['join_url'],
            'start_url'     => $meeting['start_url'],
            'status'        => 'available',
        ];
    }



    private function buildOfflineMeetingJson(array $validated, ?array $manualOccurrences = null): array
    {
        $type = $manualOccurrences[0]['type'] ?? 'date'; // date أو day

        $duration = fn($item) => isset($item['duration']) ? ((int) $item['duration'] * 60) : ($validated['meeting_duration'] * 60);
        $time     = fn($item) => $item['time'] ?? '10:00';

        $occurrences = [];

        if ($type === 'date') {
            $occurrences = collect($manualOccurrences)
            ->filter(fn($item) => !empty($item['date']) && !empty($item['time']))
            ->map(function ($item) use ($duration, $time) {
                return [
                    'occurrence_id' => time() . rand(1, 999),
                    'start_time'    => Carbon::parse($item['date'] . ' ' . $time($item), 'Asia/Dubai')->toIso8601String(),
                    'duration'      => $duration($item),
                    'status'        => 'available',
                ];
            })->values()->all();
        }

        if ($type === 'day') {
            $startDate = Carbon::parse($validated['meeting_start_date'], 'Asia/Dubai');
            $endDate   = Carbon::parse($validated['meeting_end_date'], 'Asia/Dubai');

            $daysOfWeek = collect($manualOccurrences)->pluck('day')->unique()->all();

            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                if (in_array($cursor->englishDayOfWeek, $daysOfWeek)) {
                    foreach ($manualOccurrences as $item) {
                        if ($item['day'] === $cursor->englishDayOfWeek && !empty($item['time'])) {
                            $dt = $cursor->copy()->setTimeFromTimeString($time($item));
                            $occurrences[] = [
                            'occurrence_id' => time() . rand(1, 999),
                            'start_time'    => $dt->toIso8601String(),
                            'duration'      => $duration($item),
                            'status'        => 'available',
                            ];
                        }
                    }
                }
                $cursor->addDay();
            }
        }
        return [
        'recurrence' => [
            'type'            => $validated['schedule_type'],
            'repeat_interval' => (int) ($validated['recurrence_interval'] ?? 1),
            'end_times'       => (int) ($validated['end_times'] ?? 1),
            'weekly_days'     => isset($validated['weekly_days']) ? implode(',', $validated['weekly_days']) : null,
            'monthly_day'     => $validated['monthly_day'] ?? null,
        ],
        'occurrences' => $occurrences,
        ];
    }



    private function generateOfflineOccurrences(array $validated)
    {
        $occurrences = [];

        $startDateTime = Carbon::parse($validated['meeting_start_date'] . ' ' . $validated['meeting_start_time'], 'Asia/Dubai');
        $endDateTime = ($validated['meeting_recurring'] == 1 && !empty($validated['meeting_end_date']) && !empty($validated['meeting_end_time']))
            ? Carbon::parse($validated['meeting_end_date'] . ' ' . $validated['meeting_end_time'], 'Asia/Dubai')
            : $startDateTime;

        $duration = (int) $validated['meeting_duration'] * 60;
        $current = $startDateTime->copy();
        $count = 0;

        while ($current <= $endDateTime && $count < intval($validated['end_times'])) {
            if ($validated['recurrence_type'] == 1) {
                // يومي
                $occurrences[] = [
                    'start_time' => $current->toIso8601String(),
                    'duration'   => $duration,
                    'status'     => 'available',
                ];
                $current->addDays($validated['recurrence_interval']);
            } elseif ($validated['recurrence_type'] == 2) {
                // أسبوعي
                $weekDays = $validated['weekly_days'] ?? [];

                if (in_array($current->dayOfWeekIso, $weekDays)) {
                    $occurrences[] = [
                        'start_time' => $current->toIso8601String(),
                        'duration'   => $duration,
                        'status'     => 'available',
                    ];
                    $count++;
                }
                $current->addDay();
                continue;
            } elseif ($validated['recurrence_type'] == 3) {
                // شهري
                if ($current->day == $validated['monthly_day']) {
                    $occurrences[] = [
                        'start_time' => $current->toIso8601String(),
                        'duration'   => $duration,
                        'status'     => 'available',
                    ];
                    $current->addMonths($validated['recurrence_interval']);
                } else {
                    $current->addDay();
                    continue;
                }
            }

            $count++;
        }

        return $occurrences;
    }

    private function handleFirebaseMessages($data, $user_id, $group_id, $webinar_id)
    {
        $fcmTokensQuery = UserFirebaseSessions::query();

        if ($data['type'] === 'single') {
            if (empty($user_id)) {
                return true;
            }

            $fcmTokensQuery->where('user_id', $user_id);
        }

        if ($data['type'] === 'all_users') {
        }

        if ($data['type'] === 'students') {
            $usersIds = User::query()->where('role_id', Role::getUserRoleId())
                ->pluck('id')->toArray();

            $fcmTokensQuery->whereIn('user_id', $usersIds);
        }

        if ($data['type'] === 'instructors') {
            $usersIds = User::query()->where('role_id', Role::getTeacherRoleId())
                ->pluck('id')->toArray();

            $fcmTokensQuery->whereIn('user_id', $usersIds);
        }

        if ($data['type'] === 'organizations') {
            $usersIds = User::query()->where('role_id', Role::getOrganizationRoleId())
                ->pluck('id')->toArray();

            $fcmTokensQuery->whereIn('user_id', $usersIds);
        }

        $fcmTokensQuery->orderBy('created_at', 'desc');

        $fcmTokens    = $fcmTokensQuery->get();
        $deviceTokens = array();

        foreach ($fcmTokens as $fcmToken) {
            if ($fcmToken->fcm_token && strlen($fcmToken->fcm_token) > 0) {
                $deviceTokens[] = $fcmToken->fcm_token;
            }
        }

        if (count($deviceTokens) > 0) {
            $messageFCM = app('firebase.messaging');

            foreach ($deviceTokens as $fcmToken) {
                $fcmMessage = CloudMessage::new();
                $fcmMessage = $fcmMessage->withChangedTarget('token', $fcmToken);
                $fcmMessage = $fcmMessage->withData(
                    array(
                        'user_id'    => $user_id,
                        'group_id'   => $group_id,
                        'webinar_id' => $webinar_id,
                        'sender_id'  => auth()->id(),
                        'title'      => $data['title'],
                        'message'    => preg_replace('/<[^>]*>/', '', $data['message']),
                        'sender'     => Notification::$AdminSender,
                        'type'       => $data['type'],
                        'created_at' => time(),
                    )
                );

                $fcmMessage = $fcmMessage->withNotification(\Kreait\Firebase\Messaging\Notification::create($data['title'], preg_replace('/<[^>]*>/', '', $data['message'])));

                $fcmMessage = $fcmMessage->withAndroidConfig(
                    \Kreait\Firebase\Messaging\AndroidConfig::fromArray(
                        array(
                            'ttl'          => '3600s',
                            'priority'     => 'high',
                            'notification' => array(
                                'color' => '#f45342',
                                'sound' => 'default',
                            ),
                        )
                    )
                );

                try {
                    $messageFCM->send($fcmMessage);
                } catch (\Exception $exception) {
                    // dd($exception);
                }
            }
        }
    }

    public function showCreateForm($groupId = null)
    {
        $webinars    = Webinar::all();
        $instructors = User::where('role_id', 4)->get(); // المدرسين
        $allStudents = User::where('role_id', 1)->get(); // جميع الطلاب
        $group    = null;
        $students = [];

        if ($groupId) {
            $group = CourseGroup::with('members')->findOrFail($groupId);
            $students = User::whereIn('id', $group->members->pluck('student_id'))->get(); // فقط طلاب المجموعة
        }
        return view('course_groups.admin.create_regular', compact('webinars', 'instructors', 'allStudents', 'students', 'group'));
    }
    public function showVariableForm($groupId = null)
    {
        $webinars    = Webinar::all();
        $instructors = User::where('role_id', 4)->get(); // المدرسين
        $allStudents = User::where('role_id', 1)->get(); // جميع الطلاب
        $group    = null;
        $students = [];

        if ($groupId) {
            $group = CourseGroup::with('members')->findOrFail($groupId);
            $students = User::whereIn('id', $group->members->pluck('student_id'))->get(); // فقط طلاب المجموعة
        }
        return view('course_groups.admin.create_variable', compact('webinars', 'instructors', 'allStudents', 'students', 'group'));
    }


    /**
     * Display the groups for a student.
     */
    public function studentGroups()
    {
        $groups = GroupMember::where('student_id', auth()->id())
            ->with('group.webinar', 'group.instructor')
            ->get();

        return view('course-group.student', compact('groups'));
    }

    /**
     * Remove the specified CourseGroup from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the course group by ID
        $courseGroup = CourseGroup::find($id);

        // Check if the CourseGroup exists
        if (! $courseGroup) {
            return response()->json(
                array(
                    'message' => 'CourseGroup not found.',
                ),
                404
            );
        }
        $meetingJson = json_decode($courseGroup->meeting_json, true);
        $meetingType = $meetingJson['type'] ?? 'recurring';
        $zoomMeetingId = $courseGroup->meeting_id;

        if ($zoomMeetingId && $meetingType !== 'variable') {
            $zoomDeletionResponse = $this->deleteZoomMeeting($zoomMeetingId);
            if (! $zoomDeletionResponse['success']) {
                Log::warning('Failed to delete Zoom meeting', [
                            'meeting_id' => $zoomMeetingId,
                            'error'      => $zoomDeletionResponse['error'],
                        ]);
            }
        } elseif (
            $courseGroup->session_type === 'zoom' &&
            $meetingType === 'variable' &&
            ! empty($meetingJson['occurrences'])
        ) {
            foreach ($meetingJson['occurrences'] as $occurrence) {
                $zoomMeetingId = $occurrence['occurrence_id'] ?? null;

                if ($zoomMeetingId) {
                    $zoomDelete = $this->deleteZoomMeeting($zoomMeetingId);
                    if (! $zoomDelete['success']) {
                        Log::warning('Failed to delete Zoom meeting', [
                            'meeting_id' => $zoomMeetingId,
                            'error'      => $zoomDelete['error'],
                        ]);
                    }
                }
            }
        }
        // Delete related group members first
        $courseGroup->members()->delete();

        // Delete the course group itself
        $courseGroup->delete();
        if (strpos($_SERVER['HTTP_REFERER'], 'course-group/view') !== false) {
            return redirect()->route('webinar-groups.all')->with('success', 'تم حذف المجموعة بنجاح.');
        }
        return redirect()->back()->with('success', 'تم حذف المجموعة بنجاح.');
    }
    private function deleteZoomMeeting($meetingId)
    {
        $accessToken = $this->getZoomAccessToken(); // Retrieve OAuth access token
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');

        // Zoom API endpoint for deleting a meeting
        $zoomUrl = $zoomBaseUrl . "/meetings/{$meetingId}";

        // Make API request to delete the meeting
        $response = Http::withToken($accessToken)->delete($zoomUrl);

        if ($response->failed()) {
            return array(
                'success' => false,
                'error'   => 'Failed to delete Zoom meeting: ' . $response->body(),
            );
        }

        return array(
            'success' => true,
            'message' => 'Zoom meeting deleted successfully.',
        );
    }

    public function generateZoomMeetingSignature($meetingNumber, $role)
    {
        $sdkKey    = env('ZOOM_SDK_KEY');
        $sdkSecret = env('ZOOM_SDK_SECRET');
        $iat       = time(); // Current timestamp in seconds
        $exp       = $iat + 3600; // Token valid for 1 hour
        $payload   = array(
            'appKey'   => $sdkKey,
            'sdkKey'   => $sdkKey,
            'mn'       => $meetingNumber,
            'role'     => $role,
            'iat'      => $iat,
            'exp'      => $exp,
            'tokenExp' => $exp,
        );
        return JWT::encode($payload, $sdkSecret, 'HS256');
    }

    public function getZoomSignature(Request $request)
    {
        $meetingNumber = $request->input('meeting_number');
        $role          = $request->input('role');
        return response()->json(array( 'signature' => $this->generateZoomMeetingSignature($meetingNumber, $role) ));
    }


    public function zoomSession($group)
    {

        $currentUser = auth()->user();
        $courseGroup = CourseGroup::find($group);
        if ($courseGroup) {
            $instructorDetails = $courseGroup->instructor; // This fetches the instructor related to the course group
            // Display instructor details
            if ($instructorDetails && $currentUser->isTeacher() && $instructorDetails->id === $currentUser->id) {
                $role            = 1;// 0 for attendee, 1 for host
                $userName        = $instructorDetails->email;
                $userEmail       = $instructorDetails->email;
                $meetingPassword = '123456';
                $meetingNumber   = $courseGroup->meeting_id;
                $zoomSignature   = $this->generateZoomMeetingSignature($meetingNumber, $role);
                $zoomSdkKey      = env('ZOOM_SDK_KEY');
                return view(
                    'course_groups.front.zoom',
                    compact(
                        'group',
                        'role',
                        'meetingNumber',
                        'userName',
                        'userEmail',
                        'meetingPassword',
                        'zoomSignature',
                        'zoomSdkKey'
                    )
                );
            }
        }
        abort(404);
    }
    /**
     * Create a Zoom meeting for the specified instructor.
     *
     * @param object $instructor The instructor object, containing at least an email and timezone.
     * @param array  $data The data required to create the meeting, including:
     *      - 'webinar_id' (string): The webinar ID to include in the meeting topic.
     *      - 'meeting_recurring' (bool): Whether the meeting is recurring.
     *      - 'meeting_start_time' (string): The start time of the meeting in ISO 8601 format.
     *      - 'meeting_duration' (int): The duration of the meeting in minutes.
     *      - 'meeting_end_time' (string|null): Optional end time for recurring meetings in ISO 8601 format.
     *
     *  Recurrence Settings:
     *  - Recurrence Type (`type`):
     *      - `1`: Daily
     *      - `2`: Weekly
     *      - `3`: Monthly
     *  - Repeat Interval (`repeat_interval`):
     *      - For Daily (`type = 1`): Interval in days (e.g., `1` = every day, `2` = every 2 days).
     *      - For Weekly (`type = 2`): Interval in weeks (e.g., `1` = every week, `2` = every 2 weeks).
     *      - For Monthly (`type = 3`): Interval in months (e.g., `1` = every month, `2` = every 2 months).
     *  - Additional Weekly Fields (`type = 2`):
     *      - `weekly_days`: Comma-separated values of days (e.g., `2,4` = Monday, Wednesday).
     *      - Days of the week mapping:
     *          - `1`: Sunday
     *          - `2`: Monday
     *          - `3`: Tuesday
     *          - `4`: Wednesday
     *          - `5`: Thursday
     *          - `6`: Friday
     *          - `7`: Saturday
     *  - Additional Monthly Fields (`type = 3`):
     *      - `monthly_day`: Day of the month (e.g., `15` = 15th day).
     *      - `monthly_week`: Week of the month (`-1` = last week).
     *      - `monthly_week_day`: Day of the week (same as weekly_days mapping).
     *
     * @return array Response data including success status and meeting details or error information.
     */
    private function createZoomMeeting($instructor, $data)
    {
        $accessToken = $this->getZoomAccessToken();
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');
        $zoomUrl     = $zoomBaseUrl . "/users/{$instructor->email}/meetings";

        $startDate = $data['meeting_start_date'] ?? null;
        $startTime = $data['meeting_start_time'] ?? null;

        $startDateTime = ($startDate && $startTime)
        ? Carbon::parse($startDate . ' ' . $startTime, 'Asia/Dubai')
        : Carbon::now('Asia/Dubai');

        $isRecurring = !empty($data['meeting_recurring']);

        $meetingData = [
        'topic'      => $data['topic'] ?? "Meeting for Webinar ID {$data['webinar_id']}",
        'type'       => $isRecurring ? 8 : 2,
        'start_time' => $startDateTime->format('Y-m-d\TH:i:s'),
        'duration'   => $data['meeting_duration'] * 60,
        'timezone'   => 'Asia/Dubai',
        'settings'   => [
            'host_video'        => (bool) ($data['host_video'] ?? false),
            'participant_video' => (bool) ($data['participant_video'] ?? false),
            'audio'             => $data['audio_option'] ?? 'both',
            'join_before_host'  => false,
            'mute_upon_entry'   => true,
            'approval_type'     => 0,
        ],
        ];

        if ($isRecurring) {
            $recurrence = [
            'type'            => (int) $data['recurrence_type'],
            'repeat_interval' => (int) $data['recurrence_interval'],
            'end_times'       => (int) $data['end_times'],
            ];

            if ((int) $data['recurrence_type'] === 2 && !empty($data['weekly_days'])) {
                $recurrence['weekly_days'] = implode(',', $data['weekly_days']);
            } elseif ((int) $data['recurrence_type'] === 3 && !empty($data['monthly_day'])) {
                $recurrence['monthly_day'] = (int) $data['monthly_day'];
            }

            $meetingData['recurrence'] = $recurrence;
        }

        $response = Http::withToken($accessToken)->post($zoomUrl, $meetingData);

        if ($response->failed()) {
            return [
            'success' => false,
            'error'   => 'Failed to create Zoom meeting: ' . $response->body(),
            ];
        }

        $meeting = $response->json();
        $meetingId = $meeting['id'] ?? null;

        // ✅ فقط إذا متكرر، نجلب البيانات التفصيلية
        if ($isRecurring && $meetingId) {
            $detailsResponse = Http::withToken($accessToken)->get("{$zoomBaseUrl}/meetings/{$meetingId}");
            if ($detailsResponse->successful()) {
                $details = $detailsResponse->json();
                if (isset($details['occurrences'])) {
                    $meeting['occurrences'] = $details['occurrences'];
                }
            }
        }

        return [
        'success' => true,
        'data'    => $meeting,
        ];
    }


    private function updateZoomMeeting($meetingId, $instructor, $data)
    {
        $accessToken = $this->getZoomAccessToken();
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');
        $zoomUrl     = $zoomBaseUrl . "/meetings/{$meetingId}";

        $isRecurring = !empty($data['meeting_recurring']);

        $meetingData = [
        'topic'      => "Updated Meeting for Webinar ID {$data['webinar_id']}",
        'start_time' => Carbon::parse($data['meeting_start_time'], 'Asia/Dubai')->format('Y-m-d\TH:i:s'),
        'duration'   => $data['meeting_duration'] * 60,
        'timezone'   => 'Asia/Dubai',
        'settings'   => [
            'host_video'        => (bool) $data['host_video'],
            'participant_video' => (bool) $data['participant_video'],
            'audio'             => $data['audio_option'],
            'join_before_host'  => false,
            'mute_upon_entry'   => true,
            'approval_type'     => 0,
        ],
        ];

        if ($isRecurring) {
            $recurrence = [
            'type'            => (int) $data['recurrence_type'],
            'repeat_interval' => (int) $data['recurrence_interval'],
            'end_times'       => (int) $data['end_times'],
            ];

            if ((int) $data['recurrence_type'] === 2 && !empty($data['weekly_days'])) {
                $recurrence['weekly_days'] = implode(',', $data['weekly_days']);
            } elseif ((int) $data['recurrence_type'] === 3 && !empty($data['monthly_day'])) {
                $recurrence['monthly_day'] = (int) $data['monthly_day'];
            }

            $meetingData['recurrence'] = $recurrence;
        }

        $updateResponse = Http::withToken($accessToken)->patch($zoomUrl, $meetingData);

        if ($updateResponse->failed()) {
            return [
            'success' => false,
            'error'   => 'Failed to update Zoom meeting: ' . $updateResponse->body(),
            ];
        }

        // ✅ فقط إذا متكرر، نجلب البيانات الجديدة مع التكرارات
        if ($isRecurring) {
            $getResponse = Http::withToken($accessToken)->get($zoomUrl);

            if ($getResponse->failed()) {
                return [
                'success' => false,
                'error'   => 'Zoom meeting updated, but failed to fetch updated details: ' . $getResponse->body(),
                ];
            }

            $data = $getResponse->json();
            if (!isset($data['occurrences'])) {
                $data['occurrences'] = [];
            }

            return [
            'success' => true,
            'data'    => $data,
            ];
        }

        // إذا لم يكن متكرر، نعيد البيانات الحالية فقط
        return [
        'success' => true,
        'data'    => ['id' => $meetingId],
        ];
    }





    /**
     * Retrieve the Zoom OAuth access token.
     *
     * This function uses client credentials to authenticate with the Zoom API
     * and retrieve an OAuth access token. The access token is valid for 1 hour.
     *
     * @return string The Zoom OAuth access token.
     *
     * @throws \Illuminate\Http\Client\RequestException If the request to Zoom API fails.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If Zoom is not configured properly.
     */
    private function getZoomAccessToken()
    {
        /**
         * @var string $clientId
         */
        $clientId = getFeaturesSettings('zoom_client_id');
        /**
         * @var string $clientSecret
         */
        $clientSecret = getFeaturesSettings('zoom_client_secret');
        /**
         * @var string $account_id
         */
        $account_id = getFeaturesSettings('zoom_account_id');

        if (empty($clientId) || empty($clientSecret) || empty($account_id)) {
            abort(500, 'Zoom is not configured properly');
        }

        $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)->post(
            'https://zoom.us/oauth/token',
            array(
                'grant_type' => 'account_credentials',
                'account_id' => $account_id,
            )
        );

        if ($response->failed()) {
            abort(500, 'Failed to retrieve Zoom access token: ' . $response->body());
        }

        $data = $response->json();

        // Access token will expire in 1 hour; you may store it temporarily
        return $data['access_token'];
    }

    /**
     * Fetch Zoom meetings for a specific instructor.
     *
     * This function retrieves a list of Zoom meetings for a given instructor using the Zoom API.
     * Optional query parameters can be provided to filter or paginate results.
     *
     * @param \App\Models\Instructor $instructor The instructor whose meetings are to be fetched.
     * @param array                  $queryParams Optional query parameters for the API request.
     *
     * @return array|false The response from Zoom API as an associative array, or false on failure.
     */
    public function fetchUserMeetings($instructor, $queryParams = array())
    {
        $accessToken = $this->getZoomAccessToken(); // Retrieve OAuth access token
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');

        // Zoom API endpoint for fetching meetings
        $zoomUrl = $zoomBaseUrl . "/users/{$instructor->email}/meetings";

        // Make API request to Zoom
        $response = Http::withToken($accessToken)
        ->get($zoomUrl, $queryParams);

        if ($response->failed()) {
            return false;
        }

        return $response->json();
    }
    /**
     * Fetch recordings for a recurring Zoom meeting by its meeting ID.
     *
     * This function retrieves all cloud recordings for a specific recurring meeting.
     *
     * @param string $meetingId The Zoom meeting ID.
     *
     * @return array|false The response from Zoom API as an associative array, or false on failure.
     */
    public function fetchMeetingRecordings($meetingId)
    {
        $accessToken = $this->getZoomAccessToken(); // Retrieve OAuth access token
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');

        // Zoom API endpoint for fetching recordings
        $zoomUrl = $zoomBaseUrl . "/meetings/{$meetingId}/recordings";

        // Make API request to Zoom
        $response = Http::withToken($accessToken)->get($zoomUrl);

        if ($response->failed()) {
            return false;
        }

        return $response->json();
    }
    /**
     * Handle AJAX request to fetch Zoom recordings by meeting ID.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request.
     * @return \Illuminate\Http\JsonResponse The JSON response containing recordings or an error message.
     */
    public function getMeetingRecordings(Request $request)
    {
        // Validate the meeting ID from the request
        $validated = $request->validate(
            array(
                'meeting_id' => 'required|string',
            )
        );

        // Fetch recordings using the meeting ID
        $meetingId  = $validated['meeting_id'];
        $recordings = $this->fetchMeetingRecordings($meetingId);

        if ($recordings === false) {
            return response()->json(
                array(
                    'success' => false,
                    'message' => 'لا توجد تسجيلات.',
                ),
                500
            );
        }

        return response()->json(
            array(
                'success' => true,
                'data'    => $recordings,
            )
        );
    }

    /**
     * List webinars with their associated groups.
     *
     * This function retrieves all webinars that are associated with at least one group.
     * It loads the "groups" relationship for each webinar and returns a view for displaying the data.
     *
     * @return \Illuminate\Contracts\View\View The view displaying webinars with their associated groups.
     */
    public function getWebinarsWithGroups()
    {
        return Webinar::with('groups') // Load groups relationship
        ->has('groups') // Only include webinars with at least one group
        ->get();
    }
    public function getPaginatedGroups(Request $request)
    {
        $perPage = $request->input('per_page', 10); // عدد العناصر في الصفحة
        $page    = $request->input('page', 1);

        $query = CourseGroup::with(['webinar', 'instructor', 'members.student']);

        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->input('instructor_id'));
        }

        if ($request->has('webinar_id')) {
            $query->where('webinar_id', $request->input('webinar_id'));
        }

        $groups = $query->paginate($perPage, ['*'], 'page', $page);
        $screen = 'admin';
        return view('course_groups.admin.paginated_groups', compact('groups', 'screen'));
    }

    public function editGroup($groupId)
    {
        $group    = CourseGroup::with(['webinar', 'instructor', 'members'])->find($groupId);
        $students = User::where('role_id', 1)->get();
        return view('course_groups.admin.group', compact('group', 'students'));
    }

    /**
     * List webinars with their associated groups.
     *
     * This function retrieves all webinars that are associated with at least one group.
     * It loads the "groups" relationship for each webinar and returns a view for displaying the data.
     *
     * @return \Illuminate\Contracts\View\View The view displaying webinars with their associated groups.
     */
    public function listWebinarsWithGroups()
    {
        $webinars = $this->getWebinarsWithGroups();

        return view('course_groups.admin.webninars_groups', compact('webinars'));
    }
    public function getWebinarGroupsHtml($webinarId)
    {
        $webinar = Webinar::with([
            'groups' => function ($query) {
                $query->with(['members.student', 'instructor']);
            }
        ])->findOrFail($webinarId);

        // Group groups by instructor
        $groupsByInstructor = $webinar->groups->groupBy(fn ($group) => optional($group->instructor)->id ?? 0);

        $students = User::where('role_id', 1)->get();

        return view('course_groups.admin.partials.webinar_groups', [
            'webinar' => $webinar,
            'groupsByInstructor' => $groupsByInstructor,
            'students' => $students,
        ]);
    }



    /**
     * Display the groups for a specific webinar.
     */
    public function listInstructorGroups(Request $request)
    {
        $getInstructorGroups      = $this->getGroups($request);
        $groups      = $getInstructorGroups['groups'];
        $instructors = $getInstructorGroups['instructors'];
        $students    = $getInstructorGroups['students'];

        return view('course_groups.admin.instructor_groups', compact('groups', 'instructors', 'students'));
    }
    /**
     * filter webinars with their associated groups.
     *
     * This function retrieves all webinars that are associated with at least one group.
     * It loads the "groups" relationship for each webinar and returns a view for displaying the data.
     *
     * @return \Illuminate\Contracts\View\View The view displaying webinars with their associated groups.
     */
    public function filterWebinarsWithGroups()
    {
        $webinars = $this->getWebinarsWithGroups();
        $routeToList = route('course-group.list');
        $students = User::where('role_id', 1)->get();
        return view('course_groups.admin.filter_webninars_groups', compact('webinars', 'routeToList', 'students'));
    }
    public function getWebinarInstructors($webinarId)
    {
        $groups = CourseGroup::where('webinar_id', $webinarId)->with('instructor')->get();

        $instructors = $groups->map(function ($group) {
            return [
                'id' => $group->instructor->id ?? null,
                'name' => $group->instructor->full_name ?? 'Unnamed Instructor',
            ];
        })->unique('id')->values();

        return response()->json($instructors);
    }
    public function schedule(Request $request)
    {
        $weekOffset = $request->query('week', 0);
        $instructorIdFilter = $request->query('instructor_id');

        $today = Carbon::now('Asia/Dubai')->startOfWeek(Carbon::SATURDAY)->addWeeks($weekOffset);

        $weekDays = [];
        for ($i = 0; $i < 6; $i++) {
            $dayDate = $today->copy()->addDays($i);
            $weekDays[] = [
            'name'  => $dayDate->format('l'),
            'label' => $dayDate->translatedFormat('D (M d)'),
            'date'  => $dayDate->format('Y-m-d'),
            ];
        }

        $timeSlots = [];
        $start = Carbon::createFromTime(10, 0);
        $end = Carbon::createFromTime(22, 0);

        while ($start < $end) {
            $next = $start->copy()->addHour();
            $timeSlots[] = [
            'start' => $start->format('H:i'),
            'end'   => $next->format('H:i'),
            ];
            $start = $next;
        }

        $sessions = [];

        $courseGroups = CourseGroup::with('instructor', 'webinar')->get();

        foreach ($courseGroups as $group) {
            $meetingJson = json_decode($group->meeting_json, true);
            $isRecurring = $group->meeting_recurring == 1 ? 1 : 0;
            $division    = $group->session_type === 'offline' ? 60 : 3600;
            if (!empty($meetingJson['occurrences'])) {
                $lastOccurrence = collect($meetingJson['occurrences'])->sortByDesc('start_time')->first();
                $lastDay = $isRecurring && $lastOccurrence ? Carbon::parse($lastOccurrence['start_time'])->format('Y-m-d') : null;
                
                foreach ($meetingJson['occurrences'] as $occurrence) {
                    $startUtc = Carbon::parse($occurrence['start_time'])->setTimezone('Asia/Dubai');

                    $sessions[] = [
                    'group_id'        => $group->id,
                    'instructor_id'   => $group->instructor_id,
                    'instructor_name' => $group->instructor->full_name ?? '',
                    'day'             => $startUtc->format('Y-m-d'),
                    'time'            => $startUtc->format('H:i'),
                    'duration'        => ($occurrence['duration'] ?? $group->meeting_duration) / $division,
                    'session_type'    => $group->session_type ?? 'zoom',
                    'webinar_title'   => $group->webinar->title ?? '',
                    'is_recurring'    => $isRecurring,
                    'last_day'        => $lastDay, // ✅ فقط لو متكرر
                    ];
                }
            } else {
                $startDate = Carbon::parse($group->meeting_start_time);
                $endDate = Carbon::parse($group->meeting_end_time);
                $durationHours = $group->meeting_duration / $division;
                $recurrence = $meetingJson['recurrence'] ?? [];

                if ($isRecurring && isset($recurrence['type'])) {
                    $lastDay = $endDate->format('Y-m-d');

                    $type = $recurrence['type'];
                    $interval = $recurrence['repeat_interval'] ?? 1;
                    $weeklyDays = isset($recurrence['weekly_days']) ? explode(',', $recurrence['weekly_days']) : [];
                    $monthlyDay = $recurrence['monthly_day'] ?? null;

                    $current = $startDate->copy();

                    while ($current <= $endDate) {
                        $add = false;

                        if ($type == 1) {
                            $add = true;
                            $current->addDays($interval - 1);
                        } elseif ($type == 2) {
                            if (in_array($current->dayOfWeekIso, $weeklyDays)) {
                                $add = true;
                            }
                        } elseif ($type == 3) {
                            if ($current->day == $monthlyDay) {
                                $add = true;
                            }
                        }

                        if ($add) {
                            $sessions[] = [
                            'group_id'        => $group->id,
                            'instructor_id'   => $group->instructor_id,
                            'instructor_name' => $group->instructor->full_name ?? '',
                            'day'             => $current->format('Y-m-d'),
                            'time'            => $startDate->format('H:i'),
                            'duration'        => $durationHours,
                            'session_type'    => $group->session_type ?? 'offline',
                            'webinar_title'   => $group->webinar->title ?? '',
                            'is_recurring'    => $isRecurring,
                            'last_day'        => $lastDay, // ✅ هنا فقط مع المكرر
                            ];
                        }

                        $current->addDay();
                    }
                } else {
                    // ✅ جلسة واحدة بدون تكرار (لا نحتاج last_day)
                    $sessions[] = [
                    'group_id'        => $group->id,
                    'instructor_id'   => $group->instructor_id,
                    'instructor_name' => $group->instructor->full_name ?? '',
                    'day'             => $startDate->format('Y-m-d'),
                    'time'            => $startDate->format('H:i'),
                    'duration'        => $durationHours,
                    'session_type'    => $group->session_type ?? 'offline',
                    'webinar_title'   => $group->webinar->title ?? '',
                    'is_recurring'    => 0,
                    'last_day'        => null, // ✅ صراحة نضعها null أو لا نرسلها أساساً
                    ];
                }
            }
        }

        if (!empty($instructorIdFilter)) {
            $sessions = collect($sessions)->where('instructor_id', $instructorIdFilter)->values()->toArray();
        }

        $instructors = User::where('role_name', 'teacher')->get();

        return view('course_groups.admin.schedule', compact('weekDays', 'timeSlots', 'sessions', 'weekOffset', 'instructors'));
    }
    public function addCompensatorySession(Request $request, $groupId)
    {
        $group = CourseGroup::findOrFail($groupId);
        $date = $request->input('date');
        $time = $request->input('time');

        if ($group->session_type === 'offline') {
            // ✅ تحقق من وجود التاريخ والوقت
            if (!$date || !$time) {
                return redirect()->back()->withErrors(['error' => 'يجب إدخال التاريخ والوقت للجلسات الأوفلاين.']);
            }

            $durationMinutes = $group->meeting_duration / 60;
            $result = $this->addCompensatoryOfflineSession($group, $date, $time, $durationMinutes);
        } else {
            // ✅ Zoom: فقط زيادة end_times دون الحاجة لتاريخ ووقت
            $result = $this->addCompensatoryZoomOccurrence($group);
        }

        if (!$result['success']) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->back()->with('success', 'تمت إضافة الجلسة التعويضية بنجاح.');
    }

    private function addCompensatoryZoomOccurrence($group)
    {
        try {
            $accessToken   = $this->getZoomAccessToken();
            $zoomBaseUrl   = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');
            $zoomMeetingUrl = "{$zoomBaseUrl}/meetings/{$group->meeting_id}";

            // ✅ جلب بيانات الاجتماع
            $meetingData = json_decode($group->meeting_json ?? '{}', true);
            $recurrence  = $meetingData['recurrence'] ?? [];

            $newEndTimes = ($recurrence['end_times'] ?? 1) + 1;

            $patchData = [
                'recurrence' => [
                    'type'            => $recurrence['type'] ?? 1,
                    'repeat_interval' => $recurrence['repeat_interval'] ?? 1,
                    'end_times'       => $newEndTimes,
                    'weekly_days'     => $recurrence['weekly_days'] ?? null,
                    'monthly_day'     => $recurrence['monthly_day'] ?? null,
                ]
            ];

            $patchResponse = Http::withToken($accessToken)->patch($zoomMeetingUrl, array_filter($patchData));

            if ($patchResponse->failed()) {
                return ['success' => false, 'error' => 'فشل تحديث اجتماع زووم: ' . $patchResponse->body()];
            }

            // ✅ تحديث البيانات محليًا
            $updatedMeeting = Http::withToken($accessToken)->get($zoomMeetingUrl)->json();
            $group->meeting_json = json_encode($updatedMeeting);

            // ⬇️ تحديد نوع التكرار وتعديل نهاية الجلسات محليًا
            $type = $recurrence['type'] ?? 1;
            $interval = $recurrence['repeat_interval'] ?? 1;

            $currentEnd = Carbon::parse($group->meeting_end_time);
            if ($type == 1) {
                $group->meeting_end_time = $currentEnd->addDays($interval);
            } elseif ($type == 2) {
                $group->meeting_end_time = $currentEnd->addWeeks($interval);
            } elseif ($type == 3) {
                $group->meeting_end_time = $currentEnd->addMonths($interval);
            }

            $group->save();

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function addCompensatoryOfflineSession($group, $date, $time, $durationMinutes)
    {
        try {
            $meetingData = json_decode($group->meeting_json ?? '{}', true);

            if (!isset($meetingData['occurrences'])) {
                $meetingData['occurrences'] = [];
            }

            $newOccurrence = [
                'occurrence_id' => time() . rand(1, 1000),
                'start_time' => Carbon::parse($date . ' ' . $time, 'Asia/Dubai')->toIso8601String(),
                'duration'   => $durationMinutes * 60,
                'status'     => 'available',
            ];

            $meetingData['occurrences'][] = $newOccurrence;

            $group->meeting_json = json_encode($meetingData);
            $group->save();

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
