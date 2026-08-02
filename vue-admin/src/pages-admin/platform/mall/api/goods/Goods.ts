import request, { API_BASE_URLS } from '@/utils/request'

export function getMallGoodsPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/mall/goods/pages`, { params })
}

export function addMallGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/mall/goods`, params)
}

export function getMallGoodsUpdateInfo(id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/mall/goods/${id}/edit`)
}

export function updateMallGoods(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/mall/goods/${params.id}`, params)
}

export function softDeleteMallGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/mall/goods/batch-soft-delete`, params)
}
