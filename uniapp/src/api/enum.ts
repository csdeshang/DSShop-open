import request, { API_BASE_URLS } from '@/utils/request'



export function getEnumData(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/enums/data`,  params )
}



