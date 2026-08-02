<?php

namespace app\adminapi\controller\tblStore;

use app\adminapi\service\store\TblStoreCouponService;
use app\deshang\base\controller\BaseAdminController;

/**
 * @OA\Tag(name="admin-api/tblStore/TblStoreCoupon", description="店铺优惠券管理接口")
 */
class TblStoreCoupon extends BaseAdminController
{
    /**
     * 获取优惠券分页列表
     * @OA\Get(
     *     path="/adminapi/tbl-store/coupons/pages",
     *     tags={"admin-api/tblStore/TblStoreCoupon"},
     *     summary="获取优惠券分页列表",
     *     description="分页获取店铺优惠券列表",
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
    public function getTblStoreCouponPages()
    {
        $data = [
            'coupon_name' => input('param.coupon_name'),
            'coupon_type' => input('param.coupon_type'),
        ];
        $result = (new TblStoreCouponService())->getTblStoreCouponPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取优惠券列表
     * @OA\Get(
     *     path="/adminapi/tbl-store/coupons/list",
     *     tags={"admin-api/tblStore/TblStoreCoupon"},
     *     summary="获取优惠券列表",
     *     description="获取全部店铺优惠券列表",
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
    public function getTblStoreCouponList()
    {
        $data = [];
        $result = (new TblStoreCouponService())->getTblStoreCouponList($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取优惠券详情
     * @OA\Get(
     *     path="/adminapi/tbl-store/coupons/{id}",
     *     tags={"admin-api/tblStore/TblStoreCoupon"},
     *     summary="获取优惠券详情",
     *     description="获取指定优惠券的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="优惠券ID",
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
    public function getTblStoreCouponInfo($id)
    {
        $this->validate(['id' => $id], 'app\adminapi\controller\tblStore\validate\TblStoreCouponValidate.info');

        $result = (new TblStoreCouponService())->getTblStoreCouponInfo((int)$id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 创建优惠券
     * @OA\Post(
     *     path="/adminapi/tbl-store/coupons",
     *     tags={"admin-api/tblStore/TblStoreCoupon"},
     *     summary="创建优惠券",
     *     description="创建新的店铺优惠券",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"coupon_name", "coupon_type", "discount_value"},
     *             @OA\Property(property="coupon_name", type="string", description="优惠券名称", example="满100减20"),
     *             @OA\Property(property="coupon_type", type="integer", description="优惠券类型 1满减 2折扣", example=1),
     *             @OA\Property(property="discount_value", type="number", description="折扣值", example=20.00),
     *             @OA\Property(property="min_spend", type="number", description="最低消费", example=100.00),
     *             @OA\Property(property="start_time", type="string", description="开始时间", example="2024-01-01 00:00:00"),
     *             @OA\Property(property="end_time", type="string", description="结束时间", example="2024-12-31 23:59:59"),
     *             @OA\Property(property="total_stock", type="integer", description="总库存", example=1000),
     *             @OA\Property(property="limit_per_user", type="integer", description="每人限领", example=1),
     *             @OA\Property(property="claim_type", type="integer", description="领取方式", example=1),
     *             @OA\Property(property="points_required", type="integer", description="所需积分", example=0),
     *             @OA\Property(property="sort", type="integer", description="排序", example=255)
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
    public function createTblStoreCoupon()
    {
        $data = [
            'coupon_name' => input('param.coupon_name'),
            'coupon_type' => input('param.coupon_type'),
            'discount_value' => input('param.discount_value'),
            'min_spend' => input('param.min_spend'),
            'start_time' => strtotime(input('param.start_time')),
            'end_time' => strtotime(input('param.end_time')),
            'total_stock' => input('param.total_stock'),
            'limit_per_user' => input('param.limit_per_user'),
            'claim_type' => input('param.claim_type'),
            'points_required' => input('param.points_required'),
            'sort' => input('param.sort'),
        ];

        $this->validate($data, 'app\adminapi\controller\tblStore\validate\TblStoreCouponValidate.create');

        $result = (new TblStoreCouponService())->createTblStoreCoupon($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新优惠券
     * @OA\Put(
     *     path="/adminapi/tbl-store/coupons/{id}",
     *     tags={"admin-api/tblStore/TblStoreCoupon"},
     *     summary="更新优惠券",
     *     description="更新指定的店铺优惠券",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="优惠券ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="coupon_name", type="string", description="优惠券名称", example="满100减20"),
     *             @OA\Property(property="coupon_type", type="integer", description="优惠券类型 1满减 2折扣", example=1),
     *             @OA\Property(property="discount_value", type="number", description="折扣值", example=20.00),
     *             @OA\Property(property="min_spend", type="number", description="最低消费", example=100.00),
     *             @OA\Property(property="start_time", type="string", description="开始时间", example="2024-01-01 00:00:00"),
     *             @OA\Property(property="end_time", type="string", description="结束时间", example="2024-12-31 23:59:59"),
     *             @OA\Property(property="total_stock", type="integer", description="总库存", example=1000),
     *             @OA\Property(property="limit_per_user", type="integer", description="每人限领", example=1),
     *             @OA\Property(property="claim_type", type="integer", description="领取方式", example=1),
     *             @OA\Property(property="points_required", type="integer", description="所需积分", example=0),
     *             @OA\Property(property="sort", type="integer", description="排序", example=255)
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
    public function updateTblStoreCoupon($id)
    {
        $data = [
            'id' => (int)$id,
            'coupon_name' => input('param.coupon_name'),
            'coupon_type' => input('param.coupon_type'),
            'discount_value' => input('param.discount_value'),
            'min_spend' => input('param.min_spend'),
            'start_time' => strtotime(input('param.start_time')),
            'end_time' => strtotime(input('param.end_time')),
            'total_stock' => input('param.total_stock'),
            'limit_per_user' => input('param.limit_per_user'),
            'claim_type' => input('param.claim_type'),
            'points_required' => input('param.points_required'),
            'sort' => input('param.sort'),
        ];

        $this->validate($data, 'app\adminapi\controller\tblStore\validate\TblStoreCouponValidate.update');

        $result = (new TblStoreCouponService())->updateTblStoreCoupon($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取优惠券领取记录
     * @OA\Get(
     *     path="/adminapi/tbl-store/coupon-users/pages",
     *     tags={"admin-api/tblStore/TblStoreCoupon"},
     *     summary="获取优惠券领取记录",
     *     description="分页获取优惠券用户领取记录",
     *     @OA\Parameter(
     *         name="coupon_id",
     *         in="query",
     *         required=false,
     *         description="优惠券ID",
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
    public function getTblStoreCouponUserPages()
    {
        $data = [
            'coupon_id' => input('param.coupon_id'),
        ];

        $this->validate($data, 'app\adminapi\controller\tblStore\validate\TblStoreCouponValidate.userPages');

        $result = (new TblStoreCouponService())->getTblStoreCouponUserPages($data);
        return ds_json_success('操作成功', $result);
    }
}
