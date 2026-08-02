import request, { API_BASE_URLS } from '@/utils/request'


// 获取师傅信息
export function getTechnicianInfo() {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/info`)
}

// 更新师傅信息
export function updateTechnicianInfo(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.TECHNICIAN}/technician/info`, params)
}

// 设置师傅上线/下线状态
export function setTechnicianInfoStatus(params: Record<string, any>) {
    return request.patch(`${API_BASE_URLS.TECHNICIAN}/technician/info/technician-status`, params)
}
