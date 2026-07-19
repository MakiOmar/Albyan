{{-- Trust badges homepage section: white floating card over optional background image --}}
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

<section class="home-sections trust-badges-section position-relative mt-40 {{ $trustBackground !== '' ? 'js-deferred-section-bg has-trust-bg' : '' }}"
         @if($trustBackground !== '') data-deferred-bg="{{ $trustBackground }}" @endif>
    <div class="container position-relative">
        {{-- White floating card holding the badges --}}
        <div class="trust-badges-card">
            <div class="row justify-content-center align-items-start">
                @foreach($trustItems as $index => $item)
                    <div class="col-6 col-md mb-25 mb-md-0">
                        <div class="trust-badge-item text-center">
                            <div class="trust-badge-icon d-flex align-items-center justify-content-center mx-auto">
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="24" height="24">
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
    </div>
</section>

@push('styles_top')
    <style>
        .trust-badges-section {
            background-size: cover;
            background-position: center;
        }

        /* Background image peeks around the white card */
        .trust-badges-section.has-trust-bg {
            padding: 50px 0;
        }

        .trust-badges-card {
            background: #fff;
            border-radius: 16px;
            padding: 45px 30px;
            box-shadow: 0 18px 45px rgba(15, 42, 89, 0.08);
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

        @media (max-width: 767px) {
            .trust-badges-card {
                padding: 30px 15px;
            }
        }
    </style>
@endpush
