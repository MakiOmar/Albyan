{{-- Why choose Albyan benefits list --}}
@php
    $why = getHomeContentBlocksSettings('why_albyan') ?? [];
    $whyTitle = trim((string) ($why['title'] ?? ''));
    if ($whyTitle === '') {
        $whyTitle = trans('update.why_albyan_title_default');
    }
    $itemsRaw = trim((string) ($why['items'] ?? ''));
    if ($itemsRaw !== '') {
        $whyItems = preg_split("/\r\n|\n|\r/", $itemsRaw);
        $whyItems = array_values(array_filter(array_map('trim', $whyItems)));
    } else {
        $whyItems = trans('update.why_albyan_default_items');
        if (!is_array($whyItems)) {
            $whyItems = [];
        }
    }
@endphp

<section class="home-sections container mt-40">
    <h2 class="section-title text-center">{{ $whyTitle }}</h2>
    <ul class="mt-30 list-unstyled row">
        @foreach($whyItems as $item)
            <li class="col-12 col-md-6 mt-15 d-flex align-items-start">
                <span class="text-primary mr-10 font-weight-bold">•</span>
                <span class="font-14">{{ $item }}</span>
            </li>
        @endforeach
    </ul>
</section>
