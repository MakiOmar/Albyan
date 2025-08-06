# City Contact Form System

## Overview
This system provides a configurable city-based contact form with a floating vertical bar on the left side of the website. Users can click on city names to access contact forms specific to each city.

## Features

### 1. JSON-Based Configuration System
- All city configurations are stored in `storage/app/city_contact.json`
- No database migrations required
- Easy to manage through admin panel
- Human-readable JSON format

### 2. Floating City Bar
- Vertical floating bar on the left side of the page
- Shows all active cities with flags
- Responsive design (horizontal on mobile)
- Smooth animations and hover effects

### 3. City-Specific Contact Forms
- Each city has its own contact form
- Arabic language support
- Form validation with error messages
- AJAX submission with loading states

### 4. Email Notifications
- Automatic email sending to configured city emails
- Professional email template
- Includes form data and city information

### 5. Admin Management
- Admin panel to manage cities and configurations
- Add, edit, and delete cities
- Configure form and email settings
- Enable/disable cities

## File Structure

```
storage/app/
├── city_contact.json                   # Main configuration file

app/Http/Controllers/
├── Web/CityContactController.php       # Frontend controller
└── Admin/CityContactController.php     # Admin controller

resources/views/
├── web/default/
│   ├── city_contact/
│   │   └── form.blade.php              # Contact form view
│   └── partials/
│       └── floating_city_bar.blade.php # Floating bar component
├── admin/
│   └── city_contact/
│       └── index.blade.php             # Admin management view
└── emails/
    └── city_contact_form.blade.php     # Email template

routes/
├── web.php                             # Frontend routes
└── admin.php                           # Admin routes

app/Helpers/
└── helper.php                          # Helper functions
```

## Configuration

### City Configuration
Edit `storage/app/city_contact.json` to add or modify cities:

```json
{
    "cities": [
        {
            "name": "الرياض",
            "slug": "riyadh",
            "email": "riyadh@example.com",
            "flag": "/assets/default/img/flags/sa.png",
            "is_active": true
        }
    ]
}
```

### Form Settings
```json
{
    "form": {
        "title": "تواصل معنا",
        "description": "يرجى ملء النموذج أدناه وسنقوم بالرد عليك في أقرب وقت ممكن",
        "success_message": "تم إرسال رسالتك بنجاح! سنقوم بالرد عليك قريباً.",
        "error_message": "حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى."
    }
}
```

### Email Settings
```json
{
    "email": {
        "subject": "رسالة جديدة من نموذج الاتصال - :city",
        "template": "emails.city_contact_form"
    }
}
```

## Routes

### Frontend Routes
- `GET /contact/{citySlug}` - Show city contact form (`city.contact.form`)
- `POST /contact/{citySlug}/submit` - Submit contact form (`city.contact.submit`)
- `GET /api/cities` - Get active cities (API) (`city.contact.cities`)
- `GET /api/city-contact/config` - Get complete JSON configuration (`city.contact.config`)

### Admin Routes
- `GET /admin/city-contact` - Admin management page (`admin.city-contact.index`)
- `POST /admin/city-contact/config/update` - Update form/email settings (`admin.city-contact.config.update`)
- `POST /admin/city-contact/cities/add` - Add new city (`admin.city-contact.cities.add`)
- `POST /admin/city-contact/cities/{index}/update` - Update city (`admin.city-contact.cities.update`)
- `GET /admin/city-contact/cities/{index}/delete` - Delete city (`admin.city-contact.cities.delete`)

## Helper Functions

### getCityContactConfig($key = null)
Get city contact configuration or specific key.

### getActiveCities()
Get all active cities sorted by name.

### getCityBySlug($slug)
Get specific city by slug.

### saveCityContactConfig($data)
Save city contact configuration to JSON file.

## Usage

### Adding a New City

#### Via Admin Panel:
1. Go to Admin Panel → City Contact Management (إدارة الاتصال بالمدن)
2. Click "إضافة مدينة جديدة"
3. Fill in city details:
   - Name (Arabic)
   - Slug (URL-friendly)
   - Email address
   - Flag path (optional)
4. Save

**Note:** The City Contact Management menu appears in the admin sidebar under the Content section.

#### Via Command Line:
```bash
# Interactive mode
php artisan city-contact:manage add

# With options
php artisan city-contact:manage add --name="الرياض" --slug="riyadh" --email="riyadh@example.com" --flag="/assets/default/img/flags/sa.png" --active=1
```

### Customizing the Form
1. Go to Admin Panel → City Contact Management
2. Edit form settings in the left panel
3. Edit email settings in the right panel
4. Save changes

### Command Line Management

The system includes an Artisan command for managing cities:

```bash
# List all cities
php artisan city-contact:manage list

# Add a new city
php artisan city-contact:manage add

# Edit an existing city
php artisan city-contact:manage edit

# Delete a city
php artisan city-contact:manage delete

# Show complete configuration
php artisan city-contact:manage show
```

### Adding Flags
1. Upload flag images to `public/assets/default/img/flags/`
2. Update city configuration with flag path
3. Flags will automatically display in the floating bar

## Styling

The floating bar uses CSS with:
- Fixed positioning on the left side
- Gradient background
- Smooth hover animations
- Responsive design for mobile
- Custom scrollbar styling

## Email Template

The email template includes:
- Professional layout
- City information with flag
- Form data (name, phone, email)
- Timestamp
- Responsive design

## Security Features

- CSRF protection on all forms
- Input validation and sanitization
- Email validation
- Rate limiting (can be added)
- XSS protection

## Future Enhancements

1. **Multilingual Support**
   - Add language files for different languages
   - Dynamic language switching

2. **Advanced Features**
   - File uploads
   - Custom form fields
   - Analytics tracking
   - Spam protection

3. **Integration**
   - CRM integration
   - SMS notifications
   - WhatsApp integration

## Troubleshooting

### Floating Bar Not Showing
- Check if cities are configured and active
- Verify the partial is included in the layout
- Check browser console for JavaScript errors

### Emails Not Sending
- Verify email configuration in `.env`
- Check mail server settings
- Verify city email addresses are correct

### Form Not Submitting
- Check CSRF token
- Verify route configuration
- Check browser console for AJAX errors

## Support

For issues or questions, please check:
1. Laravel logs in `storage/logs/`
2. Browser developer tools
3. Email server logs
4. Configuration file syntax 