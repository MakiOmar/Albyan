@extends('admin.layouts.app')


@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.seo_metas') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}/settings">{{ trans('admin/main.settings') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.seo_metas') }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <ul class="nav nav-pills" id="myTab3" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link active"
                                       id="extra_meta_tags-tab" data-toggle="tab" href="#extra_meta_tags"
                                       role="tab" aria-controls="extra_meta_tags"
                                       aria-selected="true">{{ trans('update.extra_meta_tags') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link"
                                       id="schema-tab" data-toggle="tab" href="#schema"
                                       role="tab" aria-controls="schema"
                                       aria-selected="false">{{ trans('update.schema_settings') }}</a>
                                </li>

                                @foreach(\App\Models\Setting::$pagesSeoMetas as $page)
                                    <li class="nav-item">
                                        <a class="nav-link"
                                           id="{{ $page }}-tab" data-toggle="tab" href="#{{ $page }}"
                                           role="tab" aria-controls="{{ $page }}"
                                           aria-selected="true">{{ trans('admin/main.seo_metas_'.$page) }}</a>
                                    </li>
                                @endforeach
                            </ul>

                            @php
                                $itemValue = (!empty($settings) and !empty($settings['seo_metas'])) ? $settings['seo_metas']->value : '';

                                if (!empty($itemValue) and !is_array($itemValue)) {
                                    $itemValue = json_decode($itemValue, true);
                                }
                            @endphp

                            <div class="tab-content" id="myTabContent2">

                                <div class="tab-pane mt-3 fade show active" id="extra_meta_tags" role="tabpanel" aria-labelledby="extra_meta_tags-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-8">
                                            <form action="{{ getAdminPanelUrl() }}/settings/seo_metas/store" method="post">
                                            {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label>{{ trans('update.extra_meta_tags') }}</label>
                                                    <textarea name="value[extra_meta_tags]" rows="6" class="form-control">{{ (!empty($itemValue) and !empty($itemValue['extra_meta_tags'])) ? $itemValue['extra_meta_tags'] : '' }}</textarea>
                                                    <p class="mb-0">- {{ trans('update.extra_meta_tags_hint1') }}</p>
                                                    <p class="mb-0">- {{ trans('update.extra_meta_tags_hint2') }}</p>
                                                    <p class="mb-0">- {{ trans('update.extra_meta_tags_hint3') }}</p>
                                                    <p class="mb-0">- {{ trans('update.extra_meta_tags_hint4') }}</p>
                                                </div>

                                                <div class="form-group custom-switches-stacked">
                                                    <label class="custom-switch pl-0 d-flex align-items-center">
                                                        <label class="custom-switch-description mb-0 mr-2">Global indexing</label>
                                                        <input type="hidden" name="value[global_noindex]" value="0">
                                                        <input type="checkbox" name="value[global_noindex]" id="globalNoindexRobot" value="1" {{ (!empty($itemValue) and !empty($itemValue['global_noindex'])) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                        <span class="custom-switch-indicator"></span>
                                                        <label class="custom-switch-description mb-0 cursor-pointer" for="globalNoindexRobot">Prevent indexing for all pages</label>
                                                    </label>
                                                    <small class="text-muted d-block mt-1">When enabled, all pages output <code>noindex,nofollow</code> regardless of per-page SEO settings.</small>
                                                </div>

                                                <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Schema.org copy (per locale) --}}
                                @php
                                    $schemaValues = $schemaValues ?? [];
                                    $schemaDefaults = $schemaDefaults ?? [];
                                    $schemaLocale = $schemaLocale ?? app()->getLocale();
                                    $schemaField = function (string $key) use ($schemaValues, $schemaDefaults) {
                                        if (array_key_exists($key, $schemaValues) && $schemaValues[$key] !== '') {
                                            return $schemaValues[$key];
                                        }
                                        return $schemaDefaults[$key] ?? '';
                                    };
                                @endphp
                                <div class="tab-pane mt-3 fade" id="schema" role="tabpanel" aria-labelledby="schema-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-8">
                                            <form action="{{ getAdminPanelUrl() }}/settings/schema_settings/store" method="post">
                                                {{ csrf_field() }}

                                                @if(!empty(getGeneralSettings('content_translate')))
                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('auth.language') }}</label>
                                                        <select name="locale" class="form-control js-schema-locale">
                                                            @foreach(getUserLanguagesLists() as $lang => $language)
                                                                <option value="{{ mb_strtolower($lang) }}" @if(mb_strtolower($schemaLocale) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="locale" value="{{ mb_strtolower($schemaLocale) }}">
                                                @endif

                                                <h6 class="font-weight-bold mt-3">{{ trans('update.schema_org_section') }}</h6>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_legal_name') }}</label>
                                                    <input type="text" name="value[legal_name]" class="form-control" value="{{ $schemaField('legal_name') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_alternate_names') }}</label>
                                                    <textarea name="value[alternate_names]" rows="3" class="form-control">{{ $schemaField('alternate_names') }}</textarea>
                                                    <small class="text-muted">{{ trans('update.schema_alternate_names_hint') }}</small>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_org_description') }}</label>
                                                    <textarea name="value[org_description]" rows="4" class="form-control">{{ $schemaField('org_description') }}</textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_logo_caption') }}</label>
                                                    <input type="text" name="value[logo_caption]" class="form-control" value="{{ $schemaField('logo_caption') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_place_name') }}</label>
                                                    <input type="text" name="value[place_name]" class="form-control" value="{{ $schemaField('place_name') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_admissions_contact_type') }}</label>
                                                    <input type="text" name="value[admissions_contact_type]" class="form-control" value="{{ $schemaField('admissions_contact_type') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_whatsapp_contact_type') }}</label>
                                                    <input type="text" name="value[whatsapp_contact_type]" class="form-control" value="{{ $schemaField('whatsapp_contact_type') }}">
                                                </div>

                                                <h6 class="font-weight-bold mt-4">{{ trans('update.schema_home_section') }}</h6>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_home_webpage_name') }}</label>
                                                    <input type="text" name="value[home_webpage_name]" class="form-control" value="{{ $schemaField('home_webpage_name') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_home_webpage_description') }}</label>
                                                    <textarea name="value[home_webpage_description]" rows="3" class="form-control">{{ $schemaField('home_webpage_description') }}</textarea>
                                                </div>

                                                <h6 class="font-weight-bold mt-4">{{ trans('update.schema_course_section') }}</h6>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_breadcrumb_home_name') }}</label>
                                                    <input type="text" name="value[breadcrumb_home_name]" class="form-control" value="{{ $schemaField('breadcrumb_home_name') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_online_instance_name_suffix') }}</label>
                                                    <input type="text" name="value[online_instance_name_suffix]" class="form-control" value="{{ $schemaField('online_instance_name_suffix') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_onsite_instance_name_suffix') }}</label>
                                                    <input type="text" name="value[onsite_instance_name_suffix]" class="form-control" value="{{ $schemaField('onsite_instance_name_suffix') }}">
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_course_workload_template') }}</label>
                                                    <input type="text" name="value[course_workload_template]" class="form-control" value="{{ $schemaField('course_workload_template') }}">
                                                    <small class="text-muted">{{ trans('update.schema_course_workload_template_hint') }}</small>
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('update.schema_learning_resource_type') }}</label>
                                                    <input type="text" name="value[learning_resource_type]" class="form-control" value="{{ $schemaField('learning_resource_type') }}">
                                                </div>

                                                <p class="text-muted small">{{ trans('update.schema_logo_fixed_hint') }}</p>

                                                <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @foreach(\App\Models\Setting::$pagesSeoMetas as $page)
                                    <div class="tab-pane mt-3 fade" id="{{ $page }}" role="tabpanel" aria-labelledby="{{ $page }}-tab">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <form action="{{ getAdminPanelUrl() }}/settings/seo_metas/store" method="post">
                                                    {{ csrf_field() }}

                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.title') }}</label>
                                                        <input type="text" name="value[{{ $page }}][title]" value="{{ (!empty($itemValue) and !empty($itemValue[$page])) ? $itemValue[$page]['title'] : old('title') }}" class="form-control  @error('title') is-invalid @enderror"/>
                                                        @error('title')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label>{{ trans('public.description') }}</label>
                                                        <textarea name="value[{{ $page }}][description]" rows="4" class="form-control  @error('description') is-invalid @enderror">{{ (!empty($itemValue) and !empty($itemValue[$page])) ? $itemValue[$page]['description'] : old('description') }}</textarea>
                                                        @error('description')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group custom-switches-stacked">
                                                        <label class="custom-switch pl-0 d-flex align-items-center">
                                                            <label class="custom-switch-description mb-0 mr-2">{{ trans('admin/main.no_index') }}</label>
                                                            <input type="hidden" name="value[{{ $page }}][robot]" value="noindex">
                                                            <input type="checkbox" name="value[{{ $page }}][robot]" id="{{ $page }}Robot" value="index" {{ (!empty($itemValue) and !empty($itemValue[$page]) and (empty($itemValue[$page]['robot']) or $itemValue[$page]['robot'] != 'noindex')) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                            <span class="custom-switch-indicator"></span>
                                                            <label class="custom-switch-description mb-0 cursor-pointer" for="{{ $page }}Robot">{{ trans('admin/main.index') }}</label>
                                                        </label>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <div class="section-title ml-0 mt-0 mb-3"><h4>{{trans('admin/main.hints')}}</h4></div>
            <div class="row">
                <div class="col-md-6">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{ trans('admin/main.seo_metas_hint_title_1') }}</div>
                        <div class=" text-small font-600-bold mb-2">{{ trans('admin/main.seo_metas_hint_description_1') }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{ trans('admin/main.seo_metas_hint_title_2') }}</div>
                        <div class=" text-small font-600-bold mb-2">{{ trans('admin/main.seo_metas_hint_description_2') }}</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection

@push('scripts_bottom')
    <script>
        (function () {
            function showSchemaTab() {
                var tabEl = document.getElementById('schema-tab');
                if (tabEl && typeof $ !== 'undefined') {
                    $(tabEl).tab('show');
                }
            }

            if (window.location.hash === '#schema') {
                showSchemaTab();
            }

            if (typeof $ !== 'undefined') {
                $('body').on('change', '.js-schema-locale', function () {
                    var val = $(this).val();
                    if (!val) {
                        return;
                    }
                    window.location.href = window.location.origin + window.location.pathname
                        + '?locale=' + encodeURIComponent(val) + '#schema';
                });
            }
        })();
    </script>
@endpush
