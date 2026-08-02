## 相关产品

### DSPlatform系统
DSPlatform系统是一个集成的解决方案，基于Thinkphp8 + Vue3 + Element Plus + Uniapp 开发，系统支持多种商业模式，包括传统商城购物、外卖配送、上门服务、上门家政、短视频带货等，满足不同行业的业务需求。无论是电商平台、本地生活服务、家政服务还是内容电商，都能快速构建完整的业务体系。
- <a href="http://help.dsplatform.csdeshang.com/" target="_blank">DSPlatform帮助手册  http://help.dsplatform.csdeshang.com/</a>
- <a href="https://gitee.com/csdeshang/DSPlatform_open" target="_blank">DSPlatform系统  https://gitee.com/csdeshang/DSPlatform_open/</a>

### DSMall商城系统
DSMall商城系统是基于ThinkPhp6.0和Uniapp开发的一套完善的B2B2C（多店铺商城）电商系统。它能够快速积累客户和会员数据，进行智能转化，提升销售和会员维护效率。主要功能包括拼团、砍价、秒杀、优惠券、积分、分销、刮刮卡等，适合企业进行二次开发。

### DSO2O外卖系统
DSO2O外卖系统专为餐饮行业设计，基于ThinkPhp6.0和Uniapp开发，支持线上点餐和线下配送。系统提供用户友好的界面，方便顾客快速下单，同时支持商家管理订单和配送。该系统还集成了实时数据分析功能，帮助商家优化运营策略。

---


## DSShop 平台自营商城 + 外卖 + 上门服务系统

DSShop 是一套完善的平台自营 **B2C** 电子商务解决方案，基于 ThinkPHP 8 + Vue3 + Element Plus + UniApp 开发。平台统一运营商品与订单，支持商城购物、外卖配送、上门家政、教育等内容业务，面向品牌方与单主体运营场景，可快速搭建自营商城与本地生活服务体系。


## DSShop 导航栏目
- <a href="http://help.dsplatform.csdeshang.com/" target="_blank">帮助手册 http://help.dsplatform.csdeshang.com/</a>
- <a href="http://www.csdeshang.com" target="_blank">官网地址 http://www.csdeshang.com</a>


## DSShop 演示 Demo
| 演示角色 | 演示地址 | 账号 | 密码 |
|-------|-------------------------------------|----|----|
| 管理后台 | https://dsshop.admin.csdeshang.com | test | 123456 |
| 用户手机端 | https://dsshop.uniapp.csdeshang.com | test | 123456 |
| 骑手手机端 | https://dsshop.uniapp.csdeshang.com/rider/pages/index/index | test | 123456 |
| 师傅手机端 | https://dsshop.uniapp.csdeshang.com/technician/pages/index/index | test | 123456 |


## 项目介绍

DSShop 是基于现代化技术栈构建的平台自营多端商城系统，支持管理后台、用户 PC 端、H5、小程序、APP 等。系统采用前后端分离架构，由平台统一管理商品、订单、营销与售后，无商户入驻与多店铺选店流程。

### 核心特性

- **现代化技术栈** - PHP 8.0+ + ThinkPHP 8.0 + Vue 3 + TypeScript
- **多端支持** - 管理后台、用户 PC（Nuxt）、H5、微信小程序、APP
- **平台自营 B2C** - 单主体运营，统一商品、订单与结算，无多商户入驻
- **多业务平台** - 支持商城、外卖、家政、教育等业务模块按需启用
- **完整支付体系** - 集成微信支付、支付宝等主流支付方式
- **精美 UI 设计** - 基于 Element Plus 和 UniApp 的现代化界面
- **安全可靠** - JWT 认证、权限控制、数据加密等安全机制
- **高度可扩展** - 模块化设计，便于二次开发

### 适用场景

- **品牌自营商城** - 品牌方 / 企业自营 B2C 商城
- **本地生活服务** - 外卖配送、上门家政等 O2O 场景
- **新零售** - 线上线下一体化零售
- **会员与分销** - 积分商城、分销推广等增长玩法
- **垂直行业** - 教育等内容类垂直电商

## 思维导图

系统提供完整的多端业务功能，包含管理端、用户端、骑手端、师傅端等角色模块（平台自营，无独立商户端）：

![功能思维导图](md-images/思维导图.png)


## 技术架构

### 技术栈

#### 后端技术
- **框架**: ThinkPHP 8.0
- **语言**: PHP 8.0+
- **数据库**: MySQL 8.0+
- **文件存储**: 本地存储 / 阿里云 OSS / 腾讯云 COS

#### 前端技术
- **管理后台**: Vue 3 + Element Plus + Vite（`vue-admin`）
- **用户 PC 端**: Nuxt（`nuxt-consumer`）
- **移动端**: UniApp + Vue 3（`uniapp`）
- **构建工具**: Vite 5.2+
- **状态管理**: Pinia
- **HTTP 客户端**: Axios
- **样式**: SCSS + Tailwind CSS

#### 第三方集成
- **支付**: 微信支付、支付宝
- **短信**: 阿里云短信、腾讯云短信
- **地图**: 腾讯地图、高德地图、百度地图

### 系统架构图

```
┌─────────────────────────────────────────────────────────────┐
│                        用户层                                │
├─────────────┬─────────────┬─────────────┬─────────────────────┤
│  PC管理后台  │  用户PC端   │   H5/小程序  │      APP应用        │
│ Vue3+Element│    Nuxt     │ UniApp+Vue3 │    UniApp+Vue3     │
└─────────────┴─────────────┴─────────────┴─────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                      API 网关层                              │
├─────────────┬─────────────┬─────────────┬─────────────────────┤
│   管理端API  │   用户端API  │   骑手端API  │     师傅端API       │
│  AdminAPI   │   UserAPI   │  RiderAPI   │   TechnicianAPI    │
└─────────────┴─────────────┴─────────────┴─────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                    业务服务层                                │
├─────────────┬─────────────┬─────────────┬─────────────────────┤
│   用户服务   │   商品服务   │   订单服务   │     支付服务        │
│   权限服务   │   营销服务   │   物流服务   │     消息服务        │
└─────────────┴─────────────┴─────────────┴─────────────────────┘
                                │
┌─────────────────────────────────────────────────────────────┐
│                    数据存储层                                │
├─────────────┬─────────────┬─────────────┬─────────────────────┤
│    MySQL    │    Redis    │   文件存储   │     第三方服务      │
│   主从复制   │   缓存队列   │  OSS/本地   │   微信/支付宝/物流   │
└─────────────┴─────────────┴─────────────┴─────────────────────┘
```

## 快速开始

### 环境要求

#### 基础环境
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Redis**: 6.0+
- **Node.js**: 16.0+
- **Nginx**: 1.20+ (推荐)

#### 推荐配置
- **服务器**: 2核4G内存起步，4核8G内存推荐
- **存储**: SSD硬盘，至少50GB可用空间
- **网络**: 带宽5M以上，推荐10M+

### 安装部署

#### 1. 克隆项目

```bash
git clone https://github.com/your-org/dsshop.git
cd dsshop
```

#### 2. 后端部署

```bash
# 进入后端目录
cd php-server

# 安装 PHP 依赖
composer install

# 访问配置域名进入安装向导完成安装
```

#### 3. 管理后台部署

```bash
# 进入管理后台目录
cd vue-admin

# 安装依赖
npm install

# 按需配置 .env.development / .env.production 中的 API 地址

# 开发环境启动
npm run dev

# 生产环境构建
npm run build
```

#### 4. 移动端部署

```bash
# 进入移动端目录
cd uniapp

# 安装依赖
npm install

# 按需配置 .env.development / .env.production 中的 API 地址

# 开发环境启动（H5）
npm run dev:h5

# 构建小程序
npm run build:mp-weixin

# 构建 APP
npm run build:app
```

#### 5. 用户 PC 端部署（可选）

```bash
cd nuxt-consumer
npm install
# 配置 API 地址后启动 / 构建
npm run dev
```


## 致谢

感谢以下开源项目的支持：

- [ThinkPHP](https://www.thinkphp.cn/) - 高性能 PHP 框架
- [Vue.js](https://vuejs.org/) - 渐进式 JavaScript 框架
- [Element Plus](https://element-plus.org/) - Vue 3 组件库
- [UniApp](https://uniapp.dcloud.io/) - 跨平台应用开发框架
- [Nuxt](https://nuxt.com/) - Vue 全栈框架
- [EasyWeChat](https://www.easywechat.com/) - 微信 SDK
