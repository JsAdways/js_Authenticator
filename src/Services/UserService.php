<?php

namespace Js\Authenticator\Services;

use Exception;
use Illuminate\Support\Facades\Cache;

final class UserService
{
    private ?string $token = null;

    private ?array $data = null;

    /**
     * 初始化
     *
     * @param string $token
     * @return static
     * @throws Exception
     */
    public function init(string $token): UserService
    {
        $this->set_token(token: $token)
            ->set_data();

        return $this;
    }

    /**
     * 設定 token
     *
     * @param string $token
     * @return static
     */
    private function set_token(string $token): UserService
    {
        $this->token = $token;

        return $this;
    }

    /**
     * 設定 token
     *
     * @return string|null
     */
    public function get_token(): string|null
    {
        return $this->token;
    }

    /**
     * 取得資料
     *
     * @return static
     * @throws Exception
     */
    private function set_data(): UserService
    {
        if (!Cache::has($this->token)) {
            throw new Exception('無 token 相關資料。');
        }

        $this->data =  Cache::get($this->token);

        return $this;
    }

    /**
     * 取得使用者資料
     *
     * @return array
     */
    public function get_user(): array
    {
        return array_key_exists('user', $this->data) ? $this->data['user'] : [];
    }

    /**
     * 取得員工資料
     *
     * @return array
     */
    public function get_employee(): array
    {
        return array_key_exists('employee', $this->data) ? $this->data['employee'] : [];
    }

    /**
     * 取得部門資料
     *
     * @return array
     */
    public function get_department(): array
    {
        return array_key_exists('department', $this->data) ? $this->data['department'] : [];
    }

    /**
     * 取得系統資料
     *
     * @return array
     */
    public function get_system(): array
    {
        return array_key_exists('system', $this->data) ? $this->data['system'] : [];
    }
}
