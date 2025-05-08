<div id="offline-repeater-wrapper" class="form-group col-md-6">
    <h6>مواعيد مخصصة</h6>
    <div id="manual-occurrences">
        <div class="row occurrence-row mb-2 position-relative">
            <div class="col-md-4">
                <label>التاريخ</label>
                <input type="date" name="manual_occurrences[0][date]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>الوقت</label>
                <input type="time" name="manual_occurrences[0][time]" class="form-control" value="10:00" required>
            </div>
            <div class="col-md-4">
                <label>مدة المحاضرة</label>
                <input type="number" name="manual_occurrences[0][duration]" class="form-control" placeholder="المدة بالدقائق" min="1" value="1" required>
            </div>
            <div class="position-absolute" style="left:-30px;bottom: 1.5px;">
                <button type="button" class="btn btn-danger remove-occurrence">X</button>
            </div>
        </div>
    </div>
    <button type="button" id="addOccurrence" class="btn btn-secondary mt-2">إضافة موعد آخر</button>
</div>

