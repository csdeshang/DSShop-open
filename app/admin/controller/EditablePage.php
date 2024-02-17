<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Lang;
use think\facade\Db;

/**
 * ============================================================================
 * DSMall多用户商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 控制器
 */
class EditablePage extends AdminControl {

    var $type = 'pc';
    var $model_dir = 'home@default/base/editable_page_model/';

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/editable_page.lang.php');
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '.php');
    }

    /**
     * 页面列表
     */
    public function page_list($type = 'pc') {
        $this->type = $type;
        $keyword = input('param.editable_page_name');

        $condition = array();
        if ($keyword) {
            $condition[]=array('editable_page_name','like', '%' . $keyword . '%');
        }
        View::assign('filtered', empty($condition) ? 0 : 1);
        if (!in_array($type, array('pc', 'h5'))) {
            $type = 'pc';
        }


        $editable_page_model = model('editable_page');
        $condition = array_merge(array(array('store_id', '=', 0), array('editable_page_client', '=', $type)), $condition);
        $editable_page_list = $editable_page_model->getEditablePageList($condition, 10);
        foreach ($editable_page_list as $key => $val) {
            if ($val['editable_page_client'] == 'pc') {
                $editable_page_list[$key]['edit_url'] = (string)url('admin/editable_page/page_setting', ['editable_page_id' => $val['editable_page_id']]);
                $editable_page_list[$key]['view_url'] = (string)url('home/special/index', ['special_id' => $val['editable_page_id']]);
            } else {
                $editable_page_list[$key]['edit_url'] = (string)url('EditablePage/mobile_page_setting', array('editable_page_id' => $val['editable_page_id']));
                $editable_page_list[$key]['view_url'] = config('ds_config.h5_site_url') . '/' . 'home/special' . '?' . http_build_query(['special_id' => $val['editable_page_id']]);
            }
        }

        View::assign('show_page', $editable_page_model->page_info->render());
        View::assign('editable_page_list', $editable_page_list);
        View::assign('type', $type);
        $this->setAdminCurItem($type . '_page_list');
        return View::fetch('page_list');
    }

    public function h5_page_list() {
        return $this->page_list('h5');
    }

    
    /**
     * 新增页面
     */
    public function page_add() {
        $editable_page_path = input('param.editable_page_path');
        $editable_page_item_id = intval(input('param.editable_page_item_id'));
        $editable_page_model = model('editable_page');
        if (!request()->isPost()) {
            return View::fetch('page_form');
        } else {
            $data = array(
                'editable_page_name' => input('post.editable_page_name'),
                'editable_page_path' => $editable_page_path,
                'editable_page_item_id' => $editable_page_item_id,
                'editable_page_client' => input('param.type', 'pc'),
                'editable_page_theme' => 'style_1',
                'editable_page_edit_time' => TIMESTAMP,
                'editable_page_theme_config' => json_encode(array(
                    'back_color' => input('param.back_color')
                ))
            );
            $result = $editable_page_model->addEditablePage($data);
            $condition = array();
            $condition[] = array('store_id','=',0);
            $condition[] = array('editable_page_id','<>',$result);
            $condition[] = array('editable_page_path','=',$data['editable_page_path']);
            $condition[] = array('editable_page_client','=',$data['editable_page_client']);
            if (!in_array($data['editable_page_path'], array('index/index'))) {
                $condition[] = array('editable_page_item_id','=',$data['editable_page_item_id']);
            }
            $editable_page_model->editEditablePage($condition, array('editable_page_path' => '', 'editable_page_item_id' => 0));
            if ($result) {
                $this->log(lang('ds_add') . ($data['editable_page_client'] == 'h5' ? lang('editable_page_h5') : lang('editable_page_pc')) . '[flex_' . $result . ':' . input('post.editable_page_name') . ']', null);
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    public function page_setting(){
        $editable_page_id = intval(input('param.editable_page_id'));

        $editable_page_model = model('editable_page');
        $editable_page_info = $editable_page_model->getOneEditablePage(array('editable_page_id' => $editable_page_id));
        if (!$editable_page_info) {
            $this->error(lang('param_error'));
        }
        $editable_page_info['editable_page_theme_config'] = json_decode($editable_page_info['editable_page_theme_config'], true);
        View::assign('editable_page', $editable_page_info);
        $editable_page_config_model = model('editable_page_config');
        $editable_page_config_list=$editable_page_config_model->getEditablePageConfigList(array(array('editable_page_id', '=', $editable_page_id)));
        $config_list=array();
        foreach($editable_page_config_list as $key => $val){
            $config_info=json_decode($val['editable_page_config_content'], true);
            $model_id=$val['editable_page_model_id'];
            $var_html=array();
        $var_config=array();
        if(!empty($config_info)){
            require_once PLUGINS_PATH.'/editable_page_model/'.$model_id.'/config.php';
            $model_name='Model'.$model_id;
            $model=new $model_name();
            $res=$model->filterData($config_info);
            if($res['code']){
                $var_config['config_info']=$res['data'];
                $res=$model->formatData(json_encode($res['data']));
                if($res['code']){
                    $var_html['config_info']=$res['data'];
                }
            }
            
        }
        $html=View::fetch('../../../plugins/editable_page_model/'.$model_id.'/index',$var_html);
        $config=View::fetch('../../../plugins/editable_page_model/'.$model_id.'/config',$var_config);
            $config_list[]=array(
                'val'=>$val,
                'html'=>$html,
                'config'=>$config
            );
        }
        View::assign('config_list', $config_list);
        return View::fetch('page_setting');
    }

    /**
     * 设置手机端页面
     */
    public function mobile_page_setting() {
        $this->type = 'h5';
        $editable_page_id = intval(input('param.editable_page_id'));

        $editable_page_model = model('editable_page');
        $editable_page_info = $editable_page_model->getOneEditablePage(array('editable_page_id' => $editable_page_id));
        if (!$editable_page_info) {
            $this->error(lang('param_error'));
        }
        $editable_page_info['editable_page_theme_config'] = json_decode($editable_page_info['editable_page_theme_config'], true);
        View::assign('editable_page', $editable_page_info);
        $editable_page_config_model = model('editable_page_config');
        $editable_page_config_list=$editable_page_config_model->getEditablePageConfigList(array(array('editable_page_id', '=', $editable_page_id)));
        $config_list=array();
        foreach($editable_page_config_list as $key => $val){
            $config_info=json_decode($val['editable_page_config_content'], true);
            $model_id=$val['editable_page_model_id'];
            $var_html=array();
        $var_config=array();
        if(!empty($config_info)){
            require_once PLUGINS_PATH.'/editable_page_model/h5_'.$model_id.'/config.php';
            $model_name='Model'.$model_id;
            $model=new $model_name();
            $res=$model->filterData($config_info);
            if($res['code']){
                $var_config['config_info']=$res['data'];
                $res=$model->formatData(json_encode($res['data']));
                if($res['code']){
                    $var_html['config_info']=$res['data'];
                }
            }
            
        }
        $html=View::fetch('../../../plugins/editable_page_model/h5_'.$model_id.'/index',$var_html);
        $config=View::fetch('../../../plugins/editable_page_model/h5_'.$model_id.'/config',$var_config);
            $config_list[]=array(
                'val'=>$val,
                'html'=>$html,
                'config'=>$config
            );
        }
        View::assign('config_list', $config_list);
        $this->setAdminCurItem('mobile_page_setting');
        return View::fetch('mobile_page_setting');
    }


    /**
     * 编辑页面
     */
    public function page_edit() {
        $editable_page_id = intval(input('param.editable_page_id'));

        $editable_page_model = model('editable_page');
        $editable_page_info = $editable_page_model->getOneEditablePage(array('editable_page_id' => $editable_page_id));
        if (!$editable_page_info) {
            $this->error(lang('param_error'));
        }
        $editable_page_info['editable_page_theme_config'] = json_decode($editable_page_info['editable_page_theme_config'], true);
        if (!request()->isPost()) {
            View::assign('editable_page', $editable_page_info);
            return View::fetch('page_form');
        } else {
            $data = array(
                'editable_page_path' => input('post.editable_page_path'),
                'editable_page_item_id' => intval(input('post.editable_page_item_id')),
                'editable_page_name' => input('post.editable_page_name'),
                'editable_page_theme_config' => json_encode(array(
                    'back_color' => input('param.back_color')
                ))
            );
            $result = $editable_page_model->editEditablePage(array('editable_page_id' => $editable_page_id), $data);
            
            $condition = array();
            $condition[] = array('store_id','=',0);
            $condition[] = array('editable_page_id','<>',$editable_page_id);
            $condition[] = array('editable_page_path','=',$data['editable_page_path']);
            $condition[] = array('editable_page_client','=',$editable_page_info['editable_page_client']);
            if (!in_array($data['editable_page_path'], array('index/index'))) {
                $condition[] = array('editable_page_item_id','=',$data['editable_page_item_id']);
            }
            $editable_page_model->editEditablePage($condition, array('editable_page_path' => '', 'editable_page_item_id' => 0));
            if ($result) {
                $this->log(lang('ds_edit') . ($editable_page_info['editable_page_client'] == 'h5' ? lang('editable_page_h5') : lang('editable_page_pc')) . '[' . $editable_page_info['editable_page_name'] . ']', null);
                dsLayerOpenSuccess(lang('ds_common_op_succ'));
            } else {
                $this->error(lang('ds_common_op_fail'));
            }
        }
    }

    /**
     * 删除页面
     */
    public function page_del() {
        $editable_page_id = intval(input('param.editable_page_id'));

        $editable_page_model = model('editable_page');
        $editable_page_info = $editable_page_model->getOneEditablePage(array('editable_page_id' => $editable_page_id));
        if (!$editable_page_info) {
            ds_json_encode(10001, lang('param_error'));
        }
        if (!$editable_page_model->delEditablePage($editable_page_id)) {
            ds_json_encode(10001, lang('ds_common_op_fail'));
        }
        $this->log(lang('ds_del') . ($editable_page_info['editable_page_client'] == 'h5' ? lang('editable_page_h5') : lang('editable_page_pc')) . '[ID:' . $editable_page_info['editable_page_id'] . ':' . $editable_page_info['editable_page_name'] . ']', null);
        ds_json_encode(10000, lang('ds_common_del_succ'));
    }




    /**
     * 搜索商品
     */
    public function search_goods() {
        $goods_model = model('goods');

        /**
         * 查询条件
         */
        $where = array();
        $search_goods_name = trim(input('param.keyword'));
        $type = trim(input('param.type'));
        if ($search_goods_name != '') {
            $where[]=array('goods_name|store_name','like', '%' . $search_goods_name . '%');
        }
        switch($type){
            case 'bargain':
                $condition=array();
                $condition[] = array('bargain_state','=',\app\common\model\Pbargain::PINTUAN_STATE_NORMAL);
                $condition[] = array('bargain_endtime','>',TIMESTAMP);
                $subQuery=Db::name('pbargain')->field('bargain_goods_id')->where($condition)->buildSql();
                $where[]=array('goods_id','exp',Db::raw('in '.$subQuery));
                break;
            case 'groupbuy':
                $condition=array();
                $condition[] = array('groupbuy_state','=',\app\common\model\Groupbuy::GROUPBUY_STATE_NORMAL);
                $condition[] = array('groupbuy_endtime','>',TIMESTAMP);
                $subQuery=Db::name('groupbuy')->field('goods_commonid')->where($condition)->buildSql();
                $where[]=array('goods_commonid','exp',Db::raw('in '.$subQuery));
                break;
            case 'pintuan':
                $condition=array();
                $condition[] = array('pintuan_state', '=', \app\common\model\Ppintuan::PINTUAN_STATE_NORMAL);
                $condition[] = array('pintuan_end_time', '>', TIMESTAMP);
                $subQuery=Db::name('ppintuan')->field('pintuan_goods_commonid')->where($condition)->buildSql();
                $where[]=array('goods_commonid','exp',Db::raw('in '.$subQuery));
                break;
            case 'presell':
                $condition=array();
                $condition[] = array('presell_state','=',\app\common\model\Presell::PRESELL_STATE_NORMAL);
                $condition[] = array('presell_end_time','>',TIMESTAMP);
                $subQuery=Db::name('presell')->field('goods_id')->where($condition)->buildSql();
                $where[]=array('goods_id','exp',Db::raw('in '.$subQuery));
                break;
            case 'xianshi':
                $condition=array();
                $condition[] = array('xianshigoods_state','=',\app\common\model\Pxianshigoods::XIANSHI_GOODS_STATE_NORMAL);
                $condition[] = array('xianshigoods_end_time','>',TIMESTAMP);
                $subQuery=Db::name('pxianshigoods')->field('goods_id')->where($condition)->buildSql();
                $where[]=array('goods_id','exp',Db::raw('in '.$subQuery));
                break;
        }

        $goods_list = $goods_model->getGoodsOnlineList($where, '*', 12);
        View::assign('goods_list', $goods_list);
        View::assign('show_page', $goods_model->page_info->render());
        $goods_id=input('param.goods_id/a');
        if(!empty($goods_id)){
            $where = array();
            $where[]=array('goods_id','in', array_keys($goods_id));
            $goods_list = $goods_model->getGoodsOnlineList($where);
            $selected_goods=array();
            foreach($goods_list as $v){
                $selected_goods[$v['goods_id']]=array_merge($v,array('sort'=>$goods_id[$v['goods_id']]['sort']));
            }
            View::assign('goods_id', $selected_goods);
        }
        
        echo View::fetch('search_goods');
        exit;
    }


    /**
     * 搜索品牌
     */
    public function search_brand() {
        $brand_model = model('brand');
        /**
         * 查询条件
         */
        $where = array();
        $search_brand_name = trim(input('param.keyword'));
        if ($search_brand_name != '') {
            $where[]=array('brand_name','like', '%' . $search_brand_name . '%');
        }

        $brand_list = $brand_model->getBrandList($where, '*', 12);
        View::assign('brand_list', $brand_list);
        View::assign('show_page', $brand_model->page_info->render());
        $brand_id=input('param.brand_id/a');
        if(!empty($brand_id)){
            $where = array();
            $where[]=array('brand_id','in', array_keys($brand_id));
            $brand_list = $brand_model->getBrandList($where);
            $selected_brand=array();
            foreach($brand_list as $v){
                $selected_brand[$v['brand_id']]=array_merge($v,array('sort'=>$brand_id[$v['brand_id']]['sort']));
            }
            View::assign('brand_id', $selected_brand);
        }
        echo View::fetch('search_brand');
        exit;
    }


    public function image_del() {
        $file_id = intval(input('param.upload_id'));
        $res = model('editable_page_model', 'logic')->imageDel($file_id);
        if (!$res['code']) {
            ds_json_encode(10001, $res['msg']);
        }

        ds_json_encode(10000);
    }

    /**
     * 图片上传
     */
    public function image_upload() {
        $res = model('editable_page_model', 'logic')->imageUpload(input('param.name'), input('param.config_id'));
        if (!$res['code']) {
            ds_json_encode(10001, $res['msg']);
        }
        $data = $res['data'];
        ds_json_encode(10000, '', $data);
    }

    public function goods_class(){
        $id=intval(input('param.id'));
        $parent_id=intval(input('param.parent_id'));
        $goodsclass_model=model('goodsclass');
        if($id){
            $data=array('id'=>array(),'list'=>array());
            $goodsclass_info=$goodsclass_model->getGoodsclassInfoById($id);
            if($goodsclass_info){
                $data['id'][]=$goodsclass_info['gc_id'];
                $data['list'][]=$goodsclass_model->getGoodsclassListByParentId($goodsclass_info['gc_parent_id']);
                if($goodsclass_info['gc_parent_id']){
                    $goodsclass_info=$goodsclass_model->getGoodsclassInfoById($goodsclass_info['gc_parent_id']);
                    if($goodsclass_info){
                        $data['id'][]=$goodsclass_info['gc_id'];
                        $data['list'][]=$goodsclass_model->getGoodsclassListByParentId($goodsclass_info['gc_parent_id']);
                        if($goodsclass_info['gc_parent_id']){
                            $goodsclass_info=$goodsclass_model->getGoodsclassInfoById($goodsclass_info['gc_parent_id']);
                            if($goodsclass_info){
                                $data['id'][]=$goodsclass_info['gc_id'];
                                $data['list'][]=$goodsclass_model->getGoodsclassListByParentId($goodsclass_info['gc_parent_id']);
                            }
                        }
                    }
                }
            }
            $data['id']=array_reverse($data['id']);
            $data['list']=array_reverse($data['list']);
        }else{
            $data=$goodsclass_model->getGoodsclassListByParentId($parent_id);
        }
        
        ds_json_encode(10000, '', $data);
    }

    public function config_load(){
        $if_h5=intval(input('param.if_h5'));
        $model_id=intval(input('param.model_id'));
        $config_info=input('param.config_info/a');
        if(!$model_id){
            ds_json_encode(10001, lang('param_error'));
        }
        $var_html=array();
        $var_config=array();
        if(!empty($config_info)){
            require_once PLUGINS_PATH.'/editable_page_model/'.($if_h5?'h5_':'').$model_id.'/config.php';
            $model_name='Model'.$model_id;
            $model=new $model_name();
            $res=$model->filterData($config_info);
            if($res['code']){
                $res=$model->formatData(json_encode($res['data']));
                if($res['code']){
                    $var_html['config_info']=$res['data'];
                }else{
                    ds_json_encode(10001, $res['msg']);
                }
            }else{
                ds_json_encode(10001, $res['msg']);
            }
            
        }
        $html=View::fetch('../../../plugins/editable_page_model/'.($if_h5?'h5_':'').$model_id.'/index',$var_html);
        $config=View::fetch('../../../plugins/editable_page_model/'.($if_h5?'h5_':'').$model_id.'/config',$var_config);
        ds_json_encode(10000, '', array('html'=>$html,'config'=>$config));
    }

    public function config_edit(){
        $if_h5=intval(input('param.if_h5'));
        $config_list=input('param.config_list/a');
        $editable_page_id=intval(input('param.page_id'));

        $editable_page_model = model('editable_page');
        $editable_page_info = $editable_page_model->getOneEditablePage(array('editable_page_id' => $editable_page_id));
        if (!$editable_page_info) {
            ds_json_encode(10001, lang('param_error'));
        }
        try{
        $data=array();
        $new_data=array();
        $editable_page_theme_config=array();
        foreach($config_list as $sort_order => $config_info){
            $model_id=$config_info['model_id'];
            switch($model_id){
                case 'page':
                case 'jump':
                case 'button':
                    if($model_id=='page'){
                        $data['editable_page_name']=$config_info['page_title'];
                    }
                    $editable_page_theme_config=array_merge($editable_page_theme_config,$config_info);
                    
                    break;
                default:
                    require_once PLUGINS_PATH.'/editable_page_model/'.($if_h5?'h5_':'').$model_id.'/config.php';
                    $model_name='Model'.$model_id;
                    $model=new $model_name();
                    $res=$model->filterData($config_info);
                    if($res['code']){
                        $new_data[]=array(
                            'editable_page_id'=>$editable_page_id,
                            'editable_page_model_id'=>$model_id,
                            'editable_page_config_sort_order'=>$sort_order,
                            'editable_page_config_content'=>json_encode($res['data'])
                        );
                    }else{
                        throw new \think\Exception($res['msg'], 10006);
                    }
            }
        }
        $data['editable_page_theme_config']=json_encode($editable_page_theme_config);
        $data['editable_page_edit_time']=TIMESTAMP;
        $editable_page_config_model = model('editable_page_config');
        $editable_page_config_model->delEditablePageConfig(array(array('editable_page_id', '=', $editable_page_id)));
        if(!empty($new_data)){
            $editable_page_config_model->addEditablePageConfigAll($new_data);
        }
        $result = $editable_page_model->editEditablePage(array('editable_page_id' => $editable_page_id), $data);
                if (!$result) {
                    throw new \think\Exception(lang('ds_common_op_fail'), 10006);
                }
            }catch(\Exception $e){
                ds_json_encode(10001, $e->getMessage());
              }       
              $this->log(lang('ds_edit') . ($editable_page_info['editable_page_client'] == 'h5' ? lang('editable_page_h5') : lang('editable_page_pc')) . '[' . $editable_page_info['editable_page_name'] . ']', null);
                    ds_json_encode(10000, lang('ds_common_op_succ')); 
    }

    /**
     * 菜单列表
     */
    protected function getAdminItemList() {
        if ($this->type == 'pc') {
            $menu_array = array(
                array(
                    'name' => 'pc_page_list',
                    'text' => lang('ds_list'),
                    'url' => (string)url('EditablePage/page_list'),
                ),
                array(
                    'name' => 'page_add',
                    'text' => lang('ds_new'),
                    'url' => "javascript:dsLayerOpen('" . (string)url('EditablePage/page_add') . "','" . lang('ds_new') . "')",
                ),
            );
        } else {
            $menu_array = array(
                array(
                    'name' => 'h5_page_list',
                    'text' => lang('ds_list'),
                    'url' => (string)url('EditablePage/page_list', array('type' => 'h5')),
                ),
                array(
                    'name' => 'page_add',
                    'text' => lang('ds_new'),
                    'url' => "javascript:dsLayerOpen('" . (string)url('EditablePage/page_add', array('type' => 'h5')) . "','" . lang('ds_new') . "')",
                ),
            );
        }
        return $menu_array;
    }

}

?>
