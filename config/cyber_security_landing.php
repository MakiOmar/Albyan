<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cyber Security Diploma landing page form ID
    |--------------------------------------------------------------------------
    | Set CYBER_SECURITY_LANDING_FORM_ID in .env to the form ID from the
    | dashboard (Forms list). Leave null to disable the landing page.
    */

    'form_id' => env('CYBER_SECURITY_LANDING_FORM_ID'),

    /*
    |--------------------------------------------------------------------------
    | Contact numbers
    |--------------------------------------------------------------------------
    | WhatsApp: digits only with country code (e.g. 971569001020).
    | Call: any format. Leave null to hide a button.
    */

    'whatsapp_number' => env('CYBER_SECURITY_LANDING_WHATSAPP_NUMBER'),
    'call_number' => env('CYBER_SECURITY_LANDING_CALL_NUMBER'),

];
