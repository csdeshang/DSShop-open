<template>

  <view class="profile-container">


    <view class="upload-image-container">
      <ds-attachment-picker v-model="formData.avatar" type="image" :limit="1" />
    </view>


    <view class="ds-field-line">
      <text class="field-name">昵称</text>
      <input class="field-input" v-model="formData.nickname" placeholder="请输入昵称" />
    </view>

    <view class="ds-field-line">
      <text class="field-name">性别</text>
      <view class="field-info">
        <uni-data-checkbox v-model="formData.sex" :localdata="sexRange"></uni-data-checkbox>
      </view>
    </view>

    <view class="ds-field-line">
      <text class="field-name">生日</text>
      <view class="field-info">
        <uni-datetime-picker type="date" return-type="timestamp" v-model="formData.birthday" :border="false" />
      </view>
    </view>


    <view class="ml-20 mt-20 mr-20">
      <button @tap="handleUpdate" type="warn" class="submit-btn">提交</button>
    </view>

  </view>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import useUserStore from '@/stores/user'
import dsAttachmentPicker from '@/components/ds-attachment/picker.vue'
import { updateUserInfo, getUserInfo } from '@/home/api/user/user'
const userStore = useUserStore()


// 表单数据
const initialFormData = {
  avatar: '',
  nickname: '',
  sex: 0,
  birthday: '',
}
const formData: Record<string, any> = reactive({ ...initialFormData })

const sexRange = ref([{ "value": 0, "text": "男" }, { "value": 1, "text": "女" }])

const handleUpdate = async () => {
  // 表单验证
  if (!formData.nickname) {
    uni.showToast({
      title: '请输入昵称',
      icon: 'none'
    });
    return;
  }

  // 显示加载提示
  uni.showLoading({
    title: '正在提交...',
  });


  // 调用更新用户信息的 API
  const response = await updateUserInfo(formData);

  // 检查响应
  if (response.code === 10000) {
    uni.showToast({
      title: '更新成功',
      icon: 'success'
    });
    // 刷新用户信息
    userStore.getUserInfoWithRoles(true)
  } else {
    uni.showToast({
      title: response.message || '更新失败',
      icon: 'none'
    });
  }
};


const fetchUserInfo = async () => {
  const res = await getUserInfo()
  if (res.code === 10000) {
    const data = res.data
    Object.keys(formData).forEach((key: string) => {
      if (data[key] != undefined) {
        // 特殊处理生日字段，将日期字符串转换为时间戳
        if (key === 'birthday' && data[key]) {
          // 如果是日期字符串，转换为时间戳
          if (typeof data[key] === 'string') {
            formData[key] = new Date(data[key]).getTime()
          } else {
            formData[key] = data[key]
          }
        } else {
          formData[key] = data[key]
        }
      }
    })
  } else {
    uni.showToast({
      title: res.message || '获取信息失败',
      icon: 'none'
    });
  }
}



// 页面显示时也刷新用户信息
onShow(() => {
  fetchUserInfo()
})
</script>

<style lang="scss" scoped>
.profile-container {
  background-color: #fff;
  min-height: 100vh;
}
.upload-image-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  /* 背景颜色 */
  padding: 50rpx 20rpx;
  /* 内边距 */
}

.uni-date-x--border {
  border: none;

}
</style>