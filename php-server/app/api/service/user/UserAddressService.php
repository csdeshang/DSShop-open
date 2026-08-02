<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;


use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\NotFoundException;


use app\common\dao\user\UserAddressDao;

class UserAddressService extends BaseUserService
{


    public function __construct()
    {
        parent::__construct();
    }

    // 获取地址列表
    public function getUserAddressList()
    {
        $condition = array();
        $condition[] = ['user_id', '=', $this->user_id];
        $address_list = (new UserAddressDao)->getAddressList($condition);
        return $address_list;
    }

    // 创建地址
    public function createUserAddress($data)
    {
        $data['user_id'] = $this->user_id;

        // 选择默认地址
        if ($data['is_default'] == 1) {
            // 设置其他地址为非默认
            (new UserAddressDao)->updateAddress([['user_id', '=', $this->user_id]], ['is_default' => 0]);
        }
        // 添加地址
        (new UserAddressDao)->createAddress($data);
        return true;
    }

    // 更新地址
    public function updateUserAddress($id, $data)
    {
        $data['user_id'] = $this->user_id;

        // 判断地址是否存在
        $condition = array();
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];
        $address_info = (new UserAddressDao)->getAddressInfo($condition);
        if (empty($address_info)) {
            throw new NotFoundException('地址不存在');
        }

        // 选择默认地址
        if ($data['is_default'] == 1) {
            // 设置其他地址为非默认
            (new UserAddressDao)->updateAddress([['user_id', '=', $this->user_id]], ['is_default' => 0]);
        }
        // 更新地址
        (new UserAddressDao)->updateAddress([['id', '=', $id], ['user_id', '=', $this->user_id]], $data);
        return true;
    }

    // 获取地址信息
    public function getUserAddressInfo($data)
    {
        $condition = array();
        $condition[] = ['id', '=', $data['id']];

        $condition[] = ['user_id', '=', $this->user_id];
        $address_info = (new UserAddressDao)->getAddressInfo($condition);
        if (empty($address_info)) {
            throw new NotFoundException('地址不存在');
        }
        return $address_info;
    }

    // 删除地址
    public function deleteUserAddress($data)
    {
        $condition = array();
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new UserAddressDao)->deleteAddress($condition);

        return $result;
    }
    //获取默认地址
    public function getDefaultUserAddress()
    {
        $condition = array();
        $condition[] = ['user_id', '=', $this->user_id];

        $condition[] = ['is_default', '=', 1];

        $address_info = (new UserAddressDao)->getAddressInfo($condition);
        if (empty($address_info)) {
            throw new NotFoundException('默认地址不存在');
        }
        return $address_info;
    }

    // 设置默认地址
    public function setDefaultUserAddress($data)
    {
        // 判断地址是否存在
        $this->getUserAddressInfo(['id' => $data['id']]);

        // 设置其他地址为非默认
        (new UserAddressDao)->updateAddress([['user_id', '=', $this->user_id]], ['is_default' => 0]);


        // 设置默认地址
        (new UserAddressDao)->updateAddress([['id', '=', $data['id']], ['user_id', '=', $this->user_id]], ['is_default' => 1]);

        return true;
    }
}
