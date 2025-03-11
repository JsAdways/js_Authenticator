<?php

namespace Js\Authenticator\Contracts;

interface UserContract
{
    public function init(string $token): bool;
    public function get_user(): array;
    public function get_employee(): array;
    public function get_department(): array;
    public function get_system(): array;
}