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
class Voucher extends BaseModel
{

    const VOUCHER_STATE_UNUSED = 1;
    const VOUCHER_STATE_USED = 2;
    const VOUCHER_STATE_EXPIRE = 3;
    public $page_info;
    private $voucher_state_array = array(
        self::VOUCHER_STATE_UNUSED => '未使用', self::VOUCHER_STATE_USED => '已使用', self::VOUCHER_STATE_EXPIRE => '已过期',
    );

    const VOUCHER_GETTYPE_DEFAULT = 'points'; //默认领取方式

    private $voucher_gettype_array = array(
        'points' => array('sign' => 1, 'name' => '积分兑换'), 
        'pwd' => array('sign' => 2, 'name' => '卡密兑换'),
        'free' => array('sign' => 3, 'name' => '免费领取')
    );
    private $templatestate_arr;

    /**
     * 构造函数
     * @access public
     * @author csdeshang
     */
    public function __construct()
    {
        parent::__construct();
        //代金券模板状态
        $this->templatestate_arr = array('usable' => array(1, '有效'), 'disabled' => array(2, '失效'));
    }

    /**
     * 获取代金券模板状态
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getTemplateState()
    {
        return $this->templatestate_arr;
    }

    /**
     * 领取的代金券状态
     * @access public
     * @author csdeshang
     * @return type
     */
    public function getVoucherState()
    {
        return array(
            'unused' => array(1, lang('voucher_voucher_state_unused')),
            'used' => array(2, lang('voucher_voucher_state_used')),
            'expire' => array(3, lang('voucher_voucher_state_expire'))
        );
    }

    /**
     * 返回当前可用的代金券列表,每种类型(模板)的代金券里取出一个代金券码(同一个模板所有码面额和到期时间都一样)
     * @access public
     * @author csdeshang
     * @param array $condition 条件
     * @param int $goods_total 商品总金额
     * @return string
     */
    public function getCurrentAvailableVoucher($condition = array(), $goods_total = 0)
    {
        $condition[]=array('voucher_enddate','>', TIMESTAMP);
        $condition[]=array('voucher_state','=',1);
        
        $voucher_list = Db::name('voucher')->where($condition)->select()->toArray();
        if(!empty($voucher_list)){
            $voucher_list = ds_change_arraykey($voucher_list,'vouchertemplate_id');
        }
        
        foreach ($voucher_list as $key => $voucher) {
            if ($goods_total < $voucher['voucher_limit']) {
                unset($voucher_list[$key]);
            }
            else {
                $voucher_list[$key]['desc'] = sprintf('面额%s元 有效期至 %s ', $voucher['voucher_price'], date('Y-m-d', $voucher['voucher_enddate']));
                if ($voucher['voucher_limit'] > 0) {
                    $voucher_list[$key]['desc'] .= sprintf(' 消费满%s可用', $voucher['voucher_limit']);
                }
            }
        }
        return $voucher_list;
    }

    /**
     * 取得当前有效代金券数量
     * @access public
     * @author csdeshang
     * @param type $member_id 会员ID
     * @return type
     */
    public function getCurrentAvailableVoucherCount($member_id)
    {
        $info = rcache($member_id, 'm_voucher');
        if (empty($info)) {
            $condition[]=array('voucher_owner_id','=',$member_id);
            $condition[]=array('voucher_enddate','>', TIMESTAMP);
            $condition[]=array('voucher_state','=',1);
            $voucher_count = Db::name('voucher')->where($condition)->count();
            $voucher_count = intval($voucher_count);
            wcache($member_id, array('voucher_count' => $voucher_count), 'm_voucher');
        }
        else {
            $voucher_count = intval($info['voucher_count']);
        }
        return $voucher_count;
    }



    
    /**
     * 获得代金券列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @return type
     */
    public function getVoucherList($where, $field = '*')
    {
        $voucher_list = Db::name('voucher')->field($field)->where($where)->select()->toArray();
        return $voucher_list;
    }
    
    /**
     * 获取失效代金卷列表
     * @access public
     * @author csdeshang
     * @param array $where 条件
     * @param type $field 字段
     * @return type
     */
    public function getVoucherUnusedList($where, $field = '*')
    {
        $where[]=array('voucher_state','=',1);
        return $this->getVoucherList($where, $field);
    }

    /**
     * 查询可兑换代金券模板详细信息，包括店铺信息
     * @access public
     * @author csdeshang
     * @param type $vid
     * @param type $member_id 会员id
     * @return type
     */
    public function getCanChangeTemplateInfo($vid, $member_id,$no_point=false)
    {
        if ($vid <= 0 || $member_id <= 0) {
            return array('state' => false, 'msg' => '参数错误');
        }
        //查询可用代金券模板
        $where = array();
        $where[]=array('vouchertemplate_id','=',$vid);
        $where[]=array('vouchertemplate_state','=',$this->templatestate_arr['usable'][0]);
        $where[]=array('vouchertemplate_enddate','>', TIMESTAMP);
        $template_info = $this->getVouchertemplateInfo($where);
        if (empty($template_info) || $template_info['vouchertemplate_total'] <= $template_info['vouchertemplate_giveout']) {//代金券不存在或者已兑换完
            return array('state' => false, 'msg' => '代金券已兑换完');
        }
        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID($member_id);
        if (empty($member_info)) {
            return array('state' => false, 'msg' => '参数错误');
        }
        if(!$no_point){
        //验证会员积分是否足够
        if ($template_info['vouchertemplate_gettype'] == $this->voucher_gettype_array['points']['sign'] && $template_info['vouchertemplate_points'] > 0) {
            if (intval($member_info['member_points']) < intval($template_info['vouchertemplate_points'])) {
                return array('state' => false, 'msg' => '您的积分不足，暂时不能兑换该代金券');
            }
        }
        }
        //验证会员级别
        /*
        $member_currgrade = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
        $member_info['member_currgrade'] = $member_currgrade ? $member_currgrade['level'] : 0;
        if ($member_info['member_currgrade'] < intval($template_info['vouchertemplate_mgradelimit'])) {
            return array('state' => false, 'msg' => '您的会员级别不够，暂时不能兑换该代金券');
        }
         */
        
        //查询代金券列表
        $where = array();
        $where[] = array('voucher_owner_id','=',$member_id);

        $voucher_list = $this->getVoucherList($where);
        if (!empty($voucher_list)) {
            if(!$no_point){
            $voucher_count = 0; //在该店铺兑换的代金券数量
            $voucherone_count = 0; //该张代金券兑换的数量
            foreach ($voucher_list as $k => $v) {
                //如果代金券未用且未过期
                if ($v['voucher_state'] == 1 && $v['voucher_enddate'] > TIMESTAMP) {
                    $voucher_count += 1;
                }
                if ($v['vouchertemplate_id'] == $template_info['vouchertemplate_id']) {
                    $voucherone_count += 1;
                }
            }
            //同一张代金券最多能兑换的次数
            if (!empty($template_info['vouchertemplate_eachlimit']) && $voucherone_count >= $template_info['vouchertemplate_eachlimit']) {
                $message = sprintf('该代金券您已兑换%s次，不可再兑换了', $template_info['vouchertemplate_eachlimit']);
                return array('state' => false, 'msg' => $message);
            }
            }
        }
        return array('state' => true, 'info' => $template_info);
    }

    /**
     * 获取代金券编码
     * @access public
     * @author csdeshang
     * @staticvar int $num
     * @param type $member_id 会员id
     * @return type
     */
    public function getVoucherCode($member_id = 0)
    {
        static $num = 1;
        $sign_arr = array();
        $sign_arr[] = sprintf('%02d', mt_rand(10, 99));
        $sign_arr[] = sprintf('%03d', (float)microtime() * 1000);
        $sign_arr[] = sprintf('%010d', TIMESTAMP - 946656000);
        if ($member_id) {
            $sign_arr[] = sprintf('%03d', (int)$member_id % 1000);
        }
        else {
            //自增变量
            $tmpnum = 0;
            if ($num > 99) {
                $tmpnum = substr($num, -1, 2);
            }
            else {
                $tmpnum = $num;
            }
            $sign_arr[] = sprintf('%02d', $tmpnum);
            $sign_arr[] = mt_rand(1, 9);
        }
        $code = implode('', $sign_arr);
        $num += 1;
        return $code;
    }

    /**
     * 生成代金券卡密
     * @staticvar int $num
     * @param type $vouchertemplate_id 卡密ID编号
     * @return boolean|array
     */
    public function createVoucherPwd($vouchertemplate_id)
    {
        if ($vouchertemplate_id <= 0) {
            return false;
        }
        static $num = 1;
        $sign_arr = array();
        //时间戳
        $time_tmp = uniqid('', true);
        $time_tmp = explode('.', $time_tmp);
        $sign_arr[] = substr($time_tmp[0], -1, 4) . $time_tmp[1];
        //自增变量
        $tmpnum = 0;
        if ($num > 999) {
            $tmpnum = substr($num, -1, 3);
        }
        else {
            $tmpnum = $num;
        }
        $sign_arr[] = sprintf('%03d', $tmpnum);
        //代金券模板ID
        if ($vouchertemplate_id > 9999) {
            $vouchertemplate_id = substr($num, -1, 4);
        }
        $sign_arr[] = sprintf('%04d', $vouchertemplate_id);
        //随机数
        $sign_arr[] = sprintf('%04d', rand(1, 9999));
        $pwd = implode('', $sign_arr);
        $num += 1;
        return array(md5($pwd), ds_encrypt($pwd));
    }

    /**
     * 生成代金券卡密
     * @access public
     * @author csdeshang
     * @param type $pwd 卡密
     * @return string
     */
    public function getVoucherPwd($pwd)
    {
        if (!$pwd) {
            return '';
        }
        $pwd = ds_decrypt($pwd);
        $pattern = "/^([0-9]{20})$/i";
        if (preg_match($pattern, $pwd)) {
            return $pwd;
        }
        else {
            return '';
        }
    }

    /**
     * 更新代金券信息
     * @param type $data 数据
     * @param type $condition 条件
     * @param type $member_id 会员id
     * @return type
     */
    public function editVoucher($data, $condition, $member_id = 0)
    {
        $result = Db::name('voucher')->where($condition)->update($data);
        if ($result && $member_id > 0) {
            wcache($member_id, array('voucher_count' => null), 'm_voucher');
        }
        return $result;
    }

    /**
     * 返回代金券状态数组
     * @access public
     * @author csdeshang
     * @return array
     */
    public function getVoucherStateArray()
    {
        return $this->voucher_state_array;
    }

    /**
     * 返回代金券领取方式数组
     * @access public
     * @author csdeshang
     * @return array
     */
    public function getVoucherGettypeArray()
    {
        return $this->voucher_gettype_array;
    }

    /**
     * 获取买家代金券列表
     * @access public
     * @author csdeshang
     * @param int $member_id 用户编号
     * @param int $voucher_state 代金券状态
     * @param int $pagesize 分页数
     * @return array
     */
    public function getMemberVoucherList($member_id, $voucher_state, $pagesize = null)
    {
        if (empty($member_id)) {
            return false;
        }

        //更新过期代金券状态
        $this->_checkVoucherExpire($member_id);

        $where = array();
        $where[] = array('voucher_owner_id','=',$member_id);
        $voucher_state = intval($voucher_state);
        if (intval($voucher_state) > 0 && array_key_exists($voucher_state, $this->voucher_state_array)) {
            $where[] = array('voucher_state','=',$voucher_state);
        }

        if($pagesize){
       $list = Db::name('voucher')->alias('v')->join('vouchertemplate t','v.vouchertemplate_id=t.vouchertemplate_id')->where($where)->order('voucher_id desc')->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
        $this->page_info=$list;
        $list=$list->items();
    }else{
            $list=Db::name('voucher')->alias('v')->join('vouchertemplate t','v.vouchertemplate_id=t.vouchertemplate_id')->where($where)->order('voucher_id desc')->select()->toArray();
        }
        if (!empty($list) && is_array($list)) {
            foreach ($list as $key => $val) {
                //代金券图片
                if (empty($val['vouchertemplate_customimg'])) {
                    $list[$key]['vouchertemplate_customimg'] = ds_get_pic(ATTACH_COMMON,config('ds_config.default_goods_image'));
                }
                else {
                    $list[$key]['vouchertemplate_customimg'] = ds_get_pic( ATTACH_VOUCHER ,$val['vouchertemplate_customimg']);
                }
                //代金券状态文字
                $list[$key]['voucher_state_text'] = $this->voucher_state_array[$val['voucher_state']];
                $list[$key]['voucher_end_date_text'] = $val['voucher_enddate'] ? @date('Y.m.d', $val['voucher_enddate']) : '';
            }
        }
        return $list;
    }

    /**
     * 更新过期代金券状态
     * @access public
     * @author csdeshang
     * @param type $member_id 会员ID
     * @return type 
     */
    private function _checkVoucherExpire($member_id)
    {
        $condition = array();
        $condition[]=array('voucher_owner_id','=',$member_id);
        $condition[]=array('voucher_state','=',self::VOUCHER_STATE_UNUSED);
        $condition[]=array('voucher_enddate','<', TIMESTAMP);

        Db::name('voucher')->where($condition)->update(array('voucher_state' => self::VOUCHER_STATE_EXPIRE));
    }

    /**
     * 查询代金券模板列表
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $limit 限制
     * @param type $pagesize 分页
     * @param type $order 排序
     * @return type
     */
    public function getVouchertemplateList($where, $field = '*', $limit = 0, $pagesize = 0, $order = '')
    {
        $voucher_list = array();
        if ($pagesize) {
            $result = Db::name('vouchertemplate')->field($field)->where($where)->order($order)->paginate(['list_rows'=>10,'query' => request()->param()],false);
            $voucher_list = $result->items();
            $this->page_info = $result;
        } else {
            $voucher_list = Db::name('vouchertemplate')->field($field)->where($where)->limit($limit)->order($order)->select()->toArray();
        }


        if (!empty($voucher_list) && is_array($voucher_list)) {
            foreach ($voucher_list as $k => $v) {
                if (!empty($v['vouchertemplate_customimg'])) {
                    $v['vouchertemplate_customimg'] = ds_get_pic( ATTACH_VOUCHER , $v['vouchertemplate_customimg']);
                }
                else {
                    $v['vouchertemplate_customimg'] = ds_get_pic(ATTACH_COMMON,config('ds_config.default_goods_image'));
                }
                //领取方式
                if ($v['vouchertemplate_gettype']) {
                    foreach ($this->voucher_gettype_array as $gtype_k => $gtype_v) {
                        if ($v['vouchertemplate_gettype'] == $gtype_v['sign']) {
                            $v['vouchertemplate_gettype_key'] = $gtype_k;
                            $v['vouchertemplate_gettype_text'] = $gtype_v['name'];
                        }
                    }
                }
                //状态
                if ($v['vouchertemplate_state']) {
                    foreach ($this->templatestate_arr as $tstate_k => $tstate_v) {
                        if ($v['vouchertemplate_state'] == $tstate_v[0]) {
                            $v['vouchertemplate_state_text'] = $tstate_v[1];
                        }
                    }
                }
                
                //会员级别
                /*
                $member_grade = model('member')->getMemberGradeArr();
                $v['vouchertemplate_mgradelimittext'] = isset($v['vouchertemplate_mgradelimit']) ? $member_grade[$v['vouchertemplate_mgradelimit']]['level_name'] : '';
                 */

                $voucher_list[$k] = $v;
            }
        }
        return $voucher_list;
    }

   
    /**
     * 更新代金券模板信息
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $data 数据
     * @return type
     */
    public function editVouchertemplate($where, $data)
    {
        return Db::name('vouchertemplate')->where($where)->update($data);
    }


    /**
     * 获得推荐的热门代金券列表
     * @access public
     * @author csdeshang
     * @param int $num 查询条数
     * @return array 
     */
    public function getRecommendTemplate($num)
    {
        //查询推荐的热门代金券列表
        $where = array();
        $where[] = array('vouchertemplate_recommend','=',1);
        $where[]=array('vouchertemplate_state','=',$this->templatestate_arr['usable'][0]);
        //领取方式为积分兑换
        $where[]=array('vouchertemplate_gettype','=',$this->voucher_gettype_array['points']['sign']);
        $where[]=array('vouchertemplate_enddate','>', TIMESTAMP);
        $recommend_voucher = $this->getVouchertemplateList($where, $field = '*', $num, 0, 'vouchertemplate_recommend desc,vouchertemplate_id desc');
        return $recommend_voucher;
    }

    /**
     * 积分兑换代金券
     * @access public
     * @author csdeshang
     * @param type $template_info 信息模板
     * @param type $member_id 会员ID
     * @param type $member_name 会员名
     * @return type
     */
    public function exchangeVoucher($template_info, $member_id, $member_name = '',$no_point=false)
    {
        if (intval($member_id) <= 0 || empty($template_info)) {
            return array('state' => false, 'msg' => '参数错误');
        }
        //查询会员信息
        if (!$member_name) {
            $member_info = model('member')->getMemberInfoByID($member_id);
            if (empty($template_info)) {
                return array('state' => false, 'msg' => '参数错误');
            }
            $member_name = $member_info['member_name'];
        }
        //添加代金券信息
        $insert_arr = array();
        $insert_arr['voucher_code'] = $this->getVoucherCode($member_id);
        $insert_arr['vouchertemplate_id'] = $template_info['vouchertemplate_id'];
        $insert_arr['voucher_title'] = $template_info['vouchertemplate_title'];
        $insert_arr['voucher_desc'] = $template_info['vouchertemplate_desc'];
        $insert_arr['voucher_startdate'] = TIMESTAMP;
        $insert_arr['voucher_enddate'] = $template_info['vouchertemplate_enddate'];
        $insert_arr['voucher_price'] = $template_info['vouchertemplate_price'];
        $insert_arr['voucher_limit'] = $template_info['vouchertemplate_limit'];
        $insert_arr['voucher_state'] = 1;
        $insert_arr['voucher_activedate'] = TIMESTAMP;
        $insert_arr['voucher_owner_id'] = $member_id;
        $insert_arr['voucher_owner_name'] = $member_name;
        $result = Db::name('voucher')->insertGetId($insert_arr);
        if (!$result) {
            return array('state' => false, 'msg' => '兑换失败');
        }
        if(!$no_point){
        //扣除会员积分
        if ($template_info['vouchertemplate_points'] > 0 && $template_info['vouchertemplate_gettype'] == $this->voucher_gettype_array['points']['sign']) {
            $points_arr['pl_memberid'] = $member_id;
            $points_arr['pl_membername'] = $member_name;
            $points_arr['pl_points'] = -$template_info['vouchertemplate_points'];
            $points_arr['point_ordersn'] = $insert_arr['voucher_code'];
            $points_arr['pl_desc'] = lang('home_voucher') . $insert_arr['voucher_code'] . '消耗积分';
            $result = model('points')->savePointslog('app', $points_arr, true);
            if (!$result) {
                return array('state' => false, 'msg' => '兑换失败');
            }
        }
        }
        if ($result) {
            //代金券模板的兑换数增加
            $result = $this->editVouchertemplate(array('vouchertemplate_id' => $template_info['vouchertemplate_id']), array(
                'vouchertemplate_giveout' => Db::raw('vouchertemplate_giveout+1')
            ));
            if (!$result) {
                return array('state' => false, 'msg' => '兑换失败');
            }
            wcache($member_id, array('voucher_count' => Db::raw('voucher_count+1')), 'm_voucher');
            return array('state' => true, 'msg' => '兑换成功');
        }
        else {
            return array('state' => false, 'msg' => '兑换失败');
        }
    }

    /**
     * 批量增加代金券
     * @access public
     * @author csdeshang
     * @param type $insert_arr 参数数据
     * @return type
     */
    public function addVoucherBatch($insert_arr)
    {
        return Db::name('voucher')->insertAll($insert_arr);
    }

    /**
     * 获得代金券模板总数量
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @return int
     */
    public function getVouchertemplateCount($where)
    {
        return Db::name('vouchertemplate')->where($where)->count();
    }

    /**
     * 获得代金券总数量
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @return int
     */
    public function getVoucherCount($where)
    {
        return Db::name('voucher')->where($where)->count();
    }

    /**
     * 获得代金券模板详情
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @return array
     */
    public function getVouchertemplateInfo($where = array(), $field = '*', $order = '')
    {
        $info = Db::name('vouchertemplate')->where($where)->field($field)->order($order)->find();
        if (!$info) {
            return array();
        }
        if ($info['vouchertemplate_gettype']) {
            foreach ($this->voucher_gettype_array as $k => $v) {
                if ($info['vouchertemplate_gettype'] == $v['sign']) {
                    $info['vouchertemplate_gettype_key'] = $k;
                    $info['vouchertemplate_gettype_text'] = $v['name'];
                }
            }
        }
        if ($info['vouchertemplate_state']) {
            foreach ($this->templatestate_arr as $k => $v) {
                if ($info['vouchertemplate_state'] == $v[0]) {
                    $info['vouchertemplate_state_text'] = $v[1];
                }
            }
        }
        if (!empty($info['vouchertemplate_customimg'])) {
            $info['vouchertemplate_customimg'] = ds_get_pic( ATTACH_VOUCHER , $info['vouchertemplate_customimg']);
        }
        else {
            $info['vouchertemplate_customimg'] = ds_get_pic(ATTACH_COMMON,config('ds_config.default_goods_image'));
        }
        
        //会员等级
        /*
        $member_grade = model('member')->getMemberGradeArr();
        $info['vouchertemplate_mgradelimittext'] = isset($info['vouchertemplate_mgradelimit'])?$member_grade[$info['vouchertemplate_mgradelimit']]['level_name']:'';
        */
        return $info;
    }

    /**
     * 获得代金券详情
     * @access public
     * @author csdeshang
     * @param type $where 条件
     * @param type $field 字段
     * @param type $order 排序
     * @return type
     */
    public function getVoucherInfo($where = array(), $field = '*', $order = '')
    {
        $info = Db::name('voucher')->where($where)->field($field)->order($order)->find();
        if ($info['voucher_state']) {
            $info['voucher_state_text'] = $this->voucher_state_array[$info['voucher_state']];
        }
        return $info;
    }

    /**
     * 更新使用的代金券状态
     * @param $input_voucher_list
     * @throws Exception
     */
    public function editVoucherState($voucher_list)
    {
        $send = new \sendmsg\sendMemberMsg();
        $update = $this->editVoucher(array('voucher_state' => 2), array('voucher_id' => $voucher_list['voucher_id']), $voucher_list['voucher_owner_id']);
        if ($update) {
            // 发送用户店铺消息
            $send->set('member_id', $voucher_list['voucher_owner_id']);
            $send->set('code', 'voucher_use');
                $ali_param = array();
                $ali_param['voucher_code'] = $voucher_list['voucher_code'];
                $ten_param=array($voucher_list['voucher_code']);
                $param=$ali_param;
            $param['voucher_url'] = HOME_SITE_URL .'/Membervoucher/index';
            $weixin_param = array(
                    'url' => config('ds_config.h5_site_url').'/pages/member/voucher/VoucherList',
                    'data'=>array(
                        "keyword1" => array(
                            "value" => $voucher_list['voucher_code'],
                            "color" => "#333"
                        ),
                        "keyword2" => array(
                            "value" => date('Y-m-d H:i'),
                            "color" => "#333"
                        )
                    ),
                );
            $send->send($param,$weixin_param,$ten_param);
        }
        else {
            return ds_callback(false, '更新代金券状态失败vcode:' . $voucher_list['voucher_code']);
        }
        return ds_callback(true);
    }
}

?>
