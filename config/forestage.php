<?php

return [
    'forestage_url' => env('FORESTAGE_URL'),
    'function_permission' => [
        [
            'name' => 'delete',
            'title' => '刪除',
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | 自定義頁面權限
    |--------------------------------------------------------------------------
    | 權限格式
    |   [
    |        'type' => 'page',
    |        'name' => 'index',
    |        'title' => 'Home',
    |        'parent' => 'root',
    |    ]
    |
    */
    'custom_page_permission' => FALSE,
    'page_permission' => [],
];