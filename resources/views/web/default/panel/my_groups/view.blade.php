@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .border-style {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .flex-item {
            padding: 0.5rem;
            border-left: 2px dashed #ccc;
            border-right: 2px dashed #ccc;
            flex: 1;
            text-align: center;
        }
        .flex-item:first-child {
            border-left: none;
        }
        .flex-item:last-child {
            border-right: none;
        }
    </style>
@endpush

@section('content')
<div class="container mt-4">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="contentTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="groups-tab" data-toggle="tab" href="#groups" role="tab" aria-controls="groups" aria-selected="true">المجموعات</a>
        </li>
        @if ( $user->isTeacher() )
            <li class="nav-item">
                <a class="nav-link" id="uploads-tab" data-toggle="tab" href="#uploads" role="tab" aria-controls="uploads" aria-selected="false">رفع الملفات</a>
            </li>
        @endif
    </ul>

    <!-- Tab content -->
    <div class="tab-content mt-3" id="contentTabsContent">
        <!-- Tab: Groups -->
        <div class="tab-pane fade show active" id="groups" role="tabpanel" aria-labelledby="groups-tab">
            <div class="card">
                <div class="card-body">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse{{ $group->id }}" aria-expanded="false" aria-controls="collapse{{ $group->id }}">
                                    المجموعة: {{ $group->id }}
                                    @if($group->session_type === 'zoom')
                                        - معرف الاجتماع: {{ $group->meeting_id }}
                                    @else
                                        - جلسة حضورية
                                    @endif

                                </button>
                            </h2>
                            <div class="mt-20 d-flex flex-column">
                                @if($group->session_type === 'zoom' && $joinUrl)
                                    <a href="{{ $joinUrl }}" class="btn btn-primary">{{ trans('public.join_meeting') }}</a>
                                @endif
                                @if($nextStartTime)
                                
                                <div id="countdown">
                                    <div class="counter">
                                        <span class="number" id="days"></span>
                                        <span class="label">{{ trans('update.day') }}</span>
                                    </div>
                                    <div class="counter">
                                        <span class="number" id="hours"></span>
                                        <span class="label">{{ trans('update.hour') }}</span>
                                    </div>
                                    <div class="counter">
                                        <span class="number" id="minutes"></span>
                                        <span class="label">{{ trans('update.minute') }}</span>
                                    </div>
                                    <div class="counter">
                                        <span class="number" id="seconds"></span>
                                        <span class="label">{{ trans('update.second') }}</span>
                                    </div>
                                </div>
                                @else
                                    <p>ليس لديك جلسات قادمة</p>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap border-style">
                                <div class="flex-item px-3">
                                    <strong>وقت بدء الدورة:</strong><br> {{ $group->meeting_start_time }}
                                </div>
                                <div class="flex-item px-3">
                                    <strong>وقت نهاية الدورة:</strong><br> {{ $group->meeting_end_time }}
                                </div>
                                <div class="flex-item px-3">
                                    <strong>الدبلوم:</strong><br> {{ $group->webinar->title }}
                                </div>
                                <div class="flex-item px-3">
                                    <strong>عدد الطلاب:</strong><br> {{ $group->members->count() }}
                                </div>
                            </div>
                            @if ( $user->isTeacher() )
                            <div class="px-3 mt-4">
                                <h3 class="text-secondary"><i class="fa fa-bookmark"></i> الطلاب</h3>
                                <div class="table-responsive pt-2">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-left">اسم الطالب</th>
                                                <th class="text-left">البريد الإلكتروني</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($group->members as $member)
                                                <tr>
                                                    <td>{{ $member->student->full_name }}</td>
                                                    <td>{{ $member->student->email }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            <div class="px-3 mt-4">
                                <h3 class="text-secondary"><i class="fa fa-bookmark"></i> قائمة المحاضرات</h3>
                                <div class="student-meetings pt-2">
                                    @include('web.default.course.learningPage.components.group_meetings', ['group' => $group, 'occurrences' => $occurrences])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Uploads -->
        <div class="tab-pane fade" id="uploads" role="tabpanel" aria-labelledby="uploads-tab">
            <div class="container">
                <h3>Upload File</h3>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('instructor-files.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                        @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mt-15">
                        <label class="input-label">حدد الملف</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button type="button" class="input-group-text panel-file-manager" data-input="file" data-preview="holder">
                                    <i class="fas fa-upload text-white"></i>
                                </button>
                            </div>
                            <input type="text" name="file" id="file" value="" class="form-control">
                        </div>
                    </div>

                    <input type="hidden" name="webinar_id" value="">
                    <input type="hidden" name="group_id" value="{{ $group->id }}">

                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>

                <hr>

                <h4>Your Uploaded Files</h4>
                <ul class="list-group mt-3">
                    @foreach(\App\Models\InstructorFile::where('instructor_id', auth()->id())->where('group_id', $group->id)->get() as $file)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $file->title }}</strong> <br>
                                <small>{{ $file->path }}</small>
                            </div>
                            <form action="{{ route('instructor-files.destroy', $file->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts_bottom')
@if($nextStartTime)
    <script>
        // Set the countdown date from PHP
        const nextSessionTime = new Date("{{ $nextStartTime }}").getTime();

        // Update the countdown every second
        const countdownInterval = setInterval(() => {
            const now = new Date().getTime(); // Get current time
            const timeRemaining = nextSessionTime - now; // Calculate remaining time

            if (timeRemaining <= 0) {
                clearInterval(countdownInterval); // Stop the countdown when it reaches zero
                document.getElementById("countdown").innerHTML = "بدأت الجلسة!";
                return;
            }

            // Calculate days, hours, minutes, and seconds
            const days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

            // Display the countdown
            document.getElementById("days").innerText = days;
            document.getElementById("hours").innerText = hours;
            document.getElementById("minutes").innerText = minutes;
            document.getElementById("seconds").innerText = seconds;
        }, 1000);
    </script>
@endif
<script src="/assets/default/js/panel/make_next_session.min.js"></script>
@endpush
