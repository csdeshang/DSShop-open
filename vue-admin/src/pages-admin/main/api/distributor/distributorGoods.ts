import request, { API_BASE_URLS } from '@/utils/request'

// 获取分销商品详情
export function getDistributorGoodsInfo(goods_id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/distributor/goods/${goods_id}`)
}

// 参与分销
export function updateDistributorGoods(params: Record<string, any>) {
    const { goods_id, ...restParams } = params
    return request.put(`${API_BASE_URLS.ADMIN}/distributor/goods/${goods_id}`, restParams)
}

// 取消分销
export function cancelDistributorGoods(goods_id: number) {
    return request.delete(`${API_BASE_URLS.ADMIN}/distributor/goods/${goods_id}`)
}
