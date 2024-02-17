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
class Goodsfcode extends BaseModel
{
    /**
     * 插入数据
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return boolean
     */
    public function addGoodsfcodeAll($data) {
        return Db::name('goodsfcode')->insertAll($data);
    }

    /**
     * 取得F码列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $order 排序
     * @return type
     */
    public function getGoodsfcodeList($condition, $order = 'goodsfcode_state asc,goodsfcode_id asc') {
        return Db::name('goodsfcode')->where($condition)->order($order)->select()->toArray();
    }

    /**
     * 删除F码
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return bool
     */
    public function delGoodsfcode($condition) {
        return Db::name('goodsfcode')->where($condition)->delete();
    }

    /**
     * 取得F码
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return bool
     */
    public function getGoodsfcode($condition) {
        return Db::name('goodsfcode')->where($condition)->find();
    }

    /**
     * 更新F码
     * @access public
     * @author csdeshang
     * @param array $data 更新数据
     * @param array $condition 条件
     * @return bool
     */
    public function editGoodsfcode($data, $condition) {
        return Db::name('goodsfcode')->where($condition)->update($data);
    }
    
    /**
     * 更新F码为使用状态
     * @param int $goodsfcode_id
     */
    public function updateGoodsfcode($goodsfcode_id)
    {
        $update = $this->editGoodsfcode(array('goodsfcode_state' => 1), array('goodsfcode_id' => $goodsfcode_id));
        if (!$update) {
            return ds_callback(false, '更新F码使用状态失败goodsfcode_id:' . $goodsfcode_id);
        }
        else {
            return ds_callback(true);
        }
    }
    /**
     * 生成商品F码
     */
    public function createGoodsfcode($param)
    {
        $insert = array();
        for ($i = 0; $i < $param['goodsfcode_count']; $i++) {
            $array = array();
            $array['goods_commonid'] = $param['goods_commonid'];
            $array['goodsfcode_code'] = strtoupper($param['goodsfcode_prefix']) . mt_rand(100000, 999999);
            $insert[$array['goodsfcode_code']] = $array;
        }
        if (!empty($insert)) {
            $insert = array_values($insert);
            $insert = $this->addGoodsfcodeAll($insert);
            if (!$insert) {
                return ds_callback(false, '生成商品F码失败goods_commonid:' . $param['goods_commonid']);
            }
        }
        return ds_callback(true);
    }
}