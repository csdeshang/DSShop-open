<template>
    <el-drawer v-model="dialogVisible" :title="popTitle" size="80%">
        <el-scrollbar style="height: 100%;">
            <el-form :model="formData" label-width="120px">
                <el-form-item label="商品名称">
                    <span>{{ formData.goods_info?.goods_name }}</span>
                </el-form-item>

                <el-form-item label="是否参与分销">
                    <el-radio-group v-model="formData.goods_info.is_distributor_goods">
                        <el-radio :value="0">不参与</el-radio>
                        <el-radio :value="1">参与</el-radio>
                    </el-radio-group>
                </el-form-item>



                <div v-show="formData.goods_info.is_distributor_goods == 1">
                    <el-form-item label="分销类型">
                        <el-radio-group v-model="formData.goods_info.distributor_goods_type">
                            <el-radio :value="0">默认佣金比例</el-radio>
                            <el-radio :value="1">单独佣金比例</el-radio>
                            <el-radio :value="2">单独佣金金额</el-radio>
                        </el-radio-group>
                        <div class="ml-[20px] text-red-500">
                            单独设置的佣金比例不能小于默认佣金比例
                        </div>

                    </el-form-item>

                    <!-- 批量设置区域 -->
                    <div v-if="formData.goods_info.distributor_goods_type !== 0" class="batch-setting mb-[40px] mt-[20px]">
                        <el-card shadow="never">
                            <template #header>
                                <div class="card-header">
                                    <span>批量设置</span>
                                    <el-button type="primary" @click="handleBatchSetAll">应用到所有规格</el-button>
                                </div>
                            </template>
                            <el-row :gutter="20">
                                <el-col :span="8">
                                    <el-form-item label="自购佣金">
                                        <el-input v-model="batchValues.self" type="number" style="width: 180px">
                                            <template #append
                                                v-if="formData.goods_info.distributor_goods_type === 2">元</template>
                                            <template #append
                                                v-if="formData.goods_info.distributor_goods_type === 1">%</template>
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="8">
                                    <el-form-item label="一级佣金">
                                        <el-input v-model="batchValues.parent1" type="number" style="width: 180px">
                                            <template #append
                                                v-if="formData.goods_info.distributor_goods_type === 2">元</template>
                                            <template #append
                                                v-if="formData.goods_info.distributor_goods_type === 1">%</template>
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                                <el-col :span="8">
                                    <el-form-item label="二级佣金">
                                        <el-input v-model="batchValues.parent2" type="number" style="width: 180px">
                                            <template #append
                                                v-if="formData.goods_info.distributor_goods_type === 2">元</template>
                                            <template #append
                                                v-if="formData.goods_info.distributor_goods_type === 1">%</template>
                                        </el-input>
                                    </el-form-item>
                                </el-col>
                            </el-row>
                        </el-card>
                    </div>

                    <div v-for="sku in formData.sku_distributor_list" :key="sku.sku_id">
                        <el-divider content-position="left">规格：{{ sku.sku_name || '默认规格' }}</el-divider>

                        <el-table :data="sku.distributor_levels" border>
                            <el-table-column label="分销等级" prop="level_name" width="120" />
                            <el-table-column label="分销价格" prop="sku_price" width="120">
                                <template #default>
                                    <span class="text-gray-500">￥{{ sku.sku_price }}</span>
                                </template>
                            </el-table-column>
                            <template v-if="formData.goods_info.distributor_goods_type === 0">
                                <el-table-column label="默认自购比例" prop="base_self_ratio">
                                    <template #default="{ row }">
                                        <el-input v-model="row.base_self_ratio" type="number" style="width: 130px"
                                            disabled>
                                            <template #append>%</template>
                                        </el-input>
                                        <span class="ml-2 text-gray-500">￥{{ ((row.base_parent1_ratio / 100) *
                                            sku.sku_price).toFixed(2) }}</span>
                                    </template>

                                </el-table-column>
                                <el-table-column label="默认一级比例" prop="base_parent1_ratio">
                                    <template #default="{ row }">
                                        <el-input v-model="row.base_parent1_ratio" type="number" style="width: 130px"
                                            disabled>
                                            <template #append>%</template>
                                        </el-input>
                                        <span class="ml-2 text-gray-500">￥{{ ((row.base_parent1_ratio / 100) *
                                            sku.sku_price).toFixed(2) }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="默认二级比例" prop="base_parent2_ratio">
                                    <template #default="{ row }">
                                        <el-input v-model="row.base_parent2_ratio" type="number" style="width: 130px"
                                            disabled>
                                            <template #append>%</template>
                                        </el-input>
                                        <span class="ml-2 text-gray-500">￥{{ ((row.base_parent2_ratio / 100) *
                                            sku.sku_price).toFixed(2) }}</span>
                                    </template>
                                </el-table-column>
                            </template>
                            <template v-if="formData.goods_info.distributor_goods_type === 1">
                                <el-table-column label="自购佣金比例">
                                    <template #default="{ row }">
                                        <el-input v-model="row.goods_self_ratio" type="number" style="width: 130px">
                                            <template #append>%</template>
                                        </el-input>
                                        <span class="ml-2 text-gray-500">￥{{ ((row.goods_self_ratio / 100) *
                                            sku.sku_price).toFixed(2) }}</span>
                                        <span class="text-red-500 ml-[10px]"
                                            v-if="row.goods_self_ratio < row.base_self_ratio">不能低于{{ row.base_self_ratio
                                            }}%</span>
                                        <span class="text-gray-500 ml-[10px]" v-else>(默认佣金{{ (row.base_self_ratio / 100 * sku.sku_price).toFixed(2) }}元)</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="一级佣金比例">
                                    <template #default="{ row }">
                                        <el-input v-model="row.goods_parent1_ratio" type="number" style="width: 130px"
                                            :min="row.base_parent1_ratio">
                                            <template #append>%</template>
                                        </el-input>
                                        <span class="ml-2 text-gray-500">￥{{ ((row.goods_parent1_ratio / 100) *
                                            sku.sku_price).toFixed(2) }}</span>
                                        <span class="text-red-500 ml-[10px]"
                                            v-if="row.goods_parent1_ratio <= row.base_parent1_ratio">不能低于{{
                                            row.base_parent1_ratio }}%</span>
                                        <span class="text-gray-500 ml-[10px]" v-else>(默认佣金{{ (row.base_parent1_ratio / 100 * sku.sku_price).toFixed(2) }}元)</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="二级佣金比例">
                                    <template #default="{ row }">
                                        <el-input v-model="row.goods_parent2_ratio" type="number" style="width: 130px">
                                            <template #append>%</template>
                                        </el-input>
                                        <span class="ml-2 text-gray-500">￥{{ ((row.goods_parent2_ratio / 100) *
                                            sku.sku_price).toFixed(2) }}</span>
                                        <span class="text-red-500 ml-[10px]"
                                            v-if="row.goods_parent2_ratio <= row.base_parent2_ratio">不能低于{{
                                            row.base_parent2_ratio }}%</span>
                                        <span class="text-gray-500 ml-[10px]" v-else>(默认佣金{{ (row.base_parent2_ratio / 100 * sku.sku_price).toFixed(2) }}元)</span>
                     
                                    </template>
                                </el-table-column>
                            </template>
                            <template v-else-if="formData.goods_info.distributor_goods_type === 2">
                                <el-table-column label="自购佣金金额">
                                    <template #default="{ row }">
                                        <el-input v-model="row.goods_self_amount" type="number" style="width: 150px">
                                            <template #append>元</template>
                                        </el-input>
                                        <span class="text-red-500 ml-[10px]"
                                            v-if="row.goods_self_amount <= (row.base_self_ratio / 100 * sku.sku_price)">不能低于{{
                                                (row.base_self_ratio/100 *
                                            sku.sku_price).toFixed(2) }}元</span>
                                        <span class="text-gray-500 ml-[10px]" v-else>(默认佣金{{ (row.base_self_ratio / 100 * sku.sku_price).toFixed(2) }}元)</span>
                               
                                    </template>
                                </el-table-column>
                                <el-table-column label="一级佣金金额">
                                    <template #default="{ row }">
                                        <el-input v-model="row.goods_parent1_amount" type="number" style="width: 150px">
                                            <template #append>元</template>
                                        </el-input>
                                        <span class="text-red-500 ml-[10px]"
                                            v-if="row.goods_parent1_amount <= (row.base_parent1_ratio / 100 * sku.sku_price)">不能低于{{
                                                (row.base_parent1_ratio/100 * sku.sku_price).toFixed(2) }}元</span>
                                        <span class="text-gray-500 ml-[10px]" v-else>(默认佣金{{ (row.base_parent1_ratio / 100 * sku.sku_price).toFixed(2) }}元)</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="二级佣金金额">
                                    <template #default="{ row }">
                                        <el-input v-model="row.goods_parent2_amount" type="number" style="width: 150px">
                                            <template #append>元</template>
                                        </el-input>
                                        <span class="text-red-500 ml-[10px]"
                                            v-if="row.goods_parent2_amount <= (row.base_parent2_ratio / 100 * sku.sku_price)">不能低于{{
                                                (row.base_parent2_ratio/100 * sku.sku_price).toFixed(2) }}元</span>
                                        <span class="text-gray-500 ml-[10px]" v-else>(默认佣金{{ (row.base_parent2_ratio / 100 * sku.sku_price).toFixed(2) }}元)</span>
                                    </template>
                                </el-table-column>
                            </template>
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

<script lang="ts" setup>
import { reactive, ref } from 'vue';
import { getDistributorGoodsInfo, updateDistributorGoods } from '@/pages-admin/main/api/distributor/distributorGoods'
import type { DistributorGoodsForm } from '@/pages-admin/main/api/distributor/distributorGoods.model'
import { ElMessage } from 'element-plus'

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle: string = ''

const formData = reactive<DistributorGoodsForm>({
    goods_info: {
        id: 0,
        goods_name: '',
        distributor_goods_type: 0,
        is_distributor_goods: 0
    },
    sku_distributor_list: []
})

const batchValues = reactive({
    self: 0,
    parent1: 0,
    parent2: 0
})

const emit = defineEmits(['complete'])

const setDialogData = async (row: any = null) => {
    loading.value = true
    popTitle = '参与分销'
    if (row.id) {
        const data = await (await getDistributorGoodsInfo(row.id)).data
        Object.assign(formData, data)
    }
    loading.value = false
}

const handleSubmit = async () => {
    try {
        loading.value = true

        const result = await updateDistributorGoods({
            goods_id: formData.goods_info.id,
            distributor_goods_type: formData.goods_info.distributor_goods_type,
            is_distributor_goods: formData.goods_info.is_distributor_goods,
            sku_distributor_list: formData.sku_distributor_list
        })

        if (result.code === 10000) {
            ElMessage.success('保存成功')
            dialogVisible.value = false
            emit('complete')
        } else {
            ElMessage.error(result.message)
        }
    } catch (error) {
        console.error(error)
        ElMessage.error('保存失败')
    } finally {
        loading.value = false
    }
}

const handleBatchSetAll = () => {
    if (formData.goods_info.distributor_goods_type === 1) {
        formData.sku_distributor_list.forEach((sku: any) => {
            sku.distributor_levels.forEach((level: any) => {
                level.goods_self_ratio = Number(batchValues.self)
                level.goods_parent1_ratio = Number(batchValues.parent1)
                level.goods_parent2_ratio = Number(batchValues.parent2)
            })
        })
    } else if (formData.goods_info.distributor_goods_type === 2) {
        formData.sku_distributor_list.forEach((sku: any) => {
            sku.distributor_levels.forEach((level: any) => {
                level.goods_self_amount = Number(batchValues.self)
                level.goods_parent1_amount = Number(batchValues.parent1)
                level.goods_parent2_amount = Number(batchValues.parent2)
            })
        })
    }
    ElMessage.success('批量设置成功')
}

defineExpose({
    openDialog: () => {
        dialogVisible.value = true
    },
    setDialogData
})
</script>

<style scoped>
.el-input-number {
    width: 180px;
}

.el-divider {
    margin: 20px 0;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.el-card {
    margin-bottom: 20px;
}

.el-form-item {
    margin-bottom: 0;
}
</style>
