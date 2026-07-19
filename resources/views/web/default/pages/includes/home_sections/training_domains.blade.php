{{-- Curated training domains (6–8 category cards) --}}
@php
    $domainsSettings = getHomeContentBlocksSettings('training_domains') ?? [];
    $domainsTitle = trim((string) ($domainsSettings['title'] ?? ''));
    if ($domainsTitle === '') {
        $domainsTitle = trans('update.training_domains_title_default');
    }
    $domainCategories = $trainingDomainCategories ?? collect();
@endphp

<section class="home-sections container mt-40">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-20">
        <h2 class="section-title mb-0">{{ $domainsTitle }}</h2>
        <a href="{{ url('/categories') }}" class="btn btn-border-white mt-10 mt-md-0">{{ trans('public.all_categories') }}</a>
    </div>

    @if($domainCategories->isEmpty())
        <p class="text-gray font-14">{{ trans('update.training_domains_empty') }}</p>
    @else
        <div class="row">
            @foreach($domainCategories as $category)
                <div class="col-12 col-md-6 col-lg-3 mt-20">
                    <a href="{{ $category->getUrl() }}" class="d-block border rounded p-20 h-100 text-dark">
                        <h3 class="font-16 font-weight-bold">{{ $category->title }}</h3>
                        @if(!empty($category->home_domain_description))
                            <p class="font-12 text-gray mt-10 mb-10">{{ $category->home_domain_description }}</p>
                        @endif
                        <span class="font-12 text-primary">
                            {{ trans_choice('update.programs_count', (int) $category->webinars_count, ['count' => (int) $category->webinars_count]) }}
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</section>
