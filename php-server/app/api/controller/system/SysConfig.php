<?php

namespace app\api\controller\system;

use app\deshang\base\controller\BaseApiController;

use app\deshang\service\system\DeshangSysConfigService;

/**
 * @OA\Tag(name="api/system/SysConfig", description="系统配置接口")
 */
class SysConfig extends BaseApiController
{

    // 集中管理允许的配置键和类型
    private $allowConfigKeys = [
        // Uniapp 底部导航栏
        'website:h5_system_bottom_nav',
        'website:h5_mall_bottom_nav',
        'website:h5_food_bottom_nav',
        'website:h5_kms_bottom_nav',
        'website:h5_house_bottom_nav',
        'website:h5_url',




        // 系统配置
        'system:site_name',
        'system:site_logo',
        'system:site_description',

        // 提现配置
        'user_withdrawal:withdrawal_min_amount',
        'user_withdrawal:withdrawal_fee_rate',

        // 商品配置
        'goods:goods_need_audit',
        'goods:goods_category_select_limit',

        // 地图配置
        'lbs:lbs_is_enabled',
        'lbs:lbs_default_provider',

        // 登录配置
        'user_login:user_login_normal',
        'user_login:user_login_mobile',
        'user_login:user_login_wechat',
        'user_login:user_login_logo',
    ];

    private $allowConfigTypes = [
        'website',
        'user_withdrawal',
        'goods',
        'lbs',
        'user_login',
        'distributor',
        'growth',
        'points',
    ];
    
    /**
     * @OA\Get(
     *     path="/api/system/configs/by-key",
     *     tags={"api/system/SysConfig"},
     *     summary="获取单个系统配置",
     *     description="根据配置键获取单个系统配置值",
     *     @OA\Parameter(
     *         name="key",
     *         in="query",
     *         description="配置键",
     *         required=true,
     *         @OA\Schema(type="string", example="system:site_name")
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
    /**
     * 获取单个系统配置
     * 
     * @return \think\Response
     */
    public function getSysConfigByKey()
    {
        $config_key = input('param.key');
        if (empty($config_key) || !in_array($config_key, $this->allowConfigKeys)) {
            return ds_json_error('非法请求');
        }
        $config_value = (new DeshangSysConfigService())->getSysConfigByKey($config_key);
        return ds_json_success('操作成功', $config_value);
    }

    /**
     * @OA\Post(
     *     path="/api/system/configs/by-keys",
     *     tags={"api/system/SysConfig"},
     *     summary="获取多个配置项",
     *     description="根据配置键数组获取多个配置项",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="keys",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"system:site_name", "system:site_logo"},
     *                 description="配置键数组"
     *             )
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
    /**
     * 获取多个配置项
     * 
     * @return \think\Response
     */
    public function getSysConfigsByKeys()
    {
        $config_keys = input('post.keys/a');

        if (empty($config_keys) || !is_array($config_keys)) {
            return ds_json_error('参数错误');
        }

        // 检查允许的配置键
        foreach ($config_keys as $key) {
            if (!in_array($key, $this->allowConfigKeys)) {
                return ds_json_error('存在非法请求的配置项');
            }
        }

        $configs = (new DeshangSysConfigService())->getSysConfigByKeys($config_keys);
        return ds_json_success('操作成功', $configs);
    }

    /**
     * @OA\Get(
     *     path="/api/system/configs/by-type",
     *     tags={"api/system/SysConfig"},
     *     summary="获取指定类型的所有配置",
     *     description="根据配置类型获取该类型下的所有配置项",
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="配置类型",
     *         required=true,
     *         @OA\Schema(type="string", example="website")
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
    /**
     * 获取指定类型的所有配置
     * 
     * @return \think\Response
     */
    public function getSysConfigsByType()
    {
        $config_type = input('param.type');

        // 检查允许的配置类型
        if (empty($config_type) || !in_array($config_type, $this->allowConfigTypes)) {
            return ds_json_error('非法请求');
        }

        $configs = (new DeshangSysConfigService())->getSysConfigByType($config_type);
        return ds_json_success('操作成功', $configs);
    }

    /**
     * @OA\Post(
     *     path="/api/system/configs/by-types",
     *     tags={"api/system/SysConfig"},
     *     summary="获取多个类型的所有配置",
     *     description="根据配置类型数组获取多个类型下的所有配置项",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="types",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"website", "user_login"},
     *                 description="配置类型数组"
     *             )
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
    /**
     * 获取多个类型的所有配置
     * 
     * @return \think\Response
     */
    public function getSysConfigsByTypes()
    {
        $config_types = input('post.types/a');

        if (empty($config_types) || !is_array($config_types)) {
            return ds_json_error('参数错误');
        }

        // 检查允许的配置类型
        foreach ($config_types as $type) {
            if (!in_array($type, $this->allowConfigTypes)) {
                return ds_json_error('存在非法请求的配置类型');
            }
        }

        $configs = (new DeshangSysConfigService())->getSysConfigByTypes($config_types);
        return ds_json_success('操作成功', $configs);
    }
}
