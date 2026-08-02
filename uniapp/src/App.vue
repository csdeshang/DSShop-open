<script setup lang="ts">
import { onLaunch, onShow, onHide } from "@dcloudio/uni-app";
onLaunch(() => {
  console.log("App Launch");
});
onShow((options) => {

  // 设置 推荐人id 注册及下单
  const referrer_id = options?.query?.referrer_id;
  if (referrer_id) {
    uni.setStorageSync("referrer_id", referrer_id);
  }
});
onHide(() => {
  console.log("App Hide");
});
</script>
<style></style>
