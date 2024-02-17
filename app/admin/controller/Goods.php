<?php

namespace app\admin\controller;
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
class Goods extends AdminControl {

    public function initialize() {
        parent::initialize();
        error_reporting(0);
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/goods.lang.php');
    }

    /**
     * 商品管理
     */
    public function index() {
        $goods_model = model('goods');
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval(input('param.choose_gcid'))) > 0 ? $t : 0;
        $gccache_arr = model('goodsclass')->getGoodsclassCache($choose_gcid, 3);
        View::assign('gc_json', json_encode($gccache_arr['showclass']));
        View::assign('gc_choose_json', json_encode($gccache_arr['choose_gcid']));

        /**
         * 查询条件
         */
        $where = array();
        
        $search_goods_name = trim(input('param.search_goods_name'));
        if ($search_goods_name != '') {
            $where[] = array('goodscommon.goods_name','like', '%' . $search_goods_name . '%');
        }
        $search_commonid = intval(input('param.search_commonid'));
        if ($search_commonid > 0) {
            $where[] = array('goodscommon.goods_commonid','=',$search_commonid);
        }
        $b_id = intval(input('param.b_id'));
        if ($b_id > 0) {
            $where[] = array('goodscommon.brand_id','=',$b_id);
        }
        if ($choose_gcid > 0) {
            $where[] = array('goodscommon.gc_id_' . ($gccache_arr['showclass'][$choose_gcid]['depth']),'=',$choose_gcid);
        }
        $goods_state = input('param.goods_state');
        if (in_array($goods_state, array('10', '1'))) {
            $where[] = array('goodscommon.goods_state','=',$goods_state);
        }

        $stock = trim(input('get.stock'));
        switch ($stock) {
            case 1:
                $where[] = array('goods.goods_storage', 'EXP', Db::raw('<=goods.goods_storage_alarm'));
                $where[] = array('goods.goods_storage_alarm', '>', 0);
                break;
        }
		$type = input('param.type');
		if ($type == 'lockup'){
			$goods_list = $goods_model->getGoodsCommonLockUpList($where);
		}else{
		    $goods_list = $goods_model->getGoodsUnionList($where, 'goods.goods_id,goods.goods_storage_alarm,goods.goods_state,goods.goods_storage,goodscommon.goods_commonid,goodscommon.mobile_body,goodscommon.goods_name,goodscommon.goods_addtime,goodscommon.goods_lock,goodscommon.goods_sort,goodscommon.goods_commend,goodscommon.goods_serial,goodscommon.is_virtual,goodscommon.is_goodsfcode,goodscommon.is_appoint,goodscommon.goods_advword,goodscommon.goods_image,goods.goods_promotion_price,goodscommon.goods_price','goodscommon.goods_commonid desc','goodscommon.goods_commonid', 10);
		}
		foreach($goods_list as $key => $val){
            if($val['goods_storage_alarm'] != 0 && $val['goods_storage'] <= $val['goods_storage_alarm']){
                $goods_list[$key]['alarm']=true;
            }
            $goods_list[$key]['sum']=Db::name('goods')->where(array(array('goods_commonid','=',$val['goods_commonid'])))->sum('goods_storage');
        }

        View::assign('goods_list', $goods_list);
        View::assign('show_page', $goods_model->page_info->render());



        // 品牌
        $brand_list = model('brand')->getBrandPassedList(array());

        View::assign('search', $where);
        View::assign('brand_list', $brand_list);

        View::assign('state', array('1' => '出售中', '10' => '仓库中'));
        if(!in_array($type, array('lockup'))){
            $type = 'allgoods';
        }

        View::assign('type', $type);
        $this->setAdminCurItem($type);
        return View::fetch();
    }

    /**
     * 计算商品库存
     */
    public function goods_storage($goods_list) {
        $goods_model = model('goods');
        // 计算库存
        $storage_array = array();
        if (!empty($goods_list)) {
            foreach ($goods_list as $value) {
                $storage_array[$value['goods_commonid']]['goods_storage'] = $goods_model->getGoodsSum(array('goods_commonid'=>$value['goods_commonid']),'goods_storage');
                $storage_array[$value['goods_commonid']][] = $goods_model->getGoodsInfo(array('goods_commonid'=>$value['goods_commonid']),'goods_id');
            }
            return $storage_array;
        } else {
            return false;
        }
    }

    /**
     * 存入仓库  - 售卖商品
     */
    public function goods_lockup() {
        if (request()->isPost()) {
            $commonids = input('param.commonids');
            $type = input('param.type');
            $commonid_array = ds_delete_param($commonids);
            if ($commonid_array == FALSE) {
                $this->error(lang('ds_common_op_fail'));
            }

            $update = array();
            $update['goods_stateremark'] = trim(input('post.close_reason'));

            $where = array();
            $where[] = array('goods_commonid','in', $commonid_array);

            model('goods')->editProducesLockUp($update, $where,$type);
            dsLayerOpenSuccess(lang('ds_common_op_succ'));
        } else {
            View::assign('commonids', input('param.commonid'));
            View::assign('type', input('param.type'));
            echo View::fetch('close_remark');
        }
    }

    /**
     * 删除商品
     */
    public function goods_del() {
        $common_id = intval(input('common_id'));
        if ($common_id <= 0) {
            $this->error(lang('ds_common_op_fail'));
        }
        model('goods')->delGoodsAll(array('goods_commonid' => $common_id));
        $this->success(lang('ds_common_op_succ'));
    }

    /**
     * ajax获取同一个commonid下面的商品信息
     * */
    public function get_goods_list_ajax() {
        $common_id = input('param.commonid');
        if (empty($common_id)) {
            $this->error(lang('param_error'));
        }
        $map['goods_commonid'] = $common_id;
        $goods_model = model('goods');
        $common_info = $goods_model->getGoodsCommonInfo($map,'spec_name');
        $goods_list = $goods_model->getGoodsList($map);
        $spec_name = array_values((array) unserialize($common_info['spec_name']));
        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array) unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . ':' . '<em title="' . $v . '">' . $v . '</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = goods_cthumb($val['goods_image']);
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['url'] = url('home/Goods/index', array('goods_id' => $val['goods_id']));
        }
        return json_encode($goods_list);
    }
    /**
     * ajax操作
     */
    public function ajax() {
        $goods_model = model('goods');
        switch (input('param.branch')) {
            case 'goods_commend':
            case 'goods_sort':
                if (empty($result)) {
                    $goods_model->editGoodsCommonById(array(trim(input('param.branch')) => trim(input('param.value'))),array(intval(input('param.id'))));
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 编辑商品页面
     */
    public function edit_goods() {
        $common_id = intval(input('param.commonid'));
        if ($common_id <= 0) {
            $this->error(lang('param_error'));
        }
        $goods_model = model('goods');
        $goodscommon_info = $goods_model->getGoodsCommonInfoByID($common_id);
        if (empty($goodscommon_info) || $goodscommon_info['goods_lock'] == 1) {
            $this->error('您的商品不存在，或商品已被锁定，请删除促销解除锁定');
        }
        $where = array('goods_commonid' => $common_id);
        $goodscommon_info['g_storage'] = $goods_model->getGoodsSum($where, 'goods_storage');
        $goodscommon_info['spec_name'] = unserialize($goodscommon_info['spec_name']);
        if ($goodscommon_info['goods_marketprice'] == 0.00){
            $goodscommon_info['goods_marketprice'] = null;
        }
        if ($goodscommon_info['mobile_body'] != '') {
            $goodscommon_info['mb_body'] = unserialize($goodscommon_info['mobile_body']);
            if (is_array($goodscommon_info['mb_body'])) {
                $mobile_body = '[';
                foreach ($goodscommon_info['mb_body'] as $val) {
                    $mobile_body .= '{"type":"' . $val['type'] . '","value":"' . $val['value'] . '"},';
                }
                $mobile_body = rtrim($mobile_body, ',') . ']';
            }
            $goodscommon_info['mobile_body'] = $mobile_body;
        }
        View::assign('goods', $goodscommon_info);

        $class_id = intval(input('param.class_id'));
        if ($class_id > 0) {
            $goodscommon_info['gc_id'] = $class_id;
        }
        $goods_class = model('goodsclass')->getGoodsclassLineForTag($goodscommon_info['gc_id']);
        View::assign('goods_class', $goods_class);

        $type_model = model('type');
        // 获取类型相关数据
        $typeinfo = $type_model->getAttribute($goods_class['type_id'], $goodscommon_info['gc_id']);
        list($spec_json, $spec_list, $attr_list, $brand_list) = $typeinfo;
        View::assign('spec_json', $spec_json);
        View::assign('sign_i', count($spec_list));
        View::assign('spec_list', $spec_list);
        View::assign('attr_list', $attr_list);
        View::assign('brand_list', $brand_list);

        // 取得商品规格的输入值
        $goods_array = $goods_model->getGoodsList($where, 'goods_id,goods_marketprice,goods_price,goods_storage,goods_serial,goods_storage_alarm,goods_spec');

        $sp_value = array();
        if (is_array($goods_array) && !empty($goods_array)) {

            // 取得已选择了哪些商品的属性
            $attr_checked_l = $type_model->typeRelatedList('goodsattrindex', array(
                'goods_id' => intval($goods_array[0]['goods_id'])
                    ), 'attrvalue_id');
            $attr_checked = array();
            if (is_array($attr_checked_l) && !empty($attr_checked_l)) {
                foreach ($attr_checked_l as $val) {
                    $attr_checked [] = $val ['attrvalue_id'];
                }
            }
            View::assign('attr_checked', $attr_checked);

            $spec_checked = array();
            foreach ($goods_array as $k => $v) {
                $a = unserialize($v['goods_spec']);
                if (!empty($a)) {
                    foreach ($a as $key => $val) {
                        $spec_checked[$key]['id'] = $key;
                        $spec_checked[$key]['name'] = addslashes($val);
                    }
                    $matchs = array_keys($a);
                    //sort($matchs);
                    $id = str_replace(',', '', implode(',', $matchs));
                    $sp_value ['i_' . $id . '|marketprice'] = $v['goods_marketprice'];
                    $sp_value ['i_' . $id . '|price'] = $v['goods_price'];
                    $sp_value ['i_' . $id . '|id'] = $v['goods_id'];
                    $sp_value ['i_' . $id . '|stock'] = $v['goods_storage'];
                    $sp_value ['i_' . $id . '|alarm'] = $v['goods_storage_alarm'];
                    $sp_value ['i_' . $id . '|sku'] = $v['goods_serial'];
                }
            }
            View::assign('spec_checked', $spec_checked);
        }
        View::assign('sp_value', $sp_value);


        // 是否能使用编辑器
        $editor_multimedia = true;
        View::assign('editor_multimedia', $editor_multimedia);

        // 小时分钟显示
        $hour_array = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
        View::assign('hour_array', $hour_array);
        $minute_array = array('05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
        View::assign('minute_array', $minute_array);


        // F码
        if ($goodscommon_info['is_goodsfcode'] == 1) {
            $fcode_array = model('goodsfcode')->getGoodsfcodeList(array('goods_commonid' => $goodscommon_info['goods_commonid']));
            View::assign('fcode_array', $fcode_array);
        }

        View::assign('edit_goods_sign', true);
        $this->setAdminCurItem('edit_goods');
        return View::fetch('goods_add_step2');
    }

    /**
     * 编辑商品保存
     */
    public function edit_save_goods() {

        $common_id = intval(input('param.commonid'));
        if (!request()->isPost() || $common_id <= 0) {
            ds_json_encode(10001,lang('goods_index_goods_edit_fail'));
        }

        $gc_id = intval(input('post.cate_id'));

        // 验证商品分类是否存在且商品分类是否为最后一级
        $data = model('goodsclass')->getGoodsclassForCacheModel();
        if (!isset($data[$gc_id]) || isset($data[$gc_id]['child']) || isset($data[$gc_id]['childchild'])) {
            ds_json_encode(10001,lang('goods_index_again_choose_category1'));
        }
        
        // 分类信息
        $goods_class = model('goodsclass')->getGoodsclassLineForTag(intval(input('post.cate_id')));
        $goods_model = model('goods');

        $update_common = array();
        $update_common['goods_name'] = input('post.g_name');
        $update_common['goods_advword'] = input('post.g_jingle');
        $update_common['gc_id'] = $gc_id;
        $update_common['gc_id_1'] = isset($goods_class['gc_id_1'])?intval($goods_class['gc_id_1']):0;
        $update_common['gc_id_2'] = isset($goods_class['gc_id_2'])?intval($goods_class['gc_id_2']):0;
        $update_common['gc_id_3'] = isset($goods_class['gc_id_3'])?intval($goods_class['gc_id_3']):0;
        $update_common['gc_name'] = input('post.cate_name');
        $update_common['brand_id'] = input('post.b_id');
        $update_common['brand_name'] = input('post.b_name');
        $update_common['type_id'] = intval(input('post.type_id'));
        $update_common['goods_image'] = input('post.image_path');
        $update_common['goods_price'] = floatval(input('post.g_price'));
        $update_common['goods_marketprice'] = floatval(input('post.g_marketprice'));
        $update_common['goods_costprice'] = floatval(input('post.g_costprice'));
        $update_common['goods_discount'] = floatval(input('post.g_discount'));
        $update_common['goods_serial'] = input('post.g_serial');
        $update_common['goods_storage_alarm'] = intval(input('post.g_alarm'));
        $update_common['goods_attr'] = !empty(input('post.attr/a'))?serialize(input('post.attr/a')):'';
        
            $goods_body=preg_replace_callback("/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i", function ($matches) {
                return str_replace($matches[2],strip_tags($matches[2]),$matches[0]);
            }, htmlspecialchars_decode(input('post.goods_body')));
            $update_common['goods_body'] = $goods_body;
        // 序列化保存手机端商品描述数据
        $mobile_body = input('post.m_body');
        if ($mobile_body != '') {
            $mobile_body = str_replace('&quot;', '"', $mobile_body);
            $mobile_body = json_decode($mobile_body, true);
            if (!empty($mobile_body)) {
                $mobile_body = serialize($mobile_body);
            } else {
                $mobile_body = '';
            }
        }
        $update_common['mobile_body'] = $mobile_body;
        $update_common['goods_commend'] = intval(input('post.g_commend'));
        $update_common['goods_state'] = intval(input('post.g_state')); 
        $update_common['goods_shelftime'] = strtotime(input('post.starttime')) + intval(input('post.starttime_H')) * 3600 + intval(input('post.starttime_i')) * 60;
        $update_common['spec_name'] = !empty(input('post.spec/a')) ? serialize(input('post.sp_name/a')) : serialize(null);
        $update_common['spec_value'] = !empty(input('post.spec/a')) ? serialize(input('post.sp_val/a')) : serialize(null);
        $update_common['goods_vat'] = intval(input('post.g_vat'));
        $update_common['areaid_1'] = intval(input('post.province_id'));
        $update_common['areaid_2'] = intval(input('post.city_id'));
        $update_common['transport_id'] = (input('post.freight') == '0') ? '0' : intval(input('post.transport_id')); // 售卖区域
        $update_common['transport_title'] = input('post.transport_title');
        $update_common['goods_freight'] = floatval(input('post.g_freight'));

        //验证数据  BEGIN
        $goods_validate = ds_validate('goods');
        if (!$goods_validate->scene('edit_save_goods')->check($update_common)) {
            ds_json_encode(10001,$goods_validate->getError());
        }
        //验证数据  END

        //查询店铺商品分类
        $goods_stcids_arr = array();
        if (empty($goods_stcids_arr)) {
            $update_common['goods_stcids'] = '';
        } else {
            $update_common['goods_stcids'] = ',' . implode(',', $goods_stcids_arr) . ',';
        }
        $update_common['plateid_top'] = intval(input('post.plate_top')) > 0 ? intval(input('post.plate_top')) : '';
        $update_common['plateid_bottom'] = intval(input('post.plate_bottom')) > 0 ? intval(input('post.plate_bottom')) : '';
        $update_common['is_virtual'] = intval(input('post.is_gv'));
        $update_common['virtual_indate'] = input('post.g_vindate') != '' ? (strtotime(input('post.g_vindate')) + 24 * 60 * 60 - 1) : 0;  // 当天的最后一秒结束
        $update_common['virtual_limit'] = intval(input('post.g_vlimit')) > 10 || intval(input('post.g_vlimit')) < 0 ? 10 : intval(input('post.g_vlimit'));
        $update_common['virtual_invalid_refund'] = intval(input('post.g_vinvalidrefund'));
        $update_common['is_goodsfcode'] = intval(input('post.is_fc'));
        $update_common['is_appoint'] = intval(input('post.is_appoint'));     // 只有库存为零的商品可以预约
        $update_common['appoint_satedate'] = $update_common['is_appoint'] == 1 ? strtotime(input('post.g_saledate')) : '';   // 预约商品的销售时间
        $update_common['is_presell'] = $update_common['goods_state'] == 1 ? intval(input('post.is_presell')) : 0;     // 只有出售中的商品可以预售
        $update_common['presell_deliverdate'] = $update_common['is_presell'] == 1 ? strtotime(input('post.g_deliverdate')) : ''; // 预售商品的发货时间

        // 开始事务
        Db::startTrans();
        try{
        $goods_model->lock=true;  
        $goodsgift_model = model('goodsgift');
        // 清除原有规格数据
        $type_model = model('type');
        $type_model->delGoodsAttr(array('goods_commonid' => $common_id));

        // 更新商品规格
        $goodsid_array = array();
        $colorid_array = array();
        $spec_array = input('post.spec/a');#获取数组
        if (is_array($spec_array)&&!empty($spec_array)) {
            foreach ($spec_array as $value) {
                $goods_info = $goods_model->getGoodsInfo(array('goods_id' => $value['goods_id'], 'goods_commonid' => $common_id), 'goods_id');
                if (!empty($goods_info)) {
                    $goods_id = $goods_info['goods_id'];
                    $update = array();
                    $update['goods_commonid'] = $common_id;
                    $update['goods_name'] = $update_common['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                    $update['goods_advword'] = $update_common['goods_advword'];
                    $update['gc_id'] = $update_common['gc_id'];
                    $update['gc_id_1'] = $update_common['gc_id_1'];
                    $update['gc_id_2'] = $update_common['gc_id_2'];
                    $update['gc_id_3'] = $update_common['gc_id_3'];
                    $update['brand_id'] = $update_common['brand_id'];
                    $update['goods_price'] = $value['price'];
                    $update['goods_marketprice'] = $value['marketprice'] == 0 ? $update_common['goods_marketprice'] : $value['marketprice'];
                    $update['goods_serial'] = $value['sku'];
                    $update['goods_storage_alarm'] = intval($value['alarm']);
                    $update['goods_spec'] = serialize($value['sp_value']);
                    $update['goods_storage'] = $value['stock'];
                    $update['goods_state'] = $update_common['goods_state'];
                    $update['goods_edittime'] = TIMESTAMP;
                    $update['areaid_1'] = $update_common['areaid_1'];
                    $update['areaid_2'] = $update_common['areaid_2'];
                    $update['color_id'] = isset($value['color'])?intval($value['color']):'';
                    $update['transport_id'] = $update_common['transport_id'];
                    $update['goods_freight'] = $update_common['goods_freight'];
                    $update['goods_vat'] = $update_common['goods_vat'];
                    $update['goods_commend'] = $update_common['goods_commend'];
                    $update['goods_stcids'] = $update_common['goods_stcids'];
                    $update['is_virtual'] = $update_common['is_virtual'];
                    $update['virtual_indate'] = $update_common['virtual_indate'];
                    $update['virtual_limit'] = $update_common['virtual_limit'];
                    $update['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                    $update['is_goodsfcode'] = $update_common['is_goodsfcode'];
                    $update['is_appoint'] = $update_common['is_appoint'];
                    $update['is_presell'] = $update_common['is_presell'];
                    // 虚拟商品不能有赠品
                    if ($update_common['is_virtual'] == 1) {
                        $update['is_have_gift'] = 0;
                        $goodsgift_model->delGoodsgift(array('goods_id' => $goods_id));
                    }
                    $goods_model->editGoodsById($update, $goods_id);
                } else {
                    $insert = array();
                    $insert['goods_commonid'] = $common_id;
                    $insert['goods_name'] = $update_common['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                    $insert['goods_advword'] = $update_common['goods_advword'];
                    $insert['gc_id'] = $update_common['gc_id'];
                    $insert['gc_id_1'] = $update_common['gc_id_1'];
                    $insert['gc_id_2'] = $update_common['gc_id_2'];
                    $insert['gc_id_3'] = $update_common['gc_id_3'];
                    $insert['brand_id'] = $update_common['brand_id'];
                    $insert['goods_price'] = $value['price'];
                    $insert['goods_promotion_price'] = $value['price'];
                    $insert['goods_marketprice'] = $value['marketprice'] == 0 ? $update_common['goods_marketprice'] : $value['marketprice'];
                    $insert['goods_serial'] = $value['sku'];
                    $insert['goods_storage_alarm'] = intval($value['alarm']);
                    $insert['goods_spec'] = serialize($value['sp_value']);
                    $insert['goods_storage'] = $value['stock'];
                    $insert['goods_image'] = $update_common['goods_image'];
                    $insert['goods_state'] = $update_common['goods_state'];
                    $insert['goods_addtime'] = TIMESTAMP;
                    $insert['goods_edittime'] = TIMESTAMP;
                    $insert['areaid_1'] = $update_common['areaid_1'];
                    $insert['areaid_2'] = $update_common['areaid_2'];
                    $insert['color_id'] = isset($value['color'])?intval($value['color']):'';
                    $insert['transport_id'] = $update_common['transport_id'];
                    $insert['goods_freight'] = $update_common['goods_freight'];
                    $insert['goods_vat'] = $update_common['goods_vat'];
                    $insert['goods_commend'] = $update_common['goods_commend'];
                    $insert['goods_stcids'] = $update_common['goods_stcids'];
                    $insert['is_virtual'] = $update_common['is_virtual'];
                    $insert['virtual_indate'] = $update_common['virtual_indate'];
                    $insert['virtual_limit'] = $update_common['virtual_limit'];
                    $insert['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                    $insert['is_goodsfcode'] = $update_common['is_goodsfcode'];
                    $insert['is_appoint'] = $update_common['is_appoint'];
                    $insert['is_presell'] = $update_common['is_presell'];
                    $goods_id = $goods_model->addGoods($insert);
                }
                $goodsid_array[] = intval($goods_id);
                $colorid_array[] = isset($value['color'])?intval($value['color']):'';
                $type_model->addGoodsType($goods_id, $common_id, array('cate_id' => input('post.cate_id'), 'type_id' => input('post.type_id'), 'attr' => input('post.attr/a')));
            }
        } else {
            $goods_info = $goods_model->getGoodsInfo(array('goods_spec' => serialize(null), 'goods_commonid' => $common_id), 'goods_id');
            if (!empty($goods_info)) {
                $goods_id = $goods_info['goods_id'];
                $update = array();
                $update['goods_commonid'] = $common_id;
                $update['goods_name'] = $update_common['goods_name'];
                $update['goods_advword'] = $update_common['goods_advword'];
                $update['gc_id'] = $update_common['gc_id'];
                $update['gc_id_1'] = $update_common['gc_id_1'];
                $update['gc_id_2'] = $update_common['gc_id_2'];
                $update['gc_id_3'] = $update_common['gc_id_3'];
                $update['brand_id'] = $update_common['brand_id'];
                $update['goods_price'] = $update_common['goods_price'];
                $update['goods_marketprice'] = $update_common['goods_marketprice'];
                $update['goods_serial'] = $update_common['goods_serial'];
                $update['goods_storage_alarm'] = $update_common['goods_storage_alarm'];
                $update['goods_spec'] = serialize(null);
                $update['goods_storage'] = intval(input('post.g_storage'));
                $update['goods_state'] = $update_common['goods_state'];
                $update['goods_edittime'] = TIMESTAMP;
                $update['areaid_1'] = $update_common['areaid_1'];
                $update['areaid_2'] = $update_common['areaid_2'];
                $update['color_id'] = 0;
                $update['transport_id'] = $update_common['transport_id'];
                $update['goods_freight'] = $update_common['goods_freight'];
                $update['goods_vat'] = $update_common['goods_vat'];
                $update['goods_commend'] = $update_common['goods_commend'];
                $update['goods_stcids'] = $update_common['goods_stcids'];
                $update['is_virtual'] = $update_common['is_virtual'];
                $update['virtual_indate'] = $update_common['virtual_indate'];
                $update['virtual_limit'] = $update_common['virtual_limit'];
                $update['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                $update['is_goodsfcode'] = $update_common['is_goodsfcode'];
                $update['is_appoint'] = $update_common['is_appoint'];
                $update['is_presell'] = $update_common['is_presell'];
                if ($update_common['is_virtual'] == 1) {
                    $update['is_have_gift'] = 0;
                    $goodsgift_model->delGoodsgift(array('goods_id' => $goods_id));
                }
                $goods_model->editGoodsById($update, $goods_id);
            } else {
                $insert = array();
                $insert['goods_commonid'] = $common_id;
                $insert['goods_name'] = $update_common['goods_name'];
                $insert['goods_advword'] = $update_common['goods_advword'];
                $insert['gc_id'] = $update_common['gc_id'];
                $insert['gc_id_1'] = $update_common['gc_id_1'];
                $insert['gc_id_2'] = $update_common['gc_id_2'];
                $insert['gc_id_3'] = $update_common['gc_id_3'];
                $insert['brand_id'] = $update_common['brand_id'];
                $insert['goods_price'] = $update_common['goods_price'];
                $insert['goods_promotion_price'] = $update_common['goods_price'];
                $insert['goods_marketprice'] = $update_common['goods_marketprice'];
                $insert['goods_serial'] = $update_common['goods_serial'];
                $insert['goods_storage_alarm'] = $update_common['goods_storage_alarm'];
                $insert['goods_spec'] = serialize(null);
                $insert['goods_storage'] = intval(input('post.g_storage'));
                $insert['goods_image'] = $update_common['goods_image'];
                $insert['goods_state'] = $update_common['goods_state'];
                $insert['goods_addtime'] = TIMESTAMP;
                $insert['goods_edittime'] = TIMESTAMP;
                $insert['areaid_1'] = $update_common['areaid_1'];
                $insert['areaid_2'] = $update_common['areaid_2'];
                $insert['color_id'] = 0;
                $insert['transport_id'] = $update_common['transport_id'];
                $insert['goods_freight'] = $update_common['goods_freight'];
                $insert['goods_vat'] = $update_common['goods_vat'];
                $insert['goods_commend'] = $update_common['goods_commend'];
                $insert['goods_stcids'] = $update_common['goods_stcids'];
                $insert['is_virtual'] = $update_common['is_virtual'];
                $insert['virtual_indate'] = $update_common['virtual_indate'];
                $insert['virtual_limit'] = $update_common['virtual_limit'];
                $insert['virtual_invalid_refund'] = $update_common['virtual_invalid_refund'];
                $insert['is_goodsfcode'] = $update_common['is_goodsfcode'];
                $insert['is_appoint'] = $update_common['is_appoint'];
                $insert['is_presell'] = $update_common['is_presell'];
                $goods_id = $goods_model->addGoods($insert);
            }
            $goodsid_array[] = intval($goods_id);
            $colorid_array[] = 0;
            $type_model->addGoodsType($goods_id, $common_id, array('cate_id' => input('post.cate_id'), 'type_id' => input('post.type_id'), 'attr' => input('post.attr/a')));
        }
        

        // 清理商品数据
        $condition = array();
        $condition[] = array('goods_id','not in', $goodsid_array);
        $condition[] = array('goods_commonid','=', $common_id);
        $goods_model->delGoods($condition);
        
        // 清理商品图片表
        $colorid_array = array_unique($colorid_array);
        $condition = array();
        $condition[] = array('color_id','not in', $colorid_array);
        $condition[] = array('goods_commonid','=', $common_id);
        $goods_model->delGoodsImages($condition);
        // 更新商品默认主图
        $default_image_list = $goods_model->getGoodsImageList(array('goods_commonid' => $common_id, 'goodsimage_isdefault' => 1), 'color_id,goodsimage_url');
        if (!empty($default_image_list)) {
            foreach ($default_image_list as $val) {
                $goods_model->editGoods(array('goods_image' => $val['goodsimage_url']), array('goods_commonid' => $common_id, 'color_id' => $val['color_id']));
            }
        }

        // 商品加入上架队列
        if (!empty(input('post.starttime'))) {
            $selltime = strtotime(input('post.starttime')) + intval(input('post.starttime_H')) * 3600 + intval(input('post.starttime_i')) * 60;
            if ($selltime > TIMESTAMP) {
                $this->addcron(array('cron_exetime' => $selltime, 'cron_value' => serialize(intval($common_id)), 'cron_type' => 'editProducesOnline'), true);
            }
        }
        // 添加操作日志
        $this->log('编辑商品，平台货号：' . $common_id,1);
        
        if ($update_common['is_virtual'] == 1 || $update_common['is_goodsfcode'] == 1 || $update_common['is_presell'] == 1) {
            // 如果是特殊商品清理促销活动，抢购、秒杀、组合销售
            model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'clearSpecialGoodsPromotion','cron_value'=>serialize(array('goods_commonid' => $common_id, 'goodsid_array' => $goodsid_array))));
        } else {
            // 更新商品促销价格
            model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'updateGoodsPromotionPriceByGoodsCommonId','cron_value'=>serialize($common_id)));
        }
        
        // 生成F码
        if ($update_common['is_goodsfcode'] == 1) {
            model('goodsfcode')->createGoodsfcode(array('goods_commonid' => $common_id, 'goodsfcode_count' => intval(input('post.g_fccount')), 'goodsfcode_prefix' => input('post.g_fcprefix')));
        }
        
        $return = $goods_model->editGoodsCommon($update_common, array('goods_commonid' => $common_id));
                } catch (\Exception $e){
            Db::rollback();
            ds_json_encode(10001,$e->getMessage());
        }
        //提交事务
        Db::commit();
        if ($return){
            ds_json_encode(10000,lang('ds_common_op_succ'));
        }

    }

    /**
     * 编辑库存
     */
    public function edit_storage() {
        $common_id=intval(input('param.commonid'));
        if(!$common_id){
            ds_json_encode(10001, lang('param_error'));
        }
        $goods_model = model('goods');
        $goodscommon_info = $goods_model->getGoodsCommonInfoByID($common_id);
        if (empty($goodscommon_info)) {
            ds_json_encode(10001, '您的商品不存在，或商品已被锁定，请删除促销解除锁定');
        }
        if (request()->isPost()) {
            Db::startTrans();
            try{
                $spec=input('param.spec/a');
                $min_item=array();
                foreach($spec as $goods_id => $val){
                    $data=array(
                        'goods_storage'=> abs(intval($val['stock'])),
                        'goods_storage_alarm'=> abs(intval($val['alarm'])),
                        'goods_serial'=> $val['sku'],
                    );
                    if(!$goodscommon_info['goods_lock']){
                        $data['goods_marketprice'] = abs(floatval($val['marketprice']));
                        $data['goods_price']=abs(floatval($val['price']));
                        $data['goods_promotion_price'] = abs(floatval($val['price']));
                        if($data['goods_price']==0){
                            throw new \think\Exception(lang('prompt_information4'), 10006);
                        }
                        if($data['goods_marketprice']>0 && $data['goods_price']>$data['goods_marketprice']){
                            throw new \think\Exception(lang('not_higher_than'), 10006);
                        }
                        if(empty($min_item) || $min_item['goods_price']>$data['goods_price']){
                            $min_item=$data;
                        }
                    }
                    $goods_model->editGoods($data, array('goods_commonid'=>$common_id,'goods_id'=>$goods_id));
                }
                //最低价格
                if(!empty($min_item)){
                    $goods_model->editGoodsCommon(array('goods_discount' => $min_item['goods_marketprice']>0?intval($min_item['goods_price'] / $min_item['goods_marketprice'] * 100):0, 'goods_marketprice' => $min_item['goods_marketprice'], 'goods_price' => $min_item['goods_price']),array('goods_commonid'=>$common_id));
                }
                
            }catch(\Exception $e){
                Db::rollback();
                ds_json_encode(10001,$e->getMessage());
            }
            Db::commit();
            ds_json_encode(10000, lang('ds_common_op_succ'));
        }else{
            View::assign('goodscommon_info',$goodscommon_info);
            $goods_list=$goods_model->getGoodsList(array('goods_commonid'=>$common_id));
            View::assign('goods_list',$goods_list);
            return View::fetch($this->template_dir . 'edit_storage');
        }
    }

    /**
     * 编辑图片
     */
    public function edit_image() {
        $common_id = intval(input('param.commonid'));
        if ($common_id <= 0) {
            $this->error(lang('param_error'), url('Goods/index'));
        }
        $goods_model = model('goods');
        $common_list = $goods_model->getGoodsCommonInfoByID($common_id);
        if (empty($common_list) || $common_list['goods_lock'] == 1) {
            $this->error('您的商品不存在，或商品已被锁定，请删除促销解除锁定');
        }
        $spec_value = unserialize($common_list['spec_value']);
        if(isset($spec_value[1])){
            View::assign('spec_value', $spec_value);
        }else{
            View::assign('spec_value', null);
        }
        View::assign('spec_value', $spec_value);

        $image_list = $goods_model->getGoodsImageList(array('goods_commonid' => $common_id));
        $image_list = array_under_reset($image_list, 'color_id', 2);

        $img_array = $goods_model->getGoodsList(array('goods_commonid' => $common_id), '*', 'color_id');
        // 整理，更具id查询颜色名称
        if (!empty($img_array)) {
            foreach ($img_array as $val) {
                if (isset($image_list[$val['color_id']])) {
                    $image_array[$val['color_id']] = $image_list[$val['color_id']];
                } else {
                    $image_array[$val['color_id']][0]['goodsimage_url'] = isset($val['goodsimage_url'])?$val['goodsimage_url']:'';
                    $image_array[$val['color_id']][0]['goodsimage_sort'] = 0;
                    $image_array[$val['color_id']][0]['goodsimage_isdefault'] = 1;
                }
                $colorid_array[] = $val['color_id'];
            }
        }
        View::assign('img', $image_array);


        $spec_model = model('spec');
        $value_array = $spec_model->getSpecvalueList(array(array('spvalue_id','in', $colorid_array)), 'spvalue_id,spvalue_name');
        if (empty($value_array)) {
            $value_array[] = array('spvalue_id' => '0', 'spvalue_name' => '无颜色');
        }
        View::assign('value_array', $value_array);

        View::assign('commonid', $common_id);

        $this->setAdminCurItem('edit_image');
        View::assign('edit_goods_sign', true);
        return View::fetch('goods_add_step3');
    }

    /**
     * 保存商品图片
     */
    public function edit_save_image() {
        if (request()->isPost()) {
            $common_id = intval(input('param.commonid'));
            $img_array = input('post.img/a');#获取数组
            if ($common_id <= 0 || empty($img_array)) {
                ds_json_encode(10001,lang('param_error'));
            }
            $goods_model = model('goods');
            // 删除原有图片信息
            $goods_model->delGoodsImages(array('goods_commonid' => $common_id));
            // 保存
            $insert_array = array();
            foreach ($img_array as $key => $value) {
                $k = 0;
                foreach ($value as $v) {
                    if ($v['name'] == '') {
                        continue;
                    }
                    // 商品默认主图
                    $update_array = array();        // 更新商品主图
                    $update_where = array();
                    $update_array['goods_image'] = $v['name'];
                    $update_where['goods_commonid'] = $common_id;
                    $update_where['color_id'] = $key;
                    if ($k == 0 || $v['default'] == 1) {
                        $k++;
                        $update_array['goods_image'] = $v['name'];
                        $update_where['goods_commonid'] = $common_id;
                        $update_where['color_id'] = $key;
                        // 更新商品主图
                        $goods_model->editGoods($update_array, $update_where);
                    }
                    $tmp_insert = array();
                    $tmp_insert['goods_commonid'] = $common_id;
                    $tmp_insert['color_id'] = $key;
                    $tmp_insert['goodsimage_url'] = $v['name'];
                    $tmp_insert['goodsimage_sort'] = ($v['default'] == 1) ? 0 : intval($v['sort']);
                    $tmp_insert['goodsimage_isdefault'] = $v['default'];
                    $insert_array[] = $tmp_insert;
                }
            }
            $rs = $goods_model->addGoodsImagesAll($insert_array);
            if ($rs) {
                // 添加操作日志
                $this->log('编辑商品，平台货号：' . $common_id,1);
                ds_json_encode(10000,lang('ds_common_op_succ'));
            } else {
                ds_json_encode(10001,lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑分类
     */
    public function edit_class() {
        // 实例化商品分类模型
        $goodsclass_model = model('goodsclass');
        // 商品分类
        $goods_class = $goodsclass_model->getGoodsclass();

        // 常用商品分类
        $staple_model = model('goodsclassstaple');
        $param_array = array();
        $staple_array = $staple_model->getGoodsclassstapleList($param_array);

        View::assign('staple_array', $staple_array);
        View::assign('goods_class', $goods_class);

        View::assign('commonid', input('param.commonid'));
        $this->setAdminCurItem('edit_class');
        View::assign('edit_goods_sign', true);
        return View::fetch('goods_add_step1');
    }

    /**
     * 设置广告词
     */
    public function edit_jingle() {
        if (request()->isPost()) {
            $common_id = $this->checkRequestCommonId(input('param.commonid'));
            $commonid_array = explode(',', $common_id);
            $where = array(array('goods_commonid','in', $commonid_array));
            $update = array('goods_advword' => trim(input('post.g_jingle')));
            $return = model('goods')->editProducesNoLock($where, $update);
            if ($return) {
                // 添加操作日志
                $this->log('设置广告词，平台货号：' . $common_id,1);
                ds_json_encode(10000,lang('ds_common_op_succ'));
            } else {
                ds_json_encode(10001,lang('ds_common_op_fail'));
            }
        }
        $this->checkRequestCommonId(input('param.commonid'));

        return View::fetch('edit_jingle');
    }

    /**
     * 添加赠品
     */
    public function add_gift() {
        $common_id = intval(input('param.commonid'));
        if ($common_id <= 0) {
            $this->error(lang('param_error'), url('Goods/index'));
        }
        $goods_model = model('goods');
        $goodscommon_info = $goods_model->getGoodsCommonInfoByID($common_id);

        // 商品列表
        $goods_array = $goods_model->getGoodsListForPromotion(array(array('goods_commonid' ,'=', $common_id)), '*', 0, 'gift');
        View::assign('goods_array', $goods_array);

        // 赠品列表
        $gift_list = model('goodsgift')->getGoodsgiftList(array('goods_commonid' => $common_id));
        $gift_array = array();
        if (!empty($gift_list)) {
            foreach ($gift_list as $val) {
                $gift_array[$val['goods_id']][] = $val;
            }
        }
        View::assign('gift_array', $gift_array);

        $this->setAdminCurItem('add_gift');
        return View::fetch('goods_edit_add_gift');
    }

    /**
     * 保存赠品
     */
    public function save_gift() {
        if (!request()->isPost()) {
            ds_json_encode(10001,lang('param_error'));
        }
        $gift_array = input('post.gift/a',array());
        $commonid = intval(input('param.commonid'));
        if ($commonid <= 0) {
            ds_json_encode(10001,lang('param_error'));
        }

        $goods_model = model('goods');
        $goodsgift_model = model('goodsgift');

        // 验证商品是否存在
        $goods_list = $goods_model->getGoodsListForPromotion(array(array('goods_commonid' ,'=', $commonid)), 'goods_id', 0, 'gift');
        if (empty($goods_list)) {
            ds_json_encode(10001,lang('param_error'));
        }
        // 删除该商品原有赠品
        $goodsgift_model->delGoodsgift(array('goods_commonid' => $commonid));
        // 重置商品礼品标记
        $goods_model->editGoods(array('is_have_gift' => 0), array('goods_commonid' => $commonid));
        // 商品id
        $goodsid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $insert = array();
        $update_goodsid = array();
        foreach ($gift_array as $key => $val) {

            $owner_gid = intval($key);  // 主商品id
            // 验证主商品是否为本店铺商品,如果不是本店商品继续下一个循环
            if (!in_array($owner_gid, $goodsid_array)) {
                continue;
            }
            $update_goodsid[] = $owner_gid;
            foreach ($val as $k => $v) {
                $gift_gid = intval($k); // 礼品id
                // 验证赠品是否为本店铺商品，如果不是本店商品继续下一个循环
                $gift_info = $goods_model->getGoodsInfoByID($gift_gid);
                $is_general = $goods_model->checkIsGeneral($gift_info);     // 验证是否为普通商品
                if ($is_general == false) {
                    continue;
                }

                $array = array();
                $array['goods_id'] = $owner_gid;
                $array['goods_commonid'] = $commonid;
                $array['gift_goodsid'] = $gift_gid;
                $array['gift_goodsname'] = $gift_info['goods_name'];
                $array['gift_goodsimage'] = $gift_info['goods_image'];
                $array['gift_amount'] = intval($v);
                $insert[] = $array;
            }
        }
        // 插入数据
        if (!empty($insert))
            $goodsgift_model->addGoodsgiftAll($insert);
        // 更新商品赠品标记
        if (!empty($update_goodsid)){
            $goods_model->editGoodsById(array('is_have_gift' => 1), $update_goodsid);
        }
        ds_json_encode(10000,lang('ds_common_save_succ'));
    }

    /**
     * 推荐搭配
     */
    public function add_combo() {
        $common_id = intval(input('param.commonid'));
        if ($common_id <= 0) {
            $this->error(lang('param_error'), url('Goods/index'));
        }
        $goods_model = model('goods');
        $goodscommon_info = $goods_model->getGoodsCommonInfoByID($common_id);
        if (empty($goodscommon_info)) {
            $this->error(lang('param_error'), url('Goods/index'));
        }

        $goods_array = $goods_model->getGoodsListForPromotion(array(array('goods_commonid' ,'=', $common_id)), '*', 0, 'combo');
        View::assign('goods_array', $goods_array);

        // 推荐组合商品列表
        $combo_list = model('goodscombo')->getGoodscomboList(array('goods_commonid' => $common_id));
        $combo_goodsid_array = array();
        if (!empty($combo_list)) {
            foreach ($combo_list as $val) {
                $combo_goodsid_array[] = $val['combo_goodsid'];
            }
        }

        $combo_goods_array = $goods_model->getGeneralGoodsList(array(array('goods_id','in', $combo_goodsid_array)), 'goods_id,goods_name,goods_image,goods_price');
        $combo_goods_list = array();
        if (!empty($combo_goods_array)) {
            foreach ($combo_goods_array as $val) {
                $combo_goods_list[$val['goods_id']] = $val;
            }
        }

        $combo_array = array();
        foreach ($combo_list as $val) {
            $combo_array[$val['goods_id']][] = $combo_goods_list[$val['combo_goodsid']];
        }
        View::assign('combo_array', $combo_array);

        $this->setAdminCurItem('add_combo');
        return View::fetch('goods_edit_add_combo');
    }

    /**
     * 保存赠品
     */
    public function save_combo() {
        if (!request()->isPost()) {
            ds_json_encode(10001,lang('param_error'));
        }

        $combo_array = input('post.combo/a');#获取数组
        
        $commonid = intval(input('param.commonid'));
        if ($commonid <= 0) {
            ds_json_encode(10001,lang('param_error'));
        }

        $goods_model = model('goods');
        $goodscombo_model = model('goodscombo');

        // 验证商品是否存在
        $goods_list = $goods_model->getGoodsListForPromotion(array(array('goods_commonid' ,'=', $commonid)), 'goods_id', 0, 'combo');
        if (empty($goods_list)) {
            ds_json_encode(10001,lang('param_error'));
        }
        // 删除该商品原有赠品
        $goodscombo_model->delGoodscombo(array('goods_commonid' => $commonid));
        // 商品id
        $goodsid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
        }

        $insert = array();
        if (!empty($combo_array)) {
            foreach ($combo_array as $key => $val) {

                $owner_gid = intval($key);  // 主商品id
                // 验证主商品是否为本店铺商品,如果不是本店商品继续下一个循环
                if (!in_array($owner_gid, $goodsid_array)) {
                    continue;
                }
                $val = array_unique($val);
                foreach ($val as $v) {
                    $combo_gid = intval($v); // 礼品id
                    // 验证推荐组合商品是否为本店铺商品，如果不是本店商品继续下一个循环
                    $combo_info = $goods_model->getGoodsInfoByID($combo_gid);
                    $is_general = $goods_model->checkIsGeneral($combo_info);     // 验证是否为普通商品
                    if ($is_general == false || $owner_gid == $combo_gid) {
                        continue;
                    }

                    $array = array();
                    $array['goods_id'] = $owner_gid;
                    $array['goods_commonid'] = $commonid;
                    $array['combo_goodsid'] = $combo_gid;
                    $insert[] = $array;
                }
            }
            // 插入数据
            $goodscombo_model->addGoodscomboAll($insert);
        }
        ds_json_encode(10000,lang('ds_common_save_succ'));
    }

    /**
     * 搜索商品（添加赠品/推荐搭配)
     */
    public function search_goods() {
        $where = array();
        $name = input('param.name');
        if ($name) {
            $where[] = array('goods_name','like', '%' . $name . '%');
        }
        $goods_model = model('goods');
        $goods_list = $goods_model->getGeneralGoodsList($where, '*', 5);
        View::assign('show_page', $goods_model->page_info->render());
        View::assign('goods_list', $goods_list);
        echo View::fetch('goods_edit_search_goods');exit;
    }

    /**
     * 下载F码
     */
    public function download_f_code_excel() {
        $common_id = input('param.commonid');
        if ($common_id <= 0) {
            $this->error(lang('param_error'));
        }
        $common_info = model('goods')->getGoodsCommonInfoByID($common_id);
        if (empty($common_info)) {
            $this->error(lang('param_error'));
        }
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '号码');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '使用状态');
        $data = model('goodsfcode')->getGoodsfcodeList(array('goods_commonid' => $common_id));
        foreach ($data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => $v['goodsfcode_code']);
            $tmp[] = array('data' => $v['goodsfcode_state'] ? '已使用' : '未使用');
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset($common_info['goods_name'], CHARSET));
        $excel_obj->generateXML($excel_obj->charset($common_info['goods_name'], CHARSET) . '-' . date('Y-m-d-H', TIMESTAMP));
    }

    /**
     * 验证commonid
     */
    private function checkRequestCommonId($common_ids) {
        if (!preg_match('/^[\d,]+$/i', $common_ids)) {
            ds_json_encode(10001,lang('param_error'));
        }
        return $common_ids;
    }

    /**
     *    栏目菜单
     */
    function getAdminItemList() {
        $item_list = array(
            array(
                'name' => 'allgoods',
                'text' => '出售中的商品',
                'url' => url('Goods/index'),
            ),
            array(
                'name' => 'lockup',
                'text' => '仓库商品',
                'url' => url('Goods/index', ['type' => 'lockup'])
            ),
            array(
                'name' => 'goods_add',
                'text' => '新增商品',
                'url' => url('Goodsadd/index'),
            ),
            array(
                'name' => 'taobao_import',
                'text' => '淘宝导入',
                'url' => url('TaobaoImport/index'),
            ),
        );
        if (request()->action() === 'edit_goods' || request()->action() === 'edit_image' || request()->action() === 'add_gift' || request()->action() === 'add_combo' || request()->action() === 'edit_class') {
            $item_list[] = array(
                'name' => 'edit_goods',
                'text' => '编辑商品',
                'url' => url('Goods/edit_goods', ['commonid' => input('param.commonid')]),
            );
            $item_list[] = array(
                'name' => 'edit_image',
                'text' => '编辑图片',
                'url' => url('Goods/edit_image', ['commonid' => input('param.commonid')]),
            );
            $item_list[] = array(
                'name' => 'add_gift',
                'text' => '赠送赠品',
                'url' => url('Goods/add_gift', ['commonid' => input('param.commonid')]),
            );
            $item_list[] = array(
                'name' => 'add_combo',
                'text' => '推荐组合',
                'url' => url('Goods/add_combo', ['commonid' => input('param.commonid')]),
            );
            $item_list[] = array(
                'name' => 'edit_class',
                'text' => '选择分类',
                'url' => url('Goods/edit_class', ['commonid' => input('param.commonid')]),
            );
        }
        return $item_list;
    }

}

?>
