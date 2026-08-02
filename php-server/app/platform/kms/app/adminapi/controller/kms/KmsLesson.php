<?php

namespace app\platform\kms\app\adminapi\controller\kms;

use app\deshang\base\controller\BaseAdminController;
use app\platform\kms\app\adminapi\service\lesson\KmsLessonService;

class KmsLesson extends BaseAdminController
{
    public function getKmsLessonList()
    {
        $data = array('goods_id' => input('param.goods_id'));
        $this->validate($data, 'app\platform\kms\app\adminapi\controller\kms\validate\KmsLessonValidate.list');
        $result = (new KmsLessonService())->getKmsLessonList($data);
        return ds_json_success('操作成功', $result);
    }

    public function getKmsLessonDetail($id)
    {
        $result = (new KmsLessonService())->getKmsLessonDetail((int) $id);
        return ds_json_success('操作成功', $result);
    }

    public function createKmsLesson()
    {
        $data = array(
            'goods_id' => input('param.goods_id'),
            'lesson_name' => input('param.lesson_name'),
            'lesson_desc' => input('param.lesson_desc'),
            'lesson_type' => input('param.lesson_type'),
            'video_url' => input('param.video_url'),
            'audio_url' => input('param.audio_url'),
            'content' => input('param.content'),
            'sort' => input('param.sort'),
            'is_free' => input('param.is_free'),
        );
        $this->validate($data, 'app\platform\kms\app\adminapi\controller\kms\validate\KmsLessonValidate.create');
        $result = (new KmsLessonService())->createKmsLesson($data);
        return ds_json_success('操作成功', $result);
    }

    public function updateKmsLesson($id)
    {
        $data = array(
            'lesson_name' => input('param.lesson_name'),
            'lesson_desc' => input('param.lesson_desc'),
            'lesson_type' => input('param.lesson_type'),
            'video_url' => input('param.video_url'),
            'audio_url' => input('param.audio_url'),
            'content' => input('param.content'),
            'sort' => input('param.sort'),
            'is_free' => input('param.is_free'),
        );
        $this->validate($data, 'app\platform\kms\app\adminapi\controller\kms\validate\KmsLessonValidate.update');
        (new KmsLessonService())->updateKmsLesson((int) $id, $data);
        return ds_json_success('操作成功');
    }

    public function deleteKmsLesson($id)
    {
        (new KmsLessonService())->deleteKmsLesson((int) $id);
        return ds_json_success('操作成功');
    }
}
