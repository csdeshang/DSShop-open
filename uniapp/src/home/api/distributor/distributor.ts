import request, { API_BASE_URLS } from '@/utils/request'


// 获取分销商统计信息
export function getDistributorStatistics() {
    return request.get(`${API_BASE_URLS.USER}/distributor/statistics`)
}


// 获取分销商佣金余额记录
export function getDistributorBalanceLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/distributor/balance-logs/pages`, params)
}


// 获取分销订单列表
export function getDistributorOrderPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/distributor/orders/pages`, params)
}

// 获取分销关系列表
export function getDistributorRelationPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/distributor/relations/pages`, params)
}

// 分销商佣金转至用户余额
export function distributorBalanceToUser(params: any) {
    return request.post(`${API_BASE_URLS.USER}/distributor/balance/to-user`, params)
}
