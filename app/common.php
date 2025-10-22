<?php

use think\facade\Db;

/* 引用全局定义 */
require __DIR__ . '/common_global.php';
/* 商品相关调用 */
require __DIR__ . '/common_goods.php';
/* 图片上传、生成缩略图、删除等操作调用 */
require __DIR__ . '/common_upload.php';
/*
 * 更换数组的键值 为了应对 ->key
 */

function ds_validate($name) {
    $name = preg_replace_callback('/([-_]+([a-z]{1}))/i', function($matches) {
        return strtoupper($matches[2]);
    }, $name);
    $class_name = '\app\common\validate\\' . ucfirst($name);
    return new $class_name;
}

function model($name, $layer = 'model') {
    $name = preg_replace_callback('/([-_]+([a-z]{1}))/i', function($matches) {
        return strtoupper($matches[2]);
    }, $name);
    $class_name = '\app\common\\' . $layer . '\\' . ucfirst($name);
    return new $class_name;
}

/**
 * 过滤特殊字符
 * @param type $clean_text
 * @return type
 */
function remove_special_words($clean_text) {
    //不过滤变量
    $filter = ['modify_pwd', 'modify_mobile', 'modify_email','modify_paypwd', 'unionpay', 'unionpay_h5',];
    if(in_array($clean_text, $filter)){
        return $clean_text;
    }
    
    $farr = [
            "/select|join|where|drop|like|modify|rename|insert|update|table|database|alter|truncate|union|into|load_file|outfile/is"
        ];
    $clean_text = preg_replace($farr, '', $clean_text);
    return $clean_text;
}

/**
 * 去除特殊表情符号
 * @param type $string
 * @return type
 */
function removeEmojis($clean_text) {
    $clean_text = preg_replace_callback(//执行一个正则表达式搜索并且使用一个回调进行替换
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $clean_text);
    return $clean_text;
}

function ds_change_arraykey($array, $key) {
    $data = array();
    foreach ($array as $value) {
        $data[$value[$key]] = $value;
    }
    return $data;
}

/**
 * 
 * @param type $table 数据表
 * @param type $field 条件对应的字段
 * @param type $name  条件对应的值
 * @param type $value 数值
 * @return type
 */
function ds_getvalue_byname($table, $field, $name, $value) {
    return Db::name($table)->where($field, $name)->value($value);
}

/*
 * 编辑器内容
 */

function build_editor($params = array()) {
    $name = isset($params['name']) ? $params['name'] : null;
    $theme = isset($params['theme']) ? $params['theme'] : 'normal';
    $content = isset($params['content']) ? $params['content'] : null;
    //http://fex.baidu.com/ueditor/#start-toolbar
    /* 指定使用哪种主题 */
    $themes = array(
        'normal' => "[   
           'fullscreen', 'source', '|', 'undo', 'redo', '|',   
           'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',   
           'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',   
           'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',   
           'directionalityltr', 'directionalityrtl', 'indent', '|',   
           'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',   
           'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',   
           'emotion',  'map', 'gmap',  'insertcode', 'template',  '|',   
           'horizontal', 'date', 'time', 'spechars', '|',   
           'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',   
           'searchreplace', 'help', 'drafts', 'charts'
       ]", 'simple' => " ['fullscreen', 'source', 'undo', 'redo', 'bold']",
    );
    switch ($theme) {
        case 'simple':
            $theme_config = $themes['simple'];
            break;
        case 'normal':
            $theme_config = $themes['normal'];
            break;
        default:
            $theme_config = $themes['normal'];
            break;
    }
    /* 配置界面语言 */
    switch (config('lang.default_lang')) {
        case 'zh-cn':
            $lang = PLUGINS_SITE_ROOT . '/ueditor/lang/zh-cn/zh-cn.js';
            break;
        case 'en-us':
            $lang = PLUGINS_SITE_ROOT . '/ueditor/lang/en/en.js';
            break;
        default:
            $lang = PLUGINS_SITE_ROOT . '/ueditor/lang/zh-cn/zh-cn.js';
            break;
    }
    $include_js = '<script type="text/javascript" charset="utf-8" src="' . PLUGINS_SITE_ROOT . '/ueditor/ueditor.config.js"></script> <script type="text/javascript" charset="utf-8" src="' . PLUGINS_SITE_ROOT . '/ueditor/ueditor.all.min.js""> </script><script type="text/javascript" charset="utf-8" src="' . $lang . '"></script>';
    $content = json_encode($content);
    $str = <<<EOT
$include_js
<script type="text/javascript">
var ue = UE.getEditor('{$name}',{
    toolbars:[{$theme_config}],
        });
    if($content){
ue.ready(function() {
       this.setContent($content);	
})
   }
      
</script>
EOT;
    return $str;
}

/**
 * 
 * @param type $code   100000表示为正确,其他为错误代码
 * @param type $message  提示消息
 * @param type $result  返回数据
 * @param type $$requestMethod  返回请求Method
 */
function ds_json_encode($code, $message = '', $result = '', $requestMethod = '', $if_exit = true) {
    $data = array('code' => $code, 'message' => $message, 'result' => $result, 'requestMethod' => $requestMethod);
    if (!empty($_GET['callback'])) {
        echo $_GET['callback'] . '(' . json_encode($data) . ')';
    } else {
        echo json_encode($data);
    }
    if ($if_exit) {
        exit;
    }
}

/**
 * 规范数据返回函数
 * @param unknown $code
 * @param unknown $msg
 * @param unknown $data
 * @return multitype:unknown
 */
function ds_callback($code, $msg = '', $data = array()) {
    return array('code' => $code, 'msg' => $msg, 'data' => $data);
}

/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++)
        $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 消息提示，主要适用于普通页面AJAX提交的情况
 *
 * @param string $message 消息内容
 * @param string $url 提示完后的URL去向
 * @param stting $alert_type 提示类型 error/succ/notice 分别为错误/成功/警示
 * @param string $extrajs 扩展JS
 * @param int $time 停留时间
 */
function ds_show_dialog($message = '', $url = '', $alert_type = 'error', $extrajs = '', $time = 2) {
    $message = str_replace("'", "\\'", strip_tags($message));

    $paramjs = null;
    if ($url == 'reload') {
        $paramjs = 'window.location.reload()';
    } elseif ($url != '') {
        $paramjs = 'window.location.href =\'' . $url . '\'';
    }
    if ($paramjs) {
        $paramjs = 'function (){' . $paramjs . '}';
    } else {
        $paramjs = 'null';
    }
    $modes = array('error' => 'alert', 'succ' => 'succ', 'notice' => 'notice', 'js' => 'js');
    $cover = $alert_type == 'error' ? 1 : 0;
    $extra = 'showDialog(\'' . $message . '\', \'' . $modes[$alert_type] . '\', null, ' . ($paramjs ? $paramjs : 'null') . ', ' . $cover . ', null, null, null, null, ' . (is_numeric($time) ? $time : 'null') . ', null);';
    $extra = '<script type="text/javascript" reload="1">' . $extra . '</script>';
    if ($extrajs != '' && substr(trim($extrajs), 0, 7) != '<script') {
        $extrajs = '<script type="text/javascript" reload="1">' . $extrajs . '</script>';
    }
    $extra .= $extrajs;
    ob_end_clean();
    @header("Expires: -1");
    @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
    @header("Pragma: no-cache");
    @header("Content-type: text/xml; charset=utf-8");

    $string = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";
    $string .= '<root><![CDATA[' . $message . $extra . ']]></root>';
    echo $string;
    exit;
}

/**
 * 取上一步来源地址
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function get_referer() {
    return empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
}

/**
 * 加密函数
 *
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function ds_encrypt($txt, $key = '') {
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5(config('ds_config.site_uniqid'));
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $nh1 = rand(0, 64);
    $nh2 = rand(0, 64);
    $nh3 = rand(0, 64);
    $ch1 = $chars{$nh1};
    $ch2 = $chars{$nh2};
    $ch3 = $chars{$nh3};
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;
    $i = 0;
    while (isset($key{$i}))
        $knum += ord($key{$i++});
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $txt = base64_encode(TIMESTAMP . '_' . $txt);
    $txt = str_replace(array('+', '/', '='), array('-', '_', '.'), $txt);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum + strpos($chars, $txt{$i}) + ord($mdKey{$k++})) % 64;
        $tmp .= $chars{$j};
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp, $ch3, $nh2 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch2, $nh1 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch1, $knum % ++$tmplen, 0);
    return $tmp;
}

/**
 * 解密函数
 *
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function ds_decrypt($txt, $key = '', $ttl = 0) {
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5(config('ds_config.site_uniqid'));

    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $knum = 0;
    $i = 0;
    $tlen = @strlen($txt);
    while (isset($key{$i}))
        $knum += ord($key{$i++});
    $ch1 = @$txt{$knum % $tlen};
    $nh1 = strpos($chars, $ch1);
    $txt = @substr_replace($txt, '', $knum % $tlen--, 1);
    $ch2 = @$txt{$nh1 % $tlen};
    $nh2 = @strpos($chars, $ch2);
    $txt = @substr_replace($txt, '', $nh1 % $tlen--, 1);
    $ch3 = @$txt{$nh2 % $tlen};
    $nh3 = @strpos($chars, $ch3);
    $txt = @substr_replace($txt, '', $nh2 % $tlen--, 1);
    $nhnum = $nh1 + $nh2 + $nh3;
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = @strlen($txt);
    $klen = @strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars, $txt{$i}) - $nhnum - ord($mdKey{$k++});
        while ($j < 0)
            $j += 64;
        $tmp .= $chars{$j};
    }
    $tmp = str_replace(array('-', '_', '.'), array('+', '/', '='), $tmp);
    $tmp = trim(base64_decode($tmp));

    if (preg_match("/\d{10}_/s", substr($tmp, 0, 11))) {
        if ($ttl > 0 && (TIMESTAMP - (int) substr($tmp, 0, 11) > $ttl)) {
            $tmp = null;
        } else {
            $tmp = substr($tmp, 11);
        }
    }
    return $tmp;
}

/**
 * 获取文件列表(所有子目录文件)
 *
 * @param string $path 目录
 * @param array $file_list 存放所有子文件的数组
 * @param array $ignore_dir 需要忽略的目录或文件
 * @return array 数据格式的返回结果
 */
function read_file_list($path, &$file_list, $ignore_dir = array()) {
    $path = rtrim($path, '/');
    if (is_dir($path)) {
        $handle = @opendir($path);
        if ($handle) {
            while (false !== ($dir = readdir($handle))) {
                if ($dir != '.' && $dir != '..') {
                    if (!in_array($dir, $ignore_dir)) {
                        if (is_file($path . '/' . $dir)) {
                            $file_list[] = $path . '/' . $dir;
                        } elseif (is_dir($path . '/' . $dir)) {
                            read_file_list($path . '/' . $dir, $file_list, $ignore_dir);
                        }
                    }
                }
            }
            @closedir($handle);
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * 价格格式化
 *
 * @param int $price
 * @return string    $price_format
 */
function ds_price_format($price) {
    $price_format = number_format($price, 2, '.', '');
    return $price_format;
}

/**
 * 价格格式化
 *
 * @param int $price
 * @return string    $price_format
 */
function ds_price_format_forlist($price) {
    if ($price >= 10000) {
        return number_format(floor($price / 100) / 100, 2, '.', '') . lang('ten_thousand');
    } else {
        return lang('currency') . $price;
    }
}

/**
 * 通知邮件/通知消息 内容转换函数
 *
 * @param string $message 内容模板
 * @param array $param 内容参数数组
 * @return string 通知内容
 */
function ds_replace_text($message, $param) {
    if (!is_array($param))
        return false;
    foreach ($param as $k => $v) {
        $message = str_replace('${' . $k . '}', $v, $message);
    }
    return $message;
}

/** @noinspection InconsistentLineSeparators */

/**
 * 字符串切割函数，一个字母算一个位置,一个字算2个位置
 *
 * @param string $string 待切割的字符串
 * @param int $length 切割长度
 * @param string $dot 尾缀
 */
function str_cut($string, $length, $dot = '') {
    $string = str_replace(array(
        '&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;',
        '&middot;', '&hellip;'
            ), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strlen = strlen($string);
    if ($strlen <= $length)
        return $string;
    $maxi = $length - strlen($dot);
    $strcut = '';

    $n = $tn = $noc = 0;
    while ($n < $strlen) {
        $t = ord($string[$n]);
        if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1;
            $n++;
            $noc++;
        } elseif (194 <= $t && $t <= 223) {
            $tn = 2;
            $n += 2;
            $noc += 2;
        } elseif (224 <= $t && $t < 239) {
            $tn = 3;
            $n += 3;
            $noc += 2;
        } elseif (240 <= $t && $t <= 247) {
            $tn = 4;
            $n += 4;
            $noc += 2;
        } elseif (248 <= $t && $t <= 251) {
            $tn = 5;
            $n += 5;
            $noc += 2;
        } elseif ($t == 252 || $t == 253) {
            $tn = 6;
            $n += 6;
            $noc += 2;
        } else {
            $n++;
        }
        if ($noc >= $maxi)
            break;
    }
    if ($noc > $maxi)
        $n -= $tn;
    $strcut = substr($string, 0, $n);
    $strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}

/*
 * 重写$_SERVER['REQUREST_URI']
 */

function request_uri() {
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    } else {
        if (isset($_SERVER['argv'])) {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
        } else {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $uri;
}



function get_member_id_by_XDSKEY() {
    $key = request()->header('X-DS-KEY');
    if (!$key) {
        return;
    }
    
    $condition = array();
    $condition[] = array('platform_type', '=', 'member');
    $condition[] = array('platform_token', '=', $key);

    $platformtoken_info = model('platformtoken')->getPlatformtokenInfo($condition);
    if (empty($platformtoken_info)) {
        return;
    } else {
        return $platformtoken_info['platform_userid'];
    }
}

function get_member_idcard_image($member_image, $if_local = false) {
    if ($member_image) {
        return ds_get_pic(ATTACH_IDCARD_IMAGE, $member_image);
    }

    return '';
}

/**
 * 取得用户头像图片
 *
 * @param string $member_avatar
 * @return string
 */
function get_member_avatar($member_avatar) {
    if (empty($member_avatar)) {
        return ds_get_pic(ATTACH_COMMON,config('ds_config.default_user_portrait'));
    } else {
        $url = ds_get_pic(ATTACH_AVATAR, $member_avatar);
        if ($url) {
            return $url;
        } else {
            return ds_get_pic(ATTACH_COMMON,config('ds_config.default_user_portrait'));
        }
    }
}

/**
 * 成员头像
 * @param string $member_id
 * @return string
 */
function get_member_avatar_for_id($id) {
    $member_model=model('member');
    $member_info=$member_model->getMemberInfoByID($id);
    if($member_info){
        return get_member_avatar($member_info['member_avatar']);
    }
}

function get_adv_code($adv_code) {
    $url = ds_get_pic(ATTACH_ADV, $adv_code);
    if (!$url) {
        return ds_get_pic(ATTACH_COMMON,config('ds_config.default_goods_image'));
    } else {
        return $url;
    }
}

function get_appadv_code($adv_code) {
    $url = ds_get_pic(ATTACH_APPADV, $adv_code);
    if (!$url) {
        return ds_get_pic(ATTACH_COMMON,config('ds_config.default_goods_image'));
    } else {
        return $url;
    }
}

/**
 * 取得随机数
 *
 * @param int $length 生成随机数的长度
 * @param int $numeric 是否只产生数字随机数 1是0否
 * @return string
 */
function random($length, $numeric = 0) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

/**
 * sns表情标示符替换为html
 */
function parsesmiles($message) {
    $message = strip_tags(htmlspecialchars_decode($message));
    $file = root_path() . 'extend/smilies.php';
    if (file_exists($file)) {
        include $file;
        if (!empty($smilies_array) && is_array($smilies_array)) {
            $imagesurl = PLUGINS_SITE_ROOT . '/js' . '/smilies' . '/images' . '/';
            $replace_arr = array();
            foreach ($smilies_array['replacearray'] AS $key => $smiley) {
                $replace_arr[$key] = '<img src="' . $imagesurl . $smiley['imagename'] . '" title="' . $smiley['desc'] . '" border="0" alt="' . $imagesurl . $smiley['desc'] . '" />';
            }

            $message = preg_replace($smilies_array['searcharray'], $replace_arr, $message);
        }
    }
    return $message;
}

/**
 * 返回以原数组某个值为下标的新数据
 *
 * @param array $array
 * @param string $key
 * @param int $type 1一维数组2二维数组
 * @return array
 */
function array_under_reset($array, $key, $type = 1) {
    if (is_array($array)) {
        $tmp = array();
        foreach ($array as $v) {
            if ($type === 1) {
                $tmp[$v[$key]] = $v;
            } elseif ($type === 2) {
                $tmp[$v[$key]][] = $v;
            }
        }
        return $tmp;
    } else {
        return $array;
    }
}

/**
 * KV缓存 读
 *
 * @param string $key 缓存名称
 * @param boolean $callback 缓存读取失败时是否使用回调 true代表使用cache.model中预定义的缓存项 默认不使用回调
 * @param callable $callback 传递非boolean值时 通过is_callable进行判断 失败抛出异常 成功则将$key作为参数进行回调
 * @return mixed
 */
function rkcache($key, $callback = false) {
    $value = cache($key);
    if (empty($value) && $callback !== false) {
        if ($callback === true) {
            $callback = array(model('cache'), 'call');
        }

        if (!is_callable($callback)) {
            throw new \think\Exception('Invalid rkcache callback!', 10006);
        }
        $value = call_user_func($callback, $key);
        wkcache($key, $value);
    }
    return $value;
}

/**
 * KV缓存 写
 *
 * @param string $key 缓存名称
 * @param mixed $value 缓存数据 若设为否 则下次读取该缓存时会触发回调（如果有）
 * @param int $expire 缓存时间 单位秒 null代表不过期
 * @return boolean
 */
function wkcache($key, $value, $expire = 7200) {
    return cache($key, $value, $expire);
}

/**
 * KV缓存 删
 *
 * @param string $key 缓存名称
 * @return boolean
 */
function dkcache($key) {
    return cache($key, NULL);
}

/**
 * 读取缓存信息
 *
 * @param string $key 要取得缓存键
 * @param string $prefix 键值前缀
 * @return array/bool
 */
function rcache($key = null, $prefix = '') {
    if ($key === null || !config('ds_config.cache_open'))
        return array();
    if (!empty($prefix)) {
        $name = $prefix . $key;
    } else {
        $name = $key;
    }
    $cache_info = cache($name);
    //如果name值不存在，则默认返回 false。
    return $cache_info;
}

/**
 * 写入缓存
 *
 * @param string $key 缓存键值
 * @param array $data 缓存数据
 * @param string $prefix 键值前缀
 * @param int $expire 缓存周期  单位分，0为永久缓存
 * @return bool 返回值
 */
function wcache($key = null, $data = array(), $prefix = '', $expire = 3600) {
    if ($key === null || !config('ds_config.cache_open') || !is_array($data))
        return;

    if (!empty($prefix)) {
        $name = $prefix . $key;
    } else {
        $name = $key;
    }
    $cache_info = cache($name, $data, $expire);
    //如果设置成功返回true，否则返回false。
    return $cache_info;
}

/**
 * 删除缓存
 * @param string $key 缓存键值
 * @param string $prefix 键值前缀
 * @return boolean
 */
function dcache($key = null, $prefix = '') {
    if ($key === null || !config('ds_config.cache_open'))
        return true;
    if (!empty($prefix)) {
        $name = $prefix . $key;
    } else {
        $name = $key;
    }
    return cache($name, NULL);
}

/**
 * 生成20位编号(时间+微秒+随机数+会员ID%1000)，该值会传给第三方支付接口
 * 长度 =12位 + 3位 + 2位 + 3位  = 20位
 * 1000个会员同一微秒提订单，重复机率为1/100
 * @return string
 */
function makePaySn($member_id) {
    return date('ymdHis', TIMESTAMP) . sprintf('%03d', (float) microtime() * 1000) . mt_rand(10, 99) . sprintf('%03d', intval($member_id) % 1000);
}

/**
 * 将字符部分加密并输出
 * @param unknown $str
 * @param unknown $start 从第几个位置开始加密(从1开始)
 * @param unknown $length 连续加密多少位
 * @return string
 */
function encrypt_show($str, $start, $length) {
    $end = $start - 1 + $length;
    $array = str_split($str);
    foreach ($array as $k => $v) {
        if ($k >= $start - 1 && $k < $end) {
            $array[$k] = '*';
        }
    }
    return implode('', $array);
}

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug 调试开启 默认false
 * @return mixed
 */
function http_request($url, $method = "GET", $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2); /* 指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的 */
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /* curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
}

/**
 * Layer 提交成功返回函数
 * @param type $message
 */
function dsLayerOpenSuccess($msg = '', $url = '') {
//    echo "<script>var index = parent.layer.getFrameIndex(window.name);parent.layer.close(index);parent.location.reload();</script>";
    $url_js = empty($url) ? "parent.location.reload();" : "parent.location.href='" . $url . "';";

    $str = "<script>";
    $str .= "parent.layer.alert('" . $msg . "',{yes:function(index, layero){" . $url_js . "},cancel:function(index, layero){" . $url_js . "}});";
    $str .= "</script>";
    echo $str;
    exit;
}


/**
 * 截取指定长度的字符
 * @param type $string  内容
 * @param type $start 开始
 * @param type $length 长度
 * @return type
 */
function ds_substing($string, $start = 0, $length = 80) {
    $string = strip_tags($string);
    $string = preg_replace('/\s/', '', $string);
    return mb_substr($string, $start, $length);
}

/**
 * 针对批量删除进行处理  '1,2,3' 转换为数组批量删除
 * @param type $ids
 * @return boolean
 */
function ds_delete_param($ids) {
    //转换为数组
    $ids_array = explode(',', $ids);
    //数组值转为整数型
    $ids_array = array_map("intval", $ids_array);
    if (empty($ids_array) || in_array(0, $ids_array)) {
        return FALSE;
    } else {
        return $ids_array;
    }
}



function word_filter_access_token(){
    $appid=config('ds_config.word_filter_appid');
    $secret=config('ds_config.word_filter_secret');
    $access_token=config('ds_config.word_filter_access_token');
    $access_token_expire=config('ds_config.word_filter_access_token_expire');
    if(!$access_token || $access_token_expire<TIMESTAMP){
        $res=http_request('https://aip.baidubce.com/oauth/2.0/token','POST',array(
            'grant_type'=>'client_credentials',
            'client_id'=>$appid,
            'client_secret'=>$secret,
        ));
        $res = json_decode($res, true);
        if(isset($res['error'])){
            return ds_callback(false, $res['error_description']);
        }
        $access_token=$res['access_token'];
        $expires_in=$res['expires_in'];
        
        $config_model = model('config');
        $update_array=array(
            'word_filter_access_token'=>$access_token,
            'word_filter_access_token_expire'=>TIMESTAMP+$expires_in
        );
        $config_model->editConfig($update_array);
    }
    return ds_callback(true,'',$access_token);
}
/**
 * 敏感词过滤
 * @param type $text
 * @return boolean
 */
function word_filter($text) {
    $data=array();
    $data['text']=$text;
    $data['if_sensitive']=false;
    if(config('ds_config.word_filter_open')!=1){
        return ds_callback(true, '', $data);
    }

    $res=word_filter_access_token();
    if(!$res['code']){
        return $res;
    }
    $access_token=$res['data'];
    $res=http_request('https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined?access_token='.$access_token,'POST',array(
        'text'=> $text
        ));
    $res = json_decode($res, true);
    if(isset($res['error_code'])){
        return ds_callback(false, $res['error_msg']);
    }
    if($res['conclusionType']==2){
        $data['if_sensitive']=true;
        $data['sensitive_msg']=array();
        $data['sensitive_word']=array();
        foreach($res['data'] as $val){
            $data['sensitive_msg'][]=$val['msg'];
            foreach($val['hits'] as $v){
                $data['sensitive_word']=array_merge($data['sensitive_word'],$v['words']);
                $data['text']=str_replace($v['words'],'**',$data['text']);
            }
        }
    }
    return ds_callback(true, '', $data);
}


/**
 * 敏感图过滤
 * @param type $text
 * @return boolean
 */
function image_filter($img_url) {
    $data=array();
    $data['if_sensitive']=false;
    if(config('ds_config.word_filter_open')!=1){
        return ds_callback(true, '', $data);
    }
    $res=word_filter_access_token();
    if(!$res['code']){
        return $res;
    }
    $access_token=$res['data'];
    $image=imgToBase64($img_url);
    if(empty($image)){
        return ds_callback(false, 'image empty');
    }
    $res=http_request('https://aip.baidubce.com/rest/2.0/solution/v1/img_censor/v2/user_defined?access_token='.$access_token,'POST',array(
        'image'=> $image['content']
        ),array(
            'Content-Type: application/x-www-form-urlencoded'
        ));
    $res = json_decode($res, true);
    if(isset($res['error_code'])){
        return ds_callback(false, $res['error_msg']);
    }
    if($res['conclusionType']==2){
        $data['if_sensitive']=true;
        $data['sensitive_msg']=array();
        foreach($res['data'] as $val){
            $data['sensitive_msg'][]=$val['msg'];
        }
    }
    return ds_callback(true, '', $data);
}


//根据USER_AGENT 获取系统名称
function getOSFromUserAgent() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $osPatterns = array(
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i' => 'Windows XP',
        '/windows xp/i' => 'Windows XP',
        '/windows nt 5.0/i' => 'Windows 2000',
        '/windows me/i' => 'Windows ME',
        '/win98/i' => 'Windows 98',
        '/win95/i' => 'Windows 95',
        '/win16/i' => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i' => 'Mac OS 9',
        '/linux/i' => 'Linux',
        '/unix/i' => 'Unix',
        '/sunos/i' => 'SunOS',
        '/bsd/i' => 'FreeBSD',
        '/ibm/i' => 'IBM OS/2',
        '/applewebkit/i' => 'Apple WebKit',
        '/chrome/i' => 'Chrome',
        '/safari/i' => 'Safari',
        '/opera/i' => 'Opera',
        '/opera mobi/i' => 'Opera Mobile',
        '/opera mini/i' => 'Opera Mini',
        '/konqueror/i' => 'Konqueror',
        '/mozilla/i' => 'Mozilla Firefox',
        '/seamonkey/i' => 'SeaMonkey',
        '/firefox/i' => 'Mozilla Firefox',
        '/msie/i' => 'Internet Explorer',
        '/netscape/i' => 'Netscape',
        '/maxthon/i' => 'Maxthon',
        '/tencent traveler/i' => 'Tencent Traveler',
        '/trident/i' => 'Trident',
        '/realplayer/i' => 'RealPlayer',
        '/flashget/i' => 'FlashGet',
        '/java/i' => 'Java',
        '/curl/i' => 'cURL',
        '/wget/i' => 'Wget',
        '/googlebot/i' => 'Googlebot',
    );
    foreach ($osPatterns as $pattern => $os) {
        if (preg_match($pattern, $userAgent)) {
            return $os;
        }
    }   
    return '未知系统';
}

//获取来源
function get_clienttype(){
    return request()->header('from-clienttype') != null? request()->header('from-clienttype'):'unknow';
}

//获取指定长度的随机字符串
function get_rand_str($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

//获取随机昵称
function get_rand_nickname($prefix='昵称'){
    return $prefix.rand(100000,999999);
}