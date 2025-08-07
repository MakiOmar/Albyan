@extends('web.default.layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <div class="city-info">
                        @if(!empty($city['flag']))
                            <img src="{{ url($city['flag']) }}" alt="{{ $city['name'] }}" class="city-flag-large">
                        @endif
                        <h1 class="page-title">فرع {{ $city['name'] }}</h1>
                        <p class="page-description">فرع البيان في {{ $city['name'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="contact-info-section">
                    <h3>معلومات الاتصال</h3>
                    
                    <div class="contact-details">
                        @if(!empty($city['phone']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-content">
                                    <h4>الهاتف</h4>
                                    <a href="tel:{{ $city['phone'] }}">{{ $city['phone'] }}</a>
                                </div>
                            </div>
                        @endif
                        
                        @if(!empty($city['whatsapp']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="contact-content">
                                    <h4>واتساب</h4>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $city['whatsapp']) }}" target="_blank">
                                        {{ $city['whatsapp'] }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        @if(!empty($city['email']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-content">
                                    <h4>البريد الإلكتروني</h4>
                                    <a href="mailto:{{ $city['email'] }}">{{ $city['email'] }}</a>
                                </div>
                            </div>
                        @endif
                        
                        @if(!empty($city['address']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-content">
                                    <h4>العنوان</h4>
                                    <p>{{ $city['address'] }}</p>
                                </div>
                            </div>
                        @endif
                        
                                                @if(!empty($city['latitude']) && !empty($city['longitude']))
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map"></i>
                                </div>
                                <div class="contact-content w-100">
                                    <h4>الموقع على الخريطة</h4>
                                                                         <div class="map-container">
                                         <iframe 
                                             width="100%" 
                                             height="350" 
                                             frameborder="0" 
                                             scrolling="no" 
                                             marginheight="0" 
                                             marginwidth="0"
                                             src="https://maps.google.com/maps?q={{ $city['latitude'] }},{{ $city['longitude'] }}&hl=ar&z=15&output=embed">
                                         </iframe>
                                     </div>
                                    <a href="https://maps.google.com/?q={{ $city['latitude'] }},{{ $city['longitude'] }}" target="_blank" class="map-link">
                                        <i class="fas fa-external-link-alt"></i>
                                        فتح في الخريطة
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map"></i>
                                </div>
                                <div class="contact-content">
                                    <h4>الموقع على الخريطة</h4>
                                    <p class="text-muted">إحداثيات الموقع غير متوفرة حالياً</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="contact-actions mt-4">
                        <a href="{{ route('city.contact.form', $city['slug']) }}" class="btn btn-primary">
                            <i class="fas fa-envelope"></i>
                            إرسال رسالة إلى فرع {{ $city['name'] }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .city-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .city-flag-large {
            width: 60px;
            height: 45px;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .page-title {
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .page-description {
            color: #666;
            font-size: 1.1rem;
        }

        .contact-form-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .contact-form-section h2 {
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .contact-info-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .contact-info-section h3 {
            color: var(--main-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .contact-details {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            border-color: var(--main-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: var(--main-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .contact-content h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            color: #333;
        }

        .contact-content a,
        .contact-content p {
            margin: 0;
            color: #666;
            text-decoration: none;
        }

        .contact-content a:hover {
            color: var(--main-color);
        }

                 .map-container {
             margin: 1rem 0;
             border-radius: 8px;
             overflow: hidden;
             box-shadow: 0 2px 8px rgba(0,0,0,0.1);
             width: 100%;
             min-height: 350px;
         }

        .map-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--main-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .map-link:hover {
            color: #013a5f;
            text-decoration: none;
        }

        .map-link i {
            font-size: 0.8rem;
        }

        .contact-actions {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }

        .contact-actions .btn {
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 500;
        }

        .contact-actions .btn i {
            margin-left: 8px;
        }

        @media (max-width: 768px) {
            .contact-info-section {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
