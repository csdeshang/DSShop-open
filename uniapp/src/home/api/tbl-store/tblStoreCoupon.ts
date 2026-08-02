import request, { API_BASE_URLS } from '@/utils/request'


// 获取店铺优惠券分页列表
export function getTblStoreCouponPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-store/coupons/pages`, params)
}


// 获取店铺优惠券列表
export function getTblStoreCouponList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-store/coupons/list`, params)
}



