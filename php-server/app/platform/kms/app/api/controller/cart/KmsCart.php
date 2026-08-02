<?php

namespace app\platform\kms\app\api\controller\cart;

use app\deshang\base\controller\BaseUserController;
use app\platform\kms\app\api\service\cart\KmsCartService;

/**
 * @OA\Tag(name="platform/kms/cart/KmsCart", description="视频教育购物车管理接口")
 */
// 商城购物车控制器
class KmsCart extends BaseUserController
{

    /**
     * @OA\Post(
     *     path="/api/kms/carts",
     *     summary="添加视频教育商品到购物车",
     *     tags={"platform/kms/cart/KmsCart"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="购物车商品信息",
     *         @OA\JsonContent(
     *             required={"goods_id", "quantity"},
     *             @OA\Property(property="goods_id", type="integer", example=1, description="商品ID"),
     *             @OA\Property(property="quantity", type="integer", example=1, description="商品数量"),
     *             @OA\Property(property="sku_id", type="integer", example=1, description="商品规格ID"),
     *             @OA\Property(property="promotion_platform", type="string", example="platform", description="促销平台"),
     *             @OA\Property(property="promotion_type", type="string", example="discount", description="促销类型")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 加入购物车
    public function addKmsCart()
    {
        $data = array(
            'goods_id' => input('param.goods_id'),
            'quantity' => input('param.quantity'),
            'sku_id' => input('param.sku_id'),
            'promotion_platform' => input('param.promotion_platform'),
            'promotion_type' => input('param.promotion_type'),
        );

        $this->validate($data, 'app\platform\kms\app\api\controller\cart\validate\KmsCartValidate.add');

        $result = (new KmsCartService())->addKmsCart($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * @OA\Get(
     *     path="/api/kms/carts",
     *     summary="获取视频教育购物车列表",
     *     tags={"platform/kms/cart/KmsCart"},
     *     @OA\Parameter(name="cart_ids", in="query", required=false, description="购物车ID数组", @OA\Schema(type="array", @OA\Items(type="integer"))),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 获取购物车列表
    public function getKmsCartList()
    {
        $data = array(
            // 'cart_ids' => ids_to_array(input('param.cart_ids')), // 预留暂时注释掉
        );
        
        $result = (new KmsCartService())->getKmsCartList($data);
        return ds_json_success('操作成功',$result);
    }


    /**
     * @OA\Delete(
     *     path="/api/kms/carts/batch",
     *     summary="批量删除视频教育购物车商品",
     *     tags={"platform/kms/cart/KmsCart"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="要删除的购物车ID数组",
     *         @OA\JsonContent(
     *             required={"cart_ids"},
     *             @OA\Property(property="cart_ids", type="array", @OA\Items(type="integer"), example={1,2,3}, description="购物车ID数组")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 删除购物车商品
    public function deleteKmsCart()
    {
        $cart_ids = input('param.cart_ids/a');
        
        $data = ['cart_ids' => $cart_ids];
        $this->validate($data, 'app\platform\kms\app\api\controller\cart\validate\KmsCartValidate.delete');

        $result = (new KmsCartService())->deleteKmsCart($cart_ids);
        return ds_json_success('操作成功',$result);
    }


    /**
     * @OA\Put(
     *     path="/api/kms/carts/{id}",
     *     summary="更新视频教育购物车商品数量",
     *     tags={"platform/kms/cart/KmsCart"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="购物车ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="购物车更新信息",
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=2, description="商品数量")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    // 更新购物车商品数量
    public function updateKmsCart($id)
    {
        $data = array(
            'cart_id' => (int) $id,
            'quantity' => input('param.quantity'),
        );

        $this->validate($data, 'app\platform\kms\app\api\controller\cart\validate\KmsCartValidate.update');

        $result = (new KmsCartService())->updateKmsCart($data);
        return ds_json_success('操作成功',$result);
    }


}
