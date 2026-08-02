import request, { API_BASE_URLS } from '@/utils/request'


export function getUserLevelInfo() {
    return request.get(`${API_BASE_URLS.USER}/user/level/info`)
}
export function getUserLevelList() {
    return request.get(`${API_BASE_URLS.USER}/user/level/list`)
}








