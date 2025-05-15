<section class="section">
    <div class="section-header">
        <h1>{{ $pageTitle }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
            </div>
            <div class="breadcrumb-item">{{ $pageTitle }}</div>
        </div>
    </div>
</section>