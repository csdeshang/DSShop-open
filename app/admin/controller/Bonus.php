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
 * 平台红包 控制器
 */
class Bonus extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/bonus.lang.php');
    }

    public function index() {
        $condition = array();
        $bonus_name = input('param.bonus_name');
        if (!empty($bonus_name)) {
            $condition[] = array('bonus_name','like', '%' . $bonus_name . '%');
        }
        //红包是否有效
        $bonus_state = intval(input('get.bonus_state'));
        if ($bonus_state) {
            $condition[] = array('bonus_state','=',$bonus_state);
        }
        //红包类型
        $bonus_type = intval(input('get.bonus_type'));
        if ($bonus_type) {
            $condition[] = array('bonus_type','=',$bonus_type);
        }
        $bonus_model = model('bonus');
        $bonus_list = $bonus_model->getBonusList($condition, 10);

        //红包类型
        View::assign('bonus_type_list', $bonus_model->bonus_type_list());
        //红包状态
        View::assign('bonus_state_list', $bonus_model->bonus_state_list());

        View::assign('bonus_list', $bonus_list);
        View::assign('show_page', $bonus_model->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 添加吸粉红包
     */
    public function add() {
        $bonus_model = model('bonus');
        if (!request()->isPost()) {
            $bonus = array(
                'bonus_type' => 1,
                'bonus_begintime' => TIMESTAMP,
                'bonus_endtime' => TIMESTAMP+3600*24*7,
            );
            //红包类型
            View::assign('bonus_type_list', $bonus_model->bonus_type_list());
            View::assign('bonus', $bonus);
            return View::fetch('form');
        } else {
            $bonus_totalprice = floatval(input('param.bonus_totalprice'));
            $bonus_pricetype = intval(input('param.bonus_pricetype'));
            $bonus_fixedprice = floatval(input('param.bonus_fixedprice'));
            $bonus_randomprice_start = floatval(input('param.bonus_randomprice_start'));
            $bonus_randomprice_end = floatval(input('param.bonus_randomprice_end'));

            //计算写入吸粉红包领取记录表
            $data_bonusreceive = array(); //红包领取记录
            if ($bonus_pricetype == 1) {
                //固定金额
                if ($bonus_fixedprice == 0 || $bonus_fixedprice > $bonus_totalprice) {
                    $this->error('固定金额或红包总面额错误');
                }
                if (($bonus_totalprice*100) % ($bonus_fixedprice*100) != 0) {
                    $this->error('总金额必须是固定金额的整数倍');
                }
                //生成红包领取记录-固定金额
                for ($i = 0; $i < $bonus_totalprice / $bonus_fixedprice; $i++) {
                    $data_bonusreceive[] = array(
                        'bonusreceive_price' => $bonus_fixedprice
                    );
                }
                $bonus_randomprice_start = 0;
                $bonus_randomprice_end = 0;
            } else {
                if ($bonus_randomprice_start == 0 || $bonus_randomprice_start > $bonus_totalprice || $bonus_randomprice_end > $bonus_totalprice || $bonus_randomprice_start >= $bonus_randomprice_end) {
                    $this->error(lang('bonus_randomprice_error'));
                }
                //生成红包领取记录-随机金额
                $surplus_price = $bonus_totalprice; //剩余未计算金额
                while (true) {
                    if ($surplus_price <= $bonus_randomprice_end) {
                        $bonusreceive_price = $surplus_price;
                    } else {
                        $bonusreceive_price = rand($bonus_randomprice_start * 100, $bonus_randomprice_end * 100) / 100;
                    }
                    $surplus_price -= $bonusreceive_price;
                    $data_bonusreceive[] = array(
                        'bonusreceive_price' => $bonusreceive_price
                    );
                    if ($surplus_price == 0) {
                        break;
                    }
                }
                $bonus_fixedprice = 0;
            }

            $data_bonus = array(
                'bonus_type' => input('param.bonus_type'),
                'bonus_name' => input('param.bonus_name'),
                'bonus_remark' => input('param.bonus_remark'),
                'bonus_blessing' => input('param.bonus_blessing'),
                'bonus_begintime' => strtotime(input('param.bonus_begintime')),
                'bonus_endtime' => strtotime(input('param.bonus_endtime')),
                'bonus_state' => 1,
                'bonus_totalprice' => $bonus_totalprice,
                'bonus_pricetype' => $bonus_pricetype,
                'bonus_fixedprice' => $bonus_fixedprice,
                'bonus_randomprice_start' => $bonus_randomprice_start,
                'bonus_randomprice_end' => $bonus_randomprice_end,
            );
            $bonus_id = $bonus_model->addBonus($data_bonus);

            if ($bonus_id > 0) {
                foreach ($data_bonusreceive as $key => $bonusreceive) {
                    $data_bonusreceive[$key]['bonus_id'] = $bonus_id;
                }
                Db::name('bonusreceive')->insertAll($data_bonusreceive);
                $this->log(lang('ds_add') . lang('ds_bonus') . '[ID' . $bonus_id . ']', 1);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 编辑吸粉红包  不可以对金额以及红包类型进行编辑。
     */
    public function edit() {
        $bonus_id = intval(input('param.bonus_id'));
        if ($bonus_id < 0) {
            ds_json_encode(10000, lang('param_error'));
        }
        $bonus_model = model('bonus');
        $condition = array();
        $condition[] = array('bonus_id','=',$bonus_id);
        if (!request()->isPost()) {
            $bonus = $bonus_model->getOneBonus($condition);
            View::assign('bonus', $bonus);
            //红包类型
            View::assign('bonus_type_list', $bonus_model->bonus_type_list());
            return View::fetch('form');
        } else {
            $data_bonus = array(
                'bonus_name' => input('param.bonus_name'),
                'bonus_remark' => input('param.bonus_remark'),
                'bonus_blessing' => input('param.bonus_blessing'),
                'bonus_begintime' => strtotime(input('param.bonus_begintime')),
                'bonus_endtime' => strtotime(input('param.bonus_endtime')),
            );
            $bonus_model->editBonus($condition, $data_bonus);
            $this->log(lang('ds_edit') . lang('ds_bonus') . '[ID' . $bonus_id . ']', 1);
            dsLayerOpenSuccess(lang('ds_common_save_succ'));
        }
    }

    /**
     * 设置红包失效    1正在进行  2过期  3失效
     */
    public function invalid() {
        $bonus_id = intval(input('param.bonus_id'));
        if ($bonus_id < 0) {
            ds_json_encode(10000, lang('param_error'));
        }
        $bonus_model = model('bonus');
        $condition = array();
        $condition[] = array('bonus_id','=',$bonus_id);
        $data['bonus_state'] = 3;
        $bonus_model->editBonus($condition, $data);
        $this->log(lang('ds_edit') . lang('ds_bonus') . '[ID' . $bonus_id . ']', 1);
        ds_json_encode(10000, lang('ds_common_op_succ'));
    }

    /**
     * 领取列表
     */
    public function receive() {
        $bonus_id = intval(input('param.bonus_id'));
        if ($bonus_id < 0) {
            $this->error(lang('param_error'));
        }
        $condition = array();
        $condition[] = array('bonus_id','=',$bonus_id);
        $bonus_model = model('bonus');
        $bonusreceive_list = $bonus_model->getBonusreceiveList($condition, 10);
        View::assign('bonusreceive_list', $bonusreceive_list);
        View::assign('show_page', $bonus_model->page_info->render());
        return View::fetch();
    }
    
    //链接信息
    public function link()
    {
        $bonus_id = intval(input('param.bonus_id'));
        if ($bonus_id < 0) {
            $this->error(lang('param_error'));
        }
        $condition = array();
        $condition[] = array('bonus_id','=',$bonus_id);
        $bonus_model = model('bonus');
        $bonus = $bonus_model->getOneBonus($condition);
        View::assign('bonus', $bonus);
        $bonus_url = config('ds_config.h5_site_url')."/pages/home/bonus/Detail?bonus_id=".$bonus['bonus_id'];
        View::assign('bonus_url', $bonus_url);
        return View::fetch();
    }
    

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('ds_manage'),
                'url' => url('Bonus/index')
            ),
            array(
                'name' => 'add',
                'text' => lang('ds_add'),
                'url' => "javascript:dsLayerOpen('" . url('Bonus/add') . "','".lang('ds_add')."')"
            ),
        );
        return $menu_array;
    }

}
