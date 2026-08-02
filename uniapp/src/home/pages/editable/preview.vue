<template>
  <view>

    <view v-for="(config, index) in elementsConfig" :key="index" class="drag-item"
      :class="{ 'selected': selectedElementIndex === index }" draggable="true" @dragstart="dragStart(index, $event)"
      @dragover.prevent @dragenter.prevent @drop="drop(index, $event)" @click="selectElement(index)">

      <!-- 拖拽手柄 -->
      <view class="drag-handle">
        [{{ index + 1 }}]{{ config.title }}
      </view>

      <!-- 组件列表 -->
      <DiyList :config="config" />

      <!-- 遮罩层 -->
      <view class="overlay"></view>

      
    </view>


    <!-- <view>接收到的父组件数据:</view> -->
    <!-- <view>{{ JSON.stringify(parentData) }}</view> -->

  </view>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, reactive } from 'vue';
import DiyList from './diy-list.vue';




// 用于存储来自父组件的数据
const parentData = reactive({});

// 提取元素配置到单独的响应式数组，方便操作
const elementsConfig = ref([]);

// 当前选中的元素索引
const selectedElementIndex = ref(-1);

// 拖拽相关变量
const draggedItemIndex = ref(null);

// 初始化 elementsConfig 和监听 selectedElementIndex 变化
watch(parentData, (newValue) => {
  if (newValue.elementsConfig) {
    elementsConfig.value = [...newValue.elementsConfig];
  }

  // 当父组件传来的 selectedElementIndex 变化时，更新本地的 selectedElementIndex
  if (newValue.selectedElementIndex !== undefined) {
    selectedElementIndex.value = newValue.selectedElementIndex;
  }
}, { deep: true, immediate: true });

// 发送消息到父组件
const sendMessage = () => {
  const data = {
    ...parentData,
    elementsConfig: elementsConfig.value,
    message: '组件排序已更新',
    timestamp: Date.now()
  };
  // 发送消息到父窗口
  window.parent.postMessage(JSON.stringify(data), '*');
};

// 接收父组件数据
const receiveData = (event) => {
  try {
    const data = JSON.parse(event.data);

    console.log('接收到的数据:', data);

    // 保存当前选中索引，以便在数据更新后恢复
    const currentSelectedIndex = data.selectedElementIndex !== undefined
      ? data.selectedElementIndex
      : selectedElementIndex.value;

    // 更新数据
    Object.assign(parentData, data);

    // 确保选中索引正确更新
    selectedElementIndex.value = currentSelectedIndex;
  } catch (error) {
    console.error('解析接收到的消息时出错:', error);
  }
};

// 选择元素
const selectElement = (index) => {
  selectedElementIndex.value = index;
  // 通知父组件当前选中的元素
  const data = {
    type: 'ELEMENT_SELECTED',
    selectedElementIndex: index,
    selectedElement: elementsConfig.value[index]
  };
  window.parent.postMessage(JSON.stringify(data), '*');
};

// 拖拽开始
const dragStart = (index, event) => {
  draggedItemIndex.value = index;
  // 设置拖拽效果
  event.dataTransfer.effectAllowed = 'move';
  // 添加一些数据到拖拽对象
  event.dataTransfer.setData('text/plain', index);
  // 选中当前拖拽的元素
  selectElement(index);
};

// 放置处理
const drop = (index, event) => {
  event.preventDefault();
  if (draggedItemIndex.value !== null && draggedItemIndex.value !== index) {
    // 创建新数组并重新排序
    const newElementsConfig = [...elementsConfig.value];
    const draggedItem = newElementsConfig[draggedItemIndex.value];

    // 从原位置删除
    newElementsConfig.splice(draggedItemIndex.value, 1);
    // 插入到新位置
    newElementsConfig.splice(index, 0, draggedItem);

    // 更新数组
    elementsConfig.value = newElementsConfig;

    // 更新选中的索引
    selectedElementIndex.value = index;

    // 自动发送更新后的数据到父组件
    const data = {
      type: 'ELEMENTS_REORDERED',
      elementsConfig: elementsConfig.value,
      selectedElementIndex: selectedElementIndex.value
    };
    window.parent.postMessage(JSON.stringify(data), '*');
  }
  draggedItemIndex.value = null;
};

// 监听来自父组件的消息
onMounted(() => {
  window.addEventListener('message', receiveData);

  // 通知父组件子组件已加载完成
  setTimeout(() => {
    window.parent.postMessage(JSON.stringify({
      type: 'PREVIEW_MOUNTED',
      status: 'ready'
    }), '*');
  }, 300);
});

onBeforeUnmount(() => {
  // 移除事件监听
  window.removeEventListener('message', receiveData);
});
</script>

<style scoped>
.drag-item {
  border: 2px solid transparent;
  position: relative;
  /* 使子元素可以使用绝对定位 */
  cursor: move;
}

.drag-item.selected {
  border: 2px solid #1890ff;
  box-shadow: 0 0 5px rgba(24, 144, 255, 0.5);
}

.drag-handle {
  position: absolute;
  /* 使用绝对定位 */
  top: 50%;
  /* 距离顶部 50% */
  right: 0;
  /* 距离右侧 0 */
  transform: translateY(-50%);
  /* 将元素向上移动自身高度的一半，以实现垂直居中 */
  display: flex;
  align-items: center;
  padding: 5px;
  background-color: #000;
  opacity: 0.7;
  color: #fff;
  border-radius: 5px;
  z-index: 1000;
}
.overlay {
  position: absolute; /* 绝对定位 */
  top: 0;
  left: 0;
  right: 0;
  bottom: 0; /* 覆盖整个父元素 */
  background-color: rgba(255, 255, 255, 0); /* 透明背景 */
}
</style>