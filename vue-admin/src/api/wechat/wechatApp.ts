
import request, { API_BASE_URLS } from '@/utils/request'

export function getWechatAppSetting() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/app/settings`)
}

export function updateWechatAppSetting(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/wechat/app/settings`, params)
}
