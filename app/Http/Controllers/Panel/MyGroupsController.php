<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\CourseGroup;
use App\Http\Controllers\Common\HandlesCourseGroupMeetings;

class MyGroupsController extends Controller
{
    use HandlesCourseGroupMeetings;

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
            'user' => $user,
            'groups'    => $groups, // Pass groups to the view
        ];

        return view(getTemplate() . '.panel.my_groups.index', $data);
    }
    public function studentGroups(Request $request)
    {
        $this->authorize("panel_webinars_lists");

        $user = auth()->user();

        if ($user->isTeacher()) {
            abort(404);
        }
        $studentId = $user->id;
        $groups = CourseGroup::whereHas('members', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
        ->with(['webinar', 'instructor']) // optional for eager loading
        ->get();

        $data = [
            'pageTitle' => 'My groups',
            'user' => $user,
            'groups'    => $groups, // Pass groups to the view
        ];

        return view(getTemplate() . '.panel.my_groups.student', $data);
    }

    public function view(Request $request)
    {
        $group = CourseGroup::findOrFail($request->id);
        $joinUrl       = false;
        $meetingID     = false;
        $nextStartTime = $this->groupNextTime($group, $joinUrl, $meetingID);
        $user = auth()->user();

        $decodedMeeting = $group->meeting_json ? json_decode($group->meeting_json, true) : [];
        $occurrences = $decodedMeeting['occurrences'] ?? [];
        return view(getTemplate() . '.panel.my_groups.view', [
        'group' => $group,
        'user' => $user,
        'joinUrl' => $joinUrl,
        'nextStartTime' => $nextStartTime,
        'meetingID' => $meetingID,
        'occurrences' => $occurrences,
        ]);
    }
}
