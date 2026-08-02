import request, { API_BASE_URLS } from '@/utils/request'

// 绑定手机
export function bindMobile(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/bind/mobile`, params)
}

// 解绑手机
export function unbindMobile(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/unbind/mobile`, params)
}