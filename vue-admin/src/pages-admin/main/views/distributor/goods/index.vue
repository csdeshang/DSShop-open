<template>

    <el-card shadow="never" class="mb-[10px]">
        <el-form :model="searchParams" inline>
            <el-form-item label="商品名称">
                <el-input v-model="searchParams.goods_name" placeholder="商品名称" clearable />
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="resetPage">查询</el-button>
                <el-button @click="resetSearchParams">重置</el-button>
            </el-form-item>
        </el-form>
    </el-card>



    <el-card shadow="never">
        <el-tabs v-model="searchParams.platform" @tab-change="handlePlatformTabChange">
            <el-tab-pane v-for="item in platformList" :key="item.id" :label="item.name" :name="item.platform">
            </el-tab-pane>
        </el-tabs>

        <el-tabs v-model="searchParams.is_distributor_goods" @tab-change="handleDistributorTabChange">
            <el-tab-pane name="" label="全部"></el-tab-pane>
            <el-tab-pane name="0" label="未参与分销"></el-tab-pane>
            <el-tab-pane name="1" label="参与分销"></el-tab-pane>
        </el-tabs>



        <el-table :data="tableData.data" size="large" v-loading="tableData.loading">


            <el-table-column label="商品ID" prop="id" width="100" />
            <el-table-column label="商品图片" prop="goods_name" width="100">
                <template #default="{ row }">
                    <el-image :src="formatImageUrl(row.cover_image, ThumbnailPresets.small, 'goods')" style="width: 50px; height: 50px;"
                                fit="cover" />
                </template>
            </el-table-column>

            <el-table-column label="商品名称" prop="goods_name" />

            <el-table-column label="是否参与分销" prop="is_distributor_goods">
                <template #default="{ row }">
                    <el-tag v-if="row.is_distributor_goods == 1" type="success">参与分销</el-tag>
                    <el-tag v-else type="danger">未参与分销</el-tag>
                </template>
            </el-table-column>

            <el-table-column label="操作" align="right" fixed="right" width="280">
                <template #default="{ row }">
                    <div class="flex flex-row">
                        <el-button type="primary" link @click="handleUpdateDistributorGoods(row)">{{ row.is_distributor_goods == 1 ? '编辑分销' : '参与分销' }}</el-button>
                        <el-button type="primary" link @click="handleCancelDistributorGoods(row)" v-if="row.is_distributor_goods == 1">取消分销</el-button>
                        <el-button type="primary" link @click="handleTblGoodsDetail(row)">查看商品</el-button>
                    </div>
                </template>
            </el-table-column>

        </el-table>

        <div class="flex justify-end mt-[20px]">

            <el-pagination v-model:current-page="tableData.page_current" v-model:page-size="tableData.page_size"
                layout="total, sizes, prev, pager, next, jumper" :total="tableData.total" @size-change="getTableList()"
                @current-change="getTableList" />

        </div>


    </el-card>


    <TblGoodsDetail ref="handleGoodsDetailRef" />
    <DistributorGoodsEdit ref="distributorGoodsEditRef" @complete="getTableList" />

</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';

import { usePagination } from '@/hooks/usePagination'
import { getTblGoodsPages } from '@/pages-admin/main/api/tbl-goods/tblGoods'
import { cancelDistributorGoods } from '@/pages-admin/main/api/distributor/distributorGoods'

import TblGoodsDetail from '@/pages-admin/components/tbl-goods/goods/detail.vue'
import DistributorGoodsEdit from './edit.vue'

import { getSysPlatformList } from '@/pages-admin/main/api/system/SysPlatform'

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { ElMessage } from 'element-plus'



const searchParams = reactive({
    goods_name: '',
    is_distributor_goods: '',
    platform: 'mall',
})

const {
    tableData,
    getTableList,
    resetSearchParams,
    resetPage
} = usePagination({
    page_current: 1,
    page_size: 10,
    requestFun: getTblGoodsPages,
    searchParams: searchParams
})
getTableList()


const handlePlatformTabChange = (name: any) => {
    searchParams.platform = name
    getTableList()
}

const handleDistributorTabChange = (val: string) => {
    searchParams.is_distributor_goods = val
    getTableList()
}

const platformList = ref<any[]>([])
const fetchPlatformList = async () => {
    try {
        const response = await getSysPlatformList({scene: 'store'});
        platformList.value = response.data
    } catch (error) {
        console.error('请求系统配置时出错:', error);
    }
};
fetchPlatformList()

const handleGoodsDetailRef = ref<any>()
const handleTblGoodsDetail = (row: any) => {
    handleGoodsDetailRef.value.setDialogData({ id: row.id })
    handleGoodsDetailRef.value?.openDialog()
}

const distributorGoodsEditRef = ref<any>()
const handleUpdateDistributorGoods = (row: any) => {
    distributorGoodsEditRef.value.setDialogData(row)
    distributorGoodsEditRef.value?.openDialog()
}

function handleCancelDistributorGoods(row: any) {
    cancelDistributorGoods(row.id).then((res: any) => {
        if (res.code == 10000) {
            ElMessage.success('取消分销成功')
            getTableList()
        }
    })
}

</script>
