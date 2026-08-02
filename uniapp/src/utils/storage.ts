const storage = {
    key: 'ds_',
    // 默认过期时间（1小时），单位：秒
    defaultExpire: 1 * 60 * 60, // 1小时的秒数

    //设置缓存(expire为缓存时效)
    set(key: string, value: any, expire?: number) {
        key = this.getKey(key)
        let data: any = {
            expire: expire ? this.time() + expire : this.time() + this.defaultExpire,
            value
        }

        if (typeof data === 'object') {
            data = JSON.stringify(data)
        }
        try {
            uni.setStorageSync(key, data)
        } catch (e) {
            return null
        }
    },
    get(key: string) {
        key = this.getKey(key)
        try {
            const data = uni.getStorageSync(key)
            if (!data) {
                return null
            }
            const { value, expire } = JSON.parse(data)
            if (expire && expire < this.time()) {
                uni.removeStorageSync(key)
                return null
            }
            return value
        } catch (e) {
            return null
        }
    },
    //获取当前时间
    time() {
        return Math.round(new Date().getTime() / 1000)
    },
    remove(key: string) {
        key = this.getKey(key)
        uni.removeStorageSync(key)
    },
    getKey(key: string) {
        return this.key + key
    }
}


export default storage