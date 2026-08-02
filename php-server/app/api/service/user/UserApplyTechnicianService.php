<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;

use app\common\dao\technician\TechnicianDao;

use app\deshang\exceptions\CommonException;

use app\common\enum\technician\TechnicianEnum;

// 用户申请成为师傅
class UserApplyTechnicianService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function apply($data)
    {

        // 判断是否已经申请过
        $apply_info = (new TechnicianDao())->getTechnicianInfo([['user_id', '=', $this->user_id]]);
        // 已删除的师傅不允许再次申请
        if ($apply_info && $apply_info['is_deleted'] === 1) {
            throw new CommonException('您的师傅账号已被删除，无法再次申请');
        }
        if ($apply_info && $apply_info['apply_status'] == TechnicianEnum::APPLY_STATUS_APPROVED) {
            throw new CommonException('您的审核已经通过，请勿重复申请');
        }

        $data['user_id'] = $this->user_id;
        $data['apply_status'] = TechnicianEnum::APPLY_STATUS_PENDING;
        $data['technician_status'] = TechnicianEnum::TECHNICIAN_STATUS_REST;
        $data['apply_time'] = time();
        // 平台自营抽成比例
        $data['technician_fee_rate'] = 30;


        // 判断是否存在师傅
        if ($apply_info) {
            $result = (new TechnicianDao())->updateTechnician([['id', '=', $apply_info['id']]], $data);
        } else {
            $result = (new TechnicianDao())->createTechnician($data);
        }
        return $result;
    }
}
