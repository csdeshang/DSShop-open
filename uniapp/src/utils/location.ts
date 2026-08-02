import { getCoordsByIp } from '@/api/system/sysLbs'


// 位置信息接口
interface LocationResult {
    latitude: number
    longitude: number
  }
  
  // IP定位处理函数
  function handleIpLocation(): Promise<LocationResult> {
    return getCoordsByIp().then(res => {
      if (res.code === 10000 && res.data) {
        return {
          latitude: res.data.latitude,
          longitude: res.data.longitude,
        }
      } else {
        uni.showToast({
          title: res.message,
          icon: 'none'
        })
        throw new Error('IP定位失败')
      }
    })
  }
  
  // 通用的 uni.getLocation 获取位置方法
  function getLocationByUni(isHighAccuracy: boolean = true): Promise<LocationResult> {
    return new Promise((resolve, reject) => {
      uni.getLocation({
        type: 'gcj02',
        altitude: false,
        isHighAccuracy,
        highAccuracyExpireTime: 3000,
        success: (res) => {
          resolve({
            latitude: res.latitude,
            longitude: res.longitude,
          })
        },
        fail: (err) => {
          if (isHighAccuracy) {
            // 高精度失败，尝试普通精度
            getLocationByUni(false).then(resolve).catch(reject)
          } else {
            reject(err)
          }
        }
      })
    })
  }
  
  // 小程序授权获取位置（专门处理小程序的授权逻辑）
  function getMiniProgramLocation(): Promise<LocationResult> {
    return new Promise((resolve, reject) => {
      // 先授权
      uni.authorize({
        scope: 'scope.userLocation',
        success() {
          // 授权成功，开始定位
          getLocationByUni().then(resolve).catch(reject)
        },
        fail(err) {
          // 授权失败，提示用户
          uni.showToast({
            title: '获取位置失败，请在设置中打开位置权限',
            icon: 'none'
          })
  
          if (err.errMsg.indexOf('authorize:fail') !== -1) {
            // 提示用户打开位置权限
            uni.showModal({
              title: '位置权限未开启',
              content: '获取位置失败，请在设置中打开位置权限',
              confirmText: '去设置',
              success(modalRes) {
                if (modalRes.confirm) {
                  // 打开设置页
                  uni.openSetting({
                    success(settingRes) {
                      if (settingRes.authSetting['scope.userLocation']) {
                        // 用户在设置页开启了权限，重新尝试
                        getMiniProgramLocation().then(resolve).catch(reject)
                      } else {
                        reject(new Error('用户未授权位置权限'))
                      }
                    },
                    fail() {
                      reject(new Error('打开设置页失败'))
                    }
                  })
                } else {
                  reject(new Error('用户取消授权'))
                }
              }
            })
          } else {
            reject(err)
          }
        }
      })
    })
  }
  
  // 获取当前位置（判断环境的主入口方法）
  export function getCurrentPosition(): Promise<LocationResult> {
    const { deviceModel } = uni.getSystemInfoSync()
  
    return new Promise((resolve, reject) => {
      // #ifdef H5
      if (deviceModel === 'PC') {
        // PC端直接使用IP定位
        handleIpLocation().then(resolve).catch(reject)
      } else {
        // H5移动端，直接定位，失败则IP定位
        getLocationByUni()
          .then(resolve)
          .catch(() => {
            uni.showToast({
              title: 'H5定位失败,使用IP定位',
              icon: 'none'
            })
            handleIpLocation().then(resolve).catch(reject)
          })
      }
      // #endif
  
      // #ifdef APP-PLUS
      // APP端，直接定位，失败则IP定位
      getLocationByUni()
        .then(resolve)
        .catch(() => {
          uni.showToast({
            title: 'APP定位失败,使用IP定位',
            icon: 'none'
          })
          handleIpLocation().then(resolve).catch(reject)
        })
      // #endif
  
      // #ifdef MP-WEIXIN || MP-ALIPAY || MP-BAIDU || MP-TOUTIAO || MP-QQ
      // 小程序端，需要先授权
      getMiniProgramLocation()
        .then(resolve)
        .catch(() => {
          uni.showToast({
            title: '小程序定位失败,使用IP定位',
            icon: 'none'
          })
          handleIpLocation().then(resolve).catch(reject)
        })
      // #endif
    })
  }
  
  
  