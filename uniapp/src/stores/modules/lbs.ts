import { defineStore } from 'pinia';
import { getAddressByLngLat  } from '@/api/system/sysLbs';
import { getCurrentPosition } from '@/utils/location';

interface LbsState {
    // 已选择的城市
    selectedCity: string;
    // 纬度
    latitude: number | null;
    // 经度
    longitude: number | null;
    // 省
    province: string;
    // 市
    city: string;
    // 区划代码
    adcode: string;
    // 城市代码
    citycode: string;
    // 区
    district: string;
    // 街道
    street: string;
    //地址名称
    name: string;
    // 地址
    address: string;
    // 来源
    source: string;
    // 时间戳
    timestamp: number;
    // 是否加载中
    isLoading: boolean;

}

export const useLbsStore = defineStore('lbs', {
    state: (): LbsState => ({
        selectedCity: '',
        latitude: null,
        longitude: null,
        province: '',
        city: '',
        adcode: '',
        citycode: '',
        district: '',
        street: '',
        name: '',
        address: '',
        source: '',
        timestamp: 0,
        isLoading: false,
    }),

    getters: {
        // 是否有位置信息
        hasLocation: (state) => state.latitude !== null && state.longitude !== null && state.latitude !== 0 && state.longitude !== 0,

        // 位置信息是否过期 (超过30分钟)
        isLocationExpired: (state) => {
            return Date.now() - state.timestamp > 30 * 60 * 1000;
        },

        // 位置坐标字符串
        locationString: (state) => {
            if (!state.latitude || !state.longitude) return '';
            return `${state.latitude},${state.longitude}`;
        }
    },

    actions: {
        // 保存到本地存储
        saveToStorage() {
            try {
                uni.setStorageSync('app_location', JSON.stringify({
                    selectedCity: this.selectedCity,
                    latitude: this.latitude,
                    longitude: this.longitude,
                    province: this.province,
                    city: this.city,
                    adcode: this.adcode,
                    citycode: this.citycode,
                    district: this.district,
                    street: this.street,
                    name: this.name || this.address,
                    address: this.address,
                    source: this.source,
                    timestamp: this.timestamp
                }));
            } catch (e) {
                console.error('保存位置信息失败', e);
            }
        },

        // 更新位置信息
        async updateLocation() {
            if (this.isLoading) return;

            this.isLoading = true;

            try {
                // 获取位置信息(只有经纬度)
                const position = await getCurrentPosition();

                // 根据经纬度 获取地址详细信息
                const address = await getAddressByLngLat({
                    latitude: position.latitude,
                    longitude: position.longitude
                });

                if (address.code !== 10000) {
                    throw new Error('获取地址失败');
                }

                // 更新位置信息
                this.selectedCity = address.data.city;
                this.latitude = position.latitude;
                this.longitude = position.longitude;
                this.province = address.data.province;
                this.city = address.data.city;
                this.adcode = address.data.adcode;
                this.citycode = address.data.citycode;
                this.district = address.data.district;
                this.street = address.data.street;
                this.name = address.data.name || address.data.address;
                this.address = address.data.address || '';
                this.source = 'location';
                this.timestamp = Date.now();

                this.saveToStorage();
                return position;
            } catch (error) {
                console.error('获取位置失败', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // 清除位置信息
        clearLocation() {
            this.selectedCity = '';
            this.latitude = null;
            this.longitude = null;
            this.province = '';
            this.city = '';
            this.adcode = '';
            this.citycode = '';
            this.district = '';
            this.street = '';
            this.address = '';
            this.source = '';
            this.timestamp = 0;

            try {
                uni.removeStorageSync('app_location');
            } catch (e) {
                console.error('清除位置信息失败', e);
            }
        },

        // 获取位置信息 (默认过期时间7天)
        async getLocation(useCache = true, expires = 3600 * 24 * 7) {
            
            // 如果不使用缓存，直接更新位置
            if (!useCache) {
                await this.updateLocation();
                return;
            }

            try {
                // 先检查store状态是否有数据
                if (!this.hasLocation) {
                    // store没有数据，尝试从本地存储获取
                    const locationData = uni.getStorageSync('app_location');
                    if (locationData) {
                        const parsed = JSON.parse(locationData);
                        // 先更新store状态
                        Object.assign(this, parsed);
                    }
                }

                // 判断是否需要更新位置
                if (this.hasLocation) {
                    // 检查是否过期
                    const isExpired = Date.now() - this.timestamp > expires * 1000;
                    if (!isExpired) {
                        // 未过期，直接返回
                        return;
                    }
                }
                
                // 如果没有位置信息或已过期，获取新位置
                await this.updateLocation();
            } catch (error) {
                console.error('获取位置信息失败', error);
                await this.updateLocation();
            }
        },

        // 设置位置信息（用于手动设置位置，如地图选点、城市选择等）
        setLocation(location: any) {
            this.selectedCity = location.city;
            this.latitude = location.latitude;
            this.longitude = location.longitude;
            this.province = location.province;
            this.city = location.city;
            this.adcode = location.adcode;
            this.citycode = location.citycode;
            this.district = location.district;
            this.street = location.street;
            this.name = location.name || location.address;
            this.address = location.address;
            this.source = location.source || 'manual'; // 手动设置的来源标记为 manual
            this.timestamp = Date.now();


            // 保存到本地存储
            this.saveToStorage();
        },
        // 设置已选择的城市
        setSelectedCity(city: string) {
            this.selectedCity = city;
            // this.saveToStorage();
        }
    }
});