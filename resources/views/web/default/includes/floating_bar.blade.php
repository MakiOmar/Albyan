@if($floatingBar->position == 'top' and $floatingBar->fixed)
    <style>
        .has-fixed-top-floating-bar {
            padding-top: {{ !empty($floatingBar->bar_height) ? $floatingBar->bar_height : 45 }}px;
        }

        .has-fixed-top-floating-bar .navbar.sticky {
            top: {{ !empty($floatingBar->bar_height) ? $floatingBar->bar_height : 45 }}px;
        }
    </style>
@endif

<div class="floating-bar {{ $floatingBar->fixed ? "is-fixed" : '' }} {{ 'position-'.$floatingBar->position }} " style="{{ !empty($floatingBar->background_image) ? "background-image: url('$floatingBar->background_image');" : '' }} {{ (!empty($floatingBar->background_color) ? "background-color: $floatingBar->background_color;" : '') }} {{ !empty($floatingBar->bar_height) ? "height: {$floatingBar->bar_height}px;" : '' }}">
    <div class="container h-100">
        <div class="d-flex align-items-center justify-content-between h-100">
            <div class="d-flex align-items-center">
                @if(!empty($floatingBar->icon))
                    <div class="floating-bar__icon mr-3">
                        <div class="icon-circle">
                            <img src="{{ $floatingBar->icon }}" alt="{{ $floatingBar->title ?? 'icon' }}" class="img-fluid">
                        </div>
                    </div>
                @endif
                <div class="floating-bar__content">
                    @if(!empty($floatingBar->title))
                        <h5 class="font-16 font-weight-bold mb-1" style="{{ !empty($floatingBar->title_color) ? "color: $floatingBar->title_color" : '' }}">{{ $floatingBar->title }}</h5>
                    @endif

                    @if(!empty($floatingBar->description))
                        <div class="font-14 mb-0" style="{{ !empty($floatingBar->description_color) ? "color: $floatingBar->description_color" : '' }}">{{ $floatingBar->description }}</div>
                    @endif
                </div>
            </div>

            @if(!empty($floatingBar->btn_text))
                <div class="floating-bar__action">
                    <a
                        href="{{ !empty($floatingBar->btn_url) ? $floatingBar->btn_url : '#!' }}"
                        class="btn btn-sm"
                        style="{{ !empty($floatingBar->btn_color) ? "background-color: $floatingBar->btn_color; border-color: $floatingBar->btn_color;" : '' }} {{ !empty($floatingBar->btn_text_color) ? "color: $floatingBar->btn_text_color;" : '' }} "
                    >{{ $floatingBar->btn_text }}</a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.floating-bar {
    position: relative;
    background: linear-gradient(135deg, #136390 0%, #1a7bb8 100%);
    background-size: cover;
    background-repeat: no-repeat;
    max-height: 45px;
    height: 45px;
    z-index: 15000;
    border-radius: 0 25px 25px 0;
    box-shadow: 0 2px 10px rgba(19, 99, 144, 0.3);
}

.floating-bar.is-fixed {
    position: fixed;
    left: 0;
    right: 0;
}

.floating-bar.position-top {
    top: 0;
}

.floating-bar.position-bottom {
    bottom: 0;
}

.floating-bar__icon {
    width: 35px;
    min-width: 35px;
    max-width: 35px;
    height: 35px;
    margin-bottom: 5px;
}

.floating-bar__content {
    margin-bottom: 5px;
}

.floating-bar__action {
    margin-bottom: 5px;
}

.floating-bar .icon-circle {
    width: 35px;
    height: 35px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.floating-bar .icon-circle img {
    width: 20px;
    height: 20px;
    object-fit: contain;
}

@media (max-width: 768px) {
    .floating-bar {
        max-height: 40px;
        height: 40px;
        border-radius: 0 20px 20px 0;
    }

    .floating-bar__icon {
        width: 30px;
        min-width: 30px;
        max-width: 30px;
        height: 30px;
    }

    .floating-bar .icon-circle {
        width: 30px;
        height: 30px;
    }

    .floating-bar .icon-circle img {
        width: 16px;
        height: 16px;
    }

    .floating-bar h5 {
        font-size: 14px !important;
    }

    .floating-bar .font-14 {
        font-size: 12px !important;
    }
}
</style>

