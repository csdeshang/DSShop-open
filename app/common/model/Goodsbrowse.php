<?php

namespace app\common\model;
use think\facade\Db;

/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 数据层模型
 */
class Goodsbrowse extends BaseModel {

    public $page_info;

    /**
     * 浏览过的商品
     * @access public
     * @author csdeshang
     * @param int $member_id 会员ID（一般传递session('member_id')）
     * @param int $shownum 查询的条数，默认0则为返回全部
     * @return array
     */
    public function getViewedGoodsList($member_id = 0, $shownum = 0) {
        $shownum = ($t = intval($shownum)) > 0 ? $t : 0;
        $browselist = array();
        //如果会员ID存在，则读取数据库浏览历史，或者memcache
        if ($member_id > 0) {
            if (!config('ds_config.cache_open')) {//查询数据库
                $browselist_tmp = Db::name('goodsbrowse')->where(array('member_id' => $member_id))->order('goodsbrowse_time desc')->limit($shownum)->select()->toArray();
                if (!empty($browselist_tmp)) {
                    foreach ($browselist_tmp as $k => $v) {
                        $browselist[$v['goods_id']] = $v;
                    }
                }
            } else {
                //生成缓存的键值
                $hash_key = $member_id;
                //先查找$hash_key缓存
                $browse_goodsid = rcache($hash_key, 'goodsbrowse');
                $goodsid_arr = $browse_goodsid['goodsid'] ? unserialize($browse_goodsid['goodsid']) : array();
                unset($browse_goodsid['goodsid']);
                if ($goodsid_arr) {
                    //截取需要的记录数
                    if ($shownum) {
                        $goodsid_arr = array_slice($goodsid_arr, -$shownum, $shownum, true);
                    }
                    $goodsid_arr = array_reverse($goodsid_arr, true); //翻转数组，按照添加顺序倒序排列
                    //获得缓存的浏览商品信息
                    $browselist_tmp = rcache($hash_key, 'goodsbrowse');
                    foreach ($goodsid_arr as $v) {
                        $browselist[$v] = isset($browselist_tmp[$v]) ? unserialize($browselist_tmp[$v]) : array();
                    }
                }
            }
        }
        //查询浏览过的商品记录cookie
        if (!$member_id) {
            $browselist = array();
            if (cookie('viewed_goods')) {
                $string_viewed_goods = ds_decrypt(cookie('viewed_goods'));
                if (get_magic_quotes_gpc())
                    $string_viewed_goods = stripslashes($string_viewed_goods); //去除斜杠
                $cookie_arr_tmp = unserialize($string_viewed_goods);
                $cookie_arr = array();
                foreach ((array) $cookie_arr_tmp as $k => $v) {
                    $info = explode("-", $v);
                    if (is_numeric($info[0])) {
                        $cookie_arr[$info[0]] = intval($info[1]);
                    }
                }
                //截取需要的记录数
                if ($shownum) {
                    $cookie_arr = array_slice($cookie_arr, 0, $shownum, true);
                }
                $cookie_arr = array_reverse($cookie_arr, true); //翻转数组，按照添加顺序倒序排列
                if ($cookie_arr) {
                    foreach ($cookie_arr as $k => $v) {
                        $browselist[$k] = array('goodsbrowse_time' => $v);
                    }
                }
            }
        }
        //查询商品数据
        $browselist_new = array();
        if ($browselist) {
            $goods_list_tmp = model('goods')->getGoodsList(array(array('goods_id','in', array_keys($browselist))), 'goods_id, goods_name,goods_advword, goods_promotion_price, goods_promotion_type, goods_marketprice, goods_image, gc_id, gc_id_1, gc_id_2, gc_id_3');
            $goods_list = array();
            foreach ((array) $goods_list_tmp as $v) {
                $goods_list[$v['goods_id']] = $v;
            }
            foreach ((array) $browselist as $k => $v) {
                if (isset($goods_list[$k])) {
                    $browselist_new[] = array(
                        "goods_id" => $goods_list[$k]['goods_id'],
                        "goods_advword" => $goods_list[$k]['goods_advword'],
                        "goodsbrowse_time" => $v['goodsbrowse_time'],
                        "goods_name" => $goods_list[$k]['goods_name'],
                        "goods_image" => $goods_list[$k]['goods_image'],
                        "goods_marketprice" => $goods_list[$k]['goods_marketprice'],
                        "goods_promotion_price" => $goods_list[$k]['goods_promotion_price'],
                        "goods_promotion_type" => $goods_list[$k]['goods_promotion_type'],
                        "gc_id" => $goods_list[$k]['gc_id'],
                        "gc_id_1" => $goods_list[$k]['gc_id_1'],
                        "gc_id_2" => $goods_list[$k]['gc_id_2'],
                        "gc_id_3" => $goods_list[$k]['gc_id_3'],
                    );
                }
            }
        }
        return $browselist_new;
    }

    /**
     * 删除浏览记录
     * @access public
     * @author csdeshang
     * @param array $where 检索条件
     * @return array
     */
    public function delGoodsbrowse($where) {
        return Db::name('goodsbrowse')->where($where)->delete();
    }

    /**
     * 添加单条浏览记录
     * @access public
     * @author csdeshang
     * @param array $insert_arr 插入数组数据
     * @return array
     */
    public function addGoodsbrowse($insert_arr) {
        Db::name('goodsbrowse')->insert($insert_arr);
    }

    /**
     * 添加多条浏览记录
     * @access public
     * @author csdeshang
     * @param array $insert_arr 插入数组数据
     * @return array
     */
    public function addGoodsbrowseAll($insert_arr) {
        Db::name('goodsbrowse')->insertAll($insert_arr);
    }

    /**
     * 查询单条浏览记录
     * @access public
     * @author csdeshang
     * @param type $where 查询条件
     * @param type $field 字段
     * @param type $order 排序
     * @return array
     */
    public function getOneGoodsbrowse($where, $field = '*', $order = '') {
       return Db::name('goodsbrowse')->field($field)->where($where)->order($order)->find();
    }

    /**
     * 获取浏览记录列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $limit 限制
     * @param type $order 排序
     * @param type $group 分组
     * @return array
     */
    public function getGoodsbrowseList($where, $field = '*', $limit = 0, $order = '', $group = '') {
        return Db::name('goodsbrowse')->field($field)->where($where)->limit($limit)->order($order)->group($group)->select()->toArray();
    }

    /**
     * 获取猜你喜欢商品
     * @access public
     * @author csdeshang
     * @param array $member_id 会员id
     * @param array $shownum 显示数量
     * @return array
     */
    public function getGuessLikeGoods($member_id = 0, $shownum = 0) {
        $shownum = ($t = intval($shownum)) > 0 ? $t : 0;
        $browseclass_arr = array(); //浏览历史商品分类数组
        //如果会员ID存在，则读取数据库浏览历史，或者memcache
        if ($member_id > 0) {
            if (!config('ds_config.cache_open')) {//查询数据库
                $browseclass_list = $this->getGoodsbrowseList(array('member_id' => $member_id), 'gc_id',20, '', 'gc_id'); //随机抽取20条信息
                foreach ((array) $browseclass_list as $k => $v) {
                    $browseclass_arr[] = $v['gc_id'];
                }
            } else {
                //生成缓存的键值
                $hash_key = $member_id;
                //先查找$hash_key缓存
                $browse_goodsid = rcache($hash_key, 'goodsbrowse');
                $goodsid_arr_tmp = $browse_goodsid['goodsid'] ? unserialize($browse_goodsid['goodsid']) : array();
                unset($browse_goodsid['goodsid']);

                $browseclass_arr = array();
                if ($goodsid_arr_tmp) {
                    $num = count($goodsid_arr_tmp) < 20 ? count($goodsid_arr_tmp) : 20;
                    $goodsidkey_arr = $goodsid_arr_tmp ? @array_rand($goodsid_arr_tmp, $num) : array(); //随机抽取20条信息
                    foreach ((array) $goodsidkey_arr as $v) {
                        $goodsid_arr[] = $goodsid_arr_tmp[$v];
                    }
                    unset($num);
                    //获得缓存的浏览商品信息
                    $browselist_tmp = rcache($hash_key, 'goodsbrowse');
                    foreach ((array) $browselist_tmp as $k => $v) {
                        if(in_array($k,$goodsid_arr)){
                            $v = unserialize($v);
                            if (!$browseclass_arr || !in_array($v['gc_id'], $browseclass_arr)) {
                                $browseclass_arr[] = $v['gc_id'];
                            }
                        }
                    }
                }
            }
        }
        //如果会员ID不存在，则查询浏览过的商品记录cookie
        if (!$member_id) {
            $browselist = array();
            if (cookie('viewed_goods')) {
                $string_viewed_goods = ds_decrypt(cookie('viewed_goods'));
                if (get_magic_quotes_gpc())
                    $string_viewed_goods = stripslashes($string_viewed_goods); //去除斜杠
                $cookie_arr_tmp = unserialize($string_viewed_goods);
                if (count($cookie_arr_tmp) >= 20) {
                    shuffle($cookie_arr_tmp); //对数组进行随机排序
                    $cookie_arr_tmp = array_slice($cookie_arr_tmp, 0, 20); //随机抽取20条信息
                }
                $cookie_arr = array();
                foreach ((array) $cookie_arr_tmp as $k => $v) {
                    $info = explode("-", $v); //每项cookie的值为商品ID-访问时间
                    if (is_numeric($info[0])) {
                        $cookie_arr[$info[0]] = intval($info[1]);
                    }
                }
                $browselist = array();
                if ($cookie_arr) {
                    $viewed_list = model('goods')->getGoodsList(array(array('goods_id','in', array_keys($cookie_arr))), 'gc_id');
                    foreach ((array) $viewed_list as $v) {
                        if (!in_array($v['gc_id'], $browseclass_arr)) {
                            $browseclass_arr[] = $v['gc_id'];
                        }
                    }
                }
            }
        }

        //商品分类缓存
        $gc_list = model('goodsclass')->getGoodsclassForCacheModel();
        //处理商品分类
        $browseclass_arrtmp = array();
        foreach ((array) $browseclass_arr as $k => $v) {
            $browseclass_arrtmp[] = $v;
            $browseclass_arrtmp = array_merge($browseclass_arrtmp, ($t = @$gc_list[$v]['child']) ? explode(',', $t) : array());
            $browseclass_arrtmp = array_merge($browseclass_arrtmp, ($t = @$gc_list[$v]['childchild']) ? explode(',', $t) : array());
        }

        
        $goodslist = model('goods')->getGoodsOnlineList(array(array('gc_id','in', $browseclass_arrtmp)), '*', 0, 'goods_id desc', 0, 'goods_commonid');
        return $goodslist;
    }

    /**
     * 登录之后把cookie中的浏览记录存入数据库
     * @access public
     * @author csdeshang
     * @param type $member_id 会员ID
     * @return type
     */
    public function mergeGoodsbrowse($member_id) {
        if ($member_id <= 0) {
            return array('state' => false, 'msg' => '参数错误');
        }
        //获取浏览历史cookie商品列表
        $cookie_list = $this->getViewedGoodsList();
        $cookie_list = array_reverse($cookie_list, true); //翻转数组，按照添加顺序排列
        if (empty($cookie_list)) {
            return array('state' => true);
        }
        $cookie_goodsid = array();
        foreach ((array) $cookie_list as $k => $v) {
            $cookie_goodsid[] = $v['goods_id'];
        }
        $goods_info=array();
        $result=array();
        //cookie中浏览记录,去除店铺自己的商品，并加入数据库
        if (!empty($cookie_list)) {
            $insert_arr = array();
            foreach ($cookie_list as $v) {
                    $tmp = array();
                    $tmp['goods_id'] = $v['goods_id'];
                    $tmp['member_id'] = $member_id;
                    $tmp['goodsbrowse_time'] = $v['goodsbrowse_time'];
                    $tmp['gc_id'] = $v['gc_id'];
                    $tmp['gc_id_1'] = $v['gc_id_1'];
                    $tmp['gc_id_2'] = $v['gc_id_2'];
                    $tmp['gc_id_3'] = $v['gc_id_3'];
                    $goods_info[$v['goods_id']] = $tmp;
            }
            if (!empty($goods_info)) {
                if (!config('ds_config.cache_open')) {//存入数据库
                    $result = $this->addViewedGoodsToDatabase(array_keys($goods_info), $member_id, $goods_info);
                } else {//存入缓存
                    $result = $this->addViewedGoodsToCache(array_keys($goods_info), $member_id, $goods_info);
                }
            }
        }
        //最后清空浏览记录cookie
        cookie('viewed_goods', '', -3600);
        return $result;
    }

    /**
     * 浏览过的商品
     * @access public
     * @author csdeshang
     * @param type $goods_id 商品ID
     * @param type $member_id 会员ID（一般传递session('member_id')）
     * @return array
     */
    public function addViewedGoods($goods_id, $member_id = 0) {
        //未登录生成浏览过产品cookie
        if ($member_id <= 0) {
            $result = $this->addViewedGoodsToCookie($goods_id);
        }
        //登录后记录浏览历史
        if ($member_id > 0) {
            if (!config('ds_config.cache_open')) {//存入数据库
                $result = $this->addViewedGoodsToDatabase($goods_id, $member_id);
            } else {//存入缓存
                $result = $this->addViewedGoodsToCache($goods_id, $member_id);
            }
        }
        return $result;
    }

    /**
     * 浏览过的商品加入浏览历史cache
     * @access public
     * @author csdeshang
     * @param mixed $goods_id 商品ID或者商品ID数组
     * @param int $member_id 会员ID（一般传递session('member_id')）
     * @param array $goods_info 如果已经获取了商品信息则可传递至函数，避免重复查询
     * @return array
     */
    public function addViewedGoodsToCache($goods_id, $member_id, $goods_info = array()) {
        if (!$goods_id || $member_id <= 0) {
            return array('state' => false, 'msg' => '参数错误');
        }
        $goodsbrowse_time = TIMESTAMP;
        if (!$goods_info) {
            //查询商品详细信息
            $goods_model = model('goods');
            if (is_array($goods_id)) {
                $goods_infotmp = $goods_model->getGoodsList(array(array('goods_id','in', $goods_id)), 'goods_id,gc_id,gc_id_1,gc_id_2,gc_id_3');
                if (!$goods_infotmp) {
                    return array('state' => true);
                }
                foreach ($goods_infotmp as $k => $v) {
                        $goods_infotmp[$v['goods_id']] = $v;
                }
                if (!$goods_infotmp) {
                    return array('state' => true);
                }
                //对数组按照浏览先后进行排序
                foreach ($goods_id as $v) {
                    if ($goods_infotmp[$v]) {
                        $goods_info[$v] = $goods_infotmp[$v];
                    }
                }
            } else {
                $goods_infotmp = $goods_model->getGoodsInfoByID($goods_id);
                if (!$goods_infotmp) {//店铺浏览自己的商品不加入浏览历史
                    return array('state' => true);
                }
                $goods_info[$goods_id] = $goods_infotmp;
            }
        }

        //生成缓存的键值
        $hash_key = $member_id;

        //处理浏览历史cache中商品ID
        $_cache = rcache($hash_key, 'goodsbrowse');
        $goodsid_arr = $_cache['goodsid'] ? unserialize($_cache['goodsid']) : array();
        if ($goodsid_arr) {
            //如果商品ID已经存在则先删除该ID，然后在数组末尾新增该ID
            $goodsid_arr = array_diff($goodsid_arr, array_keys($goods_info));
            array_push($goodsid_arr, implode(',', array_keys($goods_info)));
        } else {
            $goodsid_arr = array_keys($goods_info);
        }

        $_cache['goodsid'] = serialize($goodsid_arr);

        //处理浏览历史cache中商品详细信息
        foreach ($goods_info as $k => $v) {
            $tmp_arr = array();
            $tmp_arr['goods_id'] = $v['goods_id'];
            $tmp_arr['member_id'] = $member_id;
            $tmp_arr['goodsbrowse_time'] = !empty($v['goodsbrowse_time']) ? $v['goodsbrowse_time'] : $goodsbrowse_time;
            $tmp_arr['gc_id'] = $v['gc_id'];
            $tmp_arr['gc_id_1'] = $v['gc_id_1'];
            $tmp_arr['gc_id_2'] = $v['gc_id_2'];
            $tmp_arr['gc_id_3'] = $v['gc_id_3'];
            $_cache[$k] = serialize($tmp_arr);
        }

        //缓存商品信息
        wcache($hash_key, $_cache, 'goodsbrowse');
        return array('state' => true);
    }

    /**
     * 浏览过的商品加入浏览历史数据库
     * @access public
     * @author csdeshang
     * @param mixed $goods_id 商品ID或者商品ID数组
     * @param int $member_id 会员ID（一般传递session('member_id')）
     * @param array $goods_info 如果已经获取了商品信息则可传递至函数，避免重复查询
     * @return array
     */
    public function addViewedGoodsToDatabase($goods_id, $member_id, $goods_info = array()) {
        if (!$goods_id || $member_id <= 0) {
            return array('state' => false, 'msg' => '参数错误');
        }
        $goodsbrowse_time = TIMESTAMP;
        if (!$goods_info) {
            //查询商品详细信息
            $goods_model = model('goods');
            if (is_array($goods_id)) {
                $goods_infotmp = $goods_model->getGoodsList(array(array('goods_id','in', $goods_id)), 'goods_id,gc_id,gc_id_1,gc_id_2,gc_id_3');
                if (!$goods_infotmp) {
                    return array('state' => true);
                }
                foreach ($goods_infotmp as $k => $v) {
                        $goods_infotmp[$v['goods_id']] = $v;
                }
                if (!$goods_infotmp) {
                    return array('state' => true);
                }
                //对数组按照浏览先后进行排序
                foreach ($goods_id as $v) {
                    if ($goods_infotmp[$v]) {
                        $goods_info[$v] = $goods_infotmp[$v];
                    }
                }
            } else {
                $goods_infotmp = $goods_model->getGoodsInfoByID($goods_id);
                if (!$goods_infotmp ) {//店铺浏览自己的商品不加入浏览历史
                    return array('state' => true);
                }
                $goods_info[$goods_id] = $goods_infotmp;
            }
        }

        //构造新增的数组
        $insert_arr = array();
		//当天时间年月日
        $today = date('Y-m-d',$goodsbrowse_time);
        //处理浏览历史cache中商品详细信息
        foreach ($goods_info as $k => $v) {
            $tmp_arr = array();
            $tmp_arr['goods_id'] = $v['goods_id'];
            $tmp_arr['member_id'] = $member_id;
            $tmp_arr['goodsbrowse_time'] = isset($v['goodsbrowse_time']) ? $v['goodsbrowse_time'] : $goodsbrowse_time;
            $tmp_arr['gc_id'] = $v['gc_id'];
            $tmp_arr['gc_id_1'] = $v['gc_id_1'];
            $tmp_arr['gc_id_2'] = $v['gc_id_2'];
            $tmp_arr['gc_id_3'] = $v['gc_id_3'];
            $insert_arr[] = $tmp_arr;
			$oncondition   = array();
			$oncondition[] = array('member_id','=',$member_id);
			$oncondition[] = array('goods_id','=',$v['goods_id']);
			$goodsbrowseinfo = $this->getOneGoodsbrowse($oncondition,'*','goodsbrowse_time desc');
			if(empty($goodsbrowseinfo )){
				$this->addGoodsbrowseAll($insert_arr);
			}else{
				$goodsbrowseinfotime = date('Y-m-d',$goodsbrowseinfo['goodsbrowse_time']);
				if($goodsbrowseinfotime !== $today){
					$this->addGoodsbrowseAll($insert_arr);
				}
			}
        }
        return array('state' => true);
    }

    /**
     * 浏览过的商品加入浏览历史数据库
     * @access public
     * @author csdeshang
     * @param mixed $goods_id 商品ID或者商品ID数组
     * @return array
     */
    public function addViewedGoodsToCookie($goods_id) {
        if (!$goods_id) {
            return array('state' => false, 'msg' => '参数错误');
        }

        //浏览时间
        $goodsbrowse_time = TIMESTAMP;

        //构造cookie的一项值，每项cookie的值为商品ID-访问时间
        if (is_array($goods_id)) {
            $goods_idarr = $goods_id;
            foreach ($goods_id as $v) {
                $cookievalue[] = $v . '-' . $goodsbrowse_time;
            }
        } else {
            $cookievalue[] = $goods_id . '-' . $goodsbrowse_time;
            $goods_idarr[] = $goods_id;
        }
        unset($goods_id);

        if (cookie('viewed_goods')) {//如果cookie已经存在
            $string_viewed_goods = ds_decrypt(cookie('viewed_goods'));
            if (get_magic_quotes_gpc()) {
                $string_viewed_goods = stripslashes($string_viewed_goods); // 去除斜杠
            }
            $vg_ca = @unserialize($string_viewed_goods);
            if (!empty($vg_ca) && is_array($vg_ca)) {
                foreach ($vg_ca as $vk => $vv) {
                    $vv_arr = explode('-', $vv);
                    if (in_array($vv_arr[0], $goods_idarr)) {//如果该商品的浏览记录已经存在，则删除它
                        unset($vg_ca[$vk]);
                    }
                }
            } else {
                $vg_ca = array();
            }
            //将新浏览历史加入cookie末尾
            array_push($vg_ca, implode(',', $cookievalue));

            //cookie中最多存储50条浏览信息
            if (count($vg_ca) > 50) {
                $vg_ca = array_slice($vg_ca, -50, 50);
            }
        } else {
            $vg_ca = $cookievalue;
        }
        $vg_ca = ds_encrypt(serialize($vg_ca));
        cookie('viewed_goods', $vg_ca);
    }

}

?>
