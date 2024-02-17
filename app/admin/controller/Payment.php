<?php

namespace app\admin\controller;
use think\facade\View;
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
 * 控制器
 */
class Payment extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/payment.lang.php');
    }

    /**
     * 支付方式
     */
    public function index() {
        $payment_model = model('payment');
        //获取数据库中已安装的支付方式
        $install_payment_list = $payment_model->getPaymentList(array(array('payment_code','<>', 'predeposit')));
        $install_payment_list = ds_change_arraykey($install_payment_list,'payment_code');
        //获取已存在的支付列表文件
        $file_payment_list = $payment_model->get_builtin();
        
        $payment_platform = input('param.payment_platform');
        if(!in_array($payment_platform, array('pc','h5','app'))){
            $payment_platform = 'pc';
        }
        
        foreach ($file_payment_list as $key => $value)
        {
            if($value['payment_platform'] != $payment_platform){
                unset($file_payment_list[$key]);
                continue;
            }
            if(array_key_exists($key, $install_payment_list)){
                $file_payment_list[$key]['install'] = 1;
                //已安装的支付，配置信息使用数据库中配置信息
                $file_payment_list[$key]['payment_config'] = $install_payment_list[$key]['payment_config'];
                $file_payment_list[$key]['payment_state'] = $install_payment_list[$key]['payment_state'];
            }else{
                $file_payment_list[$key]['install'] = 0;
                $file_payment_list[$key]['payment_state'] = 0;
            }
        }

        View::assign('payment_list', $file_payment_list);
        $this->setAdminCurItem('index_'.$payment_platform);
        return View::fetch();
    }
    /**
     * 安装支付方式
     */
    function install()
    {
        $payment_code = input('param.payment_code');
        $payment_mod = model('payment');
        //如果是小程序支付、微信JS支付、微信H5支付、微信APP支付则必须先开启微信扫码支付
        if(in_array($payment_code,array('wxpay_minipro','wxpay_jsapi','wxpay_h5','wxpay_app'))){
            $payment=model('payment')->getPaymentInfo(array('payment_code'=>'wxpay_native'));
            if(empty($payment) || empty(unserialize($payment['payment_config']))){
                ds_json_encode('10001', '请先开启并配置微信扫码支付');
            }
        }
        //如果是支付宝H5支付则开启支付宝支付
        if(in_array($payment_code,array('alipay_h5'))){
            $payment=model('payment')->getPaymentInfo(array('payment_code'=>'alipay'));
            if(empty($payment) || empty(unserialize($payment['payment_config']))){
                ds_json_encode('10001', '请先开启并配置支付宝PC支付');
            }
        }
        
        $payment = model('payment')->getPaymentInfo(array('payment_code'=>$payment_code));
        if (empty($payment)) {
            $file_payment = include_once(PLUGINS_PATH . '/payments/' . $payment_code . '/payment.info.php');
            $data['payment_code'] = $file_payment['payment_code'];
            $data['payment_name'] = $file_payment['payment_name'];
            $data['payment_state'] = 1;
            $data['payment_platform'] = $file_payment['payment_platform'];
            $data['payment_config'] = serialize(array());
            $resutlt = $payment_mod->addPayment($data);
            if($resutlt){
                ds_json_encode('10000', lang('ds_common_op_succ'));
            }else{
                ds_json_encode('10001', lang('ds_common_op_fail'));
            }
        } else {
            ds_json_encode('10001', lang('ds_common_op_fail'));
        }
    }
    /**
     * 编辑
     */
    public function edit() {
        $payment_model = model('payment');
        $payment_code = trim(input('param.payment_code'));
        if (!(request()->isPost())) {
            $install_payment = $payment_model->getPaymentInfo(array('payment_code' => $payment_code));
            $file_payment = include_once(PLUGINS_PATH . '/payments/' . $install_payment['payment_code'] . '/payment.info.php');

            if(is_array($file_payment['payment_config'])){
                $install_payment_config = unserialize($install_payment['payment_config']);
                unset($install_payment['payment_config']);
                foreach ($file_payment['payment_config'] as $key => $value){
                    $install_payment['payment_config'][$key]['name'] = $value['name'];
                    $install_payment['payment_config'][$key]['type'] = $value['type'];
                    $install_payment['payment_config'][$key]['desc'] = lang($value['name'].'_desc');
                    $install_payment['payment_config'][$key]['lable'] = lang($value['name']);
                    $install_payment['payment_config'][$key]['value'] = isset($install_payment_config[$value['name']])?$install_payment_config[$value['name']]:$value['value'];
                }
            }
            View::assign('payment', $install_payment);
            return View::fetch();
        } else {
            $data = array();
            $data['payment_state'] = intval(input('post.payment_state'));
            $config_info = array();

            $cfg_value_array = input('post.cfg_value/a');#获取数组
            $cfg_name_array = input('post.cfg_name/a');#获取数组
            if (is_array($cfg_value_array) && !empty($cfg_value_array)){
                foreach ($cfg_value_array as $i => $v){
                    $config_info[trim($cfg_name_array[$i])] = trim($cfg_value_array[$i]);
                }
            }
            $data['payment_config'] = serialize($config_info);
            $payment_model->editPayment($data, array('payment_code' => $payment_code));
            dsLayerOpenSuccess(lang('ds_common_op_succ'));
//            $this->success(lang('ds_common_op_succ'), 'Payment/index');
        }
    }

    /**
     * 删除支付方式,卸载
     */
    public function del()
    {
        $payment_model = model('payment');
        $payment_code = trim(input('param.payment_code'));
        $condition = array();
        $condition[] = array('payment_code','=',$payment_code);
        $result = $payment_model->delPayment($condition);
        if($result){
            ds_json_encode('10000', lang('ds_common_op_succ'));
        }else{
            ds_json_encode('10001', lang('ds_common_op_fail'));
        }
    }



    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index_pc',
                'text' => lang('payment_index_pc'),
                'url' => url('Payment/index')
            ),
            array(
                'name' => 'index_h5',
                'text' => lang('payment_index_h5'),
                'url' => url('Payment/index',['payment_platform'=>'h5'])
            ),
            array(
                'name' => 'index_app',
                'text' => lang('payment_index_app'),
                'url' => url('Payment/index',['payment_platform'=>'app'])
            ),
        );
        return $menu_array;
    }

}

?>
