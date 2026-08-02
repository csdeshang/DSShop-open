import request, { API_BASE_URLS } from '@/utils/request'


// 获取积分商品订单分页列表
export function getPointsGoodsOrderPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/points-goods-orders/pages`, params)
}

// 获取积分商品订单详情
export function getPointsGoodsOrderDetail(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/points-goods-orders/${id}`)
}

// 取消积分商品订单
export function cancelPointsGoodsOrder(id: number) {
    return request.patch(`${API_BASE_URLS.USER}/user/points-goods-orders/${id}/cancel`)
}

// 确认收货
export function confirmPointsGoodsOrder(id: number) {
    return request.patch(`${API_BASE_URLS.USER}/user/points-goods-orders/${id}/confirm`)
}
