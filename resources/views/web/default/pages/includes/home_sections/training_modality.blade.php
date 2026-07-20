{{-- Training modality: fancy cards with image under title + trust-badge style features --}}
@php
    $modality = getHomeContentBlocksSettings('training_modality') ?? [];
    $modalityTitle = trim((string) ($modality['title'] ?? ''));
    if ($modalityTitle === '') {
        $modalityTitle = trans('update.training_modality_title_default');
    }

    $defaultIcons = [
        'in_person' => ['map-pin', 'users', 'calendar'],
        'online' => ['video', 'monitor', 'headphones'],
    ];

    $buildFeatures = function (array $cardData, string $defaultDescKey, array $fallbackIcons): array {
        $features = [];
        for ($i = 1; $i <= 3; $i++) {
            $title = trim((string) ($cardData['features'][$i]['title'] ?? ''));
            $image = trim((string) ($cardData['features'][$i]['image'] ?? ''));
            if ($title !== '') {
                $features[] = [
                    'title' => $title,
                    'image' => $image,
                    'icon' => $fallbackIcons[$i - 1] ?? 'check-circle',
                ];
            }
        }

        // Fallback: split description lines into badge items
        if (empty($features)) {
            $description = trim((string) ($cardData['description'] ?? ''));
            if ($description === '') {
                $description = trans($defaultDescKey);
            }
            $lines = preg_split("/\r\n|\n|\r/", $description) ?: [];
            foreach (array_values(array_filter(array_map('trim', $lines))) as $index => $line) {
                $features[] = [
                    'title' => $line,
                    'image' => '',
                    'icon' => $fallbackIcons[$index] ?? 'check-circle',
                ];
            }
        }

        return $features;
    };

    $cards = [
        'in_person' => [
            'title' => trim((string) ($modality['in_person']['title'] ?? '')) ?: trans('update.modality_in_person'),
            'link' => trim((string) ($modality['in_person']['link'] ?? '')) ?: '/classes',
            'image' => trim((string) ($modality['in_person']['image'] ?? '')),
            'features' => $buildFeatures($modality['in_person'] ?? [], 'update.modality_in_person_desc', $defaultIcons['in_person']),
        ],
        'online' => [
            'title' => trim((string) ($modality['online']['title'] ?? '')) ?: trans('update.modality_online'),
            'link' => trim((string) ($modality['online']['link'] ?? '')) ?: '/classes',
            'image' => trim((string) ($modality['online']['image'] ?? '')),
            'features' => $buildFeatures($modality['online'] ?? [], 'update.modality_online_desc', $defaultIcons['online']),
        ],
    ];
@endphp

<section class="home-sections container training-modality-section">
    <h2 class="section-title text-center">{{ $modalityTitle }}</h2>

    <div class="row mt-40 justify-content-center">
        @foreach($cards as $card)
            <div class="col-12 col-md-6 mt-20">
                <a href="{{ $card['link'] }}" class="training-modality-card d-block h-100 text-dark text-decoration-none">
                    <h3 class="training-modality-card-title">{{ $card['title'] }}</h3>

                    @if($card['image'] !== '')
                        <div class="training-modality-card-media mt-20">
                            <img src="{{ $card['image'] }}"
                                 alt="{{ $card['title'] }}"
                                 class="training-modality-card-image"
                                 width="480"
                                 height="240">
                        </div>
                    @endif

                    @if(!empty($card['features']))
                        <div class="row training-modality-features mt-25">
                            @foreach($card['features'] as $feature)
                                <div class="col-4 text-center mb-10">
                                    <div class="training-modality-feature-icon d-flex align-items-center justify-content-center mx-auto">
                                        @if(!empty($feature['image']))
                                            <img src="{{ $feature['image'] }}" alt="{{ $feature['title'] }}" width="22" height="22">
                                        @else
                                            <i data-feather="{{ $feature['icon'] }}" width="20" height="20"></i>
                                        @endif
                                    </div>
                                    <p class="training-modality-feature-title font-12 mb-0 mt-10">{{ $feature['title'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </a>
            </div>
        @endforeach
    </div>
</section>

@push('styles_top')
    <style>
        .training-modality-section {
            margin-top: 100px !important;
        }

        .training-modality-card {
            background: #fff;
            border: 1px solid #01477d;
            border-radius: 18px;
            padding: 28px 24px 24px;
            box-shadow: 0 16px 40px rgba(15, 42, 89, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .training-modality-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 50px rgba(15, 42, 89, 0.14);
        }

        .training-modality-card-title {
            font-size: 20px;
            font-weight: 700;
            color: #171347;
            margin: 0;
            line-height: 1.4;
            text-align: center;
        }

        .training-modality-card-media {
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            text-align: center;
        }

        .training-modality-card-image {
            width: auto;
            height: 150px;
            object-fit: cover;
            display: inline-block;
        }

        .training-modality-feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e8f0fe;
            color: var(--primary, #1967d2);
        }

        .training-modality-feature-icon svg {
            stroke: var(--primary, #1967d2);
        }

        .training-modality-feature-title {
            color: #5b6574;
            line-height: 1.45;
        }

        @media (max-width: 767px) {
            .training-modality-section {
                margin-top: 60px;
            }
        }
    </style>
@endpush
