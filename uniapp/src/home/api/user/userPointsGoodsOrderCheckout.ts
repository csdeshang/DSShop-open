import request, { API_BASE_URLS } from '@/utils/request'

// 获取积分商品下单信息
export function checkPointsGoodsOrderCheckout(params: {
    goods_id: number
    quantity?: number
    address_id?: number
}) {
    return request.get(`${API_BASE_URLS.USER}/user/points-goods-orders/checkout/check`, params)
}

// 生成积分商品订单
export function generatePointsGoodsOrder(data: {
    goods_id: number
    quantity?: number
    address_id?: number
    user_remark?: string
}) {
    return request.post(`${API_BASE_URLS.USER}/user/points-goods-orders/checkout/generate`, data)
}
