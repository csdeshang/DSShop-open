

import request, { API_BASE_URLS } from '@/utils/request'



// 获取评论分页列表
export function getVideoCommentPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/video/comments/pages`, params)
}

