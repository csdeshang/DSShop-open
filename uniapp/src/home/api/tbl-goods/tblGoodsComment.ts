import request, { API_BASE_URLS } from '@/utils/request'




// 获取商品评论分页
export function getTblGoodsCommentPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-goods/comments/pages`, params)
}






