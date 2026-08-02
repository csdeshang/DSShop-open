import request, { API_BASE_URLS } from '@/utils/request'

export function getWechatOfficialTemplateList() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/official/templates/list`)
}

export function syncWechatOfficialTemplate(keys: string[]) {
    return request.post(`${API_BASE_URLS.ADMIN}/wechat/official/templates/sync`, { keys })
}

export function deleteWechatOfficialTemplate(key: string) {
    return request.delete(`${API_BASE_URLS.ADMIN}/wechat/official/templates/${key}`)
}
