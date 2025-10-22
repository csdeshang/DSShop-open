<?php
namespace app\api\controller;/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 用户反馈控制器
 */
class Memberfeedback extends MobileMember
{
    public function initialize()
    {
        parent::initialize(); 
    }

    /**
     * 反馈列表
     */
    public function feedback_list()
    {
        $feedback_model = model('feedback');
        $condition = array(
            'member_id' => $this->member_info['member_id']
        );
        $feedback_list = $feedback_model->getFeedbackList($condition);
        $result = array_merge(array('feedback_list' => $feedback_list), mobile_page($feedback_model->page_info));
        ds_json_encode(10000, '', $result);
    }

    /**
     * 添加反馈
     */
    public function feedback_add()
    {
        $feedback_model = model('feedback');

        $param = array();
        $param['fb_content'] = input('post.feedback');
        $param['fb_type'] = 1;
        $param['fb_time'] = TIMESTAMP;
        $param['member_id'] = $this->member_info['member_id'];
        $param['member_name'] = $this->member_info['member_name'];
        $res=word_filter($param['fb_content']);
        if(!$res['code']){
            ds_json_encode(10001,$res['msg']);
        }
        $param['fb_content']=$res['data']['text'];
        
        $result = $feedback_model->addFeedback($param);

        if ($result) {
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
    }
}