import request, { API_BASE_URLS } from '@/utils/request'

export function getFoodGoodsPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/food/goods/pages`, { params })
}

export function addFoodGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/food/goods`, params)
}

export function getFoodGoodsUpdateInfo(id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/food/goods/${id}/edit`)
}

export function updateFoodGoods(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/food/goods/${params.id}`, params)
}

export function softDeleteFoodGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/food/goods/batch-soft-delete`, params)
}
