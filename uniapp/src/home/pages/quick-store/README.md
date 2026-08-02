# 快速订购模块 (Quick Store)

仿美团/饿了么的快速订购页面，支持商品浏览、购物车管理和快速下单。

## 重构后的简化架构

### 核心设计理念

**每次数据库操作后重新加载购物车** - 确保数据一致性，简化状态管理

### 购物车管理机制

#### 1. 统一的重新加载机制
- 所有购物车操作（添加、更新、删除）都调用服务端API
- API调用成功后，统一调用 `loadCartData()` 重新加载购物车
- 避免复杂的本地状态同步逻辑

#### 2. 简化的操作流程

```
用户操作 → 调用API → API成功 → 重新加载购物车 → 更新UI
```

#### 3. 主要函数

**添加商品到购物车**
```typescript
const addToCart = async (goods: any) => {
    // 调用API添加
    const res = await addTblCart(params, storeInfo.platform)
    if (res.code === 10000) {
        // 重新加载购物车
        await loadCartData()
    }
}
```

**从购物车移除商品**
```typescript
const removeFromCart = async (goodsId: string | number) => {
    // 查找要操作的购物车项
    // 调用API更新或删除
    if (res.code === 10000) {
        // 重新加载购物车
        await loadCartData()
    }
}
```

**更新购物车数量**
```typescript
const updateCartQuantity = async (itemId: string, quantity: number) => {
    // 调用API更新数量
    if (res.code === 10000) {
        // 重新加载购物车
        await loadCartData()
    }
}
```

### 优势

1. **数据一致性**: 每次操作后重新从服务器加载数据，确保前后端数据同步
2. **代码简化**: 去除复杂的本地状态更新逻辑，代码更清晰
3. **错误处理**: 统一的API错误处理机制
4. **可维护性**: 逻辑集中，便于维护和调试

### 文件结构

```
src/home/pages/quick-store/
├── index.vue                     # 主页面 - 重构后的购物车管理
├── components/
│   ├── quick-store-header.vue    # 店铺头部信息
│   ├── quick-goods-item.vue      # 商品项组件
│   ├── quick-cart-drawer.vue     # 购物车抽屉
│   └── quick-goods-spec.vue      # 商品规格选择
└── README.md                     # 文档
```

### API 依赖

- `addTblCart` - 添加商品到购物车
- `updateTblCart` - 更新购物车商品数量
- `deleteTblCart` - 删除购物车商品
- `getTblCartList` - 获取购物车列表

### 使用方法

```javascript
// 页面跳转
uni.navigateTo({
    url: `/home/pages/quick-store/index?store_id=${storeId}`
})
```

### 技术栈

- Vue 3 + TypeScript
- Uniapp 框架
- 响应式设计
- Map数据结构存储购物车
- SCSS样式

## 功能特点

### 1. 主页面功能 (`index.vue`)
- ✅ 店铺信息展示（头部横幅、logo、评分、配送时间等）
- ✅ 左侧分类导航，右侧商品列表
- ✅ 商品快速加购物车（+/- 按钮）
- ✅ 悬浮购物车显示
- ✅ 底部结算栏（总价、配送费、去结算）
- ✅ 购物车抽屉（商品管理、数量调整）
- ✅ 商品规格选择弹窗
- ✅ 跳转到公共订单结算页面
- ✅ 初始化时自动加载已有购物车数据
- ✅ 购物车数据与服务端实时同步

### 2. 组件功能
- ✅ 店铺头部：营业状态、评分、配送信息、活动标签
- ✅ 商品项：图片、名称、价格、销量、快速加购
- ✅ 购物车抽屉：商品列表、数量管理
- ✅ 规格选择：多规格选择、库存显示、价格变动

## 使用方式

### 1. 跳转到快速下单页面
```javascript
// 从店铺页面跳转，只需要传入store_id
uni.navigateTo({
  url: `/pages/quick-store/index?store_id=${store_id}`
})
```

### 2. 结算流程
快速下单页面会自动从店铺信息中获取platform参数，然后跳转到对应平台的订单结算页面：
```javascript
// 自动根据店铺platform跳转到对应平台的订单页面
uni.navigateTo({
  url: `/home/platform/${storeInfo.platform}/pages/order/checkout?cart_ids=${selectedCartIds.join(',')}`
})
```

### 3. Platform支持
系统会自动从店铺信息中获取platform字段：
- `mall` - 商城平台
- `food` - 餐饮平台  
- `house` - 房产平台
- 其他业务平台...

### 4. API 集成

需要集成以下 API：

```typescript
// 店铺相关
import { getTblStoreInfo } from '@/home/api/tbl-store/tblStore'

// 商品相关  
import { getTblGoodsPages } from '@/home/api/tbl-goods/tblGoods'

// 购物车相关
import { addTblCart, updateTblCart, deleteTblCart, getTblCartList } from '@/home/api/tbl-cart/tblCart'
```

### 5. 数据流程

#### 页面初始化流程：
1. 加载店铺信息（获取platform字段）
2. 加载已有购物车数据（按店铺ID过滤）
3. 加载商品分类
4. 加载商品数据
5. 将购物车数据同步到本地状态

#### 购物车操作流程：
1. **添加商品**：调用API → 成功后更新本地状态
2. **修改数量**：调用API → 成功后更新本地状态  
3. **删除商品**：调用API → 成功后清除本地状态
4. **结算**：跳转到对应平台的订单页面

## 设计亮点

### 1. 用户体验
- 🎯 仿美团/饿了么的经典布局
- 🎯 左侧分类 + 右侧商品的高效浏览
- 🎯 一键加购物车，操作简单快捷
- 🎯 悬浮购物车实时显示
- 🎯 底部结算栏固定显示
- 🎯 与公共订单系统无缝集成

### 2. 交互设计
- 🔄 分类切换时商品列表自动滚动
- 🔄 购物车数量实时更新
- 🔄 规格选择影响价格和库存
- 🔄 结算时自动跳转到统一订单页面

### 3. 性能优化
- ⚡ 使用 Map 存储购物车数据，查找效率高
- ⚡ 分类商品按需加载
- ⚡ 图片懒加载和缓存
- ⚡ 组件化设计，便于复用

## 自定义配置

### 1. 主题色调整
在样式文件中修改主色调：
```scss
$primary-color: #ff6600; // 可改为其他颜色
```

### 2. 配送费配置
```typescript
const deliveryFee = ref(3) // 修改配送费
```

## 注意事项

1. **API 适配**: 需要根据实际后端 API 调整数据字段
2. **权限控制**: 需要添加用户登录验证
3. **错误处理**: 已包含基础错误处理，可根据需要扩展
4. **数据验证**: 购物车商品验证由公共订单页面处理
5. **支付集成**: 由公共订单页面统一处理支付功能

## 兼容性

- ✅ 微信小程序
- ✅ 支付宝小程序  
- ✅ H5
- ✅ App 