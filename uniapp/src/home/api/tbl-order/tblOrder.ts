import request, { API_BASE_URLS } from '@/utils/request'



// 获取订单列表
export function getTblOrderPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/orders/pages`, params)
}

// 获取订单详情
export function getTblOrderInfo(orderId: number) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/orders/${orderId}`)
}

// 确认收货
export function confirmTblOrder(orderId: string | number) {
    return request.patch(`${API_BASE_URLS.USER}/tbl-order/orders/${orderId}/confirm`)
}

// 取消订单
export function cancelTblOrder(orderId: string | number) {
    return request.patch(`${API_BASE_URLS.USER}/tbl-order/orders/${orderId}/cancel`)
}

// 关闭订单
export function closeTblOrder(orderId: string | number) {
    return request.patch(`${API_BASE_URLS.USER}/tbl-order/orders/${orderId}/close`)
}


// 获取订单状态统计
export function getTblOrderStatusCount() {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/orders/status/count`)
}

// 获取订单配送员信息（包含最新位置）
export function getTblOrderRiderInfo(orderId: number) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/orders/${orderId}/rider`)
}

// 获取订单师傅信息（包含最新位置）
export function getTblOrderTechnicianInfo(orderId: number) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/orders/${orderId}/technician`)
}



