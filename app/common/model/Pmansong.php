<?php
/**
 * 满即送模型
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
class Pmansong extends BaseModel
{
    const MANSONG_STATE_NORMAL = 1;
    const MANSONG_STATE_CLOSE = 2;
    const MANSONG_STATE_CANCEL = 3;

    private $mansong_state_array = array(
        0 => '全部', self::MANSONG_STATE_NORMAL => '正常', self::MANSONG_STATE_CLOSE => '已结束',
        self::MANSONG_STATE_CANCEL => '管理员关闭'
    );
    public $page_info;

    /**
     * 读取满即送列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页数
     * @param string $order 排序
     * @param string $field 所需字段
     * @param int $limit 限制
     * @return array 满即送列表
     *
     */
    public function getMansongList($condition, $pagesize = null, $order = '', $field = '*', $limit = 0)
    {
        if ($pagesize) {
            $res = Db::name('pmansong')->field($field)->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info=$res;
            $mansong_list =$res->items();
        }
        else {
            $mansong_list = Db::name('pmansong')->field($field)->where($condition)->limit($limit)->order($order)->select()->toArray();
        }
        if (!empty($mansong_list)) {
            for ($i = 0, $j = count($mansong_list); $i < $j; $i++) {
                $mansong_list[$i] = $this->getMansongExtendInfo($mansong_list[$i]);
            }
        }
        return $mansong_list;
    }

    /**
     * 获取店铺新满即送活动开始时间限制
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getMansongNewStartTime()
    {
        $condition = array();
        $condition[] = array('mansong_state','=',self::MANSONG_STATE_NORMAL);
        $mansong_list = $this->getMansongList($condition, null, 'mansong_endtime desc');
        if(!empty($mansong_list)) {
            return $mansong_list[0]['mansong_endtime'];
        }
    }

    /**
     * 根据条件读满即送信息
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return array 秒杀信息
     */
    public function getMansongInfo($condition)
    {
        $mansong_info = Db::name('pmansong')->where($condition)->find();
        $mansong_info = $this->getMansongExtendInfo($mansong_info);
        return $mansong_info;
    }

    /**
     * 根据满即送编号读取信息
     * @access public
     * @author csdeshang
     * @param array $mansong_id 满即送活动编号
     * @return array 满即送活动信息
     *
     */
    public function getMansongInfoByStoreID($mansong_id) {
        if (intval($mansong_id) <= 0) {
            return null;
        }

        $condition = array();
        $condition[] = array('mansong_id','=',$mansong_id);
        $mansong_info = $this->getMansongInfo($condition);
        return $mansong_info;
    }

    /**
     * 获取店铺当前可用满即送活动
     * @access public
     * @author csdeshang
     * @return array 满即送活动
     */
    public function getMansongInfoByID()
    {
        $info = $this->_rGoodsMansongCache();
        if (empty($info)) {
            $condition = array();
            $condition[]=array('mansong_state','=',self::MANSONG_STATE_NORMAL);
            $condition[]=array('mansong_endtime','>', TIMESTAMP);
            $mansong_list = $this->getMansongList($condition, null, 'mansong_starttime asc', '*', 1);

            $mansong_info = isset($mansong_list[0])?$mansong_list[0]:"";

            if (!empty($mansong_info)) {
                $mansongrule_model = model('pmansongrule');
                $mansong_info['rules'] = $mansongrule_model->getMansongruleListByID($mansong_info['mansong_id']);
                if (empty($mansong_info['rules'])) {
                    $mansong_info = array(); // 如果不存在规则直接返回不记录缓存。
                }
                else {
                    // 规则数组序列化保存
                    $mansong_info['rules'] = serialize($mansong_info['rules']);
                }
            }
            $info['info'] = serialize($mansong_info);
            $this->_wGoodsMansongCache($info);
        }
        $mansong_info = unserialize($info['info']);
        if (!empty($mansong_info) && $mansong_info['mansong_starttime'] > TIMESTAMP) {
            $mansong_info = array();
        }
        if (!empty($mansong_info)) {
            $mansong_info['rules'] = unserialize($mansong_info['rules']);
        }
        return $mansong_info;
    }

    /**
     * 获取订单可用满即送规则
     * @access public
     * @author csdeshang
     * @param array $order_price 订单金额
     * @return array 满即送规则
     */
    public function getMansongruleByID($order_price)
    {
        $mansong_info = $this->getMansongInfoByID();

        if (empty($mansong_info)) {
            return null;
        }

        $rule_info = null;

        foreach ($mansong_info['rules'] as $value) {
            if ($order_price >= $value['mansongrule_price']) {
                $rule_info = $value;
                $rule_info['mansong_name'] = $mansong_info['mansong_name'];
                $rule_info['mansong_starttime'] = $mansong_info['mansong_starttime'];
                $rule_info['mansong_endtime'] = $mansong_info['mansong_endtime'];
                break;
            }
        }

        return $rule_info;
    }

    /**
     * 获取满即送状态列表
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getMansongStateArray()
    {
        return $this->mansong_state_array;
    }

    /**
     * 获取满即送扩展信息，包括状态文字和是否可编辑状态
     * @access public
     * @author csdeshang
     * @param array $mansong_info 满即送信息
     * @return array
     */
    public function getMansongExtendInfo($mansong_info)
    {
        if ($mansong_info['mansong_endtime'] > TIMESTAMP) {
            $mansong_info['mansong_state_text'] = $this->mansong_state_array[$mansong_info['mansong_state']];
        }
        else {
            $mansong_info['mansong_state_text'] = '已结束';
        }

        if ($mansong_info['mansong_state'] == self::MANSONG_STATE_NORMAL && $mansong_info['mansong_endtime'] > TIMESTAMP) {
            $mansong_info['editable'] = true;
        }
        else {
            $mansong_info['editable'] = false;
        }

        return $mansong_info;
    }

    /**
     * 增加
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addMansong($data)
    {
        $data['mansong_state'] = self::MANSONG_STATE_NORMAL;
        $result = Db::name('pmansong')->insertGetId($data);
        if ($result) {
            $this->_dGoodsMansongCache();
        }
        return $result;
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return bool
     */
    public function editMansong($update, $condition)
    {
        $mansong_list = $this->getMansongList($condition);
        if (empty($mansong_list)) {
            return true;
        }
        $result = Db::name('pmansong')->where($condition)->update($update);
        if ($result) {
            foreach ($mansong_list as $val) {
                $this->_dGoodsMansongCache();
            }
        }
        return $result;
    }

    /**
     * 删除秒杀活动，同时删除秒杀商品
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delMansong($condition)
    {
        $mansong_list = $this->getMansongList($condition);
        $mansong_id_string = '';
        if (!empty($mansong_list)) {
            foreach ($mansong_list as $value) {
                $mansong_id_string .= $value['mansong_id'] . ',';
                $this->_dGoodsMansongCache();
            }
        }

        //删除满送规则
        $mansongrule_model = model('pmansongrule');
        $mansongrule_model->delMansongrule($condition);

        return Db::name('pmansong')->where($condition)->delete();
    }

    /**
     * 取消满即送活动
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function cancelMansong($condition)
    {
        $update = array();
        $update['mansong_state'] = self::MANSONG_STATE_CANCEL;
        return $this->editMansong($update, $condition);
    }

    /**
     * 过期满送修改状态
     * @access public
     * @author csdeshang
     * @return  type
     */
    public function editExpireMansong()
    {
        $updata = array();
        $update['mansong_state'] = self::MANSONG_STATE_CLOSE;

        $condition = array();
        $condition[]=array('mansong_endtime','<', TIMESTAMP);
        $condition[]=array('mansong_state','=',self::MANSONG_STATE_NORMAL);
        $this->editMansong($update, $condition);
    }

    /**
     * 读取商品满即送缓存
     * @access public
     * @author csdeshang
     * @return array
     */
    private function _rGoodsMansongCache()
    {
        return rcache('goods_mansong');
    }

    /**
     * 写入商品满即送缓存
     * @access public
     * @author csdeshang
     * @param array $mansong_info  满即送信息
     * @return boolean
     */
    private function _wGoodsMansongCache($mansong_info)
    {
        return wcache('goods_mansong', $mansong_info);
    }

    /**
     * 删除商品满即送缓存
     * @access public
     * @author csdeshang
     * @return boolean
     */
    private function _dGoodsMansongCache()
    {
        return dcache('goods_mansong');
    }
}
