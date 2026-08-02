
import request, { API_BASE_URLS } from '@/utils/request'

export function getWechatWebSetting() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/web/settings`)
}

export function updateWechatWebSetting(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/wechat/web/settings`, params)
}
