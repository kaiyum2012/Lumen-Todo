<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;

trait PasswordHelper
{

    /**
     * Generate password using str
     * @param  string  $str
     * @return string
     */
    protected function generatePassword(string $str): string
    {
        return Hash::make($str);
    }

}
