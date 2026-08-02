<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\store\TblStoreCouponUserEnum;

class UserTblStoreCouponValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'status' => 'checkStatus',
    ];

    protected $message = [
        'id.require' => '优惠券ID不能为空',
        'id.integer' => '优惠券ID必须为整数',
        'id.gt' => '优惠券ID必须大于0',
        'status.checkStatus' => '优惠券状态值无效',
    ];

    protected $scene = [
        'receive' => ['id'],
        'pages' => ['status'],
    ];

    protected function checkStatus($value, $rule, $data)
    {
        if (empty($value)) {
            return true; // 空值允许
        }
        return array_key_exists($value, TblStoreCouponUserEnum::getStoreCouponUserStatusDict());
    }


} 