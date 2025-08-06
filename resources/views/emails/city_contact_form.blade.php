<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة جديدة من نموذج الاتصال</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .city-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-data {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-field {
            margin-bottom: 15px;
        }
        .form-field label {
            font-weight: bold;
            color: #007bff;
            display: block;
            margin-bottom: 5px;
        }
        .form-field value {
            background-color: #ffffff;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ddd;
            display: block;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>رسالة جديدة من نموذج الاتصال</h1>
        </div>

        <div class="city-info">
            <h3>المدينة: {{ $city['name'] }}</h3>
            @if($city['flag'])
                <img src="{{ url($city['flag']) }}" alt="{{ $city['name'] }}" style="width: 30px; height: 20px; margin-right: 10px;">
            @endif
        </div>

        <div class="form-data">
            <h3>بيانات المرسل:</h3>
            
            <div class="form-field">
                <label>الاسم الكامل:</label>
                <value>{{ $formData['full_name'] }}</value>
            </div>

            <div class="form-field">
                <label>رقم الهاتف:</label>
                <value>{{ $formData['phone'] }}</value>
            </div>

            <div class="form-field">
                <label>البريد الإلكتروني:</label>
                <value>{{ $formData['email'] }}</value>
            </div>
        </div>

        <div class="footer">
            <p>تم إرسال هذه الرسالة من نموذج الاتصال الخاص بمدينة {{ $city['name'] }}</p>
            <p>تاريخ الإرسال: {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html> 