{{-- Trust badges homepage section (icon circles + title/subtitle, optional background) --}}
@php
    $trustSettings = getHomeContentBlocksSettings('trust_badges');
    $trustBackground = '';
    $trustItems = [];

    if (!empty($trustSettings) && is_array($trustSettings)) {
        $trustBackground = trim((string) ($trustSettings['background'] ?? ''));

        foreach ($trustSettings as $key => $row) {
            // Numeric keys are badge items; "background" key is the section background
            if (!is_array($row)) {
                continue;
            }
            $title = trim((string) ($row['title'] ?? ''));
            if ($title !== '') {
                $trustItems[] = [
                    'title' => $title,
                    'subtitle' => trim((string) ($row['subtitle'] ?? '')),
                    'image' => trim((string) ($row['image'] ?? '')),
                ];
            }
        }
    }

    // PDF defaults when admin has not configured items yet
    if (empty($trustItems)) {
        $trustItems = [
            ['title' => trans('update.trust_badge_licensed'), 'subtitle' => '', 'image' => ''],
            ['title' => trans('update.trust_badge_hybrid'), 'subtitle' => '', 'image' => ''],
            ['title' => trans('update.trust_badge_specialties'), 'subtitle' => '', 'image' => ''],
            ['title' => trans('update.trust_badge_trainers'), 'subtitle' => '', 'image' => ''],
            ['title' => trans('update.trust_badge_certificate'), 'subtitle' => '', 'image' => ''],
        ];
    }

    // Fallback feather icons when no custom icon uploaded (cycled by position)
    $trustDefaultIcons = ['award', 'users', 'shield', 'clock', 'check-circle'];
@endphp

<section class="home-sections trust-badges-section position-relative mt-40 {{ $trustBackground !== '' ? 'js-deferred-section-bg' : '' }}"
         @if($trustBackground !== '') data-deferred-bg="{{ $trustBackground }}" @endif>
    @if($trustBackground !== '')
        <div class="trust-badges-overlay"></div>
    @endif

    <div class="container position-relative">
        <div class="row justify-content-center py-40">
            @foreach($trustItems as $index => $item)
                <div class="col-6 col-md mb-20 mb-md-0">
                    <div class="trust-badge-item text-center">
                        <div class="trust-badge-icon d-flex align-items-center justify-content-center mx-auto">
                            @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="28" height="28">
                            @else
                                <i data-feather="{{ $trustDefaultIcons[$index % count($trustDefaultIcons)] }}" width="26" height="26"></i>
                            @endif
                        </div>
                        <h3 class="trust-badge-title font-16 font-weight-bold mt-15 mb-5">{{ $item['title'] }}</h3>
                        @if($item['subtitle'] !== '')
                            <p class="trust-badge-subtitle font-14 text-gray mb-0">{{ $item['subtitle'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('styles_top')
    <style>
        .trust-badges-section {
            background-size: cover;
            background-position: center;
            border-radius: 12px;
        }

        .trust-badges-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.88);
            border-radius: 12px;
        }

        .trust-badge-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(25, 103, 210, 0.08);
            color: var(--primary, #1967d2);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .trust-badge-icon svg {
            stroke: var(--primary, #1967d2);
        }

        .trust-badge-item:hover .trust-badge-icon {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(25, 103, 210, 0.18);
        }

        .trust-badge-title {
            color: var(--primary, #1967d2);
        }
    </style>
@endpush
