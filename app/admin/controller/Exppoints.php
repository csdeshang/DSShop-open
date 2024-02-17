<?php
/**
 * 经验值管理
 */
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
class Exppoints extends AdminControl
{
    const EXPORT_SIZE = 5000;
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/membergrade.lang.php');
    }
    /**
     * 设置经验值获取规则
     */
    public function expsetting() {
        $config_model = model('config');
        if (request()->isPost()) {
            $exp_arr = array();
            $exp_arr['exp_login'] = intval(input('post.exp_login'));
            $exp_arr['exp_comments'] = intval(input('post.exp_comments'));
            $exp_arr['exp_orderrate'] = intval(input('post.exp_orderrate'));
            $exp_arr['exp_ordermax'] = intval(input('post.exp_ordermax'));
            $result = $config_model->editConfig(array('exppoints_rule' => serialize($exp_arr)));
            if ($result === true) {
                $this->log(lang('ds_edit') . lang('ds_exppoints_manage') . lang('ds_exppoints_setting'), 1);
                dsLayerOpenSuccess(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        } else {
            $list_setting = $config_model->getOneConfigByCode('exppoints_rule');
            $list_setting = unserialize($list_setting['value']);
            View::assign('list_setting', $list_setting);
            return View::fetch();
        }
    }

    /**
     * 经验值日志列表
     */
    public function index(){
        $where = array();
        $search_mname = trim(input('param.mname'));
        if(!empty($search_mname)){
            $where[] = array('explog_membername','like',"%{$search_mname}%");
        }
        if (input('param.stage')){
            $where[] = array('explog_stage','=',trim(input('param.stage')));
        }
        $stime = input('param.stime')?strtotime(input('param.stime')):0;
        $etime = input('param.etime')?strtotime(input('param.etime')):0;
        if ($stime > 0){
            $where[]=array('explog_addtime','>=',$stime);
        }
        if ($etime > 0){
            $etime=$etime+86399;
            $where[]=array('explog_addtime','<=',$etime);
        }
        $search_desc = trim(input('param.description'));
        if(!empty($search_desc)){
            $where[] = array('explog_desc','like',"%".$search_desc."%");
        }
        

        //查询经验值日志列表
        $exppoints_model = model('exppoints');
        $list_log = $exppoints_model->getExppointslogList($where, '*', 20, 'explog_id desc');
        //信息输出
        
        View::assign('stage_arr',$exppoints_model->getStage());
        View::assign('show_page',$exppoints_model->page_info->render());
        View::assign('list_log',$list_log);
        $this->setAdminCurItem('explog');
        return View::fetch();
    }

    
    /**
     * 经验值调整
     */
    public function edit()
    {
         if (!request()->isPost()) {
            return View::fetch();
        } else {
            $data = [
                'member_name' => input('post.member_name'),
                'exppoints_type' => input('post.exppoints_type'),
                'exppoints_num' => intval(input('post.exppoints_num')),
                'exppoints_desc' => input('post.exppoints_desc'),
            ];
            
            if(empty($data['member_name']) || intval($data['exppoints_type'])<=0){
                $this->error(lang('param_error'));
            }
            

            $member_name = $data['member_name'];
            $member_info = model('member')->getMemberInfo(array('member_name' => $member_name));
            if (!is_array($member_info) || count($member_info) <= 0) {
                $this->error(lang('admin_exppoints_userrecord_error'));
            }
            if ($data['exppoints_type'] == 2 && $data['exppoints_num'] > $member_info['member_exppoints']) {
                $this->error(lang('admin_exppoints_short_error') . $member_info['member_exppoints']);
            }
            //积分数据记录
            $insert_arr['explog_memberid'] = $member_info['member_id'];
            $insert_arr['explog_membername'] = $member_info['member_name'];
            if ($data['exppoints_type'] == 2) {
                $insert_arr['explog_points'] = -$data['exppoints_num'];
            } else {
                $insert_arr['explog_points'] = $data['exppoints_num'];
            }
            $insert_arr['explog_desc'] = $data['exppoints_desc'];

            $result = model('exppoints')->saveExppointslog('system', $insert_arr);
            if ($result) {
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('error'), 'Exppoints/index');
            }
        }
    }

    public function checkmember() {
        $member_name = trim(input('param.member_name'));
        if (!$member_name) {
            exit(json_encode(array('member_id' => 0)));
        }
        $obj_member = model('member');
        $member_info = $obj_member->getMemberInfo(array('member_name' => $member_name));
        if (is_array($member_info) && count($member_info) > 0) {
            echo json_encode(array('member_id' => $member_info['member_id'], 'member_name' => $member_info['member_name'], 'member_exppoints' => $member_info['member_exppoints']));
        } else {
            exit(json_encode(array('member_id' => 0)));
            die;
        }
    }

    /**
     * 经验值日志列表导出
     */
    public function export_step1(){
        $where = array();
        $search_mname = trim(input('param.mname'));
        $where[] = array('explog_membername','like',"%{$search_mname}%");
        if (input('param.stage')){
            $where[] = array('explog_stage','=',trim(input('param.stage')));
        }
        $stime = input('param.stime')?strtotime(input('param.stime')):0;
        $etime = input('param.etime')?strtotime(input('param.etime')):0;
        if ($stime > 0 && $etime>0){
            $where[] = array('explog_addtime','between',array($stime,$etime));
        }elseif ($stime > 0){
            $where[] = array('explog_addtime','>=',$stime);
        }elseif ($etime > 0){
            $where[] = array('explog_addtime','<=',$etime);
        }
        $search_desc = trim(input('param.description'));
        $where[] = array('explog_desc','like',"%$search_desc%");

        //查询经验值日志列表
        $exppoints_model = model('exppoints');
        $list_log = $exppoints_model->getExppointslogList($where, '*', self::EXPORT_SIZE,  'explog_id desc');
        if (!is_numeric(input('param.page'))){
            $count = $exppoints_model->getExppointslogCount($where);
            $export_list = array();
            if ($count > self::EXPORT_SIZE ){	//显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $export_list[$i] = $limit1.' ~ '.$limit2 ;
                }
                View::assign('export_list',$export_list);
                return View::fetch('/public/excel');
            }else{	//如果数量小，直接下载
                $this->createExcel($list_log);
            }
        }else{	//下载
            $this->createExcel($list_log);
        }
    }

    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array()){
        Lang::load(base_path() .'admin/lang/'.config('lang.default_lang').'/export.lang.php');
        $excel_obj = new \excel\Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'会员名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'经验值');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'添加时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'操作阶段');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'描述');
        $stage_arr = model('exppoints')->getStage();
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['explog_membername']);
            $tmp[] = array('format'=>'Number','data'=>ds_price_format($v['explog_points']));
            $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['explog_addtime']));
            $tmp[] = array('data'=>$stage_arr[$v['explog_stage']]);
            $tmp[] = array('data'=>$v['explog_desc']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('经验值明细',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('经验值明细',CHARSET).input('param.page').'-'.date('Y-m-d-H',TIMESTAMP));
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'explog',
                'text' => '经验值明细',
                'url' =>  url('Exppoints/index')
            ),
            array(
                'name' => 'expset',
                'text' => '规则设置',
                'url' =>  "javascript:dsLayerOpen('".url('Exppoints/expsetting')."','规则设置')"
            ),
            array(
                'name' => 'edit',
                'text' => '调整经验值',
                'url' =>  "javascript:dsLayerOpen('".url('Exppoints/edit')."','调整经验值')"
            ),
        );
        return $menu_array;
    }
}