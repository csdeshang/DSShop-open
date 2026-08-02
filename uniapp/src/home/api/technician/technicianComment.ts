import request, { API_BASE_URLS } from '@/utils/request'

export function getTechnicianCommentPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/technician/comments/pages`, params)
}

export function getTechnicianCommentList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/technician/comments/list`, params)
}
