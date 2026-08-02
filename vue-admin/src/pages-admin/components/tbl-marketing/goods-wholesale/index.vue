<template>
  <el-card shadow="never" class="mb-[10px]">
    <el-form :model="searchParams" inline>
      <el-form-item label="商品名称">
        <el-input v-model="searchParams.goods_name" placeholder="商品名称" clearable />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="resetPage">查询</el-button>
        <el-button @click="resetSearchParams">重置</el-button>
      </el-form-item>
    </el-form>
  </el-card>

  <el-card shadow="never">
    <el-tabs v-model="searchParams.platform" @tab-change="handlePlatformTabChange">
      <el-tab-pane v-for="item in platformList" :key="item.id" :label="item.name" :name="item.platform" />
    </el-tabs>

    <el-tabs v-model="searchParams.is_wholesale_goods" @tab-change="handleTabChange">
      <el-tab-pane name="" label="全部" />
      <el-tab-pane name="0" label="未参与批发" />
      <el-tab-pane name="1" label="参与批发" />
    </el-tabs>

    <el-table :data="tableData.data" size="large" v-loading="tableData.loading">
      <el-table-column label="商品ID" prop="id" width="100" />
      <el-table-column label="商品图片" prop="goods_name" width="100">
        <template #default="{ row }">
          <el-image :src="formatImageUrl(row.cover_image, ThumbnailPresets.small, 'goods')" style="width: 50px; height: 50px;" fit="cover" />
        </template>
      </el-table-column>
      <el-table-column label="商品名称" prop="goods_name" />
      <el-table-column label="是否参与批发" prop="is_wholesale_goods">
        <template #default="{ row }">
          <el-tag v-if="row.is_wholesale_goods == 1" type="success">参与批发</el-tag>
          <el-tag v-else type="danger">未参与批发</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="right" fixed="right" width="200">
        <template #default="{ row }">
          <el-button type="primary" link @click="handleUpdateWholesaleGoods(row)">
            {{ row.is_wholesale_goods == 1 ? '编辑' : '参与' }}
          </el-button>
          <el-button type="primary" link v-if="row.is_wholesale_goods == 1" @click="handleDeleteGoodsWholesale(row.id)">取消</el-button>
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

  <TblGoodsWholesaleEdit ref="handleUpdateWholesaleGoodsRef" @complete="getTableList" />
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { ElMessageBox } from 'element-plus'

import { usePagination } from '@/hooks/usePagination'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getTblGoodsPages } from '@/pages-admin/main/api/tbl-goods/tblGoods'
import { deleteTblGoodsWholesale } from '@/pages-admin/main/api/tbl-marketing/tblGoodsWholesale'
import { getSysPlatformList } from '@/pages-admin/main/api/system/SysPlatform'
import TblGoodsWholesaleEdit from './edit.vue'

const searchParams = reactive({
  goods_name: '',
  is_wholesale_goods: '',
  platform: 'mall',
})

const {
  tableData,
  getTableList,
  resetSearchParams,
  resetPage
} = usePagination({
  page_current: 1,
  page_size: 10,
  requestFun: getTblGoodsPages,
  searchParams: searchParams
})
getTableList()

const platformList = ref<any[]>([])
const fetchPlatformList = async () => {
  const response = await getSysPlatformList({ scene: 'store' })
  platformList.value = response.data
}
fetchPlatformList()

const handlePlatformTabChange = (name: string) => {
  searchParams.platform = name
  getTableList()
}

const handleTabChange = (val: string) => {
  searchParams.is_wholesale_goods = val
  getTableList()
}

const handleUpdateWholesaleGoodsRef = ref<any>()
const handleUpdateWholesaleGoods = (row: any) => {
  handleUpdateWholesaleGoodsRef.value?.setDialogData(row)
  handleUpdateWholesaleGoodsRef.value?.openDialog()
}

const handleDeleteGoodsWholesale = (goods_id: number) => {
  ElMessageBox.confirm('您确定是否删除此批发', '提示', {
    confirmButtonText: '确认',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    deleteTblGoodsWholesale(goods_id).then(() => {
      getTableList()
    })
  })
}
</script>
