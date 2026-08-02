<?php

namespace app\adminapi\controller\tblStore\validate;

use app\common\enum\system\SysPrinterProviderEnum;
use app\deshang\base\BaseValidate;

class TblStorePrinterValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'printer_id' => 'require|integer|gt:0',
        'printer_name' => 'require|max:100',
        'printer_sn' => 'require|max:50',
        'printer_key' => 'require|max:100',
        'printer_mobile' => 'max:20',
        'printer_provider' => 'require|checkPrinterProvider',
        'is_enabled' => 'in:0,1',
        'order_id' => 'require|integer|gt:0',
        'print_type' => 'in:1,2',
        'print_status' => 'in:0,1',
    ];

    protected $message = [
        'id.require' => '打印机ID不能为空',
        'id.integer' => '打印机ID必须是整数',
        'id.gt' => '打印机ID必须大于0',
        'printer_id.require' => '打印机ID不能为空',
        'printer_id.integer' => '打印机ID必须是整数',
        'printer_id.gt' => '打印机ID必须大于0',
        'printer_name.require' => '打印机名称不能为空',
        'printer_name.max' => '打印机名称不能超过100个字符',
        'printer_sn.require' => '打印机编号不能为空',
        'printer_sn.max' => '打印机编号不能超过50个字符',
        'printer_key.require' => '打印机密钥不能为空',
        'printer_key.max' => '打印机密钥不能超过100个字符',
        'printer_mobile.max' => '打印机手机号不能超过20个字符',
        'printer_provider.require' => '打印机服务商不能为空',
        'printer_provider.checkPrinterProvider' => '打印机服务商值不正确',
        'is_enabled.in' => '启用状态值不正确',
        'order_id.require' => '订单ID不能为空',
        'order_id.integer' => '订单ID必须是整数',
        'order_id.gt' => '订单ID必须大于0',
        'print_type.in' => '打印类型值不正确',
        'print_status.in' => '打印状态值不正确（0:失败 1:成功）',
    ];

    protected $scene = [
        'info' => ['id'],
        'pages' => ['printer_name', 'printer_provider'],
        'create' => ['printer_name', 'printer_sn', 'printer_key', 'printer_mobile', 'printer_provider', 'is_enabled'],
        'update' => ['id', 'printer_name', 'printer_sn', 'printer_key', 'printer_mobile', 'printer_provider', 'is_enabled'],
        'delete' => ['id'],
        'bind' => ['printer_id'],
        'unbind' => ['printer_id'],
        'print' => ['order_id', 'printer_id'],
        'status' => ['printer_id'],
        'logs' => ['print_status', 'print_type'],
    ];

    public function checkPrinterProvider($value, $rule, $data)
    {
        if ($value === '' || $value === null) {
            return true;
        }

        return array_key_exists($value, SysPrinterProviderEnum::getPrinterProviderDict());
    }
}
