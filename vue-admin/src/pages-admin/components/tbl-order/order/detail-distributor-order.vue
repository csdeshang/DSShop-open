<template>
    <el-table :data="tableData.data" style="width: 100%" v-loading="tableData.loading">
        <el-table-column label="分销商ID" prop="distributor_user_id" min-width="120" />
        <el-table-column label="分销商等级" prop="distributor_level_id" min-width="120" />
        <el-table-column label="购买者ID" prop="user_id" min-width="120" />
        <el-table-column label="商品ID" prop="goods_id" min-width="120" />
        <el-table-column label="实付金额" prop="pay_price" min-width="120" />
        <el-table-column label="佣金" prop="commission_amount" min-width="120" />
        <el-table-column label="佣金类型" prop="commission_type_desc" min-width="120" />
        <el-table-column label="佣金状态" prop="commission_status_desc" min-width="120" />
        <el-table-column label="备注" prop="commission_remark" min-width="300" />
    </el-table>
</template>

<script lang="ts" setup>
import { reactive, watch } from 'vue'
import { getDistributorOrderList } from '@/pages-admin/main/api/distributor/distributorOrder'

const props = defineProps({
  order_id: { type: Number, default: 0 }
})

const searchParams = reactive({ order_id: props.order_id })
const tableData = reactive({ loading: true, data: [] as any[] })

const fetchDistributorOrderList = () => {
  tableData.loading = true
  getDistributorOrderList(searchParams).then(res => {
    tableData.loading = false
    tableData.data = res.data
  }).catch(() => {
    tableData.loading = false
  })
}

watch(() => props.order_id, (newOrderId) => {
  searchParams.order_id = newOrderId
  if (newOrderId > 0) fetchDistributorOrderList()
}, { immediate: true })
</script>
