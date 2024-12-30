<div id="groupsAccordion">
    @foreach ($groups as $group)
    <div class="card">
        <div class="card-header d-flex justify-content-between" id="heading{{ $group->id }}">
            <h2 class="mb-0">
                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapse{{ $group->id }}" aria-expanded="false" aria-controls="collapse{{ $group->id }}">
                    المجموعة: {{ $group->id }} - معرف الاجتماع: {{ $group->meeting_id }}
                </button>
            </h2>
        </div>
        <div id="collapse{{ $group->id }}" class="collapse" aria-labelledby="heading{{ $group->id }}" data-parent="#groupsAccordion">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap border-style">
                    <div class="flex-item px-3">
                        <strong>وقت بدء الاجتماع:</strong> {{ $group->nextStartTime ?? 'لا يوجد جلسات قادمة' }}
                    </div>
                    <div class="flex-item px-3">
                        <strong>عدد الطلاب:</strong> {{ $group->members->count() }}
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    @endforeach
</div>