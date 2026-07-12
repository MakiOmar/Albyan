{{-- Related courses from the same category (crawlable links for SEO) --}}
@if(!empty($relatedCategoryCourses) && $relatedCategoryCourses->count())
    <aside class="rounded-lg shadow-sm mt-35 px-25 py-20" aria-labelledby="relatedCategoryCoursesHeading">
        {{-- Section heading for users and crawlers --}}
        <h3 id="relatedCategoryCoursesHeading" class="sidebar-title font-16 text-secondary font-weight-bold">
            {{ trans('update.related_courses') }}
            @if(!empty($course->category))
                <span class="d-block font-12 font-weight-500 text-gray mt-5">{{ $course->category->title }}</span>
            @endif
        </h3>

        <nav class="mt-15" aria-label="{{ trans('update.related_courses') }}">
            <ul class="list-unstyled m-0 p-0">
                @foreach($relatedCategoryCourses as $relatedCourse)
                    <li class="{{ !$loop->last ? 'mb-15 pb-15 border-bottom' : '' }}">
                        <a href="{{ $relatedCourse->getUrl() }}"
                           class="d-flex align-items-start text-decoration-none related-category-course-link"
                           title="{{ $relatedCourse->title }}">
                            <img src="{{ $relatedCourse->getImage() ?: '/assets/default/img/placeholder.svg' }}"
                                 alt="{{ $relatedCourse->title }}"
                                 width="64"
                                 height="48"
                                 loading="lazy"
                                 class="rounded related-category-course-thumb flex-shrink-0" />
                            <span class="ml-10">
                                <span class="d-block font-14 font-weight-bold text-dark-blue related-category-course-title">
                                    {{ clean($relatedCourse->title, 'title') }}
                                </span>
                                @if(!empty($relatedCourse->teacher))
                                    <span class="d-block font-12 text-gray mt-5">{{ $relatedCourse->teacher->full_name }}</span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        @if(!empty($course->category))
            <a href="{{ $course->category->getUrl() }}" class="d-inline-block mt-15 font-13 font-weight-500 text-primary">
                {{ trans('home.view_all') }} → {{ $course->category->title }}
            </a>
        @endif
    </aside>

    <style>
        /* Compact related-course thumbnails in the course sidebar */
        .related-category-course-thumb {
            width: 64px;
            height: 48px;
            object-fit: cover;
        }
        .related-category-course-title {
            line-height: 1.35;
        }
        .related-category-course-link:hover .related-category-course-title {
            color: #01477d;
        }
    </style>
@endif
