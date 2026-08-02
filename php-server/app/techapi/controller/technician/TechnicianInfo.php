<?php

namespace app\techapi\controller\technician;
use app\deshang\base\controller\BaseTechnicianController;

use app\techapi\service\technician\TechnicianInfoService;

/**
 * @OA\Tag(name="techapi/technician/TechnicianInfo", description="师傅信息管理接口")
 */
class TechnicianInfo extends BaseTechnicianController{

    /**
     * 获取师傅信息
     * @OA\Get(
     *     path="/techapi/technician/info",
     *     tags={"techapi/technician/TechnicianInfo"},
     *     summary="获取师傅信息",
     *     description="获取当前师傅的详细信息",
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
    public function getTechnicianInfo(){
        $result = (new TechnicianInfoService())->getTechnicianInfo();
        return ds_json_success('操作成功', $result);
    }

    /**
     * 更新师傅信息
     * @OA\Put(
     *     path="/techapi/technician/info",
     *     tags={"techapi/technician/TechnicianInfo"},
     *     summary="更新师傅信息",
     *     description="更新师傅的基本信息",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="师傅姓名", example="张师傅"),
     *             @OA\Property(property="mobile", type="string", description="手机号码", example="13800138000"),
     *             @OA\Property(property="avatar", type="string", description="头像地址", example="/uploads/avatar.jpg"),
     *             @OA\Property(property="slide_image", type="string", description="轮播图", example="/uploads/slide.jpg"),
     *             @OA\Property(property="gender", type="integer", description="性别 1男 2女", example=1),
     *             @OA\Property(property="certificate_info", type="string", description="证书信息", example="高级师傅证书"),
     *             @OA\Property(property="work_years", type="integer", description="工作年限", example=5),
     *             @OA\Property(property="description", type="string", description="个人描述", example="专业从事家电维修5年")
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
    public function updateTechnicianInfo(){ 
        $data = array(
            'name' => input('param.name'),
            'mobile' => input('param.mobile'),
            'avatar' => input('param.avatar'),
            'slide_image' => input('param.slide_image'),
            'gender' => input('param.gender'),
            'certificate_info' => input('param.certificate_info'),
            'work_years' => input('param.work_years'),
            'description' => input('param.description'), 
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianInfoValidate.update');

        $result = (new TechnicianInfoService())->updateTechnicianInfo($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * 设置师傅状态
     * @OA\Patch(
     *     path="/techapi/technician/info/technician-status",
     *     tags={"techapi/technician/TechnicianInfo"},
     *     summary="设置师傅状态",
     *     description="设置师傅的工作状态",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="integer", description="师傅状态 0离线 1在线", example=1)
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
    public function setTechnicianStatus(){
        $data = array(
            'technician_status' => input('param.status'),
        );

        // 验证器
        $this->validate($data, 'app\techapi\controller\technician\validate\TechnicianInfoValidate.setStatus');

        $result = (new TechnicianInfoService())->setTechnicianStatus($data);
 
        return ds_json_success('操作成功', $result);
    }
}
