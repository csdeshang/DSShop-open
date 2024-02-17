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
class Refund extends AdminControl
{
    const EXPORT_SIZE = 1000;
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/refund.lang.php');
        //向模板页面输出退款退货状态
        $this->getRefundStateArray();
    }

    function getRefundStateArray($type = 'all')
    {
        $state_array = array(
            '1' => lang('ds_examine'), '2' => lang('ds_agree'), '3' => lang('ds_disagree')
        ); //卖家处理状态:1为待审核,2为同意,3为不同意
        View::assign('state_array', $state_array);

        $admin_array = array(
            '1' => '处理中', '2' => '待处理', '3' => '已完成'
        ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
        View::assign('admin_array', $admin_array);

        $state_data = array(
            'admin' => $admin_array
        );
        if ($type == 'all') {
            return $state_data; //返回所有
        }
        return $state_data[$type];
    }

    /**
     * 待处理列表
     */
    public function refund_manage()
    {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('refund_type','=',1);
        $condition[] = array('refund_state','=',1); //状态:1为处理中,2为待管理员处理,3为已完成

        $keyword_type = array('order_sn', 'refund_sn', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $key . '%');
        }
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if (trim($add_time_to) != '') {
            $add_time_to = strtotime(trim($add_time_to))+86399;
            if ($add_time_to !== false) {
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        $refund_list = $refundreturn_model->getRefundList($condition, 10);
        View::assign('show_page', $refundreturn_model->page_info->render());
        View::assign('refund_list', $refund_list);
        $this->setAdminCurItem('refund_manage');
        return View::fetch('refund_manage');
    }

    /**
     * 所有记录
     */
    public function refund_all()
    {
        $refundreturn_model = model('refundreturn');
        $condition = array();

        $keyword_type = array('order_sn', 'refund_sn', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $key . '%');
        }
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if (trim($add_time_to) != '') {
            $add_time_to = strtotime(trim($add_time_to))+86399;
            if ($add_time_to !== false) {
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        $refund_list = $refundreturn_model->getRefundList($condition, 10);
        View::assign('show_page', $refundreturn_model->page_info->render());
        View::assign('refund_list', $refund_list);
        $this->setAdminCurItem('refund_all');
        return View::fetch('refund_all');
    }

    /**
     * 退款处理页
     *
     */
    public function edit()
    {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('refund_id','=',intval(input('param.refund_id')));
        $refund_list = $refundreturn_model->getRefundList($condition);
        $refund = $refund_list[0];
        //查询交易凭证
        $order_model = model('order');
        $order = $order_model->getOrderInfo(array('order_id' => $refund['order_id']));
        if (request()->isPost()) {
            if(!in_array(input('post.examine_type'),[2,3])){
              $this->error(lang('refund_state_null'));
            }
            $check = request()->checkToken('__token__');
            if(false === $check) {
                $this->error('invalid token');
            }
            if ($refund['refund_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
                $this->error(lang('ds_common_save_fail'));
            }
            $refund_array = array();
            $refund_array['admin_time'] = TIMESTAMP;
            $refund_array['refund_state'] = '3'; //状态:1为处理中,2为待管理员处理,3为已完成
            $refund_array['examine_type'] = intval(input('param.examine_type'));
            $refund_array['admin_message'] = input('post.admin_message');
            if ($refund_array['examine_type'] == '3' && $refund['order_lock'] == '2') {
                $state = $refundreturn_model->editOrderUnlock($refund['order_id']); //订单解锁
            }else{
                $trade_no=input('param.trade_no');
                if($trade_no && $trade_no!=$order['trade_no']){
                    $order_model->editOrder(array('trade_no'=>$trade_no), array(
                    'order_id' => $order['order_id']
                    ));
                    //添加订单日志
                    $data = array();
                    $data['order_id'] = $order['order_id'];
                    $data['log_role'] = 'system';
                    $data['log_user'] = $this->admin_info['admin_name'];
                    $data['log_msg'] = '修改支付平台交易号 : ' . $trade_no;
                    $data['log_orderstate'] = $order['order_state'];
                    $order_model->addOrderlog($data);
                }
                $res = $refundreturn_model->editOrderRefund($refund);
                $state=$res['code'];
                if(!$state){
                    $this->error($res['msg']);
                }
            }
            if ($state) {
                $refundreturn_model->editRefundreturn($condition, $refund_array);

                // 发送买家消息
                $param = array();
                $param['code'] = 'refund_return_notice';
                $param['member_id'] = $refund['buyer_id'];
                //阿里短信参数
                $param['ali_param'] = array(
                    'refund_sn' => $refund['refund_sn']
                );
                $param['ten_param'] = array(
                    $refund['refund_sn']
                );
                $param['param'] = array_merge($param['ali_param'],array(
                    'refund_url' => HOME_SITE_URL .'/memberrefund/view?refund_id='.$refund['refund_id'],
                ));
                //微信模板消息
                $param['weixin_param'] = array(
                    'url' => config('ds_config.h5_site_url').'/pages/member/refund/RefundView?refund_id='.$refund['refund_id'],
                    'data'=>array(
                        "keyword1" => array(
                            "value" => $refund['order_sn'],
                            "color" => "#333"
                        ),
                        "keyword2" => array(
                            "value" => $refund['refund_amount'],
                            "color" => "#333"
                        )
                    ),
                );
                model('cron')->addCron(array('cron_exetime'=>TIMESTAMP,'cron_type'=>'sendMemberMsg','cron_value'=>serialize($param)));
               
                $this->log('退款确认，退款编号' . $refund['refund_sn']);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
        View::assign('trade_no', $order['trade_no']);
        View::assign('refund', $refund);
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        View::assign('pic_list', $info['buyer']);
        $this->setAdminCurItem('edit');
        return View::fetch('edit');
    }

    /**
     * 退款记录查看页
     *
     */
    public function view()
    {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $condition[] = array('refund_id','=',intval(input('param.refund_id')));
        $refund_list = $refundreturn_model->getRefundList($condition);
        $refund = $refund_list[0];
        View::assign('refund', $refund);
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        View::assign('pic_list', $info['buyer']);
        return View::fetch('view');
    }

    /**
     * 退款退货原因
     */
    public function reason()
    {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $reason_list = $refundreturn_model->getReasonList($condition, 10);
        View::assign('reason_list', $reason_list);
        View::assign('show_page', $refundreturn_model->page_info->render());

        $this->setAdminCurItem('reason');
        return View::fetch('reason');
    }

    /**
     * 新增退款退货原因
     */
    public function add_reason()
    {
        $refundreturn_model = model('refundreturn');
        if (request()->post()) {
            $reason_array = array();
            $reason_array['reason_info'] = input('post.reason_info');
            $reason_array['reason_sort'] = intval(input('post.reason_sort'));
            $reason_array['reason_updatetime'] = TIMESTAMP;

            $state = $refundreturn_model->addReason($reason_array);
            if ($state) {
                $this->log('新增退款退货原因，编号' . $state);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
        return View::fetch('add_reason');
    }

    /**
     * 编辑退款退货原因
     *
     */
    public function edit_reason()
    {
        $refundreturn_model = model('refundreturn');
        $reason_id = intval(input('param.reason_id'));
        $condition = array();
        $condition[] = array('reason_id','=',$reason_id);
        $reason_list = $refundreturn_model->getReasonList($condition);
        $reason = $reason_list[$reason_id];
        if (request()->post()) {
            $reason_array = array();
            $reason_array['reason_info'] = input('post.reason_info');
            $reason_array['reason_sort'] = intval(input('post.reason_sort'));
            $reason_array['reason_updatetime'] = TIMESTAMP;
            $state = $refundreturn_model->editReason($condition, $reason_array);
            if ($state) {
                $this->log('编辑退款退货原因，编号' . $reason_id);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            }
            else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
        View::assign('reason', $reason);
        return View::fetch('edit_reason');
    }

    /**
     * 删除退款退货原因
     *
     */
    public function del_reason()
    {
        $refundreturn_model = model('refundreturn');
        $reason_id = input('param.reason_id');
        $reason_id_array = ds_delete_param($reason_id);
        if($reason_id_array === FALSE){
            ds_json_encode('10001', lang('param_error'));
        }
        $condition = array();
        $condition[] = array('reason_id','in', $reason_id_array);
        $state = $refundreturn_model->delReason($condition);
        if ($state) {
            $this->log('删除退款退货原因，编号' . $reason_id);
            ds_json_encode('10000', lang('ds_common_del_succ'));
        }
        else {
            ds_json_encode('10001', lang('ds_common_del_fail'));
        }
    }

    /**
     * 导出
     *
     */
    public function export_step1() {

        $refundreturn_model = model('refundreturn');
        $condition = array();

        $keyword_type = array('order_sn', 'refund_sn', 'store_name', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $key . '%');
        }
        $add_time_from = input('get.add_time_from');
        $add_time_to = input('get.add_time_to');
        if (trim($add_time_from) != '') {
            $add_time_from = strtotime(trim($add_time_from));
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if (trim($add_time_to) != '') {
            $add_time_to = strtotime(trim($add_time_to))+86399;
            if ($add_time_to !== false) {
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        if (!is_numeric(input('param.page'))) {
            $count = $refundreturn_model->getRefundCount($condition);
            $export_list = array();
            if ($count > self::EXPORT_SIZE) { //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $export_list[$i] = $limit1 . ' ~ ' . $limit2;
                }
                View::assign('export_list', $export_list);
                return View::fetch('/public/excel');
            } else { //如果数量小，直接下载
                $data = $refundreturn_model->getRefundList($condition, '', '*', 'refund_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else { //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $refundreturn_model->getRefundList($condition, $limit2, '*', 'refund_id desc');
            $this->createExcel($data);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()) {
        Lang::load(base_path() .'admin/lang/'.config('lang.default_lang').'/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_order_ordersn'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_order_refundsn'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_store_name'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_goods_name'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_order_buyer'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_order_add_time'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_tk_order_refund'));
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => 'DS' . $v['order_sn']);
            $tmp[] = array('data' => $v['refund_sn']);
            $tmp[] = array('data' => $v['store_name']);
            $tmp[] = array('data' => $v['goods_name']);
            $tmp[] = array('data' => $v['buyer_name']);
            $tmp[] = array('data' => date('Y-m-d H:i:s', $v['add_time']));
            $tmp[] = array('format' => 'Number', 'data' => ds_price_format($v['refund_amount']));
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_tk_refund'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_tk_refund'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'refund_manage', 'text' => '待处理', 'url' => url('Refund/refund_manage')
            ), array(
                'name' => 'refund_all', 'text' => '所有记录', 'url' => url('Refund/refund_all')
            ), array(
                'name' => 'reason', 'text' => '退款退货原因', 'url' => url('Refund/reason')
            ),
        );
        if (request()->action() == 'reason') {
            $menu_array[] = [
                'name' => 'add_reason', 'text' => '新增原因', 'url' =>"javascript:dsLayerOpen('".url('Refund/add_reason')."','新增原因')"
            ];
        }
        return $menu_array;
    }
}

?>
