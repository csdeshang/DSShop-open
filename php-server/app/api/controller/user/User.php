<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserService;

/**
 * @OA\Tag(name="api/user/User", description="用户基础信息接口")
 */
class User extends BaseUserController
{

    /**
     * 获取用户信息以及角色信息
     * @OA\Get(
     *     path="/api/user/user/info-with-roles",
     *     tags={"api/user/User"},
     *     summary="获取用户信息以及角色信息",
     *     description="获取当前登录用户的基础信息以及角色信息",
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

    public function getUserInfoWithRoles(){
        $result = (new UserService())->getUserInfoWithRoles();
        return ds_json_success('操作成功',$result);
    }


    /**
     * 获取用户信息
     * @OA\Get(
     *     path="/api/user/user/info",
     *     tags={"api/user/User"},
     *     summary="获取用户信息",
     *     description="获取当前登录用户的基础信息",
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
    public function getUserInfo()
    {
        $result = (new UserService())->getUserInfo();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新用户信息
     * @OA\Post(
     *     path="/api/user/user/info",
     *     tags={"api/user/User"},
     *     summary="更新用户信息",
     *     description="更新当前登录用户的基础信息",
     *     @OA\RequestBody(
     *         required=true,
     *         description="用户信息",
     *         @OA\JsonContent(
     *             @OA\Property(property="nickname", type="string", description="昵称", example="新昵称"),
     *             @OA\Property(property="avatar", type="string", description="头像URL", example="https://example.com/new-avatar.jpg"),
     *             @OA\Property(property="sex", type="integer", description="性别 0保密 1男 2女", example=1),
     *             @OA\Property(property="birthday", type="integer", description="生日时间戳(毫秒)", example=946656000000)
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
    public function updateUserInfo()
    {

        $data = array(
            'nickname' => input('param.nickname'),
            'avatar' => input('param.avatar'),
            'sex' => input('param.sex'),
            'birthday' => intval(input('param.birthday') / 1000),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserValidate.updateInfo');

        $result = (new UserService())->updateUserInfo($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 修改用户密码
     * @OA\Put(
     *     path="/api/user/user/password",
     *     tags={"api/user/User"},
     *     summary="修改用户密码",
     *     description="修改当前登录用户的密码",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="old_password", type="string", description="原密码"),
     *             @OA\Property(property="password", type="string", description="新密码"),
     *             @OA\Property(property="confirm_password", type="string", description="确认新密码")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="密码修改成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="密码修改成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function editUserPassword()
    {
        $data = [
            'old_password' => input('param.old_password'),
            'password' => input('param.password'),
            'confirm_password' => input('param.confirm_password'),
        ];

        // 验证器
        $this->validate($data, 'app\api\controller\user\validate\UserValidate.editPassword');

        $result = (new UserService())->editUserPassword($data);
        return ds_json_success('密码修改成功', $result);
    }

    /**
     * 编辑用户身份证（提交实名认证审核）
     * @OA\Put(
     *     path="/api/user/user/idcard",
     *     tags={"api/user/User"},
     *     summary="编辑用户身份证",
     *     description="提交实名认证信息，进入审核状态",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="idcard_name", type="string", description="真实姓名"),
     *             @OA\Property(property="idcard_number", type="string", description="身份证号"),
     *             @OA\Property(property="idcard_image1", type="string", description="手持身份证照URL"),
     *             @OA\Property(property="idcard_image2", type="string", description="身份证正面照URL"),
     *             @OA\Property(property="idcard_image3", type="string", description="身份证反面照URL")
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功")
     * )
     */
    public function editUserIdcard()
    {
        $data = [
            'idcard_name' => input('param.idcard_name'),
            'idcard_number' => input('param.idcard_number'),
            'idcard_image1' => input('param.idcard_image1'),
            'idcard_image2' => input('param.idcard_image2'),
            'idcard_image3' => input('param.idcard_image3'),
        ];
        $this->validate($data, 'app\api\controller\user\validate\UserValidate.editIdcard');
        $result = (new UserService())->editUserIdcard($data);
        return ds_json_success('提交成功，请等待审核', $result);
    }
}
