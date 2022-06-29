<?php

return [
    'oms' => [
        'url' => 'http://localhost:3000',
    ],
    'saalma' => [
        'url' => env('APP_ENV') === 'PRODUCTION' ? env('PROD_SAALMA_HOST') : env('DEV_SAALMA_HOST'),
        'user'  => env('APP_ENV') === 'PRODUCTION' ? env('PROD_SAALMA_USER') : env('SAALMA_USER'),
        'pass'  => env('APP_ENV') === 'PRODUCTION' ? env('PROD_SAALMA_PASS') : env('SAALMA_PASS'),
    ],
    'saalma-dev' => [
        'url' => env('DEV_SAALMA_HOST'),
        'user'  => env('SAALMA_USER'),
        'pass'  => env('SAALMA_PASS'),
    ],
    'eks' => [
        'url'   => 'https://dev-api.ccp.tienda',
        'url-dev' => 'https://api.ccp.tienda',
        'productsUrl'   => env('EKS_PRODUCTS_URL', 'https://dev-api.ccp.tienda/products'),
        'user'  => env('EKS_USER'),
        'pass'  => env('EKS_PASS'),
        'usernameAuth' => env('EKS_USER_AUTH'),
        'passwordAuth' => env('EKS_PASS_AUTH'),
        'userEksJda' => env('APP_ENV') == 'PRODUCTION' ? env('EKS_JDA_USER') : env('EKS_JDA_USER_DEV'),
        'passwordEksJda' => env('APP_ENV') == 'PRODUCTION' ? env('EKS_JDA_PASS') : env('EKS_JDA_PASS_DEV'),
    ],
    'eks-products' => [
        'url'   => env('EKS_PRODUCTS_URL', 'https://dev-api.ccp.tienda/products'),
        'user'  => env('EKS_USER'),
        'pass'  => env('EKS_PASS'),
        'usernameAuth' => env('EKS_USER_AUTH'),
        'passwordAuth' => env('EKS_PASS_AUTH'),
    ],

    'jda' => [
        'url'   => 'http://207.248.255.51',
    ]

];
