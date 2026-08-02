<template>
    <el-dialog v-model="dialogVisible" :title="popTitle" width="900px" :destroy-on-close="true">
        <el-form :model="formData" label-width="100px" ref="formRef" v-loading="loading">
            <el-form-item label="实付金额">
                <span>{{ orderInfo.pay_amount }}</span>
            </el-form-item>

            <el-divider content-position="left">商品价格调整</el-divider>

            <el-table :data="formData.goods_list" style="width: 100%" border>
                <el-table-column label="商品图片" width="80">
                    <template #default="scope">
                        <el-image :src="formatImageUrl(scope.row.goods_image, ThumbnailPresets.small, 'goods')" style="width: 50px; height: 50px;" fit="cover" />
                    </template>
                </el-table-column>
                <el-table-column prop="goods_name" label="商品名称" min-width="180">
                    <template #default="scope">
                        <div>{{ scope.row.goods_name }}</div>
                        <div class="sku-name">{{ scope.row.sku_name }}</div>
                    </template>
                </el-table-column>
                <el-table-column label="原价" width="100">
                    <template #default="scope">
                        <div>{{ scope.row.sku_price }}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="goods_num" label="数量" width="80" />
                <el-table-column label="调整价格" width="200">
                    <template #default="scope">
                        <el-input-number v-model="scope.row.pay_price" :precision="2" :step="0.01" :min="0" placeholder="调整后价格" @change="calculateTotalAmount" />
                    </template>
                </el-table-column>
            </el-table>

            <el-divider content-position="left">运费调整</el-divider>

            <el-form-item label="运费" prop="shipping_amount">
                <el-input-number v-model="formData.shipping_amount" :precision="2" :step="0.01" :min="0" placeholder="调整后运费" @change="calculateTotalAmount" />
                <div class="original-price">原运费: {{ orderInfo.shipping_amount }}</div>
            </el-form-item>

            <el-divider content-position="left">订单金额</el-divider>

            <el-form-item label="调整后金额">
                <span class="new-total">{{ formData.new_total_amount }}</span>
            </el-form-item>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="dialogVisible = false">取消</el-button>
                <el-button type="primary" :loading="loading" @click="handleSubmit">确认</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import type { FormInstance } from 'element-plus'
import { reactive, ref } from 'vue'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { toFixed } from '@/utils/util'
import { getTblOrderInfo, modifyTblOrderAmount } from '@/pages-admin/main/api/tbl-order/tblOrder'
import type { ModifyAmountForm, TblOrderGoodsItem } from '@/pages-admin/main/api/tbl-order/tblOrder.model'

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle = '订单改价'

const orderInfo = reactive({ id: 0, pay_amount: 0, shipping_amount: 0 })
const formData = reactive<ModifyAmountForm>({ order_id: 0, goods_list: [], shipping_amount: 0, new_total_amount: 0 })
const formRef = ref<FormInstance>()
const emit = defineEmits(['complete'])

const calculateTotalAmount = () => {
  let goodsTotal = 0
  formData.goods_list.forEach(item => {
    goodsTotal += item.pay_price * item.goods_num
  })
  formData.new_total_amount = toFixed(goodsTotal + formData.shipping_amount)
}

const handleSubmit = async () => {
  if (loading.value || !formRef.value) return
  await formRef.value.validate()
  loading.value = true
  modifyTblOrderAmount({
    order_id: formData.order_id,
    goods_list: formData.goods_list.map(item => ({ id: item.id, pay_price: item.pay_price })),
    shipping_amount: formData.shipping_amount,
  }).then(() => {
    loading.value = false
    dialogVisible.value = false
    emit('complete')
  }).catch(() => {
    loading.value = false
  })
}

const setDialogData = async (row: any = null) => {
  loading.value = true
  popTitle = '订单改价'
  if (row?.id) {
    try {
      const data = (await getTblOrderInfo(row.id)).data
      orderInfo.id = data.id
      orderInfo.pay_amount = data.pay_amount
      orderInfo.shipping_amount = data.shipping_amount
      formData.order_id = data.id
      formData.shipping_amount = data.shipping_amount
      formData.goods_list = data.orderGoodsList.map((item: TblOrderGoodsItem) => ({
        id: item.id,
        goods_name: item.goods_name,
        goods_image: item.goods_image,
        goods_num: item.goods_num,
        original_price: item.original_price,
        sku_name: item.sku_name,
        sku_price: item.sku_price,
        pay_price: item.pay_price
      }))
      calculateTotalAmount()
    } catch (error) {
      console.error('获取订单信息失败', error)
    }
  }
  loading.value = false
}

defineExpose({
  openDialog: () => { dialogVisible.value = true },
  setDialogData
})
</script>

<style scoped>
.sku-name { font-size: 12px; color: #909399; margin-top: 5px; }
.original-price { margin-top: 5px; font-size: 12px; color: #909399; }
.new-total { font-size: 18px; color: #f56c6c; font-weight: bold; }
</style>
