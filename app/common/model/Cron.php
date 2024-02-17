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
class Cron extends BaseModel {
    


    /**
     * 取单条任务信息
     * @access public
     * @author csdeshang 
     * @param array $condition 检索条件
     * @return type
     */
    public function getCronInfo($condition = array()) {
        return Db::name('cron')->where($condition)->find();
    }
    /**
     * 任务队列列表
     * @access public
     * @author csdeshang 
     * @param array $condition 检索条件
     * @param number $limit 数目限制
     * @return array
     */
    public function getCronList($condition, $limit = 100) {
        return Db::name('cron')->where($condition)->limit($limit)->select()->toArray();
    }
    
    /**
     * 保存任务队列
     * @access public
     * @author csdeshang 
     * @param unknown $data 参数内容
     * @return array
     */
    public function addCronAll($data) {
        return Db::name('cron')->insertAll($data);
    }
    
    /**
     * 保存任务队列
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return boolean
     */
    public function addCron($data) {
        return Db::name('cron')->insert($data);
    }
    
    /**
     * 删除任务队列
     * @access public
     * @author csdeshang 
     * @param array $condition 条件
     * @return array
     */
    public function delCron($condition) {
        return Db::name('cron')->where($condition)->delete();
    }
    
}

?>
