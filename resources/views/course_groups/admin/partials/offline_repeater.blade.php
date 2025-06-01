<div class="form-group col-md-6">
    <label for="manual_occurrences_type">نوع الإدخال</label>
    <small class="form-text text-muted">Choose if date based or week day based.</small>
    <select name="manual_occurrences_type" id="manual_occurrences_type" class="form-control">
        <option value="day" {{ $manualType == 'day' ? 'selected' : '' }}>أيام الأسبوع</option>
        <option value="date" {{ $manualType == 'date' ? 'selected' : '' }}>تواريخ</option>
    </select>


</div>

<div id="offline-repeater-wrapper" class="form-group col-md-12">
    <h6>المواعيد</h6>
    @php
        $type = $manualType ?? 'date';
        $occurrences = $meetingJson['occurrences'] ?? [];
        $seenDays = [];
        $filteredOccurrences = [];

        if ($type === 'day') {
            foreach ($occurrences as $occ) {
                $day = $occ['day'] ?? (\Carbon\Carbon::parse($occ['start_time'] ?? '')->englishDayOfWeek ?? null);
                if ($day && !in_array($day, $seenDays)) {
                    $seenDays[] = $day;
                    $filteredOccurrences[] = array_merge($occ, ['day' => $day]);
                }
            }
        } else {
            $filteredOccurrences = $occurrences;
        }
    @endphp

    <div id="manual-occurrences">
        @forelse($filteredOccurrences as $i => $occ)
            @php
                $start = \Carbon\Carbon::parse($occ['start_time'] ?? now('Asia/Dubai'))->timezone('Asia/Dubai');
                $day   = $occ['day'] ?? $start->englishDayOfWeek;
                $date  = $start->format('Y-m-d');
                $time  = $start->format('H:i');
                $duration = isset($occ['duration']) ? round($occ['duration'] / 60, 2) : 1;
            @endphp
            <div class="row occurrence-row mb-2 position-relative">
                <input type="hidden" class="manual_occurrences_type" name="manual_occurrences[{{ $i }}][type]" value="{{ $type }}">

                {{-- التاريخ --}}
                <div class="col-md-4 occurrence-date" style="{{ $type === 'day' ? 'display:none;' : '' }}">
                    <label>التاريخ</label>
                    <input type="date" name="manual_occurrences[{{ $i }}][date]" class="form-control" value="{{ $date }}">
                </div>

                {{-- اليوم --}}
                <div class="col-md-4 occurrence-day" style="{{ $type === 'date' ? 'display:none;' : '' }}">
                    <label>اليوم</label>
                    <select name="manual_occurrences[{{ $i }}][day]" class="form-control">
                        @foreach(['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'] as $dayOption)
                            <option value="{{ $dayOption }}" {{ $dayOption === $day ? 'selected' : '' }}>
                                {{ __('public.'.$dayOption) ?? $dayOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php
                    $timeSlots = [];
                    $start = \Carbon\Carbon::createFromTime(9, 0);
                    $end = \Carbon\Carbon::createFromTime(22, 0);
                    while ($start <= $end) {
                        $timeSlots[] = $start->format('H:i');
                        $start->addMinutes(30);
                    }
                @endphp

                <div class="col-md-4">
                    <label>الوقت</label>
                    <select name="manual_occurrences[{{ $i }}][time]" class="form-control">
                        @foreach($timeSlots as $slot)
                            <option value="{{ $slot }}" {{ $slot == $time ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('h:i A') }}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-4">
                    <label>المدة (بالساعات)</label>
                    <input type="number" step="0.01" name="manual_occurrences[{{ $i }}][duration]" class="form-control" placeholder="مثال: 1.5" min="0.1" value="{{ $duration }}">
                </div>

                <div class="position-absolute" style="left:-30px;bottom: 1.5px;">
                    <button type="button" class="btn btn-danger remove-occurrence">X</button>
                </div>
            </div>
        @empty
            @php $i = 0; @endphp
            <div class="row occurrence-row mb-2 position-relative">
                <input type="hidden" class="manual_occurrences_type" name="manual_occurrences[0][type]" value="{{ $type }}">

                <div class="col-md-4 occurrence-date" style="{{ $type === 'day' ? 'display:none;' : '' }}">
                    <label>التاريخ</label>
                    <input type="date" name="manual_occurrences[0][date]" class="form-control">
                </div>

                <div class="col-md-4 occurrence-day" style="{{ $type === 'date' ? 'display:none;' : '' }}">
                    <label>اليوم</label>
                    <select name="manual_occurrences[0][day]" class="form-control">
                        @foreach(['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'] as $dayOption)
                            <option value="{{ $dayOption }}">{{ __('public.'.$dayOption) ?? $dayOption }}</option>
                        @endforeach
                    </select>
                </div>

                @php
                    $timeSlots = [];
                    $start = \Carbon\Carbon::createFromTime(9, 0);
                    $end = \Carbon\Carbon::createFromTime(22, 0);
                    while ($start <= $end) {
                        $timeSlots[] = $start->format('H:i');
                        $start->addMinutes(30);
                    }
                @endphp
                <div class="col-md-4">
                    <label>الوقت</label>
                    <select name="manual_occurrences[0][time]" class="form-control">
                        @foreach($timeSlots as $slot)
                            <option value="{{ $slot }}">{{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('h:i A') }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>المدة (بالساعات)</label>
                    <input type="number" step="0.01" name="manual_occurrences[0][duration]" class="form-control" placeholder="مثال: 1.5" min="0.1" value="1">
                </div>

                <div class="position-absolute" style="left:-30px;bottom: 1.5px;">
                    <button type="button" class="btn btn-danger remove-occurrence">X</button>
                </div>
            </div>
        @endforelse
    </div>

    <button type="button" id="addOccurrence" class="btn btn-success mt-2">إضافة موعد آخر</button>
</div>
