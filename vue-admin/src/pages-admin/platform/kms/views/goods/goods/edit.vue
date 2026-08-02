<template>
  <div>
    <BackNavigation />

    <el-card class="mb-4">
      <el-form ref="formRef" :model="formData" :rules="formRules" label-width="120px" v-loading="loading">
        <el-tabs v-model="activeTab">
          <el-tab-pane name="basic" label="基本信息">
            <div class="basic-info">
              <el-form-item label="商品名称" prop="goods_name">
                <el-input v-model="formData.goods_name" placeholder="请输入商品名称" maxlength="100" />
              </el-form-item>

              <el-form-item label="商品简介" prop="goods_advword">
                <el-input v-model="formData.goods_advword" placeholder="请输入商品简介" maxlength="200" />
              </el-form-item>

              <el-form-item label="商品图片" prop="slide_image">
                <PickerImage v-model="formData.slide_image" :limit="10" type="image" />
              </el-form-item>

              <el-form-item label="商品分类" prop="category_ids">
                <TblGoodsCategorySelect v-model="formData.category_ids" platform="kms" />
              </el-form-item>

              <el-form-item label="品牌" prop="brand_id">
                <TblGoodsBrandSelect v-model="formData.brand_id" platform="kms" />
              </el-form-item>

              <el-form-item label="商品视频" prop="goods_video">
                <PickerImage v-model="formData.goods_video" :limit="1" type="video" width="200px" height="200px" />
              </el-form-item>

              <el-form-item label="排序">
                <el-input-number v-model="formData.goods_sort" :min="0" :max="9999" />
              </el-form-item>

              <el-form-item label="虚拟销量" prop="virtual_sales_num">
                <el-input-number v-model="formData.virtual_sales_num" :min="0" />
              </el-form-item>

              <el-form-item label="总库存" prop="stock_num">
                <el-input-number
                  :model-value="formData.spec_type === 'multiple' ? goodsEditStore.goodsSkuStockSum : formData.stock_num"
                  :min="0"
                  disabled
                />
              </el-form-item>

              <el-form-item label="上架状态">
                <el-radio-group v-model="formData.goods_status">
                  <el-radio :value="1">上架</el-radio>
                  <el-radio :value="0">下架</el-radio>
                </el-radio-group>
              </el-form-item>
            </div>
          </el-tab-pane>

          <el-tab-pane name="spec" label="规格/库存">
            <el-form-item label="类型" prop="spec_type">
              <el-radio-group v-model="formData.spec_type">
                <el-radio value="single">单规格</el-radio>
                <el-radio value="multiple">多规格</el-radio>
              </el-radio-group>
            </el-form-item>

            <template v-if="formData.spec_type === 'single'">
              <el-form-item label="图片" prop="single.sku_image">
                <picker-image v-model="formData.single.sku_image" :limit="1" type="image" width="100px" height="100px" />
              </el-form-item>
              <el-form-item label="价格" prop="single.sku_price">
                <el-input v-model.trim="formData.single.sku_price" clearable placeholder="0.00" min="0" max="9999999"
                  type="number" />
              </el-form-item>
              <el-form-item label="市场价" prop="single.market_price">
                <el-input v-model.trim="formData.single.market_price" clearable placeholder="0.00" maxlength="8" />
              </el-form-item>
              <el-form-item label="成本价" prop="single.cost_price">
                <el-input v-model.trim="formData.single.cost_price" clearable placeholder="0.00" maxlength="8" />
              </el-form-item>
              <el-form-item label="库存" prop="single.sku_stock">
                <el-input v-model.trim="formData.single.sku_stock" clearable placeholder="0" maxlength="8" />
              </el-form-item>
              <el-form-item label="编码" prop="single.sku_code">
                <el-input v-model.trim="formData.single.sku_code" clearable maxlength="50" />
              </el-form-item>
            </template>

            <template v-if="formData.spec_type === 'multiple'">
              <spec-manager />
            </template>
          </el-tab-pane>

          <el-tab-pane name="parameter" label="参数">
            <parameter-editor v-model="formData.goods_parameters" />
          </el-tab-pane>

          <el-tab-pane name="detail" label="商品详情">
            <RichTextEditor v-model="formData.goods_body" />
          </el-tab-pane>
        </el-tabs>
      </el-form>
    </el-card>

    <div class="absolute bottom-0 left-0 right-0 p-4 flex justify-center"
      :style="{ backgroundColor: 'var(--el-bg-color)' }">
      <el-button type="primary" @click="handleSubmit" :loading="loading">保存</el-button>
      <el-button @click="router.back()" type="default">返回</el-button>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import { useGoodsEditSkuStore, type SpecFormat, type SpecTableItem, type SkuItem } from '@/stores/modules/goodsEditSku'
import { getKmsGoodsUpdateInfo, addKmsGoods, updateKmsGoods } from '@/pages-admin/platform/kms/api/goods/Goods'
import BackNavigation from '@/components/back-navigation/index.vue'
import PickerImage from '@/components/attachment/picker-image.vue'
import TblGoodsBrandSelect from '@/pages-admin/components/select/goods-brand-select.vue'
import TblGoodsCategorySelect from '@/pages-admin/components/select/goods-category-select.vue'
import SpecManager from '@/pages-admin/components/tbl-goods/goods/components/SpecManager.vue'
import ParameterEditor from '@/pages-admin/components/tbl-goods/goods/components/ParameterEditor.vue'
import RichTextEditor from '@/components/editor/index.vue'

const route = useRoute()
const router = useRouter()
const goodsEditStore = useGoodsEditSkuStore()

const formRef = ref<FormInstance>()
const loading = ref(false)

const formData = reactive({
  id: '',
  goods_name: '',
  goods_advword: '',
  goods_body: '',
  goods_video: '',
  goods_status: 1,
  spec_type: 'single',
  brand_id: 0,
  category_ids: [] as number[],
  slide_image: [] as string[],
  virtual_sales_num: 0,
  goods_sort: 0,
  stock_num: 0,
  single: {
    sku_name: '单规格',
    sku_image: '',
    sku_code: '',
    sku_price: '',
    sku_stock: '',
    market_price: '',
    cost_price: '',
  },
  goods_spec_format: [] as SpecFormat[],
  goods_spec_items: [] as SpecTableItem[],
  goods_sku_items: {} as Record<string, SkuItem>,
  goods_parameters: [] as Array<{ name: string, value: string, sort: number }>,
})

const formRules = computed<FormRules>(() => {
  const commonRules = {
    goods_name: [
      { required: true, message: '请输入商品名称', trigger: 'blur' },
      { min: 2, max: 100, message: '商品名称长度为2-100个字符', trigger: 'blur' }
    ],
    goods_body: [
      { required: true, message: '请输入商品详情', trigger: 'blur' }
    ]
  }

  if (formData.spec_type === 'single') {
    return {
      ...commonRules,
      'single.sku_price': [
        { required: true, message: '请输入商品价格', trigger: 'blur' },
        {
          pattern: /^([1-9]\d*|0)(\.\d{1,2})?$/,
          message: '请输入正确的价格格式',
          trigger: 'blur'
        }
      ],
      'single.sku_stock': [
        { required: true, message: '请输入商品库存', trigger: 'blur' },
        {
          pattern: /^[0-9]\d*$/,
          message: '请输入正确的库存数量',
          trigger: 'blur'
        }
      ]
    }
  }

  return commonRules
})

const activeTab = ref('basic')

const loadGoodsData = async (goodsId: string | number) => {
  try {
    loading.value = true
    const id = typeof goodsId === 'string' ? parseInt(goodsId) : goodsId

    if (isNaN(id)) {
      ElMessage.error('商品ID无效')
      return
    }

    const res = await getKmsGoodsUpdateInfo(id)

    if (res.code === 10000 && res.data) {
      formData.id = res.data.id
      formData.goods_name = res.data.goods_name
      formData.goods_advword = res.data.goods_advword
      formData.goods_body = res.data.goods_body
      formData.goods_video = res.data.goods_video
      formData.goods_status = res.data.goods_status
      formData.spec_type = res.data.spec_type
      formData.brand_id = res.data.brand_id
      formData.category_ids = res.data.category_ids
      formData.slide_image = res.data.slide_image
      formData.virtual_sales_num = res.data.virtual_sales_num
      formData.goods_sort = res.data.goods_sort
      formData.stock_num = res.data.stock_num
      formData.goods_parameters = res.data.goods_parameters

      if (res.data.spec_type == 'single') {
        formData.single = res.data.goodsSkuList[0]
      }

      goodsEditStore.initGoodsEditInfo(res.data)
    } else {
      ElMessage.error(res.message || '获取商品信息失败')
    }
  } catch (error) {
    console.error('加载商品数据失败:', error)
    ElMessage.error('获取商品信息失败')
  } finally {
    loading.value = false
  }
}

const validateForm = async (): Promise<boolean> => {
  if (!formRef.value) return false

  try {
    await formRef.value.validate()

    if (formData.spec_type === 'multiple') {
      const specDataValid = goodsEditStore.validateSpecData()
      if (!specDataValid) {
        activeTab.value = 'spec'
        return false
      }
    }

    return true
  } catch (error: any) {
    if (error) {
      const errorFields = Object.keys(error)

      if (errorFields.includes('goods_name')) {
        activeTab.value = 'basic'
      } else if (errorFields.some(field => field.startsWith('single.'))) {
        activeTab.value = 'spec'
      } else if (errorFields.includes('goods_body')) {
        activeTab.value = 'detail'
      } else if (errorFields.some(field => field.includes('goods_parameters'))) {
        activeTab.value = 'parameter'
      } else {
        activeTab.value = 'basic'
      }
    }

    return false
  }
}

const handleSubmit = async () => {
  const isValid = await validateForm()
  if (!isValid) return

  const requestFun = formData.id ? updateKmsGoods : addKmsGoods

  if (formData.spec_type === 'multiple') {
    formData.stock_num = goodsEditStore.goodsSkuStockSum
  }

  formData.goods_spec_format = goodsEditStore.goodsSpecFormat
  formData.goods_sku_items = goodsEditStore.goodsSkuItems
  formData.goods_spec_items = goodsEditStore.goodsSpecItems

  loading.value = true
  try {
    const res = await requestFun(formData)

    if (res.code === 10000) {
      ElMessage.success(formData.id ? '更新商品成功' : '添加商品成功')
      if (!formData.id) {
        router.back()
      }
    } else {
      ElMessage.error(res.message || (formData.id ? '更新失败' : '添加失败'))
    }
  } finally {
    loading.value = false
  }
}

watch(() => formData.spec_type, (newVal) => {
  if (newVal === 'multiple' && goodsEditStore.goodsSpecFormat.length === 0) {
    goodsEditStore.addSpecItem()
  }
})

onMounted(async () => {
  const goodsId = route.query.goods_id

  if (goodsId) {
    await loadGoodsData(goodsId as string)
  } else if (formData.spec_type === 'multiple') {
    goodsEditStore.addSpecItem()
  }
})
</script>
