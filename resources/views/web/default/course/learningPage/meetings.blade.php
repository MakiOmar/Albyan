<div class="content-tab p-15 pb-50">
    @foreach ($groups as $group)
    @include('web.default.course.learningPage.components.group_meetings', ['group' => $group])
    @endforeach
</div>
