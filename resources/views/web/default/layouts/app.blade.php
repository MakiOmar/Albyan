<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp

<head>
    @include('web.default.includes.metas')
    <meta name="theme" content="{{ str_replace('web.', '', getTemplate()) }}">
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <!-- Font Preconnect Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://stackpath.bootstrapcdn.com">
    <link rel="preconnect" href="https://maxcdn.bootstrapcdn.com">
    <link rel="preconnect" href="https://connect.facebook.net">

    <!-- Font Preload -->
    <link rel="preload" href="https://albyaninstitute.com/store/1/fonts/cairo-regular-webfont.woff2" as="font" type="font/woff2" crossorigin>

    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/default/css/app.min.css?v={{ time() }}">

    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.min.css">
    @endif

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeFontsSettings() !!}

        {!! getThemeColorsSettings() !!}
        .flagstrap button{
            color: #fff;
        }
        #login-button{
            font-weight: bold;
        }
        .btn-border-white {
            background-color: #01477d;
            color: #fff;
        }
    </style>


    @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
    @endif
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-WTTKXKPQ');</script>
    <!-- End Google Tag Manager -->
    <style>
        :root {
            --main-color: #01477d;
            --font-size-large: 20px;
        }
        @media (min-width: 768px) {
            .slider-container{
                height: 600px;
                width: 100vw;
                background-color: rgba(0,0,0,0.3);
                padding: 0;
            }
            .slider-container #homeHeroVideoBackground {
                position: relative;
                height: 600px;
            }
        }
        #app{
            overflow-x: hidden;
        }
        .mask {
            display: none;
        }
        .slider-container{
            overflow: hidden;
            background-color: rgba(0,0,0,0.3);
        }
        
        .blue-bg{
            background-color: var(--main-color)!important;
        }
        .blue-txt{
            color: var(--main-color)!important;
        }
        #navbarShopingCart svg circle, #navbarShopingCart svg path, #navbarNotification svg path{
            stroke: #fff;
        }
        .login-register-button{
            border-radius: 25px;
            border: 1px solid #fff;
        }
        a#login-button:hover, a#login-button:focus {
            color: var(--main-color) !important;
        }
        .navbar-search ::placeholder{
            color: #fff
        }
        .trendy-category canvas{
            margin-left: 4px
        }
        .trendy-category:hover{
            background-color: var(--main-color);
            color: #fff!important

        }
        .text-dark.trendy-category:hover{
            color: #fff!important
        }
        .section-title{
            position: relative;
        }
        .section-title:not(.section-title-bg)::before{
            content: '';
            display: block;
            position: absolute;
            right: -22px;
            background-color: var(--main-color);
            width: 25px;
            height: 5px;
            border-radius: 3px;
            rotate: 90deg;
            top: 15px;
        }
        .section-title-bg{
            min-width: 200px;
            position: relative;
            display: inline-block;
            padding: 10px;
            border: 2px solid var(--main-color);
            border-radius: 15px
        }
        .webinar-card{
            background-color: #F2F5FC;
        }
        .webinar-card .webinar-card-body .webinar-price-box .real {
            font-size: 16px!important;
        }
        .top-0 {
        top: 0 !important;
        }

        .right-0 {
        right: 0 !important;
        }

        .bottom-0 {
        bottom: 0 !important;
        }

        .left-0 {
        left: 0 !important;
        }
        .light-gray-bg{
            background-color: #F2F5FC!important;
        }
        .course-teacher-card .teacher-avatar {
            position: absolute;
            top: -40px;
        }
        .course-teacher-card{
            padding-top: 35px!important
        }
        .footer {
            margin-top: 355px!important;
        }
        .course-content-section .course-title {
            color: white ; 
            }
            .cart-banner {
                padding: 20px 0!important;
            }
            .bottom-view-all{
            display:none
            }
            .d-none {
                        display: none;
                    }
                    .text-blue {
                        color: #01477d;
                        cursor: pointer;
                        background: none;
                        border: none;
                        cursor: pointer;
                        font-weight: bold;
                    }
                    .testimonials-container .testimonials-card {
                        min-height: 275px;
                    }
            .user-inline-avatar{
            display:none!important
            }
            @media (max-width: 991px) {
            .navbar-brand img {
                max-height: 30px;
            }

            .slider-container, .slider-container.slider-hero-section2 {
                min-height: 300px;
            }
            }
            @media (min-width: 1200px) {
            .container-xl, .container-lg, .container-md, .container-sm, .container {
                max-width: 1200px;
            }
            }

            .trending-image .icon img{
            width:auto;
            height:auto
            }
            .trending-card .trending-image .icon {
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .contact-us-about p {
            font-size: 18px;
            line-height: 30px;
            text-align: justify;
            }
            .testimonials-card img{
            position: absolute;
            top: 10px;
            left: 10px;
            }
            .swiper-wrapper{
            min-height: 300px;
            }
            .home-sections {
                margin-top: 50px;
            }
            .navbar-brand {
                height: auto;
            }
            .navbar-brand  img{
                width: auto;
            }
            .footer .footer-logo {

            height: auto!important; 
            }

            .footer-social a {
            display:inline-block;
            margin-bottom:5px
            }
            @media screen and (max-width:480px){
            .bottom-view-all{
            display:inline-flex
            }
            .top-view-all{
            display:none
            }
            }
        @media screen and ( max-width:480px ){
            .webinar-card .webinar-card-body .webinar-price-box .real {
                font-size: 12px !important;
            }
            .js-course-add-to-cart-btn{
                font-size: 12px !important;
                padding: 5px 8px;
            }
        }
    </style>
    
</head>

<body class="@if($isRtl) rtl @endif">
    <!-- This site is converting visitors into subscribers and customers with https://respond.io -->
    <!--<script id="respondio__widget" src="https://cdn.respond.io/webchat/widget/widget.js?cId=63dbb8bd5ba43dbcf09b1bfc5df85fd"></script>--><!-- https://respond.io -->
    
    
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WTTKXKPQ"
        height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
<div id="app" class="{{ (!empty($floatingBar) and $floatingBar->position == 'top' and $floatingBar->fixed) ? 'has-fixed-top-floating-bar' : '' }}">
    @if(!empty($floatingBar) and $floatingBar->position == 'top')
        @include('web.default.includes.floating_bar')
    @endif

    @if(!isset($appHeader))
        @include('web.default.includes.top_nav')
        @include('web.default.includes.navbar')
    @endif

    @if(!empty($justMobileApp))
        @include('web.default.includes.mobile_app_top_nav')
    @endif

    @yield('content')

    @if(!isset($appFooter))
        @include('web.default.includes.footer')
    @endif

    @include('web.default.includes.advertise_modal.index')

    @if(!empty($floatingBar) and $floatingBar->position == 'bottom')
        @include('web.default.includes.floating_bar')
    @endif
</div>
<!-- Template JS File -->
<script src="/assets/default/js/app.js"></script>
<script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script src="/assets/default/vendors/moment.min.js"></script>
<script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
<script type="text/javascript" src="/assets/default/vendors/simplebar/simplebar.min.js"></script>

@if(empty($justMobileApp) and checkShowCookieSecurityDialog())
    @include('web.default.includes.cookie-security')
@endif


<script>
    var deleteAlertTitle = '{{ trans('public.are_you_sure') }}';
    var deleteAlertHint = '{{ trans('public.deleteAlertHint') }}';
    var deleteAlertConfirm = '{{ trans('public.deleteAlertConfirm') }}';
    var deleteAlertCancel = '{{ trans('public.cancel') }}';
    var deleteAlertSuccess = '{{ trans('public.success') }}';
    var deleteAlertFail = '{{ trans('public.fail') }}';
    var deleteAlertFailHint = '{{ trans('public.deleteAlertFailHint') }}';
    var deleteAlertSuccessHint = '{{ trans('public.deleteAlertSuccessHint') }}';
    var forbiddenRequestToastTitleLang = '{{ trans('public.forbidden_request_toast_lang') }}';
    var forbiddenRequestToastMsgLang = '{{ trans('public.forbidden_request_toast_msg_lang') }}';
</script>

@if(session()->has('toast'))
    <script>
        (function () {
            "use strict";

            $.toast({
                heading: '{{ session()->get('toast')['title'] ?? '' }}',
                text: '{{ session()->get('toast')['msg'] ?? '' }}',
                bgColor: '@if(session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                textColor: 'white',
                hideAfter: 10000,
                position: 'bottom-right',
                icon: '{{ session()->get('toast')['status'] }}'
            });
        })(jQuery)
    </script>
@endif

@include('web.default.includes.purchase_notifications')

@stack('styles_bottom')
@stack('scripts_bottom')

<script src="/assets/default/js/lazy-css-loader.js"></script>
<script src="/assets/default/js/image-lazy-loader.js?v={{ time() }}"></script>
<script src="/assets/default/js/parts/main.min.js"></script>

<script>
    @if(session()->has('registration_package_limited'))
    (function () {
        "use strict";

        handleLimitedAccountModal('{!! session()->get('registration_package_limited') !!}')
    })(jQuery)

    {{ session()->forget('registration_package_limited') }}
    @endif

    {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
    $('body').on('click', '.js-course-add-to-cart-btn', function (e) {
        const $this = $(this);
        $this.addClass('loadingbar primary').prop('disabled', true);

        const $form = $this.closest('form');
        $form.attr('action', '/cart/store');
        let action = $(this).data('action');
        if( action == 'buy_now' ) {
            $('#direct_buy').val('yes');
        }
        $form.trigger('submit');
    });
</script>

<script>
    /**
        * Dynamically recolors PNG images inside a parent container.
        * @param {string} parentSelector - The class of the parent element that triggers the change.
        * @param {string} action - The trigger event: "click", "hover", or "load".
        * @param {string} color - The target color in hex format (e.g., "#FF5733").
        * @param {boolean} resetOthers - Whether to reset other images to their original state.
        */
    function applyParentImageColorChange(parentSelector, action, color, resetOthers) {
        document.querySelectorAll(parentSelector).forEach(parent => {
            let img = parent.querySelector("img"); // Target any image inside the parent
            if (!img) return; // If no image is found, exit function

            function recolorImage() {
                // Reset all previous images (only if resetOthers is true)
                if (resetOthers) {
                    document.querySelectorAll(parentSelector).forEach(otherParent => {
                        let otherImg = otherParent.querySelector("img");
                        let otherCanvas = otherParent.querySelector("canvas");
                        if (otherCanvas && otherImg) {
                            otherCanvas.style.display = "none"; // Hide canvas
                            otherImg.style.display = "inline"; // Show original image
                        }
                    });
                }

                // Ensure the image is fully loaded before modifying it
                if (!img.complete) {
                    img.onload = function () {
                        recolorImage(); // Run function again after image loads
                    };
                    return;
                }

                // Create or reuse a canvas
                let canvas = parent.querySelector("canvas");
                if (!canvas) {
                    canvas = document.createElement("canvas");
                    canvas.style.display = "none"; // Hide by default
                    img.parentNode.insertBefore(canvas, img); // Prepend canvas before the image
                }
                let ctx = canvas.getContext("2d");

                // Get high-resolution image dimensions
                let imgWidth = img.naturalWidth;
                let imgHeight = img.naturalHeight;
                canvas.width = imgWidth;
                canvas.height = imgHeight;

                // Draw the image onto the canvas
                ctx.drawImage(img, 0, 0, imgWidth, imgHeight);
                let imageData = ctx.getImageData(0, 0, imgWidth, imgHeight);
                let data = imageData.data;

                let r = parseInt(color.substring(1, 3), 16);
                let g = parseInt(color.substring(3, 5), 16);
                let b = parseInt(color.substring(5, 7), 16);

                // Loop through pixels and recolor non-transparent areas
                for (let i = 0; i < data.length; i += 4) {
                    if (data[i+3] > 0) {  // If pixel is not transparent
                        data[i] = r;   // Red
                        data[i+1] = g; // Green
                        data[i+2] = b; // Blue
                    }
                }

                // Apply the new color
                ctx.putImageData(imageData, 0, 0);

                // Ensure the canvas matches displayed image size
                canvas.style.width = img.width + "px";
                canvas.style.height = img.height + "px";

                // Hide the original image and show the canvas
                img.style.display = "none";
                canvas.style.display = "inline-block";
            }

            // Apply event listener based on action type
            if (action === "click") {
                parent.addEventListener("click", recolorImage);
            } else if (action === "hover") {
                parent.addEventListener("mouseenter", recolorImage);
                parent.addEventListener("mouseleave", function () {
                    // Reset to original state on mouse leave
                    let canvas = parent.querySelector("canvas");
                    if (canvas) canvas.style.display = "none";
                    img.style.display = "inline";
                });
            } else if (action === "load") {
                recolorImage(); // Apply color change immediately on page load
            }
        });
    }
    applyParentImageColorChange('.trendy-category', 'hover', '#ffffff', true);
</script>

    @include('web.default.partials.floating_city_bar')
    @include('web.default.includes.whatsapp-chat')

</body>
</html>
