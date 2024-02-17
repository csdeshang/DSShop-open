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
class Config extends BaseModel
{


    /**
     * 读取系统设置信息
     * @access public
     * @author csdeshang
     * @param string $name 系统设置信息名称
     * @return array 数组格式的返回结果
     */
    public function getOneConfigByCode($code){
        $where	= "code='".$code."'";
        $result=Db::name('config')->where($where)->select()->toArray();
        if(is_array($result) and is_array($result[0])){
            return $result[0];
        }
        return false;
    }
  
    /**
     * 读取系统设置列表
     * @access public
     * @author csdeshang 
     * @return type
     */
    public function getConfigList()
    {
        $result = Db::name('config')->select()->toArray();
        if (is_array($result)) {
            $list_config = array();
            foreach ($result as $k => $v) {
                $list_config[$v['code']] = $v['value'];
            }
        }
        return $list_config;
    }

    /**
     * 更新信息
     * @access public
     * @author csdeshang
     * @param array $data 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function editConfig($data)
    {
        if (empty($data)) {
            return false;
        }
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $tmp = array();
                $specialkeys_arr = array('statistics_code');
                $tmp['value'] = (in_array($k, $specialkeys_arr) ? htmlentities($v, ENT_QUOTES) : $v);
                $result = Db::name('config')->where('code', $k)->update($tmp);
            }
            dkcache('config');
            return true;
        } else {
            return false;
        }
    }

}

?>
