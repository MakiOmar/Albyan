@extends('admin.layouts.app')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        {{ $errors->first() }}
        <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@php
$pageTitle = 'تفاصيل المجموعة';
@endphp
<div class="container">
    @include('partials.admin_page_title')
    @include('course_groups.admin.partials.group_item', ['group' => $group]);
</div>
@include('course_groups.admin.partials.compensatory_session_model');
@endsection
@include('course_groups.admin.partials.compensatory_session_script');