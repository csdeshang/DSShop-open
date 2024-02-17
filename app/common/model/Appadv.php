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
class Appadv extends BaseModel
{
    /**
     * 获取APP广告位列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页页数
     * @param str $orderby 排序
     * @return array 二维数组
     */
    public function getAppadvpositionList($condition = array(), $pagesize = '', $orderby = 'ap_id desc') {
        if ($pagesize) {
            $result = Db::name('appadvposition')->where($condition)->order($orderby)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('appadvposition')->where($condition)->order($orderby)->select()->toArray();
        }
    }

    /**
     * 根据条件查询多条记录
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页页数
     * @param int $limit 数量限制
     * @param str $orderby 排序
     * @return array 二维数组
     */
    public function getAppadvList($condition = array(), $pagesize = '', $limit = 0, $orderby = 'adv_sort asc') {
        if ($pagesize) {
            $result = Db::name('appadv')->where($condition)->order($orderby)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('appadv')->where($condition)->order($orderby)->select()->toArray();
        }
    }
    /**
     * 新增广告位
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addAppadvposition($data) {
        return Db::name('appadvposition')->insertGetId($data);
    }
    /**
     * 新增广告
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addAppadv($data) {
        $result = Db::name('appadv')->insertGetId($data);
        $apId = (int) $data['ap_id'];
        dkcache("appadv/{$apId}");
        return $result;
    }
    /**
     * 更新广告位记录
     * @author csdeshang
     * @param array $data 更新内容
     * @return bool
     */
    public function editAppadvposition($ap_id,$data) {
        dkcache("appadv/{$ap_id}");
        return Db::name('appadvposition')->where('ap_id', $ap_id)->update($data);
    }
     
    /**
     * 获取一个app广告位
     * @author csdeshang
     * @param array $condition 查询条件
     * @return array
     */
    public function getOneAppadvposition($condition = array()) {
        return Db::name('appadvposition')->where($condition)->find();
    }

    /**
     * 删除一个广告位
     * @author csdeshang
     * @param array $ap_id 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function delAppadvposition($ap_id) {
        $apId = (int) $ap_id;
        dkcache("appadv/{$apId}");
        return Db::name('appadvposition')->where('ap_id', $apId)->delete();
    }
    /**
     * 获取一个广告位
     * @author csdeshang
     * @param array $condition 条件
     * @return type
     */
    public function getOneAppadv($condition = array()) {
        return Db::name('appadv')->where($condition)->find();
    }

    /**
     * 更新记录
     * @author csdeshang
     * @param array $data 更新内容
     * @return bool
     */
    public function editAppadv($adv_id,$data) {
        $adv_array = Db::name('appadv')->where('adv_id', $adv_id)->find();
        if ($adv_array) {
            // drop cache
            $apId = (int) $adv_array['ap_id'];
            dkcache("appadv/{$apId}");
        }
        return Db::name('appadv')->where('adv_id', $adv_id)->update($data);
    }

    /**
     * 删除一条广告
     * @author csdeshang
     * @param array $adv_id 广告位id
     * @return bool 布尔类型的返回结果
     */
    public function delAppadv($adv_id) {
        $condition[] = array('adv_id','=',$adv_id);
        $adv = Db::name('appadv')->where($condition)->find();
        if ($adv) {
            // drop cache
            $apId = (int) $adv['ap_id'];
            dkcache("appadv/{$apId}");
        }
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_APPADV. DIRECTORY_SEPARATOR .$adv['adv_code']);
        return Db::name('appadv')->where($condition)->delete();
    }
}