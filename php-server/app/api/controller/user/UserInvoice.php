<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserInvoiceService;

/**
 * @OA\Tag(name="api/user/UserInvoice", description="用户发票信息管理接口")
 */
class UserInvoice extends BaseUserController
{
    /**
     * 获取用户发票信息列表
     * @OA\Get(
     *     path="/api/user/invoices",
     *     tags={"api/user/UserInvoice"},
     *     summary="获取用户发票信息列表",
     *     description="获取当前用户保存的发票信息列表",
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
    public function getUserInvoiceList()
    {
        $data = (new UserInvoiceService())->getUserInvoiceList();
        return ds_json_success('操作成功', $data);
    }

    /**
     * 创建用户发票信息
     * @OA\Post(
     *     path="/api/user/invoices",
     *     tags={"api/user/UserInvoice"},
     *     summary="创建用户发票信息",
     *     description="创建新的用户常用发票信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title_name", type="string", description="本地备注名", example="公司抬头"),
     *             @OA\Property(property="invoice_type", type="integer", description="抬头类型 1个人 2企业", example=2),
     *             @OA\Property(property="invoice_kind", type="integer", description="发票种类 1普通发票 2增值税专票", example=1),
     *             @OA\Property(property="invoice_title", type="string", description="发票抬头", example="某某科技有限公司"),
     *             @OA\Property(property="tax_number", type="string", description="纳税人识别号", example="9133XXXXXXXXXXXXXX"),
     *             @OA\Property(property="register_address", type="string", description="注册地址", example="浙江省杭州市西湖区XX路"),
     *             @OA\Property(property="register_phone", type="string", description="注册电话", example="0571-88888888"),
     *             @OA\Property(property="bank_name", type="string", description="开户银行", example="中国银行杭州分行"),
     *             @OA\Property(property="bank_account", type="string", description="银行账号", example="6222020202020202"),
     *             @OA\Property(property="receiver_email", type="string", description="收票邮箱", example="invoice@example.com"),
     *             @OA\Property(property="receiver_mobile", type="string", description="收票手机", example="13800138000"),
     *             @OA\Property(property="is_default", type="integer", description="是否默认 0否 1是", example=1)
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
    public function createUserInvoice()
    {
        $data = [
            'title_name' => input('param.title_name'),
            'invoice_type' => (int) input('param.invoice_type', 1),
            'invoice_kind' => (int) input('param.invoice_kind', 1),
            'invoice_title' => input('param.invoice_title'),
            'tax_number' => input('param.tax_number', ''),
            'register_address' => input('param.register_address', ''),
            'register_phone' => input('param.register_phone', ''),
            'bank_name' => input('param.bank_name', ''),
            'bank_account' => input('param.bank_account', ''),
            'receiver_email' => input('param.receiver_email', ''),
            'receiver_mobile' => input('param.receiver_mobile', ''),
            'is_default' => (int) input('param.is_default', 0),
        ];

        $this->validate($data, 'app\api\controller\user\validate\UserInvoiceValidate.create');
        $result = (new UserInvoiceService())->createUserInvoice($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新用户发票信息
     * @OA\Put(
     *     path="/api/user/invoices/{id}",
     *     tags={"api/user/UserInvoice"},
     *     summary="更新用户发票信息",
     *     description="更新指定的用户发票信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="发票信息ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title_name", type="string", description="本地备注名", example="公司抬头"),
     *             @OA\Property(property="invoice_type", type="integer", description="抬头类型 1个人 2企业", example=2),
     *             @OA\Property(property="invoice_kind", type="integer", description="发票种类 1普通发票 2增值税专票", example=1),
     *             @OA\Property(property="invoice_title", type="string", description="发票抬头", example="某某科技有限公司"),
     *             @OA\Property(property="tax_number", type="string", description="纳税人识别号", example="9133XXXXXXXXXXXXXX"),
     *             @OA\Property(property="register_address", type="string", description="注册地址", example="浙江省杭州市西湖区XX路"),
     *             @OA\Property(property="register_phone", type="string", description="注册电话", example="0571-88888888"),
     *             @OA\Property(property="bank_name", type="string", description="开户银行", example="中国银行杭州分行"),
     *             @OA\Property(property="bank_account", type="string", description="银行账号", example="6222020202020202"),
     *             @OA\Property(property="receiver_email", type="string", description="收票邮箱", example="invoice@example.com"),
     *             @OA\Property(property="receiver_mobile", type="string", description="收票手机", example="13800138000"),
     *             @OA\Property(property="is_default", type="integer", description="是否默认 0否 1是", example=1)
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
    public function updateUserInvoice($id)
    {
        $data = [
            'title_name' => input('param.title_name'),
            'invoice_type' => (int) input('param.invoice_type', 1),
            'invoice_kind' => (int) input('param.invoice_kind', 1),
            'invoice_title' => input('param.invoice_title'),
            'tax_number' => input('param.tax_number', ''),
            'register_address' => input('param.register_address', ''),
            'register_phone' => input('param.register_phone', ''),
            'bank_name' => input('param.bank_name', ''),
            'bank_account' => input('param.bank_account', ''),
            'receiver_email' => input('param.receiver_email', ''),
            'receiver_mobile' => input('param.receiver_mobile', ''),
            'is_default' => (int) input('param.is_default', 0),
        ];

        $this->validate(array_merge($data, ['id' => (int) $id]), 'app\api\controller\user\validate\UserInvoiceValidate.update');
        $result = (new UserInvoiceService())->updateUserInvoice((int) $id, $data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 删除用户发票信息
     * @OA\Delete(
     *     path="/api/user/invoices/{id}",
     *     tags={"api/user/UserInvoice"},
     *     summary="删除用户发票信息",
     *     description="删除指定的用户发票信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="发票信息ID",
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
    public function deleteUserInvoice($id)
    {
        $data = ['id' => (int) $id];
        $this->validate($data, 'app\api\controller\user\validate\UserInvoiceValidate.delete');
        $result = (new UserInvoiceService())->deleteUserInvoice($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 获取用户发票信息详情
     * @OA\Get(
     *     path="/api/user/invoices/{id}",
     *     tags={"api/user/UserInvoice"},
     *     summary="获取用户发票信息详情",
     *     description="获取指定发票信息的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="发票信息ID",
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
    public function getUserInvoiceInfo($id)
    {
        $data = ['id' => (int) $id];
        $this->validate($data, 'app\api\controller\user\validate\UserInvoiceValidate.info');
        $result = (new UserInvoiceService())->getUserInvoiceInfo($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 设置默认发票信息
     * @OA\Patch(
     *     path="/api/user/invoices/{id}/default",
     *     tags={"api/user/UserInvoice"},
     *     summary="设置默认发票信息",
     *     description="设置指定发票信息为默认项",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="发票信息ID",
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
    public function setDefaultUserInvoice($id)
    {
        $data = ['id' => (int) $id];
        $this->validate($data, 'app\api\controller\user\validate\UserInvoiceValidate.setDefault');
        $result = (new UserInvoiceService())->setDefaultUserInvoice($data);
        return ds_json_success('操作成功', $result);
    }
}

