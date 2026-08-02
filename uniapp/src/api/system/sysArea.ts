import request, { API_BASE_URLS } from '@/utils/request'




export function getSysAreaList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/system/areas/list`, params)
}




