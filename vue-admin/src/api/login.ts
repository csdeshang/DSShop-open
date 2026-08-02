import request, { API_BASE_URLS } from '@/utils/request'

// 登录
export function loginNormal(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/login`, params)
}

// refresh_token 刷新 access_token
export function refreshToken(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/refresh-token`, { params })
}

// 退出登录
export function logout(params?: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/logout`, { params })
}
