<?php

namespace app\common\model;
use think\facade\Db;

/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 数据层模型
 */
class Address extends BaseModel {

    /**
     * 取得买家默认收货地址
     * @author csdeshang
     * @param array $condition 获取条件
     * @param string $order  排序
     * @return array
     */
    public function getDefaultAddressInfo($condition = array(), $order = 'address_is_default desc,address_id desc') {
        return $this->getAddressInfo($condition, $order);
    }

    /**
     * 取得单条地址信息
     * @author csdeshang 
     * @param array $condition 条件
     * @param type $order 排序  
     * @return string
     */
    public function getAddressInfo($condition, $order = '') {
        $addr_info = Db::name('address')->where($condition)->order($order)->find();
        return $addr_info;
    }

    /**
     * 读取地址列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param type $order 排序
     * @return array  数组格式的返回结果
     */
    public function getAddressList($condition, $order = 'address_id desc') {
        $address_list = Db::name('address')->where($condition)->order($order)->select()->toArray();
        if (empty($address_list))
            return array();
        return $address_list;
    }

    /**
     * 取数量
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getAddressCount($condition = array()) {
        return Db::name('address')->where($condition)->count();
    }

    /**
     * 新增地址
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addAddress($data) {
        //当设置为默认地址，则此用户其他的地址设置为非默认地址
        if($data['address_is_default']==1){
            Db::name('address')->where('member_id',$data['member_id'])->update(array('address_is_default'=>0));
        }
        return Db::name('address')->insertGetId($data);
    }

    /**
     * 取单个地址
     * @author csdeshang
     * @param int $id 地址ID
     * @return array 数组类型的返回结果
     */
    public function getOneAddress($id) {
        if (intval($id) > 0) {
            $result = Db::name('address')->where('address_id',intval($id))->find();
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 更新地址信息
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $condition 更新条件
     * @return bool 布尔类型的返回结果
     */
    public function editAddress($update, $condition) {
        return Db::name('address')->where($condition)->update($update);
    }

    /**
     * 验证地址是否属于当前用户
     * @author csdeshang
     * @param array $member_id 会员id
     * @param array $address_id 地址id
     * @return bool 布尔类型的返回结果
     */
    public function checkAddress($member_id, $address_id) {
        /**
         * 验证地址是否属于当前用户
         */
        $check_array = self::getOneAddress($address_id);
        if ($check_array['member_id'] == $member_id) {
            unset($check_array);
            return true;
        }
        unset($check_array);
        return false;
    }

    /**
     * 删除地址
     * @author csdeshang
     * @param array $condition记录ID
     * @return bool 布尔类型的返回结果
     */
    public function delAddress($condition) {
        return Db::name('address')->where($condition)->delete();
    }

}

?>
