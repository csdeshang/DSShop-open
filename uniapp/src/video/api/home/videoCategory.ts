

import request, { API_BASE_URLS } from '@/utils/request'



// 获取视频分类树结构
export function getVideoCategoryTree(params: any) {
    return request.get(`${API_BASE_URLS.USER}/video/categories/tree`, params)
}

// 获取视频分类列表
export function getVideoCategoryList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/video/categories/list`, params)
}