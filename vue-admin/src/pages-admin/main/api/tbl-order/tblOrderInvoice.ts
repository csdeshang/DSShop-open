import request, { API_BASE_URLS } from '@/utils/request'

export function getTblOrderInvoicePages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/invoices/pages`, { params })
}

export function getTblOrderInvoiceInfo(id: number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-order/invoices/${id}`)
}

export function markTblOrderInvoiceProcessing(invoiceId: number) {
  return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/invoices/${invoiceId}/processing`)
}

export function rejectTblOrderInvoice(params: { invoice_id: number; reject_reason: string }) {
  const { invoice_id, ...rest } = params
  return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/invoices/${invoice_id}/reject`, rest)
}

export function issueTblOrderInvoice(
  invoiceId: number,
  body: { invoice_no: string; out_invoice_no?: string; invoice_file_url?: string; issue_remark?: string }
) {
  return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/invoices/${invoiceId}/issue`, body)
}

export function voidTblOrderInvoice(invoiceId: number, body: { void_reason: string }) {
  return request.patch(`${API_BASE_URLS.ADMIN}/tbl-order/invoices/${invoiceId}/void`, body)
}
