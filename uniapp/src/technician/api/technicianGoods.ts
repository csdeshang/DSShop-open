import request, { API_BASE_URLS } from '@/utils/request'




// 获取师傅关联所属店铺商品列表
export function getTechnicianGoodsRelPages(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.TECHNICIAN}/technician/goods-rels/pages`,  params )
}

// 关联服务项目
export function updateTechnicianGoodsRel(goods_id: number) {
    return request.put(`${API_BASE_URLS.TECHNICIAN}/technician/goods-rels/${goods_id}`)
}



