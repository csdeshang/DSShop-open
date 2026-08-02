import request, { API_BASE_URLS } from '@/utils/request'



// 获取正在工作的订单，包含抢单，待取货，待送达
export function getWorkingOrderDeliveryList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.RIDER}/order/deliveries/working`,  params )
}


// 抢单
export function grabOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.RIDER}/order/deliveries/${order_id}/grab`,  restParams )
}

// 取消
export function cancelOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.RIDER}/order/deliveries/${order_id}/cancel`,  restParams )
}

// 到店
export function arrivedOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.RIDER}/order/deliveries/${order_id}/arrived`,  restParams )
}

// 取货
export function pickupOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.RIDER}/order/deliveries/${order_id}/pickup`,  restParams )
}

// 完成送达
export function completeOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.RIDER}/order/deliveries/${order_id}/complete`,  restParams )
}

// 已完成
export function getOrderDeliveryPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.RIDER}/order/deliveries/pages`,  params )
}

// 获取订单详情
export function getOrderDeliveryInfo(order_id: number) {
    return request.get(`${API_BASE_URLS.RIDER}/order/deliveries/${order_id}`)
}

