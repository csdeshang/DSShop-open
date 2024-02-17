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
class Area extends BaseModel
{


    /**
     * 获取地址列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param obj $fields 字段
     * @param array $group 分组
     * @param int $limit 数量限制
     * @param str $order 排序
     * @return array
     */
    public function getAreaList($condition = array(), $fields = '*', $group = '', $limit = 0, $order='')
    {
        return Db::name('area')->where($condition)->field($fields)->limit($limit)->order($order)->group($group)->select()->toArray();
    }

    /**
     * 从缓存获取分类 通过分类id
     * @access public
     * @author csdeshang
     * @param int $id 分类id
     * @return array
     */
    public function getAreaInfoById($id)
    {
        $data = $this->getCache();
        return $data['name'][$id];
    }

    /**
     * 获取地址详情
     * @access public
     * @author csdeshang
     * @param int $condition 条件
     * @param array $field 字段
     * @return array
     */
    public function getAreaInfo($condition = array(), $field = '*')
    {
        return Db::name('area')->where($condition)->field($field)->find();
    }

    /**
     * 获取一级地址（省级）名称数组
     * @access public
     * @author csdeshang
     * @return array 键为id 值为名称字符串
     */
    public function getTopLevelAreas()
    {
        $data = $this->getCache();
        
        $arr = array();
        foreach ($data['children'][0] as $i) {
            $arr[$i] = $data['name'][$i];
        }
        return $arr;
    }

    /**
     * 获取获取市级id对应省级id的数组
     * @access public
     * @author csdeshang
     * @return array 键为市级id 值为省级id
     */
    public function getCityProvince()
    {
        $data = $this->getCache();

        $arr = array();
        foreach ($data['parent'] as $k => $v) {
            if ($v && $data['parent'][$v] == 0) {
                $arr[$k] = $v;
            }
        }

        return $arr;
    }

    /**
     * 获取地区缓存
     * @access public
     * @author csdeshang
     * @return array
     */
    public function getAreas()
    {
        return $this->getCache();
    }

    /**
     * 获取全部地区名称数组
     * @access public
     * @author csdeshang
     * @return array 键为id 值为名称字符串
     */
    public function getAreaNames()
    {
        $data = $this->getCache();

        return $data['name'];
    }

    /**
     * 获取用于前端js使用的全部地址数组
     * @access public
     * @author csdeshang
     * @param str $src 缓存
     * @return array
     */
    public function getAreaArrayForJson($src = 'cache')
    {
        if ($src == 'cache') {
            $data = $this->getCache();
        } else {
            $data = $this->_getAllArea();
        }

        $arr = array();
        foreach ($data['children'] as $k => $v) {
            foreach ($v as $vv) {
                $arr[$k][] = array($vv, $data['name'][$vv]);
            }
        }
        return $arr;
    }

    /**
     * 更新编辑信息
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @param array $condition 条件
     * @return bool
     */
    public function editArea($data = array(), $condition = array())
    {
        // 删除缓存
        $this->dropCache();
        return Db::name('area')->where($condition)->update($data);
    }

    /**
     * 新增地区
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return boolean 
     */
    public function addArea($data)
    {
        // 删除缓存
        $this->dropCache();
        return Db::name('area')->insertGetId($data);
    }

    /**
     * 删除地区
     * @access public
     * @author csdeshang
     * @param array $area_ids 地区id
     * @return boolean
     */
    public function delAreaByAreaIds($area_ids)
    {
        $area_arr = explode(',', $area_ids);
        if (empty($area_arr)) {
            return false;
        }

        $child_list = $this->getAreaList(array(array('area_parent_id','in', $area_arr)));
        if (!empty($child_list)) {
            foreach ($child_list as $v) {
                $area_child_arr[] = $v['area_id'];
            }

            $area_child_ids = implode(',', $area_child_arr);
            $this->delAreaByAreaIds($area_child_ids);
        }
        // 删除地区
        $this->delArea(array(array('area_id','in', $area_arr)));
    }

    /**
     * 删除地区
     * @access public
     * @author csdeshang
     * @param unknown $condition 条件
     * @return boolean
     */
    public function delArea($condition)
    {
        // 删除缓存
        $this->dropCache();
        return Db::name('area')->where($condition)->delete();
    }


    /**
     * 删除缓存数据
     * @access public
     * @author csdeshang
     */
    public function dropCache()
    {
        $this->cachedData = null;

        dkcache('area');
    }

    /**
     * 获取地区数组 格式如下
     * array(
     *   'name' => array(
     *     '地区id' => '地区名称',
     *     // ..
     *   ),
     *   'parent' => array(
     *     '子地区id' => '父地区id',
     *     // ..
     *   ),
     *   'children' => array(
     *     '父地区id' => array(
     *       '子地区id 1',
     *       '子地区id 2',
     *       // ..
     *     ),
     *     // ..
     *   ),
     *   'region' => array(array(
     *     '华北区' => array(
     *       '省级id 1',
     *       '省级id 2',
     *       // ..
     *     ),
     *     // ..
     *   ),
     * )
     *
     * @return array
     */
    protected function getCache()
    {
        // 对象属性中有数据则返回
        if ($this->cachedData !== null)
            return $this->cachedData;

        // 缓存中有数据则返回
        if ($data = rkcache('area')) {
            $this->cachedData = $data;
            return $data;
        }

        // 查库
        $data = $this->_getAllArea();
        wkcache('area', $data);
        $this->cachedData = $data;

        return $data;
    }

    protected $cachedData;

    /**
     * 获取所有地区
     * @access public
     * @author csdeshang
     * @return array
     */
    private function _getAllArea()
    {
        $data = array();
        $area_all_array = Db::name('area')->select()->toArray();
        foreach ((array)$area_all_array as $a) {
            $data['name'][$a['area_id']] = $a['area_name'];
            $data['parent'][$a['area_id']] = $a['area_parent_id'];
            $data['children'][$a['area_parent_id']][] = $a['area_id'];

            if ($a['area_deep'] == 1 && $a['area_region'])
                $data['region'][$a['area_region']][] = $a['area_id'];
        }

        wkcache('area', $data);
        $this->cachedData = $data;

        return $data;
    }



    /**
     * 从缓存获取分类 通过上级分类id
     * @access public
     * @author csdeshang
     * @param int $pid 上级分类id 若传0则返回1级分类
     * @return array
     */
    public function getAreaListByParentId($pid)
    {
        $data = $this->getCache();
        $ret = array();
        foreach ((array)$data['children'][$pid] as $i) {
            if ($data['data'][$i]) {
                $ret[] = $data['data'][$i];
            }
        }
        return $ret;
    }

    /**
     * 递归取得本地区及所有上级地区名称
     * @access public
     * @author csdeshang
     * @param int $area_id 地区id
     * @param str $area_name 地区名称
     * @return array
     */
    public function getTopAreaName($area_id, $area_name = '')
    {
        $info_parent = $this->getAreaInfo(array('area_id' => $area_id), 'area_name,area_parent_id');
        if ($info_parent) {
            return $this->getTopAreaName($info_parent['area_parent_id'], $info_parent['area_name']) . ' ' . $info_parent['area_name'];
        }
    }

    /**
     * 递归取得本地区所有孩子ID
     * @access public
     * @author csdeshang
     * @param $area_id 地区id
     * @return array
     */
    public function getChildrenIDs($area_id)
    {
        $result = array();
        $list = $this->getAreaList(array('area_parent_id' => $area_id), 'area_id');
        if ($list) {
            foreach ($list as $v) {
                $result[] = $v['area_id'];
                $result = array_merge($result, $this->getChildrenIDs($v['area_id']));
            }
        }
        return $result;
    }

    /**
     * 从缓存获取所有子节点 通过id
     * @access public
     * @author csdeshang 
     * @param int $pid 上级地区id 若传0则返回1级分类
     * @return array
     */
    public function getChildsByPid($pid)
    {
        $data = $this->getCache();
        if (!isset($data['children'][$pid])) {
            return false;
        }
        $ret = array();
        foreach ((array)$data['children'][$pid] as $i) {
            $ret[] = $i;
            $ret_temp = $this->getChildsByPid($i);
            if ($ret_temp != false) {
                foreach ($ret_temp as $v) {
                    $ret[] = $v;
                }
            }
        }
        return $ret;
    }
    
    /**
     * 获取子节点名称通过id
     * @access public
     * @author csdeshang 
     * @param int $pid id
     * @return array
     */
    public function GetChildName($pid){
        return Db::name('area')->field('area_id,area_name')->where(array('area_parent_id'=>$pid))->select()->toArray();
    }
    /**
     * 获取列表 根据id
     * @access public
     * @author csdeshang
     * @param type $parent_id id
     * @return array
     */
    public function get_list($parent_id=-1){
        $map = array();
        if ($parent_id >= 0) {
            $map['area_parent_id'] = $parent_id;
        }
        return Db::name('area')->where($map)->order('area_sort')->select()->toArray();
    }
}