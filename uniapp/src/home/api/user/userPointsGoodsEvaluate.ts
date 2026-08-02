import request, { API_BASE_URLS } from '@/utils/request'

// 添加积分商品评价
export function addPointsGoodsEvaluate(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.USER}/user/points-goods-evaluates`, params)
}

// 获取积分商品评价分页列表
export function getPointsGoodsEvaluatePages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/user/points-goods-evaluates/pages`, { params })
}

// 获取积分商品评价详情
export function getPointsGoodsEvaluateInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/points-goods-evaluates/${id}`)
}
