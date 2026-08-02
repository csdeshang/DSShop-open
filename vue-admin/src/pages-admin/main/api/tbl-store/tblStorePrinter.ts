import request, { API_BASE_URLS } from '@/utils/request'

export function getTblStorePrinterPages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/printers/pages`, { params })
}

export function getTblStorePrinterInfo(id: number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${id}`)
}

export function createTblStorePrinter(params: Record<string, any>) {
  return request.post(`${API_BASE_URLS.ADMIN}/tbl-store/printers`, params)
}

export function updateTblStorePrinter(params: Record<string, any>) {
  return request.put(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${params.id}`, params)
}

export function deleteTblStorePrinter(id: number) {
  return request.delete(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${id}`)
}

export function bindTblStorePrinter(printerId: number) {
  return request.patch(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${printerId}/bind`)
}

export function unbindTblStorePrinter(printerId: number) {
  return request.patch(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${printerId}/unbind`)
}

export function printTblOrder(params: Record<string, any>) {
  const { printer_id, ...restParams } = params
  return request.post(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${printer_id}/print-order`, restParams)
}

export function getTblStorePrinterStatus(printerId: number) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/printers/${printerId}/status`)
}

export function getTblStorePrinterLogPages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.ADMIN}/tbl-store/printer-logs/pages`, { params })
}
