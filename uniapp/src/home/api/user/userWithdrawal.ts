import request, { API_BASE_URLS } from '@/utils/request'


// 获取用户提现记录列表
export function getUserWithdrawalLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/withdrawal-logs/pages`, params)
}

// 获取用户提现记录详情
export function getUserWithdrawalLogInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/withdrawal-logs/${id}`)
}


// 用户申请提现
export function applyUserWithdrawal(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/withdrawal-logs/apply`, params)
}



// 用户提现账户
export function getUserWithdrawalAccountList() {
    return request.get(`${API_BASE_URLS.USER}/user/withdrawal-accounts/list`)
}

// 用户提现账户详情
export function getUserWithdrawalAccountInfo(id: number) {
    return request.get(`${API_BASE_URLS.USER}/user/withdrawal-accounts/${id}`)
}

// 用户提现账户添加
export function createUserWithdrawalAccount(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/withdrawal-accounts`, params)
}

// 用户提现账户编辑
export function updateUserWithdrawalAccount(params: any) {
    const { id, ...restParams } = params
    return request.put(`${API_BASE_URLS.USER}/user/withdrawal-accounts/${id}`, restParams)
}

// 用户提现账户删除
export function deleteUserWithdrawalAccount(id: number) {
    return request.delete(`${API_BASE_URLS.USER}/user/withdrawal-accounts/${id}`)
}














