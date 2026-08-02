<?php

namespace app\api\controller\enum;

use app\deshang\base\controller\BaseApiController;
use app\deshang\service\enum\DeshangEnumService;

/**
 * 枚举数据控制器
 * 
 * 提供枚举数据的API接口，支持获取全部枚举或指定类型枚举
 * 统一使用 module.table.field 格式的枚举类型标识
 */

 /**
 * @OA\Tag(name="api/enum/Enum", description="枚举数据接口")
 */
class Enum extends BaseApiController
{
    /**
     * 获取枚举数据
     * @OA\Get(
     *     path="/api/enums/data",
     *     tags={"api/enum/Enum"},
     *     summary="获取枚举数据",
     *     description="获取指定类型的枚举数据，使用module.table.field格式",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         description="枚举类型，格式: module.table.field",
     *         @OA\Schema(type="string", example="common.user.status")
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
    public function getEnumData()
    {
        // 获取请求参数
        $type = $this->request->param('type', '');

        
        if (empty($type)) {
            return ds_json_error('枚举类型不能为空');
        }

        // 创建枚举服务实例
        $enumService = new DeshangEnumService();

        // 获取数据
        try {
            if (empty($type)) {
                // 获取所有数据
                $data = $enumService->getAllEnumData();
            } else {
                // 获取指定类型数据
                $data = $enumService->getEnumData($type);
            }
            
            return ds_json_success('获取枚举数据成功', $data);
        } catch (\Exception $e) {
            return ds_json_error('获取枚举数据失败: ' . $e->getMessage());
        }
    }
    
  
 
  
}