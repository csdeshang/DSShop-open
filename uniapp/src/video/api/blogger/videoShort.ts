import request, { API_BASE_URLS } from '@/utils/request'

// 博主对短视频的管理

// 发布短视频
export function publishVideoShort(params: any) {
    return request.post(`${API_BASE_URLS.BLOGGER}/video/shorts`, params)
}


// 删除短视频
export function deleteVideoShort(id: number) {
    return request.delete(`${API_BASE_URLS.BLOGGER}/video/shorts/${id}`)
}

// 获取博主短视频分页列表
export function getVideoShortPages(params: any) {
    return request.get(`${API_BASE_URLS.BLOGGER}/video/shorts/pages`, params)
}





