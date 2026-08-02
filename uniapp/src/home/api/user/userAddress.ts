import request, { API_BASE_URLS } from '@/utils/request'


export function getUserAddressList() {
    return request.get(`${API_BASE_URLS.USER}/user/addresses`)
}
export function getUserAddressInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/addresses/${id}`)
}
export function createUserAddress(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/addresses`, params)
}
export function updateUserAddress(params: any) {
    const { id, ...restParams } = params
    return request.put(`${API_BASE_URLS.USER}/user/addresses/${id}`, restParams)
}
export function deleteUserAddress(id: number) {
    return request.delete(`${API_BASE_URLS.USER}/user/addresses/${id}`)
}
export function setDefaultUserAddress(id: number) {
    return request.patch(`${API_BASE_URLS.USER}/user/addresses/${id}/default`)
}







