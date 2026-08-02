import request, { API_BASE_URLS } from '@/utils/request'




// 领取优惠券
export function receiveTblStoreCoupon(id: any) {
    return request.post(`${API_BASE_URLS.USER}/user/store-coupons/${id}/receive`, {}, { showErrorMessage: true })
}



// 获取店铺优惠券列表
export function getUserTblStoreCouponPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/store-coupons/pages`,  params )
}



