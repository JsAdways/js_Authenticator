<?php

return [
    // 驗證網址
    'host' => env('JS_AUTH_HOST'),
    // minutes
    'expiration_time' => env('JS_AUTH_EXPIRATION_TIME', 1200),
];