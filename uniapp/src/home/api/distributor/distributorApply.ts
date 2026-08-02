import request, { API_BASE_URLS } from '@/utils/request'



// 获取分销员申请信息
export function getDistributorApplyInfo() {
    return request.get(`${API_BASE_URLS.USER}/distributor/apply/info`)
}


// 获取分销员申请资格
export function getDistributorApplyPermission() {
    return request.get(`${API_BASE_URLS.USER}/distributor/apply/permission`)
}


// 创建分销员申请
export function createDistributorApply() {
    return request.post(`${API_BASE_URLS.USER}/distributor/apply`)
}




