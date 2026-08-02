<template>
  <view class="idcard-container">
    <!-- 状态说明：审核中/未通过/已认证，与 idcard-status-block 同级 -->
    <view class="tips-header" v-if="idcardStatus >= 1" :class="{ rejected: idcardStatus === 2 }">
      <text class="tips-status" :class="{ verified: idcardStatus === 3, rejected: idcardStatus === 2 }">
        {{ idcardStatus === 1 ? '审核中' : idcardStatus === 2 ? '未通过' : '已认证' }}
      </text>
      <text class="tips-text" v-if="idcardStatus === 1">您的实名认证正在审核中，请耐心等待。</text>
      <text class="tips-text" v-else-if="idcardStatus === 2">您的实名认证未通过，请修改下方信息后重新提交。</text>
      <text class="tips-text" v-else-if="idcardStatus === 3">您已完成实名认证。</text>
    </view>
    <!-- 已提交内容：仅审核中(1)、已认证(3)时展示；未通过(2)只显示上方提示+下方表单，不重复展示 -->
    <view class="idcard-status-block" v-if="idcardStatus === 1 || idcardStatus === 3">
      <view class="submitted-info" v-if="formData.idcard_name || formData.idcard_number || image1Url || image2Url || image3Url">
        <view class="ds-field-line">
          <text class="field-name">已提交信息</text>
          <text class="field-value"></text>
        </view>
        <view class="ds-field-line">
          <text class="field-name">真实姓名</text>
          <text class="field-value">{{ formData.idcard_name || '-' }}</text>
        </view>
        <view class="ds-field-line">
          <text class="field-name">身份证号</text>
          <text class="field-value">{{ formData.idcard_number || '-' }}</text>
        </view>
        <view class="ds-field-line" v-if="image1Url">
          <text class="field-name">手持身份证照</text>
          <view class="field-info" @click="previewImage(formatImageUrl(image1Url, ThumbnailPresets.medium))">
            <image class="idcard-preview-img" :src="formatImageUrl(image1Url, ThumbnailPresets.medium)" mode="aspectFill" />
          </view>
        </view>
        <view class="ds-field-line" v-if="image2Url">
          <text class="field-name">身份证正面照</text>
          <view class="field-info" @click="previewImage(formatImageUrl(image2Url, ThumbnailPresets.medium))">
            <image class="idcard-preview-img" :src="formatImageUrl(image2Url, ThumbnailPresets.medium)" mode="aspectFill" />
          </view>
        </view>
        <view class="ds-field-line" v-if="image3Url">
          <text class="field-name">身份证反面照</text>
          <view class="field-info" @click="previewImage(formatImageUrl(image3Url, ThumbnailPresets.medium))">
            <image class="idcard-preview-img" :src="formatImageUrl(image3Url, ThumbnailPresets.medium)" mode="aspectFill" />
          </view>
        </view>
      </view>
    </view>

    <!-- 表单：未认证(0)或未通过(2)时可编辑并重新提交 -->
    <template v-if="idcardStatus === 0 || idcardStatus === 2">
      <view class="ds-field-line">
        <text class="field-name">真实姓名</text>
        <input
          class="field-input"
          v-model="formData.idcard_name"
          placeholder="请输入与身份证一致的真实姓名"
        />
      </view>
      <view class="ds-field-line">
        <text class="field-name">身份证号</text>
        <input
          class="field-input"
          v-model="formData.idcard_number"
          type="idcard"
          placeholder="请输入身份证号"
        />
      </view>
      <view class="ds-field-line">
        <text class="field-name">手持身份证照</text>
        <view class="field-info">
          <ds-attachment-picker
            v-model="formData.idcard_image1"
            type="image"
            :limit="1"
            width="200rpx"
            height="200rpx"
          />
        </view>
      </view>
      <view class="ds-field-line">
        <text class="field-name">身份证正面照</text>
        <view class="field-info">
          <ds-attachment-picker
            v-model="formData.idcard_image2"
            type="image"
            :limit="1"
            width="200rpx"
            height="200rpx"
          />
        </view>
      </view>
      <view class="ds-field-line">
        <text class="field-name">身份证反面照</text>
        <view class="field-info">
          <ds-attachment-picker
            v-model="formData.idcard_image3"
            type="image"
            :limit="1"
            width="200rpx"
            height="200rpx"
          />
        </view>
      </view>

      <view class="ml-20 mt-20 mr-20">
        <button type="warn" class="submit-btn" :loading="loading" @click="handleSubmit">提交审核</button>
      </view>
    </template>
  </view>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { getUserInfo, editUserIdcard } from '@/home/api/user/user'
import useUserStore from '@/stores/user'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import dsAttachmentPicker from '@/components/ds-attachment/picker.vue'

const userStore = useUserStore()
const loading = ref(false)
const idcardStatusValue = ref(0)

const formData = reactive({
  idcard_name: '',
  idcard_number: '',
  idcard_image1: '' as string | string[],
  idcard_image2: '' as string | string[],
  idcard_image3: '' as string | string[],
})

const idcardStatus = computed(() => idcardStatusValue.value)

// 图片字段可能是 string 或 string[]（picker），统一为 string
const image1Url = computed(() => (Array.isArray(formData.idcard_image1) ? formData.idcard_image1[0] : formData.idcard_image1) || '')
const image2Url = computed(() => (Array.isArray(formData.idcard_image2) ? formData.idcard_image2[0] : formData.idcard_image2) || '')
const image3Url = computed(() => (Array.isArray(formData.idcard_image3) ? formData.idcard_image3[0] : formData.idcard_image3) || '')

function previewImage(url: string) {
  if (!url) return
  uni.previewImage({ urls: [url] })
}

const validateForm = (): boolean => {
  const errors: string[] = []
  if (!formData.idcard_name?.trim()) errors.push('请输入真实姓名')
  else if (formData.idcard_name.length > 32) errors.push('真实姓名长度不能超过32个字符')
  if (!formData.idcard_number?.trim()) errors.push('请输入身份证号')
  if (!image1Url.value) errors.push('请上传手持身份证照')
  if (!image2Url.value) errors.push('请上传身份证正面照')
  if (!image3Url.value) errors.push('请上传身份证反面照')
  if (errors.length > 0) {
    uni.showToast({ title: errors[0], icon: 'none' })
    return false
  }
  return true
}

const handleSubmit = async () => {
  if (loading.value) return
  if (idcardStatus.value !== 0 && idcardStatus.value !== 2) return
  if (!validateForm()) return

  loading.value = true
  try {
    const res = await editUserIdcard({
      idcard_name: formData.idcard_name.trim(),
      idcard_number: formData.idcard_number.trim(),
      idcard_image1: image1Url.value,
      idcard_image2: image2Url.value,
      idcard_image3: image3Url.value,
    })
    if (res.code === 10000) {
      uni.showToast({ title: '提交成功，请等待审核', icon: 'success' })
      idcardStatusValue.value = 1
      userStore.getUserInfoWithRoles(true)
    } else {
      uni.showToast({ title: res.message || '提交失败', icon: 'none' })
    }
  } catch (e: any) {
    uni.showToast({ title: e.message || '网络错误', icon: 'none' })
  } finally {
    loading.value = false
  }
}

const fetchUserInfo = async () => {
  const res = await getUserInfo()
  if (res.code === 10000) {
    const data = res.data
    idcardStatusValue.value = data.idcard_status != null ? Number(data.idcard_status) : 0
    formData.idcard_name = data.idcard_name || ''
    formData.idcard_number = data.idcard_number || ''
    formData.idcard_image1 = data.idcard_image1 || ''
    formData.idcard_image2 = data.idcard_image2 || ''
    formData.idcard_image3 = data.idcard_image3 || ''
  } else {
    uni.showToast({ title: res.message || '获取信息失败', icon: 'none' })
  }
}

// 页面显示时拉取用户信息（含实名状态），从后台切回或管理员审核后能拿到最新状态
onShow(() => {
  fetchUserInfo()
})
</script>

<style lang="scss" scoped>
.idcard-container {

  min-height: 100vh;
}

.tips-header {
  margin: 20rpx;
  padding: 28rpx 20rpx;
  background: linear-gradient(135deg, #e8f4fd 0%, #d4ebfa 100%);
  &.rejected {
    background: linear-gradient(135deg, #fff2e8 0%, #ffe7d9 100%);
  }
  .tips-status {
    display: inline-block;
    padding: 6rpx 16rpx;
    margin-bottom: 12rpx;
    font-size: 24rpx;
    color: #1890ff;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8rpx;
    font-weight: 500;
    &.verified {
      color: #52c41a;
      background: rgba(82, 196, 26, 0.1);
    }
    &.rejected {
      color: #fa8c16;
      background: rgba(250, 140, 22, 0.1);
    }
  }
  .tips-text {
    display: block;
    font-size: 28rpx;
    color: #333;
    line-height: 1.5;
  }
}

.submitted-info {
  /* 与下方表单一致，无额外 padding，仅 ds-field-line */
  /* 公共无 field-value，只读值右对齐 */
  .field-value {
    flex: 1;
    text-align: right;
  }
  .idcard-preview-img {
    width: 200rpx;
    height: 200rpx;
    border-radius: 12rpx;
    background: #f5f5f5;
    border: 1rpx solid #eee;
  }
}
</style>
