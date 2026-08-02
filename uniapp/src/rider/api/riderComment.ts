import request, { API_BASE_URLS } from '@/utils/request'

// 获取骑手评论分页列表
export function getRiderCommentPages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.RIDER}/rider/comments/pages`, params)
}

// 获取骑手评论详情
export function getRiderCommentInfo(id: number) {
  return request.get(`${API_BASE_URLS.RIDER}/rider/comments/${id}`)
}

// 骑手回复评论
export function replyRiderComment(data: { id: number; reply_content: string }) {
  const { id, ...restData } = data
  return request.post(`${API_BASE_URLS.RIDER}/rider/comments/${id}/reply`, restData)
}
