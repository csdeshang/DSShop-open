<?php

namespace app\home\controller;

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
class TaobaoExport extends BaseGoods {

    public function initialize() {
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/taobao_export.lang.php');
        parent::initialize();
    }

    public function export_image(){
        $goods_id = intval(input('param.goods_id'));
        if (!$goods_id) {
            $this->error(lang('param_error'));
        }

        // 商品详细信息
        $goods_model = model('goods');
        $goods_detail = $goods_model->getGoodsDetail($goods_id);
        if (!$goods_detail || empty($goods_detail['goods_info'])) {
            $this->error(lang('goods_index_no_goods'));
        }
        $goods_info = $goods_detail['goods_info'];
        $exportPath = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_TAOBAO . DIRECTORY_SEPARATOR . 'goods_image_' . $goods_info['goods_id'];
        if (!file_exists($exportPath.'.zip')) {
            if (!is_dir($exportPath)) {
                mkdir($exportPath, 0777, true);
            }//如果目录不存在，则创建
            //图片
            $image_path_1=iconv("UTF-8", "GBK", $exportPath . DIRECTORY_SEPARATOR . '主图');
            if (!is_dir($image_path_1)) {
                mkdir($image_path_1, 0777);
            }//如果目录不存在，则创建
            $image_path_2=iconv("UTF-8", "GBK", $exportPath . DIRECTORY_SEPARATOR . '详情图');
            if (!is_dir($image_path_2)) {
                mkdir($image_path_2, 0777);
            }//如果目录不存在，则创建
            foreach ($goods_detail['goods_image'] as $key_gi => $val_gi) {
                @file_put_contents($image_path_1 . DIRECTORY_SEPARATOR . basename($val_gi[2]), file_get_contents($val_gi[2]));
            }
            $goods_body=htmlspecialchars_decode($goods_info['goods_body']);
            if(preg_match_all("/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i",$goods_body,$matches)){
                foreach($matches[2] as $match){
                    @file_put_contents($image_path_2 . DIRECTORY_SEPARATOR . basename($match), file_get_contents($match));
                }
            }
            
            
            
            $zip = new \ZipArchive();
            if ($zip->open($exportPath . '.zip', \ZIPARCHIVE::OVERWRITE | \ZIPARCHIVE::CREATE) !== TRUE) {
                $this->error(lang('zip_create_fail'));
            }
            $this->createZip(opendir($exportPath), $zip, $exportPath);
            $zip->close();

            //删除
            $this->deldir($exportPath);
        }
        header('Content-Type:text/html;charset=utf-8');
        header('Content-disposition:attachment;filename='.'goods_image_' . $goods_info['goods_id'].'.zip');
        $filesize = filesize($exportPath.'.zip');
        readfile($exportPath.'.zip');
        header('Content-length:'.$filesize);
        exit;
    }

    public function export_csv() {
        $goods_id = intval(input('param.goods_id'));
        if (!$goods_id) {
            $this->error(lang('param_error'));
        }

        // 商品详细信息
        $goods_model = model('goods');
        $goods_detail = $goods_model->getGoodsDetail($goods_id);
        if (!$goods_detail || empty($goods_detail['goods_info'])) {
            $this->error(lang('goods_index_no_goods'));
        }
        $goods_info = $goods_detail['goods_info'];


        $exportPath = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_TAOBAO . DIRECTORY_SEPARATOR . 'goods_csv_' . $goods_info['goods_id'];
        if (!file_exists($exportPath.'.zip')) {
            if (!is_dir($exportPath)) {
                mkdir($exportPath, 0777, true);
            }//如果目录不存在，则创建


            $str = "version 1.00
title	cid	seller_cids	stuff_status	location_state	location_city	item_type	price	auction_increment	num	valid_thru	freight_payer	post_fee	ems_fee	express_fee	has_invoice	has_warranty	approve_status	has_showcase	list_time	description	cateProps	postage_id	has_discount	modified	upload_fail_msg	picture_status	auction_point	picture	video	skuProps	inputPids	inputValues	outer_id	propAlias	auto_fill	num_id	local_cid	navigation_type	user_name	syncStatus	is_lighting_consigment	is_xinpin	foodparame	features	buyareatype	global_stock_type	global_stock_country	sub_stock_type	item_size	item_weight	sell_promise	custom_design_flag	wireless_desc	barcode	sku_barcode	newprepay	subtitle	cpv_memo	input_custom_cpv	qualification	add_qualification	o2o_bind_service	tmall_extend	product_combine	tmall_item_prop_combine	taoschema_extend
宝贝名称	宝贝类目	店铺类目	新旧程度	省	城市	出售方式	宝贝价格	加价幅度	宝贝数量	有效期	运费承担	平邮	EMS	快递	发票	保修	放入仓库	橱窗推荐	开始时间	宝贝描述	宝贝属性	邮费模版ID	会员打折	修改时间	上传状态	图片状态	返点比例	新图片	视频	销售属性组合	用户输入ID串	用户输入名-值对	商家编码	销售属性别名	代充类型	数字ID	本地ID	宝贝分类	用户名称	宝贝状态	闪电发货	新品	食品专项	尺码库	采购地	库存类型	国家地区	库存计数	物流体积	物流重量	退换货承诺	定制工具	无线详情	商品条形码	sku 条形码	7天退货	宝贝卖点	属性值备注	自定义属性值	商品资质	增加商品资质	关联线下服务	tmall扩展字段	产品组合	tmall属性组合	taoschema扩展字段
";
            
            //图片
            if (!is_dir($exportPath . DIRECTORY_SEPARATOR . 'goods_' . $goods_info['goods_id'])) {
                mkdir($exportPath . DIRECTORY_SEPARATOR . 'goods_' . $goods_info['goods_id'], 0777);
            }//如果目录不存在，则创建
            $goods_image = "";
            foreach ($goods_detail['goods_image'] as $key_gi => $val_gi) {
                $temp = explode('.', basename($val_gi[2]));
                @file_put_contents($exportPath . DIRECTORY_SEPARATOR . 'goods_' . $goods_info['goods_id'] . DIRECTORY_SEPARATOR . $temp[0] . '.tbi', file_get_contents($val_gi[2]));
                $goods_image .= $temp[0] . ":1:" . $key_gi . "|;";
            }
            $str .= $goods_info['goods_name'] . "	" . //宝贝名称
                    "" . "	" . //宝贝类目
                    "" . "	" . //店铺类目
                    "1" . "	" . //新旧程度
                    "" . "	" . //省
                    "" . "	" . //城市
                    "1" . "	" . //出售方式
                    $goods_info['goods_price'] . "	" . //宝贝价格
                    "" . "	" . //加价幅度
                    $goods_info['goods_storage'] . "	" . //宝贝数量
                    "0" . "	" . //有效期
                    "2" . "	" . //运费承担
                    "5" . "	" . //平邮
                    "20" . "	" . //EMS
                    "15" . "	" . //快递
                    "0" . "	" . //发票
                    "0" . "	" . //保修
                    "2" . "	" . //放入仓库
                    "0" . "	" . //橱窗推荐
                    "" . "	" . //开始时间
                    htmlspecialchars_decode($goods_info['goods_body']) . "	" . //宝贝描述
                    "" . "	" . //宝贝属性	
                    "" . "	" . //邮费模版ID	
                    "" . "	" . //会员打折	
                    "" . "	" . //修改时间	
                    "" . "	" . //上传状态	
                    "" . "	" . //图片状态	
                    "0" . "	" . //返点比例	
                    $goods_image . "	" . //新图片	
                    "" . "	" . //视频	
                    "" . "	" . //销售属性组合	
                    "" . "	" . //用户输入ID串	
                    "" . "	" . //用户输入名-值对	
                    $goods_info['goods_serial'] . "	" . //商家编码	
                    "" . "	" . //销售属性别名	
                    "" . "	" . //代充类型	
                    "0" . "	" . //数字ID	
                    "-1" . "	" . //本地ID	
                    "1" . "	" . //宝贝分类	
                    "" . "	" . //用户名称	
                    "" . "	" . //宝贝状态	
                    "227" . "	" . //闪电发货	
                    "224" . "	" . //新品	
                    "" . "	" . //食品专项	
                    "" . "	" . //尺码库	
                    "0" . "	" . //采购地	
                    "-1" . "	" . //库存类型	
                    "" . "	" . //国家地区	
                    "2" . "	" . //库存计数	
                    "bulk:0" . "	" . //物流体积	
                    "300" . "	" . //物流重量	
                    "0" . "	" . //退换货承诺	
                    "-1" . "	" . //定制工具	
                    "" . "	" . //无线详情	
                    "" . "	" . //商品条形码	
                    ";;;" . "	" . //sku 条形码
                    "1" . "	" . //7天退货	
                    $goods_info['goods_advword'] . "	" . //宝贝卖点	
                    "" . "	" . //属性值备注	
                    "" . "	" . //自定义属性值	
                    "" . "	" . //商品资质
                    "" . "	" . //增加商品资质
                    "" . "	" . //关联线下服务
                    "" . "	" . //tmall扩展字段
                    "" . "	" . //产品组合
                    "" . "	" . //tmall属性组合
                    "" . "	"; //taoschema扩展字段

            $str = iconv("UTF-8", "UTF-16LE", $str);
            $str = "\xFF\xFE" . $str; //添加BOM  
            file_put_contents($exportPath . DIRECTORY_SEPARATOR . 'goods_' . $goods_info['goods_id'] . '.csv', $str);
            
            
            
            
            $zip = new \ZipArchive();
            if ($zip->open($exportPath . '.zip', \ZIPARCHIVE::OVERWRITE | \ZIPARCHIVE::CREATE) !== TRUE) {
                $this->error(lang('zip_create_fail'));
            }
            $this->createZip(opendir($exportPath), $zip, $exportPath);
            $zip->close();

            //删除
            $this->deldir($exportPath);
        }
        header('Content-Type:text/html;charset=utf-8');
        header('Content-disposition:attachment;filename='.'goods_csv_' . $goods_info['goods_id'].'.zip');
        $filesize = filesize($exportPath.'.zip');
        readfile($exportPath.'.zip');
        header('Content-length:'.$filesize);
        exit;
    }

    /* 压缩多级目录 
      $openFile:目录句柄
      $zipObj:Zip对象
      $sourceAbso:源文件夹路径
     */

    private function createZip($openFile, $zipObj, $sourceAbso, $newRelat = '') {
        while (($file = readdir($openFile)) != false) {
            if ($file == "." || $file == "..")
                continue;

            /* 源目录路径(绝对路径) */
            $sourceTemp = $sourceAbso . '/' . $file;
            /* 目标目录路径(相对路径) */
            $newTemp = $newRelat == '' ? $file : $newRelat . '/' . $file;
            if (is_dir($sourceTemp)) {
                //echo '创建'.$newTemp.'文件夹<br/>';  
                $zipObj->addEmptyDir($newTemp); /* 这里注意：php只需传递一个文件夹名称路径即可 */
                $this->createZip(opendir($sourceTemp), $zipObj, $sourceTemp, $newTemp);
            }
            if (is_file($sourceTemp)) {
                //echo '创建'.$newTemp.'文件<br/>';  
                $zipObj->addFile($sourceTemp, $newTemp);
            }
        }
    }

    //删除指定文件夹以及文件夹下的所有文件
    private function deldir($dir) {
        //先删除目录下的文件：
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

}
