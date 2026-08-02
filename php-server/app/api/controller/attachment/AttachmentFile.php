<?php

namespace app\api\controller\attachment;

use app\deshang\base\controller\BaseApiController;

use app\api\service\attachment\AttachmentFileService;

// 附件文件(代码与adminapi一致，只是命名空间不同)
/**
 * @OA\Tag(name="api/attachment/AttachmentFile", description="附件文件管理接口")
 */
class AttachmentFile extends BaseApiController
{

    /**
     * 获取附件文件分页
     * @OA\Get(
     *     path="/api/attachment/files/pages",
     *     tags={"api/attachment/AttachmentFile"},
     *     summary="获取附件文件分页",
     *     description="获取附件文件的分页列表",
     *     @OA\Parameter(
     *         name="cid",
     *         in="query",
     *         required=true,
     *         description="分类ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="文件名称",
     *         @OA\Schema(type="string", example="image.jpg")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         description="文件类型",
     *         @OA\Schema(type="string", example="image")
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
    public function getAttachmentFilePages(){
        $data = array(
            'cid'=>input('param.cid'),
            'name'=>input('param.name'),
            'type' => input('param.type'),
        );

        // 使用验证器进行验证
        $this->validate($data, 'app\api\controller\attachment\validate\AttachmentFileValidate.pages');

        $list = (new AttachmentFileService())->getAttachmentFilePages($data);

        return ds_json_success('操作成功', $list);
    }

    /**
     * 上传图片
     * @OA\Post(
     *     path="/api/attachment/files/image",
     *     tags={"api/attachment/AttachmentFile"},
     *     summary="上传图片",
     *     description="上传图片文件到指定分类",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="cid", type="integer", description="分类ID", example=1),
     *                 @OA\Property(property="file", type="string", format="binary", description="上传的图片文件")
     *             )
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
    public function image(){
        $data = array(
            'cid' => (int)input('param.cid')
        );

        // 使用验证器进行验证
        $this->validate($data, 'app\api\controller\attachment\validate\AttachmentFileValidate.image');

        $result = (new AttachmentFileService())->image($data);

        return ds_json_success('上传成功', $result);
    }

    /**
     * 上传视频
     * @OA\Post(
     *     path="/api/attachment/files/video",
     *     tags={"api/attachment/AttachmentFile"},
     *     summary="上传视频",
     *     description="上传视频文件到指定分类",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="cid", type="integer", description="分类ID", example=1),
     *                 @OA\Property(property="file", type="string", format="binary", description="上传的视频文件")
     *             )
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
    public function video(){
        $data = array(
            'cid' => (int)input('param.cid')
        );

        // 使用验证器进行验证
        $this->validate($data, 'app\api\controller\attachment\validate\AttachmentFileValidate.video');

        $result = (new AttachmentFileService())->video($data);

        return ds_json_success('上传成功', $result);
    }

    /**
     * 批量更新附件文件
     * @OA\Post(
     *     path="/api/attachment/files/batch",
     *     tags={"api/attachment/AttachmentFile"},
     *     summary="批量更新附件文件",
     *     description="批量更新附件文件信息（重命名、移动分类）",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids", "cid", "name"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), description="文件ID数组", example={1, 2, 3}),
     *             @OA\Property(property="cid", type="integer", description="分类ID", example=1),
     *             @OA\Property(property="name", type="string", description="文件名称", example="新文件名")
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
    public function updateBatchAttachmentFile(){
        $data = array(
            'ids' => input('param.ids/a'),
            'cid' => (int)input('param.cid'),
            'name' => input('param.name'),
        );
  
        // 使用验证器进行验证
        $this->validate($data, 'app\api\controller\attachment\validate\AttachmentFileValidate.updateBatch');
 
        (new AttachmentFileService())->updateBatchAttachmentFile($data);

        return ds_json_success('操作成功');
    }

    /**
     * 批量删除附件文件
     * @OA\Delete(
     *     path="/api/attachment/files/batch",
     *     tags={"api/attachment/AttachmentFile"},
     *     summary="批量删除附件文件",
     *     description="批量删除指定的附件文件",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), description="文件ID数组", example={1, 2, 3})
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
    public function deleteBatchAttachmentFile(){
        $data = array(
            'ids' => input('param.ids/a'),
        );

        // 使用验证器进行验证
        $this->validate($data, 'app\api\controller\attachment\validate\AttachmentFileValidate.deleteBatch');

        $result = (new AttachmentFileService())->deleteBatchAttachmentFile($data['ids']);

        return ds_json_success('删除成功', $result);
    }

}