import request, { API_BASE_URLS } from '@/utils/request'

export function getTblGoodsPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/tbl-goods/goods/pages`, params)
}