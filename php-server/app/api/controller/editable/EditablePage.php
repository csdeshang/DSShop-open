<?php

namespace app\api\controller\editable;
use app\deshang\base\controller\BaseApiController;

use app\api\service\editable\EditablePageService;

/**
 * @OA\Tag(name="api/editable/EditablePage", description="可编辑页面接口")
 */
class EditablePage extends BaseApiController{

    /**
     * 获取可编辑页面信息
     * @OA\Get(
     *     path="/api/editable/page/info",
     *     tags={"api/editable/EditablePage"},
     *     summary="获取可编辑页面信息",
     *     description="获取指定平台和类型的可编辑页面内容",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=true,
     *         description="平台标识",
     *         @OA\Schema(type="string", example="mall")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         description="页面类型",
     *         @OA\Schema(type="string", example="home")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="页面ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="device_type",
     *         in="query",
     *         required=false,
     *         description="设备类型 pc-电脑端 mobile-手机端",
     *         @OA\Schema(type="string", example="mobile")
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
    public function getEditablePageInfo(){
        $data = array(
            'platform' => input('param.platform'),
            'type' => input('param.type'),
            'id' => input('param.id'),
            'device_type' => input('param.device_type'),
        );

        $this->validate($data, 'app\api\controller\editable\validate\EditablePageValidate.info');

        $result = (new EditablePageService())->getEditablePageInfo($data);
        return ds_json_success('操作成功',$result);
    }

}