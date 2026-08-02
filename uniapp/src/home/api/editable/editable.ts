import request, { API_BASE_URLS } from '@/utils/request'




export function getEditablePageInfo(params: any) {
    return request.get(`${API_BASE_URLS.USER}/editable/page/info`, params)
}




