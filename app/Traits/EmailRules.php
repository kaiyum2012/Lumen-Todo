<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait EmailRules
{
    /**
     * @return string[]
     */
    protected function emailRules()
    {
        return ['required', 'email'];
    }

}
