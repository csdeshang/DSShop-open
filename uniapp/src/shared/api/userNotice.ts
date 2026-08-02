import request, { API_BASE_URLS } from '@/utils/request'



// 获取用户通知日志(骑手、商户、所有消息通知)
export function getUserNoticeLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/notice-logs/pages`,  params )
}


// 获取用户通知日志详情(骑手、商户、所有消息通知)
export function getUserNoticeLogInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/notice-logs/${id}`)
}


