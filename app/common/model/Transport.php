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
class Transport extends BaseModel {

    public $page_info;

    /**
     * 增加售卖区域
     * @access public
     * @author csdeshang
     * @param type $data 参数内容
     * @return type
     */
    public function addTransport($data) {
        return Db::name('transport')->insertGetId($data);
    }

    /**
     * 增加各地区详细运费设置
     * @access public
     * @author csdeshang
     * @param array $data
     * @return bool
     */
    public function addExtend($data) {
        return Db::name('transportextend')->insertAll($data);
    }

    /**
     * 取得一条售卖区域信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return array
     */
    public function getTransportInfo($condition) {
        return Db::name('transport')->where($condition)->find();
    }

    /**
     * 取得一条售卖区域扩展信息
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return array
     */
    public function getExtendInfo($condition) {
        return Db::name('transportextend')->where($condition)->select()->toArray();
    }

    /**
     * 删除售卖区域
     * @access public
     * @author csdeshang
     * @param type $transport_id 条件
     * @return boolean
     */
    public function delTansport($transport_id) {
        try {
            Db::startTrans();
            $delete = Db::name('transport')->where('transport_id',$transport_id)->delete();
            if ($delete) {
                $delete = Db::name('transportextend')->where('transport_id',$transport_id)->delete();
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();

            return false;
        }

        return true;
    }

    /**
     * 删除售卖区域扩展信息
     * @access public
     * @author csdeshang
     * @param int $transport_id 售卖区域ID
     * @return bool
     */
    public function delTransportextend($transport_id) {
        return Db::name('transportextend')->where(array('transport_id' => $transport_id))->delete();
    }

    /**
     * 取得售卖区域列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页信息
     * @param string $order 排序
     * @return array
     */
    public function getTransportList($condition = array(), $pagesize = '', $order = 'transport_id desc') {
        if($pagesize){
            $res = Db::name('transport')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            return $res->items();
        }else{
            return Db::name('transport')->where($condition)->order($order)->select()->toArray();
        }
        
        
    }

    /**
     * 取得扩展信息列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param string $order 排序
     * @return array
     */
    public function getTransportextendList($condition = array(), $order = 'transportext_id asc') {
        return Db::name('transportextend')->where($condition)->order($order)->select()->toArray();
    }
    
    /**
     * 编辑更新售卖区域
     * @access public
     * @author csdeshang
     * @param type $data 数据
     * @param type $condition 条件
     * @return type
     */
    public function editTransport($data, $condition = array()) {
        return Db::name('transport')->where($condition)->update($data);
    }

    /**
     * 检测售卖区域是否正在被使用
     * @access public
     * @author csdeshang
     * @param type $id ID编号
     * @return boolean
     */
    public function isTransportUsing($id) {
        if (!is_numeric($id)) {
            return false;
        }
        $goods_info = Db::name('goods')->where(array('transport_id' => $id))->field('goods_id')->find();

        return $goods_info ? true : false;
    }

    /**
     * 计算某地区某售卖区域ID下的商品总运费，如果售卖区域不存在或，按免运费处理
     * @access public
     * @author csdeshang
     * @param int $transport_id 售卖区域ID
     * @param int $area_id 区域ID
     * @param int $count 计数
     * @return number/boolean
     */
    public function calcTransport($transport_id, $area_id,$count=1) {
        if (empty($transport_id)) {
            return 0;
        }
        $transport = $this->getTransportInfo(array('transport_id' => $transport_id));
        $extend_list = $this->getTransportextendList(array('transport_id' => $transport_id));
        if (empty($extend_list) || !$transport) {
            return false;
        } else {
            return $this->_calc_unit($area_id,$transport, $extend_list,$count);
        }
    }

    /**
     * 计算某个具单元的运费
     * @access public
     * @author csdeshang
     * @param type $area_id 配送地区ID
     * @param type $extend 售卖区域内容
     * @param type $count 计数
     * @return type
     */
    private function _calc_unit($area_id, $transport,$extend,$count) {
        if (!empty($extend) && is_array($extend)) {
            foreach ($extend as $v) {
				if ($v['transportext_area_id']=='' && !$transport['transport_is_limited']) {
                        $calc_total = $v['transportext_sprice'];
                        if($v['transportext_xprice']>0 && $count>$v['transportext_snum']){
                            if($v['transportext_xnum']){
                            $calc_total+=ceil(($count-$v['transportext_snum'])/$v['transportext_xnum'])*$v['transportext_xprice'];
                          }else{
                            $calc_total+=$v['transportext_xprice'];
                          }
                        }
                    }
                    if($area_id){
                if (strpos($v['transportext_area_id'], "," . $area_id . ",") !== false) {
                    $calc_total = $v['transportext_sprice'];
					if($v['transportext_xprice']>0 && $count>$v['transportext_snum']){
                        if($v['transportext_xnum']){
                            $calc_total+=ceil(($count-$v['transportext_snum'])/$v['transportext_xnum'])*$v['transportext_xprice'];
                          }else{
                            $calc_total+=$v['transportext_xprice'];
                          }
                    }
                }
                }
            }
        }

        return isset($calc_total) ? ds_price_format($calc_total) : false;
    }

}