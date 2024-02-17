<?php

namespace app\admin\controller;
use think\facade\View;
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
class TaobaoImport extends AdminControl {

    public function initialize() {
        parent::initialize();
        error_reporting(E_ERROR | E_WARNING);
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/goods.lang.php');
    }

    public function index() {
        if (!request()->isPost()) {
            /**
             * 获取商品分类
             */
            $gc = model('goodsclass');
            $gc_list = $gc->getGoodsClass();
            View::assign('gc_list', $gc_list);



            if (input('get.step') != '') {
                View::assign('step', input('get.step'));
            } else {
                View::assign('step', '1');
            }
        } else {
            $file = $_FILES['csv'];
            /**
             * 上传文件存在判断
             */
            if (empty($file['name'])) {
                $this->error(lang('goods_import_choose_file'));
            }
            /**
             * 文件来源判定
             */
            if (!is_uploaded_file($file['tmp_name'])) {
                $this->error(lang('goods_import_unknown_file'));
            }
            /**
             * 文件类型判定
             */
            $file_name_array = explode('.', $file['name']);
            if ($file_name_array[count($file_name_array) - 1] != 'csv') {
                $this->error(lang('goods_import_wrong_type') . $file_name_array[count($file_name_array) - 1]);
            }
            /**
             * 文件大小判定
             */
            if ($file['size'] > intval(ini_get('upload_max_filesize')) * 1024 * 1024) {
                $this->error(lang('goods_import_size_limit'));
            }
            /**
             * 商品分类判定
             */
            if (empty(input('post.gc_id'))) {
                $this->error(lang('goods_import_wrong_class'));
            }
            $gc = model('goodsclass');
            $gc_row = $gc->getGoodsClassLineForTag(input('post.gc_id'));

            if (!is_array($gc_row) or count($gc_row) == 0) {
                $this->error(lang('goods_import_wrong_class1'));
            }
            $gc_sub_list = $gc->getGoodsClassList(array('gc_parent_id' => intval(input('post.gc_id'))));
            if (is_array($gc_sub_list) and count($gc_sub_list) > 0) {
                $this->error(lang('goods_import_wrong_class2'));
            }



            /**
             * 上传文件的字符编码转换
             */
            $csv_string = $this->unicodeToUtf8(file_get_contents($file['tmp_name']));

            /* 兼容淘宝助理5 start */
            $csv_array = explode("\tsyncStatus", $csv_string, 2);
            if (count($csv_array) == 2) {
                $csv_string = $csv_array[1];
            }
            /* 兼容淘宝助理5 end */

            /**
             * 将文件转换为二维数组形式的商品数据
             */
            $records = $this->parse_taobao_csv($csv_string);
            if ($records === false) {
                $this->error(lang('goods_import_wrong_column'));
            }

            /**
             * 转码
             */
            if (strtoupper(CHARSET) == 'GBK') {
                $records = $this->getGBK($records);
            }


            $model_goodsclass = model('goodsclass');
            $model_store_goods = model('goods');
           


            /* 商品数判断 */
            $remain_num = -1;


            /**
             * 循环添加数据
             */
            $str = '';
            if (is_array($records) and count($records) > 0) {
                foreach ($records as $k => $record) {
      

                    if (is_array($record['goods_image'])) {
                        $str .= implode(',', $record['goods_image']);
                        $str .= "\r\n";
                    } else {
                        $str .= $record['goods_image'] . "\r\n";
                    }
                    //file_put_contents('image.txt',$str,FILE_APPEND);
                    $pic_array = $this->get_goods_image($record['goods_image']);

                    if (empty($record['goods_name']))
                        continue;
                    $param = array();
                    $param['goods_name'] = $record['goods_name'];
                    $param['gc_id'] = intval(input('post.gc_id'));
                    $param['gc_id_1'] = intval(input('post.cls_1'));
                    $param['gc_id_2'] = intval(input('post.cls_2'));
                    $param['gc_id_3'] = intval(input('post.cls_3'));
                    $param['gc_name'] = input('post.cate_name');
                    $param['spec_name'] = 'N;';
                    $param['spec_value'] = 'N;';
                    $param['type_id'] = '0';
                    $param['goods_image'] = $pic_array['goods_image'][0];
                    $param['goods_marketprice'] = $record['goods_store_price'];
                    $param['goods_costprice'] = $record['goods_store_price'];
                    $param['goods_discount'] = 1;
                    $param['goods_price'] = $record['goods_store_price'];
                    //$param['goods_show']			= '1';
                    $param['goods_commend'] = $record['goods_commend'];
                    $param['goods_addtime'] = TIMESTAMP;
                    $param['goods_shelftime'] = TIMESTAMP;//上架时间
                    $param['goods_attr'] = '';
                    $param['goods_body'] = $record['goods_body'];
                    $param['goods_state'] = '0';
                    $param['areaid_1'] = intval(input('post.province_id'));
                    $param['areaid_2'] = intval(input('post.city_id'));
                    $param['goods_serial'] = $record['goods_serial'];
                    $goods_id = $model_store_goods->addGoodsCommon($param);

                    //添加库存
                    $param = array();
                    $param['goods_commonid'] = $goods_id;
                    $param['goods_name'] = $record['goods_name'];
                    $param['gc_id'] = intval(input('post.gc_id'));
                    $param['goods_image'] = $pic_array['goods_image'][0];
                    $param['goods_marketprice'] = $record['goods_store_price'];
                    $param['goods_price'] = $record['goods_store_price'];
                    //$param['goods_show']			= '1';
                    $param['goods_commend'] = $record['goods_commend'];
                    $param['goods_addtime'] = TIMESTAMP;
                    $param['goods_edittime'] = TIMESTAMP;
                    $param['goods_state'] = '0';
                    $param['areaid_1'] = intval(input('post.province_id'));
                    $param['areaid_2'] = intval(input('post.city_id'));
                    $param['goods_storage'] = $record['spec_goods_storage'];
                    $param['goods_serial'] = $record['goods_serial'];
                    $param['gc_id_1'] = intval(input('post.cls_1'));
                    $param['gc_id_2'] = intval(input('post.cls_2'));
                    $param['gc_id_3'] = intval(input('post.cls_3'));
                    $param['goods_promotion_price'] = $param['goods_price'];
                    $param['goods_spec'] = 'N;';

                    $goods_id1 = $model_store_goods->addGoods($param);

                    //规格导入
                    // 更新常用分类信息
                    $goods_class = $model_goodsclass->getGoodsClassLineForTag(input('post.gc_id'));

                    $goods_id_str .= "," . $goods_id;
                    if ($goods_id) {
                        /**
                         * 添加商品的店铺分类表
                         */
                        /**
                         * 商品多图的添加
                         */
                        if (!empty($pic_array['goods_image']) && is_array($pic_array['goods_image'])) {
                            $insert_array = array();
                            foreach ($pic_array['goods_image'] as $pic) {
                                if ($pic == '')
                                    continue;
                                $param = array();
                                $param['goodsimage_url'] = $pic;



                                $param['goods_commonid'] = $goods_id;
                                $insert_array[] = $param;
                            }
                            //$rs = model('upload');
                            //$rs = $rs->add($param);
                            $rs = $model_store_goods->addGoodsImagesAll($insert_array);
                        }
                    }
                }
                if ($goods_id_str != "") {
                    View::assign('goods_id_str', substr($goods_id_str, 1, strlen($goods_id_str)));
                }
            }
            View::assign('step', '4');
        }

        /**
         * 相册分类
         */
        $model_album = model('album');
        $param = array();
        $aclass_info = $model_album->getAlbumclassList($param);
        View::assign('aclass_info', $aclass_info);


        /* 设置卖家当前菜单 */
        $this->setAdminCurItem('taobao_import');
        return View::fetch('index');
    }

    public function import_image() {
        View::assign('session_id', $this->app->session->getId());
        return View::fetch('import_image');
    }

    public function upload() {
        $time=TIMESTAMP;
        if (isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
            $path = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_GOODS . DIRECTORY_SEPARATOR . date('Ymd',$time); //取得上传图片的绝对路径
            $SID = "";
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }//如果目录不存在，则创建
            $path = realpath($path) . '/';
            $filetype = '.jpg'; //后缀
            $upload_file = $_FILES['Filedata']; //上传的数据
            $file_info = pathinfo($upload_file['name']); //图片数组
            $sourimgname = $file_info['filename']; //不带后缀文件名，入库
            $rukuimgname = $SID . $sourimgname . $filetype; //带后缀入库的名字
            $save_name = date('YmdHis',$time) . rand(10000, 99999);
            $rukuimgname = $SID . $save_name . $filetype; //带后缀入库的名字
            $save = $path . $rukuimgname; //将要保存到服务器的路径
            $name = $_FILES['Filedata']['tmp_name']; //上传到服务器的临时文件
            //echo $save;
            if (!move_uploaded_file($name, $save)) {

                exit;
            }
            //生成不同规格大小的图片
            $fz60 = $path . $SID . $save_name . '_60.jpg';
            $fz240 = $path . $SID . $save_name . '_240.jpg';
            $fz360 = $path . $SID . $save_name . '_360.jpg';
            $fz1280 = $path . $SID . $save_name . '_1280.jpg';
            if (copy($save, $fz60)) {
                //更改图片大小
                $this->resizeimage($fz60, 60, 60, $fz60);
            }
            if (copy($save, $fz240)) {
                //更改图片大小
                $this->resizeimage($fz240, 240, 240, $fz240);
            }
            if (copy($save, $fz360)) {
                //更改图片大小
                $this->resizeimage($fz360, 360, 360, $fz360);
            }
            if (copy($save, $fz1280)) {
                //更改图片大小
                $this->resizeimage($fz1280, 1280, 1280, $fz1280);
            }
            Db::startTrans();
            try {
                //更新goods表
                $result = model('goods')->editGoods(array('goods_image' => $rukuimgname), array('goods_image' => $sourimgname));
                if (!$result) {
//                    throw new \think\Exception('更新goods表失败', 10006);
                }
                //更新goodscommon表
                $temp=Db::name('goodscommon')->where(array(array('goods_body','like','%'.$sourimgname.'%')))->order('goods_commonid desc')->find();
                if($temp){
                  $temp['goods_body']=preg_replace('/"([^"]+)'.$sourimgname.'([^"]+)"/i','"'.UPLOAD_SITE_URL."/home/store/goods/".date('Ymd',$time)."/".$rukuimgname.'"',$temp['goods_body']);
                  model('goods')->editGoodsCommon(array('goods_body' => $temp['goods_body']), array('goods_commonid' => $temp['goods_commonid']));
                }
                $result = model('goods')->editGoodsCommon(array('goods_image' => $rukuimgname), array('goods_image' => $sourimgname));
                if (!$result) {
//                    throw new \think\Exception('更新goodscommon表失败', 10006);
                }
                //更新goodsimages表
                $result = model('goods')->editGoodsImages(array('goodsimage_url' => $rukuimgname), array('goodsimage_url' => $sourimgname));
                if (!$result) {
//                    throw new \think\Exception('更新goodsimages表失败', 10006);
                }
                //插入albumpic表
                $insert_array = array();
                $insert_array['apic_name'] = $rukuimgname;
                $insert_array['apic_tag'] = '';
                $insert_array['aclass_id'] = 1;
                $insert_array['apic_cover'] = $rukuimgname;
                $insert_array['apic_size'] = '';
                $insert_array['apic_spec'] = '';
                $insert_array['apic_uploadtime'] = $time;
                $result = model('album')->addAlbumpic($insert_array);
                if (!$result) {
//                    throw new \think\Exception('插入albumpic表失败', 10006);
                }
            } catch (\Exception $e) {
                Db::rollback();
                throw new \think\Exception($e->getMessage(), 10006);
            }
            Db::commit();
        }
    }

    /*
     * 图片缩略图 
     */

    private function resizeimage($srcfile, $ratew = '', $rateh = '', $filename = "") {
        $size = getimagesize($srcfile);
        switch ($size[2]) {
            case 1:
                $img = imagecreatefromgif($srcfile);
                break;
            case 2:
                $img = imagecreatefromjpeg($srcfile); //从源文件建立一个新图片
                break;
            case 3:
                $img = imagecreatefrompng($srcfile);
                break;
            default:
                exit;
        }
        //源图片的宽度和高度
        $srcw = imagesx($img);
        echo '源文件的宽度' . $srcw . '<br />';
        $srch = imagesy($img);
        echo '源文件的高度' . $srch . '<br />';
        //目的图片的宽度和高度
        $dstw = $ratew;
        $dsth = $rateh;
        //新建一个真彩色图像
        echo '新图片的宽度' . $dstw . '高度' . $dsth . '<br />';
        $im = imagecreatetruecolor($dstw, $dsth);
        $black = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, $dstw, $dsth, $black);
        imagecopyresized($im, $img, 0, 0, 0, 0, $dstw, $dsth, $srcw, $srch);
        // 以 JPEG 格式将图像输出到浏览器或文件
        if ($filename) {
            //图片保存输出
            imagejpeg($im, $filename, 100);
        }
        //释放图片
        imagedestroy($im);
        imagedestroy($img);
    }

    /**
     * 得到数组变量的GBK编码
     *
     * @param array $key 数组
     * @return array 数组类型的返回结果
     */
    private function getGBK($key) {
        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK' && !empty($key)) {
            if (is_array($key)) {
                $result = var_export($key, true); //变为字符串
                $result = iconv('UTF-8', 'GBK', $result);
                eval("\$result = $result;"); //转换回数组
            } else {
                $result = iconv('UTF-8', 'GBK', $key);
            }
        }
        return $result;
    }

    /**
     * unicode转为utf8
     * @param string $str 待转的字符串
     * @return string
     */
    function unicodeToUtf8($str, $order = "little") {
        $utf8string = "";
        $n = strlen($str);
        for ($i = 0; $i < $n; $i++) {
            if ($order == "little") {
                $val = str_pad(dechex(ord($str[$i + 1])), 2, 0, STR_PAD_LEFT) .
                        str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT);
            } else {
                $val = str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT) .
                        str_pad(dechex(ord($str[$i + 1])), 2, 0, STR_PAD_LEFT);
            }
            $val = intval($val, 16); // 由于上次的.连接，导致$val变为字符串，这里得转回来。
            $i++; // 两个字节表示一个unicode字符。
            $c = "";
            if ($val < 0x7F) { // 0000-007F
                $c .= chr($val);
            } elseif ($val < 0x800) { // 0080-07F0
                $c .= chr(0xC0 | ($val / 64));
                $c .= chr(0x80 | ($val % 64));
            } else { // 0800-FFFF
                $c .= chr(0xE0 | (($val / 64) / 64));
                $c .= chr(0x80 | (($val / 64) % 64));
                $c .= chr(0x80 | ($val % 64));
            }
            $utf8string .= $c;
        }
        /* 去除bom标记 才能使内置的iconv函数正确转换 */
        if (ord(substr($utf8string, 0, 1)) == 0xEF && ord(substr($utf8string, 1, 2)) == 0xBB && ord(substr($utf8string, 2, 1)) == 0xBF) {
            $utf8string = substr($utf8string, 3);
        }
        return $utf8string;
    }

    private function get_goods_image($pic_string) {
        if ($pic_string == '') {
            return false;
        }
        $pic_array = explode(';', $pic_string);
        if (!empty($pic_array) && is_array($pic_array)) {
            $array = array();
            $goods_image = array();
            $multi_image = array();
            $i = 0;
            foreach ($pic_array as $v) {
                if ($v != '') {
                    $line = explode(':', $v); //[0] 文件名tbi [2] 排序
                    $goods_image[] = $line[0];
                }
            }
            $array['goods_image'] = array_unique($goods_image);
            $str = implode(',', $array['goods_image']) . "\r\n";
            return $array;
        } else {
            return false;
        }
    }

    /**
     * 淘宝数据字段名
     *
     * @return array
     */
    private function taobao_fields() {
        return array(
            'goods_name' => '宝贝名称',
            'cid' => '宝贝类目',
            'goods_form' => '新旧程度',
            'goods_store_price' => '宝贝价格',
            'spec_goods_storage' => '宝贝数量',
            'goods_indate' => '有效期',
            'goods_transfee_charge' => '运费承担',
            'py_price' => '平邮',
            'es_price' => 'EMS',
            'kd_price' => '快递',
            //'goods_show'		=> '放入仓库',
            'spec' => '销售属性别名',
            'goods_commend' => '橱窗推荐',
            'goods_body' => '宝贝描述',
            'goods_image' => '新图片',
            'goods_serial' => '商家编码'
        );
    }

    /**
     * 每个字段所在CSV中的列序号，从0开始算 
     *
     * @param array $title_arr
     * @param array $import_fields
     * @return array
     */
    private function taobao_fields_cols($title_arr, $import_fields) {
        $fields_cols = array();
        foreach ($import_fields as $k => $field) {
            $pos = array_search($field, $title_arr);
            if ($pos !== false) {
                $fields_cols[$k] = $pos;
            }
        }
        return $fields_cols;
    }

    /**
     * 解析淘宝助理CSV数据
     *
     * @param string $csv_string
     * @return string
     */
    private function parse_taobao_csv($csv_string) {


        //防止乱码
        $scount = strpos($csv_string, "宝贝名称");
        $csv_string = substr($csv_string, $scount);
        /* 定义CSV文件中几个标识性的字符的ascii码值 */
        define('ORD_SPACE', 32); // 空格
        define('ORD_QUOTE', 34); // 双引号
        define('ORD_TAB', 9); // 制表符
        define('ORD_N', 10); // 换行\n
        define('ORD_R', 13); // 换行\r

        /* 字段信息 */
        $import_fields = $this->taobao_fields(); // 需要导入的字段在CSV中显示的名称
        $fields_cols = array(); // 每个字段所在CSV中的列序号，从0开始算
        $csv_col_num = 0; // csv文件总列数

        $pos = 0; // 当前的字符偏移量
        $status = 0; // 0标题未开始 1标题已开始
        $title_pos = 0; // 标题开始位置
        $records = array(); // 记录集
        $field = 0; // 字段号
        $start_pos = 0; // 字段开始位置
        $field_status = 0; // 0未开始 1双引号字段开始 2无双引号字段开始
        $line = 0; // 数据行号
        while ($pos < strlen($csv_string)) {
            $t = ord($csv_string[$pos]); // 每个UTF-8字符第一个字节单元的ascii码
            $next = ord($csv_string[$pos + 1]);
            $next2 = ord($csv_string[$pos + 2]);
            $next3 = ord($csv_string[$pos + 3]);

            if ($status == 0 && !in_array($t, array(ORD_SPACE, ORD_TAB, ORD_N, ORD_R))) {
                $status = 1;
                $title_pos = $pos;
            }

            if ($status == 1) {
                if ($field_status == 0 && $t == ORD_N) {
                    static $flag = null;
                    if ($flag === null) {
                        $title_str = substr($csv_string, $title_pos, $pos - $title_pos);
                        $title_arr = explode("\t", trim($title_str));
                        $fields_cols = $this->taobao_fields_cols($title_arr, $import_fields);

                        if (count($fields_cols) != count($import_fields)) {
                            return false;
                        }
                        $csv_col_num = count($title_arr); // csv总列数
                        $flag = 1;
                    }

                    if ($next == ORD_QUOTE) {
                        $field_status = 1; // 引号数据单元开始
                        $start_pos = $pos = $pos + 2; // 数据单元开始位置(相对\n偏移+2)
                    } else {
                        $field_status = 2; // 无引号数据单元开始
                        $start_pos = $pos = $pos + 1; // 数据单元开始位置(相对\n偏移+1)
                    }
                    continue;
                }

                if ($field_status == 1 && $t == ORD_QUOTE && in_array($next, array(ORD_N, ORD_R, ORD_TAB))) { // 引号+换行 或 引号+\t
                    $records[$line][$field] = addslashes(substr($csv_string, $start_pos, $pos - $start_pos));
                    $field++;
                    if ($field == $csv_col_num) {
                        $line++;
                        $field = 0;
                        $field_status = 0;
                        continue;
                    }
                    if (($next == ORD_N && $next2 == ORD_QUOTE) || ($next == ORD_TAB && $next2 == ORD_QUOTE) || ($next == ORD_R && $next2 == ORD_QUOTE)) {
                        $field_status = 1;
                        $start_pos = $pos = $pos + 3;
                        continue;
                    }
                    if (($next == ORD_N && $next2 != ORD_QUOTE) || ($next == ORD_TAB && $next2 != ORD_QUOTE) || ($next == ORD_R && $next2 != ORD_QUOTE)) {
                        $field_status = 2;
                        $start_pos = $pos = $pos + 2;
                        continue;
                    }
                    if ($next == ORD_R && $next2 == ORD_N && $next3 == ORD_QUOTE) {
                        $field_status = 1;
                        $start_pos = $pos = $pos + 4;
                        continue;
                    }
                    if ($next == ORD_R && $next2 == ORD_N && $next3 != ORD_QUOTE) {
                        $field_status = 2;
                        $start_pos = $pos = $pos + 3;
                        continue;
                    }
                }

                if ($field_status == 2 && in_array($t, array(ORD_N, ORD_R, ORD_TAB))) { // 换行 或 \t
                    $records[$line][$field] = addslashes(substr($csv_string, $start_pos, $pos - $start_pos));
                    $field++;
                    if ($field == $csv_col_num) {
                        $line++;
                        $field = 0;
                        $field_status = 0;
                        continue;
                    }
                    if (($t == ORD_N && $next == ORD_QUOTE) || ($t == ORD_TAB && $next == ORD_QUOTE) || ($t == ORD_R && $next == ORD_QUOTE)) {
                        $field_status = 1;
                        $start_pos = $pos = $pos + 2;
                        continue;
                    }
                    if (($t == ORD_N && $next != ORD_QUOTE) || ($t == ORD_TAB && $next != ORD_QUOTE) || ($t == ORD_R && $next != ORD_QUOTE)) {
                        $field_status = 2;
                        $start_pos = $pos = $pos + 1;
                        continue;
                    }
                    if ($t == ORD_R && $next == ORD_N && $next2 == ORD_QUOTE) {
                        $field_status = 1;
                        $start_pos = $pos = $pos + 3;
                        continue;
                    }
                    if ($t == ORD_R && $next == ORD_N && $next2 != ORD_QUOTE) {
                        $field_status = 2;
                        $start_pos = $pos = $pos + 2;
                        continue;
                    }
                }
            }

            if ($t > 0 && $t <= 127) {
                $pos++;
            } elseif (192 <= $t && $t <= 223) {
                $pos += 2;
            } elseif (224 <= $t && $t <= 239) {
                $pos += 3;
            } elseif (240 <= $t && $t <= 247) {
                $pos += 4;
            } elseif (248 <= $t && $t <= 251) {
                $pos += 5;
            } elseif ($t == 252 || $t == 253) {
                $pos += 6;
            } else {
                $pos++;
            }
        }
        $return = array();
        foreach ($records as $key => $record) {
            foreach ($record as $k => $col) {
                $col = trim($col); // 去掉数据两端的空格
                /* 对字段数据进行分别处理 */
                switch ($k) {
                    case $fields_cols['goods_body'] : $return[$key]['goods_body'] = str_replace('\"\"', '"', $col);
                        $return[$key]['goods_body'] = str_replace(".png", '.jpg', $return[$key]['goods_body']);
                        $reg = '/<img (.*?)+src=[\'"](.*?)[\'"]/i';
                        preg_match_all( $reg , $return[$key]['goods_body'] , $returngoodsbody);
                        $returngoodsbodyurl = $returngoodsbody[2];
                        $url = UPLOAD_SITE_URL."/home/store/goods/";
                        $imgurl = substr($returngoodsbodyurl[0],0,strrpos($returngoodsbodyurl[0],"/"))."/";
                        $return[$key]['goods_body'] = str_replace($imgurl, $url, $return[$key]['goods_body']);
                        break;
                    case $fields_cols['goods_image'] : $return[$key]['goods_image'] = trim($col, '"');
                        break;
                    //case $fields_cols['goods_show']		: $return[$key]['goods_show'] = $col == 1 ? 0 : 1; break;
                    case $fields_cols['goods_name'] : $return[$key]['goods_name'] = $col;
                        break;
                    case $fields_cols['spec_goods_storage'] : $return[$key]['spec_goods_storage'] = $col;
                        break;
                    case $fields_cols['goods_store_price']: $return[$key]['goods_store_price'] = $col;
                        break;
                    case $fields_cols['goods_commend'] : $return[$key]['goods_commend'] = $col;
                        break;
                    case $fields_cols['spec'] : $return[$key]['spec'] = $col;
                        break;
                    case $fields_cols['sale_attr'] : $return[$key]['sale_attr'] = $col;
                        break;
                    case $fields_cols['goods_form'] : $return[$key]['goods_form'] = $col;
                        break;
                    case $fields_cols['goods_transfee_charge'] : $return[$key]['goods_transfee_charge'] = $col;
                        break;
                    case $fields_cols['py_price'] : $return[$key]['py_price'] = $col;
                        break;
                    case $fields_cols['es_price'] : $return[$key]['es_price'] = $col;
                        break;
                    case $fields_cols['kd_price'] : $return[$key]['kd_price'] = $col;
                        break;
                    case $fields_cols['goods_serial'] : $return[$key]['goods_serial'] = $col;
                        break;

//					case $fields_cols['goods_indate']	: $return[$key]['goods_indate'] = $col; break;
                }
            }
        }
        return $return;
    }

}
