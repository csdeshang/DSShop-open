import request, { API_BASE_URLS } from '@/utils/request'

export function getTblOrderRefundPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/pages`, { params })
}

export function getTblOrderRefundList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/list`, { params })
}

export function getTblOrderRefundInfo(refundId: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/${refundId}`)
}

export function addTblOrderRefund(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/tbl-order/refunds`, params)
}

export function getTblOrderRefundStatusDict(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/refund-status-dict`, { params })
}

export function agreeTblOrderRefund(params: Record<string, any>) {
    const { refund_id, ...restParams } = params
    return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/${refund_id}/agree`, restParams)
}

export function rejectTblOrderRefund(params: Record<string, any>) {
    const { refund_id, ...restParams } = params
    return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/${refund_id}/reject`, restParams)
}

export function receiveGoodsTblOrderRefund(refundId: number) {
    return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/${refundId}/receive-goods`)
}

export function retryTblOrderRefund(refundId: number) {
    return request.post(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/${refundId}/retry`)
}

export function getTblOrderRefundLogList(refundId: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/refunds/${refundId}/logs`)
}
