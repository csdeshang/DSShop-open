<template>
    <view class="profile-container">
        <view class="upload-image-container">
            <ds-attachment-picker v-model="formData.avatar" type="image" :limit="1" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">姓名</text>
            <input class="field-input" v-model="formData.name" placeholder="请输入姓名" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">手机号</text>
            <input class="field-input" v-model="formData.mobile" placeholder="请输入手机号" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">性别</text>
            <view class="field-info">
                <uni-data-checkbox v-model="formData.gender" :localdata="genderRange"></uni-data-checkbox>
            </view>
        </view>

        <view class="ds-field-line">
            <text class="field-name">工作年限</text>
            <input class="field-input" v-model="formData.work_years" placeholder="请输入工作年限" type="number" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">生活照</text>
            <view class="field-info" style="justify-content: flex-start;">
                <ds-attachment-picker v-model="formData.slide_image" type="image" :limit="9" width="150rpx" height="150rpx"/>
            </view>
        </view>


        <view class="ds-field-line">
            <text class="field-name">个人简介</text>
            <textarea class="field-textarea" v-model="formData.description" placeholder="请输入个人简介"></textarea>
        </view>

        <view class="ds-field-line">
            <text class="field-name">资质证书</text>
            <textarea class="field-textarea" v-model="formData.certificate_info" placeholder="请输入资质证书信息"></textarea>
        </view>


        <view class="ml-20 mt-20 mr-20">
            <button @tap="handleUpdate" type="warn" class="submit-btn">提交</button>
        </view>

    </view>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import dsAttachmentPicker from '@/components/ds-attachment/picker.vue'
import useUserStore from '@/stores/user';
import { updateTechnicianInfo, getTechnicianInfo } from '@/technician/api/technician';

const userStore = useUserStore();

// 表单数据
const initialFormData = {
    name: '',
    mobile: '',
    avatar: '',
    slide_image: [],
    gender: 0,
    certificate_info: '',
    work_years: '',
    description: '',
};

const formData = reactive({ ...initialFormData });

const genderRange = ref([{ "value": 0, "text": "保密" }, { "value": 1, "text": "男" }, { "value": 2, "text": "女" }])

const handleUpdate = async () => {
    // 表单验证
    if (!formData.name) {
        uni.showToast({
            title: '请输入姓名',
            icon: 'none'
        });
        return;
    }


    // 显示加载提示
    uni.showLoading({
        title: '正在提交...',
    });


    // 调用更新用户信息的 API
    const response = await updateTechnicianInfo(formData);

    // 检查响应
    if (response.code === 10000) {
        uni.showToast({
            title: '更新成功',
            icon: 'success'
        });
        // 刷新用户信息
        userStore.getUserInfoWithRoles(true)
        // 返回上一页
        uni.navigateBack()
    } else {
        uni.showToast({
            title: response.message || '更新失败',
            icon: 'none'
        });
    }
};

const fetchUserInfo = async () => {
    const res = await getTechnicianInfo()
    if (res.code === 10000) {
        const data = res.data
        Object.keys(formData).forEach((key: string) => {
            if (data[key] != undefined) formData[key] = data[key]
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