import request, { API_BASE_URLS } from '@/utils/request'

export function getTblGoodsFlashsaleInfo(goods_id: number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-goods/flashsales/${goods_id}`)
}

export function updateTblGoodsFlashsale(data: Record<string, any>) {
  const { goods_id, ...restParams } = data
  return request.put(`${API_BASE_URLS.ADMIN}/tbl-goods/flashsales/${goods_id}`, restParams)
}

export function deleteTblGoodsFlashsale(goods_id: number) {
  return request.delete(`${API_BASE_URLS.ADMIN}/tbl-goods/flashsales/${goods_id}`)
}
