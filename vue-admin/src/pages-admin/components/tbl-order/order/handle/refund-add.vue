<template>
    <el-dialog v-model="dialogVisible" :title="popTitle" width="900px" :destroy-on-close="true">
        <el-form :model="formData" label-width="100px" ref="formRef" v-loading="loading">
            <el-form-item label="退款类型" prop="order_goods_id">
                <el-radio-group v-model="refundTypeValue" @change="handleRefundTypeChange">
                    <el-radio :value="0">全额退款</el-radio>
                    <el-radio :value="1">单个商品退款</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-divider content-position="left">申请退款金额</el-divider>

            <template v-if="refundTypeValue === 0">
                <el-form-item label="实付金额">
                    <span>{{ orderInfo.pay_amount }}</span>
                </el-form-item>
                <el-form-item label="退款金额" prop="apply_amount">
                    <el-input-number 
                        v-model="formData.apply_amount" 
                        :precision="2" 
                        :step="0.01" 
                        :min="0"
                        :max="orderInfo.pay_amount"
                        placeholder="请输入退款金额" 
                    />
                    <div class="refund-tip">最大可退金额为订单总金额</div>
                </el-form-item>
            </template>

            <template v-else>
                <el-table :data="formData.goods_list" style="width: 100%" border>
                    <el-table-column width="50">
                        <template #default="scope">
                            <el-radio v-model="selectedGoodsIndex" :value="scope.$index"
                                @change="handleGoodsSelect(scope.row, scope.$index)"></el-radio>
                        </template>
                    </el-table-column>
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
                    <el-table-column label="实付金额" width="100">
                        <template #default="scope">
                            <div>{{ scope.row.pay_price }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="goods_num" label="数量" width="80"></el-table-column>
                    <el-table-column label="退款金额" width="200">
                        <template #default="scope">
                            <el-input-number v-model="scope.row.apply_amount" :precision="2" :step="0.01" :min="0"
                                :max="scope.row.pay_price * scope.row.goods_num"
                                :disabled="selectedGoodsIndex !== scope.$index" placeholder="退款金额"
                                @change="calculateTotalAmount" />
                        </template>
                    </el-table-column>
                </el-table>
            </template>

            <el-form-item label="退款说明" prop="refund_explain" style="margin-top: 20px;">
                <el-input v-model="formData.refund_explain" type="textarea" :rows="3" placeholder="请输入退款说明"></el-input>
            </el-form-item>

            <el-form-item label="退款总金额">
                <span class="new-total">{{ formData.apply_amount }}</span>
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
import { ElMessage } from 'element-plus'
import type { FormInstance } from 'element-plus'
import { reactive, ref } from 'vue'
import { toFixed } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getTblOrderInfo } from '@/pages-admin/main/api/tbl-order/tblOrder'
import { addTblOrderRefund } from '@/pages-admin/main/api/tbl-order/tblOrderRefund'
import type { RefundForm, RefundGoodsItem } from '@/pages-admin/main/api/tbl-order/tblOrderRefund.model'

defineProps({
    platform: {
        type: String,
        default: ''
    },
})

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle: string = '订单退款'
const selectedGoodsIndex = ref(-1)
const refundTypeValue = ref(0)

const orderInfo = reactive({
    id: 0,
    pay_amount: 0,
    goods_list: []
})

const formData = reactive<RefundForm>({
    order_id: 0,
    order_goods_id: 0,
    goods_list: [],
    apply_amount: 0,
    refund_explain: ''
})

const formRef = ref<FormInstance>()

const handleRefundTypeChange = (value: number | string | boolean) => {
    if (value === 0) {
        formData.order_goods_id = 0
        formData.apply_amount = orderInfo.pay_amount
        selectedGoodsIndex.value = -1
    } else {
        formData.goods_list.forEach(item => {
            item.apply_amount = 0
        })
        selectedGoodsIndex.value = -1
        formData.order_goods_id = 0
        formData.apply_amount = 0
        calculateTotalAmount()
    }
}

const handleGoodsSelect = (item: RefundGoodsItem, _index: number) => {
    formData.goods_list.forEach(g => {
        g.apply_amount = 0
    })
    item.apply_amount = item.pay_price * item.goods_num
    formData.order_goods_id = item.id
    calculateTotalAmount()
}

const calculateTotalAmount = () => {
    if (refundTypeValue.value === 1 && selectedGoodsIndex.value >= 0) {
        const selectedItem = formData.goods_list[selectedGoodsIndex.value]
        formData.apply_amount = selectedItem ? toFixed(selectedItem.apply_amount) : 0
    } else if (refundTypeValue.value === 1) {
        formData.apply_amount = 0
    }
}

const emit = defineEmits(['complete'])
const handleSubmit = async () => {
    if (loading.value || !formRef.value) return
    if (refundTypeValue.value === 1 && selectedGoodsIndex.value === -1) {
        ElMessage.warning('请选择要退款的商品')
        return
    }
    await formRef.value.validate()
    loading.value = true
    addTblOrderRefund({
        order_id: formData.order_id,
        order_goods_id: formData.order_goods_id,
        apply_amount: formData.apply_amount,
        refund_explain: formData.refund_explain
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
    popTitle = '订单退款'
    selectedGoodsIndex.value = -1
    refundTypeValue.value = 0

    if (row && row.id) {
        try {
            const res = await getTblOrderInfo(row.id)
            const data = res.data
            orderInfo.id = data.id
            orderInfo.pay_amount = data.pay_amount
            formData.order_id = data.id
            formData.order_goods_id = 0
            formData.apply_amount = data.pay_amount
            formData.goods_list = (data.orderGoodsList || []).map((item: Record<string, any>): RefundGoodsItem => ({
                id: item.id,
                goods_name: item.goods_name,
                goods_image: item.goods_image,
                goods_num: item.goods_num ?? 0,
                sku_name: item.sku_name,
                pay_price: item.pay_price ?? 0,
                apply_amount: 0
            }))
        } catch (error) {
            console.error('获取订单信息失败', error)
        }
    }

    loading.value = false
}

defineExpose({
    openDialog: () => {
        dialogVisible.value = true
    },
    setDialogData
})
</script>

<style scoped>
.sku-name {
    font-size: 12px;
    color: #909399;
    margin-top: 5px;
}

.new-total {
    font-size: 18px;
    color: #f56c6c;
    font-weight: bold;
}

.refund-tip {
    margin-top: 5px;
    font-size: 12px;
    color: #909399;
}
</style>
