<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database backup directory
    |--------------------------------------------------------------------------
    | Absolute path where full mysqldump files are stored. Must be writable by
    | the PHP process. Override with DB_BACKUP_PATH on the server if needed.
    */
    'path' => env('DB_BACKUP_PATH', storage_path('app/backups')),

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    | Keep at most this many backup files. Oldest files are deleted after each
    | successful create.
    */
    'retention' => (int) env('DB_BACKUP_RETENTION', 10),

    /*
    |--------------------------------------------------------------------------
    | Client binaries
    |--------------------------------------------------------------------------
    | Defaults to PATH resolution. Set full paths for WAMP/custom installs, e.g.
    | DB_BACKUP_MYSQLDUMP=C:\wamp64\bin\mysql\mysql8.x.x\bin\mysqldump.exe
    */
    'mysqldump_bin' => env('DB_BACKUP_MYSQLDUMP', 'mysqldump'),
    'mysql_bin' => env('DB_BACKUP_MYSQL', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Restore confirmation phrase
    |--------------------------------------------------------------------------
    | Admin must type this exact phrase (case-sensitive) to confirm a restore.
    */
    'confirm_phrase' => env('DB_BACKUP_CONFIRM_PHRASE', 'RESTORE'),

    /*
    |--------------------------------------------------------------------------
    | Filename pattern
    |--------------------------------------------------------------------------
    | Only files matching this basename pattern are listed / operable.
    */
    'filename_prefix' => 'albyan-backup-',
    'filename_regex' => '/^albyan-backup-\d{8}-\d{6}\.sql$/',

    /*
    |--------------------------------------------------------------------------
    | Automatic backups (Laravel scheduler)
    |--------------------------------------------------------------------------
    | Runtime toggle/interval live in Settings (admin UI). These are defaults
    | used when no setting row exists yet. Requires: php artisan schedule:run
    | via system cron every minute.
    */
    'auto_enabled' => (bool) env('DB_BACKUP_AUTO_ENABLED', false),
    'auto_interval' => env('DB_BACKUP_AUTO_INTERVAL', 'daily'), // hourly|every_6h|daily|weekly
    'auto_intervals' => [
        'hourly' => 'Hourly',
        'every_6h' => 'Every 6 hours',
        'daily' => 'Daily (02:00)',
        'weekly' => 'Weekly (Monday 02:00)',
    ],
];
