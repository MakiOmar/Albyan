@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />    @endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-label="إغلاق">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $pageTitle }}</h5>
                <input type="text" id="searchInput" class="form-control w-25" placeholder="Search...">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Webinar</th>
                                <th>Members</th>
                                <th>Start Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            @forelse ($groups as $group)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $group->webinar->title ?? 'N/A' }}</td>
                                    <td><span class="badge bg-success">{{ $group->members->count() }} students</span></td>
                                    <td>{{ \Carbon\Carbon::parse($group->meeting_start_time)->format('d M Y - H:i A') }}</td>
                                    <td>
                                        <a href="{{ route('course-group.view', $group->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-eye"></i>&nbsp;View
                                        </a>
                                        <button class="btn btn-secondary btn-sm add-makeup-session-btn"
                                            data-group-id="{{ $group->id }}"
                                            data-group-name="{{ $group->webinar->title ?? 'دورة بدون اسم' }}"
                                            data-session-type="{{ $group->session_type }}"
                                            data-last-date="{{ optional(collect(json_decode($group->meeting_json, true)['occurrences'] ?? [])->last())['start_time'] ?? '' }}"
                                            data-toggle="modal"
                                            data-target="#makeupSessionModal">
                                        إضافة جلسة تعويضية
                                    </button>
                                       
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted">No groups found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $groups->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @include('course_groups.admin.partials.compensatory_session_model')
    <!-- Fancy Table Search Script -->
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tableBody tr');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/panel/make_next_session.min.js"></script>
@endpush
@include('course_groups.admin.partials.compensatory_session_script')