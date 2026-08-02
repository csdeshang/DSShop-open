import request, { API_BASE_URLS } from '@/utils/request'




export function getTblGoodsCategoryTree(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-goods/categories/tree`, params)
}





