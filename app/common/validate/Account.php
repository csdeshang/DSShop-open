<?php

namespace app\common\validate;


use think\Validate;
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
 * 验证器
 */
class Account extends Validate
{
    protected $rule = [
        'qq_appid'=>'require',
        'qq_appkey'=>'require',
        'sina_wb_akey'=>'require',
        'sina_wb_skey'=>'require',
        'weixin_appid'=>'require',
        'weixin_secret'=>'require'
    ];
    protected $message = [
        'qq_appid.require'=>'请添加应用标识',
        'qq_appkey.require'=>'请添加应用密钥',
        'sina_wb_akey.require'=>'请添加应用标识',
        'sina_wb_skey.require'=>'请添加应用密钥',
        'weixin_appid.require'=>'请添加应用标识',
        'weixin_secret.require'=>'请添加应用密钥'
    ];
    protected $scene = [
        'qq' => ['qq_appid', 'qq_appkey'],
        'sina' => ['sina_wb_akey', 'sina_wb_skey'],
        'wx' => ['weixin_appid', 'weixin_secret']
    ];
}