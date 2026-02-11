@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('admin/main.site_faqs') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            @can('admin_site_faqs_create')
                                <a href="{{ getAdminPanelUrl() }}/site-faqs/create" class="btn btn-primary">{{ trans('admin/main.add_new') }}</a>
                            @endcan
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('public.faq') }} ({{ trans('admin/main.title') }})</th>
                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>
                                    @foreach($siteFaqs as $siteFaq)
                                        <tr>
                                            <td>{{ $siteFaq->id }}</td>
                                            <td>{{ $siteFaq->title }}</td>
                                            <td class="text-center">
                                                @if($siteFaq->status == 'active')
                                                    <span class="text-success">{{ trans('admin/main.active') }}</span>
                                                @else
                                                    <span class="text-warning">{{ trans('admin/main.disable') }}</span>
                                                @endif
                                            </td>
                                            <td width="150px">
                                                @can('admin_site_faqs_edit')
                                                    <a href="{{ getAdminPanelUrl() }}/site-faqs/{{ $siteFaq->id }}/edit" class="btn-transparent text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('admin_site_faqs_delete')
                                                    @include('admin.includes.delete_button', ['url' => getAdminPanelUrl().'/site-faqs/'.$siteFaq->id.'/delete', 'btnClass' => ''])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $siteFaqs->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
