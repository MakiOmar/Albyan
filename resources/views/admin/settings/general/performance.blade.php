@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }

    $homepageCacheMode = (!empty($itemValue['homepage_cache_mode'])) ? $itemValue['homepage_cache_mode'] : 'cached';
    $imageLazyLoadMode = (!empty($itemValue['image_lazy_load_mode'])) ? $itemValue['image_lazy_load_mode'] : 'viewport';
@endphp

{{-- Performance: homepage cache + image lazy-load strategy (PageSpeed tuning, no design changes). --}}
<div class="tab-pane mt-3 fade" id="performance" role="tabpanel" aria-labelledby="performance-tab">
    <div class="row">
        <div class="col-12 col-md-8">
            <form action="{{ getAdminPanelUrl() }}/settings/{{ \App\Models\Setting::$performanceSettingsName }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="page" value="general">
                <input type="hidden" name="name" value="{{ \App\Models\Setting::$performanceSettingsName }}">

                <div class="mb-5">
                    <h5>{{ trans('update.homepage_cache_mode') }}</h5>
                    <p class="font-12 text-gray mb-3">{{ trans('update.homepage_cache_mode_hint') }}</p>

                    <div class="form-group">
                        <select class="form-control" name="value[homepage_cache_mode]" id="homepage_cache_mode">
                            <option value="cached" {{ $homepageCacheMode === 'cached' ? 'selected' : '' }}>{{ trans('update.homepage_cache_mode_cached') }}</option>
                            <option value="original" {{ $homepageCacheMode === 'original' ? 'selected' : '' }}>{{ trans('update.homepage_cache_mode_original') }}</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <h5>{{ trans('update.image_lazy_load_mode') }}</h5>
                    <p class="font-12 text-gray mb-3">{{ trans('update.image_lazy_load_mode_hint') }}</p>

                    <div class="form-group">
                        <select class="form-control" name="value[image_lazy_load_mode]" id="image_lazy_load_mode">
                            <option value="viewport" {{ $imageLazyLoadMode === 'viewport' ? 'selected' : '' }}>{{ trans('update.image_lazy_load_mode_viewport') }}</option>
                            <option value="interaction" {{ $imageLazyLoadMode === 'interaction' ? 'selected' : '' }}>{{ trans('update.image_lazy_load_mode_interaction') }}</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
            </form>
        </div>
    </div>
</div>
