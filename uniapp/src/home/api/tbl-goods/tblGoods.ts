import request, { API_BASE_URLS } from '@/utils/request'




export function getTblGoodsList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-goods/goods/list`, params)
}
export function getTblGoodsPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-goods/goods/pages`, params)
}

export function getTblGoodsDetail(params: any, platform: string) {
    return request.get(`${API_BASE_URLS.USER}/${platform}/goods/detail`, params)
}






