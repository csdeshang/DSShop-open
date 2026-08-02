export function goGoodsDetailPage(goods: any) {
    // 判断平台
    if(goods.platform){
        uni.navigateTo({ url: `/home/platform/${goods.platform}/pages/goods/detail/index?goods_id=${goods.id}` });
    }else{
        uni.navigateTo({ url: `/home/pages/goods/detail?goods_id=${goods.id}` });
    }
}


export function goPlatformIndexPage(platform: any) {
    // 判断平台
    if(platform){
        uni.navigateTo({ url: `/home/platform/${platform}/pages/index` });
    }else{
        uni.navigateTo({ url: `/home/pages/index/index` });
    }
}
