import request, { API_BASE_URLS } from '@/utils/request'


// 添加商品收藏
export function createGoodsFavorite(goods_id: any) {
    return request.post(`${API_BASE_URLS.USER}/user/goods-favorites/${goods_id}` )
}

// 删除商品收藏
export function deleteGoodsFavorite(goods_id: any) {
    return request.delete(`${API_BASE_URLS.USER}/user/goods-favorites/${goods_id}` )
}

// 获取商品收藏列表
export function getGoodsFavoritePages(params: any) {
    return request.get(`${API_BASE_URLS.USER}/user/goods-favorites/pages`, { params })
}

// 获取商品收藏状态
export function getGoodsFavoriteStatus(goods_id: any) {
    return request.get(`${API_BASE_URLS.USER}/user/goods-favorites/${goods_id}`)
}




