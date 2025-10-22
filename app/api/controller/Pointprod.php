<?php

namespace app\api\controller;
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
 * 积分兑换控制器
 */
class Pointprod extends MobileMall {

    public function initialize() {
        parent::initialize();
        //判断系统是否开启积分兑换功能
        if (config('ds_config.points_isuse') != 1 || config('ds_config.pointprod_isuse') != 1) {
            ds_json_encode(10001,'积分兑换功能为开启');
        }
    }

    public function index() {
        $this->plist();
    }


    /**
     * @api {POST} api/Pointprod/plist 积分商品列表
     * @apiVersion 3.0.6
     * @apiGroup Pointprod
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} level 会员级别
     * @apiParam {String} isable 仅我能兑换 0否 1是
     * @apiParam {String} points_min 积分从
     * @apiParam {String} points_max 积分到
     * @apiParam {String} orderby 排序 stimedesc积分兑换开始时间降序 stimeasc积分兑换开始时间升序 pointsdesc兑换积分降序 pointsasc兑换积分升序
     * @apiParam {String} page 页码
     * @apiParam {String} per_page 每页数量
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.goods_list  积分商品列表 （返回字段参考pointsgoods表）
     * @apiSuccess {String} result.goods_list.ex_state 兑换状态代码 end不可兑换willbe即将开始going进行中
     * @apiSuccess {Int} result.goods_list.pgoods_limitgradename 所需用户等级
     * @apiSuccess {Object} result.grade_list  返回数据，键为等级ID
     * @apiSuccess {Int} result.grade_list.exppoints  所需积分
     * @apiSuccess {Int} result.grade_list.level  等级ID
     * @apiSuccess {String} result.grade_list.level_name  等级名称
     * @apiSuccess {String} result.ww  json化的查询条件
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function plist() {

        $pointprod_model = model('pointprod');

        //展示状态
        $pgoodsshowstate_arr = $pointprod_model->getPgoodsShowState();
        //开启状态
        $pgoodsopenstate_arr = $pointprod_model->getPgoodsOpenState();

        $member_model = model('member');
        //查询会员等级
        $membergrade_arr = $member_model->getMemberGradeArr();

        //查询兑换服务列表
        $where = array();
        $where[] = array('pgoods_show','=',$pgoodsshowstate_arr['show'][0]);
        $where[] = array('pgoods_state','=',$pgoodsopenstate_arr['open'][0]);
        
        //会员级别
        $level_filter = array();
        if (input('level')) {
            $level_filter['search'] = intval(input('level'));
        }
        if (input('isable') == 1) {
            if ($memberid = $this->getMemberIdIfExists()) {
                $member_infotmp = model('member')->getMemberInfoByID($memberid);
                //当前登录会员等级信息
                $membergrade_info = $member_model->getOneMemberGrade($member_infotmp['member_exppoints'], true);
                $this->member_info = array_merge($member_infotmp, $membergrade_info);
            }
        }
        if (input('isable') == 1 && isset($this->member_info['level'])) {
            $level_filter['isable'] = intval($this->member_info['level']);
        }
        if (count($level_filter) > 0) {
            if (isset($level_filter['search']) && isset($level_filter['isable'])) {
                $where[] = array('pgoods_limitmgrade', '=', $level_filter['search']);
                $where[] = array('pgoods_limitmgrade', '<=', $level_filter['isable']);
            } elseif (isset($level_filter['search'])) {
                $where[] = array('pgoods_limitmgrade', '=', $level_filter['search']);
            } elseif (isset($level_filter['isable'])) {
                $where[] = array('pgoods_limitmgrade', '<=', $level_filter['isable']);
            }
        }


        //查询仅我能兑换和所需积分
        $points_filter = array();
        if (input('isable') == 1 && isset($this->member_info['level'])) {
            $points_filter['isable'] = $this->member_info['member_points'];
        }
        if (input('points_min') > 0) {
            $points_filter['min'] = intval(input('points_min'));
        }
        if (input('points_max') > 0) {
            $points_filter['max'] = intval(input('points_max'));
        }
        if (count($points_filter) > 0) {
            asort($points_filter);
            if (count($points_filter) > 1) {
                $points_filter = array_values($points_filter);
                $where[] = array('pgoods_points','between', array($points_filter[0], $points_filter[1]));
            } else {
                if ($points_filter['min']) {
                    $where[] = array('pgoods_points','>=', $points_filter['min']);
                } elseif ($points_filter['max']) {
                    $where[] = array('pgoods_points','<=', $points_filter['max']);
                } elseif ($points_filter['isable']) {
                    $where[] = array('pgoods_points','<=', $points_filter['isable']);
                }
            }
        }


        //排序
        switch (input('orderby')) {
            case 'stimedesc':
                $orderby = 'pgoods_starttime desc,';
                break;
            case 'stimeasc':
                $orderby = 'pgoods_starttime asc,';
                break;
            case 'pointsdesc':
                $orderby = 'pgoods_points desc,';
                break;
            case 'pointsasc':
                $orderby = 'pgoods_points asc,';
                break;
            default:
                $orderby='';
        }
        $orderby .= 'pgoods_sort asc,pgoods_id desc';
        $pageSize = 10;
        $pointprod_list = $pointprod_model->getPointProdList($where, "*", $orderby, '', $pageSize);
        $page_count = $pointprod_model->page_info;

        
        $result = array_merge(array('goods_list' => $pointprod_list, 'grade_list' => $membergrade_arr, 'ww' => json_encode($where)), mobile_page($page_count));
        ds_json_encode(10000, '',$result);
    }

    /**
     * @api {POST} api/Pointprod/pinfo 积分商品详情
     * @apiVersion 3.0.6
     * @apiGroup Pointprod
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} id 积分商品id
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.goods_commend_list  推荐积分商品列表 （返回字段参考pointsgoods表）
     * @apiSuccess {String} result.goods_commend_list.ex_state 兑换状态代码 end不可兑换willbe即将开始going进行中
     * @apiSuccess {Int} result.goods_commend_list.pgoods_limitgradename 所需用户等级
     * @apiSuccess {Object} result.goods_info  积分商品信息 （返回字段参考pointsgoods表）
     * @apiSuccess {String} result.goods_info.ex_state 兑换状态代码 end不可兑换willbe即将开始going进行中
     * @apiSuccess {Int} result.goods_info.pgoods_limitgradename 所需用户等级
     */
    public function pinfo() {
        $pid = intval(input('id'));
        if (!$pid) {
            ds_json_encode(10001,'参数错误!');
        }
        $pointprod_model = model('pointprod');
        //查询兑换礼品详细
        $prodinfo = $pointprod_model->getOnlinePointProdInfo(array(array('pgoods_id' ,'=', $pid)));
        if (empty($prodinfo)) {
            ds_json_encode(10001,'商品参数错误!');
        }

        //更新礼品浏览次数
        $tm_tm_visite_pgoods = cookie('tm_visite_pgoods');
        $tm_tm_visite_pgoods = $tm_tm_visite_pgoods ? explode(',', $tm_tm_visite_pgoods) : array();
        if (!in_array($pid, $tm_tm_visite_pgoods)) {//如果已经浏览过该服务则不重复累计浏览次数 
            $result = $pointprod_model->editPointProdViewnum($pid);
            if ($result['state'] == true) {//累加成功则cookie中增加该服务ID
                $tm_tm_visite_pgoods[] = $pid;
                cookie('tm_visite_pgoods', implode(',', $tm_tm_visite_pgoods));
            }
        }

        

        //热门积分兑换服务
        $recommend_pointsprod = $pointprod_model->getRecommendPointProd(5);
        $prodinfo['pgoods_body']=htmlspecialchars_decode($prodinfo['pgoods_body']);
        ds_json_encode(10000, '',array('goods_commend_list' => $recommend_pointsprod, 'goods_info' => $prodinfo));
    }
    
    /**
     * @api {POST} api/Pointprod/get_order_list 查询兑换信息
     * @apiVersion 3.0.6
     * @apiGroup Pointprod
     * 
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {String} pgoods_id 积分商品id
     * @apiParam {String} page 页码
     * @apiParam {String} per_page 每页数量
     * 
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.order_list  订单列表 （返回字段参考pointsordergoods表）
     * @apiSuccess {String} result.order_list.member_avatar 用户头像
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function get_order_list(){
        $pgoods_id=intval(input('param.pgoods_id'));
        $pointorder_model = model('pointorder');
        $pointorderstate_arr = $pointorder_model->getPointorderStateBySign();
        $where = array();
        $where[] = array('point_orderstate','<>', $pointorderstate_arr['canceled'][0]);
        $where[] = array('pointog_goodsid','=',$pgoods_id);
        $orderprod_list = $pointorder_model->getPointorderAndGoodsList($where, '*',  'pointsordergoods.pointog_recid desc',$this->pagesize);
        if ($orderprod_list) {
            $buyerid_arr = array();
            foreach ($orderprod_list as $k => $v) {
                $buyerid_arr[] = $v['point_buyerid'];
            }
            $memberlist_tmp = model('member')->getMemberList(array(array('member_id','in', $buyerid_arr)), 'member_id,member_avatar');
            $memberlist = array();
            if ($memberlist_tmp) {
                foreach ($memberlist_tmp as $v) {
                    $memberlist[$v['member_id']] = $v;
                }
            }
            foreach ($orderprod_list as $k => $v) {
                $v['member_avatar'] = ($t = $memberlist[$v['point_buyerid']]['member_avatar']) ? ds_get_pic( ATTACH_AVATAR , $t) : ds_get_pic( ATTACH_COMMON , config('ds_config.default_user_portrait'));
                $orderprod_list[$k] = $v;
            }
        }
        $result = array_merge(array('order_list' => $orderprod_list), mobile_page( is_object($pointorder_model->page_info)?$pointorder_model->page_info:0));
        ds_json_encode(10000, '',$result);
    }

}

?>
