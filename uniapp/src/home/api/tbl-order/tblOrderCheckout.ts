
import request, { API_BASE_URLS } from '@/utils/request'



// 获取订单结算信息
export function checkTblOrderCheckout(params: any, platform: string) {
    return request.post(`${API_BASE_URLS.USER}/${platform}/order-checkout/check`, params)
}

// 生成订单
export function generateTblOrder(params: any, platform: string) {
    return request.post(`${API_BASE_URLS.USER}/${platform}/order-checkout/generate`, params)
}



