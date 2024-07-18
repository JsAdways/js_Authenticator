<?php

namespace Js\Authenticator\Contracts;

use Exception;

interface AuthContract
{
    public function login(string $account, string $password): array|Exception;
    public function get_permission(string $token, int $system_id): array|Exception;
    public function clear_permission_cache(string $token): bool|Exception;
    public function verify_token(string $token): int|Exception;
    public function logout(string $token): bool|Exception;
    public function get_data_with_id(int $user_id): array|Exception;
}