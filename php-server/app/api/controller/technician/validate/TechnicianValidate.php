<?php

namespace app\api\controller\technician\validate;

use app\deshang\base\BaseValidate;

/**
 * 师傅验证器
 */
class TechnicianValidate extends BaseValidate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        'goods_id' => 'integer|gt:0',
        'longitude' => 'checkLongitude',
        'latitude' => 'checkLatitude',
        'limit' => 'integer|between:1,100',
        'id' => 'require|integer|gt:0',
        'page_current' => 'integer|egt:1',
        'page_size' => 'integer|between:1,100',
    ];

    /**
     * 验证消息
     * @var array
     */
    protected $message = [
        'goods_id.integer' => '商品ID必须为整数',
        'goods_id.gt' => '商品ID必须大于0',
        'longitude.checkLongitude' => '经度值无效',
        'latitude.checkLatitude' => '纬度值无效',
        'limit.integer' => '限制条数必须为整数',
        'limit.between' => '限制条数必须在1-100之间',
        'id.require' => '师傅ID不能为空',
        'id.integer' => '师傅ID必须为整数',
        'id.gt' => '师傅ID必须大于0',
        'page_current.integer' => '当前页码必须为整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须为整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    /**
     * 验证场景
     * @var array
     */
    protected $scene = [
        'list' => ['goods_id', 'longitude', 'latitude', 'limit'],
        'pages' => ['goods_id', 'longitude', 'latitude', 'page_current', 'page_size'],
        'detail' => ['id', 'longitude', 'latitude'],
    ];
} 