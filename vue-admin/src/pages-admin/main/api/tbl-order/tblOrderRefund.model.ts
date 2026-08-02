/**
 * 退款表单中的商品项
 */
export interface RefundGoodsItem {
  id: number
  goods_name?: string
  goods_image?: string
  goods_num: number
  sku_name?: string
  pay_price: number
  apply_amount: number
}

/**
 * 订单退款表单
 */
export interface RefundForm {
  order_id: number
  order_goods_id: number
  goods_list: RefundGoodsItem[]
  apply_amount: number
  refund_explain: string
}
