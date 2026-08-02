
import request, { API_BASE_URLS } from '@/utils/request'

export function getWechatMiniTemplateList() {
    return request.get(`${API_BASE_URLS.ADMIN}/wechat/mini/templates/list`)
}

export function syncWechatMiniTemplate(keys: string[]) {
    return request.post(`${API_BASE_URLS.ADMIN}/wechat/mini/templates/sync`, { keys })
}

export function deleteWechatMiniTemplate(key: string) {
    return request.delete(`${API_BASE_URLS.ADMIN}/wechat/mini/templates/${key}`)
}
