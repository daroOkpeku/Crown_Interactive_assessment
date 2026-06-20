<?php

namespace App\Http\Repository\Contracts;

interface AuthRepositoryInterface
{

    public function Register($validated);

    public function login($validated);
    public function forget_password($validated);
    public function  reset_password($validated);
}
