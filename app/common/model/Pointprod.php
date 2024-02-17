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
class Pointprod extends BaseModel
{
    public $page_info;

    /**
     * 获取兑换商品的展示状态
     * @access public
     * @author csdeshang
     * @return array
     */
    public function getPgoodsShowState()
    {
        $pgoodsshowstate_arr = array('unshow' => array(0, '下架'), 'show' => array(1, '上架'));
        return $pgoodsshowstate_arr;
    }

    /**
     * 获取兑换商品的开启状态
     * @access public
     * @author csdeshang
     * @return array
     */
    public function getPgoodsOpenState()
    {
        $pgoodsopenstate_arr = array('open' => array(0, '开启'), 'close' => array(1, '禁售'));
        return $pgoodsopenstate_arr;
    }

    /**
     * @access public
     * @author csdeshang
     * 获取兑换商品的推荐状态
     * @return array
     */
    public function getPgoodsRecommendState()
    {
        $pgoodsrecommendstate_arr = array('uncommend' => array(0, '未推荐'), 'commend' => array(1, '已推荐'));
        return $pgoodsrecommendstate_arr;
    }

    /**
     * 礼品保存
     * @access public
     * @author csdeshang
     * @param type $data 商品数据
     * @return boolean
     */
    public function addPointgoods($data)
    {
        if (empty($data)) {
            return false;
        }
        $insert_id = Db::name('pointsgoods')->insertGetId($data);
        if ($insert_id) {
            return $insert_id;
        }
        else {
            return false;
        }
    }

    /**
     * 礼品信息列表
     * @access public
     * @author csdeshang
     * @param array $condition 条件数组
     * @param int $pagesize 分页
     * @param string $field 查询字段
     * @param array $pagesize 分页
     */
    /**
     * 礼品信息列表
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @param string $field 字段
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $pagesize 分页
     * @return array
     */
    public function getPointProdList($where = '', $field = '*', $order = '', $limit = 0, $pagesize = '')
    {
        if (empty($order)) {
            $order = 'pgoods_sort asc';
        }
        if ($pagesize) {
            $res = Db::name('pointsgoods')->field($field)->where($where)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $res;
            $list = $res->items();
        }
        else {
            $list = Db::name('pointsgoods')->field($field)->where($where)->order($order)->limit($limit)->select()->toArray();
        }
        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {
                $v['pgoods_image_old'] = $v['pgoods_image'];

                $v['pgoods_image_small'] = pointprod_thumb($v['pgoods_image'], 'small');
                $v['pgoods_image'] = pointprod_thumb($v['pgoods_image'], 'normal');

                $v['ex_state'] = $this->getPointProdExstate($v);
                //处理限制的会员等级
                if (isset($v['pgoods_limitmgrade'])) {
                    $limitgrade = intval($v['pgoods_limitmgrade']);
                    if ($limitgrade > 0) {
                        $membergrade_arr = model('member')->getMemberGradeArr();
                        $v['pgoods_limitgradename'] = $membergrade_arr[$limitgrade]['level_name'];
                    }
                }
                //获得浏览次数
                if (isset($v['pgoods_view'])) {
                    $v['pgoods_view'] = $this->getPointProdViewNum($v['pgoods_id'], $v['pgoods_view']);
                }
                $list[$k] = $v;
            }
        }
        return $list;
    }

    /**
     * 查询出售中的兑换礼品列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @param type $limit 限制
     * @param type $pagesize  分页
     * @return type
     */
    public function getOnlinePointProdList($where = '', $field = '*', $order = '', $limit = 0, $pagesize = '')
    {
        $pgoodsshowstate_arr = $this->getPgoodsShowState();
        $pgoodsopenstate_arr = $this->getPgoodsOpenState();
        $where[]=array('pgoods_show','=',$pgoodsshowstate_arr['show'][0]);
        $where[]=array('pgoods_state','=',$pgoodsopenstate_arr['open'][0]);
        $list = $this->getPointProdList($where, $field, $order, $limit, $pagesize);
        return $list;
    }

    /**
     * 礼品信息单条
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @return type
     */
    public function getPointProdInfo($where = '', $field = '*')
    {
        $prodinfo = Db::name('pointsgoods')->where($where)->find();
        if (!empty($prodinfo)) {
            $prodinfo['pgoods_image_old'] = $prodinfo['pgoods_image'];

            $prodinfo['pgoods_image_max'] = pointprod_thumb($prodinfo['pgoods_image']);
            $prodinfo['pgoods_image_small'] = pointprod_thumb($prodinfo['pgoods_image'], 'small');
            $prodinfo['pgoods_image'] = pointprod_thumb($prodinfo['pgoods_image'], 'normal');

            $prodinfo['ex_state'] = $this->getPointProdExstate($prodinfo);
            //处理兑换时间限制
            if ($prodinfo['pgoods_islimittime'] == 1 && $prodinfo['ex_state'] == 'going') {
                $timediff = intval($prodinfo['pgoods_endtime']) - TIMESTAMP;
                $prodinfo['timediff']['diff_day'] = intval($timediff / 86400);
                $prodinfo['timediff']['diff_hour'] = intval($timediff % 86400 / 3600);
                $prodinfo['timediff']['diff_mins'] = intval($timediff % 86400 % 3600 / 60);
                $prodinfo['timediff']['diff_secs'] = intval($timediff % 86400 % 3600 % 60);
            }
            //处理限制的会员等级
            if (isset($prodinfo['pgoods_limitmgrade'])) {
                $limitgrade = intval($prodinfo['pgoods_limitmgrade']);
                if ($limitgrade > 0) {
                    $membergrade_arr = model('member')->getMemberGradeArr();
                    $prodinfo['pgoods_limitgradename'] = $membergrade_arr[$limitgrade]['level_name'];
                }
            }
            //获得浏览次数
            if (isset($prodinfo['pgoods_view'])) {
                $prodinfo['pgoods_view'] = $this->getPointProdViewNum($prodinfo['pgoods_id'], $prodinfo['pgoods_view']);
            }
        }
        return $prodinfo;
    }

    /**
     * 查询出售中的兑换礼品
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @return type
     */
    public function getOnlinePointProdInfo($where = array(), $field = '*')
    {
        $pgoodsshowstate_arr = $this->getPgoodsShowState();
        $pgoodsopenstate_arr = $this->getPgoodsOpenState();
        $where[]=array('pgoods_show','=',$pgoodsshowstate_arr['show'][0]);
        $where[]=array('pgoods_state','=',$pgoodsopenstate_arr['open'][0]);
        $prodinfo = $this->getPointProdInfo($where, $field);
        return $prodinfo;
    }

    /**
     * 获得兑换礼品的浏览次数
     * @access public
     * @author csdeshang
     * @param type $prod_id 兑换礼品ID
     * @param type $pgoods_view 浏览
     * @return int
     */
    public function getPointProdViewNum($prod_id, $pgoods_view = '')
    {
        $prod_id = intval($prod_id);
        if ($prod_id <= 0) {
            return 0;
        }
        $is_data = true;//是否从数据库读取
        if (config('ds_config.cache_open')) {//如果开启缓存，则读取缓存的浏览次数
            $prod_info = rcache($prod_id, 'pointprod');
            if ($prod_info) {
                $is_data = false;
            }
        }
        if ($is_data) {//从数据库读取
            if ($pgoods_view === '') {//如果已经获得浏览次数则直接返回,否则查询数据库中的浏览次数
                $prod_info = Db::name('pointsgoods')->field('pgoods_view')->where(array('pgoods_id' => $prod_id))->find();
                $pgoods_view = intval($prod_info['pgoods_view']);
            }
        }
        else {
            $pgoods_view = intval($prod_info['pgoods_view']);
        }
        return $pgoods_view;
    }

    /**
     * 获得礼品兑换状态
     * @access public
     * @author csdeshang
     * @param type $prodinfo 礼品信息
     * @return string
     */
    public function getPointProdExstate($prodinfo)
    {
        $datetime = TIMESTAMP;
        $ex_state = 'end';//兑换按钮的可用状态
        if ($prodinfo['pgoods_islimittime'] == 1) {
            //即将开始
            if ($prodinfo['pgoods_starttime'] > $datetime && $prodinfo['pgoods_storage'] > 0) {
                $ex_state = 'willbe';
            }
            //时间进行中
            if ($prodinfo['pgoods_starttime'] <= $datetime && $datetime < $prodinfo['pgoods_endtime'] && $prodinfo['pgoods_storage'] > 0) {
                $ex_state = 'going';
            }
        }
        else {
            if ($prodinfo['pgoods_storage'] > 0) {
                $ex_state = 'going';
            }
        }
        return $ex_state;
    }

    /**
     * 删除礼品信息
     * @access public
     * @author csdeshang
     * @param type $pg_id 礼品ID
     * @return boolean
     */
    public function delPointProdById($pg_id)
    {
        //$pg_id  为整形或者数组
        $where[]=array('pgoods_id','in', $pg_id);
        $result = Db::name('pointsgoods')->where($where)->delete();
        //删除积分礼品下的图片信息
        if ($result) {
            //删除积分礼品下的图片信息
            $upload_model = model('upload');
            $condition = array();
            $condition[] = array('upload_type','in',array('5','6'));
            $condition[]=array('item_id','in',$pg_id);
            $upload_list = $upload_model->getUploadList($condition);
            if (is_array($upload_list) && count($upload_list) > 0) {
                $upload_idarr = array();
                foreach ($upload_list as $v) {
                    @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_POINTPROD . DIRECTORY_SEPARATOR . $v['file_name']);
                    $upload_idarr[] = $v['upload_id'];
                }
                //删除图片
                $condition = array();
                $condition[]=array('upload_id','in',$upload_idarr);
                $upload_model->delUpload($condition);
            }
        }
        return $result;
    }

    /**
     * 编辑积分礼品信息
     * @access public
     * @author csdeshang
     * @param type $update_arr 更新数据
     * @param type $where 条件
     * @return boolean
     */
    public function editPointProd($update_arr, $where)
    {
        if (empty($update_arr)) {
            return true;
        }
        $result = Db::name('pointsgoods')->where($where)->update($update_arr);
        return $result;
    }

    /**
     * 获得推荐的热门兑换商品列表
     * @access public
     * @author csdeshang
     * @param type $num 
     * @return type
     */
    public function getRecommendPointProd($num)
    {
        $where = array();
        $where[] = array('pgoods_show','=',1);
        $where[] = array('pgoods_state','=',0);
        $where[] = array('pgoods_commend','=',1);
        $recommend_pointsprod = $this->getPointProdList($where, '*', 'pgoods_sort asc,pgoods_id desc', $num);
        if ($recommend_pointsprod && is_array($recommend_pointsprod)) {
            foreach ($recommend_pointsprod as $k => $v) {
                //处理限制的会员等级
                $limitgrade = intval($v['pgoods_limitmgrade']);
                if ($limitgrade > 0) {
                    $membergrade_arr = model('member')->getMemberGradeArr();
                    $v['pgoods_limitgradename'] = $membergrade_arr[$limitgrade]['level_name'];
                    $recommend_pointsprod[$k] = $v;
                }
            }
        }
        return $recommend_pointsprod;
    }

    /**
     * 更新礼品浏览次数
     * @access public
     * @author csdeshang
     * @param type $prod_id 礼品ID
     * @return int
     */
    public function editPointProdViewnum($prod_id)
    {
        if (intval($prod_id) <= 0) {
            return array('state' => false, 'msg' => '参数错误');
        }
        $viewnum = 0;//最新浏览次数
        $cache_arr = array();
        $tmptime = TIMESTAMP;
        if (!config('ds_config.cache_open')) {//直接更新数据库浏览次数
            $this->editPointProd(array('pgoods_view' => Db::raw('pgoods_view+1')), array('pgoods_id' => $prod_id));
        }
        else {//通过缓存记录浏览次数
            $prod_info = rcache($prod_id, 'pointprod');
            if (empty($prod_info)) {//如果兑换礼品的浏览次数缓存不存在，则查询兑换礼品数据库信息，建立缓存
                //查询兑换礼品信息
                $prod_info = $this->getPointProdInfo(array('pgoods_id' => $prod_id), 'pgoods_view');
                $viewnum = intval($prod_info['pgoods_view']) + 1;
                wcache($prod_id, array('pgoods_view' => $viewnum, 'view_updatetime' => $tmptime), 'pointprod');
            }
            else {
                $viewnum = intval($prod_info['pgoods_view']) + 1;
                if (($prod_info['view_updatetime'] + 3600) < $tmptime) {//如果缓存时间超出1小时，则将更新进入数据库，时间初始为当前时间
                    $this->editPointProd(array('pgoods_view' => $viewnum), array('pgoods_id' => $prod_id));
                    wcache($prod_id, array('pgoods_view' => $viewnum, 'view_updatetime' => $tmptime), 'pointprod');
                }
                else {//如果缓存时间未超出1小时，则更新浏览次数
                    wcache($prod_id, array('pgoods_view' => $viewnum), 'pointprod');
                }
            }
        }
        return array('state' => true);
    }
}