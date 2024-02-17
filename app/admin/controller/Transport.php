<?php
/**
 * 售卖区域设置
 */
namespace app\admin\controller;
use think\facade\View;
use think\facade\Lang;
use think\facade\Db;
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
class Transport extends AdminControl
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/transport.lang.php');
        $type = input('param.type');
        if ($type != '' && $type != 'select') {
            $type = 'select';
        }
    }

    /**
     * 售卖区域列表
     *
     */
    public function index()
    {
        $transport_model = model('transport');
        $transport_list = $transport_model->getTransportList(array(), 4);
        $extend = '';
        if (!empty($transport_list) && is_array($transport_list)) {
            $transport = array();
            foreach ($transport_list as $k => $v) {
                if (!array_key_exists($v['transport_id'], $transport)) {
                    $transport[$v['transport_id']] = $v['transport_title'];
                    $transport_list[$k]['transport_id'] = intval($v['transport_id']);
                }
            }
            $extend = $transport_model->getTransportextendList(array(array('transport_id','in', array_keys($transport))));
            // 整理
            if (!empty($extend)) {
                $tmp_extend = array();
                foreach ($extend as $val) {
                    $tmp_extend[$val['transport_id']]['data'][] = $val;
                    $tmp_extend[$val['transport_id']]['price'] = isset($val['transportext_sprice'])?$val['transportext_sprice']:'';
                }
                $extend = $tmp_extend;
            }
        }
        /**
         * 页面输出
         */
        View::assign('transport_list', $transport_list);
        View::assign('extend', $extend);
        View::assign('show_page', $transport_model->page_info->render());
        $this->setAdminCurItem('transport');
        return View::fetch();
    }

    /**
     * 新增售卖区域
     *
     */
    public function add()
    {
        $areas = model('area')->getAreas();
        View::assign('areas', $areas);
        $this->setAdminCurItem('add');
        return View::fetch();
    }

    public function edit()
    {
        $id = intval(input('get.id'));
        $transport_model = model('transport');
        $transport = $transport_model->getTransportInfo(array('transport_id' => $id));
        $extend = $transport_model->getExtendInfo(array('transport_id' => $id));
        View::assign('transport', $transport);
        View::assign('extend', $extend);

        $areas = model('area')->getAreas();

        View::assign('areas', $areas);

        $this->setAdminCurItem('index');
        return View::fetch('add');
    }

    public function delete()
    {
        $id = intval(input('param.id'));
        $transport_model = model('transport');
        $transport = $transport_model->getTransportInfo(array('transport_id' => $id));
        //查看是否正在被使用
        if ($transport_model->isTransportUsing($id)) {
            $this->error(lang('transport_op_using'));
        }
        if ($transport_model->delTansport($id)) {
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    public function cloned()
    {
        $id = intval(input('get.id'));
        $transport_model = model('transport');
        $transport = $transport_model->getTransportInfo(array('transport_id' => $id));
        unset($transport['transport_id']);
        $transport['transport_title'] .= lang('transport_clone_name');
        $transport['transport_updatetime'] = TIMESTAMP;

        try {
            Db::startTrans();
            $insert = $transport_model->addTransport($transport);
            if ($insert) {
                $extend = $transport_model->getTransportextendList(array('transport_id' => $id));
                foreach ($extend as $k => $v) {
                    foreach ($v as $key => $value) {
                        $extend[$k]['transport_id'] = $insert;
                    }
                    unset($extend[$k]['transportext_id']);
                }
                $insert = $transport_model->addExtend($extend);
            }
            if (!$insert) {
                throw new \think\Exception(lang('ds_common_op_fail'), 10006);
            }
            Db::commit();
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage(), $_SERVER['HTTP_REFERER']);
        }
    }

    public function save()
    {

        if (!request()->isPost()) {
            return false;
        }

        $trans_info = array();
        $trans_info['transport_title'] = input('post.title');
        $trans_info['send_tpl_id'] = 1;
        $trans_info['transport_updatetime'] = TIMESTAMP;
        $trans_info['transport_is_limited'] =input('post.transport_is_limited');
        $transport_model = model('transport');

        $transport_id = input('post.transport_id');
        if (is_numeric($transport_id)) {
            //编辑时，删除所有附加表信息
            $transport_id = intval($transport_id);
            $transport_model->editTransport($trans_info, array('transport_id' => intval($transport_id)));
            $transport_model->delTransportextend($transport_id);
        }
        else {
            //新增
            $transport_id = $transport_model->addTransport($trans_info);
        }

        $post = input('post.');#获取POST 数据

        $trans_list = array();
        $areas = !empty($post['areas']['kd']) ? $post['areas']['kd'] : '';
        $special = !empty($post['special']['kd']) ? $post['special']['kd'] : '';

        //默认运费
        $default = $post['default']['kd'];
        $trans_list[]=array(
            'transportext_area_id' =>'',
            'transportext_area_name' =>'默认运费',
            'transportext_sprice' =>$default['postage'],
            'transport_id'  =>$transport_id,
            'transport_title' =>input('post.title'),
            'transportext_snum'   =>$default['start'],
            'transportext_xnum'   =>$default['plus'],
            'transportext_xprice'  =>$default['postageplus'],
            'transportext_is_default' =>'1',
            'transportext_top_area_id' =>''
        );

        if (is_array($special)) {
            foreach ($special as $key => $value) {
                $tmp = array();
                if (empty($areas[$key])) {
                    continue;
                }
                $areas[$key] = explode('|||', $areas[$key]);
                $tmp['transportext_area_id'] = ',' . $areas[$key][0] . ',';
                $tmp['transportext_area_name'] = $areas[$key][1];
                $tmp['transportext_sprice'] = $value['postage'];
                $tmp['transport_id'] = $transport_id;
                $tmp['transport_title'] = input('post.title');
                $tmp['transportext_snum'] = $value['start'];
                $tmp['transportext_xnum'] = $value['plus'];
                $tmp['transportext_xprice'] =$value['postageplus'];
                $tmp['transportext_is_default'] ='0';
                //计算省份ID
                $province = array();
                $tmp1 = explode(',', $areas[$key][0]);
                if (!empty($tmp1) && is_array($tmp1)) {
                    $city = model('area')->getCityProvince();
                    foreach ($tmp1 as $t) {
                        $pid = isset($city[$t]) ? $city[$t] : array();
                        if (!in_array($pid, $province) && !empty($pid)) {
                            $province[] = $pid;
                        }
                    }
                }
                if (count($province) > 0) {
                    $tmp['transportext_top_area_id'] = ',' . implode(',', $province) . ',';
                }
                else {
                    $tmp['transportext_top_area_id'] = '';
                }
                $trans_list[] = $tmp;
            }
        }
        $result = $transport_model->addExtend($trans_list);
        $type = input('param.type');
        if ($result) {
            $this->redirect('transport/index', ['type' => $type]);
        }
        else {
            $this->error(lang('ds_common_op_fail'));
        }
    }

    /**
     * 货到付款地区设置
     *
     */
    public function offpay_area() {
        $config_model = model('config');
        $area_model = model('area');

        if (request()->isPost()) {
            $county_array = input('post.county');
            if (!preg_match('/^[\d,]+$/', $county_array)) {
                $county_array = '';
            }
            $data = array();
            $county = trim($county_array, ',');
            //地区修改
            $county_array = explode(',', $county);

            $all_array = array();

            $province_array = input('post.province/a');
            if (!empty($province_array) && is_array($province_array)) {
                foreach ($province_array as $v) {
                    $all_array[$v] = $v;
                }
            }

            $city_array = input('post.city/a');
            if (!empty($city_array) && is_array($city_array)) {
                foreach ($city_array as $v) {
                    $all_array[$v] = $v;
                }
            }


            if (is_array($county_array)) {
                foreach ($county_array as $pid) {
                    if ($pid == '')
                        continue;
                    $all_array[$pid] = $pid;
                    $temp = $area_model->getChildsByPid($pid);
                    if (!empty($temp) && is_array($temp)) {
                        foreach ($temp as $v) {
                            $all_array[$v] = $v;
                        }
                    }
                }
            }

            $all_array = array_values($all_array);
            $data['distribution_area'] = serialize($all_array);
            $result = $config_model->editConfig($data);
            if ($result) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        } else {
            //取出支持货到付款的县ID及上级市ID
            $parea_info = config('ds_config.distribution_area');
            if (!empty($parea_info)) {
                $parea_ids = @unserialize($parea_info);
            }
            if (empty($parea_ids)) {
                $parea_ids = array();
            }

            View::assign('parea_ids', $parea_ids);

            //取出支持货到付款县ID的上级市ID
            $city_checked_child_array = array();
            //地区修改
            $county_array = $area_model->getAreaList(array('area_deep' => 3), 'area_id,area_parent_id');
            foreach ($county_array as $v) {
                if (in_array($v['area_id'], $parea_ids)) {
                    $city_checked_child_array[$v['area_parent_id']][] = $v['area_id'];
                }
            }
            //halt($city_checked_child_array);
            View::assign('city_checked_child_array', $city_checked_child_array);
            //市级下面的县是不是全部支持货到付款，如果全部支持，默认选中，如果其中部分县支持货到付款，默认不选中但显示一个支付到付县的数量
            //格式 city_id => 下面支持到付的县ID数量
            $city_count_array = array();
            //格式 city_id => 是否选中true/false
            $city_checked_array = array();
            $list = $area_model->getAreaList(array('area_deep' => 3), 'area_parent_id,count(area_id) as child_count', 'area_parent_id');
            foreach ($list as $k => $v) {
                $city_count_array[$v['area_parent_id']] = $v['child_count'];
            }
            foreach ($city_checked_child_array as $city_id => $city_child) {
                if (count($city_child) > 0) {
                    if (count($city_child) == $city_count_array[$city_id]) {
                        $city_checked_array[$city_id] = true;
                    }
                }
            }
            View::assign('city_checked_array', $city_checked_array);

            //取得省级地区及直属子地区(循环输出)
            require(PUBLIC_PATH . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . '/area_datas.php');
            //地区修改 修改地区从3级变成5级，以及N级引发的错误
            $province_array = array();
            foreach ($area_array as $k => $v) {
                if ($v['area_parent_id'] == '0') {
                    $province_array[$k] = $k;
                }
            }

            foreach ($area_array as $k => $v) {
                if ($v['area_parent_id'] != '0') {
                    if (in_array($v['area_parent_id'], $province_array)) {
                        $area_array[$v['area_parent_id']]['child'][$k] = $v['area_name'];
                    }
                    unset($area_array[$k]);
                }
            }

            View::assign('province_array', $area_array);

            //计算哪些省需要默认选中(即该省下面的所有县都支持到付，即所有市都是选中状态)
            $province_array = $area_array;
            foreach ($province_array as $pid => $value) {
                if (isset($value['child']) && is_array($value['child'])) {
                    foreach ($value['child'] as $k => $v) {
                        if (!array_key_exists($k, $city_checked_array)) {
                            unset($province_array[$pid]);
                            break;
                        }
                    }
                }
            }
            View::assign('province_checked_array', $province_array);

            $this->setAdminCurItem('offpay_area');
            return View::fetch();
        }
    }

    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $name 当前导航的name
     *
     * @return
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'transport', 'text' => lang('ds_member_path_postage'), 'url' => url('Transport/index')
            ),
        );
        $menu_array[] = array(
            'name' => 'offpay_area', 'text' => '配送地区', 'url' => url('Transport/offpay_area')
        );
        $menu_array[] = array(
            'name' => 'add','text' => lang('transport_tpl_add'),'url' => url('Transport/add')
        );

        return $menu_array;
    }
}