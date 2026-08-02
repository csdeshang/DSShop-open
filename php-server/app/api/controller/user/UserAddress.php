<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserAddressService;

/**
 * @OA\Tag(name="api/user/UserAddress", description="用户地址管理接口")
 */
class UserAddress extends BaseUserController
{
    /**
     * 获取用户地址列表
     * @OA\Get(
     *     path="/api/user/addresses",
     *     tags={"api/user/UserAddress"},
     *     summary="获取用户地址列表",
     *     description="获取当前用户的地址列表",
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
    public function getUserAddressList()
    {
        $data = (new UserAddressService())->getUserAddressList();
        return ds_json_success('操作成功',$data);
    }

    /**
     * 创建用户地址
     * @OA\Post(
     *     path="/api/user/addresses",
     *     tags={"api/user/UserAddress"},
     *     summary="创建用户地址",
     *     description="创建新的用户地址信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="real_name", type="string", description="收货人姓名", example="张三"),
     *             @OA\Property(property="mob_phone", type="string", description="手机号", example="13800138000"),
     *             @OA\Property(property="province_id", type="integer", description="省ID", example=1),
     *             @OA\Property(property="city_id", type="integer", description="市ID", example=1),
     *             @OA\Property(property="district_id", type="integer", description="区ID", example=1),
     *             @OA\Property(property="area_id", type="integer", description="街道ID", example=1),
     *             @OA\Property(property="area_info", type="string", description="地区信息", example="北京市朝阳区"),
     *             @OA\Property(property="address_detail", type="string", description="详细地址", example="三里屯街道1号"),
     *             @OA\Property(property="is_default", type="integer", description="是否默认 0否 1是", example=0),
     *             @OA\Property(property="longitude", type="string", description="经度", example="116.404"),
     *             @OA\Property(property="latitude", type="string", description="纬度", example="39.915")
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
    public function createUserAddress()
    {
        $data = array(
            'real_name' => input('param.real_name'),
            'mob_phone' => input('param.mob_phone'),
            'province_id' => (int) input('param.province_id'),
            'city_id' => (int) input('param.city_id'),
            'district_id' => (int) input('param.district_id'),
            'area_id' => (int) input('param.area_id'),
            'area_info' => input('param.area_info'),
            'poi_address' => input('param.poi_address'),
            'poi_name' => input('param.poi_name'),
            'house_number' => input('param.house_number'),
            'address_detail' => input('param.address_detail'),
            'is_default' => (int) input('param.is_default'),
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserAddressValidate.create');

        $result = (new UserAddressService())->createUserAddress($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 更新用户地址
     * @OA\Put(
     *     path="/api/user/addresses/{id}",
     *     tags={"api/user/UserAddress"},
     *     summary="更新用户地址",
     *     description="更新用户地址信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="地址ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="real_name", type="string", description="收货人姓名", example="张三"),
     *             @OA\Property(property="mob_phone", type="string", description="手机号", example="13800138000"),
     *             @OA\Property(property="province_id", type="integer", description="省ID", example=1),
     *             @OA\Property(property="city_id", type="integer", description="市ID", example=1),
     *             @OA\Property(property="district_id", type="integer", description="区ID", example=1),
     *             @OA\Property(property="area_id", type="integer", description="街道ID", example=1),
     *             @OA\Property(property="area_info", type="string", description="地区信息", example="北京市朝阳区"),
     *             @OA\Property(property="address_detail", type="string", description="详细地址", example="三里屯街道1号"),
     *             @OA\Property(property="is_default", type="integer", description="是否默认 0否 1是", example=0),
     *             @OA\Property(property="longitude", type="string", description="经度", example="116.404"),
     *             @OA\Property(property="latitude", type="string", description="纬度", example="39.915")
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
    public function updateUserAddress($id)
    {
        $data = array(
            'real_name' => input('param.real_name'),
            'mob_phone' => input('param.mob_phone'),
            'province_id' => (int) input('param.province_id'),
            'city_id' => (int) input('param.city_id'),
            'district_id' => (int) input('param.district_id'),
            'area_id' => (int) input('param.area_id'),
            'area_info' => input('param.area_info'),
            'poi_address' => input('param.poi_address'),
            'poi_name' => input('param.poi_name'),
            'house_number' => input('param.house_number'),
            'address_detail' => input('param.address_detail'),
            'is_default' => (int) input('param.is_default'),
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
        );

        $this->validate(array_merge($data, ['id' => $id]), 'app\api\controller\user\validate\UserAddressValidate.update');
        
        $result = (new UserAddressService())->updateUserAddress($id, $data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 删除用户地址
     * @OA\Delete(
     *     path="/api/user/addresses/{id}",
     *     tags={"api/user/UserAddress"},
     *     summary="删除用户地址",
     *     description="删除指定的用户地址",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="地址ID",
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
    public function deleteUserAddress($id)
    {
        $data = array(
            'id' => (int) $id,
        );

        $this->validate($data, 'app\api\controller\user\validate\UserAddressValidate.delete');

        $result = (new UserAddressService())->deleteUserAddress($data);
        return ds_json_success('操作成功',$result);
    }

    /**
     * 获取用户地址详情
     * @OA\Get(
     *     path="/api/user/addresses/{id}",
     *     tags={"api/user/UserAddress"},
     *     summary="获取用户地址详情",
     *     description="获取指定地址的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="地址ID",
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
    public function getUserAddressInfo($id)
    {
        $data = array(
            'id' => (int) $id,
        );

        $this->validate($data, 'app\api\controller\user\validate\UserAddressValidate.info');

        $data = (new UserAddressService())->getUserAddressInfo($data);
        return ds_json_success('操作成功',$data);
    }

    /**
     * 设置默认地址
     * @OA\Patch(
     *     path="/api/user/addresses/{id}/default",
     *     tags={"api/user/UserAddress"},
     *     summary="设置默认地址",
     *     description="设置指定地址为默认地址",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="地址ID",
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
    public function setDefaultUserAddress($id)
    {
        $data = array(
            'id' => (int) $id,
        );

        $this->validate($data, 'app\api\controller\user\validate\UserAddressValidate.setDefault');

        $result = (new UserAddressService())->setDefaultUserAddress($data);
        return ds_json_success('操作成功',$result);
    }
}

