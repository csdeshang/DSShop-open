<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Lang;
use PHPExcel;
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
class MemberAuth extends AdminControl {

    const EXPORT_SIZE = 1000;
    
    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/member.lang.php');
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/member_auth.lang.php');
    }

    public function index() {
        $member_model = model('member');
        
        $search_field_value = input('search_field_value');
        $search_field_name = input('search_field_name');
        $condition = '1=1';
        $filtered=0;
        $default_condition = array();
        if ($search_field_value != '') {
            switch ($search_field_name) {
                case 'member_name':
                    $condition.=' AND member_name LIKE "%' . trim($search_field_value) . '%"';
                    $filtered=1;
                    break;
                case 'member_email':
                    $condition.=' AND member_email LIKE "%' . trim($search_field_value) . '%"';
                    $filtered=1;
                    break;
                case 'member_mobile':
                    $condition.=' AND member_mobile LIKE "%' . trim($search_field_value) . '%"';
                    $filtered=1;
                    break;
                case 'member_truename':
                    $condition.=' AND member_truename LIKE "%' . trim($search_field_value) . '%"';
                    $filtered=1;
                    break;
            }
        }
        $search_state = input('search_state');
        switch ($search_state) {
            case 'check':
                $condition.=' AND member_auth_state=1';
                $filtered=1;
                break;
            case 'pass':
                $condition.=' AND member_auth_state=3';
                $filtered=1;
                break;
            case 'fail':
                $condition.=' AND member_auth_state=2';
                $filtered=1;
                break;
            default:
                $condition.=' AND member_auth_state IN (1,2,3)';
        }
        $member_list = $member_model->getMemberList($condition, '*', 10, 'member_id desc');
        //整理会员信息
        if (is_array($member_list) && !empty($member_list)) {
            foreach ($member_list as $k => $v) {
                $member_list[$k]['member_addtime'] = $v['member_addtime'] ? date('Y-m-d H:i:s', $v['member_addtime']) : '';
            }
        }
        View::assign('search_field_name', trim($search_field_name));
        View::assign('search_field_value', trim($search_field_value));
        View::assign('member_list', $member_list);
        View::assign('show_page', $member_model->page_info->render());

        View::assign('filtered', $filtered); //是否有查询条件

        $this->setAdminCurItem('index');
        return View::fetch();
    }
    
    public function verify(){
        $member_id = input('param.member_id');
        $state = input('param.state');
        $message = input('param.message');
        $member_id_array = ds_delete_param($member_id);
        if ($member_id_array == FALSE || !in_array($state, array(1,2))) {
            ds_json_encode(10001, lang('param_error'));
        }
        
        if($state==1){
            $update=array('member_auth_state'=>3);
        }else{
            $update=array('member_auth_state'=>2);
        }
        $condition = array();
        $condition[] = array('member_auth_state','=',1);
        $condition[] = array('member_id','in',$member_id_array);
        if(!model('member')->editMember($condition,$update)){
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
        if($message){
            //添加短消息
                $message_model = model('message');
                $insert_arr = array();
                $insert_arr['from_member_id'] = 0;
                $insert_arr['member_id'] = "," . implode(',', $member_id_array) . ",";
                $insert_arr['msg_content'] = lang('member_auth_fail').'：'.$message;
                $insert_arr['message_type'] = 1;
                $insert_arr['message_ismore'] = 1;
                $message_model->addMessage($insert_arr);
        }
        ds_json_encode(10000, lang('ds_common_op_succ'));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('ds_list'),
                'url' => url('MemberAuth/index')
            ),
        );

        return $menu_array;
    }

}

?>
