@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">إعدادات نموذج الاتصال بالمدن</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Form Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>إعدادات النموذج</h4>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.city-contact.config.update') }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label>عنوان النموذج</label>
                                                <input type="text" name="form[title]" class="form-control" value="{{ $formConfig['title'] }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>وصف النموذج</label>
                                                <textarea name="form[description]" class="form-control" rows="3" required>{{ $formConfig['description'] }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>رسالة النجاح</label>
                                                <textarea name="form[success_message]" class="form-control" rows="2" required>{{ $formConfig['success_message'] }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>رسالة الخطأ</label>
                                                <textarea name="form[error_message]" class="form-control" rows="2" required>{{ $formConfig['error_message'] }}</textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>إعدادات البريد الإلكتروني</h4>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('admin.city-contact.config.update') }}" method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label>موضوع البريد الإلكتروني</label>
                                                <input type="text" name="email[subject]" class="form-control" value="{{ $emailConfig['subject'] }}" required>
                                                <small class="form-text text-muted">استخدم :city لاستبدال اسم المدينة</small>
                                            </div>
                                            <div class="form-group">
                                                <label>قالب البريد الإلكتروني</label>
                                                <input type="text" name="email[template]" class="form-control" value="{{ $emailConfig['template'] }}" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cities Management -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4>إدارة المدن</h4>
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addCityModal">
                                            إضافة مدينة جديدة
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>الاسم</th>
                                                        <th>الرابط</th>
                                                        <th>البريد الإلكتروني</th>
                                                        <th>العلم</th>
                                                        <th>الحالة</th>
                                                        <th>الإجراءات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($cities as $index => $city)
                                                        <tr>
                                                            <td>{{ $city['name'] }}</td>
                                                            <td><code>{{ $city['slug'] }}</code></td>
                                                            <td>{{ $city['email'] }}</td>
                                                            <td>
                                                                @if($city['flag'])
                                                                    <img src="{{ url($city['flag']) }}" alt="{{ $city['name'] }}" style="width: 30px; height: 20px;">
                                                                @else
                                                                    <span class="text-muted">لا يوجد</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($city['is_active'])
                                                                    <span class="badge badge-success">نشط</span>
                                                                @else
                                                                    <span class="badge badge-secondary">غير نشط</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                                                                                 <button type="button" class="btn btn-sm btn-primary" 
                                                                         onclick="editCity('{{ $city['slug'] }}', '{{ $city['name'] }}', '{{ $city['slug'] }}', '{{ $city['email'] }}', '{{ $city['flag'] }}', {{ $city['is_active'] ? 'true' : 'false' }})">
                                                                     تعديل
                                                                 </button>
                                                                <a href="{{ route('admin.city-contact.cities.delete', $index) }}" 
                                                                   class="btn btn-sm btn-danger"
                                                                   onclick="return confirm('هل أنت متأكد من حذف هذه المدينة؟')">
                                                                    حذف
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add City Modal -->
    <div class="modal fade" id="addCityModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة مدينة جديدة</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.city-contact.cities.add') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>اسم المدينة</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>الرابط (Slug)</label>
                            <input type="text" name="slug" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>مسار العلم (اختياري)</label>
                            <input type="text" name="flag" class="form-control" placeholder="/assets/default/img/flags/sa.png">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit City Modal -->
    <div class="modal fade" id="editCityModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل المدينة</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="editCityForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>اسم المدينة</label>
                            <input type="text" name="name" id="edit_city_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>الرابط (Slug)</label>
                            <input type="text" name="slug" id="edit_city_slug" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" name="email" id="edit_city_email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>مسار العلم (اختياري)</label>
                            <input type="text" name="flag" id="edit_city_flag" class="form-control">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_active" id="edit_city_active" class="custom-control-input">
                                <label class="custom-control-label" for="edit_city_active">نشط</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
<script>
function editCity(slug, name, slugValue, email, flag, isActive) {
    document.getElementById('edit_city_name').value = name;
    document.getElementById('edit_city_slug').value = slugValue;
    document.getElementById('edit_city_email').value = email;
    document.getElementById('edit_city_flag').value = flag;
    document.getElementById('edit_city_active').checked = isActive;
    
    var actionUrl = '/admin/city-contact/cities/' + slug + '/update';
    document.getElementById('editCityForm').action = actionUrl;
    
    $('#editCityModal').modal('show');
}
</script>
@endpush 