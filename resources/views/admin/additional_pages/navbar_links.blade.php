@extends('admin.layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.settings_navbar_links') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.settings_navbar_links') }}</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Temporary live diagnostics: enable with ?debug=1 --}}
            @if(!empty($navbarDebug))
                <div class="card border-danger mb-3">
                    <div class="card-header bg-danger text-white">
                        Navbar links DEBUG (?debug=1) — remove after testing
                    </div>
                    <div class="card-body">
                        <pre style="direction: ltr; text-align: left; white-space: pre-wrap; word-break: break-word; max-height: 520px; overflow: auto; margin: 0; font-size: 12px;">{{ json_encode($navbarDebug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-8 col-lg-6">
                                    {{-- Tip when selected locale has no row but another locale still has live navbar links --}}
                                    @if(!empty($items) && !empty($itemsSourceLocale) && mb_strtolower($itemsSourceLocale) !== mb_strtolower($selectedLocal))
                                        <div class="alert alert-warning">
                                            {{-- HTML comment: list is showing fall-back locale items for editing visibility --}}
                                            Showing links saved for <strong>{{ strtoupper($itemsSourceLocale) }}</strong>
                                            (none found for <strong>{{ strtoupper($selectedLocal) }}</strong>).
                                            Switch language and save translations for each locale, or edit then save to store them under the selected language.
                                        </div>
                                    @endif

                                    <form action="{{ getAdminPanelUrl() }}/additional_page/navbar_links/store{{ request()->query('debug') == '1' ? '?debug=1' : '' }}" method="post">
                                        {{ csrf_field() }}

                                        <input type="hidden" name="navbar_link" value="{{ !empty($navbarLinkKey) ? $navbarLinkKey : 'newLink' }}">
                                        @if(request()->query('debug') == '1')
                                            <input type="hidden" name="debug" value="1">
                                        @endif

                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class="form-group">
                                                <label class="input-label">{{ trans('auth.language') }}</label>
                                                <select name="locale" class="form-control js-edit-content-locale js-navbar-links-locale">
                                                    @foreach($userLanguages as $lang => $language)
                                                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', $selectedLocal)) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                                @error('locale')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        @else
                                            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                        @endif


                                        <div class="form-group">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="value[title]" value="{{ (!empty($navbar_link)) ? $navbar_link->title : old('value.title') }}" class="form-control  @error('value.title') is-invalid @enderror"/>
                                            @error('value.title')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            {{-- Use admin/main.link — public.link Arabic string was a bad machine translation --}}
                                            <label>{{ trans('admin/main.link') }}</label>
                                            <input type="text" name="value[link]" value="{{ (!empty($navbar_link)) ? $navbar_link->link : old('value.link') }}" class="form-control  @error('value.link') is-invalid @enderror"/>
                                            @error('value.link')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.order') }}</label>
                                            <input type="number" name="value[order]" value="{{ (!empty($navbar_link)) ? $navbar_link->order : old('value.order') }}" class="form-control  @error('value.order') is-invalid @enderror"/>
                                            @error('value.order')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-1">{{ trans('admin/main.submit') }}</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive mt-4">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>{{ trans('admin/main.title') }}</th>
                                        <th>{{ trans('admin/main.link') }}</th>
                                        <th>{{ trans('admin/main.order') }}</th>
                                        <th>{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @if(!empty($items))
                                        @php
                                            $debugQs = request()->query('debug') == '1' ? '&debug=1' : '';
                                        @endphp
                                        @foreach($items as $key => $val)
                                            <tr>
                                                <td>{{ $val['title'] ?? '' }}</td>
                                                <td>{{ $val['link'] ?? '' }}</td>
                                                <td>{{ $val['order'] ?? '' }}</td>
                                                <td>
                                                    {{-- Keep locale (+ optional debug) on edit/delete --}}
                                                    <a href="{{ getAdminPanelUrl() }}/additional_page/navbar_links/{{ $key }}/edit?locale={{ urlencode($selectedLocal) }}{{ $debugQs }}" class="btn-sm" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <i class="fa fa-edit"></i>
                                                    </a>

                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/additional_page/navbar_links/'. $key .'/delete?locale='.urlencode($selectedLocal).$debugQs,'btnClass' => 'btn-sm'])
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        /* Preserve ?debug=1 while switching locale (global handler drops other query params). */
        (function ($) {
            $('body').off('change', '.js-navbar-links-locale').on('change', '.js-navbar-links-locale', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                var val = $(this).val();
                if (!val) {
                    return;
                }

                var url = window.location.origin + window.location.pathname + '?locale=' + encodeURIComponent(val);
                if (window.location.search.indexOf('debug=1') !== -1) {
                    url += '&debug=1';
                }
                window.location.href = url;
            });
        })(jQuery);
    </script>
@endpush
