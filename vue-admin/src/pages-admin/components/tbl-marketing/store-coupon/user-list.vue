<template>
  <el-dialog v-model="dialogVisible" title="优惠券领取记录" width="800px" destroy-on-close>
    <el-card shadow="never">
      <el-table :data="tableData.data" style="width: 100%" v-loading="tableData.loading">
        <el-table-column prop="id" label="记录ID" width="80" />
        <el-table-column prop="user_id" label="用户ID" width="100" />
        <el-table-column prop="create_at" label="领取时间" width="180" />
        <el-table-column prop="status_desc" label="使用状态" width="100" />
        <el-table-column prop="used_time" label="使用时间" width="180" />
      </el-table>

      <div class="flex justify-end mt-[20px]">
        <el-pagination
          v-model:current-page="tableData.page_current"
          v-model:page-size="tableData.page_size"
          layout="total, sizes, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="getTableList()"
          @current-change="getTableList"
        />
      </div>
    </el-card>

    <template #footer>
      <el-button @click="dialogVisible = false">关闭</el-button>
    </template>
  </el-dialog>
</template>

<script lang="ts" setup>
import { reactive, ref, watch } from 'vue'

import { usePagination } from '@/hooks/usePagination'
import { getTblStoreCouponUserPages } from '@/pages-admin/main/api/tbl-store/tblStoreCoupon'

const dialogVisible = ref(false)
const couponId = ref(0)

const searchParams = reactive({
  coupon_id: couponId.value
})

const { tableData, getTableList } = usePagination({
  page_current: 1,
  page_size: 10,
  requestFun: getTblStoreCouponUserPages,
  searchParams: searchParams
})

watch(
  () => couponId.value,
  (newCouponId) => {
    searchParams.coupon_id = newCouponId
    if (newCouponId > 0) {
      getTableList()
    }
  }
)

const setDialogData = (data: any) => {
  couponId.value = data.id || 0
}

const openDialog = () => {
  dialogVisible.value = true
}

const closeDialog = () => {
  dialogVisible.value = false
  couponId.value = 0
  tableData.data = []
  tableData.total = 0
  tableData.page_current = 1
}

defineExpose({
  setDialogData,
  openDialog,
  closeDialog
})
</script>
