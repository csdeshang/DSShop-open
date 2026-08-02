<?php

namespace app\platform\kms\app\adminapi\service\lesson;

use app\common\dao\goods\TblGoodsDao;
use app\common\dao\kms\KmsLessonDao;
use app\deshang\base\service\BaseAdminService;
use app\deshang\exceptions\CommonException;

class KmsLessonService extends BaseAdminService
{
    public function getKmsLessonList(array $data): array
    {
        $condition = [
            ['goods_id', '=', $data['goods_id']],
        ];
        return (new KmsLessonDao())->getKmsLessonList($condition, '*', 'sort asc, id asc');
    }

    public function getKmsLessonDetail(int $id): array
    {
        $condition = [
            ['id', '=', $id],
        ];
        $result = (new KmsLessonDao())->getKmsLessonInfo($condition);
        if (empty($result)) {
            throw new CommonException('课时不存在');
        }
        return $result;
    }

    public function createKmsLesson(array $data): int
    {
        $condition = [
            ['id', '=', $data['goods_id']],
        ];
        if (empty((new TblGoodsDao())->getGoodsInfo($condition))) {
            throw new CommonException('商品不存在');
        }
        $data['create_at'] = time();
        $data['update_at'] = time();
        return (new KmsLessonDao())->createKmsLesson($data);
    }

    public function updateKmsLesson(int $id, array $data): bool
    {
        $condition = [
            ['id', '=', $id],
        ];
        if (empty((new KmsLessonDao())->getKmsLessonInfo($condition))) {
            throw new CommonException('课时不存在');
        }
        $data['update_at'] = time();
        return (new KmsLessonDao())->updateKmsLesson($condition, $data);
    }

    public function deleteKmsLesson(int $id): bool
    {
        $condition = [
            ['id', '=', $id],
        ];
        if (empty((new KmsLessonDao())->getKmsLessonInfo($condition))) {
            throw new CommonException('课时不存在');
        }
        return (new KmsLessonDao())->deleteKmsLesson($condition) > 0;
    }
}
