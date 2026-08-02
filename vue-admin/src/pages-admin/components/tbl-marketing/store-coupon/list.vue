<template>
  <el-card shadow="never" class="mb-[10px]">
    <el-form :model="searchParams" inline>
      <el-form-item label="优惠券名称">
        <el-input v-model="searchParams.coupon_name" placeholder="优惠券名称" clearable />
      </el-form-item>

      <el-form-item label="优惠券类型">
        <el-select v-model="searchParams.coupon_type" placeholder="优惠券类型" style="width: 240px">
          <el-option :key="0" label="请选择优惠券类型" :value="0" />
          <el-option v-for="item in coupon_type_options" :key="item.value" :label="item.label" :value="item.value" />
        </el-select>
      </el-form-item>

      <el-form-item>
        <el-button type="primary" @click="resetPage">查询</el-button>
        <el-button @click="resetSearchParams">重置</el-button>
      </el-form-item>
    </el-form>
    <el-button type="primary" @click="handleAddCoupon">添加优惠券</el-button>
  </el-card>

  <el-card shadow="never">
    <el-table :data="tableData.data" size="large" v-loading="tableData.loading">
      <el-table-column label="优惠券名称" prop="coupon_name" width="200" />
      <el-table-column label="优惠券类型" prop="coupon_type_desc" width="200" />
      <el-table-column label="折扣/优惠" prop="discount_value" width="100" />
      <el-table-column label="最低消费" prop="min_spend" width="100" />
      <el-table-column label="优惠券状态" prop="status_desc" width="100" />
      <el-table-column label="总库存" prop="total_stock" width="100" />
      <el-table-column label="已领取" prop="claimed_stock" width="100" />
      <el-table-column label="剩余" prop="available_stock" width="100" />
      <el-table-column label="积分要求" prop="points_required" width="100" />
      <el-table-column label="排序" prop="sort" width="100" />
      <el-table-column label="开始时间" prop="start_time" width="200" />
      <el-table-column label="结束时间" prop="end_time" width="200" />
      <el-table-column label="创建时间" prop="create_at" width="200" />
      <el-table-column label="操作" align="right" fixed="right" width="130">
        <template #default="{ row }">
          <div class="flex flex-row">
            <el-button type="primary" link @click="handleEditCoupon(row)">编辑</el-button>
            <el-button type="primary" link @click="handleCouponUserList(row)">领取记录</el-button>
          </div>
        </template>
      </el-table-column>
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

  <TblStoreCouponUserList ref="handleCouponUserListRef" />
  <TblStoreCouponEdit ref="handleEditCouponRef" @complete="getTableList" />
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'

import { useEnum } from '@/hooks/useEnum'
import { usePagination } from '@/hooks/usePagination'
import { getTblStoreCouponPages } from '@/pages-admin/main/api/tbl-store/tblStoreCoupon'

import TblStoreCouponEdit from './edit.vue'
import TblStoreCouponUserList from './user-list.vue'

const { options: coupon_type_options } = useEnum('default.tbl_store_coupon.coupon_type')

const searchParams = reactive({
  coupon_name: '',
  coupon_type: 0
})

const {
  tableData,
  getTableList,
  resetSearchParams,
  resetPage
} = usePagination({
  page_current: 1,
  page_size: 10,
  requestFun: getTblStoreCouponPages,
  searchParams: searchParams
})
getTableList()

const handleEditCouponRef = ref<any>()

const handleAddCoupon = () => {
  handleEditCouponRef.value.setDialogData({})
  handleEditCouponRef.value?.openDialog()
}

const handleEditCoupon = (row: any) => {
  handleEditCouponRef.value.setDialogData(row)
  handleEditCouponRef.value?.openDialog()
}

const handleCouponUserListRef = ref<any>()

const handleCouponUserList = (row: any) => {
  handleCouponUserListRef.value.setDialogData(row)
  handleCouponUserListRef.value?.openDialog()
}
</script>
