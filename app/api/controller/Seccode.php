<?php

namespace app\api\controller;

use think\captcha\facade\Captcha;

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
 * 图片验证码控制器
 */
class Seccode extends MobileMall{


    /**
     * 产生验证码
     * type 验证码传入可标识的信息  
     */
    public function makecode() {
        $config =    [
            // 验证码字体大小
            'fontSize'    => 40,
            // 验证码位数
            'length'      =>  4,
            // 关闭验证码杂点
            'useNoise'    =>    false,
        ];
        config($config,'captcha');
        $captcha = Captcha::create();
        return $captcha;
    }
    /**
     * @api {POST} api/Seccode/check 检查验证码
     * @apiVersion 3.0.6
     * @apiGroup Seccode
     *
     * @apiParam {String} captcha 验证码
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     */
    public function check() {
        $captch=input('param.captcha');
        if(captcha_check($captch)){
           ds_json_encode(10000, '');
        } else {            
            ds_json_encode(10001,'验证码错误',['code'=>'']);
        }
    }

}