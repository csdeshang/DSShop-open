<?php

namespace app\api\controller;
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
 * 用户消息控制器
 */
class MemberMessage extends MobileMember
{

    public function initialize()
    {
        parent::initialize();
    }

    /**
     * @api {POST} api/MemberMessage/get_list 消息列表
     * @apiVersion 3.0.6
     * @apiGroup MemberMessage
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.notice_list  消息列表
     * @apiSuccess {String} result.notice_list.del_member_id  已经删除该消息的会员id
     * @apiSuccess {Int} result.notice_list.from_member_id  短消息发送人用户ID
     * @apiSuccess {Int} result.notice_list.from_member_name  短消息发送人用户名称
     * @apiSuccess {Int} result.notice_list.message_body  消息内容
     * @apiSuccess {Int} result.notice_list.message_id  消息ID
     * @apiSuccess {Int} result.notice_list.message_ismore  站内信是否为一条发给多个用户 0为否 1为多条
     * @apiSuccess {Int} result.notice_list.message_open  短消息打开状态
     * @apiSuccess {Int} result.notice_list.message_parent_id  回复短消息message_id
     * @apiSuccess {Int} result.notice_list.message_state  短消息状态，0为正常状态，1为发送人删除状态，2为接收人删除状态
     * @apiSuccess {Int} result.notice_list.message_time  消息发送时间，Unix时间戳
     * @apiSuccess {Int} result.notice_list.message_title  消息标题
     * @apiSuccess {Int} result.notice_list.message_type  消息类型 0为私信、1为系统消息、2为留言
     * @apiSuccess {Int} result.notice_list.message_update_time  消息更新时间，Unix时间戳
     * @apiSuccess {String} result.notice_list.read_member_id  已经读过该消息的会员id
     * @apiSuccess {Int} result.notice_list.to_member_id  短消息接收人用户ID
     * @apiSuccess {Int} result.notice_list.to_member_name  短消息接收人用户ID
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function get_list()
    {
        $member_id = $this->member_info['member_id'];
        $message_model = model('message');
        $message_list = $message_model->getMessageList(array('from_to_member_id' => $member_id), 10);
        foreach ($message_list as $key => $val) {
            if($val['message_type']==1){
                $message_list[$key]['message_body'] = preg_replace('/(<a.*?>[\s\S]*?<\/a>)/', '', htmlspecialchars_decode($val['message_body']));
            }else{
                $message_list[$key]['message_body'] = parsesmiles($val['message_body']);
            }
        }
        ds_json_encode(10000, '', array_merge(array('notice_list' => $message_list),mobile_page($message_model->page_info)));
    }


}

?>
