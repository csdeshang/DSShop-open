<?php

/**
 * 买家
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
class BaseMember extends BaseHome {

    protected $member_info = array();   // 会员信息

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/basemember.lang.php');
        /*非登录状态*/
        if (!session('is_login')) {
            
                $ref_url = request_uri();
                session('ref_url',$ref_url);
                $this->redirect(HOME_SITE_URL . '/Login/login.html');
    
        }
        //会员中心模板路径
        $this->template_dir = 'default/member/' . strtolower(request()->controller()) . '/';
        $this->member_info = $this->getMemberAndGradeInfo(true);
        if($this->member_info['member_nickname'] != session('member_nickname')){
            session('member_nickname',$this->member_info['member_nickname']);
        }
        View::assign('member_info', $this->member_info);
    }

    /**
     *    当前选中的栏目
     */
    protected function setMemberCurItem($curitem = '') {
        View::assign('member_item', $this->getMemberItemList());
        View::assign('curitem', $curitem);
    }

    /**
     *    当前选中的子菜单
     */
    protected function setMemberCurMenu($cursubmenu = '') {
        $member_menu = $this->getMemberMenuList();
        View::assign('member_menu', $member_menu);
        $curmenu = '';
        foreach ($member_menu as $key => $menu) {
            foreach ($menu['submenu'] as $subkey => $submenu) {
                if ($submenu['name'] == $cursubmenu) {
                    $curmenu = $menu['name'];
                    $nav = $submenu['text'];
                }
            }
        }
        
        // 面包屑
        $nav_link = array();
        $nav_link[] = array('title' => lang('ds_index'), 'link' => HOME_SITE_URL);
        if ($curmenu == '') {
            $nav_link[] = array('title' => lang('ds_user_center'));
        } else {
            $nav_link[] = array('title' =>  lang('ds_user_center'), 'link' => url('Member/index'));
            $nav_link[] = array('title' => $nav);
        }


        View::assign('nav_link_list', $nav_link);


        //当前一级菜单
        View::assign('curmenu', $curmenu);
        //当前二级菜单
        View::assign('cursubmenu', $cursubmenu);
    }

    /*
     * 获取卖家栏目列表,针对控制器下的栏目
     */

    protected function getMemberItemList() {
        return array();
    }

    /*
     * 获取卖家菜单列表
     */

    private function getMemberMenuList() {
        $menu_list = array(
            'trade' =>
            array(
                'name' => 'trade',
                 'ico' => '&#xe71f;',
                'text' => lang('ds_trade_manage'),
                'url' => url('Memberorder/index'),
                'submenu' => array(
                    array('name' => 'member_order', 'text' => lang('ds_real_order'), 'url' => url('Memberorder/index'),),
                    array('name' => 'member_vr_order', 'text' =>lang('ds_virtual_orders'), 'url' => url('Membervrorder/index'),),
                    array('name' => 'member_arrivalnotice', 'text' => lang('ds_arrivalnotice'), 'url' => url('MemberArrivalnotice/index'),),
                    array('name' => 'member_evaluate', 'text' => lang('ds_trading_evaluation'), 'url' => url('Memberevaluate/index'),),
                    array('name' => 'member_pointorder', 'text' => lang('ds_member_pointorder'), 'url' => url('Memberpointorder/index'),),
                )
            ),
            'info' =>
            array(
                'name' => 'info',
                'ico' => '&#xe702;',
                'text' => lang('ds_info_management'),
                'url' => url('Memberinformation/index'),
                'submenu' => array(
                    array('name' => 'member_information', 'text' => lang('ds_account_information'), 'url' => url('Memberinformation/index'),),
                    array('name' => 'member_security', 'text' =>lang('ds_account_security'), 'url' => url('Membersecurity/index'),),
                    array('name' => 'member_auth', 'text' =>lang('member_auth'), 'url' => url('MemberAuth/index'),),
                    array('name' => 'member_invoice', 'text' => lang('ds_member_invoice'), 'url' => url('Memberinvoice/index'),),
                    array('name' => 'member_address', 'text' => lang('ds_member_path_address'), 'url' => url('Memberaddress/index'),),
                    array('name' => 'member_bank', 'text' => lang('ds_member_path_bank'), 'url' => url('Memberbank/index'),),
                    array('name' => 'member_message', 'text' => lang('ds_my_news'), 'url' => url('Membermessage/message'),),
                    array('name' => 'member_favorites', 'text' => lang('ds_favorites'), 'url' => url('Memberfavorites/fglist'),),
                    array('name' => 'member_goodsbrowse', 'text' => lang('ds_my_footprint'), 'url' => url('Membergoodsbrowse/listinfo'),),
                    array('name' => 'member_connect', 'text' => lang('ds_third_party_account_login'), 'url' => url('Memberconnect/qqbind'),),
                )
            ),
            'assets' =>
            array(
                'name' => 'assets',
                'ico' => '&#xe65c;',
                'text' => lang('ds_assets_management'),
                'url' => url('Memberinformation/index'),
                'submenu' => array(
                    array('name' => 'predeposit', 'text' => lang('ds_account_balance'), 'url' => url('Predeposit/index'),),
                    array('name' => 'member_points', 'text' => lang('ds_my_points'), 'url' => url('Memberpoints/index'),),
                    array('name' => 'member_voucher', 'text' => lang('ds_member_path_myvoucher'), 'url' => url('Membervoucher/index'),),
                )
            ),
            'server' =>
            array(
                'name' => 'server',
                 'ico' => '&#xe73f;',
                'text' => lang('ds_customer_service'),
                'url' => url('Memberrefund/index'),
                'submenu' => array(
                    array('name' => 'member_refund', 'text' => lang('ds_refund_and_return'), 'url' => url('Memberrefund/index'),),
                    array('name' => 'member_consult', 'text' => lang('ds_commodity_consulting'), 'url' => url('Memberconsult/index'),),
                    array('name' => 'member_inform', 'text' => lang('ds_violation_to_report'), 'url' => url('Memberinform/index'),),
                    array('name' => 'member_mallconsult', 'text' => lang('ds_platform_for_customer_service'), 'url' => url('Membermallconsult/index'),),
                    array('name' => 'member_feedback', 'text' => lang('ds_feed_back'), 'url' => url('Memberfeedback/index'),),
                )
            )

        );
        if(config('ds_config.inviter_open')){
            //查看是否已是分销会员
            $inviter_model=model('inviter');
            $inviter_info=$inviter_model->getInviterInfo('i.inviter_id='.session('member_id'));
            if($inviter_info && $inviter_info['inviter_state']==1){
            $menu_list['inviter']=array(
                'name' => 'inviter',
                 'ico' => '&#xe6ed;',
                'text' => lang('ds_member_distribution'),
                'url' => url('Memberinviter/index'),
                'submenu' => array(
                    array('name' => 'inviter_poster', 'text' => lang('ds_distribution_information'), 'url' => url('Memberinviter/index'),),
                    array('name' => 'inviter_user', 'text' => lang('ds_distribution_member'), 'url' => url('Memberinviter/user'),),
                    array('name' => 'inviter_order', 'text' => lang('ds_distribution_commission'), 'url' => url('Memberinviter/order'),),
                )
            );
            }else{
            $menu_list['inviter']=array(
                'name' => 'inviter',
                 'ico' => '&#xe6ed;',
                'text' => lang('ds_member_distribution'),
                'url' => url('Memberinviter/add'),
                'submenu' => array(
                    array('name' => 'inviter_add', 'text' => lang('ds_become_member'), 'url' => url('Memberinviter/add'),),
                )
            );
            }

        }
        return $menu_list;
    }
}

?>
