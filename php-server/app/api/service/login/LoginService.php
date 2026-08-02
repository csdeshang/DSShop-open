<?php

namespace app\api\service\login;

use app\deshang\base\BaseService;
use app\deshang\utils\JwtToken;


use app\deshang\exceptions\CommonException;

use app\common\dao\user\UserDao;

use app\api\service\system\SysSmsService;

use app\deshang\service\user\DeshangUserService;
use app\deshang\service\user\DeshangUserBehaviorService;
use app\common\enum\user\UserBehaviorEnum;



class LoginService extends BaseService
{
    private $userBehaviorService;

    public function __construct()
    {
        parent::__construct();
        $this->userBehaviorService = new DeshangUserBehaviorService();
    }


    public function loginNormal($data)
    {
        // 1. 安全检测：检查IP频率限制
        $this->userBehaviorService->checkIpFrequency();

        // 账号/手机号 密码登录
        $condition = ['username|mobile|email' => $data['username']];
        $condition[] = ['is_deleted', '=', 0];
        $user = (new UserDao())->getUserInfo($condition);

        if (empty($user)) {
            // 记录登录失败：账户不存在
            $this->userBehaviorService->logUserBehavior([
                'user_id' => 0,
                'username' => $data['username'],
                'behavior_type' => UserBehaviorEnum::TYPE_LOGIN_NORMAL,
                'behavior_status' => UserBehaviorEnum::STATUS_FAILED,
                'failure_reason' => '账户不存在',
                'is_abnormal' => UserBehaviorEnum::ABNORMAL_NO,
                'abnormal_reason' => null,
                // 低风险
                'risk_level' => UserBehaviorEnum::RISK_LOW
            ], [
                'login_method' => 'password',
                'input_username' => $data['username']
            ]);
            throw new CommonException('账户不存在');
        }

        // 2. 安全检测：检查用户登录频率限制
        $this->userBehaviorService->checkUserLoginFrequency($user, [
            'login_method' => 'password',
            'input_username' => $data['username']
        ]);

        if (!check_password($data['password'], $user['password'])) {
            // 记录登录失败：密码错误
            $this->userBehaviorService->logUserBehavior([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'behavior_type' => UserBehaviorEnum::TYPE_LOGIN_NORMAL,
                'behavior_status' => UserBehaviorEnum::STATUS_FAILED,
                'failure_reason' => '密码错误',
                'is_abnormal' => UserBehaviorEnum::ABNORMAL_NO,
                'abnormal_reason' => null,
                'risk_level' => UserBehaviorEnum::RISK_LOW
            ], [
                'login_method' => 'password',
                'input_username' => $data['username']
            ]);
            throw new CommonException('密码错误');
        }

        // 记录登录成功
        $this->userBehaviorService->logUserBehavior([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'behavior_type' => UserBehaviorEnum::TYPE_LOGIN_NORMAL,
            'behavior_status' => UserBehaviorEnum::STATUS_SUCCESS,
            'failure_reason' => null,
            'is_abnormal' => UserBehaviorEnum::ABNORMAL_NO,
            'abnormal_reason' => null,
            // 无风险
            'risk_level' => UserBehaviorEnum::RISK_NONE
        ], [
            'login_method' => 'password',
            'input_username' => $data['username']
        ]);

        return $this->loginAfter($user, $data);
    }



    public function loginMobile($data)
    {
        // 1. 安全检测：检查IP频率限制
        $this->userBehaviorService->checkIpFrequency();

        // 手机号 验证码登录
        $condition = ['mobile' => $data['mobile']];
        $condition[] = ['is_deleted', '=', 0];
        $user = (new UserDao())->getUserInfo($condition);

        if (empty($user)) {
            // 记录登录失败：账户不存在
            $this->userBehaviorService->logUserBehavior([
                'user_id' => 0,
                'username' => $data['mobile'],
                'behavior_type' => UserBehaviorEnum::TYPE_LOGIN_MOBILE,
                'behavior_status' => UserBehaviorEnum::STATUS_FAILED,
                'failure_reason' => '账户不存在',
                'is_abnormal' => UserBehaviorEnum::ABNORMAL_NO,
                'abnormal_reason' => null,
                // 低风险
                'risk_level' => UserBehaviorEnum::RISK_LOW
            ], [
                'login_method' => 'sms',
                'mobile' => $data['mobile']
            ]);
            // 此处也可拓展为 注册新用户
            throw new CommonException('账户不存在');
        }

        // 2. 安全检测：检查用户登录频率限制
        $this->userBehaviorService->checkUserLoginFrequency($user, [
            'login_method' => 'sms',
            'mobile' => $data['mobile']
        ]);

        // 验证验证码
        $result = (new SysSmsService())->checkSms('user_login', $data['mobile'], $data['code']);
        if (!$result) {
            // 记录登录失败：验证码错误
            $this->userBehaviorService->logUserBehavior([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'behavior_type' => UserBehaviorEnum::TYPE_LOGIN_MOBILE,
                'behavior_status' => UserBehaviorEnum::STATUS_FAILED,
                'failure_reason' => '验证码错误',
                'is_abnormal' => UserBehaviorEnum::ABNORMAL_NO,
                'abnormal_reason' => null,
                // 低风险
                'risk_level' => UserBehaviorEnum::RISK_LOW
            ], [
                'login_method' => 'sms',
                'mobile' => $data['mobile']
            ]);
            throw new CommonException('验证码错误');
        }

        // 记录登录成功
        $this->userBehaviorService->logUserBehavior([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'behavior_type' => UserBehaviorEnum::TYPE_LOGIN_MOBILE,
            'behavior_status' => UserBehaviorEnum::STATUS_SUCCESS,
            'failure_reason' => null,
            'is_abnormal' => UserBehaviorEnum::ABNORMAL_NO,
            'abnormal_reason' => null,
            // 无风险
            'risk_level' => UserBehaviorEnum::RISK_NONE
        ], [
            'login_method' => 'sms',
            'mobile' => $data['mobile']
        ]);

        return $this->loginAfter($user, $data);
    }



    /**
     * 登录成功：先拉取一次用户+角色信息，用于校验与生成 token，避免重复查询
     * @param array $user 登录态用户信息（来自 loginNormal/loginMobile）
     * @param array $data 可选扩展参数（兼容旧调用）
     */
    public function loginAfter($user, $data = [])
    {
        if ($user['is_enabled'] != 1) {
            throw new CommonException('账户被禁用');
        }

        // 只查角色（复用已有 $user，不再查用户表）
        $userInfo = (new DeshangUserService())->getUserInfoWithRoles($user);
        if (empty($userInfo) || empty($userInfo['id'])) {
            throw new CommonException('用户信息异常');
        }

        // 更新登录信息
        $user_data = [];
        $user_data['old_login_time'] = strtotime($user['login_time']);
        $user_data['login_time'] = TIMESTAMP;
        $user_data['old_login_ip'] = $user['login_ip'];
        $user_data['login_ip'] = request()->ip();
        (new UserDao())->updateUser([['id', '=', $user['id']]], $user_data);

        //生成token及返回数据
        $result = $this->generateUserToken($userInfo);

        // 用户登录事件
        event('UserLoginListener', ['user_id' => $user['id']]);

        return $result;
    }



    /**
     * 根据用户+角色信息生成 token 及返回数据（仅此一个参数，由调用方保证已拉取完整 user_info）
     * @param array $user_info 来自 getUserInfoWithRoles 的完整用户与角色信息
     */
    public function generateUserToken(array $user_info): array
    {
        $access_payload_data = array(
            'role' => 'user',
            'user_id' => $user_info['id'],
            'username' => $user_info['username'],
            'user_is_enabled' => $user_info['is_enabled'],
            'rider_id' => isset($user_info['rider']['id']) ? $user_info['rider']['id'] : 0,
            'rider_is_enabled' => isset($user_info['rider']['is_enabled']) ? $user_info['rider']['is_enabled'] : 0,
            'technician_id' => isset($user_info['technician']['id']) ? $user_info['technician']['id'] : 0,
            'technician_is_enabled' => isset($user_info['technician']['is_enabled']) ? $user_info['technician']['is_enabled'] : 0,
            'blogger_id' => isset($user_info['blogger']['id']) ? $user_info['blogger']['id'] : 0,
            'blogger_is_enabled' => isset($user_info['blogger']['is_enabled']) ? $user_info['blogger']['is_enabled'] : 0,
        );

        // 有效期 2 小时
        $access_token = (new JwtToken())->generateToken($access_payload_data, 7200);

        //生成 refresh_token   
        $refresh_payload_data = array(
            'role' => 'user',
            'user_id' => $user_info['id'],
        );
        // 有效期 7 天
        $refresh_token = (new JwtToken())->generateToken($refresh_payload_data, 3600 * 24 * 7);

        $data = array(
            'access_token' => $access_token['token'],
            'access_expires_time' => $access_token['exp'],
            'refresh_token' => $refresh_token['token'],
            'refresh_expires_time' => $refresh_token['exp'],
            'userinfo' => $user_info
        );
        return $data;
    }


    /**

     * 刷新token
     */
    public function refreshUserToken(string $refresh_token)
    {

        // 验证 refresh_token
        $token_info = (new JwtToken())->validateToken($refresh_token);

        if (empty($token_info) || $token_info['role'] != 'user') {
            throw new CommonException('refresh_token 无效');
        }


        $user_id = $token_info['user_id'] ?? 0;

        if (!$user_id) {
            throw new CommonException('refresh_token 无效');
        }

        $user_info = (new UserDao())->getUserInfo([['id', '=', $user_id], ['is_deleted', '=', 0]]);
        if (empty($user_info) || empty($user_info['id'])) {
            throw new CommonException('用户不存在');
        }
        if (isset($user_info['is_enabled']) && $user_info['is_enabled'] != 1) {
            throw new CommonException('用户已禁用');
        }

        $user_info_with_roles = (new DeshangUserService())->getUserInfoWithRoles($user_info);
        return $this->generateUserToken($user_info_with_roles);
    }
}
