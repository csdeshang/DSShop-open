<?php

namespace app\api\controller\tblStore\validate;

use app\deshang\base\BaseValidate;
use app\common\enum\store\TblStoreCouponEnum;

class TblStoreCouponValidate extends BaseValidate
{
    // 定义验证规则
    protected $rule = [
        'platform' => 'checkPlatform',
        'status' => 'checkCouponStatus',
        'id' => 'require|integer|gt:0',
        'page_current' => 'integer|egt:1',
        'page_size' => 'integer|between:1,100',
    ];

    // 定义验证提示
    protected $message = [
        'platform.checkPlatform' => '平台名称不正确',
        'status.checkCouponStatus' => '优惠券状态值无效（0:未开始 1:进行中 2:已结束 3:已关闭）',
        'id.require' => 'ID不能为空',
        'id.integer' => 'ID必须是整数',
        'id.gt' => 'ID必须大于0',
        'page_current.integer' => '当前页码必须为整数',
        'page_current.egt' => '当前页码必须大于等于1',
        'page_size.integer' => '每页数量必须为整数',
        'page_size.between' => '每页数量必须在1-100之间',
    ];

    // 定义场景
    protected $scene = [
        'list' => ['platform', 'status'],
        'pages' => ['platform', 'status', 'page_current', 'page_size'],
        'info' => ['id'],
    ];

    // 验证优惠券状态
    public function checkCouponStatus($value, $rule, $data)
    {
        if (empty($value)) {
            return true; // 空值允许
        }
        return array_key_exists($value, TblStoreCouponEnum::getStoreCouponStatusDict());
    }
}
