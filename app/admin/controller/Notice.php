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
class Notice extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path().'admin/lang/'.config('lang.default_lang').'/notice.lang.php');
    }

    /**
     * 发送通知列表
     */
    public function index()
    {
        $special_condition = array();
        $special_condition['message_type'] = 1;
        $message_model = model('message');
        $message_list = $message_model->getMessageList($special_condition,10);
        View::assign('message_list', $message_list);
        View::assign('show_page', $message_model->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }
    /**
     * 会员通知
     */
    public function notice(){
        //提交
        if (request()->isPost()) {
            $notice_validate = ds_validate('notice');
            $content = trim(input('param.content1')); //信息内容
            $send_type = intval(input('param.send_type'));
            //验证
            switch ($send_type) {
                //指定会员
                case 1:
                    $data = [
                        "user_name" => input("param.user_name")
                    ];
                    if (!$notice_validate->scene('notice1')->check($data)) {
                        $this->error($notice_validate->getError());
                    }
                    break;
                //全部会员
                case 2:
                    break;
            }
            $data = [
                "content1" => $content
            ];
            if (!$notice_validate->scene('notice2')->check($data)) {
                $this->error($notice_validate->getError());
            } else {
                //发送会员ID 数组
                $memberid_list = array();
                //整理发送列表
                //指定会员
                if ($send_type == 1) {
                    $member_model = model('member');
                    $tmp = explode("\n", input('param.user_name'));
                    if (!empty($tmp)) {
                        foreach ($tmp as $k => $v) {
                            $tmp[$k] = trim($v);
                        }
                        //查询会员列表
                        $member_list = $member_model->getMemberList(array(array('member_name','in', $tmp)));
                        unset($membername_str);
                        if (!empty($member_list)) {
                            foreach ($member_list as $k => $v) {
                                $memberid_list[] = $v['member_id'];
                            }
                        }
                        unset($member_list);
                    }
                    unset($tmp);
                }
                if (empty($memberid_list) && $send_type != 2) {
                    $this->error(lang('notice_index_member_error'));
                }
                //接收内容
                $array = array();
                $array['send_mode'] = 1;
                $array['user_name'] = $memberid_list;
                $array['content'] = $content;
                //添加短消息
                $message_model = model('message');
                $insert_arr = array();
                $insert_arr['from_member_id'] = 0;
                if ($send_type == 2) {
                    $insert_arr['member_id'] = 'all';
                } else {
                    $insert_arr['member_id'] = "," . implode(',', $memberid_list) . ",";
                }
                $insert_arr['msg_content'] = $content;
                $insert_arr['message_type'] = 1;
                $insert_arr['message_ismore'] = 1;
                $message_model->addMessage($insert_arr);
                //跳转
                $this->log(lang('notice_index_send'), 1);
                dsLayerOpenSuccess(lang('notice_index_send_succ'));
//                $this->success(lang('notice_index_send_succ'), 'notice/notice');
            }
        } else {
            return View::fetch('notice_add');
        }
    }
    protected function getAdminItemList()
    {
        $menu_array=array(
            array(
                'name'=>'index','text'=>'会员通知','url'=>url('Notice/index')
            ),
            array(
                'name'=>'notice','text'=>'发送通知','url'=>"javascript:dsLayerOpen('".url('Notice/notice')."','新增用户')"
            )
        );
        return $menu_array;
    }
}