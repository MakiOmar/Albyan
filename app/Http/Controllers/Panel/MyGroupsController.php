<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\CourseGroup;

class MyGroupsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_webinars_lists");

        $user = auth()->user();

        if ($user->isUser()) {
            abort(404);
        }

        // Fetch only groups where the current user is the instructor
        $groups = CourseGroup::where('instructor_id', $user->id)
                             ->with(['webinar', 'members']) // Load related data
                             ->latest()
                             ->paginate(10); // Add pagination for performance

        $data = [
            'pageTitle' => 'My groups',
            'groups'    => $groups, // Pass groups to the view
        ];

        return view(getTemplate() . '.panel.my_groups.index', $data);
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
    public function view(Request $request)
    {
        $group = CourseGroup::findOrFail($request->id);
        $joinUrl       = false;
        $meetingID     = false;
        $nextStartTime = $this->groupNextTime($group, $joinUrl, $meetingID);
        return view(getTemplate() . '.panel.my_groups.view', [
            'group' => $group,
            'joinUrl' => $joinUrl,
            'nextStartTime' => $nextStartTime,
            'meetingID' => $meetingID,
        ]);
    }
}
