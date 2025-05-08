<div class="form-group col-md-6">
    <label for="manual_occurrences_type">نوع الإدخال</label>
    <small class="form-text text-muted">Choose if date based or week day based.</small>
    <select name="manual_occurrences_type" id="manual_occurrences_type" class="form-control">
        <option value="date">تواريخ</option>
        <option value="day">أيام الأسبوع</option>
    </select>
</div>

<div id="offline-repeater-wrapper" class="form-group col-md-12">
    <h6>المواعيد</h6>
    <div id="manual-occurrences">
        <div class="row occurrence-row mb-2 position-relative">
            <input type="hidden" class="manual_occurrences_type" name="manual_occurrences[0][type]" value="date">
            <!-- نوع: تاريخ -->
            <div class="col-md-4 occurrence-date">
                <label>التاريخ</label>
                <input type="date" name="manual_occurrences[0][date]" class="form-control">
            </div>

            <!-- نوع: يوم من الأسبوع -->
            <div class="col-md-4 occurrence-day" style="display:none;">
                <label>اليوم</label>
                <select name="manual_occurrences[0][day]" class="form-control">
                    <option value="Saturday">السبت</option>
                    <option value="Sunday">الأحد</option>
                    <option value="Monday">الاثنين</option>
                    <option value="Tuesday">الثلاثاء</option>
                    <option value="Wednesday">الأربعاء</option>
                    <option value="Thursday">الخميس</option>
                    <option value="Friday">الجمعة</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>الوقت</label>
                <input type="time" name="manual_occurrences[0][time]" class="form-control" value="10:00">
            </div>
            <div class="col-md-4">
                <label>المدة (بالساعات)</label>
                <input type="number" step="0.1" name="manual_occurrences[0][duration]" class="form-control" placeholder="مثال: 1.5" min="0.1" value="1">
            </div>            

            <div class="position-absolute" style="left:-30px;bottom: 1.5px;">
                <button type="button" class="btn btn-danger remove-occurrence">X</button>
            </div>
        </div>
    </div>

    <button type="button" id="addOccurrence" class="btn btn-secondary mt-2">إضافة موعد آخر</button>
</div>
