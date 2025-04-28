@if ($groupsByInstructor->isNotEmpty())
    <div class="accordion" id="accordionByInstructor">
        @foreach ($groupsByInstructor as $instructorId => $groups)
            @php
                $instructor = $groups->first()->instructor;
                $instructorName = $instructor?->full_name ?? 'غير معروف';
            @endphp

            <div class="card">
                <div class="card-header" id="headingInstructor{{ $instructorId }}">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseInstructor{{ $instructorId }}" aria-expanded="true" aria-controls="collapseInstructor{{ $instructorId }}">
                            المحاضر: {{ $instructorName }}
                        </button>
                    </h2>
                </div>

                <div id="collapseInstructor{{ $instructorId }}" class="collapse show" aria-labelledby="headingInstructor{{ $instructorId }}" data-parent="#accordionByInstructor">
                    <div class="card-body">
                        @foreach ($groups as $group)
                            @include('course_groups.admin.partials.group_item', ['group' => $group]);
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">لا توجد مجموعات متاحة لهذا الويبينار.</div>
@endif
