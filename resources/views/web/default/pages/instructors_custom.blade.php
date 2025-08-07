@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush


@section('content')

<div class="container text-center mt-5">
    <svg width="100" height="170" style="position: absolute;right: 0; top: 100px" viewBox="0 0 148 270" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_f_167_3760)">
        <path d="M52.1411 230.757C25.347 227.929 11.6628 197.155 27.5096 175.365L129.483 35.148C145.33 13.358 178.822 16.8939 189.77 41.5126L260.215 199.933C271.162 224.551 251.353 251.789 224.559 248.96L52.1411 230.757Z" fill="#BFE3C6" fill-opacity="0.5"/>
        </g>
        <defs>
        <filter id="filter0_f_167_3760" x="0.772705" y="0.729614" width="282.514" height="268.434" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
        <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3760"/>
        </filter>
        </defs>
    </svg>

    <svg width="100" height="170" style="position: absolute;left: 0; top: 300px"  viewBox="0 0 142 280" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_f_167_3759)">
        <path d="M-113.227 62.6108C-118.944 36.2813 -94.0145 13.6367 -68.3541 21.8506L96.7692 74.7063C122.43 82.9202 129.576 115.832 109.632 133.948L-18.704 250.521C-38.6477 268.636 -70.7232 258.369 -76.44 232.04L-113.227 62.6108Z" fill="#BFE3C6" fill-opacity="0.5"/>
        </g>
        <defs>
        <filter id="filter0_f_167_3759" x="-134.053" y="0.140381" width="275.167" height="279.501" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
        <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3759"/>
        </filter>
        </defs>
    </svg>
    



    <svg width="90" height="180" style="position: absolute;right: 0; top: 80%" viewBox="0 0 106 284" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g filter="url(#filter0_f_167_3758)">
    <path d="M21.8445 54.8684C22.0543 27.9262 51.3513 11.3145 74.579 24.9673L224.048 112.822C247.275 126.475 247.013 160.153 223.575 173.442L72.7564 258.959C49.3188 272.248 20.2842 255.182 20.494 228.24L21.8445 54.8684Z" fill="#BFE3C6" fill-opacity="0.5"/>
    </g>
    <defs>
    <filter id="filter0_f_167_3758" x="0.492676" y="0.0892334" width="260.819" height="283.476" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
    <feFlood flood-opacity="0" result="BackgroundImageFix"/>
    <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
    <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3758"/>
    </filter>
    </defs>
    </svg>
    <svg width="100" height="170" style="position: absolute;left: 0; top: 100%" viewBox="0 0 148 270" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_f_167_3761)">
        <path d="M-83.899 228.35C-110.64 225.055 -123.786 194.048 -107.563 172.537L-3.16485 34.1157C13.0588 12.6048 46.4848 16.723 57.002 41.5285L124.68 201.15C135.197 225.956 114.917 252.845 88.1764 249.55L-83.899 228.35Z" fill="#BFE3C6" fill-opacity="0.5"/>
        </g>
        <defs>
        <filter id="filter0_f_167_3761" x="-134.66" y="0.185478" width="282.168" height="269.64" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
        <feGaussianBlur stdDeviation="10" result="effect1_foregroundBlur_167_3761"/>
        </filter>
        </defs>
    </svg>
    <!-- CEO Section -->
    <h2 class="fw-bold section-title-bg p-2">المدير التنفيذي</h2>
    <br>
    <div class="row justify-content-center">
        @forelse($ceoUsers as $ceoUser)
            <div class="col-6 col-md-3">
                <a href="{{ $ceoUser->getProfileUrl() }}">
                    <img src="{{ $ceoUser->getAvatar(120) }}" class="rounded-circle border border-warning p-1" width="120" height="120" alt="{{ $ceoUser->full_name }}">
                    <center><strong>{{ $ceoUser->full_name }}</strong></center>
                </a>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">No CEO users found</p>
            </div>
        @endforelse
    </div>

    <hr class="my-4">

    <!-- Instructors Section -->
    <h3 class="fw-bold section-title-bg p-2">المدربين</h3>
    <br>
    <div class="row justify-content-center">
        @forelse($instructors as $instructor)
            <div class="col-4 col-md-3 mt-2">
                <a href="{{ $instructor->getProfileUrl() }}">
                    <img src="{{ $instructor->getAvatar(100) }}" class="rounded-circle bg-dark p-1" width="100" height="100" alt="{{ $instructor->full_name }}">
                </a>
                <center><strong>{{ $instructor->full_name }}</strong></center>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">No instructors found</p>
            </div>
        @endforelse
    </div>

    <hr class="my-4">

    <!-- Albayan Team Section -->
    <h3 class="fw-bold section-title-bg p-2">فريق البيان</h3>
    <br>
    <div class="row justify-content-center">
        @forelse($teamMembers as $teamMember)
            <div class="col-4 col-md-3">
                <a href="{{ $teamMember->getProfileUrl() }}">
                    <img src="{{ $teamMember->getAvatar(100) }}" class="rounded-circle bg-dark p-1" width="100" height="100" alt="{{ $teamMember->full_name }}">
                </a>
                <center><strong>{{ $teamMember->full_name }}</strong></center>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">No team members found</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>
    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
    </script>
    <script src="/assets/default/js/parts/contact.min.js"></script>
@endpush
