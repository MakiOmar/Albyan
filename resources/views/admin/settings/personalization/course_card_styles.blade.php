@if($authUser->can('admin_personalization'))

    <section class="mt-30">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="section-title after-line">{{ trans('admin/main.course_card_styles') }}</h2>
        </div>

        <div class="row mt-20">
            <div class="col-12">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        @include('admin.settings.personalization.course_card_styles.dark_overlay_style')
                    </div>
                    <div class="col-12 col-lg-6">
                        @include('admin.settings.personalization.course_card_styles.gray_hover_style')
                    </div>
                </div>
            </div>
        </div>
    </section>

@endif
