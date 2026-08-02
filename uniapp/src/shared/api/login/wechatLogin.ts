import request, { API_BASE_URLS } from '@/utils/request'





/**
 * 微信授权/登录统一接口
 * @param params 授权参数
 */
export function wechatAuth(params: {
    code: string,
    scene: 'wechat_app' | 'wechat_mini' | 'wechat_official',
    mode: 'login' | 'bind' | 'getOpenid',
}) {
    return request.post(`${API_BASE_URLS.USER}/wechat/auth`, params)
}

/**
 * 检查用户是否已绑定微信
 * @param data.scene 场景/绑定类型: wechat_mini/wechat_app/wechat_official
 */
export function checkUserWechatAuth(params: {
    scene?: 'wechat_app' | 'wechat_mini' | 'wechat_official'
}) {
    return request.get(`${API_BASE_URLS.USER}/wechat/check-auth`, params)
}

/**
 * 获取公众号网页授权URL(移动端专用)
 * 
 * 注意：此接口仅用于移动端公众号授权（wechat_official场景）
 * - 小程序授权（wechat_mini）不需要此接口
 * - 移动应用授权（wechat_app）不需要此接口
 * 
 * @param params.mode 授权模式: login(登录) | bind(绑定) | getOpenid(仅获取OpenID)
 * @param params.redirect_url 授权成功后的回调地址
 */
export function getOfficialAuthUrl(params: {
    mode?: 'login' | 'bind' | 'getOpenid',
    redirect_url?: string
}) {
    return request.post(`${API_BASE_URLS.USER}/wechat/official-auth-url`, params)
}


/**
 * 获取微信JS SDK配置
 * @param data.url 当前页面URL
 */
export function getJsSdkConfig(params: {
    url: string,
}) {
    return request.post(`${API_BASE_URLS.USER}/wechat/js-sdk-config`, params)
}
