import request, { API_BASE_URLS } from '@/utils/request'

export function getHouseGoodsPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/house/goods/pages`, { params })
}

export function addHouseGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/house/goods`, params)
}

export function getHouseGoodsUpdateInfo(id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/house/goods/${id}/edit`)
}

export function updateHouseGoods(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/house/goods/${params.id}`, params)
}

export function softDeleteHouseGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/house/goods/batch-soft-delete`, params)
}
