<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserAddressValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'integer|egt:0',
        'real_name' => 'require|max:32',
        'mob_phone' => 'require|mobile|max:11',
        'province_id' => 'require|integer|gt:0',
        'city_id' => 'require|integer|gt:0',
        'district_id' => 'require|integer|gt:0',
        'area_id' => 'integer|egt:0',
        'area_info' => 'require|max:128',
        'poi_address' => 'max:255',
        'poi_name' => 'max:100',
        'house_number' => 'max:100',
        'address_detail' => 'require|max:255',
        'is_default' => 'in:0,1',
        'longitude' => 'checkLongitude',
        'latitude' => 'checkLatitude',
    ];

    protected $message = [
        'id.integer' => '地址ID必须为整数',
        'id.egt' => '地址ID必须大于等于0',
        'real_name.require' => '收货人姓名不能为空',
        'real_name.max' => '收货人姓名不能超过32位',
        'mob_phone.require' => '手机号不能为空',
        'mob_phone.mobile' => '手机号格式不正确',
        'mob_phone.max' => '手机号不能超过11位',
        'province_id.require' => '省份不能为空',
        'province_id.integer' => '省份ID必须为整数',
        'province_id.gt' => '省份ID必须大于0',
        'city_id.require' => '城市不能为空',
        'city_id.integer' => '城市ID必须为整数',
        'city_id.gt' => '城市ID必须大于0',
        'district_id.require' => '区县不能为空',
        'district_id.integer' => '区县ID必须为整数',
        'district_id.gt' => '区县ID必须大于0',
        'area_id.integer' => '街道ID必须为整数',
        'area_id.egt' => '街道ID必须大于等于0',
        'area_info.require' => '地区信息不能为空',
        'area_info.max' => '地区信息不能超过128位',
        'poi_address.max' => 'POI地址不能超过255位',
        'poi_name.max' => 'POI名称不能超过100位',
        'house_number.max' => '门牌号不能超过100位',
        'address_detail.require' => '详细地址不能为空',
        'address_detail.max' => '详细地址不能超过255位',
        'is_default.in' => '默认地址设置值无效',
        'longitude.checkLongitude' => '经度格式不正确',
        'latitude.checkLatitude' => '纬度格式不正确',
    ];

    protected $scene = [
        'create' => ['real_name', 'mob_phone', 'province_id', 'city_id', 'district_id', 'area_id', 'area_info', 'poi_address', 'poi_name', 'house_number', 'address_detail', 'is_default', 'longitude', 'latitude'],
        'update' => ['id', 'real_name', 'mob_phone', 'province_id', 'city_id', 'district_id', 'area_id', 'area_info', 'poi_address', 'poi_name', 'house_number', 'address_detail', 'is_default', 'longitude', 'latitude'],
        'delete' => ['id'],
        'info' => ['id'],
        'setDefault' => ['id'],
    ];
} 