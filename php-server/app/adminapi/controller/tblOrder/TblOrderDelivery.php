<?php

namespace app\adminapi\controller\tblOrder;

use app\adminapi\service\order\TblOrderDeliveryService;
use app\common\enum\order\TblOrderEnum;
use app\deshang\base\controller\BaseAdminController;

/**
 * @OA\Tag(name="admin-api/tblOrder/TblOrderDelivery", description="订单配送管理接口")
 */
class TblOrderDelivery extends BaseAdminController
{
    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/deliveries/delivery-dict",
     *     summary="获取订单配送方式字典",
     *     tags={"admin-api/tblOrder/TblOrderDelivery"},
     *     @OA\Parameter(name="platform", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function getTblOrderDeliveryDict()
    {
        $platform = input('param.platform');
        $this->validate(['platform' => $platform], 'app\adminapi\controller\tblOrder\validate\TblOrderDeliveryValidate.dict');

        switch ($platform) {
            case 'mall':
                $result = TblOrderEnum::getMallOrderDeliveryDict();
                break;
            case 'food':
                $result = TblOrderEnum::getFoodOrderDeliveryDict();
                break;
            case 'house':
                $result = TblOrderEnum::getHouseOrderDeliveryDict();
                break;
            case 'kms':
                $result = TblOrderEnum::getKmsOrderDeliveryDict();
                break;
            default:
                return ds_json_error('平台参数错误');
        }

        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/adminapi/tbl-order/deliveries",
     *     summary="创建订单配送",
     *     tags={"admin-api/tblOrder/TblOrderDelivery"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="delivery_method", type="integer", example=1),
     *         @OA\Property(property="order_id", type="integer", example=1),
     *         @OA\Property(property="express_type", type="string"),
     *         @OA\Property(property="express_company", type="string"),
     *         @OA\Property(property="express_number", type="string"),
     *         @OA\Property(property="rider_id", type="integer"),
     *         @OA\Property(property="technician_id", type="integer")
     *     )),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function addTblOrderDelivery()
    {
        $data = array(
            'delivery_method' => input('param.delivery_method'),
            'order_id' => input('param.order_id'),
            'express_type' => input('param.express_type'),
            'express_company' => input('param.express_company'),
            'express_number' => input('param.express_number'),
            'rider_id' => input('param.rider_id'),
            'technician_id' => input('param.technician_id'),
        );
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderDeliveryValidate.add');

        $result = (new TblOrderDeliveryService())->addTblOrderDelivery($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-order/deliveries/pages",
     *     summary="获取订单配送分页列表",
     *     tags={"admin-api/tblOrder/TblOrderDelivery"},
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=10000),
     *         @OA\Property(property="msg", type="string", example="操作成功"),
     *         @OA\Property(property="data", type="object")
     *     ))
     * )
     */
    public function getTblOrderDeliveryPages()
    {
        $data = array(
            'rider_id' => input('param.rider_id'),
            'technician_id' => input('param.technician_id'),
            'delivery_method' => input('param.delivery_method'),
        );
        $this->validate($data, 'app\adminapi\controller\tblOrder\validate\TblOrderDeliveryValidate.pages');

        $result = (new TblOrderDeliveryService())->getTblOrderDeliveryPages($data);
        return ds_json_success('操作成功', $result);
    }
}
