{{-- WordPress API blog stub — empty until WP REST fetch is wired --}}
<section class="home-sections container mt-40">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="section-title">{{ trans('update.wp_blog_section_title') }}</h2>
            <p class="section-hint">{{ trans('update.wp_blog_section_hint') }}</p>
        </div>
    </div>

    @php
        $wpBlogPosts = $wpBlogPosts ?? collect();
    @endphp

    @if($wpBlogPosts->isEmpty())
        <p class="text-gray font-14 mt-20 mb-0">{{ trans('update.wp_blog_section_empty') }}</p>
    @else
        <div class="row mt-35">
            @foreach($wpBlogPosts as $post)
                <div class="col-12 col-md-4 mt-20">
                    {{-- Reserved for WP post cards --}}
                </div>
            @endforeach
        </div>
    @endif
</section>
