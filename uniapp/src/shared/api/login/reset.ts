import request, { API_BASE_URLS } from '@/utils/request'



//手机验证码找回
export function resetPasswordMobile(params :any) {
    return request.post(`${API_BASE_URLS.USER}/reset-password/mobile`,params)
}


//邮箱找回 （未实现）
export function resetPasswordEmail(params :any) {
    return request.post(`${API_BASE_URLS.USER}/reset-password/email`,params)
}


