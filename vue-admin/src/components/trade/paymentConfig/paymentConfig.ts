import request, { API_BASE_URLS } from '@/utils/request'


// 获取平台支付配置
export function getPaymentConfig() {
    return request.get(`${API_BASE_URLS.ADMIN}/trade/payment-configs/list`)
}

export function getPaymentConfigInfo(id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/trade/payment-configs/${id}`)
}

export function createPaymentConfig(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/trade/payment-configs`, params )
}

export function updatePaymentConfig(params: Record<string, any>) {
    const { id, ...restParams } = params
    return request.put(`${API_BASE_URLS.ADMIN}/trade/payment-configs/${id}`, restParams)
}
