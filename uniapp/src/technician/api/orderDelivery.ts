import request, { API_BASE_URLS } from '@/utils/request'

// 获取师傅订单列表
export function getOrderDeliveryPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/pages`,  params )
}

// 获取师傅订单详情
export function getOrderDeliveryInfo(order_id: number) {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}`)
}


// 师傅抢单
export function grabOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/grab`, restParams)
}

// 师傅确认接单
export function acceptOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/accept`, restParams)
}

// 师傅出发
export function departureOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/departure`, restParams)
}

// 师傅已到达
export function arrivedOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/arrived`, restParams)
}

// 师傅开始服务
export function startServiceOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/start-service`, restParams)
}

// 师傅完成服务
export function completeOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/complete`, restParams)
}

// 师傅取消订单
export function cancelOrderDelivery(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.post(`${API_BASE_URLS.TECHNICIAN}/order/deliveries/${order_id}/cancel`, restParams)
}






