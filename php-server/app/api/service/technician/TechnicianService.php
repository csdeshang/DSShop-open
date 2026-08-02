<?php

namespace app\api\service\technician;

use app\deshang\base\BaseService;
use app\common\dao\technician\TechnicianDao;
use app\common\dao\technician\TechnicianGoodsRelDao;
use app\common\model\technician\TechnicianModel;
use app\common\enum\technician\TechnicianEnum;
use app\deshang\exceptions\CommonException;
use app\common\dao\goods\TblGoodsSkuDao;

use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;


class TechnicianService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取师傅列表
     * 
     * @param array $data 查询条件数据
     * @return array 师傅列表
     */
    public function getTechnicianList($data)
    {

        $cacheKey = sprintf(CacheKeyManager::TECHNICIAN_LIST_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if ($result == null) {

            // 用户经纬度 默认0
            $user_longitude = isset($data['longitude']) ? $data['longitude'] : 0;
            $user_latitude = isset($data['latitude']) ? $data['latitude'] : 0;

            $condition = [];
            $condition[] = ['t.is_deleted', '=', 0];
            // 审核通过
            $condition[] = ['t.apply_status', '=', TechnicianEnum::APPLY_STATUS_APPROVED];
            // 状态为接单
            $condition[] = ['t.technician_status', '=', TechnicianEnum::TECHNICIAN_STATUS_AVAILABLE];
            // 可用
            $condition[] = ['t.is_enabled', '=', 1];

            // 商品筛选
            if (isset($data['goods_id']) && $data['goods_id'] != 0) {
                $technician_model = (new TechnicianModel)->alias('t')
                    ->join('technician_goods_rel gr', 't.id = gr.technician_id');
                $condition[] = ['gr.goods_id', '=', $data['goods_id']];
            } else {
                $technician_model = (new TechnicianModel)->alias('t');
            }

            // 计算距离的SQL表达式(单位：米)
            $distance_sql = ',(2 * 6378137 * ASIN(SQRT(POW(SIN(PI()*(' . $user_latitude . '-t.technician_latitude)/360),2)+COS(PI()*' . $user_latitude . '/180)* COS(t.technician_latitude * PI()/180)*POW(SIN(PI()*(' . $user_longitude . '-t.technician_longitude)/360),2)))) as distance';

            // 限制返回数量
            $limit = isset($data['limit']) ? intval($data['limit']) : 20;

            // 查询师傅列表，直接在SQL中计算距离并排序
            $result = $technician_model
                ->field('t.*' . $distance_sql)
                ->where($condition)
                // ->where('t.technician_latitude', '<>', '')
                // ->where('t.technician_longitude', '<>', '')
                ->order('distance ASC')
                ->limit($limit)
                ->select()
                ->toArray();

            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::TECHNICIAN_TAG);
        }



        return $result;
    }


    /**
     * 获取师傅分页列表
     * 
     * @param array $data 查询条件数据
     * @return array 分页数据
     */
    public function getTechnicianPages($data)
    {

        $cacheKey = sprintf(CacheKeyManager::TECHNICIAN_PAGES_KEY, md5(serialize($data)));
        $result = KvManager::cache()->get($cacheKey);
        if ($result == null) {


            // 用户经纬度 默认0
            $user_longitude = $data['longitude'] ?? 0;
            $user_latitude = $data['latitude'] ?? 0;

            $condition = [];
            $condition[] = ['t.is_deleted', '=', 0];
            // 审核通过
            $condition[] = ['t.apply_status', '=', TechnicianEnum::APPLY_STATUS_APPROVED];
            // 状态为接单
            $condition[] = ['t.technician_status', '=', TechnicianEnum::TECHNICIAN_STATUS_AVAILABLE];
            // 可用
            $condition[] = ['t.is_enabled', '=', 1];

            // 商品筛选
            if (isset($data['goods_id']) && $data['goods_id'] != 0) {
                $technician_model = (new TechnicianModel)->alias('t')
                    ->join('technician_goods_rel gr', 't.id = gr.technician_id');
                $condition[] = ['gr.goods_id', '=', $data['goods_id']];
            } else {
                $technician_model = (new TechnicianModel)->alias('t');
            }

            // 计算距离的SQL表达式(单位：米)
            $distance_sql = ',(2 * 6378137 * ASIN(SQRT(POW(SIN(PI()*(' . $user_latitude . '-t.technician_latitude)/360),2)+COS(PI()*' . $user_latitude . '/180)* COS(t.technician_latitude * PI()/180)*POW(SIN(PI()*(' . $user_longitude . '-t.technician_longitude)/360),2)))) as distance';


            // 查询师傅列表，直接在SQL中计算距离并排序
            $technician_model = $technician_model
                ->field('t.*' . $distance_sql)
                ->where($condition)
                // ->where('t.technician_latitude', '<>', '')
                // ->where('t.technician_longitude', '<>', '')
                ->order('distance ASC');


            // 获取分页数据
            $result = $this->getPaginate($technician_model);

            KvManager::cache()->set($cacheKey, $result, 3600, CacheKeyManager::TECHNICIAN_TAG);
        }


        return $result;
    }



    // 获取师傅详情
    public function getTechnicianDetail($data)
    {

        $cacheKey = sprintf(CacheKeyManager::TECHNICIAN_INFO_KEY, md5(serialize($data)));
        $technician = KvManager::cache()->get($cacheKey);

        if ($technician == null) {

            $user_longitude = $data['longitude'] ?? 0;
            $user_latitude = $data['latitude'] ?? 0;


            $technician = (new TechnicianDao())->getTechnicianInfoById($data['id']);

            if (empty($technician)) {
                throw new CommonException('师傅不存在');
            }
            // 访问次数+1
            (new TechnicianDao())->setTechnicianInc([['id', '=', $data['id']]], 'visit_count');

            if (!empty($technician['slide_image'])) $technician['slide_image'] = explode(',', $technician['slide_image']);

            // 获取师傅的距离
            $technician['distance'] = haversineGreatCircleDistance($user_longitude, $user_latitude, $technician['technician_longitude'] ?? 0, $technician['technician_latitude'] ?? 0);

            // 获取师傅关联的商品Sku列表
            $technician['goodsSkuList'] = $this->getTechnicianSkuList($data['id']);


            KvManager::cache()->set($cacheKey, $technician, 3600, CacheKeyManager::TECHNICIAN_TAG);
        }


        return $technician;
    }


    // 获取师傅关联的商品的Sku列表
    public function getTechnicianSkuList($technician_id)
    {

        // 获取师傅关联的商品信息
        $technician_goods_rel = (new TechnicianGoodsRelDao())->getTechnicianGoodsRelColumn([['technician_id', '=', $technician_id]], 'goods_id');



        // 获取商品Sku列表
        $sku_list = (new TblGoodsSkuDao())->getWithRelGoodsSkuList([['goods_id', 'in', $technician_goods_rel]]);
        return $sku_list;
    }
}
