import { getToken, setToken } from './auth'
import useUserStore from '@/stores/user'
import { refreshToken } from '@/shared/api/login/login'
import { getCurrentPageUrl } from './util'

// 定义API基础URL常量
export const API_BASE_URLS = {
  ADMIN: 'adminapi',     // 后台接口
  USER: 'api',           // 用户接口
  RIDER: 'riderapi',     // 骑手接口
  TECHNICIAN: 'techapi', // 师傅接口
  BLOGGER: 'bloggerapi', // 博主接口
}

// 定义请求配置接口
export interface RequestConfig {
  url: string
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH'
  data?: any
  params?: any
  header?: Record<string, any>
  timeout?: number
  dataType?: string
  responseType?: string
  showErrorMessage?: boolean   // 是否显示错误消息
  showSuccessMessage?: boolean // 是否显示成功消息
}

// 定义API响应接口
export interface ApiResponse<T = any> {
  code: number
  message: string
  data: T
}

/**
 * HTTP请求类
 * 封装uni.request，处理请求拦截、响应拦截、错误处理、token刷新等功能
 */
class Request {
  private baseURL: string
  private messageCache: Map<string, { timestamp: number }>
  private isRefreshing: boolean = false
  private refreshSubscribers: Array<(token: string) => void> = []

  constructor() {
    // 设置基础URL
    this.baseURL = this.normalizeBaseUrl(import.meta.env.VITE_APP_BASE_URL || '')

    // 初始化消息缓存
    this.messageCache = new Map()
  }

  /**
   * 规范化基础URL
   * @param url 基础URL
   * @returns 规范化后的URL
   */
  private normalizeBaseUrl(url: string): string {
    return url.endsWith('/') ? url : `${url}/`
  }

  /**
   * 处理请求配置
   * @param config 请求配置
   * @returns 处理后的请求配置
   */
  private processRequestConfig(config: RequestConfig): RequestConfig {
    // 合并默认配置
    const processedConfig: RequestConfig = {
      ...config,
      header: {
        'Content-Type': 'application/json',
        ...config.header
      },
      timeout: config.timeout || 30000
    }

    // 添加访问令牌
    const accessToken = getToken('access_token')
    if (accessToken) {
      processedConfig.header!['access-token'] = accessToken
    }

    return processedConfig
  }

  /**
   * 发送请求
   * @param config 请求配置
   * @returns Promise
   */
  private async sendRequest<T = any>(config: RequestConfig): Promise<ApiResponse<T>> {
    const processedConfig = this.processRequestConfig(config)

    return new Promise<ApiResponse<T>>((resolve, reject) => {
      // 使用类型断言支持 PATCH 方法（UniApp 类型定义可能不包含 PATCH，但运行时支持）
      uni.request({
        url: this.baseURL + processedConfig.url,
        method: (processedConfig.method || 'GET') as any,
        data: processedConfig.data,
        header: processedConfig.header,
        timeout: processedConfig.timeout,
        dataType: processedConfig.dataType || 'json',
        responseType: processedConfig.responseType,
        success: (res: any) => {
          this.handleSuccessResponse(res, processedConfig)
            .then(resolve)
            .catch(reject)
        },
        fail: (err: any) => {
          this.handleErrorResponse(err, processedConfig)
            .then(resolve)
            .catch(reject)
        }
      } as any)
    })
  }

  /**
   * 处理成功响应
   * @param response 响应对象
   * @param config 请求配置
   * @returns 处理后的响应数据
   */
  private async handleSuccessResponse(response: any, config: RequestConfig): Promise<any> {
    // 如果是二进制数据，直接返回
    if (config.responseType === 'arraybuffer') {
      return response.data
    }

    const res = response.data as ApiResponse

    // 处理HTTP状态码
    if (response.statusCode !== 200) {
      // 处理401错误（未授权）
      if (response.statusCode === 401) {
        return this.handleTokenRefresh(config)
      }

      // 处理403错误（拒绝访问）
      if (response.statusCode === 403) {
        // 优先使用响应体中的错误信息，如果没有则使用默认消息
        const errorMessage = res?.message || '权限不足'
        this.showMessage(errorMessage, 'error')

        // 使用工具函数获取当前页面路径
        const currentPath = getCurrentPageUrl()

        // 延迟登出，让用户看到提示
        setTimeout(() => {
          useUserStore().logout()
          uni.navigateTo({
            url: `/shared/pages/auth/login/index?redirect=${currentPath}`
          })
        }, 2000)

        // 返回统一的错误格式
        return res;
      } else {
        // 处理其他HTTP错误
        // 优先使用响应体中的错误信息，如果没有则使用默认消息
        const errorMessage = res?.message || this.getHttpErrorMessage(response.statusCode)
        this.showMessage(errorMessage, 'error')
        return res;
      }
    }



    // 业务逻辑错误处理
    if (res.code !== 10000) {
      if (config.showErrorMessage !== false) {
        this.showMessage(res.message, 'error')
      }
      // 返回业务错误响应，
      return res
    }

    // 显示成功消息
    if (config.showSuccessMessage) {
      this.showMessage(res.message, 'success')
    }

    return res
  }

  /**
   * 处理错误响应
   * @param error 错误对象
   * @param config 请求配置
   * @returns 统一的错误响应
   */
  private async handleErrorResponse(error: any, config: RequestConfig): Promise<any> {
    // 处理网络错误
    let errorMessage = '网络连接错误'

    if (error.errMsg) {
      if (error.errMsg.includes('timeout')) {
        errorMessage = '请求超时，请检查网络连接'
      } else if (error.errMsg.includes('fail')) {
        errorMessage = '网络错误，请检查您的互联网连接'
      }
    }

    this.showMessage(errorMessage, 'error')

    // 返回网络错误格式
    return {
      code: -1,
      message: errorMessage,
      data: null
    }
  }

  /**
   * 获取HTTP错误消息
   * @param status HTTP状态码
   * @returns 错误消息
   */
  private getHttpErrorMessage(status?: number): string {
    switch (status) {
      case 400: return '请求错误（400）'
      case 401: return '未授权，请重新登录（401）'
      case 403: return '拒绝访问（403）'
      case 404: return '请求的资源不存在（404）'
      case 408: return '请求超时（408）'
      case 500: return '服务器内部错误（500）'
      case 501: return '服务未实现（501）'
      case 502: return '网关错误（502）'
      case 503: return '服务不可用（503）'
      case 504: return '网关超时（504）'
      case 505: return 'HTTP版本不受支持（505）'
      default: return `请求失败，状态码：${status || '未知'}`
    }
  }

  /**
   * 处理Token刷新
   * @param config 请求配置
   * @returns 刷新token后的重试请求
   */
  private async handleTokenRefresh(config: RequestConfig): Promise<ApiResponse<any>> {
    // 如果已经在刷新token，则将请求添加到订阅者列表
    if (this.isRefreshing) {
      return new Promise((resolve) => {
        this.refreshSubscribers.push((token: string) => {
          config.header = config.header || {}
          config.header['access-token'] = token
          resolve(this.sendRequest(config))
        })
      })
    }

    this.isRefreshing = true

    try {
      // 获取刷新token
      const refreshTokenValue = getToken('refresh_token')
      if (!refreshTokenValue) {
        throw new Error('刷新令牌不存在')
      }

      // 调用刷新token API
      const response = await refreshToken({ refresh_token: refreshTokenValue })

      if (response && response.data.access_token) {
        // 更新token
        const newAccessToken = response.data.access_token
        const newRefreshToken = response.data.refresh_token

        setToken(newAccessToken, 'access_token')
        setToken(newRefreshToken, 'refresh_token')

        // 通知所有订阅者
        this.refreshSubscribers.forEach(callback => callback(newAccessToken))
        this.refreshSubscribers = []

        // 重试原始请求
        config.header = config.header || {}
        config.header['access-token'] = newAccessToken
        return this.sendRequest(config)  // 返回原始请求的结果
      } else {
        throw new Error('刷新令牌失败')
      }
    } catch (refreshError) {
      console.error('刷新令牌失败:', refreshError)
      // 登出用户
      useUserStore().logout()
      
      // 显示错误消息
      this.showMessage('登录已过期，请重新登录', 'error')
      
      // 返回统一的错误格式，保持一致性
      return {
        code: 401,
        message: '登录已过期，请重新登录',
        data: null
      }
    } finally {
      this.isRefreshing = false
    }
  }

  /**
   * 显示消息
   * @param message 消息内容
   * @param type 消息类型
   */
  private showMessage(message: string, type: 'success' | 'error' | 'warning' | 'info'): void {
    const cacheKey = message
    const cachedMessage = this.messageCache.get(cacheKey)
    const now = Date.now()

    // 防止短时间内显示相同的消息
    if (!cachedMessage || now - cachedMessage.timestamp > 5000) {
      this.messageCache.set(cacheKey, { timestamp: now })

      // 使用uni.showToast显示消息
      uni.showToast({
        title: message,
        icon: type === 'success' ? 'success' : 'none'
      })
    }
  }

  /**
   * 发送GET请求
   * @param url 请求URL
   * @param params 请求参数
   * @param config 请求配置
   * @returns Promise
   */
  public get<T = any>(url: string, params?: any, config?: Omit<RequestConfig, 'url' | 'method' | 'data'>): Promise<ApiResponse<T>> {
    return this.sendRequest<T>({
      url,
      method: 'GET',
      data: params,
      ...config
    })
  }

  /**
   * 发送POST请求
   * @param url 请求URL
   * @param data 请求数据
   * @param config 请求配置
   * @returns Promise
   */
  public post<T = any>(url: string, data?: any, config?: Omit<RequestConfig, 'url' | 'method' | 'data'>): Promise<ApiResponse<T>> {
    return this.sendRequest<T>({
      url,
      method: 'POST',
      data,
      ...config
    })
  }

  /**
   * 发送PUT请求
   * @param url 请求URL
   * @param data 请求数据
   * @param config 请求配置
   * @returns Promise
   */
  public put<T = any>(url: string, data?: any, config?: Omit<RequestConfig, 'url' | 'method' | 'data'>): Promise<ApiResponse<T>> {
    return this.sendRequest<T>({
      url,
      method: 'PUT',
      data,
      ...config
    })
  }

  /**
   * 发送DELETE请求
   * @param url 请求URL
   * @param config 请求配置
   * @returns Promise
   */
  public delete<T = any>(url: string, data?: any, config?: Omit<RequestConfig, 'url' | 'method' | 'data'>): Promise<ApiResponse<T>> {
    return this.sendRequest<T>({
      url,
      method: 'DELETE',
      data,
      ...config
    })
  }

  /**
   * 发送PATCH请求
   * @param url 请求URL
   * @param data 请求数据
   * @param config 请求配置
   * @returns Promise
   */
  public patch<T = any>(url: string, data?: any, config?: Omit<RequestConfig, 'url' | 'method' | 'data'>): Promise<ApiResponse<T>> {
    return this.sendRequest<T>({
      url,
      method: 'PATCH',
      data,
      ...config
    })
  }

  /**
   * 上传文件
   * @param url 请求URL
   * @param filePath 文件路径
   * @param name 文件对应的 key
   * @param formData 附加数据
   * @param config 请求配置
   * @returns Promise
   */
  public upload<T = any>(
    url: string,
    filePath: string,
    name: string = 'file',
    formData?: Record<string, any>,
    config?: Omit<RequestConfig, 'url'>
  ): Promise<ApiResponse<T>> {
    const processedConfig = this.processRequestConfig({
      url,
      ...config
    })

    return new Promise((resolve, reject) => {
      const uploadTask = uni.uploadFile({
        url: this.baseURL + url,
        filePath,
        name,
        formData,
        header: processedConfig.header,
        success: (res) => {
          // 上传接口返回的是字符串，需要转换为对象
          let data: ApiResponse<T>
          try {
            data = JSON.parse(res.data) as ApiResponse<T>
          } catch (e) {
            reject(new Error('解析响应数据失败'))
            return
          }

          if (data.code !== 10000) {
            if (processedConfig.showErrorMessage !== false) {
              this.showMessage(data.message, 'error')
            }
            reject(new Error(data.message || '上传失败'))
          } else {
            if (processedConfig.showSuccessMessage) {
              this.showMessage(data.message || '上传成功', 'success')
            }
            resolve(data)
          }
        },
        fail: (err) => {
          this.showMessage('文件上传失败', 'error')
          reject(err)
        }
      })

      // 监听上传进度
      uploadTask.onProgressUpdate((res) => {
        // 可以在这里处理上传进度
        console.log('上传进度', res.progress)
      })
    })
  }
}

// 导出请求实例
export default new Request()
