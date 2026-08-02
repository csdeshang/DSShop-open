import request, { API_BASE_URLS } from '@/utils/request'

// 获取视频浏览记录
export function getVideoViewLogPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/user/video-view-logs/pages`, params)
}

// 创建视频浏览记录
export function createVideoViewLog(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.USER}/user/video-view-logs`, params)
}

// 删除单个视频浏览记录
export function deleteVideoViewLog(id: number) {
    return request.delete(`${API_BASE_URLS.USER}/user/video-view-logs/${id}`)
}

// 批量删除视频浏览记录
export function batchDeleteVideoViewLog(params: Record<string, any>) {
    return request.delete(`${API_BASE_URLS.USER}/user/video-view-logs/batch`, params)
}

// 清空视频浏览记录
export function clearVideoViewLog(params: Record<string, any>) {
    return request.delete(`${API_BASE_URLS.USER}/user/video-view-logs/clear`, params)
}

