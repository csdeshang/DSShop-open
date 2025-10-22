<?php

namespace app\admin\controller;

use think\facade\View;
use think\facade\Lang;
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
 * 控制器
 */
class Goodsadd extends AdminControl {

    public function initialize() {
        parent::initialize();
        error_reporting(0);
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/goods.lang.php');
        $this->template_dir = 'goods/';
    }

    public function index() {
        $this->add_step_one();
    }

    /**
     * 添加商品
     */
    public function add_step_one() {
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
        $this->setAdminCurItem();
        echo View::fetch($this->template_dir . 'goods_add_step1');
        exit;
    }

    /**
     * 添加商品
     */
    public function add_step_two() {
        // 实例化商品分类模型
        $goodsclass_model = model('goodsclass');
        // 现暂时改为从匿名“自营店铺专属等级”中判断
        $editor_multimedia = true;
        View::assign('editor_multimedia', $editor_multimedia);

        $gc_id = intval(input('get.class_id'));

        // 验证商品分类是否存在且商品分类是否为最后一级
        $data = model('goodsclass')->getGoodsclassForCacheModel();
        if (!isset($data[$gc_id]) || isset($data[$gc_id]['child']) || isset($data[$gc_id]['childchild'])) {
            $this->error(lang('goods_index_again_choose_category1'));
        }

        // 更新常用分类信息
        $goods_class = $goodsclass_model->getGoodsclassLineForTag($gc_id);
        View::assign('goods_class', $goods_class);
        model('goodsclassstaple')->autoIncrementStaple($goods_class);

        // 获取类型相关数据
        $typeinfo = model('type')->getAttribute($goods_class['type_id'], $gc_id);
        list($spec_json, $spec_list, $attr_list, $brand_list) = $typeinfo;
        View::assign('sign_i', count($spec_list));
        View::assign('spec_list', $spec_list);
        View::assign('attr_list', $attr_list);
        View::assign('brand_list', $brand_list);

        // 小时分钟显示
        $hour_array = array(
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17',
            '18', '19', '20', '21', '22', '23'
        );
        View::assign('hour_array', $hour_array);
        $minute_array = array('05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
        View::assign('minute_array', $minute_array);

        $this->setAdminCurItem();
        return View::fetch($this->template_dir . 'goods_add_step2');
    }

    /**
     * 保存商品（商品发布第二步使用）
     */
    public function save_goods() {
        if (request()->isPost()) {
            $goods_model = model('goods');
            Db::startTrans();
            try {
                $type_model = model('type');

                // 分类信息
                $goods_class = model('goodsclass')->getGoodsclassLineForTag(intval(input('post.cate_id')));

                $common_array = array();
                $common_array['goods_name'] = input('post.g_name');
                $common_array['goods_advword'] = input('post.g_jingle');
                $common_array['gc_id'] = intval(input('post.cate_id'));
                $common_array['gc_id_1'] = intval($goods_class['gc_id_1']);
                $common_array['gc_id_2'] = intval($goods_class['gc_id_2']);
                $common_array['gc_id_3'] = intval($goods_class['gc_id_3']);
                $common_array['gc_name'] = input('post.cate_name');
                $common_array['brand_id'] = input('post.b_id');
                $common_array['brand_name'] = input('post.b_name');
                $common_array['type_id'] = intval(input('post.type_id'));
                $common_array['goods_image'] = input('post.image_path');
                $common_array['goods_price'] = floatval(input('post.g_price'));
                $common_array['goods_marketprice'] = floatval(input('post.g_marketprice'));
                $common_array['goods_costprice'] = floatval(input('post.g_costprice'));
                $common_array['goods_discount'] = floatval(input('post.g_discount'));
                $common_array['goods_serial'] = input('post.g_serial');
                $common_array['goods_storage_alarm'] = intval(input('post.g_alarm'));
                $common_array['goods_attr'] = input('post.attr/a') ? serialize(input('post.attr/a')) : serialize(null);
                $goods_body = preg_replace_callback("/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i", function ($matches) {
                    return str_replace($matches[2], strip_tags($matches[2]), $matches[0]);
                }, htmlspecialchars_decode(input('post.goods_body')));
                $common_array['goods_body'] = $goods_body;

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
                $common_array['mobile_body'] = $mobile_body;
                $common_array['goods_commend'] = intval(input('post.g_commend'));
                $common_array['goods_state'] = intval(input('post.g_state'));
                $common_array['goods_addtime'] = TIMESTAMP;
                $common_array['goods_shelftime'] = strtotime(input('post.starttime')) + intval(input('post.starttime_H')) * 3600 + intval(input('post.starttime_i')) * 60;
                $common_array['spec_name'] = is_array(input('post.spec/a')) ? serialize(input('post.sp_name/a')) : serialize(null);
                $common_array['spec_value'] = is_array(input('post.spec/a')) ? serialize(input('post.sp_val/a')) : serialize(null);
                $common_array['goods_vat'] = intval(input('post.g_vat'));
                $common_array['areaid_1'] = intval(input('post.province_id'));
                $common_array['areaid_2'] = intval(input('post.city_id'));
                $common_array['transport_id'] = (input('post.freight') == '0') ? '0' : intval(input('post.transport_id')); // 售卖区域
                $common_array['transport_title'] = input('post.transport_title');
                $common_array['goods_freight'] = floatval(input('post.g_freight'));

                //验证数据  BEGIN
                $goods_validate = ds_validate('goods');
                if (!$goods_validate->scene('edit_save_goods')->check($common_array)) {
                    throw new \think\Exception($goods_validate->getError(), 10006);
                }
                //验证数据  END
                //查询店铺商品分类
                $goods_stcids_arr = array();
                if (empty($goods_stcids_arr)) {
                    $common_array['goods_stcids'] = '';
                } else {
                    $common_array['goods_stcids'] = ',' . implode(',', $goods_stcids_arr) . ','; // 首尾需要加,
                }

                $common_array['plateid_top'] = intval(input('post.plate_top')) > 0 ? intval(input('post.plate_top')) : '';
                $common_array['plateid_bottom'] = intval(input('post.plate_bottom')) > 0 ? intval(input('post.plate_bottom')) : '';
                $common_array['is_virtual'] = intval(input('post.is_gv'));
                $common_array['virtual_indate'] = input('post.g_vindate') != '' ? (strtotime(input('post.g_vindate')) + 24 * 60 * 60 - 1) : 0;  // 当天的最后一秒结束
                $common_array['virtual_limit'] = intval(input('post.g_vlimit')) > 10 || intval(input('post.g_vlimit')) < 0 ? 10 : intval(input('post.g_vlimit'));
                $common_array['virtual_invalid_refund'] = intval(input('post.g_vinvalidrefund'));
                $common_array['is_goodsfcode'] = intval(input('post.is_fc'));
                $common_array['is_appoint'] = intval(input('post.is_appoint'));     // 只有库存为零的商品可以预约
                $common_array['appoint_satedate'] = $common_array['is_appoint'] == 1 ? strtotime(input('post.g_saledate')) : '';   // 预约商品的销售时间
                $common_array['is_presell'] = $common_array['goods_state'] == 1 ? intval(input('post.is_presell')) : 0;     // 只有出售中的商品可以预售
                $common_array['presell_deliverdate'] = $common_array['is_presell'] == 1 ? strtotime(input('post.g_deliverdate')) : ''; // 预售商品的发货时间
                // 保存数据
                $common_id = $goods_model->addGoodsCommon($common_array);
                if ($common_id) {
                    // 生成的商品id（SKU）
                    $goodsid_array = array();
                    // 商品规格
                    $spec_array = input('post.spec/a'); #获取数组
                    if (is_array($spec_array) && !empty($spec_array)) {
                        foreach ($spec_array as $value) {
                            $goods = array();
                            $goods['goods_commonid'] = $common_id;
                            $goods['goods_name'] = $common_array['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                            $goods['goods_advword'] = $common_array['goods_advword'];
                            $goods['gc_id'] = $common_array['gc_id'];
                            $goods['gc_id_1'] = $common_array['gc_id_1'];
                            $goods['gc_id_2'] = $common_array['gc_id_2'];
                            $goods['gc_id_3'] = $common_array['gc_id_3'];
                            $goods['brand_id'] = $common_array['brand_id'];
                            $goods['goods_price'] = $value['price'];
                            $goods['goods_promotion_price'] = $value['price'];
                            $goods['goods_marketprice'] = $value['marketprice'] == 0 ? $common_array['goods_marketprice'] : $value['marketprice'];
                            $goods['goods_serial'] = $value['sku'];
                            $goods['goods_storage_alarm'] = intval($value['alarm']);
                            $goods['goods_spec'] = serialize($value['sp_value']);
                            $goods['goods_storage'] = $value['stock'];
                            $goods['goods_image'] = $common_array['goods_image'];
                            $goods['goods_state'] = $common_array['goods_state'];
                            $goods['goods_addtime'] = TIMESTAMP;
                            $goods['goods_edittime'] = TIMESTAMP;
                            $goods['areaid_1'] = $common_array['areaid_1'];
                            $goods['areaid_2'] = $common_array['areaid_2'];
                            $goods['color_id'] = isset($value['color']) ? intval($value['color']) : '';
                            $goods['transport_id'] = $common_array['transport_id'];
                            $goods['goods_freight'] = $common_array['goods_freight'];
                            $goods['goods_vat'] = $common_array['goods_vat'];
                            $goods['goods_commend'] = $common_array['goods_commend'];
                            $goods['goods_stcids'] = $common_array['goods_stcids'];
                            $goods['is_virtual'] = $common_array['is_virtual'];
                            $goods['virtual_indate'] = $common_array['virtual_indate'];
                            $goods['virtual_limit'] = $common_array['virtual_limit'];
                            $goods['virtual_invalid_refund'] = $common_array['virtual_invalid_refund'];
                            $goods['is_goodsfcode'] = $common_array['is_goodsfcode'];
                            $goods['is_appoint'] = $common_array['is_appoint'];
                            $goods['is_presell'] = $common_array['is_presell'];
                            $goods_id = $goods_model->addGoods($goods);
                            $type_model->addGoodsType($goods_id, $common_id, array(
                                'cate_id' => input('post.cate_id'), 'type_id' => input('post.type_id'), 'attr' => input('post.attr/a')
                            ));

                            $goodsid_array[] = $goods_id;
                        }
                    } else {
                        $goods = array();
                        $goods['goods_commonid'] = $common_id;
                        $goods['goods_name'] = $common_array['goods_name'];
                        $goods['goods_advword'] = $common_array['goods_advword'];
                        $goods['gc_id'] = $common_array['gc_id'];
                        $goods['gc_id_1'] = $common_array['gc_id_1'];
                        $goods['gc_id_2'] = $common_array['gc_id_2'];
                        $goods['gc_id_3'] = $common_array['gc_id_3'];
                        $goods['brand_id'] = $common_array['brand_id'];
                        $goods['goods_price'] = $common_array['goods_price'];
                        $goods['goods_promotion_price'] = $common_array['goods_price'];
                        $goods['goods_marketprice'] = $common_array['goods_marketprice'];
                        $goods['goods_serial'] = $common_array['goods_serial'];
                        $goods['goods_storage_alarm'] = $common_array['goods_storage_alarm'];
                        $goods['goods_spec'] = serialize(null);
                        $goods['goods_storage'] = intval(input('post.g_storage'));
                        $goods['goods_image'] = $common_array['goods_image'];
                        $goods['goods_state'] = $common_array['goods_state'];
                        $goods['goods_addtime'] = TIMESTAMP;
                        $goods['goods_edittime'] = TIMESTAMP;
                        $goods['areaid_1'] = $common_array['areaid_1'];
                        $goods['areaid_2'] = $common_array['areaid_2'];
                        $goods['color_id'] = 0;
                        $goods['transport_id'] = $common_array['transport_id'];
                        $goods['goods_freight'] = $common_array['goods_freight'];
                        $goods['goods_vat'] = $common_array['goods_vat'];
                        $goods['goods_commend'] = $common_array['goods_commend'];
                        $goods['goods_stcids'] = $common_array['goods_stcids'];
                        $goods['is_virtual'] = $common_array['is_virtual'];
                        $goods['virtual_indate'] = $common_array['virtual_indate'];
                        $goods['virtual_limit'] = $common_array['virtual_limit'];
                        $goods['virtual_invalid_refund'] = $common_array['virtual_invalid_refund'];
                        $goods['is_goodsfcode'] = $common_array['is_goodsfcode'];
                        $goods['is_appoint'] = $common_array['is_appoint'];
                        $goods['is_presell'] = $common_array['is_presell'];
                        $goods_id = $goods_model->addGoods($goods);
                        $type_model->addGoodsType($goods_id, $common_id, array(
                            'cate_id' => input('post.cate_id'), 'type_id' => input('post.type_id'), 'attr' => input('post.attr/a')
                        ));

                        $goodsid_array[] = $goods_id;
                    }


                    // 商品加入上架队列
                    if (!empty(input('post.starttime'))) {
                        $selltime = strtotime(input('post.starttime')) + intval(input('post.starttime_H')) * 3600 + intval(input('post.starttime_i')) * 60;
                        if ($selltime > TIMESTAMP) {
                            $this->addcron(array('cron_exetime' => $selltime, 'cron_value' => serialize(intval($common_id)), 'cron_type' => 'editProducesOnline'), true);
                        }
                    }

                    // 记录日志
                    $this->log('添加商品，平台货号:' . $common_id, 1);

                    // 生成F码
                    if ($common_array['is_goodsfcode'] == 1) {
                        model('goodsfcode')->createGoodsfcode(array('goods_commonid' => $common_id, 'goodsfcode_count' => intval(input('post.g_fccount')), 'goodsfcode_prefix' => input('post.g_fcprefix')));
                    }
                } else {
                    throw new \think\Exception(lang('goods_index_goods_add_fail'), 10006);
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage(), get_referer());
            }

            $this->redirect(url('Goodsadd/add_step_three', ['commonid' => $common_id]));
        }
    }

    /**
     * 第三步添加颜色图片
     */
    public function add_step_three() {
        $common_id = input('param.commonid');
        if ($common_id <= 0) {
            $this->error(lang('param_error'), url('Goods/index'));
        }

        $goods_model = model('goods');
        $img_array = $goods_model->getGoodsList(array('goods_commonid' => $common_id), 'color_id,goods_image', 'color_id');
        // 整理，更具id查询颜色名称
        $colorid_array = array();
        if (!empty($img_array)) {
            $image_array = array();
            foreach ($img_array as $val) {
                $image_array[$val['color_id']][0]['goodsimage_url'] = $val['goods_image'];
                $image_array[$val['color_id']][0]['goodsimage_isdefault'] = 1;
                $colorid_array[] = $val['color_id'];
            }
            View::assign('img', $image_array);
        }

        $common_list = $goods_model->getGoodsCommonInfoByID($common_id);
        $spec_value = unserialize($common_list['spec_value']);
        View::assign('spec_value', $spec_value);

        $spec_model = model('spec');
        $value_array = $spec_model->getSpecvalueList(array(array('spvalue_id', 'in', $colorid_array)), 'spvalue_id,spvalue_name');
        if (empty($value_array)) {
            $value_array[] = array('spvalue_id' => '0', 'spvalue_name' => '无颜色');
        }
        View::assign('value_array', $value_array);

        View::assign('commonid', $common_id);

        $this->setAdminCurItem();
        return View::fetch($this->template_dir . 'goods_add_step3');
    }

    /**
     * 保存商品颜色图片
     */
    public function save_image() {
        if (request()->isPost()) {
            $common_id = intval(input('param.commonid'));
            $img_array = input('post.img/a'); #获取数组
            if ($common_id <= 0 || empty($img_array)) {
                ds_json_encode(10001, lang('param_error'));
            }
            $goods_model = model('goods');
            // 保存
            $insert_array = array();
            $k = 0;
            foreach ($img_array as $key => $value) {
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
            if (!empty($insert_array)) {
                $rs = $goods_model->addGoodsImagesAll($insert_array);
                if ($rs) {
                    $this->redirect(url('Goodsadd/add_step_four', ['commonid' => $common_id]));
                } else {
                    $this->error(lang('ds_common_save_fail'));
                }
            } else {
                $this->redirect(url('Goodsadd/add_step_four', ['commonid' => $common_id]));
            }
        }
    }

    /**
     * 商品发布第四步
     */
    public function add_step_four() {
        $commonid = input('param.commonid');
        // 单条商品信息
        $goods_info = model('goods')->getGoodsInfo(array('goods_commonid' => $commonid));

        // 自动发布动态
        $data_array = array();
        $data_array['goods_id'] = $goods_info['goods_id'];
        $data_array['goods_name'] = $goods_info['goods_name'];
        $data_array['goods_image'] = $goods_info['goods_image'];
        $data_array['goods_price'] = $goods_info['goods_price'];
        $data_array['goods_transfee_charge'] = $goods_info['goods_freight'] == 0 ? 1 : 0;
        $data_array['goods_freight'] = $goods_info['goods_freight'];

        View::assign('allow_gift', model('goods')->checkGoodsIfAllowGift($goods_info));
        View::assign('allow_combo', model('goods')->checkGoodsIfAllowCombo($goods_info));
        View::assign('goods_id', $goods_info['goods_id']);

        $this->setAdminCurItem();
        return View::fetch($this->template_dir . 'goods_add_step4');
    }

    /**
     * 上传图片
     */
    public function image_upload() {
        // 判断图片数量是否超限
        $album_model = model('album');
        $aclass_id = input('param.aclass_id');
        if ($aclass_id) {
            $class_info = $album_model->getOne(array('aclass_id' => $aclass_id), 'albumclass');
        }
        if (!$aclass_id || !$class_info) {
            $class_info = $album_model->getOne(array('aclass_isdefault' => 1), 'albumclass');
        }
        /**
         * 上传图片
         */
        $time = TIMESTAMP;
        //上传文件保存路径
        $upload_path = ATTACH_GOODS . '/' . date('Ymd', $time);
        $save_name = date('YmdHis', $time) . rand(10000, 99999);
        $file_name = input('post.name');

        $result = upload_albumpic($upload_path, $file_name, $save_name);
        if ($result['code'] == '10000') {
            $img_path = $result['result'];
            list($width, $height, $type, $attr) = getimagesize($img_path);
            $img_path = substr(strrchr($img_path, "/"), 1);
        } else {
            //未上传图片或出错不做后面处理
            exit(json_encode(array('error' => '上传失败,请检查上传内容')));
        }

        // 存入相册
        $insert_array = array();
        $insert_array['apic_name'] = $img_path;
        $insert_array['apic_tag'] = '';
        $insert_array['aclass_id'] = $class_info['aclass_id'];
        $insert_array['apic_cover'] = $img_path;
        $insert_array['apic_size'] = intval($_FILES[$file_name]['size']);
        $insert_array['apic_spec'] = $width . 'x' . $height;
        $insert_array['apic_uploadtime'] = $time;
        model('album')->addAlbumpic($insert_array);

        $data = array();
        $data ['thumb_name'] = goods_cthumb($img_path, 240);
        $data ['name'] = $img_path;

        // 整理为json格式
        $output = json_encode($data);
        echo $output;
        exit();
    }

    /**
     * 淘宝 ajax获取商品分类的子级数据
     */
    public function ajax_goods_class_import() {
        $gc_id = intval(input('get.gc_id'));
        $deep = intval(input('get.deep'));
        if ($gc_id <= 0 || $deep <= 0 || $deep >= 4) {
            exit(json_encode(array()));
        }
        $deep += 1;
        $goodsclass_model = model('goodsclass');

        // 验证分类是否存在
        $gc_info = $goodsclass_model->getGoodsclassInfoById($gc_id);
        if (empty($gc_info)) {
            echo 'false';
            exit();
        }
        if ($deep != 4) {
            $gc_list = $goodsclass_model->getGoodsclass($gc_id, $deep);
        }
        // 分类不为空输出分类信息
        if (!empty($gc_list)) {
            $data = array('type' => 'class', 'data' => $gc_list, 'deep' => $deep);
        } else {
            // 查询类型
            $type_model = model('type');
            $spec_list = $type_model->getSpecByType(array('type_id' => $gc_info['type_id']), 't.type_id, s.*');

            $data = array('type' => 'spec', 'data' => $spec_list, 'gcid' => $gc_id, 'deep' => $deep);
        }
        echo json_encode($data);
        exit();
    }

    public function ajax_goods_class() {
        $gc_id = intval(input('get.gc_id'));
        $deep = intval(input('get.deep'));
        if ($gc_id <= 0 || $deep <= 0 || $deep >= 4) {
            exit(json_encode(array()));
        }
        $goodsclass_model = model('goodsclass');
        $list = $goodsclass_model->getGoodsclass($gc_id, $deep);
        if (empty($list)) {
            exit(json_encode(array()));
        }
        echo json_encode($list);

        exit();
    }

    /**
     * ajax删除常用分类
     */
    public function ajax_stapledel() {
        $staple_id = intval(input('get.staple_id'));
        if ($staple_id < 1) {
            echo json_encode(array('done' => false, 'msg' => lang('param_error')));
            die();
        }
        /**
         * 实例化模型
         */
        $staple_model = model('goodsclassstaple');

        $result = $staple_model->delGoodsclassstaple(array('staple_id' => $staple_id));
        if ($result) {
            echo json_encode(array(
                'done' => true
            ));
            die();
        } else {
            echo json_encode(array(
                'done' => false, 'msg' => ''
            ));
            die();
        }
    }

    /**
     * ajax选择常用商品分类
     */
    public function ajax_show_comm() {
        $staple_id = intval(input('get.stapleid'));

        /**
         * 查询相应的商品分类id
         */
        $staple_model = model('goodsclassstaple');
        $staple_info = $staple_model->getGoodsclassstapleInfo(array('staple_id' => intval($staple_id)), 'gc_id_1,gc_id_2,gc_id_3');
        if (empty($staple_info) || !is_array($staple_info)) {
            echo json_encode(array(
                'done' => false, 'msg' => ''
            ));
            die();
        }

        $list_array = array();
        $list_array['gc_id'] = 0;
        $list_array['type_id'] = $staple_info['type_id'];
        $list_array['done'] = true;
        $list_array['one'] = '';
        $list_array['two'] = '';
        $list_array['three'] = '';

        $gc_id_1 = intval($staple_info['gc_id_1']);
        $gc_id_2 = intval($staple_info['gc_id_2']);
        $gc_id_3 = intval($staple_info['gc_id_3']);

        /**
         * 查询同级分类列表
         */
        $goodsclass_model = model('goodsclass');
        // 1级
        if ($gc_id_1 > 0) {
            $list_array['gc_id'] = $gc_id_1;
            $class_list = $goodsclass_model->getGoodsclass();
            if (empty($class_list) || !is_array($class_list)) {
                echo json_encode(array('done' => false, 'msg' => ''));
                die();
            }
            foreach ($class_list as $val) {
                if ($val ['gc_id'] == $gc_id_1) {
                    $list_array ['one'] .= '<li class="" onclick="selClass($(this));" data-param="{gcid:' . $val ['gc_id'] . ', deep:1, tid:' . $val ['type_id'] . '}" dstype="selClass"> <a class="classDivClick" href="javascript:void(0)"><span class="has_leaf"><i class="iconfont"></i>' . $val ['gc_name'] . '</span></a> </li>';
                } else {
                    $list_array ['one'] .= '<li class="" onclick="selClass($(this));" data-param="{gcid:' . $val ['gc_id'] . ', deep:1, tid:' . $val ['type_id'] . '}" dstype="selClass"> <a class="" href="javascript:void(0)"><span class="has_leaf"><i class="iconfont"></i>' . $val ['gc_name'] . '</span></a> </li>';
                }
            }
        }
        // 2级
        if ($gc_id_2 > 0) {
            $list_array['gc_id'] = $gc_id_2;
            $class_list = $goodsclass_model->getGoodsclass($gc_id_1, 2);
            if (empty($class_list) || !is_array($class_list)) {
                echo json_encode(array(
                    'done' => false, 'msg' => ''
                ));
                die();
            }
            foreach ($class_list as $val) {
                if ($val ['gc_id'] == $gc_id_2) {
                    $list_array ['two'] .= '<li class="" onclick="selClass($(this));" data-param="{gcid:' . $val ['gc_id'] . ', deep:2, tid:' . $val ['type_id'] . '}" dstype="selClass"> <a class="classDivClick" href="javascript:void(0)"><span class="has_leaf"><i class="iconfont"></i>' . $val ['gc_name'] . '</span></a> </li>';
                } else {
                    $list_array ['two'] .= '<li class="" onclick="selClass($(this));" data-param="{gcid:' . $val ['gc_id'] . ', deep:2, tid:' . $val ['type_id'] . '}" dstype="selClass"> <a class="" href="javascript:void(0)"><span class="has_leaf"><i class="iconfont"></i>' . $val ['gc_name'] . '</span></a> </li>';
                }
            }
        }
        // 3级
        if ($gc_id_3 > 0) {
            $list_array['gc_id'] = $gc_id_3;
            $class_list = $goodsclass_model->getGoodsclass($gc_id_2, 3);
            if (empty($class_list) || !is_array($class_list)) {
                echo json_encode(array(
                    'done' => false, 'msg' => ''
                ));
                die();
            }
            foreach ($class_list as $val) {
                if ($val ['gc_id'] == $gc_id_3) {
                    $list_array ['three'] .= '<li class="" onclick="selClass($(this));" data-param="{gcid:' . $val ['gc_id'] . ', deep:3, tid:' . $val ['type_id'] . '}" dstype="selClass"> <a class="classDivClick" href="javascript:void(0)"><span class="has_leaf"><i class="iconfont"></i>' . $val ['gc_name'] . '</span></a> </li>';
                } else {
                    $list_array ['three'] .= '<li class="" onclick="selClass($(this));" data-param="{gcid:' . $val ['gc_id'] . ', deep:3, tid:' . $val ['type_id'] . '}" dstype="selClass"> <a class="" href="javascript:void(0)"><span class="has_leaf"><i class="iconfont"></i>' . $val ['gc_name'] . '</span></a> </li>';
                }
            }
        }
        echo json_encode($list_array);
        die();
    }

    /**
     * AJAX添加商品规格值
     */
    public function ajax_add_spec() {
        $name = trim(input('get.name'));
        $gc_id = intval(input('get.gc_id'));
        $sp_id = intval(input('get.sp_id'));
        if ($name == '' || $gc_id <= 0 || $sp_id <= 0) {
            echo json_encode(array('done' => false));
            die();
        }
        $insert = array(
            'spvalue_name' => $name,
            'sp_id' => $sp_id,
            'gc_id' => $gc_id,
            'spvalue_color' => null,
            'spvalue_sort' => 0,
        );
        $value_id = model('spec')->addSpecvalue($insert);
        if ($value_id) {
            echo json_encode(array('done' => true, 'value_id' => $value_id));
            die();
        } else {
            echo json_encode(array('done' => false));
            die();
        }
    }

    /**
     * AJAX查询品牌
     */
    public function ajax_get_brand() {
        $type_id = intval(input('tid'));
        $initial = trim(input('letter'));
        $keyword = trim(input('keyword'));
        $type = trim(input('type'));
        if (!in_array($type, array(
                    'letter', 'keyword'
                )) || ($type == 'letter' && empty($initial)) || ($type == 'keyword' && empty($keyword))) {
            echo json_encode(array());
            die();
        }

        // 实例化模型
        $type_model = model('type');
        $where = array();
        $where[] = array('type_id', '=', $type_id);
        // 验证类型是否关联品牌
        $count = $type_model->getTypebrandCount($where);
        if ($type == 'letter') {
            switch ($initial) {
                case 'all':
                    break;
                case '0-9':
                    $where[] = array('brand_initial', 'in', array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9));
                    break;
                default:
                    $where[] = array('brand_initial', '=', $initial);
                    break;
            }
        } else {
            $where[] = array('brand_name|brand_initial', 'like', '%' . $keyword . '%');
        }
        if ($count > 0) {
            $brand_array = $type_model->typeRelatedJoinList($where, 'brand', 'brand.brand_id,brand.brand_name,brand.brand_initial');
        } else {
            unset($where['type_id']);
            $brand_array = model('brand')->getBrandPassedList($where, 'brand_id,brand_name,brand_initial', 0, 'brand_initial asc, brand_sort asc');
        }
        echo json_encode($brand_array);
        die();
    }
}

?>
