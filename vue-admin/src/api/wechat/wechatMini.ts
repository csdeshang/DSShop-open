
import request, { API_BASE_URLS } from '@/utils/request'

export function getWechatMiniSetting() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/mini/settings`)
}

export function updateWechatMiniSetting(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/wechat/mini/settings`, params)
}
