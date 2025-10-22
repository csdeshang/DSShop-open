<?php

namespace app\crontab\controller;
use app\BaseController;
use think\facade\Log;
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
 * 定时器
 */
class  BaseCron extends BaseController {

    public function shutdown(){
        exit("run ".request()->controller()." success at ".date('Y-m-d H:i:s',TIMESTAMP)."\n");
    }

    public function initialize(){
        parent::initialize();
        $config_list = rkcache('config', true);
        config($config_list,'ds_config');
        set_time_limit(600);
        error_reporting(E_ALL & ~E_NOTICE);
        register_shutdown_function(array($this,"shutdown"));
    }

    /**
     * 记录日志
     * @param unknown $content 日志内容
     * @param boolean $if_sql 是否记录SQL
     */
    protected function log($content, $if_sql = true) {

        Log::record('queue\\'.$content);
    }

    
}
?>
