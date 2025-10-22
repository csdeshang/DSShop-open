<?php


function mobile_page($page_info) {
    //输出是否有下一页
    $extend_data = array();
    if($page_info==''){
        $extend_data['page_total']=1;
        $extend_data['hasmore'] = false;
    }else {
        $current_page = $page_info->currentPage();
        if ($current_page <= 0) {
            $current_page = 1;
        }
        if ($current_page >= $page_info->lastPage()) {
            $extend_data['hasmore'] = false;
        }
        else {
            $extend_data['hasmore'] = true;
        }
        $extend_data['page_total'] = $page_info->lastPage();
    }
    return $extend_data;
}

/**
 * 获取手机端访问使用的浏览器
 */
function get_device_type(){
    //全部变成小写字母
    $agent= strtolower($_SERVER['HTTP_USER_AGENT']);
    if(strpos($agent,'miniprogram')){
        return 'miniprogram';
    }
    if(strpos($agent,'micromessenger')){
        return 'micromessenger';
    }
    if(strpos($agent,'android')){
        return 'android';
    }
    if(strpos($agent,'iphone') || strpos($agent,'ipad')){
        return 'ios';
    }
    return 'other';
}

