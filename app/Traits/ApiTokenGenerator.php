<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ApiTokenGenerator
{
    /**
     * Generates Random key of Length 'x'
     * @param  int  $len
     * @return string
     */
    protected function generateApiToken(int $len = 50): string
    {
        return base64_encode(Str::random($len));
    }
}
