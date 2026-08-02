import request, { API_BASE_URLS } from '@/utils/request'

// 获取骑手信息
export function getRiderInfo() {
    return request.get(`${API_BASE_URLS.RIDER}/rider/info`)
}

// 更新骑手信息
export function updateRiderInfo(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.RIDER}/rider/info`, params)
}

// 设置骑手上线/下线状态
export function setRiderInfoStatus(params: Record<string, any>) {
    return request.patch(`${API_BASE_URLS.RIDER}/rider/info/status`,  params )
}