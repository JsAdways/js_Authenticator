<?php

namespace Js\Authenticator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;
use Js\Authenticator\Contracts\AuthContract;

class AuthService implements AuthContract
{
    const TOKEN_CACHE = 'LOGIN_';

    /**
     * 登入
     *
     * @param string $account
     * @param string $password
     * @return array
     * @throws Exception
     */
    public function login(string $account, string $password): array
    {
        $key = Crypt::encryptString(json_encode(['account' => $account, 'password' => $password]));
        $user_data = $this->_verify_account(account:$account, key:$key);

        $expiration_time = $this->_gen_expiration_time();
        $token_cache = $this->_set_login_cache(token:$user_data['token'],expiration_time: $expiration_time);
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
            $user_info = $this->_get_system_permission(token: $token,system_id: $system_id);
            $expiration_time = $this->_gen_expiration_time();
            $this->_set_permission_cache(token:$token,user_info: $user_info,expiration_time: $expiration_time);
        }

        return $user_info;
    }

    /**
     * 廣播設定使用者系統權限
     *
     * @param string $token
     * @param array $user_info
     * @return void
     */
    public function set_permission(string $token,array $user_info): void
    {
        $expiration_time = $this->_gen_expiration_time();
        $this->_set_login_cache(token:$token,expiration_time: $expiration_time);
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
     * @return bool
     */
    public function logout(string $token): bool
    {
        return Cache::forget(self::TOKEN_CACHE.$token);
    }

    /**
     * 寫入登入快取
     *
     * @param string $token
     * @param Carbon $expiration_time
     * @return bool
     */
    protected function _set_login_cache(string $token,Carbon $expiration_time): bool
    {
        $login_info = [
            'expiration_time' => $expiration_time->toDateTimeString(),
        ];
        return Cache::put(self::TOKEN_CACHE.$token, $login_info, $expiration_time);
    }

    /**
     * 寫入權限快取
     *
     * @param string $token
     * @param array $user_info
     * @param Carbon $expiration_time
     * @return void
     */
    protected function _set_permission_cache(string $token,array $user_info,Carbon $expiration_time): void
    {
        $user_info['token'] = $token;
        $user_info['expiration_time'] = $expiration_time->toDateTimeString();

        Cache::put($token, $user_info, $expiration_time);
        $user_id = $user_info['user']['id'];
        Cache::put("user-{$user_id}", $token, $expiration_time);
    }

    /**
     * 產出快取過期時間
     *
     * @return Carbon
     */
    protected function _gen_expiration_time(): Carbon
    {
        return now()->addMinutes(config('js_auth.expiration_time'));
    }

    /**
     * 向HR取得系統權限資料
     *
     * @param string $token
     * @param int $system_id
     * @return array
     * @throws Exception
     */
    protected function _get_system_permission(string $token, int $system_id): array
    {
        $host = config('js_auth.host');
        $url = "{$host}/service/api/permission";

        $response = Http::withToken($token)->accept('application/json')->post($url, [
            'id' => $system_id
        ]);

        if ($response->failed()) {
            $response_json = json_encode($response->json(),  JSON_UNESCAPED_UNICODE);
            throw new Exception("登入失敗 - 取得權限失敗失敗, 原因：{$response_json}");
        }

        return $response->json()['data'];
    }

    /**
     * 向HR驗證帳號密碼
     *
     * @param string $account
     * @param string $key
     * @return array
     * @throws Exception
     */
    protected function _verify_account(string $account, string $key): array
    {
        $host = config('js_auth.host');
        $url = "{$host}/service/api/login";

        $response = Http::accept('application/json')->post($url, ['key' => $key]);

        if ($response->failed()) {
            $response_json = json_encode($response->json(), JSON_UNESCAPED_UNICODE);
            throw new Exception("登入失敗 - 帳號：{$account} 原因：{$response_json}");
        }

        ['data' => $user_data] = $response->json();

        return $user_data;
    }
}
