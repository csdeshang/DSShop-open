



import request, { API_BASE_URLS } from '@/utils/request'


// 点赞取消 点赞视频
export function toggleVideoLike(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/video-likes`, params)
}