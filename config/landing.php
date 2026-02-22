<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Landing page form ID
    |--------------------------------------------------------------------------
    | The form ID used for the public /landing page. Set LANDING_FORM_ID in .env
    | to the form's ID from the dashboard (Forms list). Leave null to disable.
    */

    'form_id' => env('LANDING_FORM_ID'),

    /*
    |--------------------------------------------------------------------------
    | Landing WhatsApp and Call numbers (explicit in code / .env)
    |--------------------------------------------------------------------------
    | Numbers for the large WhatsApp and Call buttons on the landing page.
    | WhatsApp: digits only with country code (e.g. 966501234567).
    | Call: any format (e.g. +966501234567). Leave null to hide a button.
    */

    'whatsapp_number' => env('LANDING_WHATSAPP_NUMBER'),
    'call_number' => env('LANDING_CALL_NUMBER'),

];
