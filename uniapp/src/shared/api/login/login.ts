import request, { API_BASE_URLS } from '@/utils/request'




//普通登录
export function loginNormal(params :any) {
    return request.post(`${API_BASE_URLS.USER}/login/normal`,params)
}


//手机验证码登录
export function loginMobile(params :any) {
    return request.post(`${API_BASE_URLS.USER}/login/mobile`,params)
}



//  refresh_token 刷新 access_token
export function refreshToken(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/refresh-token`,params)
}

// 退出登录
export function logout(params?: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/logout`,params)
}