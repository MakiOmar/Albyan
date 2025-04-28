<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تنبيه: مجموعة على وشك الانتهاء</title>
</head>
<body style="font-family: 'Tajawal', Arial, sans-serif; background-color: #f7f7f7; padding: 20px;direction:rtl">

    <div style="max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

        <h2 style="color: #333;">🚨 إشعار هام</h2>

        <p>مرحباً،</p>

        <p>نود إعلامكم بأن المجموعة التالية أوشكت على الانتهاء:</p>

        <ul style="list-style: none; padding: 0;">
            <li><strong>اسم الدورة:</strong> {{ $group->webinar->title ?? '-' }}</li>
            <li><strong>اسم المعلم:</strong> {{ $group->instructor->full_name ?? '-' }}</li>
            <li><strong>رقم المجموعة:</strong> {{ $group->id }}</li>
            <li><strong>تاريخ آخر جلسة:</strong> 
                @php
                    $meetingJson = json_decode($group->meeting_json, true);
                    $lastDate = '-';
                    if (!empty($meetingJson['occurrences'])) {
                        $lastDate = \Carbon\Carbon::parse(collect($meetingJson['occurrences'])->pluck('start_time')->sortDesc()->first())->timezone('Asia/Dubai')->format('Y-m-d');
                    } else {
                        $lastDate = \Carbon\Carbon::parse($group->meeting_end_time)->timezone('Asia/Dubai')->format('Y-m-d');
                    }
                @endphp
                {{ $lastDate }}
            </li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ env('APP_URL') }}/admin/course-group/view/{{ $group->id }}" target="_blank" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                مشاهدة تفاصيل المجموعة
            </a>
        </div>

        <p style="margin-top: 30px;">مع تحياتنا،<br><strong>فريق الإدارة</strong></p>

    </div>

</body>
</html>