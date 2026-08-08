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
];
