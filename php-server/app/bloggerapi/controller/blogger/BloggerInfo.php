<?php

namespace app\bloggerapi\controller\blogger;

use app\deshang\base\controller\BaseBloggerController;
use app\bloggerapi\service\blogger\BloggerInfoService;

/**
 * @OA\Tag(name="bloggerapi/blogger/BloggerInfo", description="博主信息管理接口")
 */
class BloggerInfo extends BaseBloggerController
{

    /**
     * 获取博主信息
     * @OA\Get(
     *     path="/bloggerapi/blogger/info",
     *     tags={"bloggerapi/blogger/BloggerInfo"},
     *     summary="获取博主信息",
     *     description="获取当前博主的详细信息",
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
    public function getBloggerInfo()
    {
        $result = (new BloggerInfoService())->getBloggerInfo();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新博主信息
     * @OA\Put(
     *     path="/bloggerapi/blogger/info",
     *     tags={"bloggerapi/blogger/BloggerInfo"},
     *     summary="更新博主信息",
     *     description="更新博主的基本信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="blogger_name", type="string", description="博主昵称", example="时尚博主小王"),
     *             @OA\Property(property="avatar", type="string", description="头像地址", example="/avatar/blogger.jpg"),
     *             @OA\Property(property="description", type="string", description="个人简介", example="热爱分享美好生活的时尚博主")
     *         )
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
    public function updateBloggerInfo()
    {
        $data = array(
            'blogger_name' => input('param.blogger_name'),
            'avatar' => input('param.avatar'),
            'description' => input('param.description'),
        );

        // 验证器
        $this->validate($data, 'app\bloggerapi\controller\blogger\validate\BloggerInfoValidate.update');

        $result = (new BloggerInfoService())->updateBloggerInfo($data);
        return ds_json_success('操作成功', $result);
    }
}
