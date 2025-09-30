@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $role->caption }} {{ trans('admin/main.list') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a>{{ $role->caption }}</a></div>
                <div class="breadcrumb-item"><a href="#">{{ trans('admin/main.users_list') }}</a></div>
            </div>
        </div>
    </section>

    <div class="section-body">
        <section class="card">
            <div class="card-body">
                <form method="get" class="mb-0">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.search') }}</label>
                                <input name="full_name" type="text" class="form-control" value="{{ request()->get('full_name') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                <div class="input-group">
                                    <input type="date" id="from" class="text-center form-control" name="from" value="{{ request()->get('from') }}" placeholder="Start Date">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                <div class="input-group">
                                    <input type="date" id="to" class="text-center form-control" name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">{{ trans('admin/main.show_results') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped font-14">
                        <thead>
                        <tr>
                            <th class="text-left">{{ trans('admin/main.id') }}</th>
                            <th class="text-left">{{ trans('admin/main.name') }}</th>
                            <th class="text-left">{{ trans('admin/main.email') }}</th>
                            <th class="text-left">{{ trans('admin/main.mobile') }}</th>
                            <th class="text-left">{{ trans('admin/main.register_date') }}</th>
                            <th>{{ trans('admin/main.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="text-left">{{ $user->id }}</td>
                                <td class="text-left">{{ $user->full_name }}</td>
                                <td class="text-left">{{ $user->email }}</td>
                                <td class="text-left">{{ $user->mobile }}</td>
                                <td class="text-left">{{ dateTimeFormat($user->created_at, 'j M Y') }}</td>
                                <td>
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="btn-transparent btn-sm text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('public.edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $users->appends(request()->input())->links('vendor.pagination.bootstrap-4') }}
            </div>
        </section>
    </div>
@endsection
