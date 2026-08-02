


import request, { API_BASE_URLS } from '@/utils/request'


// 添加评论
export function createVideoComment(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/video-comments`, params)
}


// 点赞取消 点赞评论
export function toggleVideoCommentLike(commentId: number) {
    return request.patch(`${API_BASE_URLS.USER}/user/video-comment-likes/${commentId}`)
}





