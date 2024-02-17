<?php
namespace app\home\controller;
use think\facade\View;
/**
 * 积分中心control父类
 */
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
class BasePointShop extends BaseHome {
    protected $member_info;
    public function initialize() {
        parent::initialize();
        //输出会员信息
        $this->member_info = $this->getMemberAndGradeInfo(true);
        View::assign('member_info', $this->member_info);
        
        //判断系统是否开启积分和积分中心功能
        if (config('ds_config.points_isuse') != 1 || config('ds_config.pointshop_isuse') != 1) {
            $this->error(lang('pointshop_unavailable'), url('Index/index'));
        }
        $this->template_dir = 'default/member/' . strtolower(request()->controller()) . '/';
        View::assign('index_sign', 'pointshop');
    }

    /**
     * 获得积分中心会员信息包括会员名、ID、会员头像、会员等级、经验值、等级进度、积分、已领代金券、已兑换礼品、礼品购物车
     */
    public function pointshopMInfo($is_return = false) {
        if (session('is_login') == '1') {
            $member_model = model('member');
            if (!$this->member_info) {
                //查询会员信息
                $member_infotmp = $member_model->getMemberInfoByID(session('member_id'));
            } else {
                $member_infotmp = $this->member_info;
            }
            $member_infotmp['member_exppoints'] = intval($member_infotmp['member_exppoints']);

            //当前登录会员等级信息
            $membergrade_info = $member_model->getOneMemberGrade($member_infotmp['member_exppoints'], true);
            $member_info = array_merge($member_infotmp, $membergrade_info);
            View::assign('member_info', $member_info);

            //查询已兑换并可以使用的代金券数量
            $voucher_model = model('voucher');
            $vouchercount = $voucher_model->getCurrentAvailableVoucherCount(session('member_id'));
            View::assign('vouchercount', $vouchercount);

            //购物车兑换商品数
            $pointcart_count = model('pointcart')->getPointcartCount(session('member_id'));
            View::assign('pointcart_count', $pointcart_count);

            //查询已兑换商品数(未取消订单)
            $pointordercount = model('pointorder')->getMemberPointsOrderGoodsCount(session('member_id'));
            View::assign('pointordercount', $pointordercount);
            if ($is_return) {
                return array('member_info' => $member_info, 'vouchercount' => $vouchercount, 'pointcart_count' => $pointcart_count, 'pointordercount' => $pointordercount);
            }
        }
    }

}
