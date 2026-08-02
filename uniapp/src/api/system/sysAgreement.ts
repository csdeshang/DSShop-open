import request, { API_BASE_URLS } from '@/utils/request'



// 获取系统协议
export function getAgreementByCode(code: any) {
  return request.get(`${API_BASE_URLS.USER}/system/agreements/${code}`)
}
