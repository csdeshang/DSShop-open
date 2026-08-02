<?php

namespace app\adminapi\service\technician;

use app\deshang\base\service\BaseAdminService;
use app\common\dao\technician\TechnicianDao;
use app\common\enum\technician\TechnicianEnum;
use app\deshang\exceptions\CommonException;
use app\deshang\utils\SearchHelper;

/**
 * 管理端师傅服务类
 */
class TechnicianService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取师傅分页列表
     * @param array $data 查询条件
     * @return array
     */
    public function getTechnicianPages($data)
    {
        $condition = [];

        // 根据参数控制是否显示已删除数据，空或不传时不筛选（显示全部）
        if (isset($data['is_deleted']) && $data['is_deleted'] !== '' && $data['is_deleted'] !== null && in_array((int)$data['is_deleted'], [0, 1], true)) {
            $condition[] = ['is_deleted', '=', (int)$data['is_deleted']];
        }
        
        // 师傅名称搜索
        if (!empty($data['name'])) {
            $condition[] = ['name', 'like', '%' . $data['name'] . '%'];
        }
        
        // 手机号搜索
        if (!empty($data['mobile'])) {
            $condition[] = ['mobile', 'like', '%' . $data['mobile'] . '%'];
        }
        
        // 申请状态筛选（仅合法枚举值生效）
        if (array_key_exists($data['apply_status'], TechnicianEnum::getApplyStatusDict())) {
            $condition[] = ['apply_status', '=', (int)$data['apply_status']];
        }

        // 师傅状态筛选
        if (array_key_exists($data['technician_status'], TechnicianEnum::getTechnicianStatusDict())) {
            $condition[] = ['technician_status', '=', (int)$data['technician_status']];
        }

        // 是否可用筛选
        if ($data['is_enabled'] !== '' && $data['is_enabled'] !== null && in_array((int)$data['is_enabled'], [0, 1], true)) {
            $condition[] = ['is_enabled', '=', (int)$data['is_enabled']];
        }
        
        // 用户名搜索
        if (isset($data['username']) && $data['username'] != '') {
            $userIds = SearchHelper::getUserIdsByUsername($data['username']);
            $condition[] = ['user_id', 'in', $userIds];
        }

        $result = (new TechnicianDao())->getWithRelTechnicianPages($condition);
        return $result;
    }

    /**
     * 获取师傅详情
     * @param int $id 师傅ID
     * @return array
     */
    public function getTechnicianInfo($id)
    {
        $condition = [
            ['id', '=', $id]
        ];
        $result = (new TechnicianDao())->getTechnicianInfo($condition);
        if (empty($result)) {
            throw new CommonException('师傅不存在');
        }
        return $result;
    }


    public function updateTechnician($id,$data)
    {
        $condition = [];
        $condition[] = ['id', '=', $id];

        // 验证师傅是否存在
        $technician = (new TechnicianDao())->getTechnicianInfo($condition);
        if (empty($technician)) {
            throw new CommonException('师傅不存在');
        }



        $result = (new TechnicianDao())->updateTechnician($condition,$data);
        return $result;
    }

    /**
     * 审核师傅申请
     * @param array $data 审核数据
     * @return int
     */
    public function auditTechnician(array $data)
    {
        // 获取师傅信息
        $technicianInfo = (new TechnicianDao())->getTechnicianInfo([['id', '=', $data['id']]]);

        // 判断师傅信息是否存在
        if (empty($technicianInfo)) {
            throw new CommonException('师傅信息不存在');
        }

        // 已通过审核的不允许重复审核
        if ($technicianInfo['apply_status'] == TechnicianEnum::APPLY_STATUS_APPROVED) {
            throw new CommonException('师傅已审核通过，不能重复审核');
        }

        if (!in_array((int)$data['apply_status'], [
            TechnicianEnum::APPLY_STATUS_APPROVED,
            TechnicianEnum::APPLY_STATUS_REJECTED,
        ], true)) {
            throw new CommonException('审核状态不正确，只能选择通过或拒绝');
        }

        // 判断审核状态
        if ($data['apply_status'] == TechnicianEnum::APPLY_STATUS_APPROVED) {
            // 审核通过 修改信息
            $updateData = [
                'apply_status' => TechnicianEnum::APPLY_STATUS_APPROVED,
                'audit_time' => time(),
                'audit_remark' => $data['audit_remark'] ?? '',
            ];
        } else if ($data['apply_status'] == TechnicianEnum::APPLY_STATUS_REJECTED) {
            // 审核拒绝 修改信息
            $updateData = [
                'apply_status' => TechnicianEnum::APPLY_STATUS_REJECTED,
                'audit_time' => time(),
                'audit_remark' => $data['audit_remark'],
            ];
        } else {
            throw new CommonException('师傅申请状态不正确');
        }

        $condition = [['id', '=', $data['id']]];
        return (new TechnicianDao())->updateTechnician($condition, $updateData);
    }

    /**
     * 软删除师傅
     *
     * @param int $id 师傅ID
     * @return int 受影响的行数
     */
    public function softDeleteTechnician(int $id)
    {
        $dao = new TechnicianDao();
        $technician_info = $dao->getTechnicianInfoById($id);
        if (empty($technician_info)) {
            throw new CommonException('师傅不存在');
        }
        if ($technician_info['is_deleted'] == 1) {
            throw new CommonException('师傅已被删除');
        }
        $update_data = [
            'is_deleted' => 1,
            'deleted_at' => time(),
        ];
        $condition = [
            ['id', '=', $id],
            ['is_deleted', '=', 0],
        ];
        return $dao->updateTechnician($condition, $update_data);
    }

    /**
     * 恢复师傅
     *
     * @param int $id 师傅ID
     * @return int 受影响的行数
     */
    public function restoreTechnician(int $id)
    {
        $dao = new TechnicianDao();
        $technician_info = $dao->getTechnicianInfoById($id);
        if (empty($technician_info)) {
            throw new CommonException('师傅不存在');
        }
        if ($technician_info['is_deleted'] != 1) {
            throw new CommonException('师傅未被删除，无需恢复');
        }
        $update_data = [
            'is_deleted' => 0,
            'deleted_at' => null,
        ];
        $condition = [
            ['id', '=', $id],
            ['is_deleted', '=', 1],
        ];
        return $dao->updateTechnician($condition, $update_data);
    }

} 