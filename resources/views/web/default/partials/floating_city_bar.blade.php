@php
    $cities = getActiveCities();
@endphp

@if($cities->count() > 0)
<div id="floating-city-bar" class="floating-city-bar">
    {{--<div class="city-bar-header">
        <span class="city-bar-title">تواصل معنا</span>
    </div>--}}
    
    <div class="city-list">
        @foreach($cities as $city)
            <a href="{{ route('city.contact.form', $city['slug']) }}" 
               class="city-item" 
               data-city="{{ $city['slug'] }}"
               title="{{ $city['name'] }}">
                @if($city['flag'])
                    <div class="flag-circle">
                        <img src="{{ url($city['flag']) }}" alt="{{ $city['name'] }}" class="city-flag">
                    </div>
                @endif
                <span class="city-name">فرع {{ $city['name'] }}</span>
            </a>
        @endforeach
    </div>
</div>

<style>
.floating-city-bar {
    position: fixed;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    border-radius: 0 15px 15px 0;
    z-index: 1000;
    transition: all 0.3s ease;
    max-height: 80vh;
    overflow-y: auto;
}

.floating-city-bar:hover {
    left: 0;
}

.city-bar-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    text-align: center;
    background: linear-gradient(135deg, #136390 0%, #1a7bb8 100%);
    border-radius: 0 15px 0 0;
}

.city-bar-title {
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.city-list {
    padding: 10px 0;
}

.city-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: white !important;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    background: linear-gradient(135deg, #136390 0%, #1a7bb8 100%);
    margin-bottom: 5px;
    height: 45px;
    max-height: 45px;
    border-radius: 0 25px 25px 0;
    box-shadow: 0 2px 10px rgba(19, 99, 144, 0.3);
}

.city-item:hover {
    background: linear-gradient(135deg, #1a7bb8 0%, #136390 100%);
    border-left-color: #ffc107;
    color: white !important;
    text-decoration: none;
    transform: translateX(5px);
}

.city-item:visited {
    color: white !important;
}

.city-item:active {
    color: white !important;
}

.city-item:focus {
    color: white !important;
}

.flag-circle {
    width: 35px;
    height: 35px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-left: 10px;
}

.city-flag {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    object-fit: contain;
}

.city-name {
    font-size: 0.95rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .floating-city-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: auto;
        transform: none;
        border-radius: 15px 15px 0 0;
        max-height: 60vh;
    }
    
    .floating-city-bar:hover {
        left: 0;
        right: 0;
    }
    
    .city-list {
        display: flex;
        overflow-x: auto;
        padding: 10px;
    }
    
    .city-item {
        flex-shrink: 0;
        min-width: 120px;
        margin-right: 10px;
        border-radius: 0 20px 20px 0;
        border-left: none;
        border-bottom: 3px solid transparent;
        color: white !important;
        height: 40px;
        max-height: 40px;
    }
    
    .city-item:hover {
        transform: translateY(-3px);
        border-left-color: transparent;
        border-bottom-color: #ffc107;
        color: white !important;
    }
    
    .city-item:visited {
        color: white !important;
    }
    
    .city-item:active {
        color: white !important;
    }
    
    .city-item:focus {
        color: white !important;
    }
    
    .flag-circle {
        width: 30px;
        height: 30px;
    }
    
    .city-flag {
        width: 16px;
        height: 16px;
    }
    
    .city-name {
        max-width: 80px;
    }
}

/* Scrollbar styling */
.floating-city-bar::-webkit-scrollbar {
    width: 4px;
}

.floating-city-bar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 2px;
}

.floating-city-bar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
}

.floating-city-bar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>

<script>
$(document).ready(function() {
    // Add smooth scrolling for mobile
    if (window.innerWidth <= 768) {
        $('.city-list').on('scroll', function() {
            $(this).addClass('scrolling');
        });
        
        $('.city-list').on('scrollend', function() {
            $(this).removeClass('scrolling');
        });
    }
    
    // Add click tracking if needed
    $('.city-item').on('click', function() {
        var citySlug = $(this).data('city');
        // You can add analytics tracking here
        console.log('City clicked:', citySlug);
    });
});
</script>
@endif 