{{-- Why choose Albyan: primary boxed section with content + image columns --}}
@php
    $why = getHomeContentBlocksSettings('why_albyan') ?? [];
    $whyTitle = trim((string) ($why['title'] ?? ''));
    if ($whyTitle === '') {
        $whyTitle = trans('update.why_albyan_title_default');
    }
    $whyImage = trim((string) ($why['image'] ?? ''));
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
    <div class="why-albyan-box">
        <div class="row align-items-center">
            <div class="col-12 {{ $whyImage !== '' ? 'col-lg-6' : '' }}">
                <h2 class="why-albyan-title">{{ $whyTitle }}</h2>
                <ul class="why-albyan-list list-unstyled mb-0 mt-25">
                    @foreach($whyItems as $item)
                        <li class="why-albyan-item d-flex align-items-start">
                            <span class="why-albyan-bullet" aria-hidden="true"></span>
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
            background: var(--primary, #01477d);
            border-radius: 20px;
            padding: 40px 36px;
            color: #fff;
        }

        .why-albyan-title {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            line-height: 1.4;
            margin: 0;
        }

        .why-albyan-item {
            margin-top: 14px;
        }

        .why-albyan-bullet {
            flex: 0 0 8px;
            width: 8px;
            height: 8px;
            margin-top: 8px;
            margin-inline-end: 12px;
            border-radius: 2px;
            background: #fff;
        }

        .why-albyan-text {
            color: #fff;
            font-size: 14px;
            line-height: 1.6;
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
