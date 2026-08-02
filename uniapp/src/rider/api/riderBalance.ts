import request, { API_BASE_URLS } from '@/utils/request'



// 获取骑手余额流水
export function getRiderBalanceLogPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.RIDER}/rider/balance-logs/pages`,  params )
}


// 转至用户余额
export function riderBalanceToUser(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.RIDER}/rider/balance/to-user`, params)
}
