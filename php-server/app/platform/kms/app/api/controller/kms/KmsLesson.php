<?php

namespace app\platform\kms\app\api\controller\kms;

use app\deshang\base\controller\BaseUserController;
use app\platform\kms\app\api\service\kms\KmsLessonService;

/**
 * @OA\Tag(name="platform/kms/kms/KmsLesson", description="视频教育课时管理接口")
 */
// 课程课时控制器
class KmsLesson extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/kms/lessons",
     *     summary="获取课时列表",
     *     tags={"platform/kms/kms/KmsLesson"},
     *     @OA\Parameter(name="goods_id", in="query", required=true, description="商品ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="array", @OA\Items(type="object"))))
     * )
     */
    // 获取课时列表
    public function getKmsLessonList()
    {
        $data = array(
            'goods_id' => input('param.goods_id'),
        );
        
        $this->validate($data, 'app\platform\kms\app\api\controller\kms\validate\KmsLessonValidate.list');
        
        $result = (new KmsLessonService())->getKmsLessonList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/api/kms/lessons/{id}",
     *     summary="获取课时详情",
     *     tags={"platform/kms/kms/KmsLesson"},
     *     @OA\Parameter(name="id", in="path", required=true, description="课时ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 获取课时详情
    public function getKmsLessonDetail($id)
    {
        $this->validate(['id'=>$id], 'app\platform\kms\app\api\controller\kms\validate\KmsLessonValidate.detail');
        
        $result = (new KmsLessonService())->getKmsLessonDetail($id);
        return ds_json_success('操作成功', $result);
    }



}