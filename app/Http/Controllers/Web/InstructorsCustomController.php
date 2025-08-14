<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;

class InstructorsCustomController extends Controller
{
    public function index(Request $request)
    {
        // Get CEO users
        $ceoUsers = User::where('role_name', 'ceo')
            ->where('status', 'active')
            ->where('verified', true)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Get active instructors (teachers) with filtering and pagination
        $instructorsQuery = User::where('role_name', 'teacher')
            ->where('status', 'active')
            ->where('verified', true)
            ->with(['occupations.category']);

        // Apply filters for instructors
        $instructorsQuery = $this->applyInstructorFilters($instructorsQuery, $request);

        // Get paginated results
        $perPage = $request->get('per_page', 20);
        $instructors = $instructorsQuery->orderBy('created_at', 'asc')->paginate($perPage);

        // Get team members (Sales_CRM role)
        $teamMembers = User::where('role_name', 'Sales_CRM')
            ->where('status', 'active')
            ->where('verified', true)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Get categories for filter dropdown
        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->orderBy('order', 'asc')
            ->get();

        // Handle AJAX request for instructors section only
        if ($request->ajax() && $request->get('section') === 'instructors') {
            $html = '';
            
            if ($instructors->count() > 0) {
                foreach ($instructors as $instructor) {
                    $html .= '<div class="col-4 col-md-3 mt-2">';
                    $html .= '<a href="' . $instructor->getProfileUrl() . '">';
                    $html .= '<img src="' . $instructor->getAvatar(100) . '" class="rounded-circle bg-dark p-1" width="100" height="100" alt="' . $instructor->full_name . '">';
                    $html .= '</a>';
                    $html .= '<center><strong>' . $instructor->full_name . '</strong></center>';
                    $html .= '</div>';
                }
            } else {
                $html = '<div class="col-12 text-center"><p class="text-muted">' . trans('instructors.no_instructors_found') . '</p></div>';
            }

            return response()->json([
                'html' => $html,
                'pagination' => [
                    'current_page' => $instructors->currentPage(),
                    'last_page' => $instructors->lastPage(),
                    'per_page' => $instructors->perPage(),
                    'total' => $instructors->total(),
                    'from' => $instructors->firstItem(),
                    'to' => $instructors->lastItem(),
                ],
                'filters' => [
                    'category_id' => $request->get('category_id'),
                    'per_page' => $request->get('per_page', 20),
                    'search' => $request->get('search'),
                ]
            ]);
        }

        $data = [
            'pageTitle' => trans('instructors.page_title'),
            'ceoUsers' => $ceoUsers,
            'instructors' => $instructors,
            'teamMembers' => $teamMembers,
            'categories' => $categories,
            'filters' => [
                'category_id' => $request->get('category_id'),
                'per_page' => $request->get('per_page', 20),
                'search' => $request->get('search'),
            ]
        ];

        return view('web.default.pages.instructors_custom', $data);
    }

    private function applyInstructorFilters($query, $request)
    {
        // Filter by category - only apply if a specific category is selected
        if ($request->has('category_id') && $request->get('category_id') && $request->get('category_id') !== '' && $request->get('category_id') !== null) {
            $categoryId = $request->get('category_id');
            
            $query->whereHas('occupations', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filter by search term - only apply if search term is not empty
        if ($request->has('search') && $request->get('search') && trim($request->get('search')) !== '') {
            $search = trim($request->get('search'));
            
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhere('headline', 'like', "%{$search}%");
            });
        }

        // If no filters are applied, return all instructors (this is the default behavior)
        return $query;
    }
} 