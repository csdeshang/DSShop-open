/**
 * 统一处理认证页面跳转
 * @param type 认证类型：login/register/reset
 * @param redirect 重定向URL
 */
export function navigateToAuth(type: 'login' | 'register' | 'reset', redirect?: string) {
  const pages = {
    login: '/shared/pages/auth/login/index',
    register: '/shared/pages/auth/register/index',
    reset: '/shared/pages/auth/resetpwd/index'
  };

  let url = pages[type];

  // 如果没有提供重定向URL，则获取当前页面URL
  if (!redirect) {
    let systemType = import.meta.env.VITE_APP_SYSTEM_TYPE
    if (systemType == 'user') {
      redirect = '/home/pages/index'
    } else if (systemType == 'rider') {
      redirect = '/rider/pages/index'
    } else {
      redirect = '/home/pages/index'
    }
  }

  // 添加重定向参数
  url += `?redirect=${encodeURIComponent(redirect)}`;

  uni.navigateTo({ url });
}
/**
 * 获取当前页面完整路径（包含参数）
 */
export function getCurrentPageUrl(): string {
  const pages = getCurrentPages();
  const currentPage = pages[pages.length - 1];
  const url = `/${currentPage.route}`;

  const queryParams = currentPage.options;
  if (Object.keys(queryParams).length > 0) {
    const queryString = Object.keys(queryParams)
      .map(key => `${key}=${queryParams[key]}`)
      .join('&');
    return `${url}?${queryString}`;
  }

  return url;
}


/**
 * 获取当前路由
 */
export const currRoute = () => {
  const pages = getCurrentPages()
  const route = pages[pages.length - 1]
  return route ? route.route : ''
}

/**
 * 判断是否是url
 * @param str
 * @returns
 */
export function isUrl(str: string): boolean {
  return str && (str.indexOf('http://') != -1 || str.indexOf('https://') != -1) || false
}

/**
 * 判断是否是微信浏览器
 */
export function isWeixinBrowser(): boolean {
  // #ifndef H5
  return false
  // #endif
  let ua = navigator.userAgent.toLowerCase()
  return /micromessenger/.test(ua) ? true : false
}


/**
 * 获取应用场景值  与服务端的  getPaymentSceneDesc 一致
 * 
 * 场景说明：
 * - 'wechat_official': 微信浏览器内（使用JSAPI支付，需要openid）
 * - 'h5': 普通浏览器（非微信浏览器，H5支付，跳转到微信支付页面，不需要openid）
 * - 'wechat_mini': 微信小程序
 * - 'app': APP应用
 */
export function getScene(): string {


  // #ifdef APP-PLUS
  return 'app'
  // #endif
  // #ifdef MP-WEIXIN
  return 'wechat_mini'
  // #endif
  // #ifdef WEB
  // 微信浏览器内返回 'wechat_official'，普通浏览器返回 'h5'
  return isWeixinBrowser() ? 'wechat_official' : 'h5'
  // #endif
}


/**
 * 处理非图片文件URL（视频、音频、文档等），根据存储类型补全完整路径
 * 注意：图片文件请使用 formatImageUrl 函数，以获得缩略图处理能力
 * @param url 原始地址（视频、音频、文档等文件URL）
 * @returns 完整的文件附件URL
 */
export function formatFileUrl(url: string): string {
  // 处理空URL情况
  if (!url) return '';

  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }

  // 获取环境变量中的基础URL，确保有默认值
  const fileBaseUrl = import.meta.env.VITE_FILE_BASE_URL || '';

  // 处理URL路径，确保正确添加斜杠
  return fileBaseUrl + (url.startsWith('/') ? url : '/' + url);
}




/**
 * 优化的页面跳转方法
 * @param options 导航选项对象
 */
export function goToPage(options: {
  url: string;
  params?: any;
  type?: 'redirectTo' | 'navigateTo' | 'reLaunch' | 'switchTab'; // 跳转类型，默认 redirectTo
}) {
  const { url, params, type = 'navigateTo' } = options;
  let fullUrl = url;

  if (params) {
    // 使用兼容的方式构建查询参数
    const queryString = Object.keys(params)
      .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
      .join('&');
    fullUrl = `${url}?${queryString}`;
  }

  // 根据类型选择跳转方式
  switch (type) {
    case 'redirectTo':
      uni.redirectTo({ url: fullUrl });
      break;
    case 'reLaunch':
      uni.reLaunch({ url: fullUrl });
      break;
    case 'switchTab':
      uni.switchTab({ url: fullUrl });
      break;
    case 'navigateTo':
    default:
      const pages = getCurrentPages();
      if (pages.length >= 9) {
        // console.warn(`页面栈深度已达到 ${stackDepth} 级，自动使用 redirectTo`);
        uni.redirectTo({ url: fullUrl });
      } else {
        uni.navigateTo({ url: fullUrl });
      }
      break;
  }
}




/**
 * 防抖函数
 * @param {Function} func - 需要防抖处理的函数
 * @param {number} wait - 延迟时间，单位为毫秒
 * @param {boolean} immediate - 是否立即执行
 * @returns {Function} - 返回一个新的防抖处理函数
 */
export function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number = 300,
  immediate: boolean = false
): T & { cancel: () => void } {
  let timeout: ReturnType<typeof setTimeout> | null = null;

  const debounced = function (this: any, ...args: Parameters<T>) {
    const context = this;

    if (timeout) clearTimeout(timeout);

    if (immediate && !timeout) {
      func.apply(context, args);
    }

    timeout = setTimeout(() => {
      if (!immediate) {
        func.apply(context, args);
      }
      timeout = null;
    }, wait);
  };

  debounced.cancel = () => {
    if (timeout) {
      clearTimeout(timeout);
      timeout = null;
    }
  };

  return debounced as T & { cancel: () => void };
}

/**
 * 格式化富文本内容 - 小程序图片自适应
 * @param content 富文本内容
 * @returns 格式化后的富文本内容
 */
export function formatRichText(content: string): string {
  if (!content) return '';
  
  // 处理图片自适应 - 小程序必须使用内联样式
  let processedContent = content.replace(/<img([^>]*?)(?:\s*\/)?>/gi, (match, attrs) => {
    // 移除可能存在的 style 属性
    const cleanAttrs = attrs.replace(/style\s*=\s*["'][^"']*["']/gi, '').trim();
    // 确保属性前有空格
    const spacedAttrs = cleanAttrs ? ` ${cleanAttrs}` : '';
    // 添加小程序兼容的样式 - 强制限制宽度
    return `<img${spacedAttrs} style="max-width:100% !important;width:100% !important;height:auto !important;display:block !important;margin:20rpx 0 !important;">`;
  });
  
  // 处理视频自适应
  processedContent = processedContent.replace(/<video([^>]*?)(?:\s*\/)?>/gi, (match, attrs) => {
    const cleanAttrs = attrs.replace(/style\s*=\s*["'][^"']*["']/gi, '').trim();
    const spacedAttrs = cleanAttrs ? ` ${cleanAttrs}` : '';
    return `<video${spacedAttrs} style="max-width:100% !important;width:100% !important;height:auto !important;">`;
  });
  
  return processedContent;
}

