<template>
  <uni-popup ref="popup" type="bottom" style="z-index: 9999;">

    <view style="min-height:40vh;background-color: #fff;">

      <view class="ds-popup-header">
        <view class="cancel" @click="close">取消</view>
        <view class="title">编辑退款</view>
        <view class="confirm" @click="handleSubmit">确定</view>
      </view>

      <view class="mt-50 ml-20 mr-20">
        <uni-forms ref="form" :modelValue="formData">
          <uni-forms-item label="退款金额" name="apply_amount">
            <uni-easyinput v-model="formData.apply_amount" placeholder="请输入退款金额" type="digit" />
          </uni-forms-item>
          <uni-forms-item label="退款说明" name="refund_explain">
            <uni-easyinput v-model="formData.refund_explain" placeholder="请输入退款说明" type="textarea" />
          </uni-forms-item>
        </uni-forms>
      </view>

      <view class="flex justify-between mt-50 ml-20 mr-20">

        <button class="ds-btn-lg" @click="handleSubmit">提交</button>
      </view>


    </view>
  </uni-popup>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { editRefund } from '@/home/api/tbl-order/tblOrderReturn'

const popup = ref()
const form = ref()
const formData = reactive({
  id: '',
  apply_amount: '',
  refund_explain: ''
})

const emit = defineEmits(['success'])

const open = (refundInfo: any) => {
  formData.id = refundInfo.id || ''
  formData.apply_amount = refundInfo.apply_amount || ''
  formData.refund_explain = refundInfo.refund_explain || ''
  popup.value.open()
}

const close = () => {
  popup.value.close()
}

const handleSubmit = async () => {
  try {
    const valid = await form.value.validate()
    if (!valid) return

    uni.showLoading({ title: '处理中...' })
    await editRefund({
      id: formData.id,
      apply_amount: formData.apply_amount,
      refund_explain: formData.refund_explain
    })

    uni.hideLoading()
    uni.showToast({ title: '修改成功', icon: 'success' })
    emit('success')
    close()
  } catch (error) {
    uni.hideLoading()
    uni.showToast({ title: error.message, icon: 'error' })
  }
}

defineExpose({
  open,
  close
})
</script>

<style lang="scss" scoped></style>