<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 0,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('SQS_KEY'),
            'secret' => env('SQS_SECRET'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'your-queue-name'),
            'region' => env('SQS_REGION', 'us-east-1'),
            'group' => 'default',
            'deduplicator' => 'unique',
            'retry_after' => 30,
        ],

        'sqs-fifo' => [
            'driver' => 'sqs-fifo',
            'key' => env('SQS_KEY'),
            'secret' => env('SQS_SECRET'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'your-queue-name'),
            'region' => env('SQS_REGION', 'us-east-1'),
            'group' => 'default',
            'deduplicator' => 'unique',
        ],

        'sqs-stores' => [
            'driver' => 'sqs-plain',
            'key' => env('SQS_STORES_KEY'),
            'secret' => env('SQS_STORES_SECRET'),
            'prefix' => env('SQS_STORES_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_STORES_QUEUE', 'your-queue-name'),
            'region' => env('SQS_STORES_REGION', 'us-east-1'),
            'group' => 'default',
            'deduplicator' => 'unique',
        ],

        'sqs-mirror' => [
            'driver' => 'sqs-plain',
            'key' => env('SQS_MIRROR_KEY'),
            'secret' => env('SQS_MIRROR_SECRET'),
            'prefix' => env('SQS_MIRROR_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_MIRROR_QUEUE', 'your-queue-name'),
            'region' => env('SQS_REGION', 'us-west-2'),
            'group' => 'default',
            'deduplicator' => 'unique',
        ],


        'redis' => [
            'client' => env('REDIS_CLIENT', 'predis'),
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
