import request, { API_BASE_URLS } from '@/utils/request'

// 获取用户发票信息列表
export function getUserInvoiceList() {
    return request.get(`${API_BASE_URLS.USER}/user/invoices`)
}

// 获取用户发票信息详情
export function getUserInvoiceInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/invoices/${id}`)
}

// 创建用户发票信息
export function createUserInvoice(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/invoices`, params)
}

// 更新用户发票信息
export function updateUserInvoice(params: any) {
    const { id, ...restParams } = params
    return request.put(`${API_BASE_URLS.USER}/user/invoices/${id}`, restParams)
}

// 删除用户发票信息
export function deleteUserInvoice(id: number) {
    return request.delete(`${API_BASE_URLS.USER}/user/invoices/${id}`)
}

// 设置默认用户发票信息
export function setDefaultUserInvoice(id: number) {
    return request.patch(`${API_BASE_URLS.USER}/user/invoices/${id}/default`)
}

