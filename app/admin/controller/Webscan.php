<?php

/**
 * 系统安全检测相关代码
 */

namespace app\admin\controller;

use think\facade\Lang;
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
 * 控制器
 */
class Webscan extends AdminControl {

    public function initialize() {
        parent::initialize();
        $this->_prefix = config('database.connections.mysql.prefix');
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/webscan.lang.php');
    }

    public function index()
    {
        $this->scan_member();
    }

    public function scan_member()
    {
        $output = array();
        //检测Member数据表中是否有重复的 用户名  邮箱  手机号
        $result = Db::query("select member_name,count(*) as count from {$this->_prefix}member group by member_name having count>1;");
    }
}
