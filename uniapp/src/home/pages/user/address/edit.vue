<template>
    <view>
        <view class="ds-field-line">
            <text class="field-name">姓名</text>
            <input class="field-input" v-model="address.real_name" placeholder="请输入姓名" />
        </view>
        <view class="ds-field-line">
            <text class="field-name">电话</text>
            <input class="field-input" v-model="address.mob_phone" placeholder="请输入电话" />
        </view>
        <view class="ds-field-line">
            <text class="field-name">地区</text>
            <text class="field-info" @click="openAreaPicker">{{ address.area_info || '请选择地区' }}</text>
            <uni-icons class="arrow" type="right" size="13"></uni-icons>
        </view>
        <view v-if="lbsConfig.lbs_is_enabled != '1'">
            <view class="ds-field-line">
                <text class="field-name">详细地址</text>
                <input class="field-input" v-model="address.address_detail" placeholder="请输入详细地址" />
            </view>
        </view>
        <view v-else>
            <view class="ds-field-line" @click="openMapSelector(address)">
                <text class="field-name">地址</text>
                <text class="field-info">
                    {{ (address.poi_address && address.poi_name) ? `${address.poi_address} ${address.poi_name}` : '点击选择位置' }}
                </text>
                <uni-icons class="arrow" type="location" size="25" color="#999"></uni-icons>
            </view>
            
            <view class="ds-field-line">
                <text class="field-name">门牌号</text>
                <input class="field-input" v-model="address.house_number" placeholder="如：3号楼2单元501室" />
            </view>

            <ds-map-chooser ref="mapSelector" @confirm="handleMapConfirm" />
        </view>

        <view class="ds-field-line">
            <text class="field-name">设为默认地址</text>
            <view class="field-info">
                <switch @change="switchChange" :checked="!!address.is_default"></switch>
            </view>
        </view>

        <view class="mt-20 mb-20 ml-20 mr-20">
            <button class="ds-btn-lg" type="warn" @click="editAddress">{{ submitTitle }}</button>
        </view>

        <ds-area-picker ref="areaPicker" @confirm="handleAreaConfirm" />

    </view>
</template>


<script setup lang="ts">
import { ref } from 'vue';
import { useConfigStore } from '@/stores/modules/config';
import { onLoad } from '@dcloudio/uni-app';
import { goToPage } from '@/utils/util';
import DsAreaPicker from '@/components/ds-area-picker/ds-area-picker.vue';
import { createUserAddress, updateUserAddress, getUserAddressInfo } from '@/home/api/user/userAddress';
import DsMapChooser from '@/components/ds-map-chooser/index.vue';


const configStore = useConfigStore();
// 获取LBS配置
const lbsConfig = ref<Record<string, string>>({});
async function fetchLbsConfig() {
    lbsConfig.value = await configStore.fetchConfigsByType('lbs');
}


let submitTitle = ref('');

const address = ref({
    id: '',
    real_name: '',
    mob_phone: '',
    province_id: null,
    city_id: null,
    district_id: null,
    area_id: null,
    area_info: '请选择地区',
    poi_address: '',
    poi_name: '',
    house_number: '',
    address_detail: '',
    latitude: null,
    longitude: null,
    is_default: true,
});

// 加载地址数据
const loadAddressData = (id: string) => {
    getUserAddressInfo(Number(id)).then((res) => {
        if (res.code == 10000) {
            address.value = res.data;
        }
    })

};

// 新增与修改地址

const editAddress = () => {
    // 1. 基础字段验证
    if (!address.value.real_name || address.value.real_name.trim() === '') {
        uni.showToast({
            title: '请输入姓名',
            icon: 'none'
        });
        return;
    }

    if (!address.value.mob_phone || address.value.mob_phone.trim() === '') {
        uni.showToast({
            title: '请输入手机号',
            icon: 'none'
        });
        return;
    }

    // 手机号格式验证
    const phoneReg = /^1[3-9]\d{9}$/;
    if (!phoneReg.test(address.value.mob_phone)) {
        uni.showToast({
            title: '请输入正确的手机号',
            icon: 'none'
        });
        return;
    }

    // 地区验证
    if (!address.value.area_info || address.value.area_info === '请选择地区') {
        uni.showToast({
            title: '请选择地区',
            icon: 'none'
        });
        return;
    }

    // 2. 地址详情验证
    if (lbsConfig.value.lbs_is_enabled == '1') {
        // LBS模式：至少需要选择位置
        if (!address.value.poi_address || !address.value.poi_name) {
            uni.showToast({
                title: '请选择位置',
                icon: 'none'
            });
            return;
        }
        
        // 组合详细地址（house_number是可选的）
        let detailParts = [];
        if (address.value.poi_address) detailParts.push(address.value.poi_address);
        if (address.value.poi_name) detailParts.push(address.value.poi_name);
        if (address.value.house_number && address.value.house_number.trim()) {
            detailParts.push(address.value.house_number.trim());
        }
        address.value.address_detail = detailParts.join(' ');
    } else {
        // 传统模式：手动输入详细地址
        if (!address.value.address_detail || address.value.address_detail.trim() === '') {
            uni.showToast({
                title: '请输入详细地址',
                icon: 'none'
            });
            return;
        }
    }

    // 3. 提交数据
    const { id } = address.value;
    const submitPromise = id && id !== '' 
        ? updateUserAddress(address.value)
        : createUserAddress(address.value);
    
    submitPromise.then((res) => {
        if (res.code == 10000) {
            uni.showToast({
                title: submitTitle.value + '成功',
                icon: 'success'
            });
            
            // 延迟跳转，让用户看到成功提示
            setTimeout(() => {
                if (redirectUrl.value) {
                    goToPage({ url: redirectUrl.value });
                } else {
                    goToPage({ url: '/home/pages/user/address/index' });
                }
            }, 1500);
        } else {
            uni.showToast({
                title: res.message,
                icon: 'none'
            });
        }
    }).catch((error) => {
        uni.showToast({
            title: '网络错误，请重试',
            icon: 'none'
        });
    });
}

const areaPicker = ref();
// 打开地区选择器
const openAreaPicker = () => {
    areaPicker.value.open();
};
// 处理地区选择确认
const handleAreaConfirm = (selectedArea: any) => {
    address.value.province_id = selectedArea.province_id;
    address.value.city_id = selectedArea.city_id;
    address.value.district_id = selectedArea.district_id;
    address.value.area_id = selectedArea.area_id;
    address.value.area_info = selectedArea.area_info;
};

// 处理开关切换
const switchChange = (event: any) => {
    address.value.is_default = event.detail.value;
};

// 返回地址
const redirectUrl = ref('');
onLoad((options: any) => {
    address.value.id = options.id ? String(options.id) : '';
    if (address.value.id) {
        loadAddressData(address.value.id);
        submitTitle.value = '修改地址';
    } else {
        submitTitle.value = '添加地址';
    }
    // 带 redirect 参数 返回至下单页
    if (options.redirect) {
        redirectUrl.value = options.redirect;
    }
    fetchLbsConfig();
});

const mapSelector = ref();
// 打开地图选择器
const openMapSelector = (address: any) => {
    mapSelector.value.open({
        latitude: address.latitude || 0,
        longitude: address.longitude || 0
    });
};
// 处理地图选择确认
const handleMapConfirm = (selectedLocation: any) => {
    address.value.poi_address = selectedLocation.address;
    address.value.poi_name = selectedLocation.name;
    address.value.latitude = selectedLocation.latitude;
    address.value.longitude = selectedLocation.longitude;
}

</script>
