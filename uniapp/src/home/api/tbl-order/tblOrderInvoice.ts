import request, { API_BASE_URLS } from '@/utils/request'

/** 与后端 TblOrderInvoiceEnum 一致（0 起连续编号） */
export const INVOICE_STATUS_NOT_SUBMITTED = 0
export const INVOICE_STATUS_PENDING = 1
export const INVOICE_STATUS_PROCESSING = 2
export const INVOICE_STATUS_ISSUED = 3
export const INVOICE_STATUS_REJECTED = 4
export const INVOICE_STATUS_VOIDED = 5

export function getTblOrderInvoicePages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/invoices/pages`, params)
}

export function applyTblOrderInvoice(params: any) {
    return request.post(`${API_BASE_URLS.USER}/tbl-order/invoices`, params)
}

export function getTblOrderInvoiceInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/tbl-order/invoices/${id}`)
}
