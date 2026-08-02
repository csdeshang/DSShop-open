import request, { API_BASE_URLS } from '@/utils/request';

// 获取系统快递公司列表
export function getSysExpressList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/system/expresses/list`, { params });
}
