<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp

<head>
    @include('web.default.includes.metas')
    {{-- robots meta tag is controlled by `resources/views/web/default/includes/metas.blade.php` --}}
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
    <link rel="preload" href="https://albyan.institute/store/1/fonts/cairo-regular-webfont.woff2" as="font" type="font/woff2" crossorigin>

    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/default/css/app.min.css">

    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.min.css">
    @endif

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeFontsSettings() !!}

        {!! getThemeColorsSettings() !!}
        #navbarNotification .badge.badge-circle-danger{
            position: absolute;
        }
        .rtl .search-icon{
            right: auto !important;
            left: 0px !important;
        }
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
        
        .forms-body img{
            max-height: 150px;
        }
        .custom-radio .custom-control-label::before {
            border-radius: 7px;
        }

        .custom-radio .custom-control-label::before {
           position: relative;
           top:auto;
           left:auto;
           right:auto;
           bottom:auto;
           margin-right:10px;
        }
        body.rtl .custom-radio .custom-control-label::before {
            margin-left:10px;
            margin-right:0;
        }
        .custom-radio .custom-control-label{
            display: flex;
        }
    </style>


    @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
    @endif
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
        /* CLS: bottom heading strip — full width + min-height so web font load / line wrap does not shift following content */
        .slider-container .slider-heading {
            width: 100%;
            left: 0;
            right: 0;
            box-sizing: border-box;
            min-height: 4.25rem;
        }
        @media screen and (max-width: 480px) {
            .slider-container .slider-heading {
                min-height: 3.5rem;
            }
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
        @media (max-width: 960px) {
            .footer-social,
            .navbar-search,
            .shopping-cart-dropdown,
            .notification-dropdown {
                display: none !important;
            }

            /* prevent content being hidden behind footer bar */
            body {
                padding-bottom: calc(60px + env(safe-area-inset-bottom)) !important;
            }

            #mobile-footer-bar {
                display: block !important;
            }
        }
        
        /* Mobile Footer Bar Styles */
        .mobile-footer-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #01477d;
            border-top: 1px solid #fff;
            z-index: 1050;
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
            box-shadow: 0 -6px 16px rgba(0,0,0,0.18);
        }
        .mobile-footer-btn-container .dropdown-toggle::after {
            display: none;
        }
        #mobile-footer-bar .dropdown-menu {
            transform: initial !important;
        }
        .mobile-footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 12px;
            gap: 8px;
        }
        
        .mobile-footer-btn {
            background: none;
            border: none;
            color: white;
            padding: 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            min-height: 44px;
            transition: background-color 0.3s;
            flex: 1 1 25%;
        }
        
        .mobile-footer-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .mobile-footer-btn-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1 1 25%;
        }
        
        .mobile-footer-btn-container .dropdown-toggle {
            background: none;
            border: none;
            color: white;
            padding: 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            min-height: 44px;
            width: 100%;
        }
        
        .mobile-footer-btn-container .dropdown-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .mobile-social-bar {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #01477d;
            border-top: 1px solid #fff;
            padding: 12px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 -8px 18px rgba(0,0,0,0.15);
        }
        
        .mobile-social-content {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .mobile-social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            transition: background-color 0.3s;
        }
        
        .mobile-social-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .mobile-search-form {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #01477d;
            border-top: 1px solid #fff;
            padding: 12px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 -8px 18px rgba(0,0,0,0.15);
        }
        
        .mobile-search-content {
            display: flex;
            gap: 8px;
            max-width: 360px;
            margin: 0 auto;
        }
        
        .mobile-search-content input {
            flex: 1;
            border-radius: 10px;
            border: none;
            padding: 10px 14px;
        }
        
        .mobile-search-content button {
            border-radius: 10px;
            padding: 10px 14px;
        }

        /* Feather icons in footer bar */
        .mobile-footer-bar svg {
            stroke: #fff;
        }
        
        /* Mobile Footer Dropdown Styles */
        .mobile-footer-bar .dropdown-menu {
            position: absolute !important;
            bottom: 100% !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            margin-bottom: 10px !important;
            min-width: 250px !important;
            top: auto !important;
            right: auto !important;
        }
        
        .mobile-footer-bar .dropdown-menu.show {
            display: block !important;
        }
        
        /* Override Bootstrap's x-placement positioning for mobile footer */
        .mobile-footer-bar .dropdown-menu[x-placement] {
            position: absolute !important;
            bottom: 100% !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            top: auto !important;
            right: auto !important;
        }
        
        /* Ensure mobile footer bar has proper z-index for dropdowns */
        .mobile-footer-bar {
            z-index: 1050;
        }
        
        .mobile-footer-bar .dropdown-menu {
            z-index: 1051;
        }
        @media (max-width: 767px) {
            #top-nav-container {
                flex-direction: column !important;
            }
        }
        @media (min-width: 768px) and (max-width: 1200px) {
            .footer-social a.mr-15 {
                padding: 2px !important;
                margin: 3px !important;
                min-width: 20px !important;
                min-height: 20px !important;
            }
        }
        @media (min-width: 768px) {
            .container-md, .container-sm, .container {
                max-width: 1200px !important;
            }
        }
    </style>
    
</head>

<body class="@if($isRtl) rtl @endif">
    <!-- This site is converting visitors into subscribers and customers with https://respond.io -->
    <!--<script id="respondio__widget" src="https://cdn.respond.io/webchat/widget/widget.js?cId=63dbb8bd5ba43dbcf09b1bfc5df85fd"></script>--><!-- https://respond.io -->
    
    
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NB3BZ2JT"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
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
<script src="/assets/default/js/image-lazy-loader.js"></script>
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
    
    // Mobile Footer Bar Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileSocialToggle = document.getElementById('mobile-social-toggle');
        const mobileSearchToggle = document.getElementById('mobile-search-toggle');
        const mobileSocialBar = document.getElementById('mobile-social-bar');
        const mobileSearchForm = document.getElementById('mobile-search-form');
        
        if (mobileSocialToggle && mobileSocialBar) {
            mobileSocialToggle.addEventListener('click', function() {
                // Close search form if open
                if (mobileSearchForm) {
                    mobileSearchForm.classList.add('d-none');
                }
                
                // Toggle social bar
                mobileSocialBar.classList.toggle('d-none');
            });
        }
        
        if (mobileSearchToggle && mobileSearchForm) {
            mobileSearchToggle.addEventListener('click', function() {
                // Close social bar if open
                if (mobileSocialBar) {
                    mobileSocialBar.classList.add('d-none');
                }
                
                // Toggle search form
                mobileSearchForm.classList.toggle('d-none');
            });
        }
        
        // Close mobile bars when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileSocialBar && !mobileSocialBar.contains(event.target) && !mobileSocialToggle.contains(event.target)) {
                mobileSocialBar.classList.add('d-none');
            }
            
            if (mobileSearchForm && !mobileSearchForm.contains(event.target) && !mobileSearchToggle.contains(event.target)) {
                mobileSearchForm.classList.add('d-none');
            }
        });
        
        // Close mobile bars on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 960) {
                if (mobileSocialBar) mobileSocialBar.classList.add('d-none');
                if (mobileSearchForm) mobileSearchForm.classList.add('d-none');
            }
        });
    });
</script>
    {{--
    @include('web.default.partials.floating_city_bar')
    --}}
    @include('web.default.includes.whatsapp-chat')

    <!-- Mobile Footer Bar -->
    <div id="mobile-footer-bar" class="mobile-footer-bar d-none">
        <div class="mobile-footer-content">
            <!-- WhatsApp Chat Icon -->
            @php
                $mobileSocials = getSocials();
                $mobileWhatsappLink = null;
                
                if (!empty($mobileSocials) && count($mobileSocials)) {
                    foreach ($mobileSocials as $social) {
                        if (strtolower($social['title']) == 'whatsapp') {
                            $mobileWhatsappLink = $social['link'];
                            break;
                        }
                    }
                }
            @endphp
            
            @if($mobileWhatsappLink)
            <a href="{{ $mobileWhatsappLink }}" target="_blank" class="mobile-footer-btn" title="{{ trans('public.whatsapp') }}" aria-label="{{ trans('public.whatsapp') }}">
                <svg width="22" height="22" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M28 16C28 22.6274 22.6274 28 16 28C13.4722 28 11.1269 27.2184 9.19266 25.8837L5.09091 26.9091L6.16576 22.8784C4.80092 20.9307 4 18.5589 4 16C4 9.37258 9.37258 4 16 4C22.6274 4 28 9.37258 28 16Z" fill="#25D366"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16 30C23.732 30 30 23.732 30 16C30 8.26801 23.732 2 16 2C8.26801 2 2 8.26801 2 16C2 18.5109 2.661 20.8674 3.81847 22.905L2 30L9.31486 28.3038C11.3014 29.3854 13.5789 30 16 30ZM16 27.8462C22.5425 27.8462 27.8462 22.5425 27.8462 16C27.8462 9.45755 22.5425 4.15385 16 4.15385C9.45755 4.15385 4.15385 9.45755 4.15385 16C4.15385 18.5261 4.9445 20.8675 6.29184 22.7902L5.23077 26.7692L9.27993 25.7569C11.1894 27.0746 13.5046 27.8462 16 27.8462Z" fill="white"/>
                    <path d="M12.5 9.49989C12.1672 8.83131 11.6565 8.8905 11.1407 8.8905C10.2188 8.8905 8.78125 9.99478 8.78125 12.05C8.78125 13.7343 9.52345 15.578 12.0244 18.3361C14.438 20.9979 17.6094 22.3748 20.2422 22.3279C22.875 22.2811 23.4167 20.0154 23.4167 19.2503C23.4167 18.9112 23.2062 18.742 23.0613 18.696C22.1641 18.2654 20.5093 17.4631 20.1328 17.3124C19.7563 17.1617 19.5597 17.3656 19.4375 17.4765C19.0961 17.8018 18.4193 18.7608 18.1875 18.9765C17.9558 19.1922 17.6103 19.083 17.4665 19.0015C16.9374 18.7892 15.5029 18.1511 14.3595 17.0426C12.9453 15.6718 12.8623 15.2001 12.5959 14.7803C12.3828 14.4444 12.5392 14.2384 12.6172 14.1483C12.9219 13.7968 13.3426 13.254 13.5313 12.9843C13.7199 12.7145 13.5702 12.305 13.4803 12.05C13.0938 10.953 12.7663 10.0347 12.5 9.49989Z" fill="white"/>
                </svg>
            </a>
            @endif
            
            <!-- Social Media Icon -->
            <button id="mobile-social-toggle" class="mobile-footer-btn" title="Social Media" aria-label="Social Media">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16.5 2.25C14.7051 2.25 13.25 3.70507 13.25 5.5C13.25 5.69591 13.2673 5.88776 13.3006 6.07412L8.56991 9.38558C8.54587 9.4024 8.52312 9.42038 8.50168 9.43939C7.94993 9.00747 7.25503 8.75 6.5 8.75C4.70507 8.75 3.25 10.2051 3.25 12C3.25 13.7949 4.70507 15.25 6.5 15.25C7.25503 15.25 7.94993 14.9925 8.50168 14.5606C8.52312 14.5796 8.54587 14.5976 8.56991 14.6144L13.3006 17.9259C13.2673 18.1122 13.25 18.3041 13.25 18.5C13.25 20.2949 14.7051 21.75 16.5 21.75C18.2949 21.75 19.75 20.2949 19.75 18.5C19.75 16.7051 18.2949 15.25 16.5 15.25C15.4472 15.25 14.5113 15.7506 13.9174 16.5267L9.43806 13.3911C9.63809 12.9694 9.75 12.4978 9.75 12C9.75 11.5022 9.63809 11.0306 9.43806 10.6089L13.9174 7.4733C14.5113 8.24942 15.4472 8.75 16.5 8.75C18.2949 8.75 19.75 7.29493 19.75 5.5C19.75 3.70507 18.2949 2.25 16.5 2.25ZM14.75 5.5C14.75 4.5335 15.5335 3.75 16.5 3.75C17.4665 3.75 18.25 4.5335 18.25 5.5C18.25 6.4665 17.4665 7.25 16.5 7.25C15.5335 7.25 14.75 6.4665 14.75 5.5ZM6.5 10.25C5.5335 10.25 4.75 11.0335 4.75 12C4.75 12.9665 5.5335 13.75 6.5 13.75C7.4665 13.75 8.25 12.9665 8.25 12C8.25 11.0335 7.4665 10.25 6.5 10.25ZM16.5 16.75C15.5335 16.75 14.75 17.5335 14.75 18.5C14.75 19.4665 15.5335 20.25 16.5 20.25C17.4665 20.25 18.25 19.4665 18.25 18.5C18.25 17.5335 17.4665 16.75 16.5 16.75Z" fill="white"/>
                </svg>
            </button>
            
            <!-- Search Icon -->
            <button id="mobile-search-toggle" class="mobile-footer-btn" title="Search" aria-label="Search">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            
            <!-- Shopping Cart -->
            <div class="mobile-footer-btn-container">
                @php $isMobileFooter = true; @endphp
                @include('web.default.includes.shopping-cart-dropdwon')
            </div>
            
            <!-- Notifications -->
            <div class="mobile-footer-btn-container">
                @php $isMobileFooter = true; @endphp
                @include('web.default.includes.notification-dropdown')
            </div>
        </div>
        
        <!-- Mobile Social Media Bar -->
        <div id="mobile-social-bar" class="mobile-social-bar d-none">
            <div class="mobile-social-content">
                @php
                    $socials = getSocials();
                    
                    if (!empty($socials) && count($socials)) {
                        $socials = collect($socials)->sortBy('order')->values()->toArray();

                        foreach ($socials as $id => $social) {
                            if ($social['title'] == 'Facebook') {
                                $socials[$id]['image'] = '/store/1/socials/211902_social_facebook.png';
                            } elseif ($social['title'] == 'Twitter') {
                                $socials[$id]['image'] = '/store/1/socials/11244080_x_twitter.png';
                            } elseif ($social['title'] == 'Instagram') {
                                $socials[$id]['image'] = '/store/1/socials/1161953_instagram_icon.png';
                            } elseif ($social['title'] == 'Whatsapp') {
                                $socials[$id]['image'] = '/store/1/socials/7156624_whatsapp.png';
                            } elseif ($social['title'] == 'Snapchat') {
                                $socials[$id]['image'] = '/store/1/socials/1851684_snap chat.png';
                            } elseif ($social['title'] == 'Linkedin') {
                                $socials[$id]['image'] = '/store/1/socials/367593_linkedin.png';
                            } elseif ($social['title'] == 'Tik Tok') {
                                $socials[$id]['image'] = '/store/1/socials/8547041_tiktok.png';
                            } elseif ($social['title'] == 'Youtube') {
                                $socials[$id]['image'] = '/store/1/socials/4375133_youtube.png';
                            }
                        }
                    }
                @endphp
                
                @if(!empty($socials) && count($socials))
                    @foreach($socials as $social)
                        <a href="{{ $social['link'] }}" target="_blank" class="mobile-social-link">
                            <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}" width="24" height="24">
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
        
        <!-- Mobile Search Form -->
        <div id="mobile-search-form" class="mobile-search-form d-none">
            <form action="/search" method="get" class="mobile-search-content">
                <input class="form-control" type="text" name="search" placeholder="{{ trans('navbar.search_anything') }}" aria-label="Search">
                <button type="submit" class="btn btn-primary">
                    <i data-feather="search" width="16" height="16"></i>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
