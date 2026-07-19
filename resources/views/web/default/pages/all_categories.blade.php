@extends(getTemplate().'.layouts.app')

@section('content')
    {{-- All categories hub page --}}
    <section class="site-top-banner search-top-banner opacity-04 position-relative">
        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="{{ getPageBackgroundSettings('categories') }}" class="img-cover" alt="{{ trans('update.all_categories_page_title') }}" width="1200" height="400"/>

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <h1 class="text-white font-30 mb-15">{{ trans('update.all_categories_page_title') }}</h1>
                    <p class="text-white font-14 mb-0">{{ trans('update.all_categories_page_hint') }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-40 mb-50">
        @if($categories->isEmpty())
            <p class="text-center text-gray">{{ trans('update.training_domains_empty') }}</p>
        @else
            @foreach($categories as $category)
                <div class="mb-40">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-15">
                        <div>
                            <h2 class="font-24 text-dark-blue mb-5">
                                <a href="{{ $category->getUrl() }}" class="text-dark-blue">{{ $category->title }}</a>
                            </h2>
                            @if(!empty($category->description))
                                <p class="font-14 text-gray mb-5">{{ $category->description }}</p>
                            @endif
                            <span class="font-12 text-gray">
                                {{ trans_choice('update.programs_count', (int) $category->programs_count, ['count' => (int) $category->programs_count]) }}
                            </span>
                        </div>
                        <a href="{{ $category->getUrl() }}" class="btn btn-sm btn-border-white mt-10 mt-md-0">{{ trans('home.view_all') }}</a>
                    </div>

                    @if(!empty($category->subCategories) && $category->subCategories->count())
                        <div class="row">
                            @foreach($category->subCategories as $subCategory)
                                <div class="col-6 col-md-4 col-lg-3 mt-15">
                                    <a href="{{ $subCategory->getUrl() }}" class="d-block border rounded p-15 text-dark h-100">
                                        <span class="font-14 font-weight-bold">{{ $subCategory->title }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endsection
