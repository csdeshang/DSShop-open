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
class Adv extends BaseModel {

    public $page_info;

    /**
     * 新增广告位
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addAdvposition($data) {
        return Db::name('advposition')->insertGetId($data);
    }

    /**
     * 新增广告
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addAdv($data) {
        $result = Db::name('adv')->insertGetId($data);
        $apId = (int) $data['ap_id'];
        dkcache("adv/{$apId}");
        return $result;
    }

    /**
     * 删除一条广告
     * @author csdeshang
     * @param array $adv_id 广告id
     * @return bool 布尔类型的返回结果
     */
    public function delAdv($adv_id) {
        $condition[] = array('adv_id','=',$adv_id);
        $adv = Db::name('adv')->where($condition)->find();
        if ($adv) {
            // drop cache
            $apId = (int) $adv['ap_id'];
            dkcache("adv/{$apId}");
        }
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_ADV. DIRECTORY_SEPARATOR .$adv['adv_code']);
        return Db::name('adv')->where($condition)->delete();
    }

    /**
     * 删除一个广告位
     * @author csdeshang
     * @param array $ap_id 广告位id
     * @return bool 布尔类型的返回结果
     */
    public function delAdvposition($ap_id) {
        $apId = (int) $ap_id;
        dkcache("adv/{$apId}");
        return Db::name('advposition')->where('ap_id', $apId)->delete();
    }

    /**
     * 获取广告位列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param obj $pagesize 分页页数
     * @param str $orderby 排序
     * @return array 二维数组
     */
    public function getAdvpositionList($condition = array(), $pagesize = '', $orderby = 'ap_id desc') {
        if ($pagesize) {
            $result = Db::name('advposition')->where($condition)->order($orderby)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('advposition')->where($condition)->order($orderby)->select()->toArray();
        }
    }

    public function getOneAdvposition($condition = array()) {
        return Db::name('advposition')->where($condition)->find();
    }

    public function getOneAdv($condition = array()) {
        return Db::name('adv')->where($condition)->find();
    }

    /**
     * 根据条件查询多条记录
     * @author csdeshang
     * @param array $condition 查询条件
     * @param obj $pagesize 分页页数
     * @param int $limit 数量限制
     * @param str $orderby 排序
     * @return array 二维数组
     */
    public function getAdvList($condition = array(), $pagesize = '', $limit = 0, $orderby = 'adv_id desc') {
        if ($pagesize) {
            $result = Db::name('adv')->where($condition)->order($orderby)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('adv')->where($condition)->order($orderby)->select()->toArray();
        }
    }

    /**
     * 手机端广告位获取
     * @author csdeshang
     * @param array $condition 条件
     * @param str $orderby 排序
     * @return array
     */
    public function mbadvlist($condition,$orderby='adv_sort desc'){
         return Db::name('adv')->alias('a')->join('advposition n','a.ap_id=n.ap_id')->where($condition)->order($orderby)->select()->toArray();
    }


    /**
     * 更新记录
     * @author csdeshang
     * @param array $data 更新内容
     * @return bool
     */
    public function editAdv($adv_id,$data) {
        $adv_array = Db::name('adv')->where('adv_id', $adv_id)->find();
        if ($adv_array) {
            // drop cache
            $apId = (int) $adv_array['ap_id'];
            dkcache("adv/{$apId}");
        }
        return Db::name('adv')->where('adv_id', $adv_id)->update($data);
    }

    /**
     * 更新广告位记录
     * @author csdeshang
     * @param array $data 更新内容
     * @return bool
     */
    public function editAdvposition($ap_id,$data) {
        dkcache("adv/{$ap_id}");
        return Db::name('advposition')->where('ap_id', $ap_id)->update($data);
    }



}

?>
