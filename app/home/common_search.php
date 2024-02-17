<?php

/**
 * 删除地址参数
 *
 * @param array $param
 */
function dropParam($param) {
    $purl = getParam();
    if (!empty($param)) {
        foreach ($param as $val) {
            $purl['param'][$val] = 0;
        }
    }
    return urldecode(url('home/'.request()->controller().'/'.request()->action(),$purl['param']));
}

/**
 * 替换地址参数
 *
 * @param array $param
 */
function replaceParam($param) {
    $purl = getParam();
    if (!empty($param)) {
        foreach ($param as $key => $val) {
            $purl['param'][$key] = $val;
        }
    }

    return urldecode(url('home/'.request()->controller().'/'.request()->action(),$purl['param']));
}

/**
 * 替换并删除地址参数
 *
 * @param array $param
 */
function replaceAndDropParam($paramToReplace, $paramToDrop) {
    $purl = getParam();
    if (!empty($paramToReplace)) {
        foreach ($paramToReplace as $key => $val) {
            $purl['param'][$key] = $val;
        }
    }
    if (!empty($paramToDrop)) {
        foreach ($paramToDrop as $val) {
            $purl['param'][$val] = 0;
        }
    }

    return urldecode(url('home/'.request()->controller().'/'.request()->action(),$purl['param']));
}

/**
 * 删除部分地址参数
 *
 * @param array $param
 */
function removeParam($param) {
    $purl = getParam();
    if (!empty($param)) {
        foreach ($param as $key => $val) {
            if (!isset($purl['param'][$key])) {
                continue;
            }
            $tpl_params = explode('_', $purl['param'][$key]);
            foreach ($tpl_params as $k => $v) {
                if ($val == $v) {
                    unset($tpl_params[$k]);
                }
            }
            if (empty($tpl_params)) {
                $purl['param'][$key] = 0;
            } else {
                $purl['param'][$key] = implode('_', $tpl_params);
            }
        }
    }
    return urldecode(url('home/'.request()->controller().'/'.request()->action(),$purl['param']));
}

function getParam() {
    $param = input('param.');
    $purl = array();
    unset($param['page']);
    $param=str_replace('/','+',$param);
    SafeFilter($param);
    $purl['param'] = $param;
    return $purl;
}
function SafeFilter (&$arr) 
{
     
   $ra=Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');
     
   if (is_array($arr))
   {
     foreach ($arr as $key => $value) 
     {
       $new_key=$key;
        if (!is_array($value))
        {
          if (!get_magic_quotes_gpc())//不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
          {
            $new_key=addslashes($new_key);
             $value  = addslashes($value); //给单引号（'）、双引号（"）、反斜线（\）与NUL（NULL字符）加上反斜线转义
          }
          $new_key=preg_replace($ra,'',$new_key);
          $value       = preg_replace($ra,'',$value);     //删除非打印字符，粗暴式过滤xss可疑字符串
          $new_key=htmlentities(strip_tags($new_key));
          unset($arr[$key]);
          $arr[$new_key]     = htmlentities(strip_tags($value)); //去除 HTML 和 PHP 标记并转换为HTML实体
        }
        else
        {
          SafeFilter($arr[$key]);
        }
     }
   }
}
?>
