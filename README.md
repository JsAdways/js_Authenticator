## Js 使用者驗證/JsAuthenticate
JsAuthenticate 是 Laravel 框架套件，用於使用者認證管理。

## 功能
* 登入 API
* 使用者 Middleware 驗證 

## 安裝
1. 下載套裝
```
composer require jsadways/authenticator
```
2. 將套件可設定配置複製到專案
```
php artisan vendor:publish --provider="Js\Authenticator\Providers\AuthServiceProvider"
```
3. 在專案 .env 添加帳號驗證網址與前端網址
```
JS_AUTH_HOST='http://authenticate.tw'
FORESTAGE_URL='http://172.16.1.156:3100/struct'
```

## 使用
* 套件有提供 Middleware 驗證功能，名稱為 js-authenticate-middleware-alias，在進行驗證成功之後會在請求加入 user_id。
```
// 在需驗證位置加入 js-authenticate-middleware-alias 中間件
Route::middleware(['js-authenticate-middleware-alias'])->group(function () {
    // 路徑
});
```

