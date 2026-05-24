<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Translation\PageTranslation;
use App\Services\PageSearchReplaceService;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index()
    {
        removeContentLocale();

        $this->authorize('admin_pages_list');

        $pages = Page::orderBy('created_at', 'desc')->paginate(10);

        $data = [
            'pageTitle' => trans('admin/pages/setting.pages'),
            'pages' => $pages
        ];

        return view('admin.pages.lists', $data);
    }

    public function create()
    {
        $this->authorize('admin_pages_create');

        $data = [
            'pageTitle' => trans('admin/pages/setting.new_pages')
        ];

        return view('admin.pages.create', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_pages_create');

        $this->validate($request, [
            'locale' => 'required',
            'name' => 'required',
            'link' => 'required|unique:pages,link',
            'title' => 'required',
            'seo_description' => 'nullable|string|max:255',
            'content' => 'required',
            'styles' => 'nullable|string',
            'scripts' => 'nullable|string',
            'head_content' => 'nullable|string',
            'footer_content' => 'nullable|string',
        ]);

        $data = $request->all();

        $firstCharacter = substr($data['link'], 0, 1);
        if ($firstCharacter !== '/') {
            $data['link'] = '/' . $data['link'];
        }

        $data['robot'] = (!empty($data['robot']) and $data['robot'] == '1');

        $page = Page::create([
            'link' => $data['link'],
            'name' => $data['name'],
            'robot' => $data['robot'],
            'status' => $data['status'],
            'created_at' => time(),
        ]);

        PageTranslation::updateOrCreate([
            'page_id' => $page->id,
            'locale' => mb_strtolower($data['locale'])
        ], [
            'title' => $data['title'],
            'seo_description' => $data['seo_description'] ?? null,
            'content' => $data['content'],
            'styles' => $data['styles'] ?? null,
            'scripts' => $data['scripts'] ?? null,
            'head_content' => $data['head_content'] ?? null,
            'footer_content' => $data['footer_content'] ?? null,
        ]);

        return redirect(getAdminPanelUrl().'/pages');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_pages_edit');

        $locale = $request->get('locale', app()->getLocale());

        $page = Page::findOrFail($id);

        storeContentLocale($locale, $page->getTable(), $page->id);

        $data = [
            'pageTitle' => trans('admin/pages/setting.edit_pages') . $page->name,
            'page' => $page
        ];

        return view('admin.pages.create', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_pages_edit');

        $page = Page::findOrFail($id);

        $this->validate($request, [
            'locale' => 'required',
            'name' => 'required',
            'link' => 'required|unique:pages,link,' . $page->id,
            'title' => 'required',
            'seo_description' => 'nullable|string|max:255',
            'content' => 'required',
            'styles' => 'nullable|string',
            'scripts' => 'nullable|string',
            'head_content' => 'nullable|string',
            'footer_content' => 'nullable|string',
        ]);

        $data = $request->all();

        $firstCharacter = substr($data['link'], 0, 1);
        if ($firstCharacter !== '/') {
            $data['link'] = '/' . $data['link'];
        }

        $data['robot'] = (!empty($data['robot']) and $data['robot'] == '1');

        $page->update([
            'link' => $data['link'],
            'name' => $data['name'],
            'robot' => $data['robot'],
            'status' => $data['status'],
            'created_at' => time(),
        ]);

        PageTranslation::updateOrCreate([
            'page_id' => $page->id,
            'locale' => mb_strtolower($data['locale'])
        ], [
            'title' => $data['title'],
            'seo_description' => $data['seo_description'] ?? null,
            'content' => $data['content'],
            'styles' => $data['styles'] ?? null,
            'scripts' => $data['scripts'] ?? null,
            'head_content' => $data['head_content'] ?? null,
            'footer_content' => $data['footer_content'] ?? null,
        ]);

        removeContentLocale();

        return redirect(getAdminPanelUrl().'/pages');
    }

    public function delete($id)
    {
        $this->authorize('admin_pages_delete');

        $page = Page::findOrFail($id);

        $page->delete();

        return redirect(getAdminPanelUrl().'/pages');
    }

    public function statusTaggle($id)
    {
        $this->authorize('admin_pages_toggle');

        $page = Page::findOrFail($id);

        $page->update([
            'status' => ($page->status == 'draft') ? 'publish' : 'draft'
        ]);

        return redirect(getAdminPanelUrl().'/pages');
    }

    public function searchReplace(PageSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_pages_edit');

        $data = [
            'pageTitle' => trans('admin/main.page_tools_search_replace'),
            'pages' => Page::orderBy('name')->get(['id', 'name', 'link']),
            'fieldOptions' => $searchReplaceService->getFieldOptions(),
        ];

        return view('admin.pages.tools.search_replace', $data);
    }

    public function searchReplacePreview(Request $request, PageSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_pages_edit');

        $data = $this->validateSearchReplaceRequest($request);

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

    public function searchReplaceApply(Request $request, PageSearchReplaceService $searchReplaceService)
    {
        $this->authorize('admin_pages_edit');

        $data = $this->validateSearchReplaceRequest($request);

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

        return redirect(getAdminPanelUrl() . '/pages/tools/search-replace')->with(['toast' => $toastData]);
    }

    private function validateSearchReplaceRequest(Request $request): array
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
}
