{{-- Why choose Albyan: primary overlay box, optional bg image, numbered fancy bullets, side image --}}
@php
    $why = getHomeContentBlocksSettings('why_albyan') ?? [];
    $whyTitle = trim((string) ($why['title'] ?? ''));
    if ($whyTitle === '') {
        $whyTitle = trans('update.why_albyan_title_default');
    }
    $whyBackground = trim((string) ($why['background'] ?? ''));
    $whyImage = trim((string) ($why['image'] ?? ''));
    $overlayOpacity = (int) ($why['overlay_opacity'] ?? 85);
    if ($overlayOpacity < 0) {
        $overlayOpacity = 0;
    }
    if ($overlayOpacity > 100) {
        $overlayOpacity = 100;
    }
    $overlayOpacityCss = number_format($overlayOpacity / 100, 2, '.', '');

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
    <div class="why-albyan-box position-relative {{ $whyBackground !== '' ? 'js-deferred-section-bg' : '' }}"
         style="--why-overlay-opacity: {{ $overlayOpacityCss }};"
         @if($whyBackground !== '') data-deferred-bg="{{ $whyBackground }}" @endif>
        {{-- Primary color overlay with controllable opacity --}}
        <div class="why-albyan-overlay"></div>

        <div class="row align-items-center position-relative">
            <div class="col-12 {{ $whyImage !== '' ? 'col-lg-6' : '' }}">
                <h2 class="why-albyan-title">{{ $whyTitle }}</h2>
                <ul class="why-albyan-list list-unstyled mb-0 mt-25">
                    @foreach($whyItems as $index => $item)
                        <li class="why-albyan-item d-flex align-items-start">
                            <span class="why-albyan-number" aria-hidden="true">{{ $index + 1 }}</span>
                            <span class="why-albyan-text">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($whyImage !== '')
                <div class="col-12 col-lg-6 mt-30 mt-lg-0">
                    <div class="why-albyan-media text-center">
                        <img src="{{ $whyImage }}"
                             alt="{{ $whyTitle }}"
                             class="why-albyan-image"
                             width="560"
                             height="360">
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

@push('styles_top')
    <style>
        .why-albyan-box {
            background-color: var(--primary, #01477d);
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            padding: 40px 36px;
            color: #fff;
            overflow: hidden;
        }

        .why-albyan-overlay {
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: var(--primary, #01477d);
            opacity: var(--why-overlay-opacity, 0.85);
            pointer-events: none;
        }

        .why-albyan-title {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            line-height: 1.4;
            margin: 0;
        }

        .why-albyan-item {
            margin-top: 16px;
        }

        /* Fancy numbered bullets (white ring + shadow, cycling fills) */
        .why-albyan-number {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            margin-inline-end: 14px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            line-height: 28px;
            text-align: center;
            background: #2b3038;
        }

        .why-albyan-item:nth-child(3n+2) .why-albyan-number {
            background: #5f7a8f;
        }

        .why-albyan-item:nth-child(3n+3) .why-albyan-number {
            background: #c4a35a;
        }

        .why-albyan-text {
            color: #fff;
            font-size: 14px;
            line-height: 1.6;
            padding-top: 6px;
        }

        .why-albyan-image {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            object-fit: cover;
            display: block;
        }

        @media (max-width: 991px) {
            .why-albyan-box {
                padding: 28px 20px;
            }

            .why-albyan-title {
                font-size: 22px;
                text-align: center;
            }
        }
    </style>
@endpush
