<?php

namespace app\api\controller\tblStore;
use app\deshang\base\controller\BaseApiController;

use app\api\service\store\TblStoreCouponService;

/**
 * @OA\Tag(name="api/tblStore/TblStoreCoupon", description="店铺优惠券接口")
 */
class TblStoreCoupon extends BaseApiController{

    


    /**
     * 获取店铺优惠券列表
     * @OA\Get(
     *     path="/api/tbl-store/coupons/list",
     *     tags={"api/tblStore/TblStoreCoupon"},
     *     summary="获取店铺优惠券列表",
     *     description="获取优惠券列表数据",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=true,
     *         description="平台标识",
     *         @OA\Schema(type="string", example="mall")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="优惠券状态：0-未生效，1-生效中，2-已失效",
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
    public function getTblStoreCouponList(){
        $data = array(
            'platform' => input('param.platform'),
            'status' => input('param.status'),
        );

        $this->validate($data, 'app\api\controller\tblStore\validate\TblStoreCouponValidate.list');

        $result = (new TblStoreCouponService())->getTblStoreCouponList($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取店铺优惠券分页列表
     * @OA\Get(
     *     path="/api/tbl-store/coupons/pages",
     *     tags={"api/tblStore/TblStoreCoupon"},
     *     summary="获取店铺优惠券分页列表",
     *     description="获取优惠券分页数据",
     *     @OA\Parameter(
     *         name="platform",
     *         in="query",
     *         required=true,
     *         description="平台标识",
     *         @OA\Schema(type="string", example="mall")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="优惠券状态：0-未生效，1-生效中，2-已失效",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_current",
     *         in="query",
     *         required=false,
     *         description="当前页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
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
    public function getTblStoreCouponPages(){
        $data = array(
            'platform' => input('param.platform'),
            'status' => input('param.status'),
            'page_current' => input('param.page_current', 1),
            'page_size' => input('param.page_size', 10),
        );
        
        $this->validate($data, 'app\api\controller\tblStore\validate\TblStoreCouponValidate.pages');
        
        $result = (new TblStoreCouponService())->getTblStoreCouponPages($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取店铺优惠券详情
     * @OA\Get(
     *     path="/api/tbl-store/coupons/{id}",
     *     tags={"api/tblStore/TblStoreCoupon"},
     *     summary="获取店铺优惠券详情",
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

        $this->validate(['id' => $id], 'app\api\controller\tblStore\validate\TblStoreCouponValidate.info');
        
        $result = (new TblStoreCouponService())->getTblStoreCouponInfo($id);
        return ds_json_success('操作成功', $result);
    }
}
