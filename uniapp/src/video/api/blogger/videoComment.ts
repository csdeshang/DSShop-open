import request, { API_BASE_URLS } from '@/utils/request'

// 博主对评论管理

// 切换评论字段状态
export function toggleVideoCommentField(params: Record<string, any>) {
    const { id, field } = params
    return request.patch(`${API_BASE_URLS.BLOGGER}/video/comments/${id}/toggle-field`, { field })
}

// 回复评论（预留功能，后端路由和控制器方法尚未实现）
export function replyVideoComment(params: {
    id: number;
    reply_content: string;
}) {
    const { id, ...restData } = params
    return request.post(`${API_BASE_URLS.BLOGGER}/video/comments/${id}/reply`, restData)
} 

