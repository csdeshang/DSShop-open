<?php
namespace app\api\controller;
use think\facade\Db;
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
 * 登录控制器
 */
class Login extends MobileMall
{

    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/login.lang.php');
    }

    /**
     * @api {POST} api/Login/index 用户登录
     * @apiVersion 3.0.6
     * @apiGroup Login
     *
     * @apiParam {String} username 用户名
     * @apiParam {String} password 密码
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {String} result.token  用户token
     * @apiSuccess {Object} result.info 用户信息
     * @apiSuccess {Int} result.info.member_id  用户ID
     * @apiSuccess {String} result.info.member_name  用户名称
     * @apiSuccess {String} result.info.member_truename  真实姓名
     * @apiSuccess {String} result.info.member_avatar  头像
     * @apiSuccess {Int} result.info.member_points  积分
     * @apiSuccess {String} result.info.member_email  邮箱
     * @apiSuccess {String} result.info.member_mobile  手机号
     * @apiSuccess {String} result.info.member_qq  QQ
     * @apiSuccess {String} result.info.member_ww  旺旺
     */
    public function index()
    {
        $username = input('param.username');
        $password = input('param.password');
        $prefix = 'login-times';
        $ip=request()->ip();
        $data = rkcache($prefix.$ip);
        if(!empty($data) && $data['times']>20){
            ds_json_encode(10001, lang('frequent_operation'));
        }
        
        
        if (empty($username) || empty($password)) {
            ds_json_encode(10001,'登录失败');
        }
//        if (config('ds_config.captcha_status_login') == 1 && !captcha_check(input('post.captcha'))) {
//            ds_json_encode(10001, lang('image_verification_code_error'));
//        }
        $member_model = model('member');

        $array = array();
        $array['member_name'] = $username;
        $array['member_password'] = md5($password);
        $member_info = $member_model->getMemberInfo($array);
        if (empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $username)) {//根据会员名没找到时查手机号
            $array = array();
            $array['member_mobile'] = $username;
            $array['member_mobilebind'] = 1;//已绑定了的手机
            $array['member_password'] = md5($password);
            $member_info = $member_model->getMemberInfo($array);
        }

        if (empty($member_info) && (strpos($username, '@') > 0)) {//按邮箱和密码查询会员
            $array = array();
            $array['member_email'] = $username;
            $array['member_password'] = md5($password);
            $member_info = $member_model->getMemberInfo($array);
        }

        if (is_array($member_info) && !empty($member_info)) {
            if (!$member_info['member_state']) {
                ds_json_encode(10001, lang('login_index_account_stop'));
            }
            //执行登录,赋值操作
            $member_model->createSession($member_info,'login');
            $this->getUserToken($member_info);
        }
        else {
            if(empty($data)){
                $data=array('times'=>0);
            }
            $data['times']++;
            wkcache($prefix.$ip, $data, 3600*24);
            ds_json_encode(10001,'用户名密码错误');
        }
    }
    
    
    
    public function get_inviter(){
        $inviter_id=intval(input('param.inviter_id'));
        $member=Db::name('member')->where('member_id',$inviter_id)->field('member_id,member_name')->find();
        ds_json_encode(10000, '',array('member' => $member));
    }
   


    /**
     * @api {POST} api/Login/register 普通注册
     * @apiVersion 3.0.6
     * @apiGroup Login
     *
     * @apiParam {String} username 用户名
     * @apiParam {String} password 密码
     * @apiParam {String} password_confirm 确认密码
     * @apiParam {Int} inviter_id 推荐人id
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.userid  用户ID
     * @apiSuccess {String} result.username  用户名称
     * @apiSuccess {String} result.token  用户token
     * @apiSuccess {Object} result.info 用户信息
     * @apiSuccess {Int} result.info.member_id  用户ID
     * @apiSuccess {Object} result.info.member_name  用户名称
     * @apiSuccess {Object} result.info.member_truename  真实姓名
     * @apiSuccess {Object} result.info.member_avatar  头像
     * @apiSuccess {Object} result.info.member_points  积分
     * @apiSuccess {Object} result.info.member_email  邮箱
     * @apiSuccess {Object} result.info.member_mobile  手机号
     * @apiSuccess {Object} result.info.member_qq  QQ
     * @apiSuccess {Object} result.info.member_ww  旺旺
     */
    public function register()
    {
        if(config('ds_config.member_normal_register')!=1){
            ds_json_encode(10001,lang('login_register_cancel'));
        }
        $username = trim(input('param.username'));
        $password = input('param.password');
        $password_confirm = input('param.password_confirm');
        $inviter_id = intval(input('param.inviter_id'));
	if($password_confirm!=$password){
            ds_json_encode(10001,'密码不一致');
        }
//        if (config('ds_config.captcha_status_register') == 1 && !captcha_check(input('post.captcha'))) {
//            ds_json_encode(10001,lang('image_verification_code_error'));
//        }
        $member_model = model('member');
        $register_info = array();
        $register_info['member_name'] = $username;
        $register_info['member_password'] = $password;
        
        $res=word_filter($register_info['member_name']);
        if(!$res['code']){
            ds_json_encode(10001,$res['msg']);
        }
        if($res['data']['if_sensitive']){
            ds_json_encode(10001,implode('、',$res['data']['sensitive_msg']));
        }
        //添加奖励积分
        if($inviter_id){
            $register_info['inviter_id'] = $inviter_id;
        }
        $member_info = $member_model->register($register_info);
        if (!isset($member_info['error'])) {
            $token = $member_model->getBuyerToken($member_info['member_id'], $member_info['member_name']);
            if ($token) {
                ds_json_encode(10000, '',array('info'=>$this->getMemberUser($member_info),'username' => $member_info['member_name'], 'userid' => $member_info['member_id'],'token' => $token));
            }
            else {
                ds_json_encode(10001,'注册失败');
            }
        }
        else {
            ds_json_encode(10001,$member_info['error']);
        }
    }
    


}

?>
