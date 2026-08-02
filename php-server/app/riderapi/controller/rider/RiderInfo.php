<?php

namespace app\riderapi\controller\rider;
use app\deshang\base\controller\BaseRiderController;

use app\riderapi\service\rider\RiderInfoService;

/**
 * @OA\Tag(name="riderapi/rider/RiderInfo", description="骑手信息管理接口")
 */
class RiderInfo extends BaseRiderController{

    /**
     * 获取骑手信息
     * @OA\Get(
     *     path="/riderapi/rider/info",
     *     tags={"riderapi/rider/RiderInfo"},
     *     summary="获取骑手信息",
     *     description="获取当前骑手的详细信息",
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
    public function getRiderInfo(){
        $result = (new RiderInfoService())->getRiderInfo();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新骑手信息
     * @OA\Post(
     *     path="/riderapi/rider/info",
     *     tags={"riderapi/rider/RiderInfo"},
     *     summary="更新骑手信息",
     *     description="更新骑手的基本信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="骑手姓名", example="张师傅"),
     *             @OA\Property(property="mobile", type="string", description="手机号码", example="13800138000")
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
    public function updateRiderInfo(){ 
        $data = array(
            'name' => input('param.name'),
            'mobile' => input('param.mobile'),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderInfoValidate.update');

        $result = (new RiderInfoService())->updateRiderInfo($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 设置骑手状态
     * @OA\Patch(
     *     path="/riderapi/rider/info/status",
     *     tags={"riderapi/rider/RiderInfo"},
     *     summary="设置骑手状态",
     *     description="更新骑手的在线状态",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="integer", description="骑手状态 0离线 1在线", example=1)
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
    public function setRiderStatus(){
        $data = array(
            'status' => input('param.status'),
        );

        // 验证器
        $this->validate($data, 'app\riderapi\controller\rider\validate\RiderInfoValidate.setStatus');

        $result = (new RiderInfoService())->setRiderStatus($data);
        return ds_json_success('操作成功', $result);
    }

}
