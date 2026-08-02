import request, { API_BASE_URLS } from '@/utils/request'



//普通注册
export function registerNormal(params :any) {
    return request.post(`${API_BASE_URLS.USER}/register/normal`,params)
}


//手机验证码注册
export function registerMobile(params :any) {
    return request.post(`${API_BASE_URLS.USER}/register/mobile`,params)
}


