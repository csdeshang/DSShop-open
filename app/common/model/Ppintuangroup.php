<?php

/**
 * 拼团活动模型 
 *
 */

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
class Ppintuangroup extends BaseModel {

    public $page_info;
    const PINTUANGROUP_STATE_CLOSE = 0;
    const PINTUANGROUP_STATE_NORMAL = 1;
    const PINTUANGROUP_STATE_SUCCESS = 2;

    private $pintuangroup_state_array = array(
        self::PINTUANGROUP_STATE_CLOSE => '拼团取消',
        self::PINTUANGROUP_STATE_NORMAL => '参团中',
        self::PINTUANGROUP_STATE_SUCCESS => '拼团成功'
    );

    /**
     * 获取开团表列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @return type
     */
    public function getPpintuangroupList($condition, $pagesize = '',$order='pintuangroup_starttime desc') {
        $field = "ppintuangroup.*,member.member_name";
        if ($pagesize) {
            $result = Db::name('ppintuangroup')->alias('ppintuangroup')->join('member member','ppintuangroup.pintuangroup_headid=member.member_id')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            $ppintuangroup_list = $result->items();
        }else{
            $ppintuangroup_list =  Db::name('ppintuangroup')->alias('ppintuangroup')->join('member member','ppintuangroup.pintuangroup_headid=member.member_id')->field($field)->where($condition)->order($order)->select()->toArray();
        }
        if (!empty($ppintuangroup_list)) {
            foreach ($ppintuangroup_list as $key => $ppintuangroup) {
                //此拼团发起活动剩余还可购买的份额
                $pintuangroup_surplus = $ppintuangroup['pintuangroup_limit_number'] - $ppintuangroup['pintuangroup_joined'];
                $ppintuangroup_list[$key]['pintuangroup_state_text'] = $this->pintuangroup_state_array[$ppintuangroup['pintuangroup_state']];
                $ppintuangroup_list[$key]['pintuangroup_surplus'] = $pintuangroup_surplus;
                $ppintuangroup_list[$key]['pintuangroup_avatar'] = get_member_avatar_for_id($ppintuangroup['pintuangroup_headid']);
            }
        }
        return $ppintuangroup_list;
    }
    /**
     * 获取单个单团信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getOnePpintuangroup($condition){
        return Db::name('ppintuangroup')->where($condition)->find();
    }
    
    /**
     * 插入拼团开团表
     * @access public
     * @author csdeshang
     * @param type $data 参数数据
     * @return type
     */
    public function addPpintuangroup($data)
    {
        return Db::name('ppintuangroup')->insertGetId($data);
    }
 
    /**
     * 编辑拼团开团表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $data 数据
     * @return type
     */
    public function editPpintuangroup($condition,$data)
    {
        return Db::name('ppintuangroup')->where($condition)->update($data);
    }
    
    /**
     * 拼团成功,拼团订单信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     */
    public function successPpintuangroup($condition,$condition2)
    {
        //更新拼团开团信息
        $update_group['pintuangroup_state'] = 2;
        $update_group['pintuangroup_endtime'] = TIMESTAMP;
        $this->editPpintuangroup($condition, $update_group);
        //更新拼团订单信息
        $update_order['pintuanorder_state'] = 2;
        model('ppintuanorder')->editPpintuanorder($condition2,$update_order);
    }
 
    /**
     * 拼团成功,拼团订单信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type 
     */
    public function failPpintuangroup($condition)
    {
        //更新拼团开团信息
        $update_group['pintuangroup_state'] = 0;
        $update_group['pintuangroup_endtime'] = TIMESTAMP;
        $this->editPpintuangroup($condition, $update_group);
        //更新拼团订单信息
        $update_order['pintuanorder_state'] = 0;
        model('ppintuanorder')->editPpintuanorder($condition,$update_order);
    }
  
    /**
     * 拼团状态数组
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getPintuangroupStateArray() {
        return $this->pintuangroup_state_array;
    }
    
}
