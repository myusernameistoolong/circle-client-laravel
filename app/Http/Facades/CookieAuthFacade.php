<?php

namespace App\Http\Facades;

use App\Http\Middleware\CookieAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CookieAuthFacade extends Auth
{
    public static function check()
    {
        if(Cookie::get("username"))
            return true;
        else
            return false;
    }

    public static function user()
    {
        $user = (object) ["name" => Cookie::get("username")];
        return $user;
    }
}
