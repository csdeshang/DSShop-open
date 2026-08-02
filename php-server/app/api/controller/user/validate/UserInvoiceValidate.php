<?php

namespace app\api\controller\user\validate;

use app\deshang\base\BaseValidate;

class UserInvoiceValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require|integer|gt:0',
        'title_name' => 'max:64',
        'invoice_type' => 'require|in:1,2',
        'invoice_kind' => 'require|in:1,2',
        'invoice_title' => 'require|max:128',
        'tax_number' => 'max:32',
        'register_address' => 'max:255',
        'register_phone' => 'max:32',
        'bank_name' => 'max:64',
        'bank_account' => 'max:64',
        'receiver_email' => 'max:128|email',
        'receiver_mobile' => 'mobile|max:20',
        'is_default' => 'in:0,1',
    ];

    protected $message = [
        'id.require' => '发票信息ID不能为空',
        'id.integer' => '发票信息ID必须为整数',
        'id.gt' => '发票信息ID必须大于0',
        'title_name.max' => '备注名不能超过64个字符',
        'invoice_type.require' => '抬头类型不能为空',
        'invoice_type.in' => '抬头类型不正确',
        'invoice_kind.require' => '发票种类不能为空',
        'invoice_kind.in' => '发票种类不正确',
        'invoice_title.require' => '发票抬头不能为空',
        'invoice_title.max' => '发票抬头不能超过128个字符',
        'tax_number.max' => '纳税人识别号不能超过32个字符',
        'register_address.max' => '注册地址不能超过255个字符',
        'register_phone.max' => '注册电话不能超过32个字符',
        'bank_name.max' => '开户银行不能超过64个字符',
        'bank_account.max' => '银行账号不能超过64个字符',
        'receiver_email.max' => '收票邮箱不能超过128个字符',
        'receiver_email.email' => '收票邮箱格式不正确',
        'receiver_mobile.mobile' => '收票手机格式不正确',
        'receiver_mobile.max' => '收票手机不能超过20个字符',
        'is_default.in' => '默认状态值不正确',
    ];

    protected $scene = [
        'create' => ['title_name', 'invoice_type', 'invoice_kind', 'invoice_title', 'tax_number', 'register_address', 'register_phone', 'bank_name', 'bank_account', 'receiver_email', 'receiver_mobile', 'is_default'],
        'update' => ['id', 'title_name', 'invoice_type', 'invoice_kind', 'invoice_title', 'tax_number', 'register_address', 'register_phone', 'bank_name', 'bank_account', 'receiver_email', 'receiver_mobile', 'is_default'],
        'delete' => ['id'],
        'info' => ['id'],
        'setDefault' => ['id'],
    ];
}

