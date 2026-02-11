@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
@endpush

<div class=" mt-3 ">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <form action="{{ getAdminPanelUrl() }}/settings/personalization/home_sections" method="post" id="js-home-sections-form">
                                {{ csrf_field() }}
                                <select name="name" id="js-home-section-name" class="form-control @error('name') is-invalid @enderror">
                                    <option value="">{{ trans('admin/main.select') }}</option>

                                    @foreach(\App\Models\HomeSection::$names as $sectionName)
                                        @if(!in_array($sectionName,$selectedSectionsName) || $sectionName == \App\Models\HomeSection::$category_courses)
                                            <option value="{{ $sectionName }}">{{ trans('admin/main.'.$sectionName) }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                {{-- Category courses options: category select, mode, specific webinars --}}
                                <div id="js-category-courses-options" class="mt-3 d-none">
                                    <div class="form-group">
                                        <label for="js-home-section-category-select">{{ trans('update.search_categories') }}</label>
                                        <select name="category_id" id="js-home-section-category-select" class="form-control search-category-select2" data-placeholder="{{ trans('update.search_categories') }}" style="width: 100%;">
                                            <option value=""></option>
                                        </select>
                                        @error('category_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('admin/main.category_courses_mode') }}</label>
                                        <div>
                                            <label class="mr-3">
                                                <input type="radio" name="category_courses_mode" value="recent" checked> {{ trans('admin/main.category_courses_mode_recent') }}
                                            </label>
                                            <label>
                                                <input type="radio" name="category_courses_mode" value="specific"> {{ trans('admin/main.category_courses_mode_specific') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div id="js-specific-courses-options" class="form-group d-none">
                                        <label for="js-home-section-webinars-select">{{ trans('admin/main.category_courses_select_webinars') }}</label>
                                        <select name="category_courses_webinar_ids[]" id="js-home-section-webinars-select" class="form-control" data-placeholder="{{ trans('admin/main.category_courses_select_webinars') }}" multiple style="width: 100%;">
                                        </select>
                                        @error('category_courses_webinar_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success mt-2">{{ trans('admin/main.add_new') }}</button>
                            </form>

                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 col-md-6">
                            <h3 class="font-20 font-weight-bold">{{ trans('admin/main.home_sections') }}</h3>

                            <ul class="draggable-lists list-group" data-order-table="home_sections">

                                @foreach($sections as $section)
                                    <li data-id="{{ $section->id }}" class="form-group list-group">
                                        <div class="d-flex align-items-center justify-content-between p-2 border rounded-lg">
                                            <span>
                                                {{ trans('admin/main.'.$section->name) }}
                                                @if($section->name == \App\Models\HomeSection::$category_courses && $section->category)
                                                    ({{ $section->category->title }}) – {{ $section->getCategoryCoursesMode() === 'specific' ? trans('admin/main.category_courses_mode_specific') : trans('admin/main.category_courses_mode_recent') }}
                                                @endif
                                            </span>

                                            <div class="d-flex align-items-center">
                                                @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/settings/personalization/home_sections/'. $section->id .'/delete','btnClass' => 'text-danger mr-2 font-16'])

                                                <div class="cursor-pointer move-icon font-16 mr-1">
                                                    <i class="fa fa-arrows-alt"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach


                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>

    <script src="/assets/default/js/admin/home_sections.min.js"></script>
    <script>
        (function ($) {
            var categoryCoursesOptions = $('#js-category-courses-options');
            var specificCoursesOptions = $('#js-specific-courses-options');
            var nameSelect = $('#js-home-section-name');
            var categorySelect = $('#js-home-section-category-select');
            var webinarsSelect = $('#js-home-section-webinars-select');
            var webinarsSelect2Initialized = false;

            function toggleCategoryCoursesOptions() {
                if (nameSelect.val() === '{{ \App\Models\HomeSection::$category_courses }}') {
                    categoryCoursesOptions.removeClass('d-none');
                } else {
                    categoryCoursesOptions.addClass('d-none');
                    specificCoursesOptions.addClass('d-none');
                }
            }

            function toggleSpecificCoursesOptions() {
                if ($('input[name="category_courses_mode"]:checked').val() === 'specific') {
                    specificCoursesOptions.removeClass('d-none');
                    initWebinarsSelect2();
                } else {
                    specificCoursesOptions.addClass('d-none');
                    webinarsSelect.val(null).trigger('change');
                }
            }

            function initWebinarsSelect2() {
                if (webinarsSelect2Initialized) return;
                webinarsSelect2Initialized = true;
                webinarsSelect.select2({
                    placeholder: webinarsSelect.attr('data-placeholder'),
                    minimumInputLength: 2,
                    allowClear: true,
                    ajax: {
                        url: (typeof adminPanelPrefix !== 'undefined' ? adminPanelPrefix : '') + '/webinars/search',
                        dataType: 'json',
                        type: 'POST',
                        data: function (params) {
                            return {
                                term: params.term,
                                category_id: categorySelect.val() || ''
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return { id: item.id, text: item.title };
                                })
                            };
                        }
                    }
                });
            }

            nameSelect.on('change', toggleCategoryCoursesOptions);
            $('input[name="category_courses_mode"]').on('change', toggleSpecificCoursesOptions);

            toggleCategoryCoursesOptions();
        })(jQuery);
    </script>
@endpush
