import request, { API_BASE_URLS } from '@/utils/request'

// 获取当前用户的微信绑定状态
export function getUserBindWechatStatus() {
    return request.get(`${API_BASE_URLS.USER}/user/bind/wechat/status`)
}

// 解绑微信
export function unbindUserWechat() {
    return request.post(`${API_BASE_URLS.USER}/user/bind/wechat/delete`)
}



