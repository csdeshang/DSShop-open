<?php

namespace app\api\controller;
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
 * 用户控制器
 */
class Member extends MobileMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/member_auth.lang.php');
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/goods.lang.php');
    }

    /**
     * @api {POST} api/Member/index 用户首页基本信息显示
     * @apiVersion 3.0.6
     * @apiGroup Member
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.member_info  会员信息
     * @apiSuccess {String} result.member_info.available_predeposit  可用预存款余额
     * @apiSuccess {String} result.member_info.available_rc_balance  可用充值卡余额
     * @apiSuccess {String} result.member_info.exppoints  当前等级所需经验值
     * @apiSuccess {String} result.member_info.freeze_predeposit  冻结预存款余额
     * @apiSuccess {String} result.member_info.freeze_rc_balance  冻结充值卡余额
     * @apiSuccess {String} result.member_info.inform_allow  是否允许举报(1可以/2不可以)
     * @apiSuccess {String} result.member_info.inviter_id  推荐人ID
     * @apiSuccess {String} result.member_info.inviter_state  推广员状态（0审核中1已审核2已清退）
     * @apiSuccess {String} result.member_info.is_allowtalk  会员是否有咨询和发送站内信的权限 1为开启 0为关闭
     * @apiSuccess {String} result.member_info.is_buylimit  会员是否有购买权限 1为开启 0为关闭
     * @apiSuccess {String} result.member_info.level  会员等级
     * @apiSuccess {String} result.member_info.level_name  等级名称
     * @apiSuccess {String} result.member_info.member_addtime  添加时间，Unix时间戳
     * @apiSuccess {String} result.member_info.member_areaid  地区ID
     * @apiSuccess {String} result.member_info.member_areainfo  地区信息
     * @apiSuccess {String} result.member_info.member_avatar  用户头像
     * @apiSuccess {String} result.member_info.member_birthday  生日
     * @apiSuccess {String} result.member_info.member_cityid  城市ID
     * @apiSuccess {String} result.member_info.member_email  邮箱
     * @apiSuccess {String} result.member_info.member_emailbind  已绑定邮箱 0否1是
     * @apiSuccess {String} result.member_info.member_exppoints  会员经验值
     * @apiSuccess {String} result.member_info.member_id  用户ID
     * @apiSuccess {String} result.member_info.member_login_ip  本次登录IP
     * @apiSuccess {String} result.member_info.member_loginnum  本次登录次数
     * @apiSuccess {String} result.member_info.member_logintime  本次登录时间，Unix时间戳
     * @apiSuccess {String} result.member_info.member_mobile  手机号
     * @apiSuccess {String} result.member_info.member_mobilebind  已绑定手机 0否1是
     * @apiSuccess {String} result.member_info.member_name  用户名称
     * @apiSuccess {String} result.member_info.member_old_login_ip  上次登录IP
     * @apiSuccess {String} result.member_info.member_old_logintime  上次登录时间，Unix时间戳
     * @apiSuccess {String} result.member_info.member_points  用户积分
     * @apiSuccess {String} result.member_info.member_privacy  隐私设定
     * @apiSuccess {String} result.member_info.member_provinceid  省份ID
     * @apiSuccess {String} result.member_info.member_qq  用户QQ
     * @apiSuccess {String} result.member_info.member_qqinfo  qq快捷登录信息
     * @apiSuccess {String} result.member_info.member_qqopenid  qq openid
     * @apiSuccess {String} result.member_info.member_sex  会员性别 0保密1男2女3保密
     * @apiSuccess {String} result.member_info.member_sinainfo  新浪快捷登录信息
     * @apiSuccess {String} result.member_info.member_sinaopenid  新浪openid
     * @apiSuccess {String} result.member_info.member_state  会员的开启状态 1为开启 0为关闭
     * @apiSuccess {String} result.member_info.member_truename  会员真实姓名
     * @apiSuccess {String} result.member_info.member_ww  用户旺旺
     * @apiSuccess {String} result.member_info.order_noeval_count  待评论订单数
     * @apiSuccess {String} result.member_info.order_nopay_count  待付款订单数
     * @apiSuccess {String} result.member_info.order_noreceipt_count  待收货订单数
     * @apiSuccess {String} result.member_info.order_noship_count  待发货订单数
     * @apiSuccess {String} result.member_info.order_refund_count  退款中订单数
     * @apiSuccess {String} result.member_info.store_id  店铺ID
     * @apiSuccess {String} result.member_info.voucher_count  可用优惠券数
     * @apiSuccess {String} result.member_info.member_signin_time  最后一次签到时间
     * @apiSuccess {String} result.member_info.member_signin_days_cycle  持续签到天数，每周期后清零
     * @apiSuccess {String} result.member_info.member_signin_days_total  签到总天数
     * @apiSuccess {String} result.member_info.member_signin_days_series  持续签到天数总数，非连续周期清零
     */
    public function index() {
        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
        
        unset($member_info['member_password']);
        unset($member_info['member_paypwd']);
        
        if ($member_info) {
            $member_gradeinfo = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_info = array_merge($member_info, $member_gradeinfo);
            //代金券数量
            $member_info['voucher_count'] = model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']);
            $member_info['member_avatar'] = get_member_avatar_for_id($this->member_info['member_id']);
            $member_info['member_idcard_image1_url'] = get_member_idcard_image($member_info['member_idcard_image1']);
            $member_info['member_idcard_image2_url'] = get_member_idcard_image($member_info['member_idcard_image2']);
            $member_info['member_idcard_image3_url'] = get_member_idcard_image($member_info['member_idcard_image3']);
        }
        
        //获取用户是否有推广权限
        if (config('ds_config.inviter_open')) {
            //查看是否已是分销会员
            $inviter_model = model('inviter');
            $inviter_info = $inviter_model->getInviterInfo('i.inviter_id=' . $this->member_info['member_id']);
            if(!empty($inviter_info)){
                $member_info['inviter_state'] = $inviter_info['inviter_state']; // 是否是分销员
            }
        }


        // 交易提醒
        $order_model = model('order');
        $refundreturn_model = model('refundreturn');
        $member_info['order_nopay_count'] = intval($order_model->getOrderCountByID($this->member_info['member_id'], 'NewCount'));
        $member_info['order_noreceipt_count'] = intval($order_model->getOrderCountByID($this->member_info['member_id'], 'SendCount'));
        $member_info['order_noeval_count'] = intval($order_model->getOrderCountByID($this->member_info['member_id'], 'EvalCount'));
        $member_info['order_noship_count'] = intval($order_model->getOrderCountByID($this->member_info['member_id'], 'PayCount'));
        $member_info['order_refund_count'] = intval($refundreturn_model->getRefundreturnCount(array(array('buyer_id' ,'=', $this->member_info['member_id']), array('refund_state','<>', 3))));
        
        ds_json_encode(10000, '', array('member_info' => $member_info));
    }

    public function my_asset() {
        $fields_arr = array('point', 'predepoit', 'available_rc_balance', 'voucher');
        $fields_str = trim(input('fields'));
        if ($fields_str) {
            $fields_arr = explode(',', $fields_str);
        }
        $member_info = array();
        if (in_array('point', $fields_arr)) {
            $member_info['point'] = $this->member_info['member_points'];
        }
        if (in_array('predepoit', $fields_arr)) {
            $member_info['predepoit'] = $this->member_info['available_predeposit'];
        }
        if (in_array('available_rc_balance', $fields_arr)) {
            $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        }
        if (in_array('voucher', $fields_arr)) {
            $member_info['voucher'] = model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']);
        }
        ds_json_encode(10000, '', $member_info);
    }


    /**
     * @api {POST} api/Member/information 用户基本信息显示
     * @apiVersion 3.0.6
     * @apiGroup Member
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object} result.member_info  会员信息
     * @apiSuccess {String} result.member_info.available_predeposit  可用预存款余额
     * @apiSuccess {String} result.member_info.available_rc_balance  可用充值卡余额
     * @apiSuccess {String} result.member_info.freeze_predeposit  冻结预存款余额
     * @apiSuccess {String} result.member_info.freeze_rc_balance  冻结充值卡余额
     * @apiSuccess {String} result.member_info.inform_allow  是否允许举报(1可以/2不可以)
     * @apiSuccess {String} result.member_info.inviter_id  推荐人ID
     * @apiSuccess {String} result.member_info.is_allowtalk  会员是否有咨询和发送站内信的权限 1为开启 0为关闭
     * @apiSuccess {String} result.member_info.is_buylimit  会员是否有购买权限 1为开启 0为关闭
     * @apiSuccess {String} result.member_info.member_addtime  添加时间，Unix时间戳
     * @apiSuccess {String} result.member_info.member_areaid  地区ID
     * @apiSuccess {String} result.member_info.member_areainfo  地区信息
     * @apiSuccess {String} result.member_info.member_avatar  用户头像
     * @apiSuccess {String} result.member_info.member_birthday  生日
     * @apiSuccess {String} result.member_info.member_cityid  城市ID
     * @apiSuccess {String} result.member_info.member_email  邮箱
     * @apiSuccess {String} result.member_info.member_emailbind  已绑定邮箱 0否1是
     * @apiSuccess {String} result.member_info.member_exppoints  会员经验值
     * @apiSuccess {String} result.member_info.member_id  用户ID
     * @apiSuccess {String} result.member_info.member_login_ip  本次登录IP
     * @apiSuccess {String} result.member_info.member_loginnum  登录次数
     * @apiSuccess {String} result.member_info.member_logintime  本次登录时间，Unix时间戳
     * @apiSuccess {String} result.member_info.member_mobile  手机号
     * @apiSuccess {String} result.member_info.member_mobilebind  已绑定手机 0否1是
     * @apiSuccess {String} result.member_info.member_name  用户名称
     * @apiSuccess {String} result.member_info.member_old_login_ip  上次登录IP
     * @apiSuccess {String} result.member_info.member_old_logintime  上次登录时间，Unix时间戳
     * @apiSuccess {String} result.member_info.member_points  用户积分
     * @apiSuccess {String} result.member_info.member_privacy  隐私设定
     * @apiSuccess {String} result.member_info.member_provinceid  省份ID
     * @apiSuccess {String} result.member_info.member_qq  用户QQ
     * @apiSuccess {String} result.member_info.member_qqinfo  qq快捷登录信息
     * @apiSuccess {String} result.member_info.member_qqopenid  qq openid
     * @apiSuccess {String} result.member_info.member_sex  会员性别 0保密1男2女3保密
     * @apiSuccess {String} result.member_info.member_sinainfo  新浪快捷登录信息
     * @apiSuccess {String} result.member_info.member_sinaopenid  新浪openid
     * @apiSuccess {String} result.member_info.member_state  会员的开启状态 1为开启 0为关闭
     * @apiSuccess {String} result.member_info.member_truename  会员真实姓名
     * @apiSuccess {String} result.member_info.member_ww  用户旺旺
     */
    public function information() {
        $member_model = model('member');
        $condition = array();
        $condition[] = array('member_id', '=', $this->member_info['member_id']);
        $member_info = $member_model->getMemberInfo($condition);
        $member_info['member_avatar'] = get_member_avatar_for_id($member_info['member_id']);
        $member_info['member_idcard_image1_url'] = get_member_idcard_image($member_info['member_idcard_image1']);
            $member_info['member_idcard_image2_url'] = get_member_idcard_image($member_info['member_idcard_image2']);
            $member_info['member_idcard_image3_url'] = get_member_idcard_image($member_info['member_idcard_image3']);
        unset($member_info['member_password']);
        unset($member_info['member_paypwd']);
        ds_json_encode(10000, '', array('member_info'=>$member_info));
    }

    /**
     * @api {POST} api/Member/edit_information 用户基本信息修改
     * @apiVersion 3.0.6
     * @apiGroup Member
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {String} member_nickname 真实姓名
     * @apiParam {String} member_qq 会员QQ
     * @apiParam {String} member_ww 会员旺旺
     * @apiParam {String} member_birthday 生日
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function edit_information() {
        $data = array(
            'member_nickname' => input('param.member_nickname'),
            'member_qq' => input('param.member_qq'),
            'member_ww' => input('param.member_ww'),
            'member_birthday' => strtotime(input('param.member_birthday')),
        );

        $member_model = model('member');
        $condition[] = array('member_id', '=', $this->member_info['member_id']);
        $result = $member_model->editMember($condition, $data,$this->member_info['member_id']);
        if ($result) {
            ds_json_encode(10000, '修改成功');
        } else {
            ds_json_encode(10001, '修改失败');
        }
    }

    /**
     * @api {POST} api/Member/edit_memberavatar 更新用户头像
     * @apiVersion 3.0.6
     * @apiGroup Member
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     *
     * @apiParam {File} file 用户头像
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {String} result  用户头像
     */
    public function edit_memberavatar() {
        $file = request()->file('memberavatar');
        $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_AVATAR . DIRECTORY_SEPARATOR;
        $avatar_name = 'avatar_' . $this->member_info['member_id'] . '.jpg';
        $res = ds_upload_pic(ATTACH_AVATAR, 'memberavatar', $avatar_name);
        if ($res['code']) {
            $file_name = $res['data']['file_name'];
            if(file_exists($upload_file . '/' . $avatar_name)){
            //生成缩略图
            $image = \think\Image::open($upload_file . '/' . $avatar_name);
            $image->thumb(100, 100, \think\Image::THUMB_CENTER)->save($upload_file . '/' . $avatar_name);
        }
            $member_model = model('member');
            $condition = array();
            $condition[] = array('member_id', '=', $this->member_info['member_id']);
            $result = $member_model->editMember($condition, array('member_avatar'=>$file_name), $this->member_info['member_id']);
            ds_json_encode(10000, '', get_member_avatar($file_name));
        } else {
            ds_json_encode(10001, $res['msg']);
        }
    }

    public function goods_poster() {
        $goods_id = intval(input('param.goods_id'));
        if (!$goods_id) {
            ds_json_encode(10001,lang('param_error'));
        }
        $goods_model = model('goods');
        $goods_detail = $goods_model->getGoodsDetail($goods_id);
        if (empty($goods_detail)) {
            ds_json_encode(10001,lang('goods_goods_not_exist'));
        }
        
        !is_dir(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER) && mkdir(BASE_UPLOAD_PATH . '/' . ATTACH_INVITER, 0755, true);
        $refer_qrcode_logo = BASE_UPLOAD_PATH . '/' . ATTACH_INVITER . '/' . $this->member_info['member_id'] .'_'.$goods_id. '_poster.png';
        //判断是否已生成海报
        if (!file_exists($refer_qrcode_logo) || 1) {
            
                //一个1080*1632的白板
                $back_width = 1080;
                $back_height = 1632;

                $t_logo2 = imagecreatetruecolor($back_width, $back_height);
                $background2 = imagecolorallocate($t_logo2, 246, 246, 246);
                imagefill($t_logo2, 0, 0, $background2);

                //定义边距
                $margin = 40;
                

                $font_file = PUBLIC_PATH . '/font/hyngt.ttf';
                $textcolor = imagecolorallocate($t_logo2, 0, 0, 0);
                $textcolor2 = imagecolorallocate($t_logo2, 128, 128, 128);
                $textcolor3 = imagecolorallocate($t_logo2, 244, 67, 54);


                //商品图片
                $goods_image = goods_cthumb($goods_detail['goods_info']['goods_image'],1280);
                $logo = imagecreatefromstring(file_get_contents($goods_image));
                $source_info = getimagesize($goods_image);
                imagecopyresampled($t_logo2, $logo, 0, 0, 0, 0, $back_width, $back_width, $source_info[0], $source_info[1]);


                
                
                
                $goods_name_size = 40;
                $line_split=10;
                $top_margin=20;
                //上边距离
                $y=$back_width+$top_margin;
                
                //商品名称换行处理
                $text_array = $this->draw_txt_to(array('fontsize' => $goods_name_size, 'width' => $back_width - 2 * $margin, 'left' => 0), $goods_detail['goods_info']['goods_name']);
                foreach ($text_array as $text) {
                    $y+=$goods_name_size+$line_split;
                    imagefttext($t_logo2, $goods_name_size, 0, $margin, $y, $textcolor, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));
                }

                //广告语
                $y+=$top_margin;
                $goods_name_size = 30;
                $text_array = $this->draw_txt_to(array('fontsize' => $goods_name_size, 'width' => $back_width - 2 * $margin, 'left' => 0), $goods_detail['goods_info']['goods_advword']);
                foreach ($text_array as $text) {
                    $y+=$goods_name_size+$line_split;
                    imagefttext($t_logo2, $goods_name_size, 0, $margin, $y, $textcolor2, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));
                }
                $y+=$top_margin;
                $goods_name_size = 40;
                $y+=$goods_name_size+$line_split;
                $text='零售价：';
                imagefttext($t_logo2, $goods_name_size, 0, $margin, $y, $textcolor2, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));
                //价格
                $goods_name_size = 50;
                $left_temp = 250;
                $text = '￥'.floatval($goods_detail['goods_info']['goods_price']);
                imagefttext($t_logo2, $goods_name_size, 0, $left_temp, $y, $textcolor3, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));

                $y+=$top_margin;

                //二维码
                $logo_size = 200;
                $logo = imagecreatefromstring(file_get_contents(HOME_SITE_URL.'/qrcode?url='. urlencode(config('ds_config.h5_site_url').'/pages/home/goodsdetail/Goodsdetail?goods_id='.$goods_detail['goods_info']['goods_id'].'&inviter_id='.$this->member_info['member_id'])));
                $source_info = getimagesize(HOME_SITE_URL.'/qrcode?url='. urlencode(config('ds_config.h5_site_url').'/pages/home/goodsdetail/Goodsdetail?goods_id='.$goods_detail['goods_info']['goods_id'].'&inviter_id='.$this->member_info['member_id']));
                imagecopyresampled($t_logo2, $logo, $margin, $y, 0, 0, $logo_size, $logo_size, $source_info[0], $source_info[1]);
                //长按查看商品
                
                $text = '长按或扫描二维码识别';
                $left_temp = $logo_size+$margin;
                $goods_name_size = 30;
                $left_top=$y+($logo_size-$goods_name_size*2);
                imagefttext($t_logo2, $goods_name_size, 0, $left_temp, $left_top, $textcolor2, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));
                $text = '查看商品详情';
                $left_top=$y+($logo_size-$goods_name_size)+$line_split;
                imagefttext($t_logo2, $goods_name_size, 0, $left_temp, $left_top, $textcolor2, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));
                
                //获取店铺logo
                $logo_size =$logo_size-$goods_name_size*2;
                $left_temp = $back_width-($logo_size+$margin);
                $logo = imagecreatefromstring(file_get_contents(UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . config('ds_config.site_mobile_logo')));
                $source_info = getimagesize(UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/' . config('ds_config.site_mobile_logo'));
                imagecopyresampled($t_logo2, $logo, $left_temp, $y, 0, 0, $logo_size, $logo_size, $source_info[0], $source_info[1]);

                
                //将店铺名称放到白板上
                $text = config('ds_config.site_name');
                $text_array=$this->draw_txt_to(array('fontsize'=>$goods_name_size,'width'=>$back_width/2-$margin,'left'=>0),$text,1);
                $text=$text_array[0];
                $l= imagettfbbox ( $goods_name_size , 0 , $font_file , $text );
                imagefttext($t_logo2, $goods_name_size, 0, $back_width-$margin-($l[2]-$l[0]), $left_top, $textcolor2, $font_file, mb_convert_encoding($text, 'html-entities', 'UTF-8'));

                imagepng($t_logo2, $refer_qrcode_logo);
        }
        if (file_exists($refer_qrcode_logo)) {
            ds_json_encode(10000,'',array('goods_poster' => UPLOAD_SITE_URL . '/' . ATTACH_INVITER . '/' . $this->member_info['member_id'] .'_'.$goods_id. '_poster.png'));
        } else {
            ds_json_encode(10001,lang('ds_common_op_fail'));
        }
    }

    private function draw_txt_to($pos, $string, $line = 2) {
        $font_file = PUBLIC_PATH . '/font/hyngt.ttf';
        $_string = '';
        $__string = array();

        for ($i = 0; $i < mb_strlen($string, 'utf-8'); $i++) {
            $box = imagettfbbox($pos['fontsize'], 0, $font_file, $_string);
            $_string_length = $box[2] - $box[0];
            $box = imagettfbbox($pos['fontsize'], 0, $font_file, mb_substr($string, $i, 1, 'utf-8'));

            if ($_string_length + $box[2] - $box[0] < ($pos['width'] - $pos['left'])) {
                $_string .= mb_substr($string, $i, 1, 'utf-8');
            } else {
                if (count($__string) >= ($line - 1)) {
                    $_string = mb_substr($_string, 0, mb_strlen($_string, 'utf-8') - 1, 'utf-8') . '...';
                    break;
                }
                $pos['left'] = 0;
                $__string[] = $_string;
                $_string = mb_substr($string, $i, 1, 'utf-8');
            }
        }
        $__string[] = $_string;

        return $__string;
    }

    private function get_lt_rounder_corner($radius) {
        $img = imagecreatetruecolor($radius, $radius); // 创建一个正方形的图像
        $bgcolor = imagecolorallocate($img, 255, 255, 255);  // 图像的背景
        $fgcolor = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $bgcolor);
        // $radius,$radius：以图像的右下角开始画弧
        // $radius*2, $radius*2：已宽度、高度画弧
        // 180, 270：指定了角度的起始和结束点
        // fgcolor：指定颜色
        imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
        // 将弧角图片的颜色设置为透明
        imagecolortransparent($img, $fgcolor);
        // 变换角度
        // $img	= imagerotate($img, 90, 0);
        // $img	= imagerotate($img, 180, 0);
        // $img	= imagerotate($img, 270, 0);
        // header('Content-Type: image/png');
        // imagepng($img);
        return $img;
    }
    public function auth()
    {
        $member_model = model('member');

            $member_array = array();
            $member_array['member_auth_state'] = 1;
            $member_array['member_idcard'] = input('post.member_idcard');
            $member_array['member_truename'] = input('post.member_truename');
                
        if(empty($member_array['member_truename'])){
            ds_json_encode(10001, '真实姓名必填');
        }
        if(empty($member_array['member_idcard'])){
            ds_json_encode(10001, '身份证号必填');
        }
                
            if(!$this->member_info['member_idcard_image1']){
              ds_json_encode(10001,lang('member_idcard_image1_require'));
            }    
            if(!$this->member_info['member_idcard_image2']){
              ds_json_encode(10001,lang('member_idcard_image2_require'));
            }  
            if(!$this->member_info['member_idcard_image3']){
              ds_json_encode(10001,lang('member_idcard_image3_require'));
            }  
            if(!input('post.if_confirm')){
                ds_json_encode(10000);
            }
            $condition = array();
            $condition[] = array('member_id','=',$this->member_info['member_id']);
            $condition[] = array('member_auth_state','in',array(0,2));
            $update = $member_model->editMember($condition, $member_array,$this->member_info['member_id']);

            $message = $update ? lang('ds_common_op_succ') : lang('ds_common_op_fail');
            
            if($update){
                ds_json_encode(10000,$message);
            }else{
                ds_json_encode(10001,$message);
            }
        

    }
    public function edit_auth() {
        $file_name = input('param.id');
            if (!empty($_FILES[$file_name]['name'])) {

                $res=ds_upload_pic(ATTACH_IDCARD_IMAGE,$file_name);
                if(!$res['code']){
                    ds_json_encode(10001,$res['msg']);
                }
                if(!in_array(substr($file_name,0,20),array('member_idcard_image1','member_idcard_image2','member_idcard_image3'))){
                    ds_json_encode(10001,lang('param_error'));
                }
                $member_array=array();
                $member_array[substr($file_name,0,20)] = $res['data']['file_name'];
                $member_model = model('member');
                $condition = array();
                $condition[] = array('member_id','=',$this->member_info['member_id']);
                $condition[] = array('member_auth_state','in',array(0,2));
                if(!$member_model->editMember($condition, $member_array,$this->member_info['member_id'])){
                    ds_json_encode(10001,lang('ds_common_op_fail'));
                }
                ds_json_encode(10000,'',array('file_name'=>$res['data']['file_name'],'file_path'=>get_member_idcard_image($res['data']['file_name'])));
            }
            ds_json_encode(10001,lang('param_error'));
    }
    public function drop_auth(){
        $file_name=input('param.file_name');
        if(!in_array($file_name,array('member_idcard_image1','member_idcard_image2','member_idcard_image3'))){
            ds_json_encode(10001,lang('param_error'));
        }
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_IDCARD_IMAGE . DIRECTORY_SEPARATOR . $this->member_info[$file_name]);
        $member_array=array();
        $member_array[$file_name] = '';
        $member_model = model('member');
        $condition = array();
                $condition[] = array('member_id','=',$this->member_info['member_id']);
                $condition[] = array('member_auth_state','in',array(0,2));
        if(!$member_model->editMember($condition, $member_array,$this->member_info['member_id'])){
                    ds_json_encode(10001,lang('ds_common_op_fail'));
                }
        ds_json_encode(10000);        
    }
    
    public function logout() {
        $condition = array();
        $condition[] = array('platform_userid', '=', $this->member_info['member_id']);
        $condition[] = array('platform_token', '=', $this->member_info['member_token']);
        $condition[] = array('platform_type', '=', 'member');
        $result = model('platformtoken')->delPlatformtoken($condition);

        if (!$result) {
            ds_json_encode(10001, '退出失败');
        }
        session(null); //删除session中的member_id下，因为次登录时要整合cookie中的商品到数据库中
        ds_json_encode(10000, '');
    }
    
    //解除当前用户的微信绑定
    public function unbindWechat(){
        
        $update_arr['member_wxunionid'] = '';
        
        $update_arr['member_pc_wxopenid'] = '';
        $update_arr['member_h5_wxopenid'] = '';
        $update_arr['member_mini_wxopenid'] = '';
        $update_arr['member_app_wxopenid'] = '';
        
        $update_arr['member_wxnickname'] = '';
        $member_model = model('member');
        $edit_state = $member_model->editMember(array('member_id' => $this->member_info['member_id']), $update_arr,$this->member_info['member_id']);
        if ($edit_state) {
            ds_json_encode(10000, '');
        }else{
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
        
    }
    //当前用户更新微信绑定信息
    public function updateBindWechat(){
        
        $WeixinLogin = new \weixin\WeixinLogin();
        $res = $WeixinLogin->getOpenid();
        $userinfo = $WeixinLogin->getUserinfo($res);
        
        $member_wxunionid = $userinfo['unionid'];
        
        if (empty($member_wxunionid) || strlen($member_wxunionid) != 28) {
            ds_json_encode(10001, 'updateBindWechat参数错误');
        }

        $member_model = model('member');
        $member_info = $member_model->getMemberInfo(array('member_wxunionid' => $member_wxunionid));
        
        if(!empty($member_info)){
            ds_json_encode(10001, '该微信账户已绑定了账户');
        }
        
        $update_arr['member_wxunionid'] = $member_wxunionid;
        
        $wx_from = input('param.wx_from');
        switch ($wx_from){
            case 'pc':
                $update_arr['member_pc_wxopenid'] = $userinfo['openid'];
                break;
            case 'h5':
                $update_arr['member_h5_wxopenid'] = $userinfo['openid'];
                break;
            case 'miniprogram':
                $update_arr['member_mini_wxopenid'] = $userinfo['openid'];
                break;
            case 'app':
                $update_arr['member_app_wxopenid'] = $userinfo['openid'];
                break;
        }
        
        $update_arr['member_wxnickname'] = isset($userinfo['nickname'])?$userinfo['nickname']:'';
        
        $edit_state = $member_model->editMember(array('member_id' => $this->member_info['member_id']), $update_arr,$this->member_info['member_id']);
        
        if ($edit_state) {
            ds_json_encode(10000, '');
        }else{
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
        
    }
    
}

?>
