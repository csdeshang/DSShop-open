/**
 * 分销商品 - 等级佣金项
 */
export interface DistributorLevelItem {
  distributor_level_id?: number
  level_name?: string
  base_self_ratio?: number
  base_parent1_ratio?: number
  base_parent2_ratio?: number
  goods_self_ratio?: number
  goods_parent1_ratio?: number
  goods_parent2_ratio?: number
  goods_self_amount?: number
  goods_parent1_amount?: number
  goods_parent2_amount?: number
}

/**
 * 分销商品 - SKU 项
 */
export interface SkuDistributorItem {
  sku_id?: number
  sku_name?: string
  sku_price: number
  distributor_levels?: DistributorLevelItem[]
}

/**
 * 分销商品信息
 */
export interface DistributorGoodsInfo {
  id?: number
  goods_name?: string
  distributor_goods_type?: number
  is_distributor_goods?: number
}

/**
 * 分销商品编辑表单
 */
export interface DistributorGoodsForm {
  goods_info: DistributorGoodsInfo
  sku_distributor_list: SkuDistributorItem[]
}
