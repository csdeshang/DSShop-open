<template>
  <div class="spec-manager">

    <!-- 规格项和规格值设置 -->
    <div class="spec-items mb-6">

      <div class="flex flex-col gap-4">


          <div v-for="(item, index) in goodsSpecFormat" :key="item.id"
            class="spec-item relative p-5 rounded-lg transition-all"
            :class="{ 'bg-gray-50 hover:bg-gray-100': !isDisabled }">
            <!-- 规格名称 -->
            <div class="spec-name mb-4">
              <el-form-item label-width="0" class="mb-0">
                <el-input v-model.trim="item.spec_name" clearable placeholder="规格名称，如：颜色" class="w-[200px]"
                  maxlength="20" :disabled="isDisabled" @input="specDataChanged" />
              </el-form-item>
            </div>

            <!-- 规格值列表 -->
            <div class="spec-value">
              <div class="flex flex-wrap gap-2 mb-3">
                <div v-for="(specValue, specIndex) in item.values" :key="specValue.id" class="relative group">
                  <el-input v-model.trim="specValue.spec_value_name" clearable placeholder="规格值，如：红色" class="w-[150px]"
                    @input="specDataChanged" maxlength="20" :disabled="isDisabled" />

                  <!-- 删除规格值按钮 -->
                  <el-button v-if="!isDisabled" type="danger" circle size="small"
                    class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                    @click="deleteSpecValue(index, specIndex)">
                    <el-icon :size="12">
                      <Delete />
                    </el-icon>
                  </el-button>
                </div>

                <!-- 添加规格值按钮 -->
                <el-button v-if="!isDisabled" type="primary" plain @click="addSpecValue(index)" class="h-[32px]">
                  <el-icon class="mr-1">
                    <Plus />
                  </el-icon>
                  添加规格值
                </el-button>
              </div>
            </div>

            <!-- 删除规格项按钮 -->
            <el-button v-if="!isDisabled" type="danger" circle class="absolute top-2 right-2"
              @click="deleteSpecItem(index)">
              <el-icon :size="16">
                <Delete />
              </el-icon>
            </el-button>
          </div>


      </div>

      <!-- 添加规格项按钮 -->
      <el-button v-if="!isDisabled" type="primary" @click="addSpec" class="mt-3">
        <el-icon class="mr-1">
          <Plus />
        </el-icon>
        添加规格项
      </el-button>
    </div>

    <!-- 规格组合表格 -->
    <div v-if="Object.keys(goodsSkuItems).length" class="sku-table mt-6">

      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm text-left">
          <thead class="text-xs uppercase text-center border-b">
            <tr>
              <th scope="col" class="p-4 w-12">
                <el-checkbox v-model="skuCheckboxGroup.isSelectAll"
                  :indeterminate="skuCheckboxGroup.isPartiallySelected" @change="handleSkuSelectAllChange" />
              </th>
              <template v-for="(item, index) in goodsSpecFormat" :key="index">
                <th v-if="item.spec_name" scope="col" class="px-4 py-3">
                  {{ item.spec_name }}
                </th>
              </template>
              <th scope="col" class="px-4 py-3">图片</th>
              <th scope="col" class="px-4 py-3">价格</th>
              <th scope="col" class="px-4 py-3">市场价</th>
              <th scope="col" class="px-4 py-3">成本价</th>
              <th scope="col" class="px-4 py-3">库存</th>
              <th scope="col" class="px-4 py-3">编码</th>
              <th scope="col" class="px-4 py-3">默认</th>
            </tr>
          </thead>

          <!-- 批量设置行 -->
          <tbody class="text-center">
            <tr class="border-b">
              <td>
              </td>
              <template v-for="(item, index) in goodsSpecFormat" :key="index">
                <td v-if="item.spec_name" class="p-3"></td>
              </template>
              <td class="p-3">
                <picker-image v-model="batchSkuData.sku_image" :limit="1" type="image" width="50px" height="50px" />
              </td>
              <td class="p-3">
                <el-input v-model="batchSkuData.sku_price" placeholder="价格" type="number" class="w-[100px]"></el-input>
              </td>
              <td class="p-3">
                <el-input v-model="batchSkuData.market_price" placeholder="市场价" type="number" class="w-[100px]"></el-input>
              </td>
              <td class="p-3">
                <el-input v-model="batchSkuData.cost_price" placeholder="成本价" type="number" class="w-[100px]"></el-input>
              </td>
              <td class="p-3">
                <el-input v-model="batchSkuData.sku_stock" placeholder="库存" type="number" class="w-[100px]"></el-input>
              </td>
              <td class="p-3">
                <el-input v-model="batchSkuData.sku_code" placeholder="编码" class="w-[200px]"></el-input>
              </td>
              <td class="p-3 text-center">
                <el-button @click="batchSetSku" :disabled="!skuCheckboxGroup.selectedSkuIds.length || isDisabled">
                  批量修改
                </el-button>
              </td>
            </tr>

            <!-- 规格组合数据 -->
            <tr v-for="(item, key, index) in goodsSkuItems" :key="key" class="border-b">
              <td class="p-3 text-center">
                <el-checkbox v-model="skuCheckboxGroup.selectedSkuIds" :value="key" :key="key" @change="handleSkuSelectionChange"
                  :disabled="isDisabled" />
              </td>

              <!-- 规格值显示 -->
              <template v-for="(c, cIndex) in goodsSpecItems" :key="cIndex">
                <td v-if="c.index == index" :rowspan="c.rowSpan" class="p-3 font-medium whitespace-nowrap">
                  {{ c.spec_value_name }}
                </td>
              </template>

              <!-- SKU相关属性 -->
              <td class="p-3">
                <picker-image v-model="item.sku_image" :limit="1" type="image" width="50px" height="50px" />
              </td>
              <td class="p-3">
                <el-input v-model.trim="item.sku_price" class="w-[100px]" placeholder="0.00" type="number"
                  :disabled="isDisabled" />
              </td>
              <td class="p-3">
                <el-input v-model.trim="item.market_price" class="w-[100px]" placeholder="0.00" type="number" />
              </td>
              <td class="p-3">
                <el-input v-model.trim="item.cost_price" class="w-[100px]" placeholder="0.00" type="number" />
              </td>
              <td class="p-3">
                <el-input v-model.trim="item.sku_stock" class="w-[100px]" placeholder="0" @input="updateSkuStockSum"
                  type="number" :disabled="isDisabled" />
              </td>
              <td class="p-3">
                <el-input v-model.trim="item.sku_code" class="w-[200px]" maxlength="50" />
              </td>
              <td class="p-3 text-center">
                <el-switch v-model="item.is_default" :active-value="1" :inactive-value="0"
                  @change="val => setDefaultSku(val, key)" :disabled="isDisabled" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 无规格组合时的空状态提示 -->
    <el-empty v-else-if="goodsSpecFormat.length > 0" description="请添加有效的规格项和规格值" class="mt-6" />
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { debounce } from 'lodash-es'
import { Plus, Delete } from '@element-plus/icons-vue'
import { useGoodsEditSkuStore } from '@/stores/modules/goodsEditSku'
import PickerImage from '@/components/attachment/picker-image.vue'

defineOptions({
  name: 'SpecManager'
})

const goodsStore = useGoodsEditSkuStore()

const goodsSpecFormat = goodsStore.goodsSpecFormat
const goodsSkuItems = goodsStore.goodsSkuItems
const goodsSpecItems = goodsStore.goodsSpecItems
const batchSkuData = goodsStore.batchSkuData
const skuCheckboxGroup = goodsStore.skuCheckboxGroup
const isDisabled = ref(false)

const specDataChanged = debounce(() => {
  goodsStore.handleSpecDataChanged()
}, 300)

const {
  deleteSpecItem,
  addSpecValue,
  deleteSpecValue,
  setDefaultSku,
  handleSkuSelectAllChange,
  handleSkuSelectionChange,
  batchSetSku,
  updateSkuStockSum
} = goodsStore

const addSpec = () => {
  goodsStore.addSpecItem()
}
</script>

<style scoped>
.spec-item {
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
  background-color: var(--el-bg-color-page);
  border: none;
  display: flex;
  flex-direction: column;
  position: relative;
}

.spec-item .delete-spec {
  display: none;
  position: absolute;
  top: 0px;
  right: 0px;
}

.spec-item:hover .delete-spec {
  display: block;
}
</style>
