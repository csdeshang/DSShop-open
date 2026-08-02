import request, { API_BASE_URLS } from '@/utils/request'

// 上报骑手位置
export function reportRiderTrack(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.RIDER}/rider/tracks`, params)
}

// 获取骑手轨迹分页
export function getRiderTrackPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.RIDER}/rider/tracks/pages`, params)
}

// 获取骑手轨迹
export function getRiderTrackList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.RIDER}/rider/tracks/list`, params)
}
