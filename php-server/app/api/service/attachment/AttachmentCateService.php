<?php

namespace app\api\service\attachment;

use app\deshang\base\service\BaseUserService;
use app\deshang\service\attachment\DeshangAttachmentCateService;

use app\common\enum\attachment\AttachmentEnum;

// 附件分类(代码与adminapi一致，只是命名空间不同)

/**
 * 附件管理
 */
class AttachmentCateService extends BaseUserService
{
    public function __construct()
    {
        parent::__construct();
    }

    
    public function getAttachmentCateList($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentCateService())->getAttachmentCateList($data);
    }

    public function getAttachmentCateInfo($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentCateService())->getAttachmentCateInfo($data);
    }

    public function createAttachmentCate($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentCateService())->createAttachmentCate($data);
    }

    public function updateAttachmentCate($data){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        return (new DeshangAttachmentCateService())->editAttachmentCate($data);
    }

    public function deleteAttachmentCate($id){
        $data['user_id'] = $this->user_id;
        $data['user_scene'] = AttachmentEnum::USER_SCENE_USER; // 0 admin 1 user
        $data['id'] = $id;
        return (new DeshangAttachmentCateService())->delAttachmentCate($data);
    }

}