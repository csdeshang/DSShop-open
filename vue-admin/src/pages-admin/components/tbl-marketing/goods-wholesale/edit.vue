<template>
    <el-drawer v-model="dialogVisible" :title="popTitle" size="80%">
        <el-scrollbar style="height: 100%;">
            <el-form :model="formData" label-width="160px">
                <el-form-item label="商品名称">
                    <span>{{ formData.goods_info?.goods_name }}</span>
                </el-form-item>

                <el-form-item label="是否参与批发">
                    <el-radio-group v-model="formData.goods_info.is_wholesale_goods">
                        <el-radio :value="0">不参与</el-radio>
                        <el-radio :value="1">参与</el-radio>
                    </el-radio-group>
                </el-form-item>

                <div v-show="formData.goods_info.is_wholesale_goods == 1">
                    <div v-for="sku in formData.sku_wholesale_list" :key="sku.sku_id" class="mb-[20px]">
                        <el-divider content-position="left">规格：{{ sku.sku_name || '默认规格' }}</el-divider>
                        <div class="flex mb-[15px]">
                            <span class="mr-[20px]">原价：￥{{ sku.sku_price }}</span>
                            <el-button type="primary" size="small" @click="addWholesaleRule(sku)">添加价格档位</el-button>
                        </div>
                        <el-table :data="sku.wholesale_ladders" border style="width: 100%" v-if="sku.wholesale_ladders?.length > 0">
                            <el-table-column label="批发数量" width="400">
                                <template #default="{ row }">
                                    <div class="flex items-center">
                                        <span class="mr-2">起始数量</span>
                                        <el-input-number v-model="row.quantity_min" :min="1" :precision="0" controls-position="right" style="width: 120px" />
                                        <span class="mx-2">截止数量</span>
                                        <el-input-number v-model="row.quantity_max" :min="row.quantity_min || 1" :precision="0" controls-position="right" style="width: 120px" />
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="批发价格" width="200">
                                <template #default="{ row }">
                                    <el-input-number v-model="row.wholesale_price" :min="0" :max="sku.sku_price" :precision="2" style="width: 150px" />
                                </template>
                            </el-table-column>
                            <el-table-column label="折扣率" width="150">
                                <template #default="{ row }">
                                    <span class="text-green-500" v-if="row.wholesale_price > 0 && row.wholesale_price < sku.sku_price">
                                        {{ ((row.wholesale_price / sku.sku_price) * 100).toFixed(1) }}%
                                    </span>
                                    <span class="text-red-500" v-else-if="row.wholesale_price >= sku.sku_price">批发价必须低于原价</span>
                                    <span v-else>-</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="操作">
                                <template #default="{ $index }">
                                    <el-button type="danger" link @click="removeWholesaleRule(sku, $index)">删除</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <div v-else class="text-gray-400 text-center p-5 border border-dashed rounded">
                            尚未设置批发价格档位，请点击"添加价格档位"按钮添加
                        </div>
                    </div>
                </div>
            </el-form>

            <div class="flex justify-center mt-[20px]">
                <el-button type="primary" @click="handleSubmit" :loading="loading">保存</el-button>
                <el-button @click="dialogVisible = false">取消</el-button>
            </div>
        </el-scrollbar>
    </el-drawer>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
export default defineComponent({ name: 'GoodsWholesaleEdit' })
</script>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { getTblGoodsWholesaleInfo, updateTblGoodsWholesale } from '@/pages-admin/main/api/tbl-marketing/tblGoodsWholesale'

interface GoodsInfo { id: number; goods_name: string; is_wholesale_goods: number }
interface WholesaleRule { id?: number; goods_id?: number; sku_id?: number; wholesale_price: number; quantity_min: number; quantity_max: number }
interface SkuWholesale { sku_id: number; sku_name: string; sku_price: number; wholesale_ladders: WholesaleRule[] }
interface FormData { goods_info: GoodsInfo; sku_wholesale_list: SkuWholesale[] }

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle = '参与批发'
const initialFormData: FormData = { goods_info: { id: 0, goods_name: '', is_wholesale_goods: 0 }, sku_wholesale_list: [] }
const formData = reactive<FormData>({ ...initialFormData })
const emit = defineEmits(['complete'])

const addWholesaleRule = (sku: SkuWholesale) => {
  if (!sku.wholesale_ladders) sku.wholesale_ladders = []
  let minQuantity = 2
  if (sku.wholesale_ladders.length > 0) {
    minQuantity = Math.max(...sku.wholesale_ladders.map(rule => rule.quantity_max)) + 1
  }
  sku.wholesale_ladders.push({ wholesale_price: sku.sku_price * 0.9, quantity_min: minQuantity, quantity_max: minQuantity + 9 })
}

const removeWholesaleRule = (sku: SkuWholesale, index: number) => {
  sku.wholesale_ladders.splice(index, 1)
}

const setDialogData = async (row: any = {}) => {
  loading.value = true
  popTitle = '参与批发'
  Object.assign(formData, JSON.parse(JSON.stringify(initialFormData)))
  if (row.id) {
    const data = (await getTblGoodsWholesaleInfo(row.id)).data
    Object.assign(formData, data)
  }
  loading.value = false
}

const handleSubmit = async () => {
  if (loading.value) return
  if (formData.goods_info.is_wholesale_goods === 0) { submitData(); return }
  for (const sku of formData.sku_wholesale_list) {
    if (!sku.wholesale_ladders?.length) {
      ElMessage.warning(`规格"${sku.sku_name || '默认规格'}"尚未设置批发规则`)
      return
    }
    for (const rule of sku.wholesale_ladders) {
      if (rule.quantity_min <= 0) { ElMessage.warning('最小数量必须大于0'); return }
      if (rule.quantity_max < rule.quantity_min) { ElMessage.warning('最大数量必须大于或等于最小数量'); return }
      if (rule.wholesale_price <= 0) { ElMessage.warning('批发价格必须大于0'); return }
      if (rule.wholesale_price >= sku.sku_price) { ElMessage.warning('批发价格必须低于原价'); return }
    }
    const sortedRules = [...sku.wholesale_ladders].sort((a, b) => a.quantity_min - b.quantity_min)
    for (let i = 0; i < sortedRules.length - 1; i++) {
      if (sortedRules[i].quantity_max >= sortedRules[i + 1].quantity_min) {
        ElMessage.warning('批发规则的数量区间不能重叠')
        return
      }
    }
  }
  submitData()
}

const submitData = async () => {
  try {
    loading.value = true
    const result = await updateTblGoodsWholesale({
      goods_id: formData.goods_info.id,
      is_wholesale_goods: formData.goods_info.is_wholesale_goods,
      sku_wholesale_list: formData.sku_wholesale_list
    })
    if (result.code === 10000) {
      ElMessage.success('保存成功')
      dialogVisible.value = false
      emit('complete')
    } else {
      ElMessage.error(result.message || '保存失败')
    }
  } catch (error) {
    console.error(error)
    ElMessage.error('保存失败')
  } finally {
    loading.value = false
  }
}

const openDialog = () => { dialogVisible.value = true }
defineExpose({ openDialog, setDialogData })
</script>

<style scoped>
.el-input-number { width: 120px; }
.el-table { margin-bottom: 20px; }
.el-divider { margin: 20px 0; }
.el-form-item { margin-bottom: 20px; }
</style>
