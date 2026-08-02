<?php

namespace app\techapi\controller\technician;
use app\deshang\base\controller\BaseTechnicianController;

use app\techapi\service\technician\TechnicianGoodsRelService;
use app\deshang\exceptions\CommonException;

/**
 * @OA\Tag(name="techapi/technician/TechnicianGoodsRel", description="师傅商品关联管理接口")
 */
class TechnicianGoodsRel extends BaseTechnicianController{

    /**
     * 获取师傅商品关联分页列表
     * @OA\Get(
     *     path="/techapi/technician/goods-rels/pages",
     *     tags={"techapi/technician/TechnicianGoodsRel"},
     *     summary="获取师傅商品关联分页列表",
     *     description="获取当前师傅可关联的商品分页列表",
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
    public function getTechnicianGoodsRelPages(){
        $result = (new TechnicianGoodsRelService())->getTechnicianGoodsRelPages();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新师傅商品关联
     * @OA\Put(
     *     path="/techapi/technician/goods-rels/{goods_id}",
     *     tags={"techapi/technician/TechnicianGoodsRel"},
     *     summary="更新师傅商品关联",
     *     description="师傅关联或取消关联指定商品",
     *     @OA\Parameter(
     *         name="goods_id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
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
    public function updateTechnicianGoodsRel($goods_id){
        // 验证器
        $this->validate(['goods_id'=>$goods_id], 'app\techapi\controller\technician\validate\TechnicianGoodsRelValidate.update');

        $result = (new TechnicianGoodsRelService())->updateTechnicianGoodsRel((int)$goods_id);
        return ds_json_success('操作成功', $result);
    }
}