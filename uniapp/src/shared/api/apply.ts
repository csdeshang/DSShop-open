import request, { API_BASE_URLS } from '@/utils/request'


// 用户申请骑手
export function userApplyRider(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.USER}/user/apply/rider`,  params )
}


// 用户申请师傅
export function userApplyTechnician(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.USER}/user/apply/technician`,  params )
}
