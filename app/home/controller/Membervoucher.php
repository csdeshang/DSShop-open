<?php
/**
 *    代金券
 */
namespace app\home\controller;
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
class Membervoucher extends BaseMember
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        Lang::load(base_path().'home/lang/'.config('lang.default_lang').'/member_voucher.lang.php');
        //判断系统是否开启代金券功能
        if (intval(config('ds_config.voucher_allow')) !== 1){
            $this->error(lang('voucher_unavailable'));
        }
    }
    /*
	 * 默认显示代金券模版列表
	 */
    public function index() {
        $voucher_model = model('voucher');
        $voucher_list = $voucher_model->getMemberVoucherList(session('member_id'), input('param.select_detail_state'), 10);

        //取已经使用过并且未有voucher_order_id的代金券的订单ID
        $used_voucher_code = array();
        $voucher_order = array();
        if (!empty($voucher_list)) {
            foreach ($voucher_list as $v) {
                if ($v['voucher_state'] == 2 && empty($v['voucher_order_id'])) {
                    $used_voucher_code[] = $v['voucher_code'];
                }
            }
        }
        if (!empty($used_voucher_code)) {
            $order_list = model('order')->getOrdercommonList(array(array('voucher_code','in',$used_voucher_code)),'order_id,voucher_code');
            if (!empty($order_list)) {
                foreach ($order_list as $v) {
                    $voucher_order[$v['voucher_code']] = $v['order_id'];
                    $voucher_model->editVoucher(array('voucher_order_id'=>$v['order_id']),array('voucher_code'=>$v['voucher_code']));
                }
            }
        }

        View::assign('voucher_list', $voucher_list);
        View::assign('voucherstate_arr', $voucher_model->getVoucherStateArray());
        View::assign('show_page',$voucher_model->page_info->render()) ;
        $this->setMemberCurItem('voucher_list');
        $this->setMemberCurMenu('member_voucher');
        return View::fetch($this->template_dir.'member_voucher_list');
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string	$menu_type	导航类型
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    protected function getMemberItemList()
    {
       $menu_array=array(
           array(
               'name'=>'voucher_list','text'=>lang('ds_myvoucher'),'url'=>url('Membervoucher/index')
           )
       );
       return $menu_array;
    }


}