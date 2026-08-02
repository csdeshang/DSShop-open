<?php

namespace app\api\service\system;

use app\deshang\base\service\BaseApiService;

use app\deshang\exceptions\CommonException;

use app\common\dao\user\UserDao;

use app\common\dao\system\SysNoticeSmsLogDao;

// 短信服务
class SysSmsService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
    }
    
    // 发送短信（发送验证码）
    public function sendSms($data)
    {
        // 生成验证码
        $code = rand(100000, 999999);
        $key = $data['key'];

        switch ($key) {
            case 'user_register':
                // 手机号是否绑定注册
                $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile']]);
                if ($user) {
                    throw new CommonException('手机号已绑定注册');
                }
                break;
            case 'user_login':
                
                // 手机号是否绑定注册
                $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile'], 'mobile_bind' => 1]);
                if (!$user) {
                    throw new CommonException('手机号未绑定注册');
                }
                break;
            case 'user_reset_password':
                // 重置密码
                // 手机号是否绑定注册
                $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile'], 'mobile_bind' => 1]);
                if (!$user) {
                    throw new CommonException('手机号未绑定注册');
                }
                break;
            case 'user_bind_mobile':
                // 绑定手机号
                // 手机号是否绑定注册
                $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile'], 'mobile_bind' => 1]);
                if ($user) {
                    throw new CommonException('手机号已绑定注册');
                }
                break;
            case 'user_unbind_mobile':
                // 解绑手机号
                // 手机号是否绑定注册
                $user = (new UserDao())->getUserInfo(['mobile' => $data['mobile'], 'mobile_bind' => 1]);
                if (!$user) {
                    throw new CommonException('手机号未绑定注册');
                }
                break;
            default:
                throw new CommonException('短信类型错误');
        }


        // 消息通知  发送短信
        event('SysNoticeListener', [
            'key' => $key,
            'receiver_params' => [
                'user_id' => $this->user_id ?? 0,
            ],
            // 具体参数  以  sys_notice_tpl 表 为准
            'template_params' => [
                'code' => $code,
                'mobile' => $data['mobile'],
            ]
        ]);
    }


    // 校验验证码
    public function checkSms(string $key, string $mobile, string $code)
    {
        if(!$key || !$mobile || !$code){
            throw new CommonException('参数错误');
        }

        // 获取验证码  最近10分钟
        $condition = [];
        $condition[] = ['key', '=', $key];
        $condition[] = ['mobile', '=', $mobile];

        // 获取最新的一条记录
        $sms = (new SysNoticeSmsLogDao)->getSysNoticeSmsLogInfo($condition,'*','id desc');
        if (!$sms) {
            throw new CommonException('验证码不存在');
        }

        // 验证码是否正确
        if ($sms['code'] != $code) {
            throw new CommonException('验证码错误');
        }

        // 验证码是否过期
        if ($sms['create_at'] < time() - 600) {
            throw new CommonException('验证码已过期');
        }

        // 验证码修改为已验证状态
        (new SysNoticeSmsLogDao)->updateSysNoticeSmsLog(['id' => $sms['id']], ['is_verify' => 1]);

        return true;
    }


}
