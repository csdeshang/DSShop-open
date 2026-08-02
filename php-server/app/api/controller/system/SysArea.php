<?php

namespace app\api\controller\system;
use app\deshang\base\controller\BaseApiController;

use app\api\service\system\SysAreaService;

/**
 * @OA\Tag(name="api/system/SysArea", description="地区信息接口")
 */
class SysArea extends BaseApiController{

    /**
     * 获取地区列表
     * @OA\Get(
     *     path="/api/system/areas/list",
     *     tags={"api/system/SysArea"},
     *     summary="获取地区列表",
     *     description="根据父级ID获取地区列表",
     *     @OA\Parameter(
     *         name="pid",
     *         in="query",
     *         required=false,
     *         description="父级地区ID，0为获取省份",
     *         @OA\Schema(type="integer", default=0, example=1)
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
    public function getSysAreaList(){

        $data = array(
            'pid' => (int) input('param.pid'),
        );

        $this->validate($data, 'app\api\controller\system\validate\SysAreaValidate.list');

        $result = (new SysAreaService())->getSysAreaList($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取地区树形结构
     * @OA\Get(
     *     path="/api/system/areas/tree",
     *     tags={"api/system/SysArea"},
     *     summary="获取地区树形结构",
     *     description="获取完整的地区树形结构数据",
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
    public function getSysAreaTree(){

        $data = array(
  
        );
        $result = (new SysAreaService())->getSysAreaList($data);

        $result = linearToTree($result,'id','pid');

        return ds_json_success('操作成功',$result);
    }


}