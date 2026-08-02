import request, { API_BASE_URLS } from '@/utils/request'



// 获取支付方式
export function getPaymentList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/trade/payment/list`, params)
}

// 支付
export function tradePay(params: any) {
    return request.post(`${API_BASE_URLS.USER}/trade/pay`, params)
}




