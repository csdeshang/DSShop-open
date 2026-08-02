import request, { API_BASE_URLS } from '@/utils/request'

// 获取博主信息
export function getBloggerInfo() {
    return request.get(`${API_BASE_URLS.BLOGGER}/blogger/info`)
}

// 更新博主信息
export function updateBloggerInfo(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.BLOGGER}/blogger/info`, params)
}
