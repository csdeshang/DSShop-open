<template>
    <uni-popup ref="popupRef" type="bottom">
        <view style="min-height:100vh;background-color: #fff;">
            <uni-nav-bar title="选择城市" left-icon="back" @clickLeft="close" :border="false"></uni-nav-bar>

            <view class="search-city">
                <uni-search-bar placeholder="请输入城市名" cancelButton="none" :focus="true" v-model="city_keyword"
                    @input="searchInput" @clear="clear"></uni-search-bar>
            </view>
            <view class="search-list" v-if="city_keyword != ''">
                <view class="item" v-for="search_city in search_city_list" :key="search_city"
                    @click="confirmCity(search_city)">
                    {{ search_city }}
                </view>
            </view>
            <view class="hotcity" v-if="city_keyword == ''">
                <view class="mt">热门城市</view>
                <view class="mc">
                    <view class="item" v-for="hot_city in hot_city_list" :key="hot_city" @click="confirmCity(hot_city)">
                        {{ hot_city }}
                    </view>
                </view>
            </view>
            <view class="city-list" v-if="city_keyword == ''">
                <uni-indexed-list :options="city_list" :showSelect="false" @click="bindClickCity"></uni-indexed-list>
            </view>


        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useLbsStore } from '@/stores/modules/lbs';
import { getCityList, getCityListByKeyword } from '@/api/system/sysLbs';


const popupRef = ref();
const city_keyword = ref('');
const search_city_list = ref([]);
const hot_city_list = ref([]);
const city_list = ref([]);

const lbsStore = useLbsStore();

const searchInput = (e: any) => {
    city_keyword.value = e;
    getCityListByKeyword({ keyword: e }).then((res: any) => {
        search_city_list.value = res.data.search_city_list;
    });
}

//获取城市列表
const fetchCityList = async () => {
    const res = await getCityList();
    city_list.value = res.data.city_list;
    hot_city_list.value = res.data.hot_city_list;
}


// 设置选择城市
const confirmCity = (city: string) => {
    lbsStore.setSelectedCity(city);
    close();
}

// 点击城市
const bindClickCity = (e: any) => {

    confirmCity(e.item.name);
}

const clear = () => {
    city_keyword.value = '';
    search_city_list.value = [];
}

// 打开弹窗
const open = () => {
    popupRef.value.open();
    fetchCityList();

};
// 取消
const close = () => {
    popupRef.value.close();
};

defineExpose({
    open,
    close
});

</script>

<style lang="scss" scoped>
.status-holder {
	  background-color: #fff;
	}
	.search-city {
		background: #fff;
	}

	.search-list {
		min-height: 500px;
		background: #fff;
	}

	.search-list .item {
		border-bottom: 1px dashed #eee;
		line-height: 50px;
		font-size: 16px;
		padding: 0 25px;
	}

	.hotcity {
		font-size: 14px;
		padding: 10px;
		background: #fff;
	}

	.hotcity .mt {
		line-height: 30px;
	}

	.hotcity .mc {
		display: flex;
		flex-wrap: wrap;
	}

	.hotcity .mc .item {
		width: 90px;
		border: 1px solid #e0e0e0;
		border-radius: 5px;
		text-align: center;
		line-height: 30px;
		margin: 10px 10px;
	}

	.city-list {
		position: relative;
		width: 100%;
		height: 500px;
	}

</style>

