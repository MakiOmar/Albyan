<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;

class InstructorsCustomController extends Controller
{
    public function index()
    {
        // Get CEO users
        $ceoUsers = User::where('role_name', 'ceo')
            ->where('status', 'active')
            ->where('verified', true)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Get active instructors (teachers)
        $instructors = User::where('role_name', 'teacher')
            ->where('status', 'active')
            ->where('verified', true)
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        // Get team members (Sales_CRM role)
        $teamMembers = User::where('role_name', 'Sales_CRM')
            ->where('status', 'active')
            ->where('verified', true)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $data = [
            'pageTitle' => 'Our Instructors',
            'ceoUsers' => $ceoUsers,
            'instructors' => $instructors,
            'teamMembers' => $teamMembers,
        ];

        return view('web.default.pages.instructors_custom', $data);
    }
} 