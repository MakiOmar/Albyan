@extends('admin.layouts.app')
@php
$pageTitle = 'Webinars with Course Groups';
@endphp
@section('content')
<section class="section">
    @include('partials.admin_page_title')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <!-- Table for webinars with course groups -->
            <table class="table table-striped font-14 ">
                <thead>
                    <tr>
                        <th>Webinar Title</th>
                        <th>Total Groups</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($webinars as $webinar)
                        <tr>
                            <td>
                                <a href="{{ route('course-group.manage', $webinar->id) }}">
                                    {{ $webinar->title }}
                                </a>
                            </td>
                            <td>{{ $webinar->groups->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No webinars with course groups found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</section>
@include('course_groups.admin.partials.compensatory_session_model');
@endsection
@include('course_groups.admin.partials.compensatory_session_script');