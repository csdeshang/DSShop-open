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
class Bonus extends BaseModel {

    public $page_info;
    /**
     * 吸粉红包列表
     * @author csdeshang
     * @param array $condition 检索条件
     * @param array $pagesize 分页信息
     * @return array 数组类型的返回结果
     */
    public function getBonusList($condition,$pagesize ,$limit = 0,$order='bonus_id desc') {
        if($pagesize){
            $result = Db::name('bonus')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$result;
            return $result->items();
        }else{
            $result = Db::name('bonus')->where($condition)->order($order)->limit($limit)->select()->toArray();
            return $result;
        }
    }

    /**
     * 取单个吸粉红包的内容
     * @author csdeshang
     * @param array $conditions 检索条件
     * @return array 数组类型的返回结果
     */
    public function getOneBonus($conditions) {
        return Db::name('bonus')->where($conditions)->find();
    }

    /**
     * 新增
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addBonus($data) {
        if (empty($data)) {
            return false;
        }
        return Db::name('bonus')->insertGetId($data);
    }

    /**
     * 更新信息
     * @author csdeshang
     * @param array $condition 条件
     * @param array $data 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editBonus($condition,$data) {
        if (empty($data)) {
            return false;
        }
        return Db::name('bonus')->where($condition)->update($data);
    }
    /**
     * 删除
     * @author csdeshang
     * @param array $conditions 检索条件
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function delBonus($conditions) {
        return Db::name('bonus')->where($conditions)->delete();
    }
    // 获取红包类型
    public function bonus_type_list()
    {
        return array(
            '1'=>'活动红包',
            '2'=>'注册红包',
            '3'=>'奖品红包'
        );
    }
    // 获取红包状态
    public function bonus_state_list()
    {
        return array(
            '1'=>'正在进行',
            '2'=>'已过期',
            '3'=>'已失效'
        );
    }
    /**
     * 吸粉红包领取列表
     * @author csdeshang
     * @param array $condition 检索条件
     * @param array $pagesize 分页信息
     * @return array 数组类型的返回结果
     */
    public function getBonusreceiveList($condition,$pagesize ,$limit = 0) {
        if($pagesize){
            $result = Db::name('bonusreceive')->where($condition)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$result;
            return $result->items();
        }else{
            $result = Db::name('bonusreceive')->where($condition)->limit($limit)->select()->toArray();
            return $result;
        }
    }
    /**
     * 取吸粉红包的领取详情
     * @author csdeshang
     * @param array $conditions 检索条件
     * @return array 数组类型的返回结果
     */
    public function getOneBonusreceive($conditions) {
        return Db::name('bonusreceive')->where($conditions)->find();
    }
    /**
     * 更新信息
     * @author csdeshang
     * @param array $bonusreceive_id 条件
     * @param array $data 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editBonusreceive($bonusreceive_id,$data) {
        if (empty($data)) {
            return false;
        }
        return Db::name('bonusreceive')->where('bonusreceive_id',$bonusreceive_id)->update($data);
    }
    
    /**
     * 领取红包
     * @author csdeshang
     * @param array $member_info 用户信息
     * @param array $bonus 红包信息
     * @param array $bonusreceive 红包领取信息
     * @param string $lg_desc 描述信息
     * @return bool 布尔类型的返回结果
     */
    public function receiveBonus($member_info,$bonus,$bonusreceive,$lg_desc) {
        $data_bonusreceive = array(
                'member_id' => $member_info['member_id'],
                'member_name' => $member_info['member_name'],
                'bonusreceive_time' => TIMESTAMP,
                'bonusreceive_transformed' => 1, //是否转入预存款
            );
            $flag=$this->editBonusreceive($bonusreceive['bonusreceive_id'], $data_bonusreceive);
            if(!$flag){
                return ds_callback(false, '红包领取信息更新失败');
            }
            //更新活动红包统计
            $data_bonus = array(
                'bonus_receivecount' => $bonus['bonus_receivecount'] + 1,
                'bonus_receiveprice' => $bonus['bonus_receiveprice'] + $bonusreceive['bonusreceive_price'],
            );
            $flag=$this->editBonus(array('bonus_id' => $bonus['bonus_id']), $data_bonus);
            if(!$flag){
                return ds_callback(false, '红包信息更新失败');
            }
            //把红包加入预存款
            $data = array();
            $data['member_id'] = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount'] = $bonusreceive['bonusreceive_price'];
            $data['order_sn'] = $bonusreceive['bonusreceive_id'];
            $data['rcblog_description'] = $lg_desc;
            model('predeposit')->changeRcb('bonus', $data);
            return ds_callback(true);
    }
}

?>
