<?php
/**
 * 验证码
 *
 */

namespace app\home\controller;


use app\BaseController;
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
 * 控制器
 */
class Seccode extends BaseController
{
    /**
     *产生验证码
     */
    public function makecode()
    {
        $config = [
            'fontSize' => 20, // // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false,//是否添加杂点
            'useCurve' =>true,
            'imageH' => 50,//高度
            'imageW' => 150,
        ];
        config($config,'captcha');
        $captcha = Captcha::create();
        return $captcha;
    }

    /**
     * AJAX验证
     */
    public function check()
    {
        $config=[];
        if(input('param.reset')=='false'){
            //验证成功之后,验证码是否失效,验证成功后是否重置
            $config['reset'] = FALSE;
        }
        config($config,'captcha');
        $code = input('param.captcha');
        if (captcha_check($code)) {
            exit('true');
        }
        else {
            exit('false');
        }
    }
}