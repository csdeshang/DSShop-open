<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysPlatformService;

/**
 * @OA\Tag(name="api/system/SysPlatform", description="系统平台接口")
 */
class SysPlatform extends BaseApiController{

    /**
     * @OA\Get(
     *     path="/api/system/platforms/list",
     *     tags={"api/system/SysPlatform"},
     *     summary="获取系统平台列表",
     *     description="获取系统支持的平台列表",
     *     @OA\Parameter(
     *         name="scene",
     *         in="query",
     *         description="应用场景",
     *         required=false,
     *         @OA\Schema(type="string", example="mall")
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
    public function getSysPlatformList(){

        // 参数预留
        $data = array(
            'scene' => input('param.scene'),
        );

        $this->validate($data, 'app\api\controller\system\validate\SysPlatformValidate.list');

        $result = (new SysPlatformService())->getSysPlatformList($data);
        return ds_json_success('操作成功',$result);
    }


}