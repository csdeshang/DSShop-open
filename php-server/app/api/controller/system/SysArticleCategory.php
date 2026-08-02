<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysArticleCategoryService;

/**
 * @OA\Tag(name="api/system/SysArticleCategory", description="系统文章分类接口")
 */
class SysArticleCategory extends BaseApiController{

    /**
     * 获取文章分类树结构
     * @OA\Get(
     *     path="/api/system/article-categories/tree",
     *     tags={"api/system/SysArticleCategory"},
     *     summary="获取文章分类树结构",
     *     description="获取文章分类的树形结构数据",
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
    public function getSysArticleCategoryTree(){
        $data = array();

        $this->validate($data, 'app\api\controller\system\validate\SysArticleCategoryValidate.tree');

        $result = (new SysArticleCategoryService())->getArticleCategoryList($data);

        $result = linearToTree($result,'id','pid');

        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取文章分类列表
     * @OA\Get(
     *     path="/api/system/article-categories/list",
     *     tags={"api/system/SysArticleCategory"},
     *     summary="获取文章分类列表",
     *     description="获取文章分类列表数据",
     *     @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         required=false,
     *         description="父级分类ID",
     *         @OA\Schema(type="integer", default=0)
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
    public function getSysArticleCategoryList(){
        $data = array(
            'pid' => input('param.pid', 0, 'intval'),
        );

        $this->validate($data, 'app\api\controller\system\validate\SysArticleCategoryValidate.list');

        $result = (new SysArticleCategoryService())->getArticleCategoryList($data);

        return ds_json_success('操作成功',$result);
    }

}
