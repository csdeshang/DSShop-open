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
class Inform extends BaseModel {

    public $page_info;

    /**
     * 查询举报数量
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return int
     */
    public function getInformCount($condition) {
        return Db::name('inform')->where($condition)->count();
    }

    /**
     * 增加
     * @access public
     * @author csdeshang
     * @param array $data  参数内容
     * @return bool
     */
    public function addInform($data) {
        return Db::name('inform')->insertGetId($data);
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $update_array 更新数据
     * @param array $where_array 更新条件
     * @return bool
     */
    public function editInform($update_array, $where_array) {
        return Db::name('inform')->where($where_array)->update($update_array);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @return bool
     */
    public function delInform($condition) {
        return Db::name('inform')->where($condition)->delete();
    }

    /**
     * 获得列表
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @param int $pagesize 分页
     * @param string $order 排序
     * @return array
     */
    public function getInformList($condition = '', $pagesize = '',$order = 'inform_id desc') {
        if($pagesize){
            $res = Db::name('inform')->alias('inform')->join('informsubject inform_subject', 'inform.informsubject_id = inform_subject.informsubject_id', 'LEFT')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        }else{
            return Db::name('inform')->alias('inform')->join('informsubject inform_subject', 'inform.informsubject_id = inform_subject.informsubject_id', 'LEFT')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 根据id获取举报详细信息
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return array
     */
    public function getOneInform($condition) {
        return Db::name('inform')->where($condition)->find();
    }

    /**
     *  判断该商品是否正在被举报
     * @access public
     * @author csdeshang
     *  @param int $goods_id 商品id
     *  @return bool
     */
    public function isProcessOfInform($goods_id) {

        $condition = array();
        $condition[] = array('inform_goods_id','=',$goods_id);
        $condition[] = array('inform_state','=',1);
        $inform = $this->getOneInform($condition);
        if (!empty($inform)) {
            return true;
        } else {
            return false;
        }
    }


}
