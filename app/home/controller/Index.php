<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Db;
use think\facade\Lang;
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
 * 控制器
 */
class Index extends BaseMall {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/index.lang.php');
    }

    public function index() {
        View::assign('index_sign', 'index');
		$editable_page_model = model('editable_page');
		$editable_page_info = $editable_page_model->getOneEditablePage(array('editable_page_path' => 'index/index','editable_page_client'=>'pc'));
		if ($editable_page_info) {
		    $editable_page_info['editable_page_theme_config'] = json_decode($editable_page_info['editable_page_theme_config'], true);
		    View::assign('editable_page', $editable_page_info);
		    $editable_page_config_model = model('editable_page_config');
		    $editable_page_config_list=$editable_page_config_model->getEditablePageConfigList(array(array('editable_page_id', '=', $editable_page_info['editable_page_id'])));
		    $config_list=array();
		    foreach($editable_page_config_list as $key => $val){
		        $config_info=json_decode($val['editable_page_config_content'], true);
		        $model_id=$val['editable_page_model_id'];
		        $var_html=array();
		    if(!empty($config_info)){
		        require_once PLUGINS_PATH.'/editable_page_model/'.$model_id.'/config.php';
		        $model_name='Model'.$model_id;
		        $model=new $model_name();
		        $res=$model->filterData($config_info);
		        if($res['code']){
		            $res=$model->formatData(json_encode($res['data']));
		            if($res['code']){
		                $var_html['config_info']=$res['data'];
		            }
		        }
		        
		    }
		    $html=View::fetch('../../../plugins/editable_page_model/'.$model_id.'/index',$var_html);
		        $config_list[]=array(
		            'val'=>$val,
		            'html'=>$html,
		        );
		    }
		    View::assign('config_list', $config_list);
		    View::assign('editable_page', $editable_page_info);
		}else{

        $this->getIndexData();
        //楼层广告
        $result=false;
        $condition=array();
        $condition[]=['ap_id','=',1];
        $condition[]=['adv_enabled','=',1];
        $condition[]=['adv_startdate','<',strtotime(date('Y-m-d H:00:00'))];
        $condition[]=['adv_enddate','>',strtotime(date('Y-m-d H:00:00'))];
        $adv_list=model('adv')->getAdvList($condition,'',10,'adv_sort asc,adv_id asc');
        if(!empty($adv_list)){
            $result=$adv_list;
        }
        View::assign('adv_index_1', $result);
        
        $result=false;
        $condition=array();
        $condition[]=['ap_id','=',2];
        $condition[]=['adv_enabled','=',1];
        $condition[]=['adv_startdate','<',strtotime(date('Y-m-d H:00:00'))];
        $condition[]=['adv_enddate','>',strtotime(date('Y-m-d H:00:00'))];
        $adv_list=model('adv')->getAdvList($condition,'',10,'adv_sort asc,adv_id asc');
        if(!empty($adv_list)){
            $result=$adv_list;
        }
        View::assign('adv_index_2', $result);
        //楼层数据
        $floor_block=array();
	    $goodsclass_list = Db::name('goodsclass')->where('gc_parent_id', 0)->where('gc_show', 1)->order('gc_sort asc')->select()->toArray();
        foreach ($goodsclass_list as $key => $goodsclass) {
            $floor_list = $this->getFloorList($goodsclass['gc_id']);
            $floor_block[$key] = $floor_list;
            $floor_block[$key]['gc_name'] = $goodsclass['gc_name'];
            if(!$floor_block[$key]['goods_list'][0]['gc_list']){
                unset($floor_block[$key]);
                continue;
            }
            $floor_block[$key]['adv_left']=false;
            $floor_block[$key]['adv_bottom']=false;
            if($key<5){
                $result=false;
                $condition=array();
                $condition_1=array();
                $condition_2=array();
                $condition[]=['adv_enabled','=',1];
                $condition[]=['adv_startdate','<',strtotime(date('Y-m-d H:00:00'))];
                $condition[]=['adv_enddate','>',strtotime(date('Y-m-d H:00:00'))];
                switch($key){
                    case 0:
                        $condition_1[]=['ap_id','=',8];
                        $condition_2[]=['ap_id','=',3];
                        break;
                    case 1:
                        $condition_1[]=['ap_id','=',9];
                        $condition_2[]=['ap_id','=',4];
                        break;
                    case 2:
                        $condition_1[]=['ap_id','=',10];
                        $condition_2[]=['ap_id','=',5];
                        break;
                    case 3:
                        $condition_1[]=['ap_id','=',11];
                        $condition_2[]=['ap_id','=',6];
                        break;
                    case 4:
                        $condition_1[]=['ap_id','=',12];
                        $condition_2[]=['ap_id','=',7];
                        break;
                }
                $adv_list=model('adv')->getAdvList(array_merge($condition,$condition_1),'',1,'adv_sort asc,adv_id asc');
                if(!empty($adv_list)){
                    $result=$adv_list[0];
                }
                $floor_block[$key]['adv_left']=$result;
                $adv_list=model('adv')->getAdvList(array_merge($condition,$condition_2),'',1,'adv_sort asc,adv_id asc');
                if(!empty($adv_list)){
                    $result=$adv_list[0];
                }
                $floor_block[$key]['adv_bottom']=$result;
            }
        }

        //楼层数据
        View::assign('floor_block', $floor_block);

        //显示订单信息
        if (session('is_login')) {
            //交易提醒 - 显示数量
            $order_model = model('order');
            $member_order_info['order_nopay_count'] = $order_model->getOrderCountByID(session('member_id'), 'NewCount');
            $member_order_info['order_noreceipt_count'] = $order_model->getOrderCountByID(session('member_id'), 'SendCount');
            $member_order_info['order_noeval_count'] = $order_model->getOrderCountByID(session('member_id'), 'EvalCount');
            View::assign('member_order_info', $member_order_info);
        }
		}
        //SEO 设置
        $seo = model('seo')->type('index')->show();
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . 'index');
    }
    
    private function getIndexData()
    {
        $index_data = rcache("index_data");
        if (empty($index_data)) {
            $goods_model=model('goods');
            $index_data = array();
            $index_data['recommend_list'] = $goods_model->getGoodsUnionList(array(array('goodscommon.goods_commend','=',1)), 'goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_image,goods.goods_promotion_price,goodscommon.goods_price','goodscommon.goods_sort asc','goodscommon.goods_commonid', 0,5);
            foreach($index_data['recommend_list'] as $key => $val){
                if(!$val['goods_storage']){
                    $goods_info=$goods_model->getGoodsStorageByCommonId($val['goods_commonid']);
                    if($goods_info){
                        $index_data['recommend_list'][$key]['goods_id']=$goods_info['goods_id'];
                        $index_data['recommend_list'][$key]['goods_storage']=$goods_info['goods_storage'];
                        $index_data['recommend_list'][$key]['goods_promotion_price']=$goods_info['goods_promotion_price'];
                    }
                }
            }
            //秒杀
            $index_data['promotion_list'] = model('pxianshigoods')->getXianshigoodsCommendList(5);
            $index_data['new_list'] = $goods_model->getGoodsUnionList(array(), 'goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_image,goods.goods_promotion_price,goodscommon.goods_price','goodscommon.goods_addtime desc','goodscommon.goods_commonid', 0,5);
            foreach($index_data['new_list'] as $key => $val){
                if(!$val['goods_storage']){
                    $goods_info=$goods_model->getGoodsStorageByCommonId($val['goods_commonid']);
                    if($goods_info){
                        $index_data['new_list'][$key]['goods_id']=$goods_info['goods_id'];
                        $index_data['new_list'][$key]['goods_storage']=$goods_info['goods_storage'];
                        $index_data['new_list'][$key]['goods_promotion_price']=$goods_info['goods_promotion_price'];
                    }
                }
            }
            $index_data['groupbuy_list'] = model('groupbuy')->getGroupbuyCommendedList(5);
            //友情链接
            $index_data['link_list'] = model('link')->getLinkList();
            //获取第一文章分类的前三篇文章
            $index_data['index_articles'] = Db::name('article')->where('ac.ac_code', 'notice')->where('a.article_show', 1)->alias('a')->field('a.article_id,a.article_url,a.article_title')->order('a.article_sort asc,a.article_time desc')->limit(3)->join('articleclass ac', 'a.ac_id=ac.ac_id')->select()->toArray();
            wcache('index_data',$index_data);
        }
        View::assign('recommend_list', $index_data['recommend_list']);
        View::assign('promotion_list', $index_data['promotion_list']);
        View::assign('new_list', $index_data['new_list']);
        View::assign('groupbuy_list', $index_data['groupbuy_list']);
        View::assign('link_list', $index_data['link_list']);
        View::assign('index_articles', $index_data['index_articles']);
    }
    

    private function getFloorList($cate_id) {
        $prefix = 'home-index-floor-';
        $result = rcache($cate_id,$prefix);
        if (empty($result)) {
            $goods_model=model('goods');
            //获取此楼层下的所有分类
            $goods_class_list = Db::name('goodsclass')->where('gc_parent_id=' . $cate_id)->select()->toArray();
            //获取每个分类下的商品
            $goods_list = array();
            $goods_list[0]['gc_name'] = lang('hot_recommended');
            $goods_list[0]['gc_id'] = $cate_id;
            $condition=$goods_model->_getRecursiveClass(array(),$cate_id,'goodscommon');
            $goods_list[0]['gc_list'] = $goods_model->getGoodsUnionList($condition,'goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_marketprice,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_image,goods.goods_promotion_price,goodscommon.goods_price','goodscommon.goods_commend desc,goodscommon.goods_sort asc','goodscommon.goods_commonid', 0,10);
            foreach($goods_list[0]['gc_list'] as $key => $val){
                if(!$val['goods_storage']){
                    $goods_info=$goods_model->getGoodsStorageByCommonId($val['goods_commonid']);
                    if($goods_info){
                        $goods_list[0]['gc_list'][$key]['goods_id']=$goods_info['goods_id'];
                        $goods_list[0]['gc_list'][$key]['goods_promotion_price']=$goods_info['goods_promotion_price'];
                    }
                }
            }
            $hot_goods_class_list = Db::name('goodsclass')->where('gc_parent_id=' . $cate_id)->order('gc_sort asc')->limit(5)->select()->toArray();
            foreach ($hot_goods_class_list as $key => $hot_goods_class) {
                $data = array();
                $data['gc_name'] = $hot_goods_class['gc_name'];
                $data['gc_id'] = $hot_goods_class['gc_id'];
                $condition=$goods_model->_getRecursiveClass(array(),$data['gc_id'],'goodscommon');
                $data['gc_list'] = $goods_model->getGoodsUnionList($condition,'goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_marketprice,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_image,goods.goods_promotion_price,goodscommon.goods_price','goodscommon.goods_commend desc,goodscommon.goods_sort asc','goodscommon.goods_commonid', 0,10);
                foreach($data['gc_list'] as $k => $v){
                    if(!$v['goods_storage']){
                        $goods_info=$goods_model->getGoodsStorageByCommonId($v['goods_commonid']);
                        if($goods_info){
                            $data['gc_list'][$k]['goods_id']=$goods_info['goods_id'];
                            $data['gc_list'][$k]['goods_promotion_price']=$goods_info['goods_promotion_price'];
                        }
                    }
                }
                $goods_list[] = $data;
            }
            $result['goods_list'] = $goods_list;
            $result['goods_class_list'] = $goods_class_list;
            wcache($cate_id, $result,$prefix, 3600);
        }
        return $result;
    }

    //json输出商品分类
    public function josn_class() {
        /**
         * 实例化商品分类模型
         */
        $goodsclass_model = model('goodsclass');
        $goods_class = $goodsclass_model->getGoodsclassListByParentId(intval(input('get.gc_id')));
        $array = array();
        if (is_array($goods_class) and count($goods_class) > 0) {
            foreach ($goods_class as $val) {
                $array[$val['gc_id']] = array(
                    'gc_id' => $val['gc_id'],
                    'gc_name' => htmlspecialchars($val['gc_name']),
                    'gc_parent_id' => $val['gc_parent_id'],
                    'gc_sort' => $val['gc_sort']
                );
            }
        }

        echo input('param.callback') . '(' . json_encode($array) . ')';
    }

    /**
     * json输出地址数组 public/static/plugins/area_datas.js
     */
    public function json_area() {
        echo input('param.callback') . '(' . json_encode(model('area')->getAreaArrayForJson()) . ')';
    }

    /**
     * json输出地址数组 
     */
    public function json_area_show() {
        $area_info['text'] = model('area')->getTopAreaName(intval($_GET['area_id']));
        echo input('param.callback') . '(' . json_encode($area_info) . ')';
    }

    //判断是否登录
    public function login() {
        echo (session('is_login') == '1') ? '1' : '0';
    }

    /**
     * 查询每月的周数组
     */
    public function getweekofmonth() {
        include_once root_path(). 'extend/mall/datehelper.php';
        $year = input('get.y');
        $month = input('get.m');
        $week_arr = getMonthWeekArr($year, $month);
        echo json_encode($week_arr);
        die;
    }

    /**
     * 头部最近浏览的商品
     */
    public function viewed_info() {
        $info = array();
        if (session('is_login') == '1') {
            $member_id = session('member_id');
            $info['m_id'] = $member_id;
            if (config('ds_config.voucher_allow') == 1) {
                $time_to = TIMESTAMP; //当前日期
                $condition = array();
                $condition[] = array('voucher_owner_id','=',$member_id);
                $condition[] = array('voucher_state','=',1);
                $condition[] = array('voucher_startdate','<=',$time_to);
                $condition[] = array('voucher_enddate','>=',$time_to);
                $info['voucher'] = Db::name('voucher')->where($condition)->count();
            }
            $time_to = strtotime(date('Y-m-d')); //当前日期
            $time_from = date('Y-m-d', ($time_to - 60 * 60 * 24 * 7)); //7天前
            $consult_mod=model('consult');
            $condition = array();
            $condition[] = array('member_id','=',$member_id);
            $condition[] = array('consult_replytime','>',strtotime($time_from));
            $condition[] = array('consult_replytime','<',$time_to + 60 * 60 * 24);
            $info['consult'] = $consult_mod->getConsultCount($condition);
        }
        $goods_list = model('goodsbrowse')->getViewedGoodsList(session('member_id'), 5);
        if (is_array($goods_list) && !empty($goods_list)) {
            $viewed_goods = array();
            foreach ($goods_list as $key => $val) {
                $goods_id = $val['goods_id'];
                $val['url'] = (string)url('Goods/index', ['goods_id' => $goods_id]);
                $val['goods_image'] = goods_thumb($val, 60);
                $viewed_goods[$goods_id] = $val;
            }
            $info['viewed_goods'] = $viewed_goods;
        }
        echo json_encode($info);
    }

}
