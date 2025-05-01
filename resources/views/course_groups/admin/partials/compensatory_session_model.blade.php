<!-- مودال إضافة جلسة تعويضية -->
<div class="modal fade" id="makeupSessionModal" tabindex="-1" role="dialog" aria-labelledby="makeupSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="makeup-session-form" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="makeupSessionModalLabel">إضافة جلسة تعويضية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- للمجموعات Zoom -->
                    <div id="zoom-confirm-message" style="display: none;">
                        <p class="text-danger font-weight-bold">
                            سيتم تمديد نهاية المجموعة تلقائيًا وإضافة جلسة جديدة على Zoom بعد آخر جلسة.
                        </p>
                    </div>

                    <!-- للمجموعات Offline -->
                    <div id="offline-inputs" style="display: none;">
                        <div class="form-group">
                            <label for="makeup_date">التاريخ</label>
                            <input type="date" class="form-control" name="date" id="makeup_date">
                        </div>
                        <div class="form-group">
                            <label for="makeup_time">الوقت</label>
                            <input type="time" class="form-control" name="time" id="makeup_time">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>اسم المجموعة</label>
                        <p id="groupName" class="font-weight-bold mb-0"></p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">إضافة الجلسة</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </form>
    </div>
</div>
