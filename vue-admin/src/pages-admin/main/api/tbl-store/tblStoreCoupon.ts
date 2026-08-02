import request, { API_BASE_URLS } from '@/utils/request'

// 优惠券分页列表
export function getTblStoreCouponPages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/coupons/pages`, { params })
}

// 优惠券列表
export function getTblStoreCouponList(params: Record<string, any> = {}) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/coupons/list`, { params })
}

// 优惠券详情
export function getTblStoreCouponInfo(id: string | number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/coupons/${id}`)
}

// 创建优惠券
export function createTblStoreCoupon(data: Record<string, any>) {
  return request.post(`${API_BASE_URLS.ADMIN}/tbl-store/coupons`, data)
}

// 更新优惠券
export function updateTblStoreCoupon(data: Record<string, any>) {
  return request.put(`${API_BASE_URLS.ADMIN}/tbl-store/coupons/${data.id}`, data)
}

// 优惠券领取记录
export function getTblStoreCouponUserPages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/coupon-users/pages`, { params })
}
