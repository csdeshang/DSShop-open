<?php
namespace app\api\service\user;
use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;
use app\common\dao\user\UserDao;
use app\common\enum\user\UserEnum;
use app\deshang\service\user\DeshangUserService;

class UserService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }

    // 获取用户信息以及角色信息
    public function getUserInfoWithRoles()
    {
        $user_info = (new UserDao())->getUserInfo([['id', '=', $this->user_id], ['is_deleted', '=', 0]]);
        if (empty($user_info) || empty($user_info['id'])) {
            throw new CommonException('用户不存在');
        }
        $user_info_with_roles = (new DeshangUserService())->getUserInfoWithRoles($user_info);
        return $user_info_with_roles;
    }

    // 获取用户信息
    public function getUserInfo()
    {
        $user_info = (new UserDao())->getUserInfo([['id', '=', $this->user_id], ['is_deleted', '=', 0]]);
        if (empty($user_info) || empty($user_info['id'])) {
            throw new CommonException('用户不存在');
        }
        unset($user_info['password']);
        unset($user_info['pay_password']);
        return $user_info;
    }

    // 更新用户信息
    public function updateUserInfo($data)
    {
        return  (new DeshangUserService())->updateUser($this->user_id, $data);
    }

    // 修改用户密码
    public function editUserPassword($data)
    {
        // 获取当前用户信息
        $userDao = new UserDao();
        $user_info = $userDao->getUserInfoById($this->user_id);
        
        if (!$user_info) {
            throw new CommonException('用户不存在');
        }

        // 验证原密码
        if (!password_verify($data['old_password'], $user_info['password'])) {
            throw new CommonException('原密码错误');
        }

        // 更新密码
        $update_data = [
            'password' => $data['password'],
        ];
        
        $result = (new DeshangUserService())->updateUser($this->user_id, $update_data);
        
        if (!$result) {
            throw new CommonException('密码修改失败');
        }

        return ['success' => true];
    }

    /**
     * 编辑用户身份证认证信息（提交审核）
     * @param array $data idcard_name, idcard_number, idcard_image1, idcard_image2, idcard_image3
     * @return int
     */
    public function editUserIdcard(array $data)
    {
        $userDao = new UserDao();
        $user = $userDao->getUserInfoById($this->user_id, 'idcard_status');
        if (!$user) {
            throw new CommonException('用户不存在');
        }
        $status = (int)($user['idcard_status'] ?? 0);
        if ($status === UserEnum::IDCARD_STATUS_VERIFIED) {
            throw new CommonException('已认证不可修改');
        }
        if ($status === UserEnum::IDCARD_STATUS_PENDING) {
            throw new CommonException('审核中，请勿重复提交');
        }
        $data['idcard_status'] = UserEnum::IDCARD_STATUS_PENDING;
        $condition = [['id', '=', $this->user_id]];
        return (new UserDao())->updateUser($condition, $data);
    }
}