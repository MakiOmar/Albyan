{{-- Content for optional Al-Byan homepage sections (enable each via Home sections) --}}
@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }
    $iv = is_array($itemValue) ? $itemValue : [];
@endphp

<div class="mt-3">
    <form action="{{ getAdminPanelUrl() }}/settings/main" method="post">
        {{ csrf_field() }}
        <input type="hidden" name="name" value="home_content_blocks">
        <input type="hidden" name="page" value="personalization">

        @if(!empty(getGeneralSettings('content_translate')))
            <div class="form-group col-md-6 px-0">
                <label class="input-label">{{ trans('auth.language') }}</label>
                <select name="locale" class="form-control js-edit-content-locale">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', (!empty($iv) and !empty($iv['locale'])) ? $iv['locale'] : app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif

        <p class="text-muted font-14 mb-4">{{ trans('update.home_content_blocks_hint') }}</p>

        {{-- Catalog / LMS section titles & hints (translatable per locale) --}}
        @php
            $sectionTitleKeys = [
                'featured_classes' => ['title' => 'home.featured_classes', 'hint' => 'home.featured_classes_hint', 'view_all' => 'home.view_all'],
                'latest_classes' => ['title' => 'home.latest_webinars', 'hint' => 'home.latest_webinars_hint', 'view_all' => 'home.view_all'],
                'latest_bundles' => ['title' => 'update.latest_bundles', 'hint' => 'update.latest_bundles_hint', 'view_all' => 'home.view_all'],
                'upcoming_courses' => ['title' => 'update.upcoming_courses', 'hint' => 'update.upcoming_courses_home_section_hint', 'view_all' => 'home.view_all'],
                'best_rates' => ['title' => 'home.best_rates', 'hint' => 'home.best_rates_hint', 'view_all' => 'home.view_all'],
                'best_sellers' => ['title' => 'home.best_sellers', 'hint' => 'home.best_sellers_hint', 'view_all' => 'home.view_all'],
                'discount_classes' => ['title' => 'home.discount_classes', 'hint' => 'home.discount_classes_hint', 'view_all' => 'home.view_all'],
                'free_classes' => ['title' => 'home.free_classes', 'hint' => 'home.free_classes_hint', 'view_all' => 'home.view_all'],
                'store_products' => ['title' => 'update.store_products', 'hint' => 'update.store_products_hint', 'view_all' => 'home.view_all'],
                'testimonials' => ['title' => 'home.testimonials', 'hint' => 'home.testimonials_hint'],
                'subscribes' => ['title' => 'home.subscribe_now', 'hint' => 'home.subscribe_now_hint'],
                'instructors' => ['title' => 'home.instructors', 'hint' => 'home.instructors_hint', 'view_all' => 'home.all_instructors'],
                'organizations' => ['title' => 'home.organizations', 'hint' => 'home.organizations_hint', 'view_all' => 'home.all_organizations'],
                'blog' => ['title' => 'home.blog', 'hint' => 'home.blog_hint', 'view_all' => 'home.all_blog'],
                'faq_section' => ['title' => 'home.faq_section_title'],
            ];
            $st = $iv['section_titles'] ?? [];
        @endphp

        <h5 class="font-16 font-weight-bold">{{ trans('update.home_section_titles') }}</h5>
        <p class="font-12 text-gray">{{ trans('update.home_section_titles_hint') }}</p>

        <div class="accordion mb-3" id="homeSectionTitlesAccordion">
            @foreach($sectionTitleKeys as $sectionKey => $fields)
                <div class="card">
                    <div class="card-header p-2" id="heading_{{ $sectionKey }}">
                        <h6 class="mb-0">
                            <button class="btn btn-link btn-block text-left font-14" type="button"
                                    data-toggle="collapse" data-target="#collapse_{{ $sectionKey }}"
                                    aria-expanded="false" aria-controls="collapse_{{ $sectionKey }}">
                                {{ trans($fields['title']) }}
                            </button>
                        </h6>
                    </div>
                    <div id="collapse_{{ $sectionKey }}" class="collapse" aria-labelledby="heading_{{ $sectionKey }}" data-parent="#homeSectionTitlesAccordion">
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="form-group col-md-{{ !empty($fields['hint']) ? '4' : (!empty($fields['view_all']) ? '6' : '12') }}">
                                    <label>{{ trans('admin/main.title') }}</label>
                                    <input type="text" name="value[section_titles][{{ $sectionKey }}][title]" class="form-control"
                                           value="{{ $st[$sectionKey]['title'] ?? '' }}"
                                           placeholder="{{ trans($fields['title']) }}">
                                </div>
                                @if(!empty($fields['hint']))
                                    <div class="form-group col-md-{{ !empty($fields['view_all']) ? '4' : '8' }}">
                                        <label>{{ trans('public.description') }}</label>
                                        <input type="text" name="value[section_titles][{{ $sectionKey }}][hint]" class="form-control"
                                               value="{{ $st[$sectionKey]['hint'] ?? '' }}"
                                               placeholder="{{ trans($fields['hint']) }}">
                                    </div>
                                @endif
                                @if(!empty($fields['view_all']))
                                    <div class="form-group col-md-{{ !empty($fields['hint']) ? '4' : '6' }}">
                                        <label>{{ trans('home.view_all') }}</label>
                                        <input type="text" name="value[section_titles][{{ $sectionKey }}][view_all]" class="form-control"
                                               value="{{ $st[$sectionKey]['view_all'] ?? '' }}"
                                               placeholder="{{ trans($fields['view_all']) }}">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <hr class="my-4">

        {{-- Trending categories --}}
        <h5 class="font-16 font-weight-bold">{{ trans('admin/main.trend_categories') }}</h5>
        <p class="font-12 text-gray">{{ trans('update.trending_categories_settings_hint') }}</p>

        <div class="row">
            <div class="form-group col-md-4">
                <label>{{ trans('update.trending_categories_layout') }}</label>
                <select name="value[trending_categories][layout]" class="form-control">
                    <option value="rounded" {{ (($iv['trending_categories']['layout'] ?? 'rounded') === 'rounded') ? 'selected' : '' }}>
                        {{ trans('update.trending_categories_layout_rounded') }}
                    </option>
                    <option value="cards" {{ (($iv['trending_categories']['layout'] ?? '') === 'cards') ? 'selected' : '' }}>
                        {{ trans('update.trending_categories_layout_cards') }}
                    </option>
                    <option value="soft_cards" {{ (($iv['trending_categories']['layout'] ?? '') === 'soft_cards') ? 'selected' : '' }}>
                        {{ trans('update.trending_categories_layout_soft_cards') }}
                    </option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('admin/main.title') }}</label>
                <input type="text" name="value[trending_categories][title]" class="form-control"
                       value="{{ $iv['trending_categories']['title'] ?? '' }}"
                       placeholder="{{ trans('home.trending_categories') }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('public.description') }}</label>
                <input type="text" name="value[trending_categories][hint]" class="form-control"
                       value="{{ $iv['trending_categories']['hint'] ?? '' }}"
                       placeholder="{{ trans('home.trending_categories_hint') }}">
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6">
                <label>{{ trans('update.trending_categories_all_button') }}</label>
                <input type="text" name="value[trending_categories][all_button_title]" class="form-control"
                       value="{{ $iv['trending_categories']['all_button_title'] ?? '' }}"
                       placeholder="{{ trans('public.all_categories') }}">
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('admin/main.link') }}</label>
                <input type="text" name="value[trending_categories][all_button_link]" class="form-control"
                       value="{{ $iv['trending_categories']['all_button_link'] ?? '/categories' }}"
                       placeholder="/categories">
            </div>
        </div>

        {{-- Soft floating cards style controls (border radius + shadow) --}}
        <div class="row">
            <div class="form-group col-md-6">
                <label>{{ trans('update.trending_categories_card_border_radius') }}</label>
                <input type="number" min="0" max="48" step="1"
                       name="value[trending_categories][card_border_radius]" class="form-control"
                       value="{{ $iv['trending_categories']['card_border_radius'] ?? 24 }}">
                <div class="text-muted font-12 mt-1">{{ trans('update.trending_categories_card_border_radius_hint') }}</div>
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('update.trending_categories_card_shadow') }}</label>
                <select name="value[trending_categories][card_shadow]" class="form-control">
                    @php $cardShadow = $iv['trending_categories']['card_shadow'] ?? 'soft'; @endphp
                    <option value="none" {{ $cardShadow === 'none' ? 'selected' : '' }}>{{ trans('update.trending_categories_card_shadow_none') }}</option>
                    <option value="soft" {{ $cardShadow === 'soft' ? 'selected' : '' }}>{{ trans('update.trending_categories_card_shadow_soft') }}</option>
                    <option value="medium" {{ $cardShadow === 'medium' ? 'selected' : '' }}>{{ trans('update.trending_categories_card_shadow_medium') }}</option>
                    <option value="strong" {{ $cardShadow === 'strong' ? 'selected' : '' }}>{{ trans('update.trending_categories_card_shadow_strong') }}</option>
                </select>
                <div class="text-muted font-12 mt-1">{{ trans('update.trending_categories_card_shadow_hint') }}</div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Trust badges --}}
        <h5 class="font-16 font-weight-bold">{{ trans('admin/main.trust_badges') }}</h5>
        <p class="font-12 text-gray">{{ trans('update.trust_badges_settings_hint') }}</p>

        <div class="row">
            <div class="form-group col-md-6">
                <label>{{ trans('update.trust_badges_background') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="input-group-text admin-file-manager" data-input="trust_badges_background" data-preview="holder">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </div>
                    <input type="text" name="value[trust_badges][background]" id="trust_badges_background"
                           value="{{ $iv['trust_badges']['background'] ?? '' }}" class="form-control">
                </div>
                <div class="text-muted font-12 mt-1">{{ trans('update.trust_badges_background_hint') }}</div>
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('update.trust_hero_side_image') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="input-group-text admin-file-manager" data-input="trust_hero_side_image" data-preview="holder">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </div>
                    <input type="text" name="value[trust_badges][side_image]" id="trust_hero_side_image"
                           value="{{ $iv['trust_badges']['side_image'] ?? '' }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-4">
                <label>{{ trans('update.trust_hero_chip') }}</label>
                <input type="text" name="value[trust_badges][chip]" class="form-control"
                       value="{{ $iv['trust_badges']['chip'] ?? '' }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('update.trust_hero_title_line1') }}</label>
                <input type="text" name="value[trust_badges][title_line1]" class="form-control"
                       value="{{ $iv['trust_badges']['title_line1'] ?? '' }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('update.trust_hero_title_line2') }}</label>
                <input type="text" name="value[trust_badges][title_line2]" class="form-control"
                       value="{{ $iv['trust_badges']['title_line2'] ?? '' }}">
            </div>
        </div>

        <div class="form-group">
            <label>{{ trans('public.description') }}</label>
            <textarea name="value[trust_badges][description]" rows="3" class="form-control">{{ $iv['trust_badges']['description'] ?? '' }}</textarea>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                <label>{{ trans('update.button') }} 1 - {{ trans('admin/main.title') }}</label>
                <input type="text" name="value[trust_badges][button1][title]" class="form-control"
                       value="{{ $iv['trust_badges']['button1']['title'] ?? '' }}">
            </div>
            <div class="form-group col-md-3">
                <label>{{ trans('update.button') }} 1 - {{ trans('admin/main.link') }}</label>
                <input type="text" name="value[trust_badges][button1][link]" class="form-control"
                       value="{{ $iv['trust_badges']['button1']['link'] ?? '' }}">
            </div>
            <div class="form-group col-md-3">
                <label>{{ trans('update.button') }} 2 - {{ trans('admin/main.title') }}</label>
                <input type="text" name="value[trust_badges][button2][title]" class="form-control"
                       value="{{ $iv['trust_badges']['button2']['title'] ?? '' }}">
            </div>
            <div class="form-group col-md-3">
                <label>{{ trans('update.button') }} 2 - {{ trans('admin/main.link') }}</label>
                <input type="text" name="value[trust_badges][button2][link]" class="form-control"
                       value="{{ $iv['trust_badges']['button2']['link'] ?? '' }}">
            </div>
        </div>

        <h6 class="mt-3">{{ trans('update.trust_badges_items') }}</h6>
        @for($i = 1; $i <= 5; $i++)
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{{ trans('admin/main.title') }} #{{ $i }}</label>
                    <input type="text" name="value[trust_badges][{{ $i }}][title]" class="form-control"
                           value="{{ $iv['trust_badges'][$i]['title'] ?? '' }}">
                </div>
                <div class="form-group col-md-4">
                    <label>{{ trans('update.trust_badge_subtitle') }} #{{ $i }}</label>
                    <input type="text" name="value[trust_badges][{{ $i }}][subtitle]" class="form-control"
                           value="{{ $iv['trust_badges'][$i]['subtitle'] ?? '' }}">
                </div>
                <div class="form-group col-md-4">
                    <label>{{ trans('update.trust_badge_icon') }} #{{ $i }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="trust_badge_image_{{ $i }}" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[trust_badges][{{ $i }}][image]" id="trust_badge_image_{{ $i }}"
                               value="{{ $iv['trust_badges'][$i]['image'] ?? '' }}" class="form-control">
                    </div>
                </div>
            </div>
        @endfor

        <hr class="my-4">

        {{-- Training domains --}}
        <h5 class="font-16 font-weight-bold">{{ trans('admin/main.training_domains') }}</h5>
        <div class="form-group">
            <label>{{ trans('update.training_domains_title') }}</label>
            <input type="text" name="value[training_domains][title]" class="form-control"
                   value="{{ $iv['training_domains']['title'] ?? '' }}"
                   placeholder="{{ trans('update.training_domains_title_default') }}">
        </div>
        <div class="form-group">
            <label>{{ trans('update.training_domains_category_ids') }}</label>
            <input type="text" name="value[training_domains][category_ids]" class="form-control"
                   value="{{ $iv['training_domains']['category_ids'] ?? '' }}"
                   placeholder="1,2,3,4,5,6">
            <div class="text-muted font-12 mt-1">{{ trans('update.training_domains_category_ids_hint') }}</div>
        </div>

        <hr class="my-4">

        {{-- Training modality --}}
        <h5 class="font-16 font-weight-bold">{{ trans('admin/main.training_modality') }}</h5>
        <div class="form-group">
            <label>{{ trans('admin/main.title') }}</label>
            <input type="text" name="value[training_modality][title]" class="form-control"
                   value="{{ $iv['training_modality']['title'] ?? '' }}"
                   placeholder="{{ trans('update.training_modality_title_default') }}">
        </div>
        @foreach(['in_person' => trans('update.modality_in_person'), 'online' => trans('update.modality_online')] as $key => $label)
            <h6 class="mt-3">{{ $label }}</h6>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{{ trans('admin/main.title') }}</label>
                    <input type="text" name="value[training_modality][{{ $key }}][title]" class="form-control"
                           value="{{ $iv['training_modality'][$key]['title'] ?? '' }}">
                </div>
                <div class="form-group col-md-4">
                    <label>{{ trans('admin/main.link') }}</label>
                    <input type="text" name="value[training_modality][{{ $key }}][link]" class="form-control"
                           value="{{ $iv['training_modality'][$key]['link'] ?? '' }}" placeholder="/classes">
                </div>
                <div class="form-group col-md-4">
                    <label>{{ trans('admin/main.image') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="training_modality_{{ $key }}_image" data-preview="holder">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                        <input type="text" name="value[training_modality][{{ $key }}][image]" id="training_modality_{{ $key }}_image"
                               value="{{ $iv['training_modality'][$key]['image'] ?? '' }}" class="form-control">
                    </div>
                    <div class="text-muted font-12 mt-1">{{ trans('update.modality_card_image_hint') }}</div>
                </div>
            </div>

            <p class="font-12 text-gray mb-2">{{ trans('update.modality_features_hint') }}</p>
            @for($i = 1; $i <= 3; $i++)
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>{{ trans('update.modality_feature_title') }} #{{ $i }}</label>
                        <input type="text" name="value[training_modality][{{ $key }}][features][{{ $i }}][title]" class="form-control"
                               value="{{ $iv['training_modality'][$key]['features'][$i]['title'] ?? '' }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ trans('update.modality_feature_icon') }} #{{ $i }}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button type="button" class="input-group-text admin-file-manager" data-input="training_modality_{{ $key }}_feature_{{ $i }}" data-preview="holder">
                                    <i class="fa fa-chevron-up"></i>
                                </button>
                            </div>
                            <input type="text" name="value[training_modality][{{ $key }}][features][{{ $i }}][image]" id="training_modality_{{ $key }}_feature_{{ $i }}"
                                   value="{{ $iv['training_modality'][$key]['features'][$i]['image'] ?? '' }}" class="form-control">
                        </div>
                    </div>
                </div>
            @endfor
        @endforeach

        <hr class="my-4">

        {{-- Why Albyan --}}
        <h5 class="font-16 font-weight-bold">{{ trans('admin/main.why_albyan') }}</h5>
        <div class="form-group">
            <label>{{ trans('admin/main.title') }}</label>
            <input type="text" name="value[why_albyan][title]" class="form-control"
                   value="{{ $iv['why_albyan']['title'] ?? '' }}"
                   placeholder="{{ trans('update.why_albyan_title_default') }}">
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label>{{ trans('update.why_albyan_background') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="input-group-text admin-file-manager" data-input="why_albyan_background" data-preview="holder">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </div>
                    <input type="text" name="value[why_albyan][background]" id="why_albyan_background"
                           value="{{ $iv['why_albyan']['background'] ?? '' }}" class="form-control">
                </div>
                <div class="text-muted font-12 mt-1">{{ trans('update.why_albyan_background_hint') }}</div>
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('update.why_albyan_overlay_opacity') }}</label>
                <input type="number" min="0" max="100" step="1"
                       name="value[why_albyan][overlay_opacity]" class="form-control"
                       value="{{ $iv['why_albyan']['overlay_opacity'] ?? 85 }}">
                <div class="text-muted font-12 mt-1">{{ trans('update.why_albyan_overlay_opacity_hint') }}</div>
            </div>
        </div>
        <div class="form-group">
            <label>{{ trans('update.why_albyan_side_image') }}</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text admin-file-manager" data-input="why_albyan_image" data-preview="holder">
                        <i class="fa fa-chevron-up"></i>
                    </button>
                </div>
                <input type="text" name="value[why_albyan][image]" id="why_albyan_image"
                       value="{{ $iv['why_albyan']['image'] ?? '' }}" class="form-control">
            </div>
            <div class="text-muted font-12 mt-1">{{ trans('update.why_albyan_image_hint') }}</div>
        </div>
        <div class="form-group">
            <label>{{ trans('update.why_albyan_items') }}</label>
            <textarea name="value[why_albyan][items]" rows="8" class="form-control"
                      placeholder="{{ trans('update.why_albyan_items_hint') }}">{{ $iv['why_albyan']['items'] ?? '' }}</textarea>
        </div>

        <hr class="my-4">

        {{-- Help CTA band --}}
        <h5 class="font-16 font-weight-bold">{{ trans('admin/main.help_cta_band') }}</h5>
        <div class="form-group">
            <label>{{ trans('admin/main.title') }}</label>
            <input type="text" name="value[help_cta_band][title]" class="form-control"
                   value="{{ $iv['help_cta_band']['title'] ?? '' }}"
                   placeholder="{{ trans('update.help_cta_band_title_default') }}">
        </div>
        <div class="row">
            <div class="form-group col-md-4">
                <label>{{ trans('update.help_cta_whatsapp') }}</label>
                <input type="text" name="value[help_cta_band][whatsapp]" class="form-control"
                       value="{{ $iv['help_cta_band']['whatsapp'] ?? '' }}" placeholder="https://wa.me/971...">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('public.phone') }}</label>
                <input type="text" name="value[help_cta_band][phone]" class="form-control"
                       value="{{ $iv['help_cta_band']['phone'] ?? '' }}" placeholder="+971569001020">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('update.help_cta_hours') }}</label>
                <input type="text" name="value[help_cta_band][hours]" class="form-control"
                       value="{{ $iv['help_cta_band']['hours'] ?? '' }}">
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('update.help_cta_map_url') }}</label>
                <input type="text" name="value[help_cta_band][map_url]" class="form-control"
                       value="{{ $iv['help_cta_band']['map_url'] ?? '' }}">
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('update.help_cta_classes_url') }}</label>
                <input type="text" name="value[help_cta_band][classes_url]" class="form-control"
                       value="{{ $iv['help_cta_band']['classes_url'] ?? '/classes' }}">
            </div>
        </div>

        {{-- Help CTA button labels (translatable; empty uses lang defaults) --}}
        <h6 class="mt-3">{{ trans('update.help_cta_button_labels') }}</h6>
        <div class="row">
            <div class="form-group col-md-4">
                <label>{{ trans('site.contact_training_advisor') }}</label>
                <input type="text" name="value[help_cta_band][advisor_button]" class="form-control"
                       value="{{ $iv['help_cta_band']['advisor_button'] ?? '' }}"
                       placeholder="{{ trans('site.contact_training_advisor') }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('site.explore_courses_diplomas') }}</label>
                <input type="text" name="value[help_cta_band][classes_button]" class="form-control"
                       value="{{ $iv['help_cta_band']['classes_button'] ?? '' }}"
                       placeholder="{{ trans('site.explore_courses_diplomas') }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('update.help_cta_whatsapp') }}</label>
                <input type="text" name="value[help_cta_band][whatsapp_button]" class="form-control"
                       value="{{ $iv['help_cta_band']['whatsapp_button'] ?? '' }}"
                       placeholder="{{ trans('update.help_cta_whatsapp') }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('update.help_cta_call_us') }}</label>
                <input type="text" name="value[help_cta_band][call_button]" class="form-control"
                       value="{{ $iv['help_cta_band']['call_button'] ?? '' }}"
                       placeholder="{{ trans('update.help_cta_call_us') }}">
            </div>
            <div class="form-group col-md-4">
                <label>{{ trans('update.help_cta_map') }}</label>
                <input type="text" name="value[help_cta_band][map_button]" class="form-control"
                       value="{{ $iv['help_cta_band']['map_button'] ?? '' }}"
                       placeholder="{{ trans('update.help_cta_map') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-2">{{ trans('admin/main.save_change') }}</button>
    </form>
</div>
