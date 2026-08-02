import request, { API_BASE_URLS } from '@/utils/request'

export function getTblOrderDeliveryDict(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/deliveries/delivery-dict`, { params })
}

export function addTblOrderDelivery(data: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/tbl-order/deliveries`, data)
}

export function getTblOrderDeliveryPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/deliveries/pages`, { params })
}
