<?php

namespace app\api\controller\user;

use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserApplyTechnicianService;

/**
 * @OA\Tag(name="api/user/UserApplyTechnician", description="用户申请师傅接口")
 */
// 用户申请师傅
class UserApplyTechnician extends BaseUserController
{

    /**
     * 用户申请师傅
     * @OA\Post(
     *     path="/api/user/apply/technician",
     *     tags={"api/user/UserApplyTechnician"},
     *     summary="申请成为师傅",
     *     description="用户提交申请成为师傅",
     *     @OA\RequestBody(
     *         required=true,
     *         description="申请数据",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="真实姓名", example="张三"),
     *             @OA\Property(property="mobile", type="string", description="手机号码", example="13800138000"),
     *             @OA\Property(property="gender", type="integer", description="性别 0未知 1男 2女", example=1),
     *             @OA\Property(property="certificate_info", type="string", description="证书信息", example="高级师傅证书"),
     *             @OA\Property(property="work_years", type="integer", description="工作年限", example=5),
     *             @OA\Property(property="description", type="string", description="个人描述", example="专业从事家电维修5年"),
     *             @OA\Property(property="apply_remark", type="string", description="申请备注", example="希望加入贵平台")
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
    // 用户申请师傅
    public function apply()
    {
        $data = array(
            'name' => input('param.name'),
            'mobile' => input('param.mobile'),
            'gender' => input('param.gender'),
            'certificate_info' => input('param.certificate_info'),
            'work_years' => input('param.work_years'),
            'description' => input('param.description'),
            'apply_remark' => input('param.apply_remark'),
        );

        $this->validate($data, 'app\api\controller\user\validate\UserApplyTechnicianValidate.apply');

        $result = (new UserApplyTechnicianService())->apply($data);
        return ds_json_success('操作成功', $result);

        
    }



}
