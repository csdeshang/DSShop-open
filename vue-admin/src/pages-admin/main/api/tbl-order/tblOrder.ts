import request, { API_BASE_URLS } from '@/utils/request'

export function getTblOrderPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/orders/pages`, { params })
}

export function getTblOrderStatusDict(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/orders/order-status-dict`, { params })
}

export function getTblOrderInfo(id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/orders/${id}`);
}

export function modifyTblOrderAmount(params: Record<string, any>) {
    const { order_id, ...restParams } = params
    return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/orders/${order_id}/modify-amount`, restParams)
}

/** 软删除订单 */
export function softDeleteTblOrder(id: number) {
    return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/orders/${id}/soft-delete`)
}

/** 恢复已删除的订单 */
export function restoreTblOrder(id: number) {
    return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/orders/${id}/restore`)
}

export function getTblOrderGoodsList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/goods/list`, { params })
}

export function getTblOrderGoodsPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/goods/pages`, { params })
}

export function getTblOrderLogList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/order-logs`, { params })
}

export function getTblOrderPayLogList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/order-pay-logs`, { params })
}

/** 导出订单 Excel（返回 Blob） */
export function exportTblOrders(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/orders/export`, {
        params,
        responseType: 'blob',
        timeout: 120000,
        showErrorMessage: false,
    } as any)
}
