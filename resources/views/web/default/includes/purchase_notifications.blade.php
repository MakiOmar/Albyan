@if(!empty($purchaseNotifications) and count($purchaseNotifications))

    <script>
        (function () {
            "use strict";

            @foreach($purchaseNotifications as $purchaseNotification)
            @if(!empty($purchaseNotification->content))
            setTimeout(function () {
                $.toast({
                    heading: '',
                    text: `<a href="{{ $purchaseNotification->content->getUrl() }}" target="_blank">
                        <div class="purchase-notification d-flex w-100 h-100">
                            <div class="purchase-notification-image">
                                <img width="200" height="150" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="{{ $purchaseNotification->content->getImage() ?: '/assets/default/img/placeholder.svg' }}" alt="{{ $purchaseNotification->content->title }}" class="img-cover">
                            </div>
                            <div class="ml-10">
                                <h4 class="font-14 font-weight-bold text-dark">{{ $purchaseNotification->notif_title }}</h4>
                                <p class="mt-5 font-12 text-gray">{{ $purchaseNotification->notif_subtitle }}</p>

                                <div class="mt-10 font-10 purchase-notification-time">{{ $purchaseNotification->time }}</div>
                            </div>
                        </div>
                    </a>`,
                    bgColor: 'white',
                    hideAfter: Number('{{ !empty($purchaseNotification->popup_duration) ? ($purchaseNotification->popup_duration * 1000) : 5000 }}'),
                    position: 'bottom-right',
                    allowToastClose : true,
                    loaderBg: 'var(--primary)',
                });
            }, Number('{{ !empty($purchaseNotification->popup_delay) ? ($purchaseNotification->popup_delay * 1000) : 0 }}'))
            @endif
            @endforeach
        })(jQuery)
    </script>
@endif
