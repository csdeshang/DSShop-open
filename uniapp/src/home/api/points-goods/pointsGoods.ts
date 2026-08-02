import request, { API_BASE_URLS } from '@/utils/request'

// 获取积分商品列表
export function getPointsGoodsList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/points-goods/goods/list`, params)
}

// 获取积分商品分页列表
export function getPointsGoodsPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/points-goods/goods/pages`, params)
}

// 获取积分商品详情
export function getPointsGoodsDetail(id: number) {
    return request.get(`${API_BASE_URLS.USER}/points-goods/goods/${id}`)
}
