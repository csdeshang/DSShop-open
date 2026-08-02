<template>
  <el-drawer v-model="dialogVisible" :title="popTitle" size="60%" :destroy-on-close="true">
    <el-form ref="formRef" :model="formData" :rules="formRules" label-width="120px" class="p-4" v-loading="loading">
      <el-divider content-position="left">基本信息</el-divider>

      <el-form-item label="优惠券名称" prop="coupon_name">
        <el-input v-model="formData.coupon_name" placeholder="请输入优惠券名称" maxlength="32" show-word-limit />
      </el-form-item>

      <el-form-item label="优惠券类型" prop="coupon_type">
        <el-radio-group v-model="formData.coupon_type">
          <el-radio v-for="option in coupon_type_options" :key="option.value" :value="option.value">{{
            option.label
          }}</el-radio>
        </el-radio-group>
      </el-form-item>

      <el-form-item :label="couponTypeLabel" prop="discount_value">
        <el-input-number
          v-model="formData.discount_value"
          :min="0.1"
          :precision="formData.coupon_type === 2 ? 1 : 2"
          :max="formData.coupon_type === 2 ? 9.9 : 99999"
          :step="formData.coupon_type === 2 ? 0.1 : 1"
          :placeholder="couponTypePlaceholder"
        />
        <span class="ml-2 text-gray-500" v-if="formData.coupon_type === 2">折</span>
        <span class="ml-2 text-gray-500" v-else>元</span>
      </el-form-item>

      <el-form-item label="最低消费金额" prop="min_spend">
        <el-input-number v-model="formData.min_spend" :min="0" :precision="2" placeholder="请输入最低消费金额" />
        <span class="ml-2 text-gray-500">元</span>
      </el-form-item>

      <el-divider content-position="left">有效期设置</el-divider>

      <el-form-item label="开始时间" prop="start_time">
        <el-date-picker v-model="formData.start_time" type="datetime" placeholder="选择开始时间" />
      </el-form-item>

      <el-form-item label="结束时间" prop="end_time">
        <el-date-picker v-model="formData.end_time" type="datetime" placeholder="选择结束时间" />
      </el-form-item>

      <el-divider content-position="left">发放设置</el-divider>

      <el-form-item label="发放类型" prop="claim_type">
        <el-radio-group v-model="formData.claim_type">
          <el-radio v-for="option in claim_type_options" :key="option.value" :value="option.value">{{
            option.label
          }}</el-radio>
        </el-radio-group>
      </el-form-item>

      <el-form-item label="总库存数量" prop="total_stock">
        <el-input-number v-model="formData.total_stock" :min="1" :precision="0" placeholder="请输入总库存数量" />
      </el-form-item>

      <el-form-item label="每人限领" prop="limit_per_user">
        <el-input-number v-model="formData.limit_per_user" :min="1" :precision="0" placeholder="请输入每人限领数量" />
        <span class="ml-2 text-gray-500">张</span>
      </el-form-item>

      <el-form-item label="所需积分" prop="points_required">
        <el-input-number v-model="formData.points_required" :min="0" :precision="0" placeholder="请输入所需积分" />
        <span class="ml-2 text-gray-500">积分（0表示无需积分）</span>
      </el-form-item>

      <el-divider content-position="left">其他设置</el-divider>

      <el-form-item label="排序" prop="sort">
        <el-input-number v-model="formData.sort" :min="0" :precision="0" placeholder="请输入排序值" />
        <span class="ml-2 text-gray-500">数值越大越靠前</span>
      </el-form-item>

      <el-form-item label="状态" prop="status">
        <el-switch
          v-model="formData.status"
          :active-value="1"
          :inactive-value="0"
          active-text="启用"
          inactive-text="禁用"
        />
      </el-form-item>
    </el-form>

    <template #footer>
      <div class="flex justify-end">
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="loading">确认</el-button>
      </div>
    </template>
  </el-drawer>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue'

import { ElMessage, type FormInstance } from 'element-plus'

import { useEnum } from '@/hooks/useEnum'
import {
  createTblStoreCoupon,
  getTblStoreCouponInfo,
  updateTblStoreCoupon
} from '@/pages-admin/main/api/tbl-store/tblStoreCoupon'

const { options: coupon_type_options } = useEnum('default.tbl_store_coupon.coupon_type')
const { options: claim_type_options } = useEnum('default.tbl_store_coupon.claim_type')

const formRef = ref<FormInstance>()
const dialogVisible = ref(false)
const loading = ref(false)
let popTitle = ''

const initialFormData = {
  id: '',
  coupon_name: '',
  coupon_type: 1,
  discount_value: 0,
  min_spend: 0,
  start_time: 0,
  end_time: 0,
  total_stock: 100,
  claimed_stock: 0,
  available_stock: 100,
  limit_per_user: 1,
  claim_type: 1,
  points_required: 0,
  sort: 0,
  status: 1
}

const formData: Record<string, any> = reactive({ ...initialFormData })

const formRules = {
  coupon_name: [
    { required: true, message: '请输入优惠券名称', trigger: 'blur' },
    { max: 32, message: '优惠券名称不能超过32个字符', trigger: 'blur' }
  ],
  coupon_type: [{ required: true, message: '请选择优惠券类型', trigger: 'change' }],
  discount_value: [{ required: true, message: '请输入优惠金额/折扣', trigger: 'blur' }],
  min_spend: [{ required: true, message: '请输入最低消费金额', trigger: 'blur' }],
  start_time: [{ required: true, message: '请选择开始时间', trigger: 'change' }],
  end_time: [{ required: true, message: '请选择结束时间', trigger: 'change' }],
  total_stock: [{ required: true, message: '请输入总库存数量', trigger: 'blur' }],
  limit_per_user: [{ required: true, message: '请输入每人限领数量', trigger: 'blur' }]
}

const couponTypeLabel = computed(() => {
  switch (formData.coupon_type) {
    case 1:
      return '直减金额'
    case 2:
      return '折扣比例'
    default:
      return '优惠金额'
  }
})

const couponTypePlaceholder = computed(() => {
  switch (formData.coupon_type) {
    case 1:
      return '请输入直减金额'
    case 2:
      return '请输入折扣比例'
    default:
      return '请输入优惠金额'
  }
})

const setDialogData = async (row: any = {}) => {
  Object.assign(formData, initialFormData)

  if (row.id) {
    popTitle = '编辑优惠券'
    loading.value = true

    const res = await getTblStoreCouponInfo(row.id)
    const couponData = res.data

    Object.keys(formData).forEach((key) => {
      if (key in couponData) {
        formData[key] = couponData[key]
      }
    })

    loading.value = false
  } else {
    popTitle = '添加优惠券'
  }
}

const openDialog = () => {
  dialogVisible.value = true
}

const handleSubmit = async () => {
  await formRef.value?.validate(async (valid) => {
    if (!valid) return

    if (!formData.start_time || !formData.end_time) {
      ElMessage.warning('请选择有效期')
      return
    }

    if (formData.coupon_type === 2 && (formData.discount_value <= 0 || formData.discount_value >= 10)) {
      ElMessage.warning('折扣比例必须在0.1-9.9之间')
      return
    }

    loading.value = true
    formData.available_stock = formData.total_stock - formData.claimed_stock

    const requestFun = formData.id ? updateTblStoreCoupon : createTblStoreCoupon

    requestFun(formData)
      .then(() => {
        loading.value = false
        dialogVisible.value = false
        emit('complete')
      })
      .catch(() => {
        loading.value = false
      })
  })
}

const emit = defineEmits(['complete'])

defineExpose({
  openDialog,
  setDialogData
})
</script>

<style scoped>
.el-divider {
  margin: 16px 0;
}
</style>
