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
class Article extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/article.lang.php');
    }

    public function index() {

        /**
         * 检索条件
         */
        $condition = array();
        $search_ac_id = intval(input('param.search_ac_id'));
        if ($search_ac_id) {
            $condition[] = array('ac_id','=',$search_ac_id);
        }
        $search_title = trim(input('param.search_title'));
        if ($search_title) {
            $condition[] = array('article_title','like', "%" . $search_title . "%");
        }
        $article_model = model('article');
        $article_list = $article_model->getArticleList($condition, 10);

        $articleclass_model = model('articleclass');
        /**
         * 整理列表内容
         */
        if (is_array($article_list)) {
            /**
             * 取文章分类
             */
            $class_list = $articleclass_model->getArticleclassList(array());
            $tmp_class_name = array();
            if (is_array($class_list)) {
                foreach ($class_list as $k => $v) {
                    $tmp_class_name[$v['ac_id']] = $v['ac_name'];
                }
            }
            foreach ($article_list as $k => $v) {
                /**
                 * 发布时间
                 */
                $article_list[$k]['article_time'] = date('Y-m-d H:i:s', $v['article_time']);
                /**
                 * 所属分类
                 */
                if (@array_key_exists($v['ac_id'], $tmp_class_name)) {
                    $article_list[$k]['ac_name'] = $tmp_class_name[$v['ac_id']];
                }
            }
        }

        /**
         * 分类列表
         */
        $parent_list = $articleclass_model->getTreeClassList(2);
        if (is_array($parent_list)) {
            $unset_sign = false;
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
            }
        }

        View::assign('article_list', $article_list);
        View::assign('show_page', $article_model->page_info->render());
        View::assign('search_title', $search_title);
        View::assign('search_ac_id', $search_ac_id);
        View::assign('parent_list', $parent_list);
        
        View::assign('filtered', $condition ? 1 : 0); //是否有查询条件
        
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    public function add() {
        if (!(request()->isPost())) {
            $article = [
                'article_id' => 0,
                'article_title' => '',
                'ac_id' => input('param.ac_id'),
                'article_url' => '',
                'article_show' => 0,
                'article_sort' => 0,
                'article_content' => '',
            ];
            $articleclass_model = model('articleclass');
            $cate_list=$articleclass_model->getTreeClassList(2);
            View::assign('ac_list', $cate_list);
            View::assign('article', $article);
            //游离图片
            $article_pic_list=model('upload')->getUploadList(array('upload_type'=>'1','item_id'=>0));
            View::assign('file_upload', $article_pic_list);
            $this->setAdminCurItem('add');
            return View::fetch('form');
        } else {
            $data = array(
                'article_title' => input('post.article_title'),
                'ac_id' => input('post.ac_id'),
                'article_url' => input('post.article_url'),
                'article_sort' => input('post.article_sort'),
                'article_content' => input('post.article_content'),
                'article_time' => TIMESTAMP,
            );
            $data['article_show'] = intval(input('post.article_show'));
            //验证数据  BEGIN
            $article_validate = ds_validate('article');
            if (!$article_validate->scene('add')->check($data)) {
                $this->error($article_validate->getError());
            }
            //验证数据  END
            $article_id = model('article')->addArticle($data);
            if ($article_id) {
                //更新图片信息ID
                $upload_model = model('upload');
                $file_id_array = input('post.file_id/a');
                if (!empty($file_id_array) && is_array($file_id_array)) {
                    foreach ($file_id_array as $k => $v) {
                        $update_array = array();
                        $update_array['item_id'] = $article_id;
                        $upload_model->editUpload($update_array,array(array('upload_id','=',intval($v))));
                        unset($update_array);
                    }
                }
                //上传文章封面
                if (!empty($_FILES['_pic']['name'])) {
                    $res=ds_upload_pic(ATTACH_ARTICLE,'_pic');
                    if($res['code']){
                        $article_pic=$res['data']['file_name'];
                        model('article')->editArticle(array('article_pic' => $article_pic), $article_id);
                    }else{
                        $this->error($res['msg'], (string) url('Article/edit', ['article_id' => $article_id]));
                    }
                }
                $this->success(lang('ds_common_save_succ'), 'Article/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function edit() {
        $art_id = intval(input('param.article_id'));
        if ($art_id<=0) {
            $this->error(lang('param_error'));
        }
        $condition = array();
        $condition[] = array('article_id','=',$art_id);
        $article = model('article')->getOneArticle($condition);
        if(!$article){
            $this->error(lang('ds_no_record'));
        }
        if (!request()->isPost()) {
            View::assign('article', $article);
            $articleclass_model = model('articleclass');
            $cate_list=$articleclass_model->getTreeClassList(2);
            View::assign('ac_list', $cate_list);
            //附属图片
            $article_pic_list=model('upload')->getUploadList(array('upload_type'=>'1','item_id'=>$art_id));
            View::assign('file_upload', $article_pic_list);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        } else {
            $data = array(
                'article_title' => input('post.article_title'),
                'ac_id' => input('post.ac_id'),
                'article_url' => input('post.article_url'),
                'article_sort' => input('post.article_sort'),
                'article_content' => input('post.article_content'),
                'article_time' => TIMESTAMP,
            );
            $data['article_show'] = intval(input('post.article_show'));

            $article_validate = ds_validate('article');
            if (!$article_validate->scene('edit')->check($data)) {
                $this->error($article_validate->getError());
            }
            //上传文章封面
            if (!empty($_FILES['_pic']['name'])) {
                $res=ds_upload_pic(ATTACH_ARTICLE,'_pic');
                if($res['code']){
                    $file_name=$res['data']['file_name'];
                    //删除原图
                    if($article['article_pic']){
                        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_ARTICLE . DIRECTORY_SEPARATOR . $article['article_pic']);
                    }
                    $data['article_pic'] = $file_name;
                }else{
                    $this->error($res['msg'], (string)url('Article/edit', ['article_id' => $art_id]));
                }
            }
            //验证数据  END
            $result = model('article')->editArticle($data, $art_id);
            if ($result) {
                $this->success(lang('ds_common_save_succ'), 'Article/index');
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    public function drop() {
        $article_id = intval(input('param.article_id'));
        if (empty($article_id)) {
            ds_json_encode(10001, lang('param_error'));
        }
        $condition = array();
        $condition[] = array('article_id','=',$article_id);
        $article = model('article')->getOneArticle($condition);
        if(!$article){
            ds_json_encode(10001, lang('ds_no_record'));
        }
        //删除图片
        if($article['article_pic']){
            @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_ARTICLE . DIRECTORY_SEPARATOR . $article['article_pic']);
        }
        $article_pic_list=model('upload')->getUploadList(array('upload_type'=>'1','item_id'=>$article_id));
        foreach($article_pic_list as $article_pic){
            @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_ARTICLE . DIRECTORY_SEPARATOR . $article_pic['file_name']);
        }
        $result = model('article')->delArticle($article_id);
        if ($result) {
            ds_json_encode(10000, lang('ds_common_op_succ'));
        } else {
            ds_json_encode(10001, lang('error'));
        }
    }

    /**
     * 文章图片上传
     */
    public function article_pic_upload() {
        $file_name = '';
        $file_object = request()->file('fileupload');
        if ($file_object) {
                $res=ds_upload_pic(ATTACH_ARTICLE,'fileupload');
                if($res['code']){
                    $file_name=$res['data']['file_name'];
                }else{
                    echo $e->getMessage();
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
        $insert_array['upload_type'] = '1';
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
                'name' => 'index',
                'text' => lang('ds_manage'),
                'url' => url('Article/index')
            ),
        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => lang('ds_new'),
                'url' => url('Article/add')
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => lang('ds_edit'),
                'url' => 'javascript:void(0)'
            );
        }
        return $menu_array;
    }

}