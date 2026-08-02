{{-- WordPress featured blog cards (zskeleton REST API via LEAD_GENERATION_BASE_URL) --}}
@php
    $wpBlogSection = $wpBlogSection ?? ['enabled' => false, 'title' => '', 'archive_url' => ''];
    $wpBlogPosts = $wpBlogPosts ?? collect();
    $wpBlogEnabled = !empty($wpBlogSection['enabled']) && $wpBlogPosts->isNotEmpty();
    $wpBlogTitle = trim((string) ($wpBlogSection['title'] ?? ''));
    if ($wpBlogTitle === '') {
        $wpBlogTitle = trans('update.wp_blog_section_title');
    }
    $wpBlogArchiveUrl = trim((string) ($wpBlogSection['archive_url'] ?? ''));
@endphp

@if($wpBlogEnabled)
<section class="home-sections container mt-40" aria-labelledby="wp-blog-section-heading">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            {{-- Section heading: prefer API title, fall back to translation --}}
            <h2 id="wp-blog-section-heading" class="section-title">{{ $wpBlogTitle }}</h2>
            <p class="section-hint">{{ trans('update.wp_blog_section_hint') }}</p>
        </div>

        @if($wpBlogArchiveUrl !== '')
            <a href="{{ $wpBlogArchiveUrl }}" class="btn btn-border-white">
                {{ trans('update.wp_blog_section_all') }}
            </a>
        @endif
    </div>

    <div class="row mt-35">
        @foreach($wpBlogPosts as $post)
            @php
                $postTitle = (string) ($post['title'] ?? '');
                $postUrl = (string) ($post['permalink'] ?? '#');
                $postExcerpt = (string) ($post['excerpt'] ?? '');
                $postDate = (string) ($post['date_display'] ?? '');
                $thumbUrl = (string) ($post['thumbnail_url'] ?? '');
                $thumbAlt = (string) ($post['thumbnail_alt'] ?? $postTitle);
                $isLocked = !empty($post['is_locked']);
                $membersLabel = (string) ($post['members_only_label'] ?? trans('update.wp_blog_members_only'));
                $category = is_array($post['category'] ?? null) ? $post['category'] : null;
                $showViews = !empty($post['show_views']);
                $viewsDisplay = (string) ($post['views_display'] ?? '0');
            @endphp

            <div class="col-12 col-md-4 mt-20 mt-lg-0">
                {{-- Reuse existing homepage blog card look --}}
                <article class="blog-grid-card h-100">
                    <div class="blog-grid-image">
                        @if($isLocked)
                            <div class="badges-lists">
                                <span class="badge bg-danger">{{ $membersLabel }}</span>
                            </div>
                        @endif

                        <a href="{{ $postUrl }}" aria-label="{{ $postTitle }}">
                            {{-- Placeholder + data-src so deferred image-lazy-loader controls the fetch --}}
                            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                 data-src="{{ $thumbUrl }}"
                                 class="img-cover"
                                 alt="{{ $thumbAlt }}"
                                 loading="lazy"
                                 decoding="async"
                                 width="768"
                                 height="432">
                        </a>

                        @if($postDate !== '')
                            <span class="badge created-at d-flex align-items-center">
                                <i data-feather="calendar" width="20" height="20" class="mr-5"></i>
                                <span>{{ $postDate }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="blog-grid-detail">
                        @if(!empty($category['name']))
                            <p class="blog-grid-category mb-5 font-12 text-gray">
                                @if(!empty($category['url']))
                                    <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>
                                @else
                                    {{ $category['name'] }}
                                @endif
                            </p>
                        @endif

                        <a href="{{ $postUrl }}">
                            <h3 class="blog-grid-title mt-10">{{ $postTitle }}</h3>
                        </a>

                        @if($postExcerpt !== '')
                            <div class="mt-20 blog-grid-desc">{{ \Illuminate\Support\Str::limit(strip_tags($postExcerpt), 160) }}</div>
                        @endif

                        <div class="blog-grid-footer d-flex align-items-center justify-content-between mt-15">
                            @if($showViews)
                                <span class="d-flex align-items-center">
                                    <i data-feather="eye" width="20" height="20" class=""></i>
                                    <span class="ml-5">{{ $viewsDisplay }}</span>
                                </span>
                            @else
                                <span></span>
                            @endif

                            @if($isLocked)
                                <span class="d-flex align-items-center text-danger">
                                    <i data-feather="lock" width="18" height="18" class=""></i>
                                    <span class="ml-5 font-12">{{ $membersLabel }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</section>
@endif
