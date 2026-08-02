import request, { API_BASE_URLS } from '@/utils/request'


//生成二维码并返回图片数据
export function qrcodeCreate(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/qrcodes`,  params )
}

//生成二维码并直接输出图片(直接返回URL)
export function qrcodeShow(params: Record<string, any>) {
    const baseUrl = import.meta.env.VITE_APP_BASE_URL.replace(/\/$/, '');
    
    // 构建完整的URL，直接返回可访问的二维码图片URL
    const queryParams = new URLSearchParams();
    
    // 将所有参数添加到URL查询参数中
    for (const key in params) {
        if (params.hasOwnProperty(key)) {
            queryParams.append(key, params[key]);
        }
    }
    
    // 返回完整的URL，前端可以直接用于img标签的src属性
    return `${baseUrl}/${API_BASE_URLS.USER}/qrcodes/image?${queryParams.toString()}`;
}




