<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysAgreementService;

/**
 * @OA\Tag(name="api/system/SysAgreement", description="系统协议接口")
 */
class SysAgreement extends BaseApiController{

    /**
     * 获取系统协议信息
     * @OA\Get(
     *     path="/api/system/agreements/{code}",
     *     tags={"api/system/SysAgreement"},
     *     summary="获取系统协议信息",
     *     description="根据协议代码获取系统协议详细信息",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         description="协议代码",
     *         @OA\Schema(type="string", example="privacy_policy")
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
    public function getSysAgreementInfo($code = ''){

        $code = $code ?: input('param.code', '');
        $data = array(
            'code' => $code,
        );

        $this->validate($data, 'app\api\controller\system\validate\SysAgreementValidate.info');
        
        $result = (new SysAgreementService())->getSysAgreementInfo($data);
        return ds_json_success('操作成功',$result);
    }




}