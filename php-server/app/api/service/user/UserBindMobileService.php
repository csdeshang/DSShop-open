<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\common\dao\user\UserDao;
use app\api\service\system\SysSmsService;

class UserBindMobileService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 绑定手机号（支持首次绑定和更换绑定）
     * @param array $data
     * @return array
     */
    public function bindMobile($data)
    {
        $userInfo = (new UserDao())->getUserInfo(['id' => $this->user_id]);
        
        // 验证短信验证码
        $result = (new SysSmsService())->checkSms('user_bind_mobile', $data['mobile'], $data['code']);
        if (!$result) {
            throw new CommonException('验证码错误或已过期');
        }

        // 检查手机号是否已被其他用户绑定
        $existUser = (new UserDao())->getUserInfo(['mobile' => $data['mobile']]);
        if (!empty($existUser) && $existUser['id'] != $this->user_id) {
            throw new CommonException('该手机号已被其他用户绑定');
        }

        // 更新用户手机信息
        $updateData = [
            'mobile' => $data['mobile'],
            'mobile_bind' => 1,
        ];
        
        $result = (new UserDao())->updateUser(['id' => $this->user_id], $updateData);
        
        if (!$result) {
            throw new CommonException('绑定失败，请重试');
        }

        return true;

        
    }

    /**
     * 解绑手机号
     * @param array $data
     * @return array
     */
    public function unbindMobile($data)
    {
        $userInfo = (new UserDao())->getUserInfo(['id' => $this->user_id]);
        
        // 检查是否已绑定手机
        if ($userInfo['mobile_bind'] != 1) {
            throw new CommonException('您尚未绑定手机号');
        }

        // 验证短信验证码
        $result = (new SysSmsService())->checkSms('user_unbind_mobile', $userInfo['mobile'], $data['code']);
        if (!$result) {
            throw new CommonException('验证码错误或已过期');
        }


        // 更新用户手机信息
        $updateData = [
            'mobile' => null,
            'mobile_bind' => 0,
        ];
        
        $result = (new UserDao())->updateUser(['id' => $this->user_id], $updateData);
        
        if (!$result) {
            throw new CommonException('解绑失败，请重试');
        }

        return true;
    }


}