<template>
    <el-drawer v-model="dialogVisible" :title="popTitle" size="80%">
        <el-scrollbar style="height: 100%;">
            <el-form :model="formData" label-width="160px">
                <el-form-item label="商品名称">
                    <span>{{ formData.goods_info?.goods_name }}</span>
                </el-form-item>

                <el-form-item label="是否启用会员价">
                    <el-radio-group v-model="formData.goods_info.is_userdiscount_goods">
                        <el-radio :value="0">不启用</el-radio>
                        <el-radio :value="1">启用</el-radio>
                    </el-radio-group>
                </el-form-item>

                <div v-show="formData.goods_info.is_userdiscount_goods == 1">
                    <div v-for="sku in formData.sku_userdiscount_list" :key="sku.sku_id" class="mb-[20px]">
                        <el-divider content-position="left">规格：{{ sku.sku_name || '默认规格' }}</el-divider>
                        <el-table :data="sku.user_levels" border style="width: 100%">
                            <el-table-column label="会员等级" prop="level_name" width="200" />
                            <el-table-column label="会员原价" prop="sku_price" width="150">
                                <template #default>
                                    <span class="text-gray-500">￥{{ sku.sku_price }}</span>
                                </template>
                            </el-table-column>
                            <el-table-column label="会员价格" width="300">
                                <template #default="{ row }">
                                    <el-input-number v-model="row.user_level_price" :min="0" :max="sku.sku_price" :precision="2" style="width: 150px" />
                                    <span class="ml-2 text-gray-500" v-if="row.user_level_price > 0">
                                        折扣率: {{ ((row.user_level_price / sku.sku_price) * 100).toFixed(1) }}%
                                    </span>
                                </template>
                            </el-table-column>
                            <el-table-column label="折扣状态" min-width="200">
                                <template #default="{ row }">
                                    <span class="text-green-500" v-if="row.user_level_price > 0 && row.user_level_price < sku.sku_price">优惠生效</span>
                                    <span class="text-red-500" v-else-if="row.user_level_price >= sku.sku_price">会员价必须低于原价</span>
                                    <span class="text-gray-400" v-else-if="row.user_level_price === 0">未设置</span>
                                </template>
                            </el-table-column>
                        </el-table>
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
export default defineComponent({ name: 'GoodsUserdiscountEdit' })
</script>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { getTblGoodsUserdiscountInfo, updateTblGoodsUserdiscount } from '@/pages-admin/main/api/tbl-marketing/tblGoodsUserdiscount'

interface GoodsInfo { id: number; goods_name: string; is_userdiscount_goods: number }
interface UserLevel { user_level_id: number; level_name: string; min_growth: number; user_level_price: number }
interface SkuUserdiscount { sku_id: number; sku_name: string; sku_price: number; user_levels: UserLevel[] }
interface FormData { goods_info: GoodsInfo; sku_userdiscount_list: SkuUserdiscount[] }

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle = '设置会员价格'
const initialFormData: FormData = { goods_info: { id: 0, goods_name: '', is_userdiscount_goods: 0 }, sku_userdiscount_list: [] }
const formData = reactive<FormData>({ ...initialFormData })
const emit = defineEmits(['complete'])

const setDialogData = async (row: any = {}) => {
  loading.value = true
  popTitle = '设置会员价格'
  Object.assign(formData, JSON.parse(JSON.stringify(initialFormData)))
  if (row.id) {
    const data = (await getTblGoodsUserdiscountInfo(row.id)).data
    Object.assign(formData, data)
  }
  loading.value = false
}

const handleSubmit = async () => {
  if (loading.value) return
  if (formData.goods_info.is_userdiscount_goods === 0) { submitData(); return }
  for (const sku of formData.sku_userdiscount_list) {
    for (const level of sku.user_levels) {
      if (level.user_level_price > 0 && level.user_level_price >= sku.sku_price) {
        ElMessage.warning(`会员等级"${level.level_name}"的价格必须低于原价`)
        return
      }
    }
  }
  submitData()
}

const submitData = async () => {
  try {
    loading.value = true
    const result = await updateTblGoodsUserdiscount({
      goods_id: formData.goods_info.id,
      is_userdiscount_goods: formData.goods_info.is_userdiscount_goods,
      sku_userdiscount_list: formData.sku_userdiscount_list
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
