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
    <h2 class="fw-bold section-title-bg text-white p-2">المدير التنفيذي</h2>
    <br>
    <div class="row justify-content-center">
        <div class="col-6 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/RaghebFouda.png" class="rounded-circle border border-warning p-1" width="120" height="120" alt="CEO 1">
                <center><strong>Mr.Ragheb Fouda</strong></center>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/AhmedYounes.png" class="rounded-circle border border-warning p-1" width="120" height="120" alt="CEO 2">
                <center><strong>Mr.Ahmed Younes</strong></center>
            </a>
        </div>
    </div>

    <hr class="my-4">

    <!-- Instructors Section -->
    <h3 class="fw-bold section-title-bg text-white p-2">المدربين</h3>
    <br>
    <div class="row justify-content-center">
        <div class="col-4 col-md-3 mt-2">
            <a href="/users/1072/profile">
                <img src="/store/1/Instructors Profiles/Dr.Nour.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 1">
            </a>
            <center><strong>د. نور صالح</strong></center>
        </div>
        <div class="col-4 col-md-3 mt-2">
            <a href="/users/1068/profile">
                <img src="/store/1/Instructors Profiles/Dr.MahmoudAbo-Amera.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 2">
            </a>
            <center><strong>د. محمود أبو عميرة</strong></center>
        </div>
        <div class="col-4 col-md-3 mt-2">
            <a href="/users/1060/profile">
                <img src="/store/1/Instructors Profiles/Dr.Fatma.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د فاطمة احمد</strong></center>
        </div>

        <div class="col-4 col-md-3 mt-2">
            <a href="#">
                <img src="/store/1/Instructors Profiles/WhatsApp Image 2025-04-17 at 11.40.59_ef7d6394.jpg" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د.محمد غانم</strong></center>
        </div>

        <div class="col-4 col-md-3 mt-2">
            <a href="#">
                <img src="/store/1/Instructors Profiles/WhatsApp Image 2025-04-17 at 11.40.59_66c17b90.jpg" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د.ابراهيم رشدي</strong></center>
        </div>
        <div class="col-4 col-md-3 mt-2">
            <a href="#">
                <img src="/store/1/Instructors Profiles/WhatsApp Image 2025-04-17 at 11.40.59_6d26eb9b.jpg" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د.سارة عبدالسميع</strong></center>
        </div>
        <div class="col-4 col-md-3 mt-2">
            <a href="#">
                <img src="/store/1/Instructors Profiles/WhatsApp Image 2025-04-17 at 11.41.00_c958ace3.jpg" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د.يوسف شنودة</strong></center>
        </div>
        <div class="col-4 col-md-3 mt-2">
            <a href="#">
                <img src="/store/1/Instructors Profiles/WhatsApp Image 2025-04-17 at 13.00.25_ba1366e4.jpg" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د.اشرف الصياح</strong></center>
        </div>
    </div>

    <hr class="my-4">

    <!-- Albayan Team Section -->
    <h3 class="fw-bold section-title-bg text-white p-2">فريق البيان</h3>
    <br>
    <div class="row justify-content-center">
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/GehadSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 1">
            </a>
            <center><strong>جهاد ابراهيم</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/ManarSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 2">
            </a>
            <center><strong>منار محمد</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/MariSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 3">
            </a>
            <center><strong>مارى الحركة</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/SamehaSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>سميحة محمد</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/MahmoudAccounting.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>محمود عبدالسلام</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/MohamedSamirSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>محمد سمير</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="#">
                <img src="/store/1/Instructors Profiles/7.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>معاذ خالد</strong></center>
        </div>
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
