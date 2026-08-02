import request, { API_BASE_URLS } from '@/utils/request'

// 获取微信订阅模板ID
export function getWechatSubscribeTemplateId(params: {
    keys: string
    type: string
}) {
    return request.get(`${API_BASE_URLS.USER}/wechat/subscribe/template-id`, params ,{
        showErrorMessage: false
    })
}

// 保存订阅记录
export function createWechatSubscribeRecord(data: {
    keys: string
    type: string
}) {
    return request.post(`${API_BASE_URLS.USER}/wechat/subscribe/record`, data)
}

// 获取公众号订阅URL
export function getOfficialSubscribeUrl(data: {
    keys: string
    redirect_url: string
    scene?: number
}) {
    return request.post(`${API_BASE_URLS.USER}/wechat/subscribe/official-url`, data)
}