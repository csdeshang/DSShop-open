<template>
  <div>
    <el-card shadow="never" class="mb-[10px]">
      <el-form :model="searchParams" inline>
        <el-form-item label="商品名">
          <el-input v-model="searchParams.goods_name" placeholder="输入商品名" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="resetPage">查询</el-button>
          <el-button @click="resetSearchParams">重置</el-button>
        </el-form-item>
      </el-form>
      <el-button type="primary" @click="handleAdd()">添加</el-button>
    </el-card>

    <el-card shadow="never">
      <el-tabs v-model="searchParams.tab_selected" @tab-change="handleTabChange">
        <el-tab-pane name="all" label="全部"></el-tab-pane>
        <el-tab-pane name="pending" label="销售中"></el-tab-pane>
        <el-tab-pane name="un_send" label="下架"></el-tab-pane>
        <el-tab-pane name="pending_review" label="待审核"></el-tab-pane>
        <el-tab-pane name="review_failed" label="审核失败"></el-tab-pane>
        <el-tab-pane name="violated" label="违规下架"></el-tab-pane>
      </el-tabs>

      <el-table :data="tableData.data" size="large" v-loading="tableData.loading">
        <el-table-column label="ID" prop="id" width="60" />
        <el-table-column label="商品" prop="goods_name">
          <template #default="{ row }">
            <div class="flex flex-row">
              <div class="mr-[10px]">
                <el-image :src="formatImageUrl(row.cover_image, ThumbnailPresets.small, 'goods')" style="width: 50px; height: 50px;" fit="cover" />
              </div>
              <div>
                <div class="text-[14px]">{{ row.goods_name }}</div>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="价格" prop="goodsSku.sku_price" width="100" />
        <el-table-column label="销量" prop="sales_num" width="100" />
        <el-table-column label="总库存" prop="stock_num" width="100" />
        <el-table-column label="排序" prop="goods_sort" width="100" />
        <el-table-column label="状态" prop="goods_status_desc" width="100" />
        <el-table-column label="操作" align="right" fixed="right" width="340">
          <template #default="{ row }">
            <el-button type="primary" link @click="openGoodsPreview(row, 'pc')">PC预览</el-button>
            <el-button type="primary" link @click="openGoodsPreview(row, 'h5')">H5预览</el-button>
            <template v-if="!isGoodsInPromotion(row)">
              <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
              <el-button type="primary" link @click="handleDelete(row.id)">删除</el-button>
              <el-button type="primary" link @click="handleKmsLesson(row)">课时管理</el-button>
            </template>
            <el-tooltip v-else :content="goodsNotEditDesc(row)" placement="right">
              <el-button type="primary" link disabled>已锁定</el-button>
            </el-tooltip>
          </template>
        </el-table-column>
      </el-table>

      <div class="flex justify-end mt-[20px]">
        <el-pagination v-model:current-page="tableData.page_current" v-model:page-size="tableData.page_size"
          layout="total, sizes, prev, pager, next, jumper" :total="tableData.total" @size-change="getTableList()"
          @current-change="getTableList" />
      </div>
    </el-card>

    <KmsLessonDrawer ref="kmsLessonDrawerRef" />
  </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { usePagination } from '@/hooks/usePagination'
import { openGoodsPreview } from '@/utils/goods-preview'
import { getKmsGoodsPages, softDeleteKmsGoods } from '@/pages-admin/platform/kms/api/goods/Goods'
import KmsLessonDrawer from './kms-lesson-drawer.vue'

const router = useRouter()
const kmsLessonDrawerRef = ref<any>()

const searchParams = reactive({
  goods_name: '',
  tab_selected: 'all',
})

const {
  tableData,
  getTableList,
  resetSearchParams,
  resetPage
} = usePagination({
  page_current: 1,
  page_size: 10,
  requestFun: getKmsGoodsPages,
  searchParams: searchParams
})
getTableList()

const handleTabChange = (name: string) => {
  searchParams.tab_selected = name
  getTableList()
}

const isGoodsInPromotion = (row: any) => {
  return row.is_flashsale_goods === 1 ||
    row.is_wholesale_goods === 1 ||
    row.is_userdiscount_goods === 1 ||
    row.is_distributor_goods === 1
}

const goodsNotEditDesc = (row: any) => {
  const promotionTypes = [
    { key: 'is_flashsale_goods', text: '秒杀活动' },
    { key: 'is_wholesale_goods', text: '批发活动' },
    { key: 'is_userdiscount_goods', text: '用户折扣活动' },
    { key: 'is_distributor_goods', text: '商品分销' }
  ]

  const activePromotions = promotionTypes
    .filter(type => row[type.key] === 1)
    .map(type => type.text)

  return `商品参加了 ${activePromotions.join(',')}，商品锁定`
}

const handleAdd = () => {
  router.push('edit')
}

const handleEdit = (row: any) => {
  router.push('edit?goods_id=' + row.id)
}

const handleDelete = (goods_ids: string) => {
  ElMessageBox.confirm('您确定是否删除此商品', '提示',
    {
      confirmButtonText: '确认',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(() => {
    softDeleteKmsGoods({ goods_ids }).then(() => {
      getTableList()
    }).catch(() => {})
  })
}

const handleKmsLesson = (row: any) => {
  kmsLessonDrawerRef.value.setDialogData(row)
  kmsLessonDrawerRef.value?.openDialog()
}
</script>
