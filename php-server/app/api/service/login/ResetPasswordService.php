<?php

namespace app\api\service\login;

use app\deshang\base\BaseService;

use app\deshang\service\user\DeshangUserService;
use app\api\service\system\SysSmsService;

use app\deshang\exceptions\CommonException;

use app\common\dao\user\UserDao;

// 重置密码服务
class ResetPasswordService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    // 手机号重置密码
    public function resetPasswordMobile($data){

        // 验证手机号是否存在
        $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile']]);
        if (empty($user)) {
            throw new CommonException('手机号不存在');
        }

        // 验证验证码
        $result = (new SysSmsService())->checkSms('user_reset_password', $data['mobile'], $data['code']);
        if (!$result) {
            throw new CommonException('验证码错误');
        }

        // 更新密码
        $update_data = [
            'password' => $data['password'],
        ];
        $result = (new DeshangUserService())->updateUser($user['id'],$update_data);

        return $result;
    }




}