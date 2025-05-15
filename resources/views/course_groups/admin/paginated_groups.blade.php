@extends('admin.layouts.app')
@php
$pageTitle = 'قائمة المجموعات';
@endphp
@section('content')
<section class="section">
    <div class="container">
        @include('partials.admin_page_title')
        <!-- Nav Tabs -->
        @include('course_groups.admin.partials.forms_nav')
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Webinar</th>
                            <th>Members</th>
                            <th>Start Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse ($groups as $group)
                            @include('course_groups.admin.partials.group_table_item', ['group' => $group,'screen' => $screen])
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">No groups found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $groups->links() }} {{-- روابط التصفح --}}
        </div>
    </div>
</section>
@endsection
