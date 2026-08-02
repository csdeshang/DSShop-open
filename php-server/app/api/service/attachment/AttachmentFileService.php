<?php

namespace app\api\service\attachment;

use app\deshang\base\service\BaseUserService;
use app\deshang\service\attachment\DeshangAttachmentFileService;

use app\common\enum\attachment\AttachmentEnum;

// 附件文件(代码与adminapi一致，只是命名空间不同)
/**
 * 附件管理
 */
class AttachmentFileService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function image($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentFileService())->uploadFile($data,'image');
    }

    public function video($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentFileService())->uploadFile($data,'video');
    }

    public function getAttachmentFilePages($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentFileService())->getAttachmentFilePages($data);
    }


    public function updateBatchAttachmentFile($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentFileService())->updateBatchAttachmentFile($data);
    }

    public function deleteBatchAttachmentFile($ids){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        $data['ids'] = $ids;
        return (new DeshangAttachmentFileService())->deleteBatchAttachmentFile($data);
    }

}