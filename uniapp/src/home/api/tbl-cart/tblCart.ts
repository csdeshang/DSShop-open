
import request, { API_BASE_URLS } from '@/utils/request'



// 商城购物车接口(如果单独逻辑  又不想影响其他平台可在此单独处理)
export function addTblCart(params: any, platform: string) {
    return request.post(`${API_BASE_URLS.USER}/${platform}/carts`, params)
}

// 获取购物车列表
export function getTblCartList(params: any, platform: string) {
    return request.get(`${API_BASE_URLS.USER}/${platform}/carts`, params)
}

// 删除购物车商品
export function deleteTblCart(params: any, platform: string) {
    return request.delete(`${API_BASE_URLS.USER}/${platform}/carts/batch`, params)
}

// 更新购物车商品数量
export function updateTblCart(params: any, platform: string) {
    const { cart_id, ...restParams } = params
    return request.put(`${API_BASE_URLS.USER}/${platform}/carts/${cart_id}`, restParams)
}





