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
class Articleclass extends BaseModel
{
    
    /**
     * 类别列表
     * @access public
     * @author csdeshang
     * @param array $condition 检索条件
     * @return array 数组结构的返回结果
     */
    public function getArticleclassList($condition,$order='ac_parent_id asc,ac_sort asc,ac_id asc'){
        $result = Db::name('articleclass')->where($condition)->order($order)->select()->toArray();
        return $result;
    }


    /**
     * 取单个分类的内容
     * @access public
     * @author csdeshang
     * @param int $id 分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneArticleclass($id){
        if (intval($id) > 0){
            $value = intval($id);
            $result = Db::name('articleclass')->where(array('ac_id'=>$value))->find();
            return $result;
        }else {
            return false;
        }
    }

    /**
     * 新增
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addArticleclass($data){
        $result = Db::name('articleclass')->insertGetId($data);
        return $result;
    }

    /**
     * 更新信息
     * @access public
     * @author csdeshang
     * @param array $data 更新数据
     * @param array $ac_id 内容ID
     * @return bool 布尔类型的返回结果
     */
    public function editArticleclass($data,$ac_id){
        $result =Db::name('articleclass')->where("ac_id",$ac_id)->update($data);
        return $result;
    }

    /**
     * 删除分类
     * @access public
     * @author csdeshang
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function delArticleclass($id){
        return Db::name('articleclass')->where("ac_id = '". intval($id) ."'")->delete();
    }

    /**
     * 取分类列表，按照深度归类
     * @access public
     * @author csdeshang
     * @param int $show_deep 显示深度
     * @return array 数组类型的返回结果
     */
    public function getTreeClassList($show_deep='2'){
        $condition	= array();
        $class_list = $this->getArticleclassList($condition);
        $show_deep = intval($show_deep);
        $result = array();
        if(is_array($class_list) && !empty($class_list)) {
            $result = $this->_getTreeClassList($show_deep,$class_list);
        }
        return $result;
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
    private function _getTreeClassList($show_deep,$class_list,$deep=1,$parent_id=0,$i=0){
        static $show_class = array();//树状的平行数组
        if(is_array($class_list) && !empty($class_list)) {
            $size = count($class_list);
            if($i == 0) $show_class = array();//从0开始时清空数组，防止多次调用后出现重复
            for ($i;$i < $size;$i++) {//$i为上次循环到的分类编号，避免重新从第一条开始
                $val = $class_list[$i];
                $ac_id = $val['ac_id'];
                $ac_parent_id	= $val['ac_parent_id'];
                if($ac_parent_id == $parent_id) {
                    $val['deep'] = $deep;
                    $show_class[] = $val;
                    if($deep < $show_deep && $deep < 2) {//本次深度小于显示深度时执行，避免取出的数据无用
                        $this->_getTreeClassList($show_deep,$class_list,$deep+1,$ac_id,$i+1);
                    }
                }
                if($ac_parent_id > $parent_id) break;//当前分类的父编号大于本次递归的时退出循环
            }
        }
        return $show_class;
    }

    /**
     * 取指定分类ID下的所有子类
     * @access public
     * @author csdeshang
     * @param int/array $parent_id 父ID 可以单一可以为数组
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function getChildClass($parent_id){
        $all_class = $this->getArticleclassList(array());
        if (is_array($all_class)){
            if (!is_array($parent_id)){
                $parent_id = array($parent_id);
            }
            $result = array();
            foreach ($all_class as $k => $v){
                $ac_id	= $v['ac_id'];//返回的结果包括父类
                $ac_parent_id	= $v['ac_parent_id'];
                if (in_array($ac_id,$parent_id) || in_array($ac_parent_id,$parent_id)){
                    $result[] = $v;
                }
            }
            return $result;
        }else {
            return false;
        }
    }
}