import request, { API_BASE_URLS } from '@/utils/request'



export function getTechnicianList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/technician/technicians/list`, params)
}
export function getTechnicianPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/technician/technicians/pages`, params)
}

export function getTechnicianDetail(params: any) {
    const { id, ...queryParams } = params
    return request.get(`${API_BASE_URLS.USER}/technician/technicians/${id}`, queryParams)
}

