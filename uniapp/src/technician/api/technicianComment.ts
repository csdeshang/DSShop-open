import request, { API_BASE_URLS } from '@/utils/request'

// 获取师傅评论分页列表
export function getTechnicianCommentPages(params: Record<string, any>) {
  return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/comments/pages`, params)
}

// 获取师傅评论详情
export function getTechnicianCommentInfo(id: number) {
  return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/comments/${id}`)
}

// 回复师傅评论
export function replyTechnicianComment(data: { id: number; reply_content: string }) {
  const { id, ...restData } = data
  return request.post(`${API_BASE_URLS.TECHNICIAN}/technician/comments/${id}/reply`, restData)
} 