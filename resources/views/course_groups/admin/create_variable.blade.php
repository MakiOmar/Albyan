@extends('admin.layouts.app')

@push('libraries_top')

@endpush
@push('styles_top')
<style>
    #groupsModalContent .fade{
        opacity: 1;
    }
</style>
@endpush
@php
$values = !empty($setting) ? $setting->value : null;

if (!empty($values)) {
    $values = json_decode($values, true);
}
$isEdit = isset($group);
$meetingJson = $isEdit ? json_decode($group->meeting_json, true) : null;
@endphp
@section('content')
<div class="container">
    <h1>Create a New Group</h1>

    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <!-- Nav Tabs -->
    @include('course_groups.admin.partials.forms_nav')

    @include('course_groups.admin.variable_group_form', compact('isEdit', 'values', 'meetingJson'))
</div>
<!-- Modal -->
@include('course_groups.admin.partials.instructor_groups_modal')

@endsection

@include('course_groups.admin.partials.create_group_scripts', compact('isEdit'))

