<?php

namespace app\api\controller\tblGoods;
use app\deshang\base\controller\BaseApiController;

use app\api\service\goods\TblGoodsCategoryService;

/**
 * @OA\Tag(name="api/tblGoods/TblGoodsCategory", description="商品分类接口")
 */
class TblGoodsCategory extends BaseApiController{

    /**
     * 获取商品分类树结构
     * @OA\Get(
     *     path="/api/tbl-goods/categories/tree",
     *     tags={"api/tblGoods/TblGoodsCategory"},
     *     summary="获取商品分类树结构",
     *     description="获取商品分类的树形结构数据",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=false,
     *         description="平台标识",
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
    public function getTblGoodsCategoryTree(){
        $data = array(
            'platform' => input('param.platform', ''),
        );

        $this->validate($data, 'app\api\controller\tblGoods\validate\TblGoodsCategoryValidate.tree');

        $result = (new TblGoodsCategoryService())->getTblGoodsCategoryList($data);

        $result = linearToTree($result,'id','pid');

        return ds_json_success('操作成功',$result);
    }


    

}