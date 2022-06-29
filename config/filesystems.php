<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),
    'wamas' => env('WAMAS_FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 'ftp'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'wamas_sim' => [
            'driver' => 'local',
            'root' => storage_path('app/'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
        
        'ftpwamas' => [

            'driver' => 'ftp',
            'host' => env('WAMAS_FTP_HOST'),
            'username' => env('WAMAS_FTP_USERNAME'),
            'password' => env('WAMAS_FTP_PASSWORD'),
            'cache' => [
                'store' => 'memcached',
                'expire' => 600,
                'prefix' => 'cache-prefix',
            ]
            // Optional FTP Settings...
            // 'port' => 21,
            // 'root' => '',
            // 'passive' => true,
            // 'ssl' => true,
            // 'timeout' => 30,
        ],

        'sftpwamas' => [
            'driver' => 'sftp',
            'host' => env('WAMAS_SFTP_HOST'),
            'username' => env('WAMAS_SFTP_USERNAME'),
            'password' => env('WAMAS_SFTP_PASSWORD'),
            'cache' => [
                'store' => 'memcached',
                'expire' => 600,
                'prefix' => 'cache-prefix',
            ],
            'root' => '/garcia/',
            // Settings for SSH key based authentication...
            // 'privateKey' => '/path/to/privateKey',
            // 'password' => 'encryption-password',

            // Optional SFTP Settings...
            // 'port' => 22,
            // 'root' => '',
            // 'timeout' => 30,
        ],

        'omsserver' => [
            'userdatabase' => env('DB_USERNAME'),
            'passdatabase' => env('DB_PASSWORD'),
            'database'     => env('DB_DATABASE'),
            'sudopassword' => (env('APP_ENV') === 'PRODUCTION') ? env('SUDO_SERVER_PASS') : '',
        ]
    ],

];
