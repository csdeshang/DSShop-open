<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserWithdrawalService;

/**
 * @OA\Tag(name="api/user/UserWithdrawal", description="用户提现管理接口")
 */
class UserWithdrawal extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/withdrawal-logs/pages",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="获取用户提现记录",
     *     description="获取当前用户的提现记录列表",
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
    public function getUserWithdrawalLogPages()
    {
        $data = array();
        $data = (new UserWithdrawalService())->getUserWithdrawalLogPages($data);
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Get(
     *     path="/api/user/withdrawal-logs/{id}",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="获取用户提现记录详情",
     *     description="获取指定提现记录的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="提现记录ID",
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
    public function getUserWithdrawalLogInfo($id)
    {
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserWithdrawalValidate.info');
        
        $data = (new UserWithdrawalService())->getUserWithdrawalLogInfo($id);
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Post(
     *     path="/api/user/withdrawal-logs/apply",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="申请提现",
     *     description="用户申请提现到指定账户",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", example=100.00, description="提现金额"),
     *             @OA\Property(property="account_id", type="integer", example=1, description="提现账户ID")
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
    public function applyUserWithdrawal()
    {
        $data = array(
            'amount' => input('post.amount'),
            'account_id' => input('post.account_id'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserWithdrawalValidate.apply');

        $result = (new UserWithdrawalService())->applyUserWithdrawal($data);

        return ds_json_success('提现成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/api/user/withdrawal-accounts/list",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="获取提现账户列表",
     *     description="获取用户的提现账户列表",
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
    public function getUserWithdrawalAccountList()
    {
        $data = (new UserWithdrawalService())->getUserWithdrawalAccountList();
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Get(
     *     path="/api/user/withdrawal-accounts/{id}",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="获取提现账户详情",
     *     description="获取指定提现账户的详细信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="账户ID",
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
    public function getUserWithdrawalAccountInfo($id)
    {
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserWithdrawalValidate.accountInfo');
        
        $data = (new UserWithdrawalService())->getUserWithdrawalAccountInfo((int)$id);
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Post(
     *     path="/api/user/withdrawal-accounts",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="添加提现账户",
     *     description="添加新的提现账户",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="account_type", type="string", example="bank", description="账户类型"),
     *             @OA\Property(property="account_name", type="string", example="中国银行", description="账户名称"),
     *             @OA\Property(property="account_number", type="string", example="6217000000000000000", description="账户号码"),
     *             @OA\Property(property="account_holder", type="string", example="张三", description="账户持有人"),
     *             @OA\Property(property="is_default", type="integer", example=1, description="是否默认账户")
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
    public function createUserWithdrawalAccount()
    {
        $data = array(
            'account_type' => input('post.account_type'),
            'account_name' => input('post.account_name'),
            'account_number' => input('post.account_number'),
            'account_holder' => input('post.account_holder'),
            'is_default' => input('post.is_default'),
        );

        //验证器
        $this->validate($data, 'app\api\controller\user\validate\UserWithdrawalAccountValidate.create');

        $data = (new UserWithdrawalService())->createUserWithdrawalAccount($data);
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Put(
     *     path="/api/user/withdrawal-accounts/{id}",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="修改提现账户",
     *     description="修改提现账户信息",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="账户ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="account_type", type="string", example="bank", description="账户类型"),
     *             @OA\Property(property="account_name", type="string", example="中国银行", description="账户名称"),
     *             @OA\Property(property="account_number", type="string", example="6217000000000000000", description="账户号码"),
     *             @OA\Property(property="account_holder", type="string", example="张三", description="账户持有人"),
     *             @OA\Property(property="is_default", type="integer", example=1, description="是否默认账户")
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
    public function updateUserWithdrawalAccount($id)
    {
        $data = array(
            'account_type' => input('post.account_type'),
            'account_name' => input('post.account_name'),
            'account_number' => input('post.account_number'),
            'account_holder' => input('post.account_holder'),
            'is_default' => input('post.is_default'),
        );
        $this->validate(array_merge($data, ['id' => (int) $id]), 'app\api\controller\user\validate\UserWithdrawalAccountValidate.update');

        $data = (new UserWithdrawalService())->updateUserWithdrawalAccount($id, $data);
        return ds_json_success('操作成功', $data);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/withdrawal-accounts/{id}",
     *     tags={"api/user/UserWithdrawal"},
     *     summary="删除提现账户",
     *     description="删除指定的提现账户",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="账户ID",
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
    public function deleteUserWithdrawalAccount($id)
    {
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserWithdrawalValidate.deleteAccount');
        
        $data = (new UserWithdrawalService())->deleteUserWithdrawalAccount((int)$id);
        return ds_json_success('操作成功', $data);
    }
}
