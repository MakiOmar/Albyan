@php
    $socials = getSocials();
    $whatsappLink = null;
    
    if (!empty($socials) && count($socials)) {
        foreach ($socials as $social) {
            if (strtolower($social['title']) == 'whatsapp') {
                $whatsappLink = $social['link'];
                break;
            }
        }
    }
@endphp

@if($whatsappLink)
    <div class="whatsapp-chat-widget">
        <div class="whatsapp-tooltip">
            {{ trans('public.whatsapp') }}
        </div>
        <a href="{{ $whatsappLink }}" target="_blank" class="whatsapp-chat-button" title="{{ trans('public.whatsapp') }}" aria-label="{{ trans('public.whatsapp') }}">
            <svg width="60" height="60" viewBox="0 0 58 58" xmlns="http://www.w3.org/2000/svg">
                <g>
                    <path style="fill:#2CB742;" d="M0,58l4.988-14.963C2.457,38.78,1,33.812,1,28.5C1,12.76,13.76,0,29.5,0S58,12.76,58,28.5
                        S45.24,57,29.5,57c-4.789,0-9.299-1.187-13.26-3.273L0,58z"/>
                    <path style="fill:#FFFFFF;" d="M47.683,37.985c-1.316-2.487-6.169-5.331-6.169-5.331c-1.098-0.626-2.423-0.696-3.049,0.42
                        c0,0-1.577,1.891-1.978,2.163c-1.832,1.241-3.529,1.193-5.242-0.52l-3.981-3.981l-3.981-3.981c-1.713-1.713-1.761-3.41-0.52-5.242
                        c0.272-0.401,2.163-1.978,2.163-1.978c1.116-0.627,1.046-1.951,0.42-3.049c0,0-2.844-4.853-5.331-6.169
                        c-1.058-0.56-2.357-0.364-3.203,0.482l-1.758,1.758c-5.577,5.577-2.831,11.873,2.746,17.45l5.097,5.097l5.097,5.097
                        c5.577,5.577,11.873,8.323,17.45,2.746l1.758-1.758C48.048,40.341,48.243,39.042,47.683,37.985z"/>
                </g>
            </svg>
        </a>
    </div>

    <style>
        .whatsapp-chat-widget {
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 9999;
            transition: all 0.3s ease;
        }

        .whatsapp-chat-button {
            display: block;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            background: #25D366;
            border: 2px solid #25D366;
            position: relative;
        }

        .whatsapp-chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .whatsapp-chat-button svg {
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }

        /* Tooltip styles */
        .whatsapp-tooltip {
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .whatsapp-tooltip::after {
            content: '';
            position: absolute;
            left: -5px;
            top: 50%;
            transform: translateY(-50%);
            border-left: 5px solid #333;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
        }

        .whatsapp-chat-widget:hover .whatsapp-tooltip {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .whatsapp-chat-widget {
                bottom: 15px;
                left: 15px;
            }
            
            .whatsapp-chat-button {
                width: 50px;
                height: 50px;
            }

            .whatsapp-tooltip {
                display: none;
            }
        }

        /* Animation for attention */
        .whatsapp-chat-button {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
            50% {
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            }
            100% {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
        }

        /* RTL support */
        .rtl .whatsapp-chat-widget {
            left: auto;
            right: 20px;
        }

        .rtl .whatsapp-tooltip {
            left: auto;
            right: 70px;
        }

        .rtl .whatsapp-tooltip::after {
            left: auto;
            right: -5px;
            border-left: none;
            border-right: 5px solid #333;
        }

        @media (max-width: 768px) {
            .rtl .whatsapp-chat-widget {
                right: 15px;
            }
        }
    </style>
@endif 