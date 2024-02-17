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
class Appadv extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/adv.lang.php');
    }
    
    function index()
    {
        /**
         * 显示广告位管理界面
         */
        $condition = array();
        $search_name = trim(input('get.search_name'));
        if ($search_name != '') {
            $condition[] = array('ap_name','=',$search_name);
        }
        $appadv_model = model('appadv');
        $ap_list= $appadv_model->getAppadvpositionList($condition,'10');
        $adv_list = $appadv_model->getAppadvList();
        View::assign('ap_list',$ap_list);
        View::assign('adv_list',$adv_list);
        View::assign('showpage', $appadv_model->page_info->render());
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     *
     * 新增广告位
     */
    public function ap_add() {
        if (!request()->isPost()) {
            $ap['ap_isuse']=1;
            View::assign('ap',$ap);
            return View::fetch('ap_form');
        } else {
            $appadv_model = model('appadv');
            $insert_array['ap_name'] = trim(input('post.ap_name'));
            $insert_array['ap_intro'] = trim(input('post.ap_intro'));
            $insert_array['ap_isuse'] = intval(input('post.ap_isuse'));
            $insert_array['ap_width'] = intval(input('post.ap_width'));
            $insert_array['ap_height'] = intval(input('post.ap_height'));

            //验证数据  BEGIN
            $adv_validate = ds_validate('adv');
            if (!$adv_validate->scene('app_ap_add')->check($insert_array)) {
                $this->error($adv_validate->getError());
            }
            //验证数据  END

            $result = $appadv_model->addAppadvposition($insert_array);

            if ($result) {
                $this->log(lang('ap_add_succ') . '[' . input('post.ap_name') . ']', null);
                dsLayerOpenSuccess(lang('ap_add_succ'),url('Appadv/index'));
            } else {
                $this->error(lang('ap_add_fail'));
            }
        }
    }


    /**
     *
     * 删除广告位
     */
    public function ap_del() {
        $appadv_model = model('appadv');
        /**
         * 删除一个广告位
         */
        $ap_id = intval(input('param.ap_id'));
        $result = $appadv_model->delAppadvposition($ap_id);
        if (!$result) {
            ds_json_encode(10001, lang('ap_del_fail'));
        } else {
            $this->log(lang('ap_del_succ') . '[' . $ap_id . ']', null);
            ds_json_encode(10000, lang('ap_del_succ'));
        }
    }

    /**
     *
     * 删除广告
     */
    public function adv_del() {
        $appadv_model = model('appadv');
        /**
         * 删除一个广告
         */
        $adv_id = intval(input('param.adv_id'));
        $result = $appadv_model->delAppadv($adv_id);

        if (!$result) {
            ds_json_encode(10001, lang('adv_del_fail'));
        } else {
            $this->log(lang('adv_del_succ') . '[' . $adv_id . ']', null);
            ds_json_encode(10000, lang('adv_del_succ'));
        }
    }

    /**
     *
     * 修改广告
     */
    public function adv_edit() {
        $adv_id = intval(input('param.adv_id'));
        $appadv_model = model('appadv');
        //获取指定广告
        $condition = array();
        $condition[] = array('adv_id','=',$adv_id);
        $adv = $appadv_model->getOneAppadv($condition);
        if (!request()->isPost()) {

            //获取广告列表
            $ap_list = $appadv_model->getAppadvpositionList();
            View::assign('ap_list', $ap_list);
            View::assign('adv', $adv);
            View::assign('ref_url', get_referer());
            return View::fetch('adv_form');
        } else {
            $param['ap_id'] = intval(input('post.ap_id'));
            $param['adv_title'] = trim(input('post.adv_name'));
            $param['adv_type'] = input('post.adv_type');
            $param['adv_typedate'] = input('post.adv_typedate');
            $param['adv_sort'] = input('post.adv_sort');
            $param['adv_enabled'] = input('post.adv_enabled');
            $param['adv_startdate'] = $this->getunixtime(trim(input('post.adv_startdate')));
            $param['adv_enddate'] = $this->getunixtime(trim(input('post.adv_enddate')));


            if (!empty($_FILES['adv_code']['name'])) {
                //上传文件保存路径
                $upload_file = BASE_UPLOAD_PATH . '/' . ATTACH_APPADV;
                $res=ds_upload_pic(ATTACH_APPADV,'adv_code');
                if($res['code']){
                    //还需删除原来图片
                    if (!empty($adv['adv_code'])) {
                        @unlink($upload_file . DIRECTORY_SEPARATOR . $adv['adv_code']);
                    }
                    $file_name=$res['data']['file_name'];
                    $param['adv_code'] = $file_name;
                }else{
                    $this->error($res['msg']);
                }
                
            }

            //验证数据  BEGIN
            $adv_validate = ds_validate('adv');
            if (!$adv_validate->scene('app_adv_edit')->check($param)) {
                $this->error($adv_validate->getError());
            }
            //验证数据  END

            $result = $appadv_model->editAppadv($adv_id,$param);

            if ($result>=0) {
                $this->log(lang('adv_change_succ') . '[' . input('post.ap_name') . ']', null);
                dsLayerOpenSuccess(lang('adv_change_succ'),input('post.ref_url'));
            } else {
                $this->error(lang('adv_change_fail'));
            }
        }
    }
    
    public function ajax() {
        $appadv_model = model('appadv');
        switch (input('get.branch')) {
            case 'ap_branch':
                $column = input('param.column');
                $value = input('param.value');
                $ap_id = intval(input('param.id'));
                $param[$column] = trim($value);
                $result = $appadv_model->editAppadvposition($ap_id,$param);
                break;
            //ADV数据表更新
            case 'adv_branch':
                $column = input('param.column');
                $value = input('param.value');
                $adv_id = intval(input('param.id'));
                $param[$column] = trim($value);
                $result = $appadv_model->editAppadv($adv_id,$param);
                break;
        }
        if($result>=0){
            echo 'true';
        }else{
            echo false;
        }
    }
    
    
    /**
     *
     * 广告管理
     */
    public function adv() {
        $appadv_model = model('appadv');
        $ap_id = intval(input('param.ap_id'));
        if (!request()->isPost()) {
            $condition = array();
            if ($ap_id) {
                $condition[] = array('ap_id','=',$ap_id);
            }
            $adv_info = $appadv_model->getAppadvList($condition, 20, '', '');
            View::assign('adv_info', $adv_info);
            $ap_list = $appadv_model->getAppadvpositionList();
            View::assign('ap_list', $ap_list);
            if ($ap_id) {
                $ap_condition=array();
                $ap_condition['ap_id']=$ap_id;
                $ap = $appadv_model->getOneAppadvposition($ap_condition); 
                View::assign('ap_name', $ap['ap_name']);
            } else {
                View::assign('ap_name', '');
            }

            View::assign('show_page', $appadv_model->page_info->render());
            
            View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
            
            $this->setAdminCurItem('adv');
            return View::fetch('adv_index');
        }
    }

    /**
     * 管理员添加广告
     */
    public function appadv_add() {
        $appadv_model = model('appadv');
        if (!request()->isPost()) {

            $ap_list = $appadv_model->getAppadvpositionList();
            View::assign('ap_list', $ap_list);
            $adv = array(
                'ap_id' => 0,
                'adv_enabled' => '1',
                'adv_startdate' => TIMESTAMP,
                'adv_enddate' => TIMESTAMP + 24 * 3600 * 365,
                'adv_type'=>''
            );
            View::assign('adv', $adv);
            return View::fetch('adv_form');
        } else {
            $insert_array['ap_id'] = intval(input('post.ap_id'));
            $insert_array['adv_title'] = trim(input('post.adv_name'));
            $insert_array['adv_type'] = input('post.adv_type');
            $insert_array['adv_typedate'] = input('post.adv_typedate');
            $insert_array['adv_sort'] = input('post.adv_sort');
            $insert_array['adv_enabled'] = input('post.adv_enabled');
            $insert_array['adv_startdate'] = $this->getunixtime(input('post.adv_startdate'));
            $insert_array['adv_enddate'] = $this->getunixtime(input('post.adv_enddate'));

            //上传文件保存路径
            $upload_file = BASE_UPLOAD_PATH . '/' . ATTACH_APPADV;
            if (!empty($_FILES['adv_code']['name'])) {
                $res=ds_upload_pic(ATTACH_APPADV,'adv_code');
                if($res['code']){
                    //还需删除原来图片
                    if (!empty($adv['adv_code'])) {
                        @unlink($upload_file . DIRECTORY_SEPARATOR . $adv['adv_code']);
                    }
                    $file_name=$res['data']['file_name'];
                    $insert_array['adv_code'] = $file_name;
                }else{
                    $this->error($res['msg']);
                }
            }

            //验证数据  BEGIN
            $adv_validate = ds_validate('adv');
            if (!$adv_validate->scene('app_adv_add')->check($insert_array)) {
                $this->error($adv_validate->getError());
            }
            //验证数据  END
            //广告信息入库
            $result = $appadv_model->addAppadv($insert_array);
            //更新相应广告位所拥有的广告数量
            $ap_condition=array();
            $ap_condition['ap_id']=intval(input('post.ap_id'));
            $ap_list = $appadv_model->getOneAppadvposition($ap_condition);
            if ($result) {
                $this->log(lang('adv_add_succ') . '[' . input('post.adv_name') . ']', null);
                dsLayerOpenSuccess(lang('adv_add_succ'),url('Appadv/adv', ['ap_id' => input('post.ap_id')]));
            } else {
                $this->error(lang('adv_add_fail'));
            }
        }
    }

    /**
     *
     * 修改广告位
     */
    public function ap_edit() {
        $ap_id = intval(input('param.ap_id'));

        $appadv_model = model('appadv');
        if (!request()->isPost()) {
            $condition = array();
            $condition[] = array('ap_id','=',$ap_id);
            $ap = $appadv_model->getOneAppadvposition($condition);
            View::assign('ref_url', get_referer());
            View::assign('ap', $ap);
            return View::fetch('ap_form');
        } else {
            $param['ap_name'] = trim(input('post.ap_name'));
            $param['ap_intro'] = trim(input('post.ap_intro'));
            $param['ap_width'] = intval(trim(input('post.ap_width')));
            $param['ap_height'] = intval(trim(input('post.ap_height')));
            if (input('post.ap_isuse') != '') {
                $param['ap_isuse'] = intval(input('post.ap_isuse'));
            }
            //验证数据  BEGIN
            $adv_validate = ds_validate('adv');
            if (!$adv_validate->scene('app_ap_edit')->check($param)) {
                $this->error($adv_validate->getError());
            }
            //验证数据  END
            $result = $appadv_model->editAppadvposition($ap_id,$param);

            if ($result>=0) {
                $this->log(lang('ap_change_succ') . '[' . input('post.ap_name') . ']', null);
                dsLayerOpenSuccess(lang('ap_change_succ'),input('post.ref_url'));
            } else {
                $this->error(lang('ap_change_fail'));
            }
        }
    }
    /**
     *
     * 获取UNIX时间戳
     */
    public function getunixtime($time) {
        $array = explode("-", $time);
        $unix_time = mktime(0, 0, 0, $array[1], $array[2], $array[0]);
        return $unix_time;
    }
    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('ap_manage'),
                'url' => url('Appadv/index')
            ),
        );
        $menu_array[] = array(
            'name' => 'adv',
            'text' => lang('adv_manage'),
            'url' => url('Appadv/adv')
        );
        $menu_array[] = array(
            'name' => 'adv_add',
            'text' => lang('adv_add'),
            'url' => "javascript:dsLayerOpen('".url('Appadv/appadv_add', ['ap_id' => input('param.ap_id')])."','".lang('adv_add')."')"
        );
        return $menu_array;
    }
    
}
