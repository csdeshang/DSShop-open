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
class Album extends BaseModel {
    public $page_info;
    /**
     * 计算数量
     * @author csdeshang
     * @param array $condition 条件
     * @return int
     */
    public function getAlbumpicCount($condition) {
        $result = Db::name('albumpic')->where($condition)->count();
        return $result;
    }

    /**
     * 计算数量
     * @author csdeshang
     * @param array $condition 条件
     * @param string $table 表名
     * @return int
     */
    public function getCount($condition, $table = 'albumpic') {
        $result = Db::name($table)->where($condition)->count();
        return $result;
    }

    /**
     * 获取单条数据
     * @author csdeshang
     * @param array $condition 条件
     * @param string $table 表名
     * @return array 一维数组
     */
    public function getOne($condition, $table = 'albumpic') {
        $resule = Db::name($table)->where($condition)->find();
        return $resule;
    }

    /**
     * 分类列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param obj $pagesize 分页页数
     * @param str $order 排序
     * @return array 二维数组
     */
    public function getAlbumclassList($condition, $pagesize = '', $order = '') {
        $result = Db::name('albumclass')->where($condition)->order($order)->select()->toArray();
        return $result;
    }


    /**
     * 验证相册
     * @author csdeshang
     * @param array $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function checkAlbum($condition) {
        /**
         * 验证是否为有默认相册
         */
        $result = Db::name('albumclass')->where($condition)->select()->toArray();
        if (!empty($result)) {
            unset($result);
            return true;
        }
        unset($result);
        return false;
    }

    /**
     * 图片列表
     * @author csdeshang
     * @param array $condition 查询条件
     * @param obj $pagesize 分页页数
     * @param obj $field 字段名
     * @param obj $order 排序
     * @return array 二维数组
     */
    public function getAlbumpicList($condition, $pagesize = '', $field = '*',$order='apic_id desc') {
        if($pagesize){
            $result = Db::name('albumpic')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        }else{
            $result = Db::name('albumpic')->where($condition)->field($field)->order($order)->select()->toArray();
            return $result;
        }
    }

    /**
     * 添加相册分类
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addAlbumclass($data) {
        return Db::name('albumclass')->insertGetId($data);
    }

    /**
     * 添加相册图片
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool
     */
    public function addAlbumpic($data) {
        $result = Db::name('albumpic')->insertGetId($data);
        return $result;
    }

    /**
     * 更新相册分类
     * @author csdeshang
     * @param array $data 参数内容
     * @param int $id 相册id
     * @return bool
     */
    public function editAlbumclass($data, $id) {
        return Db::name('albumclass')->where('aclass_id', $id)->update($data);
    }

    /**
     * 更新相册图片
     * @author csdeshang
     * @param array $data 参数类容
     * @param int $condition 更新条件
     * @return bool
     */
    public function editAlbumpic($data, $condition) {
        $result = Db::name('albumpic')->where($condition)->update($data);
       return $result;
    }

    /**
     * 删除分类
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delAlbumclass($condition) {
        return Db::name('albumclass')->where($condition)->delete();
    }


    /**
     * 删除图片
     * @author csdeshang
     * @param string $id 图片id
     * @return bool
     */
    public function delAlbumpic($condition) {
        $pic_list = $this->getAlbumpicList($condition, '', 'apic_cover,apic_uploadtime');
        /**
         * 删除图片
         */
        $res=del_albumpic($pic_list);
        return Db::name('albumpic')->where($condition)->delete();
    }

    /**
     * 查询单条分类信息
     * @author csdeshang
     * @param int $condition 查询条件
     * @return array 一维数组
     */
    public function getOneAlbumclass($condition) {
        return Db::name('albumclass')->where($condition)->find();
    }

    /**
     * 根据id查询一张图片
     * @author csdeshang
     * @param int $condition 查询条件
     * @return array 一维数组
     */
    public function getOneAlbumpicById($condition) {
        return Db::name('albumpic')->where($condition)->find();
    }
    /**
     * 获取相册列表
     * @param type $condition
     * @param type $pagesize
     * @param type $field
     * @return type
     */
    public function getGoodsalbumList($condition,$pagesize,$field){
        if($pagesize){
            $result = Db::name('albumclass')->where($condition)->field($field)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        } else {
            return Db::name('albumclass')->where($condition)->field($field)->select()->toArray();
        }        
    }
    /**
     * 获取相册图片数列表
     * @param type $condition
     * @param type $field
     * @param type $group
     * @return type
     */
    public function getAlbumpicCountlist($condition,$field,$group){
        return Db::name('albumpic')->field($field)->group($group)->where($condition)->select()->toArray();
    }

}
