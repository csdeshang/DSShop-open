<template>

    <view>

        <view class="pay-amount">
            <view class="price">￥</view>
            <view class="amount">{{ payData.pay_amount }}</view>
        </view>


        <view class="payment-list">
            <view class="payment-item" v-for="item in paymentList" :key="item.id"
                @click="handleSelectPayment(item.payment_channel)">
                <view class="left">
                    <view class="mr-20">
                        <image :src="fetchRemoteIconUrl(item.payment_channel_icon)" mode="widthFix"
                            style="width:30px;height:30px;"></image>
                    </view>
                    <view class="payment-name">
                        {{ item.payment_name }}
                    </view>
                </view>
                <view>
                    <uni-icons :type="payData.pay_channel === item.payment_channel ? 'checkbox-filled' : 'circle'"
                        size="24" :color="payData.pay_channel === item.payment_channel ? '#ff5722' : '#4e545d'">
                        <!-- 动态颜色 -->
                    </uni-icons>
                </view>
            </view>
        </view>

        <view class="p-30" style="position: fixed;bottom: 0;left: 0;right: 0;">
            <button class="pay-btn" type="warn" :loading="loading" @click="goToPay">确认支付</button>
        </view>

    </view>



</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { goToPage, getScene } from '@/utils/util'
import { fetchRemoteIconUrl } from '@/utils/image'
import { getPaymentList, tradePay } from '@/home/api/trade/trade';
import { onLoad } from '@dcloudio/uni-app';
import { checkUserWechatAuth } from '@/shared/api/login/wechatLogin';
import wechatAuthUtil from '@/utils/wechatAuthUtil';



// 支付场景 H5 APP 小程序
const payScene = ref(getScene());

const uni_redirect_url = ref('');

const payData = reactive({
    // 来源类型  充值 订单支付
    source_type: '',
    // 来源id  充值id 订单id
    source_id: '',
    // 支付金额
    pay_amount: '',
    // 支付渠道
    pay_channel: '',
    // 支付场景
    pay_scene: payScene.value,
});



// 加载支付方式
const loading = ref(false);
const pay_info = ref('');





// 支付方式列表
const paymentList = ref([]);


// 选择支付方式
const handleSelectPayment = (pay_channel: string) => {
    // 更新选中的支付项的 pay_channel
    payData.pay_channel = pay_channel;
};

// 获取支付方式
const fetchPaymentList = () => {
    getPaymentList({
        pay_scene: payScene.value,
        pay_type: payData.source_type
    }).then((res) => {
        paymentList.value = res.data;

        if (paymentList.value[0].payment_channel !== undefined) {
            // 默认选中第一个项
            payData.pay_channel = paymentList.value[0].payment_channel;
        } else {
            console.error("第一个支付项没有 pay_channel 属性");
        }
    });
}



// 处理微信支付
const handleWechatPay = (payResult: any) => {
    // 从 trade_pay_result 获取第三方支付返回的原始数据
    const tradePayResult = payResult.trade_pay_result;

    // #ifdef H5
    if (payScene.value === 'h5') {
        // 'h5' 表示普通浏览器（非微信浏览器），H5支付跳转到微信支付页面，不需要openid
        if (tradePayResult.pay_url) {
            window.location.href = tradePayResult.pay_url;
        } else {
            uni.showToast({
                title: '支付数据异常',
                icon: 'none'
            });
        }
        return;
    }
    // #endif


    // 公众号JSAPI支付
    if (payScene.value === 'wechat_official') {
        // 定义支付函数
        function onBridgeReady() {
            WeixinJSBridge.invoke('getBrandWCPayRequest', {
                "appId": tradePayResult.appId,     // 公众号ID
                "timeStamp": tradePayResult.timeStamp, // 时间戳
                "nonceStr": tradePayResult.nonceStr,   // 随机串
                "package": tradePayResult.package,     // 预支付交易会话标识
                "signType": "RSA",               // 微信签名方式
                "paySign": tradePayResult.paySign     // 微信签名
            }, function (res) {
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    // 支付成功
                    handlePaySuccess();
                } else if (res.err_msg == "get_brand_wcpay_request:cancel") {
                    // 用户取消支付
                    uni.showToast({
                        title: '支付已取消',
                        icon: 'none'
                    });
                } else {
                    // 支付失败
                    uni.showToast({
                        title: '支付失败',
                        icon: 'none'
                    });
                }
            });
        }

        // 检查WeixinJSBridge是否存在
        if (typeof WeixinJSBridge == "undefined") {
            if (document.addEventListener) {
                document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
            } else if (document.attachEvent) {
                document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
            }
        } else {
            onBridgeReady();
        }
        return;
    }

    // #ifdef MP-WEIXIN
    // 小程序支付
    uni.requestPayment({
        provider: 'wxpay',
        timeStamp: tradePayResult.timeStamp,
        nonceStr: tradePayResult.nonceStr,
        package: tradePayResult.package,
        signType: tradePayResult.signType,
        paySign: tradePayResult.paySign,
        success: function () {
            handlePaySuccess();
        },
        fail: function (err) {
            console.error('微信支付失败', err);
            uni.showToast({
                title: '支付失败',
                icon: 'none'
            });
        }
    });
    // #endif

    // #ifdef APP-PLUS
    // App微信支付
    uni.requestPayment({
        provider: 'wxpay',
        orderInfo: tradePayResult, // 微信支付需要的订单信息
        success: function () {
            handlePaySuccess();
        },
        fail: function (err) {
            console.error('微信支付失败', err);
            uni.showToast({
                title: '支付失败',
                icon: 'none'
            });
        }
    });
    // #endif
}

// 处理支付宝支付
const handleAlipayPay = (payResult: any) => {
    // 从 trade_pay_result 获取第三方支付返回的原始数据
    const tradePayResult = payResult.trade_pay_result;
    
    // #ifdef H5
    if (payScene.value === 'h5' || payScene.value === 'pc') {
        // H5支付宝支付，通常是表单提交
        if (typeof tradePayResult === 'string') {
            const div = document.createElement('div');
            div.innerHTML = tradePayResult;
            document.body.appendChild(div);
            const form = document.forms[0];
            if (form) {
                form.submit();
            } else {
                uni.showToast({
                    title: '支付宝支付表单生成失败',
                    icon: 'none'
                });
            }
        } else {
            uni.showToast({
                title: '支付宝支付数据格式异常',
                icon: 'none'
            });
        }
        return;
    }
    // #endif

    // #ifdef MP-ALIPAY
    // 支付宝小程序支付
    my.tradePay({
        tradeNO: tradePayResult.trade_no,
        success: function (res) {
            if (res.resultCode === '9000') {
                handlePaySuccess();
            } else {
                uni.showToast({
                    title: '支付失败',
                    icon: 'none'
                });
            }
        },
        fail: function (err) {
            uni.showToast({
                title: '支付失败',
                icon: 'none'
            });
        }
    });
    // #endif

    // #ifdef APP-PLUS
    // App支付宝支付
    uni.requestPayment({
        provider: 'alipay',
        orderInfo: tradePayResult.orderInfo, // 支付宝支付需要的订单信息字符串
        success: function () {
            handlePaySuccess();
        },
        fail: function (err) {
            uni.showToast({
                title: '支付失败',
                icon: 'none'
            });
        }
    });
    // #endif
}


// 去支付
const goToPay = async () => {
    if (payData.pay_channel === '') {
        uni.showToast({
            title: '支付方式不能为空',
            icon: 'none'
        })
        return
    }

    if (payData.pay_amount === '') {
        uni.showToast({
            title: '金额不能为空',
            icon: 'none'
        })
        return
    }

    if (payData.source_type === '') {
        uni.showToast({
            title: '来源类型不能为空',
            icon: 'none'
        })
        return
    }

    // 检查用户是否已授权微信
    const isAuthorized = await checkUserOpenid(payData);
    if (!isAuthorized) {
        return;
    }


    loading.value = true;
    tradePay(payData).then((res: any) => {
        loading.value = false;

        if (res.code === 10000) {
            // 根据支付渠道处理不同的支付方式
            if (payData.pay_channel === 'wechat_pay') {
                // 微信支付
                handleWechatPay(res.data);
            } else if (payData.pay_channel === 'ali_pay') {
                // 支付宝支付
                handleAlipayPay(res.data);
            } else if (payData.pay_channel === 'balance_pay') {
                // 余额支付 直接支付成功
                handlePaySuccess();
            } else {
                // 其他支付方式
                uni.showToast({
                    title: '暂不支持该支付方式',
                    icon: 'none'
                });
            }
        } else {
            // 支付失败
            uni.showToast({
                title: res.message || '支付失败',
                icon: 'none'
            });
        }

    }).catch((err: any) => {
        loading.value = false;
        console.error('支付失败', err);
        uni.showToast({
            title: err.message || '支付失败',
            icon: 'none'
        });
    });

}


// 将支付场景映射到微信登录场景
const mapPaySceneToWechatScene = (payScene: string): 'wechat_official' | 'wechat_mini' | 'wechat_app' | undefined => {
    if (payScene === 'wechat_official') {
        return 'wechat_official'
    } else if (payScene === 'wechat_mini') {
        return 'wechat_mini'
    } else if (payScene === 'h5') {
        // 'h5' 表示普通浏览器（非微信浏览器），H5支付不需要openid，直接跳转到微信支付页面
        // 注意：如果在微信浏览器内，getScene() 会返回 'wechat_official'，使用JSAPI支付
        return 'wechat_official'
    } else if (payScene === 'app') {
        return 'wechat_app'
    }
    return undefined
}

// 检查用户是否已授权微信
const checkWechatAuth = async () => {
    try {
        const wechatScene = mapPaySceneToWechatScene(payData.pay_scene)
        if (!wechatScene) {
            return false
        }
        
        const res = await checkUserWechatAuth({
            scene: wechatScene
        });

        if (res.code === 10000) {
            return res.data;
        } else {
            return false;
        }


    } catch (error) {
        console.error('检查微信授权失败', error);
        return false;
    }
};

// 微信支付需要openid 判断用户是否绑定openid
const checkUserOpenid = async (payData: any) => {
    loading.value = true;


    // 微信支付
    if (payData.pay_channel === 'wechat_pay') {
        // 微信公众号、小程序支付需要 openid
        // 注意：'h5' 表示普通浏览器（非微信浏览器），H5支付不需要openid，但这里保留检查以便后续扩展
        if (payData.pay_scene === 'wechat_official' || payData.pay_scene === 'wechat_mini' || payData.pay_scene === 'h5') {

            const isAuthorized = await checkWechatAuth();

            if (!isAuthorized) {

                // 弹出确认框询问用户是否授权
                uni.showModal({
                    title: '授权提示',
                    content: '微信支付需要获取您的微信信息，是否授权？',
                    success: (res) => {
                        if (res.confirm) {
                            let redirect_url = ''
                            if (payScene.value === 'wechat_official') {
                                redirect_url = window.location.href
                            }
                            // 用户同意，使用无感授权
                            const wechatScene = mapPaySceneToWechatScene(payData.pay_scene)
                            if (wechatScene) {
                                wechatAuthUtil.authorize({
                                    redirect_url: redirect_url,
                                    scene: wechatScene,
                                    mode: 'getOpenid',
                                });
                            }


                        } else {
                            uni.showToast({
                                title: '您取消了授权，无法完成微信支付',
                                icon: 'none'
                            });
                            loading.value = false;
                            return false;
                        }
                    }
                });
            } else {
                return true;
            }
        }
    }

    return true;



}


// 支付成功后统一使用 redirectTo
const handlePaySuccess = () => {
    const message = payData.source_type === 'recharge' ? '充值成功' : '支付成功';
    uni.showToast({
        title: message,
        icon: 'success'
    });

    setTimeout(() => {

        if (uni_redirect_url.value) {
            // 如果存在跳转地址，则直接跳转
            // 先解码URL，避免双重编码
            const decodedUrl = decodeURIComponent(uni_redirect_url.value);
            goToPage({
                url: decodedUrl,
                type: 'redirectTo'
            });
        } else {
            // 根据来源类型跳转到不同页面
            let redirectUrl = '/home/pages/user/index/index';

            if (payData.source_type === 'recharge') {
                redirectUrl = '/home/pages/user/balance/index';
            } else if (payData.source_type === 'order') {
                redirectUrl = '/home/pages/user/order/index';
            }
            goToPage({
                url: redirectUrl,
                type: 'redirectTo'  // 使用 redirectTo 防止返回
            });
        }



    }, 1500);
};




onLoad((option: any) => {
    // 在iOS环境下记录初始URL(解决微信JSSDK签名问题)
    // #ifdef H5
    if (uni.getSystemInfoSync().platform == 'ios') {
        uni.setStorageSync('initUrl', window.location.href.split('#')[0]);
    }
    // #endif

    payData.source_type = option.source_type;
    payData.source_id = option.source_id;
    payData.pay_amount = option.pay_amount;
    pay_info.value = option.pay_info;

    // 获取支付成功跳转页面
    if (option.uni_redirect_url) {
        uni_redirect_url.value = option.uni_redirect_url;
    }

    fetchPaymentList();

    // 处理从微信授权页面返回的情况
    if (option.code) {
        // 处理微信授权回调
        wechatAuthUtil.handleOfficialCallback({
            code: option.code,
            mode: 'getOpenid',
        })
    }
})


</script>


<style lang="scss" scoped>
.pay-amount {
    padding-bottom: 30rpx;
    padding-top: 30rpx;
    text-align: center;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;

    .price {
        font-size: 30rpx;
    }

    .amount {
        font-size: 50rpx;
        font-weight: bold;
    }
}

.payment-list {
    display: flex;
    flex-direction: column;

    .payment-item {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        padding: 30rpx;

        .left {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex: 1;

            .payment-name {
                flex: 1;
            }
        }
    }
}
</style>
