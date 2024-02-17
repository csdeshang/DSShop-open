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
 * 刮刮卡模型层
 */
class Marketmanage extends BaseModel {

    /**
     * 营销活动列表
     * @author csdeshang
     * @param array $condition 检索条件
     * @param array $pagesize 分页信息
     * @return array 数组类型的返回结果
     */
    public function getMarketmanageList($condition, $pagesize, $limit = 0,$order='marketmanage_id desc') {
        if ($pagesize) {
            $result = Db::name('marketmanage')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            $result = Db::name('marketmanage')->where($condition)->order($order)->limit($limit)->select()->toArray();
            return $result;
        }
    }

    /**
     * 取单个营销活动的内容
     * @author csdeshang
     * @param array $conditions 检索条件
     * @return array 数组类型的返回结果
     */
    public function getOneMarketmanage($conditions,$lock=false) {
        return Db::name('marketmanage')->where($conditions)->lock($lock)->find();
    }

    /**
     * 新增
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addMarketmanage($data) {
        if (empty($data)) {
            return false;
        }
        return Db::name('marketmanage')->insertGetId($data);
    }

    /**
     * 更新信息
     * @author csdeshang
     * @param array $condition 条件
     * @param array $data 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editMarketmanage($condition, $data) {
        if (empty($data)) {
            return false;
        }
        return Db::name('marketmanage')->where($condition)->update($data);
    }

    /**
     * 删除
     * @author csdeshang
     * @param array $marketmanage_id 检索条件
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function delMarketmanage($marketmanage_id) {
        //删除主表
        $result = Db::name('marketmanage')->where('marketmanage_id',$marketmanage_id)->delete();
        //删除奖品表
        Db::name('marketmanageaward')->where('marketmanage_id',$marketmanage_id)->delete();
        //删除领取记录表
        Db::name('marketmanagelog')->where('marketmanage_id',$marketmanage_id)->delete();
        return $result;
    }
    /**
     * 新增营销活动奖品信息
     * @author csdeshang
     * @param array $data 更新信息
     * @return array 数组类型的返回结果
     */
    public function addMarketmanageAward($data) {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('marketmanageaward')->insertGetId($data);
        return $result;
    }
    
    /**
     * 更新营销活动奖品信息
     * @author csdeshang
     * @param array $condition 检索条件
     * @param array $data 更新信息
     * @return array 数组类型的返回结果
     */
    public function editMarketmanageAward($condition,$data) {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('marketmanageaward')->where($condition)->update($data);
        return $result;
    }
    
    /**
     * 营销活动奖品记录
     * @author csdeshang
     * @param array $condition 检索条件
     * @param array $pagesize 分页信息
     * @return array 数组类型的返回结果
     */
    public function getMarketmanageAwardList($condition,$lock=false) {
        $result = Db::name('marketmanageaward')->where($condition)->order('marketmanageaward_level asc')->lock($lock)->select()->toArray();
        return $result;
    }

    /**
     * 新增营销活动参与记录
     * @author csdeshang
     * @param array $data 信息
     * @return array 数组类型的返回结果
     */
    public function addMarketmanageLog($data) {
        if (empty($data)) {
            return false;
        }
        $result = Db::name('marketmanagelog')->insertGetId($data);
        return $result;
    }
    
    /**
     * 营销活动参与记录列表
     * @author csdeshang
     * @param array $condition 检索条件
     * @param array $pagesize 分页信息
     * @return array 数组类型的返回结果
     */
    public function getMarketmanageLogList($condition, $pagesize='', $limit = 0) {
        if ($pagesize) {
            $result = Db::name('marketmanagelog')->where($condition)->order('marketmanagelog_id desc')->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            $result = Db::name('marketmanagelog')->where($condition)->order('marketmanagelog_id desc')->limit($limit)->select()->toArray();
            return $result;
        }
    }
    
    
    //营销活动类型
    public function marketmanage_type_list() {
        return array(
            1 => '刮刮卡',
            2 => '大转盘',
            3 => '砸金蛋',
            4 => '生肖翻翻看',
        );
    }

}
