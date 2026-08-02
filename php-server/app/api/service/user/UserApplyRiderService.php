<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;

use app\common\dao\rider\RiderDao;
use app\deshang\exceptions\CommonException;

use app\common\enum\rider\RiderEnum;

// 用户申请成为骑手
class UserApplyRiderService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }
    public function apply($data)
    {

        // 判断是否已经申请过
        $apply_info = (new RiderDao())->getRiderInfo([['user_id', '=', $this->user_id]]);
        // 已删除的骑手不允许再次申请
        if ($apply_info && $apply_info['is_deleted'] === 1) {
            throw new CommonException('您的骑手账号已被删除，无法再次申请');
        }
        if ($apply_info && $apply_info['apply_status'] == RiderEnum::APPLY_STATUS_APPROVED) {
            throw new CommonException('您的审核已经通过，请勿重复申请');
        }

        $data['user_id'] = $this->user_id;
        $data['apply_remark'] = $data['apply_remark'];
        $data['apply_status'] = RiderEnum::APPLY_STATUS_PENDING;
        $data['status'] = RiderEnum::RIDER_STATUS_AVAILABLE;

        // 判断是否存在骑手
        if ($apply_info) {
            $result = (new RiderDao())->updateRider([['id', '=', $apply_info['id']]], $data);
        } else {
            $result = (new RiderDao())->createRider($data);
        }   

        return $result;
    }



}
