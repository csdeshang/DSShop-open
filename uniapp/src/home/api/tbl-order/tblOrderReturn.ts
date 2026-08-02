import request, { API_BASE_URLS } from '@/utils/request'

// 申请退款
export function applyRefund(params: any) {
    return request.post(`${API_BASE_URLS.USER}/tbl-order/refunds`, params)
}

// 获取退款记录列表
export function getRefundPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/refunds/pages`, params)
}

// 获取退款详情
export function getRefundInfo(refundId: string) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/refunds/${refundId}`)
}

// 取消退款
export function cancelRefund(refundId: string) {
    return request.patch(`${API_BASE_URLS.USER}/tbl-order/refunds/${refundId}/cancel`)
}

// 编辑退款 ， 退款申请中及退款拒绝可编辑
export function editRefund(params: any) {
    const { id, ...restParams } = params
    return request.put(`${API_BASE_URLS.USER}/tbl-order/refunds/${id}`, restParams)
}


// 退货
export function returnGoods(params: any) {
    const { id, ...restParams } = params
    return request.post(`${API_BASE_URLS.USER}/tbl-order/refunds/${id}/return-goods`, restParams)
}


