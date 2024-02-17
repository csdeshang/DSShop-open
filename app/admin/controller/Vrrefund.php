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
class Vrrefund extends AdminControl {

    const EXPORT_SIZE = 1000;
    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/vrrefund.lang.php');
        $this->getRefundStateArray();
    }

    /**
     * 向模板页面输出退款状态
     *
     * @param
     * @return array
     */
    public function getRefundStateArray($type = 'all') {
        $admin_array = array(
            '1' => '待审核',
            '2' => '同意',
            '3' => '不同意'
        ); //退款状态:1为待审核,2为同意,3为不同意
        View::assign('admin_array', $admin_array);

        $state_data = array(
            'admin' => $admin_array
        );
        if ($type == 'all')
            return $state_data; //返回所有
        return $state_data[$type];
    }

    /**
     * 待处理列表
     */
    public function refund_manage() {
        $vrrefund_model = model('vrrefund');
        $condition = array();
        $condition[] = array('admin_state','=',1); //状态:1为待审核,2为同意,3为不同意

        $keyword_type = array('order_sn', 'refund_sn', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $type . '%');
        }

        $add_time_from = trim(input('get.add_time_from'));
        $add_time_to = trim(input('get.add_time_to'));
        if ($add_time_from != '') {
            $add_time_from = strtotime($add_time_from);
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if ($add_time_to != '') {
            $add_time_to = strtotime($add_time_to);
            if ($add_time_to !== false) {
                $add_time_to=$add_time_to+86399;
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        $refund_list = $vrrefund_model->getVrrefundList($condition, 10);

        View::assign('refund_list', $refund_list);
        View::assign('show_page', $vrrefund_model->page_info->render());
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        $this->setAdminCurItem('refund_manage');
        return View::fetch('vr_refund_manage_list');
    }

    /**
     * 所有记录
     */
    public function refund_all() {
        $vrrefund_model = model('vrrefund');
        $condition = array();

        $keyword_type = array('order_sn', 'refund_sn', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $key . '%');
        }
        $add_time_from = trim(input('get.add_time_from'));
        $add_time_to = trim(input('get.add_time_to'));
        if ($add_time_from != '') {
            $add_time_from = strtotime($add_time_from);
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if ($add_time_to != '') {
            $add_time_to = strtotime($add_time_to);
            if ($add_time_to !== false) {
                $add_time_to=$add_time_to+86399;
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        $refund_list = $vrrefund_model->getVrrefundList($condition, 10);
        View::assign('refund_list', $refund_list);
        View::assign('show_page', $vrrefund_model->page_info->render());
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('refund_all');
        return View::fetch('vr_refund_all_list');
    }

    /**
     * 审核页
     *
     */
    public function edit() {
        $refund_id = intval(input('param.refund_id'));
        $vrrefund_model = model('vrrefund');
        $condition=array();
        $condition[]=array('refund_id','=',$refund_id);
        $refund = $vrrefund_model->getOneVrrefund($condition);
        if (!(request()->isPost())) {
            View::assign('refund', $refund);
            $code_array = explode(',', $refund['redeemcode_sn']);
            View::assign('code_array', $code_array);
            return View::fetch('vr_refund_edit');
        } else {
            if ($refund['admin_state'] != '1') {//检查状态,防止页面刷新不及时造成数据错误
                $this->error(lang('ds_common_save_fail'));
            }
            $refund['admin_time'] = TIMESTAMP;
            $refund['admin_state'] = '2';
            if (input('post.admin_state') == '3') {
                $refund['admin_state'] = '3';
            }
            $refund['admin_message'] = input('post.admin_message');
            $state = $vrrefund_model->editVrorderRefund($refund);
            if ($state) {
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
                    'refund_url' => HOME_SITE_URL .'/Membervrrefund/view?refund_id='.$refund['refund_id'],
                ));
                //微信模板消息
                $param['weixin_param'] = array(
                    'url' => config('ds_config.h5_site_url').'/pages/member/vrrefund/VrRefundView?refund_id='.$refund['refund_id'],
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
                
                $this->log('虚拟订单退款审核，退款编号' . $refund['refund_sn']);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 查看页
     *
     */
    public function view() {
        $vrrefund_model = model('vrrefund');
        $refund_id = intval(input('param.refund_id'));
        $condition=array();
        $condition[]=array('refund_id','=',$refund_id);
        $refund = $vrrefund_model->getOneVrrefund($condition);
        View::assign('refund', $refund);
        $code_array = explode(',', $refund['redeemcode_sn']);
        View::assign('code_array', $code_array);
        return View::fetch('vr_refund_view');
    }

    /**
     * 导出
     *
     */
    public function export_step1() {

        $vrrefund_model = model('vrrefund');
        $condition = array();

        $keyword_type = array('order_sn', 'refund_sn', 'store_name', 'buyer_name', 'goods_name');
        $key = input('get.key');
        $type = input('get.type');
        if (trim($key) != '' && in_array($type, $keyword_type)) {
            $condition[] = array($type,'like', '%' . $key . '%');
        }
        $add_time_from = trim(input('get.add_time_from'));
        $add_time_to = trim(input('get.add_time_to'));
        if ($add_time_from != '') {
            $add_time_from = strtotime($add_time_from);
            if ($add_time_from !== false) {
                $condition[] = array('add_time','>=', $add_time_from);
            }
        }
        if ($add_time_to != '') {
            $add_time_to = strtotime($add_time_to);
            if ($add_time_to !== false) {
                $add_time_to=$add_time_to+86399;
                $condition[] = array('add_time','<=', $add_time_to);
            }
        }
        if (!is_numeric(input('param.page'))) {
            $count = $vrrefund_model->getVrrefundCount($condition);
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
                $data = $vrrefund_model->getVrrefundList($condition, '', '*', 'refund_id desc', self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else { //下载
            $limit1 = (input('param.page') - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $vrrefund_model->getVrrefundList($condition, $limit2, '*', 'refund_id desc');
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
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_order_ordersn'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_order_refundsn'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_store_name'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_goods_name'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_buyer_name'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_add_time'));
        $excel_data[0][] = array('styleid' => 's_title', 'data' => lang('exp_xn_refund_amount'));
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
        $excel_obj->addWorksheet($excel_obj->charset(lang('exp_xn_refund'), CHARSET));
        $excel_obj->generateXML($excel_obj->charset(lang('exp_xn_refund'), CHARSET) . input('param.page') . '-' . date('Y-m-d-H', TIMESTAMP));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'refund_manage',
                'text' => '待审核',
                'url' => url('Vrrefund/refund_manage')
            ),
            array(
                'name' => 'refund_all',
                'text' => '所有记录',
                'url' => url('Vrrefund/refund_all')
            ),
        );
        if(request()->action() == 'edit'){
            $menu_array[]=array('name'=>'vr_refund_edit','text'=>'编辑','url'=>'javascript:void(0)');
        }
        if(request()->action() == 'view'){
            $menu_array[]=array('name'=>'vr_refund_view','text'=>'查看','url'=>'javascript:void(0)');
        }
        
        return $menu_array;
    }
}
