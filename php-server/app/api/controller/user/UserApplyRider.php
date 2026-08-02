<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserApplyRiderService;

/**
 * @OA\Tag(name="api/user/UserApplyRider", description="用户申请骑手接口")
 */
// 用户申请骑手
class UserApplyRider extends BaseUserController
{

    /**
     * 申请成为骑手
     * @OA\Post(
     *     path="/api/user/apply/rider",
     *     tags={"api/user/UserApplyRider"},
     *     summary="申请成为骑手",
     *     description="用户提交申请成为骑手",
     *     @OA\RequestBody(
     *         required=true,
     *         description="申请数据",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="真实姓名", example="张三"),
     *             @OA\Property(property="mobile", type="string", description="手机号码", example="13800138000"),
     *             @OA\Property(property="apply_remark", type="string", description="申请备注", example="有配送经验，希望加入平台")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="申请提交成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    // 骑手申请
    public function apply()
    {
        $data = array(
            'name' => input('param.name'),
            'mobile' => input('param.mobile'),
            'apply_remark' => input('param.apply_remark'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserApplyRiderValidate.apply');

        $result = (new UserApplyRiderService())->apply($data);
        return ds_json_success('操作成功', $result);
    }




}