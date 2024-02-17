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
 * 会员协议管理 控制器
 */
class Document extends AdminControl {

    public function initialize() {
        parent::initialize(); 
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/document.lang.php');
    }

    /**
     * 系统文章管理首页
     */
    public function index() {
        $document_model = model('document');
        $doc_list = $document_model->getDocumentList();
        View::assign('doc_list', $doc_list);
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 系统文章编辑
     */
    public function edit() {
        if (request()->isPost()) {
            /**
             * 验证
             */
            $data = [
                'document_title' => input('post.document_title'),
                'document_content' => input('post.document_content'),
            ];
            $document_validate = ds_validate('document');
            if (!$document_validate->scene('edit')->check($data)) {
                $this->error($document_validate->getError());
            } else {

                $param = array();
                $param['document_id'] = intval(input('document_id'));
                $param['document_title'] = trim(input('document_title'));
                $param['document_content'] = trim(input('document_content'));
                $param['document_time'] = TIMESTAMP;
                $document_model = model('document');

                $result = $document_model->editDocument($param);

                if ($result) {
                    /**
                     * 更新图片信息ID
                     */
                    $upload_model = model('upload');
                    $file_id_array = input('post.file_id/a');
                    if (is_array($file_id_array) && !empty($file_id_array)) {
                        foreach ($file_id_array as $k => $v) {
                            $v = intval($v);
                            $update_array = array();
                            $update_array['item_id'] = intval(input('document_id'));
                            $upload_model->editUpload($update_array,array(array('upload_id','=',intval($v))));
                            unset($update_array);
                        }
                    }

                    $this->log(lang('ds_edit') . lang('document_index_document') . '[ID:' . input('document_id') . ']', 1);
                    $this->success(lang('ds_common_save_succ'), 'document/index');
                } else {
                    $this->error(lang('ds_common_save_fail'));
                }
            }
        }  else {
            if (empty(input('param.document_id'))) {
                $this->error(lang('miss_argument'));
            }
            $document_model = model('document');
            $doc = $document_model->getOneDocumentById(intval(input('param.document_id')));

            /**
             * 模型实例化
             */
            $upload_model = model('upload');
            $condition = array();
            $condition[] = array('upload_type','=','4');
            $condition[] = array('item_id','=',$doc['document_id']);
            $file_upload = $upload_model->getUploadList($condition);
            if (is_array($file_upload)) {
                foreach ($file_upload as $k => $v) {
                    $file_upload[$k]['upload_path'] = $file_upload[$k]['file_name'];
                }
            }

            View::assign('PHPSESSID', session_id());
            View::assign('file_upload', $file_upload);
            View::assign('doc', $doc);
            $this->setAdminCurItem('edit');
            return View::fetch();
        }
    }

    /**
     * 系统文章图片上传
     */
    public function document_pic_upload() {
        /**
         * 上传图片
         */

        $file_name = '';
        $file_object = request()->file('fileupload');
        if ($file_object) {
                $res=ds_upload_pic(ATTACH_ARTICLE,'fileupload');
                if($res['code']){
                    $file_name=$res['data']['file_name'];
                }else{
                    echo $res['msg'];
                    exit;
                }
        } else {
            echo 'error';
            exit;
        }
        
        /**
         * 模型实例化
         */
        $upload_model = model('upload');
        /**
         * 图片数据入库
         */
        $insert_array = array();
        $insert_array['file_name'] = $file_name;
        $insert_array['upload_type'] = '4';
        $insert_array['file_size'] = $_FILES['fileupload']['size'];
        $insert_array['item_id'] = intval(input('param.item_id'));
        $insert_array['upload_time'] = TIMESTAMP;
        $result = $upload_model->addUpload($insert_array);
        if ($result) {
            $data = array();
            $data['file_id'] = $result;
            $data['file_name'] = $file_name;
            $data['file_path'] = ds_get_pic(ATTACH_ARTICLE , $file_name);
            /**
             * 整理为json格式
             */
            $output = json_encode($data);
            echo $output;
        }
    }

    /**
     * ajax操作
     */
    public function ajax() {
        switch (input('param.branch')) {
            /**
             * 删除文章图片
             */
            case 'del_file_upload':
                if (intval(input('param.file_id')) > 0) {
                    $upload_model = model('upload');
                    /**
                     * 删除图片
                     */
                    $file_array = $upload_model->getOneUpload(intval(input('param.file_id')));
                    @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_ARTICLE . DIRECTORY_SEPARATOR . $file_array['file_name']);
                    /**
                     * 删除信息
                     */
                    $condition = array();
                    $condition[] = array('upload_id','=',intval(input('param.file_id')));
                    $upload_model->delUpload($condition);
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => '管理', 'url' => url('Document/index')
            ),
        );
        if (request()->action() == 'edit'){
            $menu_array[] = array(
                'name' => 'edit', 'text' => '编辑', 'url' => 'javascript:void(0)'
            );
        }
        return $menu_array;
    }

}
