@extends('web.default.layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h1 class="page-title">فروع البيان</h1>
                    <p class="page-description">اختر المدينة للتواصل مع فرع البيان</p>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($cities as $city)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="city-card">
                        <div class="city-card-header">
                            @if(!empty($city['flag']))
                                <img src="{{ url($city['flag']) }}" alt="{{ $city['name'] }}" class="city-flag">
                            @endif
                            <h3 class="city-name">{{ $city['name'] }}</h3>
                        </div>
                        <div class="city-card-body">
                            @if(!empty($city['phone']))
                                <div class="contact-info">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $city['phone'] }}</span>
                                </div>
                            @endif
                            @if(!empty($city['whatsapp']))
                                <div class="contact-info">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>{{ $city['whatsapp'] }}</span>
                                </div>
                            @endif
                            @if(!empty($city['email']))
                                <div class="contact-info">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ $city['email'] }}</span>
                                </div>
                            @endif
                            @if(!empty($city['address']))
                                <div class="contact-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $city['address'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="city-card-footer">
                            <a href="{{ route('city.contact.show', $city['slug']) }}" class="btn btn-primary">
                                التواصل مع {{ $city['name'] }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }

        .page-title {
            color: var(--main-color);
            margin-bottom: 1rem;
        }

        .page-description {
            color: #666;
            font-size: 1.1rem;
        }

        .city-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }

        .city-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .city-card-header {
            background: var(--main-color);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }

        .city-flag {
            width: 40px;
            height: 30px;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .city-name {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .city-card-body {
            padding: 1.5rem;
        }

        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #555;
        }

        .contact-info i {
            width: 20px;
            margin-left: 10px;
            color: var(--main-color);
        }

        .city-card-footer {
            padding: 1rem 1.5rem 1.5rem;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--main-color);
            border-color: var(--main-color);
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: #013a5f;
            border-color: #013a5f;
        }

        @media (max-width: 768px) {
            .city-card {
                margin-bottom: 1rem;
            }
        }
    </style>
@endsection
