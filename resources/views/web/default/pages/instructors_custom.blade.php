@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/leaflet/leaflet.css">
@endpush


@section('content')

<div class="container text-center mt-5">
    <!-- CEO Section -->
    <h2 class="fw-bold">CEO</h2>
    <div class="row justify-content-center">
        <div class="col-6 col-md-3">
            <a href="/profile/ceo1">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle border border-warning p-1" width="120" height="120" alt="CEO 1">
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="/profile/ceo2">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle border border-warning p-1" width="120" height="120" alt="CEO 2">
            </a>
        </div>
    </div>

    <hr class="my-4">

    <!-- Instructors Section -->
    <h3 class="fw-bold">Instructors</h3>
    <div class="row justify-content-center">
        <div class="col-4 col-md-2">
            <a href="/profile/instructor1">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 1">
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/instructor2">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 2">
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/instructor3">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Instructor 3">
            </a>
        </div>
    </div>

    <hr class="my-4">

    <!-- Albayan Team Section -->
    <h3 class="fw-bold">Albyan Team</h3>
    <div class="row justify-content-center">
        <div class="col-4 col-md-2">
            <a href="/profile/team1">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 1">
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/team2">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 2">
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/team3">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 3">
            </a>
        </div>
        <div class="col-4 col-md-2">
            <a href="/profile/team4">
                <img src="/store/1/Instructors Profiles/instructor-sample.png" class="rounded-circle bg-dark p-1" width="100" height="100" alt="Team Member 4">
            </a>
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
