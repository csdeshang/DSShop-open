<?php

namespace app\api\middleware;

use app\Request;
use app\deshang\utils\JwtToken;

/**
 * admin用户登录token验证
 */
class UserAuthorizeToken
{

    public function handle(Request $request, \Closure $next)
    {
        
        //通过配置来设置系统header参数
        $token = $request->header('access-token');
        
        if(strlen($token ?? '') > 0){
            $token_info = (new JwtToken())->validateToken($token);

            // 需要 返回 401 使用 refresh_token 刷新token

            // 判断用户有哪一些身份

            if ($token_info == false) {
                return ds_json_error('登录状态已失效，请重新登录',[],10001,401);
            }

   


            if ($token_info['role'] != 'user'){
                return ds_json_error('身份验证失败，请重新登录',[],10001,403);
            }
            

            $request->user_id = $token_info['user_id']?? 0;
            $request->username = $token_info['username']?? '';
            $request->user_is_enabled = $token_info['user_is_enabled']?? 0;

            $request->rider_id = $token_info['rider_id']?? 0;
            $request->rider_is_enabled = $token_info['rider_is_enabled']?? 0;

            $request->technician_id = $token_info['technician_id']?? 0;
            $request->technician_is_enabled = $token_info['technician_is_enabled']?? 0;

            $request->blogger_id = $token_info['blogger_id']?? 0;
            $request->blogger_is_enabled = $token_info['blogger_is_enabled']?? 0;

        }







        return $next($request);
    }
}
