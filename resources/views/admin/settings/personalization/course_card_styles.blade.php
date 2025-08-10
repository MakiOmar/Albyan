@if($authUser->can('admin_personalization'))

    <div class="mt-3">
        <form action="{{ getAdminPanelUrl() }}/settings/main" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="name" value="course_card_styles">
            <input type="hidden" name="page" value="personalization">

            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            @include('admin.settings.personalization.course_card_styles.dark_overlay_style')
                        </div>
                        <div class="col-12 col-lg-4">
                            @include('admin.settings.personalization.course_card_styles.white_overlay_style')
                        </div>
                        <div class="col-12 col-lg-4">
                            @include('admin.settings.personalization.course_card_styles.gray_hover_style')
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-20">
                <div class="col-12">
                    <button type="submit" class="btn btn-success">{{ trans('admin/main.save_change') }}</button>
                </div>
            </div>
        </form>
    </div>

@endif
