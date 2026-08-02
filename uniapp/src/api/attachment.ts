import request, { API_BASE_URLS } from '@/utils/request'


// 用户管理附件

export function getAttachmentCateList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/attachment/categories`,  params )
}
export function createAttachmentCate(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.USER}/attachment/categories`, params)
}
export function updateAttachmentCate(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.USER}/attachment/categories/${params.id}`, params)
}

export function deleteAttachmentCate(id: number) {
    return request.delete(`${API_BASE_URLS.USER}/attachment/categories/${id}`)
}


//附件文件
export function getAttachmentFilePages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/attachment/files/pages`,  params )
}

// 附件重命名 以及移动分类
export function updateBatchAttachmentFile(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.USER}/attachment/files/batch`, params)
}

export function deleteAttachmentFile(params: Record<string, any>) {
    return request.delete(`${API_BASE_URLS.USER}/attachment/files/batch`, { data: params, showSuccessMessage: true })
}



