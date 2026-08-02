import request, { API_BASE_URLS } from '@/utils/request'

export function getTblGoodsUserdiscountInfo(goods_id: number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-goods/userdiscounts/${goods_id}`)
}

export function updateTblGoodsUserdiscount(data: Record<string, any>) {
  const { goods_id, ...restParams } = data
  return request.put(`${API_BASE_URLS.ADMIN}/tbl-goods/userdiscounts/${goods_id}`, restParams)
}

export function deleteTblGoodsUserdiscount(goods_id: number) {
  return request.delete(`${API_BASE_URLS.ADMIN}/tbl-goods/userdiscounts/${goods_id}`)
}
