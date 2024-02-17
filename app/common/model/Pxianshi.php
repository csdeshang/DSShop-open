<?php

/**
 * 秒杀活动模型 
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
class Pxianshi extends BaseModel {

    public $page_info;
    const XIANSHI_STATE_NORMAL = 1;
    const XIANSHI_STATE_CLOSE = 2;
    const XIANSHI_STATE_CANCEL = 3;

    private $xianshi_state_array = array(
        0 => '全部',
        self::XIANSHI_STATE_NORMAL => '正常',
        self::XIANSHI_STATE_CLOSE => '已结束',
        self::XIANSHI_STATE_CANCEL => '关闭'
    );

    /**
     * 读取秒杀列表
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @param type $pagesize 分页
     * @param type $order 排序
     * @param type $field 字段
     * @return type
     */
    public function getXianshiList($condition, $pagesize = null, $order = '', $field = '*') {
        if($pagesize){
        $res = Db::name('pxianshi')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
        $this->page_info=$res;
        $xianshi_list= $res->items();
        
        }else{
            $xianshi_list= Db::name('pxianshi')->field($field)->where($condition)->order($order)->select()->toArray();
        }
        
        if (!empty($xianshi_list)) {
            for ($i = 0, $j = count($xianshi_list); $i < $j; $i++) {
                $xianshi_list[$i] = $this->getXianshiExtendInfo($xianshi_list[$i]);
            }
        }
        
        return $xianshi_list;
    }

    /**
     * 根据条件读取限制折扣信息
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function getXianshiInfo($condition) {
        $xianshi_info = Db::name('pxianshi')->where($condition)->find();
        $xianshi_info = $this->getXianshiExtendInfo($xianshi_info);
        return $xianshi_info;
    }

    /**
     * 根据秒杀编号读取限制折扣信息
     * @access public
     * @author csdeshang
     * @param type $xianshi_id 限制折扣活动编号
     * @return array
     */
    public function getXianshiInfoByID($xianshi_id) {
        if (intval($xianshi_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition[] = array('xianshi_id','=',$xianshi_id);
        $xianshi_info = $this->getXianshiInfo($condition);
            return $xianshi_info;
    }

    /**
     * 秒杀状态数组
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getXianshiStateArray() {
        return $this->xianshi_state_array;
    }

    /**
     * 增加
     * @access public
     * @author csdeshang
     * @param array $data 数据
     * @return bool
     */
    public function addXianshi($data) {
        $data['xianshi_state'] = self::XIANSHI_STATE_NORMAL;
        return Db::name('pxianshi')->insertGetId($data);
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param type $update 数据
     * @param type $condition 条件
     * @return type
     */
    public function editXianshi($update, $condition) {
        return Db::name('pxianshi')->where($condition)->update($update);
    }

    /**
     * 删除秒杀活动，同时删除秒杀商品
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return bool
     */
    public function delXianshi($condition) {
        $xianshi_list = $this->getXianshiList($condition);
        $xianshi_id_string = '';
        if (!empty($xianshi_list)) {
            foreach ($xianshi_list as $value) {
                $xianshi_id_string .= $value['xianshi_id'] . ',';
            }
        }

        //删除秒杀商品
        if ($xianshi_id_string !== '') {
            $xianshigoods_model = model('pxianshigoods');
            $xianshigoods_model->delXianshigoods(array(array('xianshi_id','in', $xianshi_id_string)));
        }

        return Db::name('pxianshi')->where($condition)->delete();
    }

    /**
     * 取消秒杀活动，同时取消秒杀商品
     * @access public
     * @author csdeshang
     * @param type $condition 条件
     * @return type
     */
    public function cancelXianshi($condition) {
        $xianshi_list = $this->getXianshiList($condition);
        $xianshi_id_string = '';
        if (!empty($xianshi_list)) {
            foreach ($xianshi_list as $value) {
                $xianshi_id_string .= $value['xianshi_id'] . ',';
            }
        }

        $update = array();
        $update['xianshi_state'] = self::XIANSHI_STATE_CANCEL;

        //删除秒杀商品
        if ($xianshi_id_string !== '') {
            $xianshigoods_model = model('pxianshigoods');
            $xianshigoods_model->editXianshigoods(array('xianshigoods_state'=>self::XIANSHI_STATE_CANCEL), array(array('xianshi_id','in', $xianshi_id_string)));
        }

        return $this->editXianshi($update, $condition);
    }

    /**
     * 获取秒杀扩展信息，包括状态文字和是否可编辑状态
     * @access public
     * @author csdeshang
     * @param type $xianshi_info 秒杀信息
     * @return boolean
     */
    public function getXianshiExtendInfo($xianshi_info) {
        if ($xianshi_info['xianshi_end_time'] > TIMESTAMP) {
            $xianshi_info['xianshi_state_text'] = $this->xianshi_state_array[$xianshi_info['xianshi_state']];
        } else {
            $xianshi_info['xianshi_state_text'] = '已结束';
        }

        if ($xianshi_info['xianshi_state'] == self::XIANSHI_STATE_NORMAL && $xianshi_info['xianshi_end_time'] > TIMESTAMP) {
            $xianshi_info['editable'] = true;
        } else {
            $xianshi_info['editable'] = false;
        }

        return $xianshi_info;
    }

    /**
     * 编辑过期修改状态
     * @access public
     * @author csdeshang
     * @param type $condition
     * @return boolean
     */
    public function editExpireXianshi($condition) {
        $condition[]=array('xianshi_end_time','<', TIMESTAMP);

        // 更新商品促销价格
        $xianshigoods_list = model('pxianshigoods')->getXianshigoodsList(array(array('xianshigoods_end_time','<', TIMESTAMP)));
        if (!empty($xianshigoods_list)) {
            $goodsid_array = array();
            foreach ($xianshigoods_list as $val) {
                $goodsid_array[] = $val['goods_id'];
            }
            // 更新商品促销价格，需要考虑抢购是否在进行中
            model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsId','cron_value'=>serialize($goodsid_array)));
        }
        $condition[]=array('xianshi_state','=',self::XIANSHI_STATE_NORMAL);

        $updata = array();
        $update['xianshi_state'] = self::XIANSHI_STATE_CLOSE;
        $result = $this->editXianshi($update, $condition);
        if ($result) {
            foreach ($xianshigoods_list as $value) {
                $this->_unlockGoods($value['goods_commonid']);
            }
        }
        return true;
    }
    
    /**
     * 解锁商品
     * @access private
     * @author csdeshang
     * @param type $goods_commonid 商品编号ID
     */
    private function _unlockGoods($goods_commonid)
    {
        $goods_model = model('goods');
        $goods_model->editGoodsCommonUnlock(array('goods_commonid' => $goods_commonid));
        $goods_model->editGoodsUnlock(array('goods_commonid' => $goods_commonid));
        // 添加对列 更新商品促销价格
        model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsCommonId','cron_value'=>serialize($goods_commonid)));
    }

}
