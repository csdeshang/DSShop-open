import request, { API_BASE_URLS } from '@/utils/request'

// 获取积分商品评价分页列表
export function getPointsGoodsEvaluatePages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/points-goods/evaluates/pages`, params)
}

// 获取积分商品评价列表
export function getPointsGoodsEvaluateList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/points-goods/evaluates/list`, params)
}
