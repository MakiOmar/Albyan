<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\DatabaseSearchReplaceService;
use App\Services\PageSearchReplaceService;
use Illuminate\Http\Request;

class ToolsController extends Controller
{
    public function pagesSearchReplace(PageSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_settings');

        $data = [
            'pageTitle' => trans('admin/main.tools_pages_search_replace'),
            'pages' => Page::orderBy('name')->get(['id', 'name', 'link']),
            'fieldOptions' => $searchReplaceService->getFieldOptions(),
        ];

        return view('admin.tools.pages_search_replace', $data);
    }

    public function pagesSearchReplacePreview(Request $request, PageSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_settings');

        $data = $this->validatePagesSearchReplaceRequest($request);

        $result = $searchReplaceService->preview(
            $data['search'],
            $data['replace'] ?? '',
            $data['fields'],
            $data['page_ids'] ?? [],
            !empty($data['case_sensitive']),
            !empty($data['whole_word']),
            $data['locale'] ?? null
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function pagesSearchReplaceApply(Request $request, PageSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_settings');

        $data = $this->validatePagesSearchReplaceRequest($request);

        $result = $searchReplaceService->apply(
            $data['search'],
            $data['replace'] ?? '',
            $data['fields'],
            $data['page_ids'] ?? [],
            !empty($data['case_sensitive']),
            !empty($data['whole_word']),
            $data['locale'] ?? null
        );

        $toastData = [
            'title' => trans('public.success'),
            'msg' => trans('admin/main.page_search_replace_applied', [
                'occurrences' => $result['total_occurrences'],
                'records' => $result['updated_records'],
            ]),
            'status' => 'success',
        ];

        return redirect(getAdminPanelUrl() . '/tools/pages-search-replace')->with(['toast' => $toastData]);
    }

    public function databaseSearchReplace()
    {
        $this->authorize('admin_settings');

        $data = [
            'pageTitle' => trans('admin/main.tools_database_search_replace'),
        ];

        return view('admin.tools.database_search_replace', $data);
    }

    public function databaseSearchReplacePreview(Request $request, DatabaseSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_settings');

        $data = $this->validateDatabaseSearchReplaceRequest($request);

        $result = $searchReplaceService->preview(
            $data['search'],
            $data['replace'] ?? '',
            !empty($data['case_sensitive']),
            !empty($data['whole_word'])
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function databaseSearchReplaceApply(Request $request, DatabaseSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_settings');

        $data = $this->validateDatabaseSearchReplaceRequest($request);

        $result = $searchReplaceService->apply(
            $data['search'],
            $data['replace'] ?? '',
            !empty($data['case_sensitive']),
            !empty($data['whole_word'])
        );

        $toastData = [
            'title' => trans('public.success'),
            'msg' => trans('admin/main.db_search_replace_applied', [
                'occurrences' => $result['total_occurrences'],
                'records' => $result['updated_records'],
            ]),
            'status' => 'success',
        ];

        return redirect(getAdminPanelUrl() . '/tools/database-search-replace')->with(['toast' => $toastData]);
    }

    private function validatePagesSearchReplaceRequest(Request $request): array
    {
        return $request->validate([
            'search' => 'required|string|min:1',
            'replace' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*' => 'string|in:' . implode(',', array_merge(
                PageSearchReplaceService::PAGE_FIELDS,
                PageSearchReplaceService::TRANSLATED_FIELDS
            )),
            'page_ids' => 'nullable|array',
            'page_ids.*' => 'integer|exists:pages,id',
            'locale' => 'nullable|string|max:10',
            'case_sensitive' => 'nullable|in:0,1',
            'whole_word' => 'nullable|in:0,1',
        ]);
    }

    private function validateDatabaseSearchReplaceRequest(Request $request): array
    {
        return $request->validate([
            'search' => 'required|string|min:1',
            'replace' => 'nullable|string',
            'case_sensitive' => 'nullable|in:0,1',
            'whole_word' => 'nullable|in:0,1',
        ]);
    }
}
