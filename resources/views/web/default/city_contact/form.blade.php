@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <style>
        .city-contact-form {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .city-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        
        .city-name {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }
        
        .city-flag {
            width: 40px;
            height: 30px;
            border-radius: 5px;
            margin-left: 10px;
        }
        
        .form-description {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .form-control.error {
            border-color: #dc3545;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, #0056b3, #004085);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        
        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="city-contact-form">
        <div class="city-header">
            <div class="city-name">
                {{ $city['name'] }}
                @if($city['flag'])
                    <img src="{{ url($city['flag']) }}" alt="{{ $city['name'] }}" class="city-flag">
                @endif
            </div>
            <div class="form-description">
                {{ $formConfig['description'] }}
            </div>
        </div>

        <div class="alert alert-success" id="success-alert">
            {{ $formConfig['success_message'] }}
        </div>

        <div class="alert alert-danger" id="error-alert">
            {{ $formConfig['error_message'] }}
        </div>

        <form id="city-contact-form">
            @csrf
            
            <div class="form-group">
                <label for="full_name" class="form-label">الاسم الكامل *</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required>
                <div class="error-message" id="full_name_error"></div>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">رقم الهاتف *</label>
                <input type="tel" id="phone" name="phone" class="form-control" required>
                <div class="error-message" id="phone_error"></div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">البريد الإلكتروني *</label>
                <input type="email" id="email" name="email" class="form-control" required>
                <div class="error-message" id="email_error"></div>
            </div>

            <button type="submit" class="submit-btn" id="submit-btn">
                إرسال الرسالة
            </button>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>جاري الإرسال...</p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts_bottom')
<script>
$(document).ready(function() {
    $('#city-contact-form').on('submit', function(e) {
        e.preventDefault();
        
        // Reset previous errors
        $('.form-control').removeClass('error');
        $('.error-message').hide();
        $('.alert').hide();
        
        // Show loading
        $('#submit-btn').prop('disabled', true);
        $('#loading').show();
        
        $.ajax({
            url: '{{ route("city.contact.submit", $city["slug"]) }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#success-alert').show();
                    $('#city-contact-form')[0].reset();
                } else {
                    $('#error-alert').show();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        $('#' + field).addClass('error');
                        $('#' + field + '_error').text(messages[0]).show();
                    });
                } else {
                    $('#error-alert').show();
                }
            },
            complete: function() {
                $('#submit-btn').prop('disabled', false);
                $('#loading').hide();
            }
        });
    });
});
</script>
@endpush 