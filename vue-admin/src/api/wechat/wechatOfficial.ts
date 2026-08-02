
import request, { API_BASE_URLS } from '@/utils/request'

export function getWechatOfficialSetting() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/official/settings`)
}

export function updateWechatOfficialSetting(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/wechat/official/settings`, params)
}

export function updateWechatOfficialMenu(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/wechat/official/menus`, params)
}

export function publishWechatOfficialMenu(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/wechat/official/menus/publish`, params)
}

export function getWechatOfficialMenu() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/official/menus`)
}
