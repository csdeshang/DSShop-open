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
class Link extends BaseModel
{
    public $page_info;
    /**
     * 友情链接列表
     * @access public
     * @author csdeshang
     * @param type $condition  查询条件
     * @param type $pagesize       分页页数
     * @param type $order      排序
     * @return type            返回结果
     */
    public function getLinkList($condition = '', $pagesize = '',$order='link_sort asc')
    {
        if ($pagesize) {
            $result = Db::name('link')->where($condition)->order($order)->paginate(['list_rows'=>$pagesize,'query' => request()->param()],false);
            $this->page_info = $result;
            return $result->items();
        }else{
            return Db::name('link')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 取单个友情链接
     * @access public
     * @author csdeshang
     * @param type $id 链接ID
     * @return type
     */
    public function getOneLink($id)
    {
        return Db::name('link')->where('link_id',$id)->find();
    }

    /**
     * 新增友情链接
     * @access public
     * @author csdeshang
     * @param type $data 参数内容
     * @return type
     */
    public function addLink($data)
    {
        return Db::name('link')->insertGetId($data);
    }

    /**
     * 更新友情链接  
     * @access public
     * @author csdeshang
     * @param type $data 更新数据
     * @param type $link_id 链接id
     * @return type
     */
    public function editLink($data,$link_id)
    {
        return Db::name('link')->where('link_id',$link_id)->update($data);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param array $id 链接id
     * @return bool
     */
    public function delLink($id)
    {
        $link = $this->getOneLink($id);
        //删除友情链接图片
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . DIR_ADMIN . DIRECTORY_SEPARATOR .'link'. DIRECTORY_SEPARATOR .$link['link_pic']);
        return Db::name('link')->where('link_id',intval($id))->delete();
    }
}