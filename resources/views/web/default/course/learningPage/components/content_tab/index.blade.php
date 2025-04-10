<div class="content-tab p-15 pb-50">
{{--
    @if(
        (empty($sessionsWithoutChapter) or !count($sessionsWithoutChapter)) and
        (empty($textLessonsWithoutChapter) or !count($textLessonsWithoutChapter)) and
        (empty($filesWithoutChapter) or !count($filesWithoutChapter)) and
        (empty($course->chapters) or !count($course->chapters))
    )
        <div class="learning-page-forum-empty d-flex align-items-center justify-content-center flex-column">
            <div class="learning-page-forum-empty-icon d-flex align-items-center justify-content-center">
                <img src="/assets/default/img/learning/content-empty.svg" class="img-fluid" alt="">
            </div>

            <div class="d-flex align-items-center flex-column mt-10 text-center">
                <h3 class="font-20 font-weight-bold text-dark-blue text-center">{{ trans('update.learning_page_empty_content_title') }}</h3>
                <p class="font-14 font-weight-500 text-gray mt-5 text-center">{{ trans('update.learning_page_empty_content_hint') }}</p>
            </div>
        </div>
    @else
        @if(!empty($sessionsWithoutChapter) and count($sessionsWithoutChapter))
            @foreach($sessionsWithoutChapter as $session)
                @include('web.default.course.learningPage.components.content_tab.content',['item' => $session, 'type' => \App\Models\WebinarChapter::$chapterSession])
            @endforeach
        @endif

        @if(!empty($textLessonsWithoutChapter) and count($textLessonsWithoutChapter))
            @foreach($textLessonsWithoutChapter as $textLesson)
                @include('web.default.course.learningPage.components.content_tab.content',['item' => $textLesson, 'type' => \App\Models\WebinarChapter::$chapterTextLesson])
            @endforeach
        @endif

        @if(!empty($filesWithoutChapter) and count($filesWithoutChapter))
            @foreach($filesWithoutChapter as $file)
                @include('web.default.course.learningPage.components.content_tab.content',['item' => $file, 'type' => \App\Models\WebinarChapter::$chapterFile])
            @endforeach
        @endif

        @if(!empty($course->chapters) and count($course->chapters))
            @include('web.default.course.learningPage.components.content_tab.chapter')
        @endif
        
    @endif
    --}}
        @php
        
        @endphp
        @foreach($groups as $group)
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <strong>المجموعة رقم: {{ $group->id }}</strong>
            </div>
            <div class="card-body">

                @if($group->instructorFiles->isEmpty())
                    <p class="text-muted">لا توجد ملفات مرفقة من المدرّب.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>التحميل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->instructorFiles as $file)
                                <tr>
                                    <td>{{ $file->title }}</td>
                                    <td>
                                        <a href="{{ asset($file->path) }}" target="_blank" class="btn btn-sm btn-primary">
                                            تحميل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
    @endforeach
</div>

