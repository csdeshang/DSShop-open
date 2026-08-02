<?php

namespace app\api\middleware;

use app\Request;



class UserAuthorizeLog
{
    public function handle(Request $request, \Closure $next)
    {

        return $next($request);
    }
}
