<template>
    <view class="quick-store-page">
        <!-- 店铺头部 -->
        <QuickStoreHeader :store-info="storeInfo" />
        
        <!-- 主要内容区域 -->
        <view class="main-content">
            <!-- 左侧分类菜单 -->
            <view class="category-sidebar">
                <scroll-view scroll-y class="category-scroll">
                    <view 
                        v-for="(category, index) in categories" 
                        :key="category.id"
                        class="category-item"
                        :class="{ active: currentCategoryIndex === index }"
                        @click="switchCategory(index)"
                    >
                        <text class="category-name">{{ category.name }}</text>
                        <view v-if="getCategoryCartCount(category.id) > 0" class="category-count">
                            {{ getCategoryCartCount(category.id) }}
                        </view>
                    </view>
                </scroll-view>
            </view>

            <!-- 右侧商品列表 -->
            <scroll-view scroll-y class="goods-content" :scroll-into-view="scrollIntoView">
                <view 
                    v-for="(category, categoryIndex) in categories" 
                    :key="category.id"
                    :id="`category-${categoryIndex}`"
                    class="goods-category-section"
                >
                    <view class="category-title">{{ category.name }}</view>
                    <view class="goods-list">
                        <QuickGoodsItem
                            v-for="goods in category.goods"
                            :key="goods.id"
                            :goods="goods"
                            :cart-quantity="getCartQuantity(goods.id)"
                            @add-to-cart="addToCart"
                            @remove-from-cart="removeFromCart"
                            @show-spec="showGoodsSpec"
                        />
                    </view>
                </view>
            </scroll-view>
        </view>

        <!-- 悬浮购物车按钮 -->
        <view 
            v-if="cartTotalCount > 0" 
            class="floating-cart-btn"
            @click="toggleCartDrawer"
        >
            <view class="cart-icon">
                <uni-icons type="cart" size="24" color="#fff" />
                <view class="cart-badge">{{ cartTotalCount }}</view>
            </view>
        </view>

        <!-- 底部结算栏 -->
        <view class="settlement-bar">
            <view class="left-info" @click="toggleCartDrawer">
                <view class="cart-info">
                    <view class="cart-icon-small">
                        <uni-icons type="cart" size="20" color="#fff" />
                        <view v-if="cartTotalCount > 0" class="cart-badge-small">{{ cartTotalCount }}</view>
                    </view>
                    <view class="price-info">
                        <text class="total-price">¥{{ cartTotalCount > 0 ? cartTotalPrice : '0.00' }}</text>
                        <text class="delivery-fee">另需配送费¥{{ deliveryFee }}</text>
                    </view>
                </view>
            </view>
            <view class="checkout-btn" 
                  :class="{ 'disabled': cartTotalCount === 0 }"
                  @click="goCheckout">
                <text class="checkout-text">{{ cartTotalCount > 0 ? '去结算' : '未选商品' }}</text>
            </view>
        </view>

        <!-- 购物车抽屉 -->
        <QuickCartDrawer
            :visible="cartDrawerVisible"
            :cart-items="cartItems"
            @close="cartDrawerVisible = false"
            @update-quantity="updateCartQuantity"
        />

        <!-- 商品规格选择弹窗 -->
        <QuickGoodsSpec
            :visible="specPopupVisible"
            :goods="selectedGoods"
            @close="specPopupVisible = false"
            @confirm="confirmAddToCart"
        />
    </view>
</template>

<script setup lang="ts">
import { ref, computed, reactive, watch } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { debounce } from '@/utils/util'

// 导入组件
import QuickStoreHeader from './components/quick-store-header.vue'
import QuickGoodsItem from './components/quick-goods-item.vue'
import QuickCartDrawer from './components/quick-cart-drawer.vue'
import QuickGoodsSpec from './components/quick-goods-spec.vue'

// 导入API
import { getTblStoreInfo } from '@/home/api/tbl-store/tblStore'
import { getTblGoodsPages } from '@/home/api/tbl-goods/tblGoods'
import { addTblCart, updateTblCart, deleteTblCart, getTblCartList } from '@/home/api/tbl-cart/tblCart'

// 类型定义
interface Category {
    id: number
    name: string
    goods: any[]
}

// 购物车商品（与扁平 carts 接口一致）
interface CartItemData {
    id: number
    platform: string
    user_id: number
    goods_id: number
    sku_id: number
    quantity: number
    promotion_platform: string
    promotion_type: string
    promotion_related_id: number
    promotion_price: number
    is_active: number
    create_at: string
    update_at: string
    goods: {
        id: number
        brand_id: number
        goods_name: string
        cover_image: string
        goods_status: number
        sys_status: number
    }
    goodsSku: {
        id: number
        goods_id: number
        sku_name: string
        sku_image: string
        sku_code: string
        sku_price: number
        sku_stock: number
        sku_spec_format: string
        market_price: number
        cost_price: number
        is_default: number
    }
}

interface StoreInfo {
    id: number
    platform: string
    merchant_id: number
    store_name: string
    store_logo: string
    store_introduction: string
    store_business_status: number
    service_fee_rate: string
    category_id: number
    area_id: number
    address: string
    avg_describe_score: string
    avg_logistics_score: string
    avg_service_score: string
    sales_num: number
    is_recommend: number
    sort: number
    is_enabled: number
    apply_status: number
}

// 页面参数
const storeId = ref('')
const platform = ref('mall') // 添加平台参数，默认为mall
const currentCategoryIndex = ref(0)
const scrollIntoView = ref('')
const cartDrawerVisible = ref(false)
const specPopupVisible = ref(false)
const selectedGoods = ref(null)
const deliveryFee = ref(3) // 配送费
const cartLoading = ref(false) // 购物车加载状态

// 店铺信息
const storeInfo = reactive<StoreInfo>({
    id: 0,
    platform: '',
    merchant_id: 0,
    store_name: '',
    store_logo: '',
    store_introduction: '',
    store_business_status: 0,
    service_fee_rate: '',
    category_id: 0,
    area_id: 0,
    address: '',
    avg_describe_score: '',
    avg_logistics_score: '',
    avg_service_score: '',
    sales_num: 0,
    is_recommend: 0,
    sort: 0,
    is_enabled: 0,
    apply_status: 0
})

// 商品分类和数据
const categories = reactive<Category[]>([])
const cartItems = reactive<CartItemData[]>([]) // 直接使用数组存储购物车商品

// 计算属性
const cartTotalCount = computed(() => {
    return cartItems.reduce((total, item) => total + item.quantity, 0)
})

const cartTotalPrice = computed(() => {
    const total = cartItems.reduce((sum, item) => sum + (item.quantity * item.promotion_price), 0)
    return total.toFixed(2)
})

// 页面加载
onLoad((options: any) => {
    storeId.value = options?.store_id || ''
    if (storeId.value) {
        loadStoreData()
    }
})

// 加载店铺数据
const loadStoreData = async () => {
    try {
        // 加载店铺信息
        await loadStoreInfo()
        // 加载购物车数据
        await loadCartData()
        // 加载商品分类
        await loadCategories()
        // 加载商品数据
        await loadGoodsData()
    } catch (error) {
        console.error('加载店铺数据失败:', error)
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        })
    }
}

// 加载店铺信息
const loadStoreInfo = async () => {
    try {
        const result = await getTblStoreInfo(Number(storeId.value))
        // 确保数据类型正确赋值
        const data = result.data
        Object.assign(storeInfo, {
            id: data.id || 0,
            platform: data.platform || '',
            merchant_id: data.merchant_id || 0,
            store_name: data.store_name || '',
            store_logo: data.store_logo || '',
            store_introduction: data.store_introduction || '',
            store_business_status: data.store_business_status || 0,
            service_fee_rate: data.service_fee_rate || '',
            category_id: data.category_id || 0,
            area_id: data.area_id || 0,
            address: data.address || '',
            avg_describe_score: data.avg_describe_score || '0.00',
            avg_logistics_score: data.avg_logistics_score || '0.00',
            avg_service_score: data.avg_service_score || '0.00',
            sales_num: data.sales_num || 0,
            is_recommend: data.is_recommend || 0,
            sort: data.sort || 0,
            is_enabled: data.is_enabled || 0,
            apply_status: data.apply_status || 0
        })
    } catch (error) {
        console.error('加载店铺信息失败:', error)
    }
}

// 加载购物车数据
const loadCartData = async () => {
    try {
        // 在加载购物车前需要先确保storeInfo.platform已获取
        if (!storeInfo.platform) {
            return
        }
        
        // 设置加载状态
        cartLoading.value = true
        
        const response = await getTblCartList({}, storeInfo.platform)
        
        if (response.code === 10000 && response.data) {
            const items = Array.isArray(response.data) ? response.data : Object.values(response.data || {})
            cartItems.splice(0, cartItems.length, ...items)
        } else {
            // 如果没有数据或请求失败，清空购物车
            cartItems.splice(0, cartItems.length)
        }
    } catch (error) {
        console.error('加载购物车数据失败:', error)
        // 购物车加载失败不影响主要功能，只记录错误
    } finally {
        // 重置加载状态
        cartLoading.value = false
    }
}

// 加载商品分类
const loadCategories = async () => {
    categories.splice(0, categories.length)
    categories.push({ id: 0, name: '全部', goods: [] })
}

// 加载商品数据
const loadGoodsData = async () => {
    try {
        for (let i = 0; i < categories.length; i++) {
            const category = categories[i]
            const params: any = {
                store_id: Number(storeId.value),
                status: 1,
                page_current: 1,
                page_size: 100
            }
            
            const result = await getTblGoodsPages(params)
            category.goods = result.data.data || []
        }
    } catch (error) {
        console.error('加载商品数据失败:', error)
    }
}

// 切换分类
const switchCategory = (index: number) => {
    currentCategoryIndex.value = index
    scrollIntoView.value = `category-${index}`
}

// 获取分类购物车数量
const getCategoryCartCount = (categoryId: number) => {
    let count = 0
    const category = categories.find(cat => cat.id === categoryId)
    if (category) {
        category.goods.forEach((goods: any) => {
            // 遍历所有购物车项，找到属于当前商品的所有规格
            cartItems.forEach(cartItem => {
                if (cartItem.goods_id === goods.id) {
                    count += cartItem.quantity
                }
            })
        })
    }
    return count
}

// 获取商品购物车数量（所有规格的总数量）
const getCartQuantity = (goodsId: string | number) => {
    const targetGoodsId = Number(goodsId)
    let totalQuantity = 0
    
    // 遍历所有购物车项，累加同一商品的所有规格数量
    cartItems.forEach(cartItem => {
        if (cartItem.goods_id === targetGoodsId) {
            totalQuantity += cartItem.quantity
        }
    })
    
    return totalQuantity
}

// 添加到购物车
const addToCart = async (goods: any) => {
    // 如果商品有多规格，显示规格选择弹窗
    if (goods.goodsSkuList && goods.goodsSkuList.length > 1) {
        selectedGoods.value = goods
        specPopupVisible.value = true
        return
    }
    
    // 防止重复点击
    if (cartLoading.value) return
    
    try {
        cartLoading.value = true
        
        // 调用API添加到购物车
        const params = {
            goods_id: goods.id,
            sku_id: goods.goodsSku?.id || 0, // 使用默认SKU或0
            quantity: 1,
            promotion_platform: '',
            promotion_type: '',
            promotion_related_id: 0
        }
        
        const res = await addTblCart(params, storeInfo.platform)
        
        if (res.code === 10000) {
            // API调用成功，重新加载购物车数据
            await loadCartData()
            
            uni.showToast({
                title: '已加入购物车',
                icon: 'success',
            })
        } else {
            uni.showToast({
                title: res.message || '添加失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('添加购物车失败:', error)
        uni.showToast({
            title: '添加失败，请重试',
            icon: 'none'
        })
    } finally {
        cartLoading.value = false
    }
}

// 从购物车移除
const removeFromCart = async (goodsId: string | number) => {
    // 防止重复点击
    if (cartLoading.value) return
    
    // 确保使用数字类型的goodsId查找购物车项
    const targetGoodsId = Number(goodsId)
    
    // 查找该商品的购物车项（可能有多个规格）
    const cartItemList = cartItems
    let cartItemToUpdate: CartItemData | undefined = undefined
    
    for (const item of cartItemList) {
        if (item.goods_id === targetGoodsId && item.quantity > 0) {
            if (!cartItemToUpdate || item.quantity > cartItemToUpdate.quantity) {
                // 选择数量最多的规格进行操作
                cartItemToUpdate = item
            }
        }
    }
    
    if (!cartItemToUpdate || !cartItemToUpdate.id) {
        return
    }

    try {
        cartLoading.value = true
        
        if (cartItemToUpdate.quantity > 1) {
            // 减少数量，调用更新API
            const params = {
                cart_id: cartItemToUpdate.id,
                quantity: cartItemToUpdate.quantity - 1
            }
            
            const res = await updateTblCart(params, storeInfo.platform)
            
            if (res.code === 10000) {
                // API调用成功，重新加载购物车数据
                await loadCartData()
            } else {
                uni.showToast({
                    title: res.message || '更新失败',
                    icon: 'none'
                })
            }
        } else {
            // 删除商品，调用删除API
            const params = {
                cart_ids: [cartItemToUpdate.id]
            }
            
            const res = await deleteTblCart(params, storeInfo.platform)
            
            if (res.code === 10000) {
                // API调用成功，重新加载购物车数据
                await loadCartData()
            } else {
                uni.showToast({
                    title: res.message || '删除失败',
                    icon: 'none'
                })
            }
        }
    } catch (error) {
        console.error('操作购物车失败:', error)
        uni.showToast({
            title: '操作失败，请重试',
            icon: 'none'
        })
    } finally {
        cartLoading.value = false
    }
}

// 显示商品规格选择
const showGoodsSpec = (goods: any) => {
    selectedGoods.value = goods
    specPopupVisible.value = true
}

// 确认添加到购物车（带规格）
const confirmAddToCart = async (specData: any) => {
    // 防止重复操作
    if (cartLoading.value) return
    
    const { goods, selectedSpec, quantity } = specData
    
    try {
        cartLoading.value = true
        
        // 调用API添加到购物车
        const params = {
            goods_id: goods.id,
            sku_id: selectedSpec.id,
            quantity: quantity,
            promotion_platform: selectedSpec.promotion_platform || '',
            promotion_type: selectedSpec.promotion_type || '',
            promotion_related_id: selectedSpec.promotion_related_id || 0
        }
        
        const res = await addTblCart(params, storeInfo.platform)
        
        if (res.code === 10000) {
            // API调用成功，重新加载购物车数据
            await loadCartData()
            
            uni.showToast({
                title: '已加入购物车',
                icon: 'success',
            })
        } else {
            uni.showToast({
                title: res.message || '添加失败',
                icon: 'none'
            })
        }
    } catch (error) {
        console.error('添加购物车失败:', error)
        uni.showToast({
            title: '添加失败，请重试',
            icon: 'none'
        })
    } finally {
        cartLoading.value = false
    }
    
    specPopupVisible.value = false
}

// 切换购物车抽屉
const toggleCartDrawer = () => {
    cartDrawerVisible.value = !cartDrawerVisible.value
}

// 更新购物车数量（由购物车抽屉调用）
const updateCartQuantity = async (itemId: string, quantity: number) => {
    // 防止重复操作
    if (cartLoading.value) return
    
    const cartItem = cartItems.find(item => item.id === Number(itemId))
    if (!cartItem || !cartItem.id) {
        return
    }

    try {
        cartLoading.value = true
        
        if (quantity <= 0) {
            // 删除商品
            const params = {
                cart_ids: [cartItem.id]
            }
            
            const res = await deleteTblCart(params, storeInfo.platform)
            
            if (res.code === 10000) {
                // API调用成功，重新加载购物车数据
                await loadCartData()
            } else {
                uni.showToast({
                    title: res.message || '删除失败',
                    icon: 'none'
                })
            }
        } else {
            // 更新数量
            const params = {
                cart_id: cartItem.id,
                quantity: quantity
            }
            
            const res = await updateTblCart(params, storeInfo.platform)
            
            if (res.code === 10000) {
                // API调用成功，重新加载购物车数据
                await loadCartData()
            } else {
                uni.showToast({
                    title: res.message || '更新失败',
                    icon: 'none'
                })
            }
        }
    } catch (error) {
        console.error('更新购物车失败:', error)
        uni.showToast({
            title: '操作失败，请重试',
            icon: 'none'
        })
    } finally {
        cartLoading.value = false
    }
}

// 去结算
const goCheckout = () => {
    if (cartTotalCount.value === 0) {
        uni.showToast({
            title: '请先选择商品',
            icon: 'none'
        })
        return
    }
    
    // 将购物车数据转换为数组并提取cart_ids
    const selectedCartIds = cartItems.map(item => item.id).filter(id => id) // 过滤掉undefined的cart_id
    
    if (selectedCartIds.length === 0) {
        uni.showToast({
            title: '购物车数据异常，请重新添加商品',
            icon: 'none'
        })
        return
    }
    
    // 跳转到公共的订单结算页面
    uni.navigateTo({
        url: `/home/platform/${storeInfo.platform}/pages/order/checkout?cart_ids=${selectedCartIds.join(',')}`
    })
}
</script>

<style lang="scss" scoped>
.quick-store-page {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: #f8f8f8;
}

.main-content {
    flex: 1;
    display: flex;
    overflow: hidden;
    padding-bottom: 140rpx; // 为底部结算栏留空间
}

.category-sidebar {
    width: 180rpx;
    background: #f5f5f5;
    border-right: 1rpx solid #e5e5e5;

    .category-scroll {
        height: 100%;
    }

    .category-item {
        position: relative;
        padding: 30rpx 20rpx;
        text-align: center;
        border-bottom: 1rpx solid #e5e5e5;

        &.active {
            background: #fff;
            border-right: 4rpx solid #ff6600;

            .category-name {
                color: #ff6600;
                font-weight: bold;
            }
        }

        .category-name {
            font-size: 26rpx;
            color: #333;
        }

        .category-count {
            position: absolute;
            top: 15rpx;
            right: 15rpx;
            min-width: 32rpx;
            height: 32rpx;
            line-height: 32rpx;
            background: #ff6600;
            color: #fff;
            font-size: 20rpx;
            text-align: center;
            border-radius: 16rpx;
            padding: 0 8rpx;
        }
    }
}

.goods-content {
    flex: 1;
    background: #fff;
}

.goods-category-section {
    .category-title {
        padding: 20rpx 30rpx;
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
        background: #fff;
        border-bottom: 1rpx solid #f0f0f0;
    }

    .goods-list {
        padding: 0 15rpx;
    }
}

.floating-cart-btn {
    position: fixed;
    right: 40rpx;
    bottom: 200rpx;
    width: 100rpx;
    height: 100rpx;
    background: #ff6600;
    border-radius: 50rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4rpx 12rpx rgba(255, 102, 0, 0.3);
    z-index: 99;

    .cart-icon {
        position: relative;

        .cart-badge {
            position: absolute;
            top: -15rpx;
            right: -15rpx;
            min-width: 36rpx;
            height: 36rpx;
            line-height: 36rpx;
            background: #ff3333;
            color: #fff;
            font-size: 20rpx;
            text-align: center;
            border-radius: 18rpx;
            padding: 0 8rpx;
        }
    }
}

.settlement-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 120rpx;
    background: #fff;
    display: flex;
    align-items: center;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.1);
    z-index: 100;

    .left-info {
        flex: 1;
        padding: 0 30rpx;

        .cart-info {
            display: flex;
            align-items: center;

            .cart-icon-small {
                position: relative;
                margin-right: 20rpx;
                width: 60rpx;
                height: 60rpx;
                background: #ff6600;
                border-radius: 30rpx;
                display: flex;
                align-items: center;
                justify-content: center;

                .cart-badge-small {
                    position: absolute;
                    top: -10rpx;
                    right: -10rpx;
                    min-width: 32rpx;
                    height: 32rpx;
                    line-height: 32rpx;
                    background: #ff3333;
                    color: #fff;
                    font-size: 18rpx;
                    text-align: center;
                    border-radius: 16rpx;
                    padding: 0 6rpx;
                }
            }

            .price-info {
                display: flex;
                flex-direction: column;

                .total-price {
                    font-size: 36rpx;
                    color: #ff6600;
                    font-weight: bold;
                }

                .delivery-fee {
                    font-size: 22rpx;
                    color: #999;
                    margin-top: 4rpx;
                }
            }
        }
    }

    .checkout-btn {
        width: 200rpx;
        height: 80rpx;
        background: #ff6600;
        border-radius: 40rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 30rpx;

        &.disabled {
            background: #ccc;
        }

        .checkout-text {
            color: #fff;
            font-size: 30rpx;
            font-weight: bold;
        }
    }
}
</style> 