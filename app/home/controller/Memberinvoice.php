<?php

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
class Memberinvoice extends BaseMember {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/memberinvoice.lang.php');
    }

    /*
     * 收货地址列表
     */

    public function index() {
        $invoice_model = model('invoice');
        $invoice_list = $invoice_model->getInvoiceList(array('member_id' => session('member_id')));
        View::assign('invoice_list', $invoice_list);

        /* 设置买家当前菜单 */
        $this->setMemberCurMenu('member_invoice');
        /* 设置买家当前栏目 */
        $this->setMemberCurItem('my_invoice');
        return View::fetch($this->template_dir . 'index');
    }

    private function get_data() {
        $data = array();
        $data['invoice_state'] = input('post.invoice_state');
        $data['invoice_title'] = input('post.invoice_title');
        $data['invoice_content'] = input('post.invoice_content');
        $data['invoice_code'] = input('post.invoice_code');
        $data['invoice_company'] = input('post.invoice_company');
        $data['invoice_company_code'] = input('post.invoice_company_code');
        $data['invoice_reg_addr'] = input('post.invoice_reg_addr');
        $data['invoice_reg_phone'] = input('post.invoice_reg_phone');
        $data['invoice_reg_bname'] = input('post.invoice_reg_bname');
        $data['invoice_reg_baccount'] = input('post.invoice_reg_baccount');
//                $data['invoice_rec_name'] = input('post.invoice_rec_name');
//                $data['invoice_rec_mobphone'] = input('post.invoice_rec_mobphone');
//                $data['invoice_rec_province'] = input('post.area_info');
//                $data['invoice_goto_addr'] = input('post.invoice_goto_addr');
        return $data;
    }

    public function add() {
        if (!request()->isPost()) {

            $invoice = $this->get_data();
            $invoice['invoice_state']=1;
            View::assign('invoice', $invoice);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_invoice');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('my_invoice_add');
            return View::fetch($this->template_dir . 'form');
        } else {
            $data = $this->get_data();
            $data['member_id'] = session('member_id');
            $memberinvoice_validate = ds_validate('invoice');
            $scene='';
            if($data['invoice_state']==1){
                $scene = 'invoice_1_update';
            }else{
                $scene = 'invoice_2_update';
            }
            if (!$memberinvoice_validate->scene($scene)->check($data)) {
                ds_json_encode(10001, $memberinvoice_validate->getError());
            }

            $invoice_model = model('invoice');
            $result = $invoice_model->addInvoice($data);
            if ($result) {
                ds_json_encode(10000, lang('ds_common_save_succ'));
            } else {
                ds_json_encode(10001, lang('ds_common_save_fail'));
            }
        }
    }

    public function edit() {

        $invoice_id = intval(input('param.invoice_id'));
        if (0 >= $invoice_id) {
            ds_json_encode(10001, lang('param_error'));
        }
        $invoice_model = model('invoice');
        $invoice = $invoice_model->getInvoiceInfo(array('member_id' => session('member_id'), 'invoice_id' => $invoice_id));
        if (empty($invoice)) {
            ds_json_encode(10001, lang('invoice_does_not_exist'));
        }
        if (!request()->isPost()) {

            View::assign('invoice', $invoice);
            /* 设置买家当前菜单 */
            $this->setMemberCurMenu('member_invoice');
            /* 设置买家当前栏目 */
            $this->setMemberCurItem('my_invoice_edit');
            return View::fetch($this->template_dir . 'form');
        } else {
            $data = $this->get_data();
            $memberinvoice_validate = ds_validate('invoice');
            $scene='';
            if($data['invoice_state']==1){
                $scene = 'invoice_1_update';
            }else{
                $scene = 'invoice_2_update';
            }
            if (!$memberinvoice_validate->scene($scene)->check($data)) {
                ds_json_encode(10001, $memberinvoice_validate->getError());
            }

            $result = $invoice_model->editInvoice($data, array('member_id' => session('member_id'), 'invoice_id' => $invoice_id));
            if ($result) {
                ds_json_encode(10000, lang('ds_common_save_succ'));
            } else {
                ds_json_encode(10001, lang('ds_common_save_fail'));
            }
        }
    }

    public function drop() {
        $invoice_id = intval(input('param.invoice_id'));
        if (0 >= $invoice_id) {
            ds_json_encode(10001, lang('empty_error'));
        }
        $invoice_model = model('invoice');
        $result = $invoice_model->delInvoice(array('invoice_id' => $invoice_id,'member_id'=>session('member_id')));
        if ($result) {
            ds_json_encode(10000, lang('ds_common_del_succ'));
        } else {
            ds_json_encode(10001, lang('ds_common_del_fail'));
        }
    }

    /**
     *    栏目菜单
     */
    function getMemberItemList() {
        $item_list = array(
            array(
                'name' => 'my_invoice',
                'text' => lang('my_invoice'),
                'url' => url('Memberinvoice/index'),
            ),
            array(
                'name' => 'my_invoice_add',
                'text' => lang('new_invoice'),
                'url' => url('Memberinvoice/add'),
            ),
        );
        if (request()->action() == 'edit') {
            $item_list[] = array(
                'name' => 'my_invoice_edit',
                'text' => lang('edit_invoice'),
                'url' => "javascript:void(0)",
            );
        }

        return $item_list;
    }

}

?>
