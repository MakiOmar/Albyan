<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    public function view(Request $request)
    {
        return 'View';
    }
}
