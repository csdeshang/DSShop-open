<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysArticleService;

/**
 * @OA\Tag(name="api/system/SysArticle", description="系统文章接口")
 */
class SysArticle extends BaseApiController{

    /**
     * 获取系统文章列表
     * @OA\Get(
     *     path="/api/system/articles/list",
     *     tags={"api/system/SysArticle"},
     *     summary="获取系统文章列表",
     *     description="获取系统文章列表数据",
     *     @OA\Parameter(
     *         name="cid",
     *         in="query",
     *         description="分类ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getSysArticleList(){

        $data = array(
            'cid' => input('cid', 0, 'intval')
        );

        $this->validate($data, 'app\api\controller\system\validate\SysArticleValidate.list');

        $result = (new SysArticleService())->getSysArticleList($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取系统文章分页列表（支持搜索）
     * @OA\Get(
     *     path="/api/system/articles/pages",
     *     tags={"api/system/SysArticle"},
     *     summary="获取系统文章分页列表",
     *     description="获取系统文章分页列表数据，支持按分类和关键词搜索",
     *     @OA\Parameter(
     *         name="cid",
     *         in="query",
     *         description="分类ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="搜索关键词",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         required=false,
     *         description="当前页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="list", type="array"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="page", type="integer"),
     *                 @OA\Property(property="page_size", type="integer"),
     *                 @OA\Property(property="total_pages", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function getSysArticlePages(){
        $data = [
            'cid' => input('cid', 0),
            'keyword' => input('keyword', ''),
            'page_current' => input('page_current', 1),
            'page_size' => input('page_size', 10),
        ];

        $this->validate($data, 'app\api\controller\system\validate\SysArticleValidate.pages');
        
        $result = (new SysArticleService())->getSysArticlePages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取系统文章详情
     * @OA\Get(
     *     path="/api/system/articles/{id}",
     *     tags={"api/system/SysArticle"},
     *     summary="获取系统文章详情",
     *     description="根据ID获取系统文章详情",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="文章ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getSysArticleDetail($id){
        $this->validate(['id' => $id], 'app\api\controller\system\validate\SysArticleValidate.detail');
        
        $result = (new SysArticleService())->getSysArticleDetail($id);
        if (!$result) {
            return ds_json_error('文章不存在');
        }
        
        return ds_json_success('操作成功', $result);
    }







}