import request, { API_BASE_URLS } from '@/utils/request'



// 获取师傅余额流水
export function getTechnicianBalanceLogPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/balance-logs/pages`,  params )
}


// 转至用户余额
export function technicianBalanceToUser(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.TECHNICIAN}/technician/balance/to-user`, params)
}
