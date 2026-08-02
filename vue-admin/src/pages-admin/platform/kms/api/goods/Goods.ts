import request, { API_BASE_URLS } from '@/utils/request'

export function getKmsGoodsPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/kms/goods/pages`, { params })
}

export function addKmsGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/kms/goods`, params)
}

export function getKmsGoodsUpdateInfo(id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/kms/goods/${id}/edit`)
}

export function updateKmsGoods(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/kms/goods/${params.id}`, params)
}

export function softDeleteKmsGoods(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/kms/goods/batch-soft-delete`, params)
}
