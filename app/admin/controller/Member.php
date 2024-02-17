<?php

namespace app\admin\controller;
use think\facade\View;
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
 * 控制器
 */
class Member extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/member.lang.php');
    }

    public function member() {
        $member_model = model('member');


        //会员级别
        $member_grade = $member_model->getMemberGradeArr();
        $search_field_value = input('search_field_value');
        $search_field_name = input('search_field_name');
        $condition = array();
        if ($search_field_value != '') {
            switch ($search_field_name) {
                case 'member_name':
                    $condition[] = array('member_name','like', '%' . trim($search_field_value) . '%');
                    break;
                case 'member_email':
                    $condition[] = array('member_email','like', '%' . trim($search_field_value) . '%');
                    break;
                case 'member_mobile':
                    $condition[] = array('member_mobile','like', '%' . trim($search_field_value) . '%');
                    break;
                case 'member_truename':
                    $condition[] = array('member_truename','like', '%' . trim($search_field_value) . '%');
                    break;
            }
        }
        $search_state = input('search_state');
        switch ($search_state) {
            case 'no_informallow':
                $condition[] = array('inform_allow','=',2);
                break;
            case 'no_isbuy':
                $condition[] = array('is_buylimit','=',0);
                break;
            case 'no_isallowtalk':
                $condition[] = array('is_allowtalk','=',0);
                break;
            case 'no_memberstate':
                $condition[] = array('member_state','=',0);
                break;
        }
        //会员等级
        $search_grade = intval(input('get.search_grade'));
        if ($search_grade>0 && $member_grade) {
            if (isset($member_grade[$search_grade + 1]['exppoints'])) {
                $condition[] = array('member_exppoints','between',array($member_grade[$search_grade]['exppoints'],$member_grade[$search_grade + 1]['exppoints']));
            }else{
                $condition[] = array('member_exppoints','>=', $member_grade[$search_grade]['exppoints']);
            }
        }

        //排序
        $order = trim(input('get.search_sort'));
        if (!in_array($order,array('member_logintime desc','member_loginnum desc'))) {
            $order = 'member_id desc';
        }
        $member_list = $member_model->getMemberList($condition, '*', 10, $order);
        //整理会员信息
        if (is_array($member_list)) {
            foreach ($member_list as $k => $v) {
                $member_list[$k]['member_addtime'] = $v['member_addtime'] ? date('Y-m-d H:i:s', $v['member_addtime']) : '';
                $member_list[$k]['member_logintime'] = $v['member_logintime'] ? date('Y-m-d H:i:s', $v['member_logintime']) : '';
                $member_list[$k]['member_grade'] = ($t = $member_model->getOneMemberGrade($v['member_exppoints'], false, $member_grade)) ? $t['level_name'] : '';
            }
        }
        View::assign('member_grade', $member_grade);
        View::assign('search_sort', $order);
        View::assign('search_field_name', trim($search_field_name));
        View::assign('search_field_value', trim($search_field_value));
        View::assign('member_list', $member_list);
        View::assign('show_page', $member_model->page_info->render());

        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件

        $this->setAdminCurItem('member');
        return View::fetch();
    }

    public function add() {
        if (!request()->isPost()) {
            return View::fetch();
        } else {
            $member_model = model('member');
            $data = array(
                'member_name' => input('post.member_name'),
                'member_password' => input('post.member_password'),
                'member_email' => input('post.member_email'),
                'member_truename' => input('post.member_truename'),
                'member_sex' => input('post.member_sex'),
                'member_qq' => input('post.member_qq'),
                'member_ww' => input('post.member_ww'),
                'member_addtime' => TIMESTAMP,
                'member_loginnum' => 0,
                'inform_allow' => 1, //默认允许举报商品
            );
            $member_validate = ds_validate('member');
            if (!$member_validate->scene('add')->check($data)){
                $this->error($member_validate->getError());
            }
            $result = $member_model->addMember($data);
            if ($result) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('member_add_fail'));
            }
        }
    }

    public function edit() {
        //注：pathinfo地址参数不能通过get方法获取，查看“获取PARAM变量”
        $member_id = input('param.member_id');
        if (empty($member_id)) {
            $this->error(lang('param_error'));
        }
        $member_model = model('member');
        
            $condition = array();
            $condition[] = array('member_id','=',$member_id);
            $member_array = $member_model->getMemberInfo($condition);
        if (!request()->isPost()) {    
            View::assign('member_array', $member_array);
            return View::fetch();
        } else {
            $member_mobile=input('post.member_mobile');
            if($member_mobile==encrypt_show($member_array['member_mobile'],4,4)){
                $member_mobile=$member_array['member_mobile'];
            }

            $data = array(
                'member_email' => input('post.member_email'),
                'member_truename' => input('post.member_truename'),
                'member_sex' => input('post.member_sex'),
                'member_qq' => input('post.member_qq'),
                'member_ww' => input('post.member_ww'),
                'inform_allow' => input('post.inform_allow'),
                'is_buylimit' => input('post.isbuy'),
                'is_allowtalk' => input('post.allowtalk'),
                'member_state' => input('post.member_state'),
                'member_cityid' => input('post.city_id'),
                'member_provinceid' => input('post.province_id'),
                'member_areainfo' => input('post.region'),
                'member_areaid' => input('post.area_id'),
                'member_mobile' => $member_mobile,
                'member_emailbind' => input('post.member_emailbind'),
                'member_mobilebind' => input('post.member_mobilebind'),
                'member_auth_state' => input('post.member_auth_state'),
            );

            if (input('post.member_password')) {
                $data['member_password'] = md5(input('post.member_password'));
            }
            if (input('post.member_paypwd')) {
                $data['member_paypwd'] = md5(input('post.member_paypwd'));
            }

            $member_validate = ds_validate('member');
            if (!$member_validate->scene('edit')->check($data)){
                $this->error($member_validate->getError());
            }
            $result = $member_model->editMember(array('member_id'=>intval($member_id)),$data,intval($member_id));
            if ($result>=0) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    /**
     * ajax操作
     */
    public function ajax() {
        $branch = input('param.branch');

        switch ($branch) {
            /**
             * 验证会员是否重复
             */
            case 'check_user_name':
                $member_model = model('member');
                $condition[] = array('member_name','=',input('param.member_name'));
                $condition[] = array('member_id','<>', intval(input('get.member_id')));
                $list = $member_model->getMemberInfo($condition);
                if (empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
            /**
             * 验证邮件是否重复
             */
            case 'check_email':
                $member_model = model('member');
                $condition[] = array('member_email','=',input('param.member_email'));
                $condition[] = array('member_id','<>', intval(input('param.member_id')));
                $list = $member_model->getMemberInfo($condition);
                if (empty($list)) {
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 设置会员状态
     */
    public function memberstate() {
        $member_id = input('param.member_id');
        $member_id_array = ds_delete_param($member_id);
        if ($member_id_array == FALSE) {
            ds_json_encode('10001', lang('param_error'));
        }
        $data['member_state'] = input('param.member_state') ? input('param.member_state') : 0;

        $condition = array();
        $condition[] = array('member_id','in', $member_id_array);
        $result = Db::name('member')->where($condition)->update($data);
        if ($result>=0) {
            foreach ($member_id_array as $key => $member_id) {
                dcache($member_id, 'member');
            }
            $this->log(lang('ds_edit') .  '[ID:' . implode(',', $member_id_array) . ']', 1);
            ds_json_encode('10000', lang('ds_common_del_succ'));
        }else{
            ds_json_encode('10001', lang('ds_common_del_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'member',
                'text' => '管理',
                'url' => url('Member/member')
            ),
        );
        if (request()->action() == 'add' || request()->action() == 'member') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增',
                'url' => "javascript:dsLayerOpen('".url('Member/add')."','新增用户')"
            );
        }
        return $menu_array;
    }

}

?>
