<?php

namespace app\api\service\login;

use app\deshang\base\BaseService;

use app\deshang\service\user\DeshangUserService;
use app\api\service\system\SysSmsService;

use app\deshang\exceptions\CommonException;
use app\common\dao\user\UserDao;
class RegisterService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function registerNormal($data){
        // 共用一个注册方法，与后台注册方法一致
        $user_id = (new DeshangUserService())->createUser($data);

        // 返回登录信息
        $user_info = (new UserDao())->getUserInfoById($user_id);
        $result = (new LoginService())->loginAfter($user_info);
        
        return $result;
    }

    public function registerMobile($data){

        // 验证验证码
        $result = (new SysSmsService())->checkSms('user_register', $data['mobile'], $data['code']);
        if (!$result) {
            throw new CommonException('验证码错误');
        }
        // 手机号注册
        $data['mobile_bind'] = 1;
        $data['mobile'] = $data['mobile'];

        // 检查手机号是否已注册
        $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile']]);
        if ($user) {
            throw new CommonException('手机号已注册');
        }


        // 共用一个注册方法，与后台注册方法一致
        $user_id = (new DeshangUserService())->createUser($data);

        // 返回登录信息
        $user_info = (new UserDao())->getUserInfoById($user_id);
        $result = (new LoginService())->loginAfter($user_info);


        return $result;
    }





}