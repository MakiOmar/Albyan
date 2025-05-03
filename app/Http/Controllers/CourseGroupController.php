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

        return view('course_groups.admin.view', compact('group'));
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

    public function createGroup(Request $request)
    {
        $validated = $request->validate([
            'webinar_id'          => 'required|exists:webinars,id',
            'meeting_start_date'  => 'required|date',
            'meeting_start_time'  => 'required',
            'meeting_end_date'    => 'required_if:meeting_recurring,1|date|nullable',
            'meeting_end_time'    => 'required_if:meeting_recurring,1|nullable',
            'meeting_duration'    => 'required|integer|min:1',
            'meeting_recurring'   => 'required|in:0,1',
            'recurrence_type'     => 'required_if:meeting_recurring,1|in:1,2,3',
            'recurrence_interval' => 'required_if:meeting_recurring,1|integer|min:1',
            'weekly_days'         => 'required_if:recurrence_type,2|array|nullable',
            'weekly_days.*'       => 'in:1,2,3,4,5,6,7',
            'monthly_day'         => 'nullable|required_if:recurrence_type,3|integer|min:1|max:31',
            'participant_video'   => 'nullable|in:0,1',
            'host_video'          => 'nullable|in:0,1',
            'audio_option'        => 'nullable|in:both,voip,telephony',
            'student_ids'         => 'required|array|min:1',
            'student_ids.*'       => 'exists:users,id',
            'teacher_id'          => 'required|exists:users,id',
            'end_times'           => 'required|integer|min:1',
            'session_type'        => 'required|in:zoom,offline',
            'manual_occurrences'  => 'nullable|array',
            'manual_occurrences.*.date' => 'nullable|date',
            'manual_occurrences.*.time' => 'nullable|date_format:H:i',
        ]);

        $startDateTime = Carbon::parse($validated['meeting_start_date'] . ' ' . $validated['meeting_start_time'], 'Asia/Dubai');
        $endDateTime = ($validated['meeting_recurring'] == 1 && !empty($validated['meeting_end_date']) && !empty($validated['meeting_end_time']))
            ? Carbon::parse($validated['meeting_end_date'] . ' ' . $validated['meeting_end_time'], 'Asia/Dubai')
            : null;

        $webinar = Webinar::findOrFail($validated['webinar_id']);
        $instructor = User::findOrFail($validated['teacher_id']);
        $zoomMeeting = null;

        if ($validated['session_type'] == 'zoom') {
            $zoomMeetingResponse = $this->createZoomMeeting($instructor, $validated);

            if (!$zoomMeetingResponse['success']) {
                return redirect()->back()->withErrors(['zoom_meeting' => $zoomMeetingResponse['error']]);
            }

            $zoomData = $zoomMeetingResponse['data'];
            unset($zoomData['global_dial_in_numbers']); // إزالة بيانات لا حاجة لها
            $zoomMeeting = $zoomData;
        }

        if ($validated['session_type'] == 'offline') {
            $manualOccurrences = $request->input('manual_occurrences');

            if (is_array($manualOccurrences) && count($manualOccurrences) > 0) {
                $offlineOccurrences = collect($manualOccurrences)
                    ->filter(fn($item) => !empty($item['date']) && !empty($item['time']))
                    ->map(function ($item) use ($validated) {
                        return [
                            'start_time' => Carbon::parse($item['date'] . ' ' . $item['time'], 'Asia/Dubai')->toIso8601String(),
                            'duration'   => $validated['meeting_duration'] * 60,
                            'status'     => 'available',
                        ];
                    })->values()->all();
            } else {
                $offlineOccurrences = $this->generateOfflineOccurrences($validated);
            }

            $zoomMeeting = [
                'recurrence' => [
                    'type'            => (int) $validated['recurrence_type'],
                    'repeat_interval' => (int) $validated['recurrence_interval'],
                    'end_times'       => (int) $validated['end_times'],
                    'weekly_days'     => isset($validated['weekly_days']) ? implode(',', $validated['weekly_days']) : null,
                    'monthly_day'     => $validated['monthly_day'] ?? null,
                ],
                'occurrences' => $offlineOccurrences,
            ];
        }

        $group = CourseGroup::create([
            'webinar_id'         => $validated['webinar_id'],
            'instructor_id'      => $validated['teacher_id'],
            'meeting_id'         => $zoomMeeting['id'] ?? time(),
            'meeting_start_time' => $startDateTime,
            'meeting_end_time'   => $endDateTime,
            'meeting_duration'   => $validated['meeting_duration'] * 60,
            'meeting_recurring'  => $validated['meeting_recurring'],
            'meeting_json'       => $zoomMeeting ? json_encode($zoomMeeting) : null,
            'session_type'       => $validated['session_type'],
        ]);

        foreach ($validated['student_ids'] as $studentId) {
            GroupMember::create([
                'group_id'   => $group->id,
                'student_id' => $studentId,
                'webinar_id' => $validated['webinar_id'],
            ]);
        }

        return redirect()->route('webinar-groups.all')->with('success', 'Group created successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'webinar_id'          => 'required|exists:webinars,id',
            'meeting_start_date'  => 'required|date',
            'meeting_start_time'  => 'required',
            'meeting_end_date'    => 'required_if:meeting_recurring,1|date|nullable',
            'meeting_end_time'    => 'required_if:meeting_recurring,1|nullable',
            'meeting_duration'    => 'required|integer|min:1',
            'meeting_recurring'   => 'required|in:0,1',
            'recurrence_type'     => 'required_if:meeting_recurring,1|in:1,2,3',
            'recurrence_interval' => 'required_if:meeting_recurring,1|integer|min:1',
            'weekly_days'         => 'required_if:recurrence_type,2|array|nullable',
            'weekly_days.*'       => 'in:1,2,3,4,5,6,7',
            'monthly_day'         => 'nullable|required_if:recurrence_type,3|integer|min:1|max:31',
            'participant_video'   => 'nullable|in:0,1',
            'host_video'          => 'nullable|in:0,1',
            'audio_option'        => 'nullable|in:both,voip,telephony',
            'student_ids'         => 'required|array|min:1',
            'student_ids.*'       => 'exists:users,id',
            'teacher_id'          => 'required|exists:users,id',
            'end_times'           => 'required|integer|min:1',
            'session_type'        => 'required|in:zoom,offline',
            'manual_occurrences'  => 'nullable|array',
            'manual_occurrences.*.date' => 'nullable|date',
            'manual_occurrences.*.time' => 'nullable|date_format:H:i',
        ]);

        $startDateTime = Carbon::parse($validated['meeting_start_date'] . ' ' . $validated['meeting_start_time'], 'Asia/Dubai');
        $endDateTime = ($validated['meeting_recurring'] == 1 && !empty($validated['meeting_end_date']) && !empty($validated['meeting_end_time']))
            ? Carbon::parse($validated['meeting_end_date'] . ' ' . $validated['meeting_end_time'], 'Asia/Dubai')
            : null;

        $group = CourseGroup::findOrFail($id);
        $instructor = User::findOrFail($validated['teacher_id']);

        $zoomMeeting = null;
        if ($validated['session_type'] == 'zoom') {
            $zoomUpdateResponse = $this->updateZoomMeeting($group->meeting_id, $instructor, $validated);

            if (!$zoomUpdateResponse['success']) {
                return redirect()->back()->withErrors(['zoom_meeting' => $zoomUpdateResponse['error']]);
            }

            $zoomData = $zoomUpdateResponse['data'];
            unset($zoomData['global_dial_in_numbers']);
            $zoomMeeting = $zoomData;
        }

        if ($validated['session_type'] == 'offline') {
            $manualOccurrences = $request->input('manual_occurrences');

            if (is_array($manualOccurrences) && count($manualOccurrences) > 0) {
                $offlineOccurrences = collect($manualOccurrences)
                    ->filter(fn($item) => !empty($item['date']) && !empty($item['time']))
                    ->map(function ($item) use ($validated) {
                        return [
                            'occurrence_id' => time() . rand(1, 1000),
                            'start_time' => Carbon::parse($item['date'] . ' ' . $item['time'], 'Asia/Dubai')->toIso8601String(),
                            'duration'   => $validated['meeting_duration'] * 60,
                            'status'     => 'available',
                        ];
                    })->values()->all();
            } else {
                $offlineOccurrences = $this->generateOfflineOccurrences($validated);
            }

            $zoomMeeting = [
                'recurrence' => [
                    'type'            => (int) $validated['recurrence_type'],
                    'repeat_interval' => (int) $validated['recurrence_interval'],
                    'end_times'       => (int) $validated['end_times'],
                    'weekly_days'     => isset($validated['weekly_days']) ? implode(',', $validated['weekly_days']) : null,
                    'monthly_day'     => $validated['monthly_day'] ?? null,
                ],
                'occurrences' => $offlineOccurrences,
            ];
        }

        $group->update([
            'webinar_id'         => $validated['webinar_id'],
            'instructor_id'      => $validated['teacher_id'],
            'meeting_start_time' => $startDateTime,
            'meeting_end_time'   => $endDateTime,
            'meeting_duration'   => $validated['meeting_duration'] * 60,
            'meeting_recurring'  => $validated['meeting_recurring'],
            'meeting_json'       => $zoomMeeting ? json_encode($zoomMeeting) : null,
            'session_type'       => $validated['session_type'],
        ]);

        GroupMember::where('group_id', $group->id)->delete();
        foreach ($validated['student_ids'] as $studentId) {
            GroupMember::create([
                'group_id'   => $group->id,
                'student_id' => $studentId,
                'webinar_id' => $validated['webinar_id'],
            ]);
        }

        return redirect()->back()->with('success', 'Group updated successfully.');
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
        return view('course_groups.admin.create', compact('webinars', 'instructors', 'allStudents', 'students', 'group'));
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
        $zoomMeetingId = $courseGroup->meeting_id;

        if ($zoomMeetingId) {
            $zoomDeletionResponse = $this->deleteZoomMeeting($zoomMeetingId);
            if (! $zoomDeletionResponse['success']) {
                LOG::info('Zoom errors', array( 'zoom_meeting' => $zoomDeletionResponse['error'] ));
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

        $meetingData = array(
            'topic'      => "Meeting for Webinar ID {$data['webinar_id']}",
            'type'       => $data['meeting_recurring'] ? 8 : 2,
            'start_time' => Carbon::parse($data['meeting_start_time'], 'Asia/Dubai')->format('Y-m-d\TH:i:s'),
            'duration'   => $data['meeting_duration'] * 60,
            'timezone'   => 'Asia/Dubai',
            'settings'   => array(
                'host_video'        => (bool) $data['host_video'],
                'participant_video' => (bool) $data['participant_video'],
                'audio'             => $data['audio_option'],
                'join_before_host'  => false,
                'mute_upon_entry'   => true,
                'approval_type'     => 0,
            ),
        );

        if ($data['meeting_recurring']) {
            $recurrence = array(
                'type'            => (int) $data['recurrence_type'], // 1: Daily, 2: Weekly, 3: Monthly
                'repeat_interval' => $data['recurrence_interval'], // Interval from form
                'end_times' => $data['end_times'],
            );

            // Additional settings for weekly or monthly recurrence
            if ($data['recurrence_type'] == 2) { // Weekly
                $recurrence['weekly_days'] = implode(',', $data['weekly_days']); // e.g., "1,3,5"
            } elseif ($data['recurrence_type'] == 3) { // Monthly
                $recurrence['monthly_day'] = $data['monthly_day'];
            }

            $meetingData['recurrence'] = $recurrence;
        }

        $response = Http::withToken($accessToken)->post($zoomUrl, $meetingData);

        if ($response->failed()) {
            return array(
                'success' => false,
                'error'   => 'Failed to create Zoom meeting: ' . $response->body(),
            );
        }

        return array(
            'success' => true,
            'data'    => $response->json(),
        );
    }
    private function updateZoomMeeting($meetingId, $instructor, $data)
    {
        $accessToken = $this->getZoomAccessToken();
        $zoomBaseUrl = env('ZOOM_BASE_URL', 'https://api.zoom.us/v2');
        $zoomUpdateUrl = $zoomBaseUrl . "/meetings/{$meetingId}";
        $zoomGetUrl    = $zoomUpdateUrl; // same endpoint

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

        if ($data['meeting_recurring']) {
            $recurrence = [
                'type'            => (int) $data['recurrence_type'],
                'repeat_interval' => $data['recurrence_interval'],
                'end_times' => $data['end_times'],
            ];

            if ($data['recurrence_type'] == 2) {
                $recurrence['weekly_days'] = implode(',', $data['weekly_days']);
            } elseif ($data['recurrence_type'] == 3) {
                $recurrence['monthly_day'] = $data['monthly_day'];
            }

            $meetingData['recurrence'] = $recurrence;
        }

        // 1️⃣ تحديث الاجتماع
        $updateResponse = Http::withToken($accessToken)->patch($zoomUpdateUrl, $meetingData);

        if ($updateResponse->failed()) {
            return [
            'success' => false,
            'error'   => 'Failed to update Zoom meeting: ' . $updateResponse->body(),
            ];
        }

        // 2️⃣ جلب البيانات المحدثة
        $getResponse = Http::withToken($accessToken)->get($zoomGetUrl);

        if ($getResponse->failed()) {
            return [
            'success' => false,
            'error'   => 'Zoom meeting updated, but failed to fetch updated details: ' . $getResponse->body(),
            ];
        }

        return [
        'success' => true,
        'data'    => $getResponse->json(),
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

            if (!empty($meetingJson['occurrences'])) {
                $lastOccurrence = collect($meetingJson['occurrences'])->sortByDesc('start_time')->first();
                $lastDay = $isRecurring && $lastOccurrence ? Carbon::parse($lastOccurrence['start_time'])->format('Y-m-d') : null;

                foreach ($meetingJson['occurrences'] as $occurrence) {
                    $startUtc = Carbon::parse($occurrence['start_time']);

                    $sessions[] = [
                    'group_id'        => $group->id,
                    'instructor_id'   => $group->instructor_id,
                    'instructor_name' => $group->instructor->full_name ?? '',
                    'day'             => $startUtc->format('Y-m-d'),
                    'time'            => $startUtc->format('H:i'),
                    'duration'        => ($occurrence['duration'] ?? $group->meeting_duration) / 60,
                    'session_type'    => $group->session_type ?? 'zoom',
                    'webinar_title'   => $group->webinar->title ?? '',
                    'is_recurring'    => $isRecurring,
                    'last_day'        => $lastDay, // ✅ فقط لو متكرر
                    ];
                }
            } else {
                $startDate = Carbon::parse($group->meeting_start_time);
                $endDate = Carbon::parse($group->meeting_end_time);
                $durationHours = $group->meeting_duration / 60;
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
