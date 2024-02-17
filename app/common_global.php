<?php
//获取URL访问的ROOT地址 网站的相对路径
define('BASE_SITE_ROOT', str_replace('/index.php', '', \think\facade\Request::instance()->root()));
define('PLUGINS_SITE_ROOT', BASE_SITE_ROOT.'/static/plugins');
define('ADMIN_SITE_ROOT', BASE_SITE_ROOT.'/static/admin');
define('HOME_SITE_ROOT', BASE_SITE_ROOT.'/static/home');

define("REWRITE_MODEL", FALSE); // 设置伪静态
if (!REWRITE_MODEL) {
    define('BASE_SITE_URL', \think\facade\Request::instance()->domain() . \think\facade\Request::instance()->baseFile());
} else {
    // 系统开启伪静态
    if (empty(BASE_SITE_ROOT)) {
        define('BASE_SITE_URL', \think\facade\Request::instance()->domain());
    } else {
        define('BASE_SITE_URL', \think\facade\Request::instance()->domain() . \think\facade\Request::instance()->root());
    }
}

//检测是否安装 DSSHOP 系统
if(file_exists("install/") && !file_exists("install/install.lock")){
    header('Location: '.BASE_SITE_ROOT.'/install/install.php');
    exit();
}
//error_reporting(E_ALL ^ E_NOTICE);//显示除去 E_NOTICE 之外的所有错误信息




//define('BASE_SITE_URL', BASE_SITE_URL);
define('HOME_SITE_URL', BASE_SITE_URL.'/home');
define('ADMIN_SITE_URL', BASE_SITE_URL.'/admin');
define('API_SITE_URL', BASE_SITE_URL.'/api');
define('UPLOAD_SITE_URL',str_replace('/index.php', '', BASE_SITE_URL).'/uploads');
define('EXAMPLES_SITE_URL',str_replace('/index.php', '', BASE_SITE_URL).'/examples');
define('SESSION_EXPIRE',3600);

defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH',ROOT_PATH.'public');
define('PLUGINS_PATH',ROOT_PATH.'plugins');
define('BASE_DATA_PATH',PUBLIC_PATH.'/data');
define('BASE_UPLOAD_PATH',PUBLIC_PATH.'/uploads');

define('TIMESTAMP',time());
define('DIR_HOME','home');
define('DIR_ADMIN','admin');

define('DIR_UPLOAD','public/uploads');

define('ATTACH_PATH','home');
define('ATTACH_COMMON',ATTACH_PATH.'/common');
define('ATTACH_AVATAR',ATTACH_PATH.'/avatar');
define('ATTACH_INVITER',ATTACH_PATH.'/inviter');
define('ATTACH_EDITOR',ATTACH_PATH.'/editor');
define('ATTACH_MEMBERTAG',ATTACH_PATH.'/membertag');
define('ATTACH_IDCARD_IMAGE',ATTACH_PATH.'/idcard_image');
define('ATTACH_GOODS',ATTACH_PATH.'/store/goods');
define('ATTACH_TAOBAO',ATTACH_PATH.'/store/taobao');
define('ATTACH_LOGIN',ATTACH_PATH.'/login');
define('ATTACH_ARTICLE',ATTACH_PATH.'/article');
define('ATTACH_EDITABLE_PAGE',ATTACH_PATH.'/editable_page');
define('ATTACH_BRAND',ATTACH_PATH.'/brand');
define('ATTACH_GOODS_CLASS',ATTACH_PATH.'/goods_class');
define('ATTACH_ADV',ATTACH_PATH.'/adv');
define('ATTACH_APPADV',ATTACH_PATH.'/appadv');
define('ATTACH_ACTIVITY',ATTACH_PATH.'/activity');
define('ATTACH_WATERMARK',ATTACH_PATH.'/watermark');
define('ATTACH_POINTPROD',ATTACH_PATH.'/pointprod');
define('ATTACH_GROUPBUY',ATTACH_PATH.'/groupbuy');
define('ATTACH_VOUCHER',ATTACH_PATH.'/voucher');
define('ATTACH_MOBILE','mobile');
define('ATTACH_MALBUM',ATTACH_PATH.'/member');
define('TPL_SHOP_NAME','default');
define('TPL_ADMIN_NAME', 'default');
define('TPL_DELIVERY_NAME', 'default');
define('TPL_MEMBER_NAME', 'default');

define('DEFAULT_CONNECT_SMS_TIME', 60);//倒计时时间

define('MD5_KEY', 'a2382918dbb49c8643f19bc3ab90ecf9');
define('CHARSET','UTF-8');
define('ALLOW_IMG_EXT','jpg,png,gif,bmp,jpeg');#上传图片后缀
define('ALLOW_IMG_SIZE',2097152);#上传图片大小（2MB）
define('HTTP_TYPE',  \think\facade\Request::instance()->isSsl() ? 'https://' : 'http://');#是否为SSL

define('VERIFY_CODE_INVALIDE_MINUTE',15);//验证码失效时间（分钟）
//
//默认颜色规格id(前台显示图片的规格)
define('DEFAULT_SPEC_COLOR_ID', 1);


/**
 * 店铺相册图片规格形式, 处理的图片包含 商品图片以
 */
define('GOODS_IMAGES_WIDTH', '240,480,1280');
define('GOODS_IMAGES_HEIGHT', '240,480,1280');
define('GOODS_IMAGES_EXT', '_240,_480,_1280');

/**
 * 通用图片生成规格形式
 */
define('COMMON_IMAGES_EXT', '_240,_480,_1280');


/**
 *  订单状态
 */
//已取消
define('ORDER_STATE_CANCEL', 0);
//已产生但未支付
define('ORDER_STATE_NEW', 10);
//已支付
define('ORDER_STATE_PAY', 20);
//已发货
define('ORDER_STATE_SEND', 30);
//已收货，交易成功
define('ORDER_STATE_SUCCESS', 40);
//默认未删除
define('ORDER_DEL_STATE_DEFAULT', 0);
//已删除
define('ORDER_DEL_STATE_DELETE', 1);
//彻底删除
define('ORDER_DEL_STATE_DROP', 2);
//订单结束后可评论时间，15天，60*60*24*15
define('ORDER_EVALUATE_TIME', 1296000);
//抢购订单状态
define('OFFLINE_ORDER_CANCEL_TIME', 3);//单位为天

?>
