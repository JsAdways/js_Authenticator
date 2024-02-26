<?php

namespace Js\Authenticator\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Hash;
use Cache;
use Exception;
use Log;
use Js\Authenticator\Contracts\AuthContract;

class AuthService implements AuthContract
{
    const TOKEN_CACHE = 'LOGIN_';

    /**
     * 登入
     *
     * @param string $account
     * @param int $password
     * @return array
     * @throws Exception
     */
    public function login(string $account, string $password): array
    {
        $key = Crypt::encryptString(json_encode(['account' => $account, 'password' => $password]));
        $host = config('js_auth.host');
        $url = "${host}/service/api/login";

        $response = Http::accept('application/json')->post($url, ['key' => $key]);

        if ($response->failed()) {
            ['status_code' => $status_code, 'message' => $message] = $response->json();
            throw new Exception("登入失敗 - 帳號：${account} 原因：${message}");
        }

        ['data' => $user_data] = $response->json();

        $expiration_time = now()->addMinutes(config('js_auth.expiration_time'));
        $user_info['expiration_time'] = $expiration_time->toDateTimeString();
 
        Cache::put(self::TOKEN_CACHE.$user_data['token'], $user_data['user'], $expiration_time);

        return $user_data;
    }

    /**
     * 取得使用者系統權限
     *
     * @param string $token
     * @param int $system_id
     * @return array
     * @throws Exception
     */
    public function get_permission(string $token, int $system_id): array
    {
        if (Cache::has($token)) {
            $user_info = Cache::get($token);
        } else {
            $host = config('js_auth.host');
            $url = "${host}/service/api/permission";

            $response = Http::withToken($token)->accept('application/json')->post($url, [
                'id' => $system_id
            ]);

            if ($response->failed()) {
                ['status_code' => $status_code, 'message' => $message] = $response->json();
                throw new Exception("登入失敗 - 帳號：${account} 原因：${message}");
            }
            $json = $response->json();
            $user_info = $json['data'];
            $user_info['token'] = $token;
            $expiration_time = now()->addMinutes(config('js_auth.expiration_time'));
            $user_info['expiration_time'] = $expiration_time->toDateTimeString();
       
            Cache::put($token, $user_info, $expiration_time);
        }

        return $user_info;
    }

    /**
     * 使用者權限驗證
     *
     * @param string $token
     * @return int 使用者 id
     * @throws Exception
     */
    public function verify_token(string $token): int
    {
        try {
            if (!Cache::has(self::TOKEN_CACHE.$token)) {
                throw new Exception('驗證失效');
            }
            $user = Cache::get(self::TOKEN_CACHE.$token);
            return $user['id'];
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 取得使用者系統權限
     *
     * @param string $token
     * @param int $system_id
     * @return array
     * @throws Exception
     */
    public function logout(string $token): bool
    {
        return Cache::forget(self::TOKEN_CACHE.$token);
    }
}