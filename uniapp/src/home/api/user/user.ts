import request, { API_BASE_URLS } from '@/utils/request'


//获取用户信息以及角色信息
export function getUserInfoWithRoles() {
    return request.get(`${API_BASE_URLS.USER}/user/user/info-with-roles`)
}

//获取用户信息
export function getUserInfo() {
    return request.get(`${API_BASE_URLS.USER}/user/user/info`)
}

// 更新用户信息
export function updateUserInfo(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/user/info`,  params )
}

// 修改用户密码
export function editUserPassword(params: any) {
    return request.put(`${API_BASE_URLS.USER}/user/user/password`,  params )
}

// 编辑用户身份证（提交实名认证审核）
export function editUserIdcard(params: { idcard_name: string; idcard_number: string; idcard_image1: string; idcard_image2: string; idcard_image3: string }) {
    return request.put(`${API_BASE_URLS.USER}/user/user/idcard`, params)
}


// 获取用户充值记录
export function getUserRechargeLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/recharge-logs/pages`,  params)
}

// 获取用户提现记录
export function getUserWithdrawLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/withdrawal-logs/pages`,  params)
}

// 获取用户余额记录
export function getUserBalanceLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/balance-logs/pages`,  params )
}

// 获取用户积分记录
export function getUserPointsLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/points-logs/pages`,  params )
}

// 获取签到状态
export function getUserPointsSignStatus() {
    return request.get(`${API_BASE_URLS.USER}/user/points-sign/status`)
}

// 签到
export function doUserPointsSign() {
    return request.post(`${API_BASE_URLS.USER}/user/points-sign`)
}

// 获取成长值记录
export function getUserGrowthLogPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/growth-logs/pages`,  params )
}


// 用户商品评价 列表
export function getUserGoodsCommentPages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/goods-comments/pages`,  params )
}

// 添加商品评价
export function addUserGoodsComment(params: any) {
    return request.post(`${API_BASE_URLS.USER}/user/goods-comments`,  params )
}


