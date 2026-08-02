<template>
    <view>
        <uni-popup ref="popupRef" type="bottom">
            <scroll-view scroll-y="true" style="height: 100vh;">
                <view style="min-height:100vh;background-color: #fff;padding-bottom:160rpx;">
                    <uni-nav-bar title="选择地址" left-icon="back" @clickLeft="close" :border="false"></uni-nav-bar>

                    <view class="search-wrap">
                        <view class="city-info" @click="openCitySelector()">
                            <view class="city-name">{{ lbsStore.selectedCity }}</view><uni-icons class="ico" type="down"
                                size="16"></uni-icons>
                        </view>
                        <view class="city-search">
                            <uni-search-bar placeholder="请输入你的地址" cancelButton="none" :focus="false" v-model="keyword"
                                @input="searchAddress" @clear="clearKeyword"></uni-search-bar>
                        </view>
                    </view>

                    <view class="result-list" v-if="current == 'nearby'">
                        <view class="current-address">
                            <view class="notice">
                                <view>当前地址</view>
                                <view class="reset" @click="getPosition"><uni-icons type="location" size="20"></uni-icons>重新定位</view>
                            </view>
                            <view class="item" style="border-bottom:0">
                                {{ lbsStore.address }}
                            </view>
                        </view>

                        <view v-if="user_address_list && user_address_list.length > 0" class="mt-5">
                            <view class="notice">
                                <view>我的地址</view>
                            </view>
                            <view class="common-address-list">
                                <view class="item" v-for="(item, index) in user_address_list" :key="index"
                                    @click="selectUserAddress(item)">
                                    <view class="content">
                                        <view class="name">{{ item.address_detail }}</view>
                                        <view class="address">
                                            <view>{{ item.real_name }}</view>
                                            <view class="ml-10">{{ item.mob_phone }}</view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>

                        <view class="notice mt-5">附近地址</view>
                        <view class="common-address-list">
                            <view class="item" v-for="(item, index) in nearby_address_list" :key="index"
                                @click="selectPosition(item)">
                                <view class="content">
                                    <view class="name">{{ item.name }}</view>
                                    <view class="address">{{ item.address }}</view>
                                </view>
                                <view class="distance" v-if="item.distance">{{ item.distance }}km</view>
                            </view>
                        </view>
                    </view>

                    <view class="result-list" v-if="current == 'search'">
                        <view class="common-address-list">
                            <view class="item" v-for="(item, index) in search_address_list" :key="index"
                                @click="selectPosition(item)">
                                <view class="content">
                                    <view class="name">{{ item.name }}</view>
                                    <view class="address">{{ item.address }}</view>
                                </view>
                                <view class="distance" v-if="item.distance">{{ item.distance }}km</view>
                            </view>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </uni-popup>
        <SelectCity ref="citySelectorRef" />
    </view>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useLbsStore } from '@/stores/modules/lbs';
import useUserStore from '@/stores/user';
import SelectCity from './select-city.vue';
import { getAroundAddressList, getCityAddressList } from '@/api/system/sysLbs';
import { debounce } from '@/utils/util';
import { getUserAddressList } from '@/home/api/user/userAddress';

// 定义接口类型 - 根据数据库表结构 ds_user_address
interface UserAddress {
    id: number;
    user_id: number;
    real_name: string;           // 会员姓名
    mob_phone: string;           // 手机号
    province_id?: number;        // 省级ID
    city_id?: number;           // 市级ID
    district_id: number;        // 区县ID
    area_id: number;            // 地区ID
    area_info: string;          // 地区信息
    poi_address?: string;       // POI地址
    poi_name?: string;          // POI名称
    house_number?: string;      // 门牌号
    address_detail: string;     // 详细地址
    is_default: number;         // 是否默认地址 1=默认
    longitude?: string;         // 经度
    latitude?: string;          // 纬度
    create_at: number;          // 创建时间
    update_at: number;          // 更新时间
}

interface LocationItem {
    name: string;
    address: string;
    latitude: number;
    longitude: number;
    distance?: number;
    province?: string;
    city?: string;
    adcode?: string;
    citycode?: string;
    district?: string;
    street?: string;
}

const lbsStore = useLbsStore();
const userStore = useUserStore();

const popupRef = ref();
const keyword = ref('');
const current = ref('nearby');
const user_address_list = ref<UserAddress[]>([]);
const nearby_address_list = ref<LocationItem[]>([]);
const search_address_list = ref<LocationItem[]>([]);

const emit = defineEmits(['confirm', 'complete']);

// 带防抖的搜索函数实现
const doSearchAddress = (value: string) => {
    if (!value) {
        return;
    }

    current.value = 'search';

    const params = {
        latitude: lbsStore.latitude,
        longitude: lbsStore.longitude,
        keyword: value,
        city: lbsStore.selectedCity
    }
    getCityAddressList(params).then((res: any) => {
        search_address_list.value = res.data;
    });
}

// 使用防抖包装搜索函数
const searchAddress = debounce(doSearchAddress, 500);

// 清空搜索关键词
const clearKeyword = () => {
    keyword.value = '';
    current.value = 'nearby';
    search_address_list.value = [];
}

// 重新定位
const getPosition = async () => {
    await lbsStore.updateLocation();
    fetchAroundAddressList();
}

// 选择位置（附近地址和搜索结果）
const selectPosition = (item: LocationItem) => {
    const location = {
        selectedCity: item.city,
        latitude: item.latitude,
        longitude: item.longitude,
        province: item.province,
        city: item.city,
        adcode: item.adcode,
        citycode: item.citycode,
        district: item.district,
        street: item.street,
        name: item.name,
        address: item.address,
    }
    lbsStore.setLocation(location);
    emit('confirm', location);
    clearKeyword();
    close();
}

// 选择用户地址
const selectUserAddress = (item: UserAddress) => {
    const location = {
        selectedCity: '', // 从用户地址中可能没有城市信息，需要根据经纬度获取
        latitude: parseFloat(item.latitude || '0'),
        longitude: parseFloat(item.longitude || '0'),
        province: '',
        city: '',
        adcode: '',
        citycode: '',
        district: '',
        street: '',
        name: item.poi_name || item.address_detail,
        address: item.poi_address || item.address_detail,
    }
    lbsStore.setLocation(location);
    emit('confirm', location);
    clearKeyword();
    close();
}

// 城市选择器
const citySelectorRef = ref();
const openCitySelector = () => {
    citySelectorRef.value.open();
};

// 获取用户地址列表
const fetchUserAddressList = async () => {
    // 判断是否登录
    if (!userStore.isLogin) {
        return;
    }

    try {
        const res = await getUserAddressList();
        if (res.code === 10000) {
            user_address_list.value = res.data || [];
        } else {
            console.error('获取用户地址失败:', res.message);
        }
    } catch (error) {
        console.error('获取用户地址异常:', error);
    }
}

// 打开弹窗
const open = () => {
    fetchUserAddressList();
    fetchAroundAddressList();
    popupRef.value.open();
};

// 取消
const close = () => {
    popupRef.value.close();
};

// 获取当前坐标附近的地址
const fetchAroundAddressList = async () => {
    if (!lbsStore.latitude || !lbsStore.longitude) {
        console.log('没有位置信息，跳过获取附近地址');
        return;
    }

    try {
        const params = {
            latitude: lbsStore.latitude,
            longitude: lbsStore.longitude,
        }

        const res = await getAroundAddressList(params);
        if (res.code === 10000) {
            nearby_address_list.value = res.data || [];
        } else {
            console.error('获取附近地址失败:', res.message);
        }
    } catch (error) {
        console.error('获取附近地址异常:', error);
    }
}

defineExpose({
    open,
    close
});
</script>

<style lang="scss" scoped>
.search-wrap {
    width: 100%;
    height: 60px;
    // position: fixed;
    z-index: 999;
    background: #fff;
    display: flex;

    .city-info {
        padding-left: 10px;
        float: left;
        line-height: 60px;
        width: 25%;
        font-size: 16px;

        .city-name {
            float: left;
            margin-right: 5px;
            max-width: 70px;
            text-align: center;
            height: 60px;
            line-height: 60px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    }

    .city-search {
        width: 75%
    }
}

.result-list {
    // padding-top: 60px;

    .notice {
        background: #fff;
        padding: 0 10px;
        font-size: $ds-font-md;
        color: #aeaeae;
        line-height: 40px;
        display: flex;
        justify-content: space-between;
    }

    .reset {
        color: $ds-primary-color;
    }
}

.current-address {
    background: #fff;
    box-shadow: 0 4px 4px #f7f7f7;
}

.current-address .item {
    padding: 14px 0;
    font-size: 16px;
    color: #333;
    border-bottom: 1px dashed #eee;
    margin: 0 24rpx;
}

.common-address-list {
    background: #fff;
}

.common-address-list .item {
    position: relative;
    display: flex;
    justify-content: space-between;
    padding: 15px;
    border-bottom: 1px dashed #eee;
}

.common-address-list .item .content {
    flex-wrap: wrap;
    display: flex;
    flex-direction: column;
}

.common-address-list .item .name {
    font-size: 16px;
    line-height: 30px;
}

.common-address-list .item .address {
    font-size: 12px;
    color: #999;
    line-height: 20px;
    display: flex;
}

.common-address-list .item .distance {
    width: 60px;
    text-align: right;
    font-size: 12px;
    color: #999;
}
</style>
