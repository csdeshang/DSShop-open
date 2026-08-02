<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysExpressService;

/**
 * @OA\Tag(name="api/system/SysExpress", description="快递公司接口")
 */
class SysExpress extends BaseApiController{

    /**
     * @OA\Get(
     *     path="/api/system/expresses/list",
     *     tags={"api/system/SysExpress"},
     *     summary="获取快递公司列表",
     *     description="获取系统支持的快递公司列表",
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
    public function getSysExpressList(){

        // 参数预留
        $data = array(

        );
        $result = (new SysExpressService())->getSysExpressList($data);
        return ds_json_success('操作成功',$result);
    }


}