<?php

namespace app\api\controller\system;

use app\deshang\base\controller\BaseApiController;
use app\deshang\core\ThirdPartyLoader;
use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;
use app\common\dao\system\SysAreaDao;

/**
 * @OA\Tag(name="api/system/SysLbs", description="地理位置服务接口")
 */
class SysLbs extends BaseApiController{

    /**
     * @OA\Get(
     *     path="/api/system/lbs/coords-by-ip",
     *     tags={"api/system/SysLbs"},
     *     summary="通过IP获取经纬度",
     *     description="根据IP地址获取对应的经纬度坐标",
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
    // 通过IP获取经纬度
    public function getCoordsByIp(){

        $ip = request()->ip();

        $lbs = ThirdPartyLoader::lbs();
        $result = $lbs->getCoordsByIp($ip);

        if($result['success']){
            return ds_json_success('操作成功', $result['data']);
        }else{
            return ds_json_error($result['message']);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/system/lbs/address-by-lng-lat",
     *     tags={"api/system/SysLbs"},
     *     summary="根据经纬度获取位置信息",
     *     description="根据经纬度坐标获取详细地址信息",
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="经度",
     *         required=true,
     *         @OA\Schema(type="number", format="float", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="纬度",
     *         required=true,
     *         @OA\Schema(type="number", format="float", example=39.915)
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
    // 根据经纬度获取位置信息
    public function getAddressByLngLat(){

        $lng = input('param.longitude');
        $lat = input('param.latitude');

        if(empty($lng) || empty($lat)){
            return ds_json_error('经纬度不能为空');
        }

        $lbs = ThirdPartyLoader::lbs();
        $result = $lbs->getAddressByLngLat($lng, $lat);

        return ds_json_success('操作成功', $result);

    }

    /**
     * @OA\Post(
     *     path="/api/system/lbs/around-address-list",
     *     tags={"api/system/SysLbs"},
     *     summary="获取周边位置列表",
     *     description="根据经纬度获取周边地址列表",
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="经度",
     *         required=true,
     *         @OA\Schema(type="number", format="float", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="纬度",
     *         required=true,
     *         @OA\Schema(type="number", format="float", example=39.915)
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="搜索关键词",
     *         required=false,
     *         @OA\Schema(type="string", example="餐厅")
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
    // 获取周边位置列表
    public function getAroundAddressList(){

        $data = [
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
            'keyword' => input('param.keyword'),
        ];

        if(empty($data['longitude']) || empty($data['latitude'])){
            return ds_json_error('经纬度不能为空');
        }



        $lbs = ThirdPartyLoader::lbs();
        $result = $lbs->getAroundAddressList($data);

        return ds_json_success('操作成功', $result);
        
    }

    /**
     * @OA\Post(
     *     path="/api/system/lbs/city-address-list",
     *     tags={"api/system/SysLbs"},
     *     summary="获取城市地址列表",
     *     description="根据城市和关键词搜索地址列表",
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="城市名称",
     *         required=true,
     *         @OA\Schema(type="string", example="北京市")
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="搜索关键词",
     *         required=true,
     *         @OA\Schema(type="string", example="餐厅")
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="经度(可选)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=116.404)
     *     ),
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="纬度(可选)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=39.915)
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
    // 获取城市地址列表  经纬度可选
    public function getCityAddressList(){

        $data = [
            'city' => input('param.city'),
            'longitude' => input('param.longitude'),
            'latitude' => input('param.latitude'),
            'keyword' => input('param.keyword'),
        ];

        if(empty($data['city'])){
            return ds_json_error('城市不能为空');
        }
        if(empty($data['keyword'])){
            return ds_json_error('关键词不能为空');
        }

        $lbs = ThirdPartyLoader::lbs();
        $result = $lbs->getCityAddressList($data);

        return ds_json_success('操作成功', $result);

    }

    /**
     * @OA\Get(
     *     path="/api/system/lbs/cities",
     *     tags={"api/system/SysLbs"},
     *     summary="获取城市列表",
     *     description="获取系统支持的城市列表，按首字母分组",
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
    // 获取城市列表
    public function getCityList(){
        
        $cacheKey = CacheKeyManager::LBS_CITY_LIST_KEY;
        $city_list = KvManager::cache()->get($cacheKey);

        if ($city_list == null) {
            $condition = array();
            $condition[] = array('deep', 'in', array(2, 3));
            $area_list_1 = (new SysAreaDao())->getAreaList($condition);
            $area_list = array();
            foreach ($area_list_1 as $area_key => $area_value) {
                $first_charter = get_first_charter($area_value['name']);
                $area_list[$first_charter][] = $area_value['name'];
            }

            //排序
            $order_keys = array_keys($area_list);
            array_multisort($order_keys, SORT_ASC, $area_list);

            //适合uniapp代码   https://uniapp.dcloud.net.cn/component/uniui/uni-indexed-list.html
            $city_list = array();
            foreach ($area_list as $key => $value) {
                $city_list[] = array(
                    'letter' => $key,
                    'data' => $value,
                );
            }
            KvManager::cache()->set($cacheKey, $city_list, 3600);
        }


        $hot_city_list = array(
            '北京市',
            '上海市',
            '杭州市',
            '南京市',
            '武汉市',
            '杭州市',
        );
        
        return ds_json_success('操作成功', array('city_list' => $city_list,'hot_city_list' => $hot_city_list));
    }

    /**
     * @OA\Get(
     *     path="/api/system/lbs/city-search",
     *     tags={"api/system/SysLbs"},
     *     summary="根据关键词检索城市",
     *     description="根据关键词模糊搜索城市名称",
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="搜索关键词",
     *         required=false,
     *         @OA\Schema(type="string", example="北京")
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
    // 根据关键词检索城市
    public function getCityListByKeyword(){
        $keyword = input('param.keyword');
        if(empty($keyword)){
            return ds_json_success('操作成功', array('search_city_list' => array()));
        }
        
        $condition = array();
        $condition[] = array('deep','in', array(2,3));
        $condition[]=array('name','like', "%" . $keyword . "%");
        
        $area_list_1 = (new SysAreaDao())->getAreaList($condition);
        
        $search_city_list = array();
        foreach ($area_list_1 as $area_key => $area_value) {
            $search_city_list[] = $area_value['name'];
        }
        return ds_json_success('操作成功', array('search_city_list' => $search_city_list));
    }


}