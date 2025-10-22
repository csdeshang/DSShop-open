<?php

namespace app\api\controller;

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
 * 文章控制器
 */
class Article extends MobileMall
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * @api {POST} api/Article/article_list 文章列表
     * @apiVersion 3.0.6
     * @apiGroup Article
     *
     * @apiParam {Int} ac_id 文章分类
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.article_list  文章列表
     * @apiSuccess {Int} result.article_list.ac_id  分类ID
     * @apiSuccess {String} result.article_list.article_content  文章内容
     * @apiSuccess {Int} result.article_list.article_id  文章ID
     * @apiSuccess {String} result.article_list.article_pic  文章图片
     * @apiSuccess {Int} result.article_list.article_show  是否显示 0否1是
     * @apiSuccess {Int} result.article_list.article_sort  排序
     * @apiSuccess {Int} result.article_list.article_time  添加时间（Unix时间戳）
     * @apiSuccess {String} result.article_list.article_title  文章标题
     * @apiSuccess {String} result.article_list.article_url  文章跳转链接
     * @apiSuccess {String} result.article_type_name  分类名称
     */
    public function article_list() {
        $ac_id = intval(input('param.ac_id'));
        if ($ac_id > 0) {
            $articleclass_model = model('articleclass');
            $article_model = model('article');
            $condition = array();

            $child_class_list = $articleclass_model->getChildClass($ac_id);
            $ac_ids = array();
            if (!empty($child_class_list) && is_array($child_class_list)) {
                foreach ($child_class_list as $v) {
                    $ac_ids[] = $v['ac_id'];
                }
            }
            $ac_ids = implode(',', $ac_ids);
            $condition[] = array('ac_id','in', $ac_ids);
            $condition[] = array('article_show','=',1);
            $result['article_list'] = $article_model->getArticleList($condition,20);
            $result['article_type_name'] = $this->article_type_name($ac_ids);
            $result = array_merge(array('article_list' => $result['article_list'], 'article_type_name' => $result['article_type_name']), mobile_page(is_object($article_model->page_info) ? $article_model->page_info : ''));
            ds_json_encode(10000, '', $result );
        } else {
            ds_json_encode(10001, '缺少参数:文章类别编号');
        }
    }

    /**
     * 根据类别编号获取文章类别信息
     */
    private function article_type_name() {
        $ac_id = intval(input('param.ac_id'));
        if($ac_id > 0) {
            $articleclass_model = model('articleclass');
            $article_class = $articleclass_model->getOneArticleclass($ac_id);
            return ($article_class['ac_name']);
        }
        else {
            return ('缺少参数:文章类别编号');
        }
    }

    /**
     * @api {POST} api/Article/article_show 单篇文章显示
     * @apiVersion 3.0.6
     * @apiGroup Article
     *
     * @apiParam {Int} article_id 文章ID
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.ac_id  分类ID
     * @apiSuccess {String} result.article_content  文章内容
     * @apiSuccess {Int} result.article_id  文章ID
     * @apiSuccess {String} result.article_pic  文章图片
     * @apiSuccess {Int} result.article_show  是否显示 0否1是
     * @apiSuccess {Int} result.article_sort  排序
     * @apiSuccess {Int} result.article_time  添加时间（Unix时间戳）
     * @apiSuccess {String} result.article_title  文章标题
     * @apiSuccess {String} result.article_url  文章跳转链接
     */
    public function article_show() {
        $article_model = model('article');
        $article_id = intval(input('param.article_id'));
        if ($article_id > 0) {
            $prefix = 'api-article-show-';
            $article = rcache($article_id, $prefix);
            if (empty($article)) {
                $condition = array();
                $condition[] = array('article_id','=',$article_id);
                $article = $article_model->getOneArticle($condition);
                wcache($article_id, $article, $prefix, 3600);
            }
            if (empty($article)) {
                ds_json_encode(10001, '文章不存在');
            } else {
                $article['article_content'] = htmlspecialchars_decode($article['article_content']);
                ds_json_encode(10000, '', $article);
            }
        } else {
            ds_json_encode(10001, '缺少参数:文章编号');
        }
    }

}