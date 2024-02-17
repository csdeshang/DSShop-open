<?php

namespace app\admin\controller;
use think\facade\View;
use app\BaseController;
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
class AdminControl extends BaseController {

    /**
     * 管理员资料 name id group
     */
    protected $admin_info;

    protected $permission;
    public function initialize() {
        $config_list = rkcache('config', true);
        config($config_list,'ds_config');
        
        if(request()->controller()!='Login'){
            $this->admin_info = $this->systemLogin();

            if ($this->admin_info['admin_id'] != 1) {
                // 验证权限
                $this->checkPermission();
            }
            $this->setMenuList();
        }
    }

    /**
     * 取得当前管理员信息
     *
     * @param
     * @return 数组类型的返回结果
     */
    protected final function getAdminInfo() {
        return $this->admin_info;
    }

    /**
     * 系统后台登录验证
     *
     * @param
     * @return array 数组类型的返回结果
     */
    protected final function systemLogin() {
        $admin_info = array(
            'admin_id' => session('admin_id'),
            'admin_name' => session('admin_name'),
            'admin_gid' => session('admin_gid'),
            'admin_is_super' => session('admin_is_super'),
        );
        if(input('post.PHPSESSID') && !session('admin_id') && request()->controller() == 'TaobaoImport' && request()->action() == 'upload'){
          $this->app->session->setId(input('post.PHPSESSID'));
          $this->app->session->init();
          session('limited', 1);
        }
        if(session('limited') && (request()->controller() != 'TaobaoImport' || request()->action() != 'upload')){
          $this->redirect('Login/index');
        }
        if (empty($admin_info['admin_id']) || empty($admin_info['admin_name']) || !isset($admin_info['admin_gid']) || !isset($admin_info['admin_is_super'])) {
            session(null);
            $this->redirect('Login/index');
        }

        return $admin_info;
    }

    public function setMenuList() {
        $menu_list = $this->menuList();

        $menu_list=$this->parseMenu($menu_list);
        View::assign('menu_list', $menu_list);
    }

    /**
     * 验证当前管理员权限是否可以进行操作
     *
     * @param string $link_nav
     * @return
     */
    protected final function checkPermission($link_nav = null){
        if ($this->admin_info['admin_is_super'] == 1) return true;

        $controller = request()->controller();
        $action = request()->action();
        if (empty($this->permission)){
            
            $admin_model=model('admin');
            $gadmin = $admin_model->getOneGadmin(array('gid'=>$this->admin_info['admin_gid']));
            
            $permission = ds_decrypt($gadmin['glimits'],MD5_KEY.md5($gadmin['gname']));
            $this->permission = $permission = explode('|',$permission);
        }else{
            $permission = $this->permission;
        }
        //显示隐藏小导航，成功与否都直接返回
        if (is_array($link_nav)){
            if (!in_array("{$link_nav['controller']}.{$link_nav['action']}",$permission) && !in_array($link_nav['controller'],$permission)){
                return false;
            }else{
                return true;
            }
        }
        //以下几项不需要验证
        $tmp = array('Index','Dashboard','Login');
        if (in_array($controller,$tmp)){
            return true;
        }
        if (in_array($controller,$permission) || in_array("$controller.$action",$permission)){
            return true;
        }else{
            $extlimit = array('ajax','export_step1');
            if (in_array($action,$extlimit) && (in_array($controller,$permission) || strpos(serialize($permission),'"'.$controller.'.'))){
                return true;
            }
            //带前缀的都通过
            foreach ($permission as $v) {
                if (!empty($v) && strpos("$controller.$action",$v.'_') !== false) {
                    return true;break;
                }
            }
        }
        $this->error(lang('ds_assign_right'),'Dashboard/welcome');
    }

    /**
     * 过滤掉无权查看的菜单
     *
     * @param array $menu
     * @return array
     */
    private final function parseMenu($menu = array()) {
        if ($this->admin_info['admin_is_super'] == 1) {
            return $menu;
        }
        foreach ($menu as $k => $v) {
            foreach ($v['children'] as $ck => $cv) {
                $tmp = explode(',', $cv['args']);
                //以下几项不需要验证
                $except = array('Index', 'Dashboard', 'Login');
                if (in_array($tmp[1], $except))
                    continue;
                if (!in_array($tmp[1], array_values($this->permission)) && !in_array($tmp[1].'.'.$tmp[0], array_values($this->permission))) {
                    if($this->admin_info['admin_name']!='dsshop'){
                        unset($menu[$k]['children'][$ck]);
                    }
                }
            }
            if (empty($menu[$k]['children'])) {
                unset($menu[$k]);
                unset($menu[$k]['children']);
            }
        }
        return $menu;
    }

    /**
     * 记录系统日志
     *
     * @param $lang 日志语言包
     * @param $state 1成功0失败null不出现成功失败提示
     * @param $admin_name
     * @param $admin_id
     */
    protected final function log($lang = '', $state = 1, $admin_name = '', $admin_id = 0) {
        if ($admin_name == '') {
            $admin_name = session('admin_name');
            $admin_id = session('admin_id');
        }
        $data = array();
        if (is_null($state)) {
            $state = null;
        } else {
            $state = $state ? '' : lang('ds_fail');
        }
        $data['adminlog_content'] = $lang . $state;
        $data['adminlog_time'] = TIMESTAMP;
        $data['admin_name'] = $admin_name;
        $data['admin_id'] = $admin_id;
        $data['adminlog_ip'] = request()->ip();
        $data['adminlog_url'] = request()->controller() . '&' . request()->action();
        
        $adminlog_model=model('adminlog');
        return $adminlog_model->addAdminlog($data);
    }

    /**
     * 添加到任务队列
     *
     * @param array $goods_array
     * @param boolean $ifdel 是否删除以原记录
     */
    protected function addcron($data = array(), $ifdel = false) {
        $cron_model = model('cron');
        if (isset($data[0])) { // 批量插入
            $where = array();
            foreach ($data as $k => $v) {
                // 删除原纪录条件
                if ($ifdel) {
                    $where[] = '(cron_type = "' . $data['cron_type'] . '" and cron_value = "' . $data['cron_value'] . '")';
                }
            }
            // 删除原纪录
            if ($ifdel) {
                $cron_model->delCron(implode(',', $where));
            }
            $cron_model->addCronAll($data);
        } else { // 单条插入
            // 删除原纪录
            if ($ifdel) {
                $cron_model->delCron(array('cron_type' => $data['cron_type'], 'cron_value' => $data['cron_value']));
            }
            $cron_model->addCron($data);
        }
    }

    /**
     * 当前选中的栏目
     */
    protected function setAdminCurItem($curitem = '') {
        View::assign('admin_item', $this->getAdminItemList());
        View::assign('curitem', $curitem);
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        return array();
    }

    /*
     * 侧边栏列表
     */

    function menuList() {
        return array(
            'dashboard' => array(
                'name' => 'dashboard',
                'text' => lang('ds_dashboard'),
                'show' => TRUE,
                'children' => array(
                    'welcome' => array(
                        'ico'=>"&#xe70b;",
                        'text' => lang('ds_welcome'),
                        'args' => 'welcome,Dashboard,dashboard',
                    ),
                    /*
                    'aboutus' => array(
                        'text' => lang('ds_aboutus'),
                        'args' => 'aboutus,dashboard,dashboard',
                    ),
                     */
                    'config' => array(
                        'ico'=>'&#xe6e0;',
                        'text' => lang('ds_base'),
                        'args' => 'base,Config,dashboard',
                    ),
                    'member' => array(
                        'ico'=>'&#xe667;',
                        'text' => lang('ds_member_manage'),
                        'args' => 'member,Member,dashboard',
                    ),
                ),
            ),
            'setting' => array(
                'name' => 'setting',
                'text' => lang('ds_set'),
                'show' => TRUE,
                'children' => array(
                    'config' => array(
                        'ico'=>'&#xe6e0;',
                        'text' => lang('ds_base'),
                        'args' => 'base,Config,setting',
                    ),
                    'account' => array(
                        'ico'=>'&#xe678;',
                        'text' => lang('ds_account'),
                        'args' => 'qq,Account,setting',
                    ),
                    'upload_set' => array(
                        'ico'=>'&#xe72a;',
                        'text' => lang('ds_upload_set'),
                        'args' => 'default_thumb,Upload,setting',
                    ),
                    'seo' => array(
                        'ico'=>'&#xe6e0;',
                        'text' => lang('ds_seo_set'),
                        'args' => 'index,Seo,setting',
                    ),
                    'message' => array(
                        'ico'=>'&#xe71b;',
                        'text' => lang('ds_message'),
                        'args' => 'email,Message,setting',
                    ),
                    'payment' => array(
                        'ico'=>'&#xe74d;',
                        'text' => lang('ds_payment'),
                        'args' => 'index,Payment,setting',
                    ),
                    'admin' => array(
                        'ico'=>'&#xe67b;',
                        'text' => lang('ds_admin'),
                        'args' => 'admin,Admin,setting',
                    ),
                    'express' => array(
                        'ico'=>'&#xe69e;',
                        'text' => lang('ds_express'),
                        'args' => 'index,Express,setting',
                    ),
                    'Region' => array(
                        'ico'=>'&#xe69e;',
                        'text' => lang('ds_region'),
                        'args' => 'index,Region,setting',
                    ),
                    'db' => array(
                        'ico'=>'&#xe6f5;',
                        'text' => lang('ds_db'),
                        'args' => 'db,Database,setting',
                    ),
                    'admin_log' => array(
                        'ico'=>'&#xe71f;',
                        'text' => lang('ds_adminlog'),
                        'args' => 'loglist,Adminlog,setting',
                    ),
                ),
            ),
            'member' => array(
                'name' => 'member',
                'text' => lang('ds_member'),
                'show' => TRUE,
                'children' => array(
                    'member' => array(
                        'ico'=>'&#xe667;',
                        'text' => lang('ds_member_manage'),
                        'args' => 'member,Member,member',
                    ),
                    'member_auth' => array(
                        'ico'=>'&#xe6ea;',
                        'text' => lang('member_auth'),
                        'args' => 'index,member_auth,member',
                    ),
                    'membergrade' => array(
                        'ico'=>'&#xe6a3;',
                        'text' => lang('ds_membergrade'),
                        'args' => 'index,Membergrade,member',
                    ),
                    'exppoints' => array(
                        'ico'=>'&#xe727;',
                        'text' => lang('ds_exppoints'),
                        'args' => 'index,Exppoints,member',
                    ),
                    'notice' => array(
                        'ico'=>'&#xe71b;',
                        'text' => lang('ds_notice'),
                        'args' => 'index,Notice,member',
                    ),
                    'points' => array(
                        'ico'=>'&#xe6f5;',
                        'text' => lang('ds_points'),
                        'args' => 'index,Points,member',
                    ),
                    'predeposit' => array(
                        'ico'=>'&#xe6e2;',
                        'text' => lang('ds_predeposit'),
                        'args' => 'pdrecharge_list,Predeposit,member',
                    ),
                ),
            ),
            'goods' => array(
                'name' => 'goods',
                'text' => lang('ds_goods'),
                'show' => TRUE,
                'children' => array(
                    'goodsclass' => array(
                        'ico'=>'&#xe652;',
                        'text' => lang('ds_goodsclass'),
                        'args' => 'goods_class,Goodsclass,goods',
                    ),
                    'Brand' => array(
                        'ico'=>'&#xe6b0;',
                        'text' => lang('ds_brand'),
                        'args' => 'index,Brand,goods',
                    ),
                    'Goods' => array(
                        'ico'=>'&#xe732;',
                        'text' => lang('ds_goods_manage'),
                        'args' => 'index,Goods,goods',
                    ),
                    'Type' => array(
                        'ico'=>'&#xe728;',
                        'text' => lang('ds_type'),
                        'args' => 'index,Type,goods',
                    ),
                    'Spec' => array(
                        'ico'=>'&#xe71d;',
                        'text' => lang('ds_spec'),
                        'args' => 'index,Spec,goods',
                    ),
                    'Arrivalnotice' => array(
                        'ico'=>'&#xe71b;',
                        'text' => lang('ds_arrivalnotice'),
                        'args' => 'index,Arrivalnotice,goods',
                    ),
                    'transport' => array(
                        'ico'=>'&#xe720;',
                        'text' => lang('ds_transport'),
                        'args' => 'index,Transport,goods',
                    ),
                    'album' => array(
                        'ico'=>'&#xe72a;',
                        'text' => lang('ds_album'),
                        'args' => 'index,Goodsalbum,goods',
                    ),
                ),
            ),
            'trade' => array(
                'name' => 'trade',
                'text' => lang('ds_trade'),
                'show' => TRUE,
                'children' => array(
                    'deliver' => array(
                        'ico'=>'&#xe69e;',
                        'text' => lang('ds_deliver'),
                        'args' => 'index,Deliver,trade',
                    ),
                    'order' => array(
                        'ico'=>'&#xe69c;',
                        'text' => lang('ds_order'),
                        'args' => 'index,Order,trade',
                    ),
                    'vrorder' => array(
                        'ico'=>'&#xe71f;',
                        'text' => lang('ds_vrorder'),
                        'args' => 'index,Vrorder,trade',
                    ),
                    'refund' => array(
                        'ico'=>'&#xe6f3;',
                        'text' => lang('ds_refund'),
                        'args' => 'refund_manage,Refund,trade',
                    ),
                    'return' => array(
                        'ico'=>'&#xe6f3;',
                        'text' => lang('ds_return'),
                        'args' => 'return_manage,Returnmanage,trade',
                    ),
                    'vrrefund' => array(
                        'ico'=>'&#xe6f3;',
                        'text' => lang('ds_vrrefund'),
                        'args' => 'refund_manage,Vrrefund,trade',
                    ),
                    'consulting' => array(
                        'ico'=>'&#xe71c;',
                        'text' => lang('ds_consulting'),
                        'args' => 'Consulting,Consulting,trade',
                    ),
                    'inform' => array(
                        'ico'=>'&#xe64a;',
                        'text' => lang('ds_inform'),
                        'args' => 'inform_list,Inform,trade',
                    ),
                    'evaluate' => array(
                        'ico'=>'&#xe6f2;',
                        'text' => lang('ds_evaluate'),
                        'args' => 'evalgoods_list,Evaluate,trade',
                    ),
                    'deliverset' => array(
                        'ico'=>'&#xe69e;',
                        'text' => '发货设置',
                        'args' => 'index,Deliverset,trade',
                    ),
//                    'transport' => array(
//                        'ico'=>'&#xe655;',
//                        'text' => '售卖区域',
//                        'args' => 'index,Transport,trade',
//                    ),
                ),
            ),
            'entityshop' => array(
                'name' => 'entityshop',
                'text' => lang('ds_entityshop'),
                'show' => TRUE,
                'children' => array(
                    'entityshop' => array(
                        'ico'=>'&#xe6ec;',
                        'text' => lang('ds_entityshop_manage'),
                        'args' => 'index,Entityshop,entityshop',
                    ),
                    'entityclerk' => array(
                        'ico'=>'&#xe6ec;',
                        'text' => lang('ds_entityclerk_manage'),
                        'args' => 'index,Entityclerk,entityshop',
                    ),
                ),
            ),
            'website' => array(
                'name' => 'website',
                'text' => lang('ds_website'),
                'show' => TRUE,
                'children' => array(
                    'Articleclass' => array(
                        'ico'=>'&#xe652;',
                        'text' => lang('ds_articleclass'),
                        'args' => 'index,Articleclass,website',
                    ),
                    'Article' => array(
                        'ico'=>'&#xe71d;',
                        'text' => lang('ds_article'),
                        'args' => 'index,Article,website',
                    ),
                    'Document' => array(
                        'ico'=>'&#xe74f;',
                        'text' => lang('ds_document'),
                        'args' => 'index,Document,website',
                    ),
                    'Navigation' => array(
                        'ico'=>'&#xe67d;',
                        'text' => lang('ds_navigation'),
                        'args' => 'index,Navigation,website',
                    ),
                    'Adv' => array(
                        'ico'=>'&#xe707;',
                        'text' => lang('ds_adv'),
                        'args' => 'ap_manage,Adv,website',
                    ),
					'EditablePagePc' => array(
					    'ico'=>'&#xe60c;',
					    'text' => lang('editable_page_pc'),
					    'args' => 'page_list,EditablePage,website',
					),
					'EditablePageH5' => array(
					    'ico'=>'&#xe601;',
					    'text' => lang('editable_page_h5'),
					    'args' => 'h5_page_list,EditablePage,website',
					),
                    'Link' => array(
                        'ico'=>'&#xe67d;',
                        'text' => lang('ds_friendlink'),
                        'args' => 'index,Link,website',
                    ),
                    'Mallconsult' => array(
                        'ico'=>'&#xe750;',
                        'text' => lang('ds_shop_consult'),
                        'args' => 'index,Mallconsult,website',
                    ),
                    'Feedback' => array(
                        'ico'=>'&#xe672;',
                        'text' => lang('ds_feedback'),
                        'args' => 'flist,Feedback,website',
                    ),
                ),
            ),
            'operation' => array(
                'name' => 'operation',
                'text' => lang('ds_operation'),
                'show' => TRUE,
                'children' => array(
                    'Operation' => array(
                        'ico'=>'&#xe734;',
                        'text' => lang('ds_operation_set'),
                        'args' => 'index,Operation,operation',
                    ),
                    
                ),
            ),
            'stat' => array(
                'name' => 'stat',
                'text' => lang('ds_stat'),
                'show' => TRUE,
                'children' => array(
                    'stat_general' => array(
                        'ico'=>'&#xe734;',
                        'text' => lang('ds_statgeneral'),
                        'args' => 'general,Statgeneral,stat',
                    ),
                    'stat_industry' => array(
                         'ico'=>'&#xe745;',
                        'text' => lang('ds_statindustry'),
                        'args' => 'scale,Statindustry,stat',
                    ),
                    'stat_member' => array(
                        'ico'=>'&#xe73f;',
                        'text' => lang('ds_statmember'),
                        'args' => 'newmember,Statmember,stat',
                    ),
                    'stat_trade' => array(
                         'ico'=>'&#xe745;',
                        'text' => lang('ds_stattrade'),
                        'args' => 'predeposit,Stattrade,stat',
                    ),
                    'stat_goods' => array(
                        'ico'=>'&#xe732;',
                        'text' => lang('ds_statgoods'),
                        'args' => 'pricerange,Statgoods,stat',
                    ),
                    'stat_marketing' => array(
                         'ico'=>'&#xe745;',
                        'text' => lang('ds_statmarketing'),
                        'args' => 'promotion,Statmarketing,stat',
                    ),
                    'stat_stataftersale' => array(
                         'ico'=>'&#xe745;',
                        'text' => lang('ds_stataftersale'),
                        'args' => 'refund,Stataftersale,stat',
                    ),
                ),
            ),
            'mobile' => array(
                'name' => 'mobile',
                'text' => lang('mobile'),
                'show' => TRUE,
                'children' => array(
                    'app_appadv' => array(
                        'text' => lang('appadv'),
                        'args' => 'index,Appadv,mobile',
                    ),
                ),
            ),
            'wechat' => array(
                'name' => 'wechat',
                'text' => lang('wechat'),
                'show' => TRUE,
                'children' => array(
                    'wechat_setting' => array(
                        'ico'=>'&#xe6e0;',
                        'text' => lang('wechat_setting'),
                        'args' => 'setting,Wechat,wechat',
                    ),
                    'wechat_material' => array(
                        'ico'=>'&#xe679;',
                        'text' => lang('wechat_material'),
                        'args' => 'material,Wechat,wechat',
                    ),
                    'wechat_menu' => array(
                        'ico'=>'&#xe679;',
                        'text' => lang('wechat_menu'),
                        'args' => 'menu,Wechat,wechat',
                    ),
                    'wechat_keywords' => array(
                        'ico'=>'&#xe672;',
                        'text' => lang('wechat_keywords'),
                        'args' => 'k_text,Wechat,wechat',
                    ),
                    'wechat_member' => array(
                        'ico'=>'&#xe729;',
                        'text' => lang('wechat_member'),
                        'args' => 'member,Wechat,wechat',
                    ),
                    'wechat_push' => array(
                        'ico'=>'&#xe71b;',
                        'text' => lang('wechat_push'),
                        'args' => 'SendList,Wechat,wechat',
                    ),
                ),
            ),
        );
    }

    /*
     * 权限选择列表
     */

    function limitList() {
        $_limit = array(
            array('name' => lang('ds_set'), 'child' => array(
                    array('name' => lang('ds_base'), 'action' => null, 'controller' => 'Config'),
                    array('name' => lang('ds_account'), 'action' => null, 'controller' => 'Account'),
                    array('name' => lang('ds_upload_set'), 'action' => null, 'controller' => 'Upload'),
                    array('name' => lang('ds_seo_set'), 'action' => null, 'controller' => 'Seo'),
                    array('name' => lang('ds_payment'), 'action' => null, 'controller' => 'Payment'),
                    array('name' => lang('ds_message'), 'action' => null, 'controller' => 'Message'),
                    array('name' => lang('ds_admin'), 'action' => null, 'controller' => 'Admin'),
                    array('name' => lang('ds_express'), 'action' => null, 'controller' => 'Express'),
                    array('name' => lang('ds_region'), 'action' => null, 'controller' => 'Region'),
                    array('name' => lang('ds_db'), 'action' => null, 'controller' => 'Database'),
                    array('name' => lang('ds_adminlog'), 'action' => null, 'controller' => 'Adminlog'),
                )),
            array('name' => lang('ds_goods'), 'child' => array(
                    array('name' => lang('ds_goods_manage'), 'action' => null, 'controller' => 'Goods'),
                    array('name' => lang('ds_goods_add'), 'action' => null, 'controller' => 'Goodsadd'),
                    array('name' => lang('ds_goodsclass'), 'action' => null, 'controller' => 'Goodsclass'),
                    array('name' => lang('ds_brand'), 'action' => null, 'controller' => 'Brand'),
                    array('name' => lang('ds_type'), 'action' => null, 'controller' => 'Type'),
                    array('name' => lang('ds_spec'), 'action' => null, 'controller' => 'Spec'),
                    array('name' => lang('ds_arrivalnotice'), 'action' => null, 'controller' => 'Arrivalnotice'),
                    array('name' => lang('ds_transport'), 'action' => null, 'controller' => 'Transport'),
                    array('name' => lang('ds_album'), 'action' => null, 'controller' => 'Goodsalbum'),
                )),
            array('name' => lang('ds_member'), 'child' => array(
                    array('name' => lang('ds_member_manage'), 'action' => null, 'controller' => 'Member'),
                    array('name' => lang('member_auth'), 'action' => null, 'controller' => 'MemberAuth'),
                    array('name' => lang('ds_membergrade'), 'action' => null, 'controller' => 'Membergrade'),
                    array('name' => lang('ds_exppoints'), 'action' => null, 'controller' => 'Exppoints'),
                    array('name' => lang('ds_notice'), 'action' => null, 'controller' => 'Notice'),
                    array('name' => lang('ds_points'), 'action' => null, 'controller' => 'Points'),
                    array('name' => lang('ds_predeposit'), 'action' => null, 'controller' => 'Predeposit'),
                )),
            array('name' => lang('ds_trade'), 'child' => array(
                    array('name' => lang('ds_deliver'), 'action' => null, 'controller' => 'Deliver'),
                    array('name' => lang('ds_order'), 'action' => null, 'controller' => 'Order'),
                    array('name' => lang('ds_vrorder'), 'action' => null, 'controller' => 'Vrorder'),
                    array('name' => lang('ds_refund'), 'action' => null, 'controller' => 'Refund'),
                    array('name' => lang('ds_return'), 'action' => null, 'controller' => 'Returnmanage'),
                    array('name' => lang('ds_vrrefund'), 'action' => null, 'controller' => 'Vrrefund'),
                    array('name' => lang('ds_consulting'), 'action' => null, 'controller' => 'Consulting'),
                    array('name' => lang('ds_inform'), 'action' => null, 'controller' => 'Inform'),
                    array('name' => lang('ds_evaluate'), 'action' => null, 'controller' => 'Evaluate'),
                    array('name' => '发货设置', 'action' => null, 'controller' => 'Deliverset'),
                )),
            array('name' => lang('ds_entityshop'), 'child' => array(
                    array('name' => lang('ds_entityshop_manage'), 'action' => null, 'controller' => 'Entityshop'),
                    array('name' => lang('ds_entityclerk_manage'), 'action' => null, 'controller' => 'Entityclerk'),
                )),
            array('name' => lang('ds_website'), 'child' => array(
                    array('name' => lang('ds_articleclass'), 'action' => null, 'controller' => 'Articleclass'),
                    array('name' => lang('ds_article'), 'action' => null, 'controller' => 'Article'),
                    array('name' => lang('ds_document'), 'action' => null, 'controller' => 'Document'),
                    array('name' => lang('ds_navigation'), 'action' => null, 'controller' => 'Navigation'),
                    array('name' => lang('ds_adv'), 'action' => null, 'controller' => 'Adv'),
                    array('name' => lang('ds_friendlink'), 'action' => null, 'controller' => 'Link'),
                    array('name' => lang('ds_mall_consult'), 'action' => null, 'controller' => 'Mallconsult'),
                    array('name' => lang('ds_feedback'), 'action' => null, 'controller' => 'Feedback'),
                )),
            array('name' => lang('ds_operation'), 'child' => array(
                    array('name' => lang('ds_operation_set'), 'action' => null, 'controller' => 'Operation|Promotionxianshi|Promotionmansong|Promotionbundling|Groupbuy|Vrgroupbuy|Voucher|Promotionmgdiscount|Promotionpintuan|Activity|Inviter|Bonus|Marketmanage|Pointprod|Pointorder|Rechargecard'),
                )),
            array('name' => lang('ds_stat'), 'child' => array(
                    array('name' => lang('ds_statgeneral'), 'action' => null, 'controller' => 'Statgeneral'),
                    array('name' => lang('ds_statindustry'), 'action' => null, 'controller' => 'Statindustry'),
                    array('name' => lang('ds_statmember'), 'action' => null, 'controller' => 'Statmember'),
                    array('name' => lang('ds_stattrade'), 'action' => null, 'controller' => 'Stattrade'),
                    array('name' => lang('ds_statgoods'), 'action' => null, 'controller' => 'Statgoods'),
                    array('name' => lang('ds_statmarketing'), 'action' => null, 'controller' => 'Statmarketing'),
                    array('name' => lang('ds_stataftersale'), 'action' => null, 'controller' => 'Stataftersale'),
                )),
            array('name' => lang('mobile'), 'child' => array(
                    array('name' => lang('appadv'), 'action' => null, 'controller' => 'Appadv'),
                )),
            array('name' => lang('wechat'), 'child' => array(
                    array('name' => lang('wechat_setting'), 'action' => 'setting', 'controller' => 'Wechat'),
                    array('name' => lang('wechat_template_message'), 'action' => 'template_message', 'controller' => 'Wechat'),
                    array('name' => lang('wechat_menu'), 'action' => 'menu', 'controller' => 'Wechat'),
                    array('name' => lang('wechat_keywords'), 'action' => 'k_text', 'controller' => 'Wechat'),
                    array('name' => lang('wechat_member'), 'action' => 'member', 'controller' => 'Wechat'),
                    array('name' => lang('wechat_push'), 'action' => 'SendList', 'controller' => 'Wechat'),
                )),
        );

        return $_limit;
    }

}

?>
