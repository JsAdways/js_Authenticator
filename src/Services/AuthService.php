<?php

namespace Js\Authenticator\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Hash;
use Cache;
use Exception;
use Log;
use Carbon\Carbon;
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
        $login_info = [
            'expiration_time' => $expiration_time->toDateTimeString(),
        ];
 
        $token_cache = Cache::put(self::TOKEN_CACHE.$user_data['token'], $login_info, $expiration_time);
        if (!$token_cache) {
            throw new Exception('cache 儲存失敗');
        }

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
                throw new Exception("取得權限失敗失敗 - 原因：${message}");
            }
            $json = $response->json();
            $user_info = $json['data'];
            $user_info['token'] = $token;
            $expiration_time = now()->addMinutes(config('js_auth.expiration_time'));
            $user_info['expiration_time'] = $expiration_time->toDateTimeString();
       
            Cache::put($token, $user_info, $expiration_time);
            $user_id = $user_info['user']['id'];
            Cache::put("user-${user_id}", $token, $expiration_time);
        }

        return $user_info;
    }

    /**
     * 清除使用者權限快取
     *
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public function clear_permission_cache(string $token): bool
    {
        $result = true;

        if (Cache::has($token)) {
            $result = Cache::forget($token);
        } 

        return $result;
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
            $info = Cache::get(self::TOKEN_CACHE.$token);
            $token_is_valid = Carbon::now()->greaterThan(Carbon::parse($info['expiration_time']));
            if ($token_is_valid) {
                throw new Exception('token 已逾期');
            }

            $user_info = Cache::get($token);
            $user_id = $user_info['user']['id'];
    
            return $user_id;
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

    /**
     * 使用取得搖滾與部門資料
     *
     * @param int $user_id
     * @return array
     * @throws Exception
     */
    public function get_data_with_id(int $user_id): array
    {
        try {
            $token = Cache::get("user-${user_id}");
            $token_info = Cache::get($token);

            return [
                'employee_list' => $token_info['employee'],
                'department_list' => $token_info['department'],
            ];
        } catch(Exception $e) {
            Log::notice('js-auth: '.$e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}