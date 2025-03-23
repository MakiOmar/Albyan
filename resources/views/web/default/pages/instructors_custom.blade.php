@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush


@section('content')

<div class="container text-center mt-5">
    <!-- CEO Section -->
    <h2 class="fw-bold">CEO</h2>
    <br>
    <div class="row justify-content-center">
        <div class="col-6 col-md-3">
            <a href="/profile/ceo1">
                <img src="/store/1/Instructors Profiles/RaghebFouda.png" class="rounded-circle border border-warning p-1" width="120" height="120" alt="CEO 1">
                <center><strong>Mr.Ragheb Fouda</strong></center>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="/profile/ceo2">
                <img src="/store/1/Instructors Profiles/AhmedYounes.png" class="rounded-circle border border-warning p-1" width="120" height="120" alt="CEO 2">
                <center><strong>Mr.Ahmed Younes</strong></center>
            </a>
        </div>
    </div>

    <hr class="my-4">

    <!-- Instructors Section -->
    <h3 class="fw-bold">Instructors</h3>
    <br>
    <div class="row justify-content-center">
        <div class="col-4 col-md-2">
            <a href="/profile/instructor1">
                <img src="/store/1/Instructors Profiles/Dr.Nour.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 1">
            </a>
            <center><strong>د. نور صالح</strong></center>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/instructor2">
                <img src="/store/1/Instructors Profiles/Dr.MahmoudAbo-Amera.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 2">
            </a>
            <center><strong>د. محمود أبو عميرة</strong></center>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/instructor3">
                <img src="/store/1/Instructors Profiles/Dr.Fatma.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
            <center><strong>د فاطمة احمد</strong></center>
        </div>
    </div>

    <hr class="my-4">

    <!-- Albayan Team Section -->
    <h3 class="fw-bold">Albyan Team</h3>
    <br>
    <div class="row justify-content-center">
        <div class="col-4 col-md-3">
            <a href="/profile/team1">
                <img src="/store/1/Instructors Profiles/GehadSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 1">
            </a>
            <center><strong>جهاد ابراهيم</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="/profile/team2">
                <img src="/store/1/Instructors Profiles/ManarSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 2">
            </a>
            <center><strong>منار محمد</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="/profile/team3">
                <img src="/store/1/Instructors Profiles/MariSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 3">
            </a>
            <center><strong>مارى الحركة</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="/profile/team4">
                <img src="/store/1/Instructors Profiles/SamehaSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>سميحة محمد</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="/profile/team4">
                <img src="/store/1/Instructors Profiles/MahmoudAccounting.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>محمود عبدالسلام</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="/profile/team4">
                <img src="/store/1/Instructors Profiles/MohamedSamirSales.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
            <center><strong>محمد سمير</strong></center>
        </div>
        <div class="col-4 col-md-3">
            <a href="/profile/team4">
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
