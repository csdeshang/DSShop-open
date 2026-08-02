import request, { API_BASE_URLS } from '@/utils/request'


// 收藏取消 收藏视频
export function toggleVideoCollect(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/video-collects`, params)
}