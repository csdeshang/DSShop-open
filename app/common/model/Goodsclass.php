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
class Goodsclass extends BaseModel
{

    /**
     * 缓存数据
     */
    protected $cachedData;

    /**
     * 缓存数据 原H('goods_class')形式
     */
    protected $gcForCacheModel;

    /**
     * 获取缓存数据
     * @access public
     * @author csdeshang
     * @return array
     * array(
     *   'data' => array(
     *     // Id => 记录
     *   ),
     *   'parent' => array(
     *     // 子Id => 父Id
     *   ),
     *   'children' => array(
     *     // 父Id => 子Id数组
     *   ),
     *   'children2' => array(
     *     // 1级Id => 3级Id数组
     *   ),
     * )
     */
    public function getCache()
    {
        if ($this->cachedData) {
            return $this->cachedData;
        }
        $data = rkcache('gc_class');
        if (!$data) {
            $data = array();
            foreach ((array)$this->getGoodsclassList(array()) as $v) {
                $id = $v['gc_id'];
                $pid = $v['gc_parent_id'];
                $data['data'][$id] = $v;
                $data['parent'][$id] = $pid;
                $data['children'][$pid][] = $id;
            }
            foreach ((array)@$data['children'][0] as $id) {
                if (!empty($data['children'][$id])) {
                    foreach ((array)$data['children'][$id] as $cid) {
                        if (!empty($data['children'][$cid])) {
                            foreach ((array)$data['children'][$cid] as $ccid) {
                                $data['children2'][$id][] = $ccid;
                            }
                        }
                    }
                }
            }
            wkcache('gc_class', $data);
        }
        return $this->cachedData = $data;
    }

    /**
     * 删除缓存数据
     * @access public
     * @author csdeshang
     * @return bool
     */
    public function dropCache()
    {
        $this->cachedData = null;
        $this->gcForCacheModel = null;

        dkcache('gc_class');
        dkcache('all_categories');
    }

    /**
     * 类别列表
     * @access public
     * @author csdeshang
     * @param  array $condition 检索条件
     * @param  string $field 字段
     * @return array   返回二位数组
     */
    public function getGoodsclassList($condition, $field = '*')
    {
        $result = Db::name('goodsclass')->field($field)->where($condition)->order('gc_parent_id asc,gc_sort asc,gc_id asc')->select()->toArray();
        return $result;
    }

    /**
     * 从缓存获取全部分类
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getGoodsclassListAll()
    {
        $data = $this->getCache();
        return array_values((array)@$data['data']);
    }

    /**
     * 从缓存获取全部分类 分类id作为数组的键
     * @access public
     * @author csdeshang
     * @return array
     */
    public function getGoodsclassIndexedListAll()
    {
        $data = $this->getCache();
        if($data){
            return (array)$data['data'];
        }
        return '';
    }

    /**
     * 从缓存获取分类 通过分类id数组
     * @access public
     * @author csdeshang
     * @param array $ids 分类id数组
     * @return array
     */
    public function getGoodsclassListByIds($ids)
    {
        $data = $this->getCache();
        $ret = array();
        foreach ((array)$ids as $i) {
            if (isset($data['data'][$i]) && $data['data'][$i]) {
                $ret[] = $data['data'][$i];
            }
        }
        return $ret;
    }


    /**
     * 从缓存获取分类 通过上级分类id
     * @access public
     * @author csdeshang
     * @param type $pid 上级分类id 若传0则返回1级分类
     * @return type
     */
    public function getGoodsclassListByParentId($pid)
    {
        $data = $this->getCache();
        $ret = array();
        if (!empty($data['children'][$pid])) {
            foreach ((array)$data['children'][$pid] as $i) {
                if ($data['data'][$i]) {
                    $ret[] = $data['data'][$i];
                }
            }
        }

        return $ret;
    }

    /**
     * 从缓存获取分类 通过分类id
     * @access public
     * @author csdeshang
     * @param type $id 分类id
     * @return type
     */
    public function getGoodsclassInfoById($id)
    {
        $data = $this->getCache();
        return @$data['data'][$id];
    }

    /**
     * 返回缓存数据 原H('goods_class')形式
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getGoodsclassForCacheModel()
    {

        if ($this->gcForCacheModel)
            return $this->gcForCacheModel;

        $data = $this->getCache();
        $r = isset($data['data'])?$data['data']:'';
        $p = isset($data['parent'])?$data['parent']:'';
        $c = isset($data['children'])?$data['children']:'';
        $c2 = empty($data['children2']) ? '' : $data['children2'];
       if(!empty($r)) {
           $r = (array)$r;
           foreach ($r as $k => & $v) {
               if ((string)$p[$k] == '0') {
                   $v['depth'] = 1;
                   if (!empty($data['children'][$k])) {
                       $v['child'] = implode(',', $c[$k]);
                   }
                   if (!empty($data['children2'][$k])) {
                       $v['childchild'] = implode(',', $c2[$k]);
                   }
               }
               else if (isset($p[$p[$k]]) && (string)$p[$p[$k]] == '0') {
                   $v['depth'] = 2;
                   if (isset($data['children'][$k])) {
                       $v['child'] = implode(',', $c[$k]);
                   }
               }
               else if (isset($p[$p[$k]]) && isset($p[$p[$p[$k]]]) && (string)$p[$p[$p[$k]]] == '0') {
                   $v['depth'] = 3;
               }
           }
       }

        return $this->gcForCacheModel = $r;
    }

    /**
     * 更新信息
     * @access public
     * @author csdeshang
     * @param type $data 更新数据
     * @param type $condition 条件
     * @return bool
     */
    public function editGoodsclass($data = array(), $condition = array())
    {
        // 删除缓存
        $this->dropCache();
        return Db::name('goodsclass')->where($condition)->update($data);
    }

    /**
     * 取得店铺绑定的分类
     * @access public
     * @author csdeshang
     * @param   number $pid 父级分类id
     * @param   number $deep 深度
     * @return  array   二维数组
     */
    public function getGoodsclass($pid = 0, $deep = 1)
    {
        // 读取商品分类
        $gc_list_o = $gc_list = $this->getGoodsclassListByParentId($pid);
        return $gc_list;
    }

    /**
     * 删除商品分类
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return boolean
     */
    public function delGoodsclass($condition)
    {
        // 删除缓存
        $this->dropCache();
        return Db::name('goodsclass')->where($condition)->delete();
    }

    /**
     * 删除商品分类
     * @access public
     * @author csdeshang
     * @param array $gcids 分类ID
     * @return boolean
     */
    public function delGoodsclassByGcIdString($gcids)
    {
        $gcids = explode(',', $gcids);
        if (empty($gcids)) {
            return false;
        }
        $goods_class = $this->getGoodsclassForCacheModel();
        $gcid_array = array();
        foreach ($gcids as $gc_id) {
            $child = (!empty($goods_class[$gc_id]['child'])) ? explode(',', $goods_class[$gc_id]['child']) : array();
            $childchild = (!empty($goods_class[$gc_id]['childchild'])) ? explode(',', $goods_class[$gc_id]['childchild']) : array();
            $gcid_array = array_merge($gcid_array, array($gc_id), $child, $childchild);
        }
        // 删除商品分类
        $this->delGoodsclass(array(array('gc_id','in', $gcid_array)));
        // 删除常用商品分类
        model('goodsclassstaple')->delGoodsclassstaple(array(array('gc_id_1|gc_id_2|gc_id_3','in', $gcid_array)));
        // 删除分类tag表
        model('goodsclasstag')->delGoodsclasstag(array(array('gc_id_1|gc_id_2|gc_id_3','in', $gcid_array)));
        return true;
    }

    /**
     * 前台头部的商品分类
     * @access public
     * @author csdeshang
     * @param  number $update_all 更新
     * @return array   数组
     */
    public function get_all_category($update_all = 0)
    {
        // 不存在时更新或者强制更新时执行
        if ($update_all == 1 || !($gc_list = rkcache('all_categories'))) {
            $class_list = $this->getTreeClassList(3);
            $gc_list = [];
            $type_ids = array(); //第2级分类关联类型
            if (is_array($class_list) && !empty($class_list)) {
                //遍历一级商品分类
                foreach ($class_list as $k => $v) {
                    if ($v[ 'deep' ] == 1) {
                        $nav_info = $this->_getGoodsclassnavById($v['gc_id']);//一级分类推荐品牌
                        $gc_list[$v['gc_id']] = array_merge($v, $nav_info);
                        unset($class_list[ $k ]);
                    }
                }
                $class_list = array_values($class_list);
                //遍历二级商品分类
                foreach ($class_list as $k => $v) {
                    foreach ($gc_list as $ck => $cv) {
                        if ($v[ 'deep' ] == 2 && $cv[ 'gc_id' ] == $v[ 'gc_parent_id' ]) {
                            $gc_list[$cv['gc_id']][ 'class2' ][$v['gc_id']] = $v;
                            $type_ids[] = $cv['type_id'];
                            unset($class_list[ $k ]);
                        }
                    }
                }
                $class_list = array_values($class_list);
                //遍历三级商品分类
                foreach ($class_list as $k => $v) {
                    foreach ($gc_list as $ck => $cv) {
                        if (!empty($cv[ 'class2' ])) {
                            foreach ($cv[ 'class2' ] as $third_k => $third_v) {
                                if ($v[ 'deep' ] == 3 && $third_v[ 'gc_id' ] == $v[ 'gc_parent_id' ]) {
                                    $gc_list[$cv['gc_id']][ 'class2' ][$third_v['gc_id']][ 'class3' ][] = $v;
                                    unset($class_list[ $k ]);
                                }
                            }
                        }
                    }
                }
                $type_brands = $this->get_type_brands($type_ids); //类型关联品牌
                foreach ($gc_list as $key => $value) {
                    $class2s = isset($value['class2']) ? $value['class2'] : '';
                    if (is_array($class2s) && !empty($class2s)) {//第2级关联品牌
                        foreach ($class2s as $k2 => $v2) {
                            $p_id = $v2['gc_parent_id'];
                            $gc_id = $v2['gc_id'];
                            $type_id = $v2['type_id'];
                            $gc_list[$p_id]['class2'][$gc_id]['brands'] = isset($type_brands[$type_id]) ? $type_brands[$type_id] : '';
                        }
                    }
                }
            }
            wkcache('all_categories', $gc_list);
        }
        return $gc_list;
    }

    /**
     * 类型关联品牌
     * @access public
     * @author csdeshang
     * @param   array $type_ids 类型
     * @return  array   数组
     */
    public function get_type_brands($type_ids = array())
    {
        $brands = array(); //品牌
        $type_brands = array(); //类型关联品牌
        if (is_array($type_ids) && !empty($type_ids)) {
            $type_ids = array_unique($type_ids);
            $type_list = Db::name('typebrand')->where(array(array('type_id','in', $type_ids)))->limit(10000)->select()->toArray();
            if (is_array($type_list) && !empty($type_list)) {
                $brand_mod=model('brand');
                $brand_list = $brand_mod->getBrandList(array(),'brand_id,brand_name,brand_pic',10000);
                if (is_array($brand_list) && !empty($brand_list)) {
                    foreach ($brand_list as $key => $value) {
                        $brand_id = $value['brand_id'];
                        $brands[$brand_id] = $value;
                    }

                    foreach ($type_list as $key => $value) {
                        $type_id = $value['type_id'];
                        $brand_id = $value['brand_id'];
                        $brand = $brands[$brand_id];
                        if (is_array($brand) && !empty($brand)) {
                            $type_brands[$type_id][$brand_id] = $brand;
                        }
                    }
                }
            }
        }
        return $type_brands;
    }
    /**
     * 获取商品分类导航
     * @access public
     * @author csdeshang
     * @param int $gc_id 商品分类id
     * @return type
     */
    private function _getGoodsclassnavById($gc_id)
    {
        $classnav_model = model('goodsclassnav');
        $brand_model = model('brand');

        $nav_info = $classnav_model->getGoodsclassnavInfoByGcId($gc_id);
        if (empty($nav_info)) {
            return array();
        }

        if (!empty($nav_info['goodscn_pic'])) {
            $nav_info['goodscn_pic'] = ds_get_pic( ATTACH_GOODS_CLASS , $nav_info['goodscn_pic']);
        }
        else {
            unset($nav_info['goodscn_pic']);
        }
        if (!empty($nav_info['goodscn_adv1'])) {
            $nav_info['goodscn_adv1'] = ds_get_pic( ATTACH_GOODS_CLASS , $nav_info['goodscn_adv1']);
        }
        else {
            unset($nav_info['goodscn_adv1']);
        }
        if (!empty($nav_info['goodscn_adv2'])) {
            $nav_info['goodscn_adv2'] = ds_get_pic( ATTACH_GOODS_CLASS , $nav_info['goodscn_adv2']);
        }
        else {
            unset($nav_info['goodscn_adv2']);
        }
        if ($nav_info['goodscn_brandids'] != '') {
            $nav_info['cn_brands'] = $brand_model->getBrandList(array(array('brand_id','in', $nav_info['goodscn_brandids'])));
            unset($nav_info['goodscn_brandids']);
        }
        if ($nav_info['goodscn_classids'] != '') {
            $nav_info['cn_classs'] = $this->getGoodsclassList(array(array('gc_id','in', $nav_info['goodscn_classids'])));
            unset($nav_info['goodscn_classids']);
        }
        if ($nav_info['goodscn_alias'] != '') {
            $nav_info['gc_name'] = $nav_info['goodscn_alias'];
            unset($nav_info['goodscn_alias']);
        }
        return $nav_info;
    }

    /**
     * 新增商品分类
     * @access public
     * @author csdeshang
     * @param array $data 参数数据
     * @return boolean
     */
    public function addGoodsclass($data)
    {
        // 删除缓存
        $this->dropCache();
        return Db::name('goodsclass')->insertGetId($data);
    }

    /**
     * 取分类列表，最多为三级
     * @access public
     * @author csdeshang
     * @param int $show_deep 显示深度
     * @param array $condition 检索条件
     * @return array 数组类型的返回结果
     */
    public function getTreeClassList($show_deep = '3', $condition = array())
    {
        $class_list = $this->getGoodsclassList($condition);
        $goods_class = array(); //分类数组
        if (is_array($class_list) && !empty($class_list)) {
            $show_deep = intval($show_deep);
            if ($show_deep == 1) {//只显示第一级时用循环给分类加上深度deep号码
                foreach ($class_list as $val) {
                    if ($val['gc_parent_id'] == 0) {
                        $val['deep'] = 1;
                        $goods_class[] = $val;
                    }
                    else {
                        break; //父类编号不为0时退出循环
                    }
                }
            }
            else {//显示第二和三级时用递归
                $goods_class = $this->_getTreeClassList($show_deep, $class_list);
            }
        }
        return $goods_class;
    }

    /**
     * 递归 整理分类
     * @access public
     * @author csdeshang
     * @param int $show_deep 显示深度
     * @param array $class_list 类别内容集合
     * @param int $deep 深度
     * @param int $parent_id 父类编号
     * @param int $i 上次循环编号
     * @return array $show_class 返回数组形式的查询结果
     */
    private function _getTreeClassList($show_deep, $class_list, $deep = 1, $parent_id = 0, $i = 0)
    {
        static $show_class = array(); //树状的平行数组
        if (is_array($class_list) && !empty($class_list)) {
            $size = count($class_list);
            if ($i == 0)
                $show_class = array(); //从0开始时清空数组，防止多次调用后出现重复
            for ($i; $i < $size; $i++) {//$i为上次循环到的分类编号，避免重新从第一条开始
                $val = $class_list[$i];
                $gc_id = $val['gc_id'];
                $gc_parent_id = $val['gc_parent_id'];
                if ($gc_parent_id == $parent_id) {
                    $val['deep'] = $deep;
                    $show_class[] = $val;
                    if ($deep < $show_deep && $deep < 3) {//本次深度小于显示深度时执行，避免取出的数据无用
                        $this->_getTreeClassList($show_deep, $class_list, $deep + 1, $gc_id, 1);
                    }
                }
                if ($gc_parent_id > $parent_id)
                    break; //当前分类的父编号大于本次递归的时退出循环
            }
        }
        return $show_class;
    }

    /**
     * 取指定分类ID下的所有子类
     * @access public
     * @author csdeshang
     * @staticvar type $_cache 
     * @param int $parent_id 父ID 可以单一可以为数组
     * @return array 返回数组形式的查询结果
     */
    public function getChildClass($parent_id)
    {
        static $_cache;
        if ($_cache !== null)
            return $_cache;
        $all_class = $this->getGoodsclassListAll();
        if (is_array($all_class)) {
            if (!is_array($parent_id)) {
                $parent_id = array($parent_id);
            }
            $result = array();
            foreach ($all_class as $k => $v) {
                $gc_id = $v['gc_id']; //返回的结果包括父类
                $gc_parent_id = $v['gc_parent_id'];
                if (in_array($gc_id, $parent_id) || in_array($gc_parent_id, $parent_id)) {
                    $parent_id[] = $v['gc_id'];
                    $result[] = $v;
                }
            }
            $return = $result;
        }
        else {
            $return = false;
        }
        return $_cache = $return;
    }

    /**
     * 取指定分类ID的导航链接
     * @access public
     * @author csdeshang
     * @param int $id 父类ID/子类ID
     * @param int $sign 1、0 1为最后一级不加超链接，0为加超链接
     * @return array $nav_link 返回数组形式类别导航连接
     */
    public function getGoodsclassnav($id = 0, $sign = 1)
    {
        if (intval($id) > 0) {
            $data = $this->getGoodsclassIndexedListAll();
            // 当前分类不加超链接
            if ($sign == 1) {
                if (isset($data[$id])) {
                    $nav_link [] = array(
                        'title' => $data[$id]['gc_name']
                    );
                }
            }
            else {
                if (isset($data[$id])) {
                    $nav_link [] = array(
                        'title' => isset($data[$id]['gc_name']) ? $data[$id]['gc_name'] : '..',
                        'link' => url('/home/Search/index', ['cate_id' => $data[$id]['gc_id']]),
                    );
                }
            }
            if (isset($data[$id])) {
                // 最多循环4层
                for ($i = 1; $i < 5; $i++) {
                    if ($data[$id]['gc_parent_id'] == '0') {
                        break;
                    }
                    $id = $data[$id]['gc_parent_id'];
                    if(!isset($data[$id])){
                      break;
                    }
                    $nav_link[] = array(
                        'title' => $data[$id]['gc_name'],
                        'link' => url('/home/Search/index', ['cate_id' => $data[$id]['gc_id']])
                    );
                }
            }
        }
        else {
            // 加上 首页 商品分类导航
            $nav_link[] = array('title' => lang('goods_class_index_search_results'));
        }
        // 首页导航
        $nav_link[] = array('title' => lang('ds_index'), 'link' => url('home/Index/index'));

        krsort($nav_link);
        return $nav_link;
    }

    /**
     * 根据一级分类id取得所有三级分类
     * @access public
     * @author csdeshang
     * @param type $id 分类ID
     * @return type
     */
    public function getChildClassByFirstId($id)
    {
        $data = $this->getCache();
        $result = array();
        if (!empty($data['children2'][$id])) {
            foreach ($data['children2'][$id] as $val) {
                $child = $data['data'][$val];
                $result[$child['gc_parent_id']]['class'][$child['gc_id']] = $child['gc_name'];
                $result[$child['gc_parent_id']]['name'] = $data['data'][$child['gc_parent_id']]['gc_name'];
            }
        }
        return $result;
    }

    /**
     * 取指定分类ID的所有父级分类
     * @access public
     * @author csdeshang
     * @param int $id 父类ID/子类ID
     * @return array 
     */
    public function getGoodsclassLineForTag($id = 0)
    {
        if (intval($id) > 0) {
            $gc_line = array();
            /**
             * 取当前类别信息
             */
            $class = $this->getGoodsclassInfoById(intval($id));
            $gc_line['gc_id'] = $class['gc_id'];
            $gc_line['type_id'] = $class['type_id'];
            $gc_line['gc_virtual'] = $class['gc_virtual'];
            $gc_line['gctag_name']='>';
            $gc_line['gctag_value']=',';
            /**
             * 是否是子类
             */
            if ($class['gc_parent_id'] != 0) {
                $parent_1 = $this->getGoodsclassInfoById($class['gc_parent_id']);
                if ($parent_1['gc_parent_id'] != 0) {
                    $parent_2 = $this->getGoodsclassInfoById($parent_1['gc_parent_id']);
                    $gc_line['gc_id_1'] = $parent_2['gc_id'];
                    $gc_line['gctag_name'] = trim($parent_2['gc_name']) . ' >';
                    $gc_line['gctag_value'] = trim($parent_2['gc_name']) . ',';
                }
                if (!isset($gc_line['gc_id_1'])) {
                    $gc_line['gc_id_1'] = $parent_1['gc_id'];
                }
                else {
                    $gc_line['gc_id_2'] = $parent_1['gc_id'];
                }
                $gc_line['gctag_name'] .= trim($parent_1['gc_name']) . ' >';
                $gc_line['gctag_value'] .= trim($parent_1['gc_name']) . ',';
            }
            if (!isset($gc_line['gc_id_1'])) {
                $gc_line['gc_id_1'] = $class['gc_id'];
            }
            else if (!isset($gc_line['gc_id_2'])) {
                $gc_line['gc_id_2'] = $class['gc_id'];
            }
            else {
                $gc_line['gc_id_3'] = $class['gc_id'];
            }
            $gc_line['gctag_name'] .= trim($class['gc_name']) . ' >';
            $gc_line['gctag_value'] .= trim($class['gc_name']) . ',';
        }
        $gc_line['gctag_name'] = trim($gc_line['gctag_name'], ' >');
        $gc_line['gctag_value'] = trim($gc_line['gctag_value'], ',');
        return $gc_line;
    }

    /**
     * 取得分类关键词，方便SEO
     * @access public
     * @author csdeshang
     * @param type $gc_id 商品分类ID
     * @return boolean
     */
    public function getKeyWords($gc_id = null)
    {
        if (empty($gc_id))
            return false;
        $keywrods = rkcache('goodsclassseo', true);
        if (empty($keywrods)) {
            return array(1 => '', 2 => trim('', ','), 3 => trim('', ','));
        }
        $seo_title = $keywrods[$gc_id]['title'];
        $seo_key = '';
        $seo_desc = '';
        if ($gc_id > 0) {
            if (isset($keywrods[$gc_id])) {
                $seo_key .= $keywrods[$gc_id]['key'] . ',';
                $seo_desc .= $keywrods[$gc_id]['desc'] . ',';
            }
            $goods_class = model('goodsclass')->getGoodsclassIndexedListAll();
            if (($gc_id = $goods_class[$gc_id]['gc_parent_id']) > 0) {
                if (isset($keywrods[$gc_id])) {
                    $seo_key .= $keywrods[$gc_id]['key'] . ',';
                    $seo_desc .= $keywrods[$gc_id]['desc'] . ',';
                }
            }
            if (($gc_id = $goods_class[$gc_id]['gc_parent_id']) > 0) {
                if (isset($keywrods[$gc_id])) {
                    $seo_key .= $keywrods[$gc_id]['key'] . ',';
                    $seo_desc .= $keywrods[$gc_id]['desc'] . ',';
                }
            }
        }
        return array(1 => $seo_title, 2 => trim($seo_key, ','), 3 => trim($seo_desc, ','));
    }

    /**
     * 获得商品分类缓存
     * @access public
     * @author csdeshang
     * @param int $choose_gcid 选择分类ID
     * @param int $show_depth 需要展示分类深度
     * @return array 返回分类数组和选择分类id数组
     */
    public function getGoodsclassCache($choose_gcid, $show_depth = 3)
    {
        $gc_list = $this->getGoodsclassForCacheModel();
        //获取需要展示的分类数组
        $show_gc_list = array();
        if(!empty($gc_list)) {
            foreach ((array)$gc_list as $k => $v) {
                if(isset($v['depth'])){
                    if ($v['depth'] < $show_depth) {
                        $show_gc_list[$v['gc_id']] = $v;
                    }
                    elseif ($v['depth'] == $show_depth) {
                        unset($v['child'], $v['childchild']);
                        $show_gc_list[$v['gc_id']] = $v;
                    }
                }
            }
        }
        $choose_gcidarr = array();
        if ($choose_gcid > 0) {
            //遍历出选择商品分类的上下级ID
            $gc_depth = $gc_list[$choose_gcid]['depth'];
            $parentid = $choose_gcid;
            for ($i = $gc_depth - 1; $i >= 0; $i--) {
                $choose_gcidarr[$i] = $parentid;
                $parentid = $gc_list[$parentid]['gc_parent_id'];
            }
        }
        return array('showclass' => $show_gc_list, 'choose_gcid' => $choose_gcidarr);
    }
}

?>
