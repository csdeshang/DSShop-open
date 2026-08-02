import request, { API_BASE_URLS } from '@/utils/request'

export function getTblGoodsWholesaleInfo(goods_id: number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-goods/wholesales/${goods_id}`)
}

export function updateTblGoodsWholesale(data: Record<string, any>) {
  const { goods_id, ...restParams } = data
  return request.put(`${API_BASE_URLS.ADMIN}/tbl-goods/wholesales/${goods_id}`, restParams)
}

export function deleteTblGoodsWholesale(goods_id: number) {
  return request.delete(`${API_BASE_URLS.ADMIN}/tbl-goods/wholesales/${goods_id}`)
}
