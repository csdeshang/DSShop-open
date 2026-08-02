<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserTblStoreCouponService;

/**
 * @OA\Tag(name="api/user/UserTblStoreCoupon", description="用户店铺优惠券管理接口")
 */
// 用户店铺优惠券
class UserTblStoreCoupon extends BaseUserController
{
    /**
     * 获取用户店铺优惠券列表
     * @OA\Get(
     *     path="/api/user/store-coupons/pages",
     *     tags={"api/user/UserTblStoreCoupon"},
     *     summary="获取用户店铺优惠券列表",
     *     description="分页获取用户的店铺优惠券列表",
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="优惠券状态",
     *         required=false,
     *         @OA\Schema(type="string", example="available")
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
    // 获取用户店铺优惠券列表
    public function getUserTblStoreCouponPages(){
        $data = array(
            'status' => input('param.status'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserTblStoreCouponValidate.pages');
        
        $result = (new UserTblStoreCouponService())->getUserTblStoreCouponPages($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 用户领取店铺优惠券
     * @OA\Post(
     *     path="/api/user/store-coupons/{id}/receive",
     *     tags={"api/user/UserTblStoreCoupon"},
     *     summary="领取店铺优惠券",
     *     description="用户领取指定的店铺优惠券",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="优惠券ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="领取成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    // 用户领取店铺优惠券
    public function receiveTblStoreCoupon($id){
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserTblStoreCouponValidate.receive');

        $result = (new UserTblStoreCouponService())->receiveTblStoreCoupon($id);
        return ds_json_success('操作成功',$result);
    }




}



