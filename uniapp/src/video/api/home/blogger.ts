import request, { API_BASE_URLS } from '@/utils/request'




// 获取博主列表
export function getBloggerPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/blogger/bloggers/pages`, params)
}


// 获取博主详情
export function getBloggerDetail(bloggerId: number) {
    return request.get(`${API_BASE_URLS.USER}/blogger/bloggers/${bloggerId}`)
}


