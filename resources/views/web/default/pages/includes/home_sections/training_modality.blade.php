{{-- In-person vs online modality cards --}}
@php
    $modality = getHomeContentBlocksSettings('training_modality') ?? [];
    $modalityTitle = trim((string) ($modality['title'] ?? ''));
    if ($modalityTitle === '') {
        $modalityTitle = trans('update.training_modality_title_default');
    }
    $cards = [
        'in_person' => [
            'title' => trim((string) ($modality['in_person']['title'] ?? '')) ?: trans('update.modality_in_person'),
            'description' => trim((string) ($modality['in_person']['description'] ?? '')) ?: trans('update.modality_in_person_desc'),
            'link' => trim((string) ($modality['in_person']['link'] ?? '')) ?: '/classes',
        ],
        'online' => [
            'title' => trim((string) ($modality['online']['title'] ?? '')) ?: trans('update.modality_online'),
            'description' => trim((string) ($modality['online']['description'] ?? '')) ?: trans('update.modality_online_desc'),
            'link' => trim((string) ($modality['online']['link'] ?? '')) ?: '/classes',
        ],
    ];
@endphp

<section class="home-sections container mt-40">
    <h2 class="section-title text-center">{{ $modalityTitle }}</h2>
    <div class="row mt-30 justify-content-center">
        @foreach($cards as $card)
            <div class="col-12 col-md-6 mt-20">
                <a href="{{ $card['link'] }}" class="d-block border rounded p-30 h-100 text-dark">
                    <h3 class="font-20 font-weight-bold">{{ $card['title'] }}</h3>
                    <p class="font-14 text-gray mt-15 mb-0">{!! nl2br(e($card['description'])) !!}</p>
                </a>
            </div>
        @endforeach
    </div>
</section>
