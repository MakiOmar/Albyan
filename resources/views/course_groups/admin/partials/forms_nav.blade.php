<ul class="nav">
    <li class="nav-item">
        <a class="nav-link {{ request()->is('admin/course-group/create') ? 'bg-primary text-white' : '' }}" href="{{ route('course-group.create-form') }}">
            إنشاء جدول منتظم
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('admin/course-group/variable-create') ? 'bg-primary text-white' : '' }}" href="{{ route('course-group.create-variable-form') }}">
            إنشاء مواعيد متغيرة
        </a>
    </li>
</ul>

<hr>
<div class="m-2"></div>
