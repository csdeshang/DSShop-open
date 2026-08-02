<?php

namespace app\api\controller\blogger;

use app\deshang\base\controller\BaseApiController;

use app\api\service\blogger\BloggerService;

/**
 * @OA\Tag(name="api/blogger/Blogger", description="博主信息接口")
 */
class Blogger extends BaseApiController
{

    /**
     * 获取博主详情
     * @OA\Get(
     *     path="/api/blogger/bloggers/{id}",
     *     tags={"api/blogger/Blogger"},
     *     summary="获取博主详情",
     *     description="获取指定博主的基本信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="博主ID",
     *         @OA\Schema(type="integer", example=1)
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
    public function getBloggerDetail($id) {

        $result = (new BloggerService())->getBloggerDetail((int)$id);
        return ds_json_success('操作成功',$result);
    }



}
