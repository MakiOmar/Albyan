@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h3>تفاصيل المجموعة</h3>
    @include('course_groups.admin.partials.group_item', ['group' => $group]);
</div>
@endsection
