<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Lang;
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
class Login extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/login.lang.php');
    }

    public function index() {
        if (session('admin_id')) {
            $this->success('已经登录', 'Index/index');
        }
        if (request()->isPost()) {

            $admin_name = input('post.admin_name');
            $admin_password = input('post.admin_password');
            $captcha = input('post.captcha');

            $data = array(
                'admin_name' => $admin_name,
                'admin_password' => $admin_password,
                'captcha' => $captcha,
            );

            //验证数据  BEGIN
            $login_validate = ds_validate('admin');
            if (!$login_validate->scene('index')->check($data)) {
                ds_json_encode(10001,$login_validate->getError());
            }
            //验证数据  END
            if (!captcha_check(input('post.captcha'))) {
                //验证失败
                ds_json_encode(10001,'验证码错误');
            }
            $condition = array();
            $condition[] = array('admin_name','=',$admin_name);
            $condition[] = array('admin_password','=',md5($admin_password));
            $admin_mod=model('admin');
            $admin_info = $admin_mod->getOneAdmin($condition);

            if (is_array($admin_info) and !empty($admin_info)) {
                //更新 admin 最新信息
                $update_info = array(
                    'admin_login_num' => ($admin_info['admin_login_num'] + 1),
                    'admin_login_time' => TIMESTAMP
                );
                $admin_mod->editAdmin($update_info, $admin_info['admin_id']);

                //设置 session
                session('admin_id', $admin_info['admin_id']);
                session('admin_name', $admin_info['admin_name']);
                session('admin_gid', $admin_info['admin_gid']);
                session('admin_is_super', $admin_info['admin_is_super']);
                ds_json_encode(10000,'登录成功', '','',false);
            } else {
                ds_json_encode(10001,'帐号密码错误');
            }
        } else {
            return View::fetch();
        }
    }

    public function logout() {
        //设置 session
        session(null);
        ds_json_encode(10000,'退出成功', '','',false);
    }
    
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

}

?>
