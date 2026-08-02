import request, { API_BASE_URLS } from '@/utils/request'

// 上报师傅位置
export function reportTechnicianTrack(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.TECHNICIAN}/technician/tracks`, params)
}

// 获取师傅轨迹分页
export function getTechnicianTrackPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/tracks/pages`, params)
}


// 获取师傅轨迹
export function getTechnicianTrackList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/tracks/list`, params)
}


