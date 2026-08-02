import request, { API_BASE_URLS } from '@/utils/request'


// 前端显示 相关接口

// 获取短视频分页列表
export function getVideoShortPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/video/shorts/pages`, params)
}


// 获取短视频详情
export function getVideoShortDetail(id: string) {
    return request.get(`${API_BASE_URLS.USER}/video/shorts/${id}`)
}


// 获取短视频关联的商品列表
export function getVideoShortGoodsList(id: string) {
    return request.get(`${API_BASE_URLS.USER}/video/shorts/${id}/goods-list`)
}