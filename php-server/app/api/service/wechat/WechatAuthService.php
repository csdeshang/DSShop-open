<?php

namespace app\api\service\wechat;

use app\deshang\base\service\BaseApiService;
use app\deshang\exceptions\CommonException;
use app\deshang\exceptions\AuthException;
use app\deshang\service\wechat\DeshangOfficialSettingService;
use app\deshang\service\wechat\DeshangOfficialEasyService;
use app\deshang\service\wechat\DeshangMiniEasyService;
use app\deshang\service\wechat\DeshangWebEasyService;
use app\deshang\service\wechat\DeshangAppEasyService;

use app\common\dao\user\UserDao;
use app\common\dao\user\UserIdentityDao;

use app\api\service\login\LoginService;

use app\deshang\service\user\DeshangUserService;

/**
 * 微信授权服务类
 * 
 * 处理所有微信授权相关的业务逻辑，支持多种授权场景：
 * 
 * 授权场景说明：
 * - wechat_official: 微信公众号网页授权（微信浏览器内）
 *   - 授权URL: https://open.weixin.qq.com/connect/oauth2/authorize
 *   - 使用服务: DeshangOfficialEasyService
 *   - OpenID字段: wx_oauth_openid
 *   - 用途: 微信浏览器内网页授权登录/绑定
 * 
 * - wechat_web: 微信开放平台网站应用PC扫码登录
 *   - 授权URL: https://open.weixin.qq.com/connect/qrconnect
 *   - 使用服务: DeshangWebEasyService
 *   - OpenID字段: wx_web_openid
 *   - 用途: PC端扫码登录（网站应用）
 * 
 * - wechat_mini: 微信小程序授权
 *   - 使用服务: DeshangMiniEasyService
 *   - OpenID字段: wx_mini_openid
 *   - 用途: 小程序内登录/绑定
 * 
 * - wechat_app: 微信开放平台移动应用授权
 *   - 使用服务: DeshangAppEasyService（移动应用授权）
 *   - OpenID字段: wx_app_openid
 *   - 用途: APP内微信登录
 * 
 * 重要说明：
 * - wechat_official 和 wechat_web 使用不同的AppID和授权接口，openid也不同
 * - 所有场景都通过 unionid 进行用户身份统一识别
 */
class WechatAuthService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取微信授权信息
     * 
     * @param array $data 授权参数
     * @param string $data['scene'] 授权场景：wechat_official(公众号) | wechat_web(网站应用PC扫码) | wechat_mini(小程序) | wechat_app(移动应用)
     * @param string $data['code'] 授权code
     * @param string $data['mode'] 授权模式：login(登录) | bind(绑定) | getOpenid(仅获取OpenID)
     * @return array 授权结果
     */
    public function getWechatAuthInfo($data)
    {
        // 根据场景选择不同的授权服务
        if ($data['scene'] == 'wechat_official') {
            // 静默授权 
            // 公众号网页授权（微信浏览器内）
            if ($data['mode'] == 'getOpenid') {
                $scope = 'snsapi_base';
            } else {
                // 获取用户信息
                $scope = 'snsapi_userinfo';
            }
            $wechatInfo = (new DeshangOfficialEasyService())
                ->init()
                ->getWechatInfoByCode($data['code'], $scope);
        } else if ($data['scene'] == 'wechat_web') {
            // 网站应用PC扫码登录（微信开放平台）
            $wechatInfo = (new DeshangWebEasyService())
                ->init()
                ->getWechatInfoByCode($data['code']);
        } else if ($data['scene'] == 'wechat_mini') {
            // 小程序授权
            $wechatInfo = (new DeshangMiniEasyService())
                ->init()
                ->getWechatInfoByCode($data['code']);
        } else if ($data['scene'] == 'wechat_app') {
            // 移动应用授权（微信开放平台）
            // APP端通过微信SDK获取code，后端通过code换取access_token和openid
            $wechatInfo = (new DeshangAppEasyService())
                ->init()
                ->getWechatInfoByCode($data['code']);
        } else {
            throw new CommonException('getWechatAuthInfo 场景错误');
        }

        // 是否获取到openid
        if (empty($wechatInfo['openid'])) {
            throw new CommonException('获取用户信息失败');
        }

        // 更新用户授权信息
        if ($data['mode'] == 'login') {
            $result = $this->handleLogin($wechatInfo, $data);
        } else if ($data['mode'] == 'bind') {
            $result = $this->handleBind($wechatInfo, $data);
        } else if ($data['mode'] == 'getOpenid') {
            $result = $this->handleGetOpenid($wechatInfo, $data);
        } else {
            throw new CommonException('授权模式错误');
        }

        return $result;
    }


    /**
     * 处理getOpenid微信授权信息（当用户未绑定微信，用于支付需要用到的openid）
     * 
     * @param array $wechatInfo 微信授权信息
     * @param array $data 授权参数
     * @return array 授权结果
     */
    private function handleGetOpenid(array $wechatInfo, array $data)
    {
        if (!$this->user_id) {
            throw new AuthException('请先登录');
        }

        // 是否存在用户信息
        $user_identity = (new UserIdentityDao())->getUserIdentityInfo([['user_id', '=', $this->user_id]]);
        // 此处不做 unionid 的更新，当其中有用户绑定了unionid，其他的普通用户需要支付获取 openid。如果更新了unionid 会导致重复，登录是通过 unionid 关联
        $user_identity_data = [
            'user_id' => $this->user_id,
        ];

        switch ($data['scene']) {
            case 'wechat_official':
                // 公众号网页授权openid
                $user_identity_data['wx_oauth_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_web':
                // 网站应用PC扫码登录openid
                $user_identity_data['wx_web_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_mini':
                // 小程序openid
                $user_identity_data['wx_mini_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_app':
                // 移动应用openid
                $user_identity_data['wx_app_openid'] = $wechatInfo['openid'];
                break;
            default:
                throw new CommonException('handleGetOpenid 场景错误');
        }


        if (empty($user_identity)) {
            // 创建用户绑定信息
            (new UserIdentityDao())->createUserIdentity($user_identity_data);
        } else {
            // 更新用户绑定信息
            (new UserIdentityDao())->updateUserIdentity([['user_id', '=', $this->user_id]], $user_identity_data);
        }
        return $wechatInfo;
    }

    /**
     * 处理登录微信授权信息
     * 
     * @param array $wechatInfo 微信授权信息
     * @param array $data 授权参数
     * @return array 登录结果
     */
    private function handleLogin(array $wechatInfo, array $data)
    {
        // 登录是一定需要通过微信开放平台关联，通过 unionid 关联
        // 在大多数商城系统中，解除微信绑定关系的操作通常是独立的，但考虑到用户如果需要重新绑定其他账户，unionid 会重复，所以只通过 unionid 关联
        if (empty($wechatInfo['unionid'])) {
            throw new CommonException('用户信息unionid不存在');
        }

        // 用户是否关联unionid，存在则自动登录（可能还存在一种情况，用户普通登录，但微信支付更新了 openid，此处通过unionid关联，不影响）
        $user_identity = (new UserIdentityDao())->getUserIdentityInfo([['wx_unionid', '=', $wechatInfo['unionid']]]);
        if (!empty($user_identity)) {
            // 自动登录
            $user_id = $user_identity['user_id'];
        } else {
            // 自动注册
            $register_data = [
                'username' => $this->generateRandomUsername(),
                'password' => generateRandomString(6),
            ];
            $user_id = (new DeshangUserService())->createUser($register_data);
        }

        // 更新用户绑定信息
        $user_identity_data = [
            'user_id' => $user_id,
            'wx_unionid' => $wechatInfo['unionid'],
        ];
        // 根据授权场景存储到对应的openid字段
        switch ($data['scene']) {
            case 'wechat_official':
                // 公众号网页授权openid（微信浏览器内授权，存储到wx_oauth_openid）
                $user_identity_data['wx_oauth_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_web':
                // 网站应用PC扫码登录openid（微信开放平台网站应用，存储到wx_web_openid）
                $user_identity_data['wx_web_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_mini':
                // 小程序openid（存储到wx_mini_openid）
                $user_identity_data['wx_mini_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_app':
                // 移动应用openid（存储到wx_app_openid）
                $user_identity_data['wx_app_openid'] = $wechatInfo['openid'];
                break;
            default:
                throw new CommonException('handleLogin 场景错误');
        }

        if (empty($user_identity)) {
            // 创建用户绑定信息
            (new UserIdentityDao())->createUserIdentity($user_identity_data);
        } else {
            // 更新用户绑定信息
            (new UserIdentityDao())->updateUserIdentity([['user_id', '=', $user_id], ['wx_unionid', '=', $wechatInfo['unionid']]], $user_identity_data);
        }


        $user_info = (new UserDao())->getUserInfo([['id', '=', $user_id], ['is_deleted', '=', 0]]);
        if (empty($user_info) || empty($user_info['id'])) {
            throw new CommonException('用户不存在');
        }
        $result = (new LoginService())->loginAfter($user_info);
        return $result;
    }

    /**
     * 处理绑定微信授权信息
     * 
     * @param array $wechatInfo 微信授权信息
     * @param array $data 授权参数
     * @return array 绑定结果
     */
    private function handleBind(array $wechatInfo, array $data)
    {
        if (!$this->user_id) {
            throw new AuthException('请先登录');
        }
        // 绑定微信授权信息一定需要通过微信开放平台关联，通过 unionid 关联
        // 在大多数商城系统中，解除微信绑定关系的操作通常是独立的，但考虑到用户如果需要重新绑定其他账户，unionid 会重复，所以只通过 unionid 关联
        if (empty($wechatInfo['unionid'])) {
            throw new CommonException('用户信息unionid不存在');
        }

        // 检测用户是否已绑定微信
        $user_identity = (new UserIdentityDao())->getUserIdentityInfo([['wx_unionid', '=', $wechatInfo['unionid']]]);
        if (!empty($user_identity)) {
            throw new CommonException('此微信已经绑定了用户');
        }

        // 先删除当前用户下所有绑定
        // 可能出现普通用户通过支付如果已经绑定了 openid，但是没有绑定 unionid，所以需要先删除所有绑定，再进行绑定
        (new UserIdentityDao())->deleteUserIdentity([['user_id', '=', $this->user_id]]);

        // 更新用户绑定信息
        $user_identity_data = [
            'user_id' => $this->user_id,
            'wx_unionid' => $wechatInfo['unionid'],
        ];
        // 根据授权场景存储到对应的openid字段
        switch ($data['scene']) {
            case 'wechat_official':
                // 公众号网页授权openid（微信浏览器内授权，存储到wx_oauth_openid）
                $user_identity_data['wx_oauth_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_web':
                // 网站应用PC扫码登录openid（微信开放平台网站应用，存储到wx_web_openid）
                $user_identity_data['wx_web_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_mini':
                // 小程序openid（存储到wx_mini_openid）
                $user_identity_data['wx_mini_openid'] = $wechatInfo['openid'];
                break;
            case 'wechat_app':
                // 移动应用openid（存储到wx_app_openid）
                $user_identity_data['wx_app_openid'] = $wechatInfo['openid'];
                break;
            default:
                throw new CommonException('handleBind 场景错误');
        }

        // 创建用户绑定信息
        (new UserIdentityDao())->createUserIdentity($user_identity_data);

        return $wechatInfo;


    }

    // 获取随机用户名
    public function generateRandomUsername($prefix = '')
    {
        for ($i = 1; $i <= 30; $i++) {
            $user_name = $prefix . date('ymdHis') . generateRandomString(4);
            $user_info = (new UserDao())->getUserInfo([['username', '=', $user_name]]);
            if ($user_info) {
                //用户名存在继续
                continue;
            }
            return $user_name;
        }
        throw new CommonException('获取随机用户名失败');
    }





    /**
     * 获取公众号网页授权URL（仅用于wechat_official场景）
     * 
     * 用于微信浏览器内的公众号网页授权
     * 授权URL: https://open.weixin.qq.com/connect/oauth2/authorize
     * 使用公众号配置（wechat_official_setting）
     * 
     * @param array $data 授权参数
     * @param string $data['mode'] 授权模式：login(登录) | bind(绑定) | getOpenid(仅获取OpenID)
     * @param string $data['redirect_url'] 授权后重定向URL
     * @return string 授权URL
     * @throws CommonException
     */
    public function getOfficialAuthUrl($data)
    {
        // 公众号网页授权（微信浏览器内）
        // 授权URL: https://open.weixin.qq.com/connect/oauth2/authorize
        // 使用公众号配置（wechat_official_setting）
        $scope = 'snsapi_userinfo';
        if ($data['mode'] == 'getOpenid') {
            $scope = 'snsapi_base';
        }
        // 使用EasyService获取授权url
        return (new DeshangOfficialEasyService())->init()->getOAuthUrl($data['redirect_url'], $scope);
    }

    /**
     * 获取网站应用PC扫码登录URL（仅用于wechat_web场景）
     * 
     * 用于PC端网站应用扫码登录
     * 授权URL: https://open.weixin.qq.com/connect/qrconnect
     * 使用网站应用配置（wechat_web_setting）
     * 网站应用授权URL固定使用 qrconnect 接口，scope固定为 snsapi_login
     * 
     * @param array $data 授权参数
     * @param string $data['mode'] 授权模式：login(登录) | bind(绑定) | getOpenid(仅获取OpenID)
     * @param string $data['redirect_url'] 授权后重定向URL
     * @return string 授权URL
     * @throws CommonException
     */
    public function getWebAuthUrl($data)
    {
        // 网站应用PC扫码登录（微信开放平台）
        // 授权URL: https://open.weixin.qq.com/connect/qrconnect
        // 使用网站应用配置（wechat_web_setting）
        // 网站应用授权URL固定使用 qrconnect 接口，scope固定为 snsapi_login
        // mode 参数在此场景下主要用于业务逻辑区分，不用于 state 参数
        // state参数使用默认值（空字符串，微信会自动处理）
        return (new DeshangWebEasyService())->init()->getQrConnectUrl($data['redirect_url']);
    }





    /**
     * 检测用户是否绑定 openid
     * 
     * 根据不同的授权场景检测对应的openid字段：
     * - wechat_official: 检测 wx_oauth_openid（公众号网页授权openid）
     * - wechat_web: 检测 wx_web_openid（网站应用PC扫码登录openid）
     * - wechat_mini: 检测 wx_mini_openid（小程序openid）
     * - wechat_app: 检测 wx_app_openid（移动应用openid）
     * 
     * @param array $data 检测参数
     * @param string $data['scene'] 授权场景：wechat_official(公众号) | wechat_web(网站应用) | wechat_mini(小程序) | wechat_app(移动应用)
     * @return bool 是否已绑定
     */
    public function checkWechatAuth($data)
    {
        if (!$this->user_id) {
            throw new AuthException('请先登录');
        }

        $condition = [
            ['user_id', '=', $this->user_id],
        ];

        $user_identity = (new UserIdentityDao())->getUserIdentityInfo($condition);
        if (empty($user_identity)) {
            return false;
        }

        // 根据场景检测对应的openid字段
        switch ($data['scene']) {
            case 'wechat_official':
                // 公众号网页授权openid（微信浏览器内授权）
                if (!empty($user_identity['wx_oauth_openid'])) {
                    return true;
                }
                break;
            case 'wechat_web':
                // 网站应用PC扫码登录openid（微信开放平台网站应用）
                if (!empty($user_identity['wx_web_openid'])) {
                    return true;
                }
                break;
            case 'wechat_mini':
                // 小程序openid
                if (!empty($user_identity['wx_mini_openid'])) {
                    return true;
                }
                break;
            case 'wechat_app':
                // 移动应用openid
                if (!empty($user_identity['wx_app_openid'])) {
                    return true;
                }
                break;
        }

        return false;
    }


    // 获取JS SDK配置
    public function getJsSdkConfig($data)
    {
        // 获取公众号配置信息
        $result = (new DeshangOfficialEasyService())->init()->getJsSdkConfig($data['url'], false);
        return $result;
    }
}
