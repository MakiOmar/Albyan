{{-- Trust hero section: background with light overlay, chip + two-line title + description +
     buttons + side image, then trust badges row (reference design) --}}
@php
    $trustSettings = getHomeContentBlocksSettings('trust_badges');
    $trustSettings = is_array($trustSettings) ? $trustSettings : [];

    $trustBackground = trim((string) ($trustSettings['background'] ?? ''));
    $trustSideImage = trim((string) ($trustSettings['side_image'] ?? ''));
    $trustChip = trim((string) ($trustSettings['chip'] ?? ''));
    $trustTitleLine1 = trim((string) ($trustSettings['title_line1'] ?? ''));
    $trustTitleLine2 = trim((string) ($trustSettings['title_line2'] ?? ''));
    $trustDescription = trim((string) ($trustSettings['description'] ?? ''));

    $button1Title = trim((string) ($trustSettings['button1']['title'] ?? '')) ?: trans('site.contact_training_advisor');
    $button1Link = trim((string) ($trustSettings['button1']['link'] ?? '')) ?: getLeadGenerationFormUrl();
    $button2Title = trim((string) ($trustSettings['button2']['title'] ?? '')) ?: trans('site.explore_courses_diplomas');
    $button2Link = trim((string) ($trustSettings['button2']['link'] ?? '')) ?: '/classes';

    $hasHeroContent = ($trustTitleLine1 !== '' || $trustTitleLine2 !== '' || $trustDescription !== '' || $trustSideImage !== '');

    $trustItems = [];
    foreach ($trustSettings as $row) {
        // Numeric keys hold badge items; scalar keys are section options
        if (!is_array($row) || isset($row['link'])) {
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

<section class="trust-hero-section position-relative {{ $trustBackground !== '' ? 'js-deferred-section-bg' : '' }}"
         @if($trustBackground !== '') data-deferred-bg="{{ $trustBackground }}" @endif>
    {{-- Light white overlay over the background image --}}
    <div class="trust-hero-overlay"></div>

    <div class="container position-relative">
        @if($hasHeroContent)
            <div class="row align-items-center pt-50">
                <div class="col-12 col-lg-6">
                    @if($trustChip !== '')
                        <span class="trust-hero-chip d-inline-flex align-items-center">
                            <i data-feather="shield" width="14" height="14" class="mr-5"></i>
                            {{ $trustChip }}
                        </span>
                    @endif

                    @if($trustTitleLine1 !== '' || $trustTitleLine2 !== '')
                        <h2 class="trust-hero-title mt-15 mb-0">
                            @if($trustTitleLine1 !== '')
                                <span class="d-block trust-hero-title-dark">{{ $trustTitleLine1 }}</span>
                            @endif
                            @if($trustTitleLine2 !== '')
                                <span class="d-block trust-hero-title-primary">{{ $trustTitleLine2 }}</span>
                            @endif
                        </h2>
                    @endif

                    @if($trustDescription !== '')
                        <p class="trust-hero-description font-14 mt-20">{!! nl2br(e($trustDescription)) !!}</p>
                    @endif

                    <div class="d-flex flex-wrap align-items-center mt-25" style="gap: 12px;">
                        <a href="{{ $button1Link }}" class="btn btn-primary">{{ $button1Title }}</a>
                        <a href="{{ $button2Link }}" class="btn trust-hero-btn-white">{{ $button2Title }}</a>
                    </div>
                </div>

                @if($trustSideImage !== '')
                    <div class="col-12 col-lg-6 mt-30 mt-lg-0">
                        {{-- Placeholder + data-src so deferred image-lazy-loader controls the fetch --}}
                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                             data-src="{{ $trustSideImage }}"
                             alt="{{ $trustTitleLine1 !== '' ? $trustTitleLine1 : trans('update.trust_badges_items') }}"
                             class="trust-hero-side-image img-cover w-100"
                             loading="lazy"
                             decoding="async">
                    </div>
                @endif
            </div>
        @endif

        {{-- Trust badges row --}}
        <div class="row justify-content-center align-items-start py-50">
            @foreach($trustItems as $index => $item)
                <div class="col-6 col-md mb-25 mb-md-0">
                    <div class="trust-badge-item text-center">
                        <div class="trust-badge-icon d-flex align-items-center justify-content-center mx-auto">
                            @if(!empty($item['image']))
                                {{-- Placeholder + data-src so deferred image-lazy-loader controls the fetch --}}
                                <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                     data-src="{{ $item['image'] }}"
                                     alt="{{ $item['title'] }}"
                                     width="24"
                                     height="24"
                                     loading="lazy"
                                     decoding="async">
                            @else
                                <i data-feather="{{ $trustDefaultIcons[$index % count($trustDefaultIcons)] }}" width="22" height="22"></i>
                            @endif
                        </div>
                        <h3 class="trust-badge-title font-16 font-weight-bold mt-15 mb-0">{{ $item['title'] }}</h3>
                        @if($item['subtitle'] !== '')
                            <p class="trust-badge-subtitle font-14 mt-5 mb-0">{{ $item['subtitle'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('styles_top')
    <style>
        .trust-hero-section {
            background-size: cover;
            background-position: center;
            margin-top: 40px;
        }

        .trust-hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(248, 250, 253, 0.9);
        }

        .trust-hero-chip {
            background: #e8f0fe;
            color: var(--primary, #1967d2);
            border-radius: 30px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
        }

        .trust-hero-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.25;
        }

        .trust-hero-title-dark {
            color: #10131a;
        }

        .trust-hero-title-primary {
            color: var(--primary, #1967d2);
        }

        .trust-hero-description {
            color: #6f7a8a;
            max-width: 480px;
        }

        .trust-hero-btn-white {
            background: #fff;
            color: #10131a;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(15, 42, 89, 0.06);
        }

        .trust-hero-btn-white:hover {
            background: #f4f7fb;
            color: #10131a;
        }

        .trust-hero-side-image {
            border-radius: 14px;
            box-shadow: 0 24px 55px rgba(15, 42, 89, 0.18);
            max-height: 380px;
            object-fit: cover;
        }

        .trust-badge-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #e8f0fe;
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
            line-height: 1.5;
        }

        .trust-badge-subtitle {
            color: #7c8698;
        }

        @media (max-width: 991px) {
            .trust-hero-title {
                font-size: 30px;
            }
        }
    </style>
@endpush
