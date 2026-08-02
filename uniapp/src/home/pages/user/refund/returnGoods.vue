<template>
  <uni-popup ref="popup" type="bottom" style="z-index: 9999;">

    <view style="min-height:40vh;">
      <view class="ds-popup-header">
        <view class="cancel" @click="close">取消</view>
        <view class="title">填写退货物流</view>
        <view class="confirm" @click="handleSubmit">确定</view>
      </view>


      <view class="mt-50 ml-20 mr-20">


        <uni-forms ref="form" :modelValue="formData">
          <uni-forms-item label="快递公司" name="express_company" required label-width="150rpx">
            <uni-data-select v-model="formData.express_company" :localdata="companyOptions"
              placeholder="请选择快递公司"></uni-data-select>
          </uni-forms-item>

          <uni-forms-item label="快递单号" name="express_number" required label-width="150rpx">
            <uni-easyinput v-model="formData.express_number" placeholder="请输入快递单号" />
          </uni-forms-item>
        </uni-forms>

        <view class="flex justify-between mt-50">
          <button class="ds-btn-lg ds-btn-orange" @click="handleSubmit">提交</button>
        </view>
      </view>
    </view>

  </uni-popup>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { returnGoods } from '@/home/api/tbl-order/tblOrderReturn'


const popup = ref()
const form = ref()
const formData = reactive({
  id: '',
  express_company: '',
  express_number: ''
})

const companyOptions = [
  { value: 'SF', text: '顺丰速运' },
  { value: 'STO', text: '申通快递' },
  { value: 'YTO', text: '圆通速递' },
  { value: 'ZTO', text: '中通快递' },
  { value: 'YD', text: '韵达快递' },
  { value: 'EMS', text: 'EMS' },
  { value: 'OTHER', text: '其他快递' }
]

const open = (returnInfo: any) => {
  formData.id = returnInfo.id
  formData.express_company = ''
  formData.express_number = ''
  popup.value.open()
}

const close = () => {
  popup.value.close()
}

const handleSubmit = async () => {
  try {
    const valid = await form.value.validate()
    if (!valid) return

    uni.showLoading({ title: '提交中...' })
    await returnGoods({
      id: formData.id,
      express_company: formData.express_company,
      express_number: formData.express_number
    })

    uni.hideLoading()
    uni.showToast({ title: '提交成功', icon: 'success' })
    close()
  } catch (error) {
    uni.hideLoading()
    uni.showToast({ title: '提交失败', icon: 'error' })
  }
}

defineExpose({
  open,
  close
})
</script>

<style lang="scss" scoped></style>