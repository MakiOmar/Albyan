{{-- Trust badges homepage section --}}
@php
    $trustSettings = getHomeContentBlocksSettings('trust_badges');
    $trustItems = [];
    if (!empty($trustSettings) && is_array($trustSettings)) {
        foreach ($trustSettings as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title !== '') {
                $trustItems[] = [
                    'title' => $title,
                    'image' => trim((string) ($row['image'] ?? '')),
                ];
            }
        }
    }
    // PDF defaults when admin has not configured items yet
    if (empty($trustItems)) {
        $trustItems = [
            ['title' => trans('update.trust_badge_licensed'), 'image' => ''],
            ['title' => trans('update.trust_badge_hybrid'), 'image' => ''],
            ['title' => trans('update.trust_badge_specialties'), 'image' => ''],
            ['title' => trans('update.trust_badge_trainers'), 'image' => ''],
            ['title' => trans('update.trust_badge_certificate'), 'image' => ''],
        ];
    }
@endphp

<section class="home-sections container mt-40">
    <div class="row">
        @foreach($trustItems as $item)
            <div class="col-6 col-md text-center mb-20">
                @if(!empty($item['image']))
                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="48" height="48" class="mb-10">
                @endif
                <p class="font-14 font-weight-bold mb-0">{{ $item['title'] }}</p>
            </div>
        @endforeach
    </div>
</section>
