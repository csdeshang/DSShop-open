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
class Document extends BaseModel {

    /**
     * 查询所有系统文章
     * @access public
     * @author csdeshang 
     * @return type
     */
    public function getDocumentList() {
        return Db::name('document')->select()->toArray();
    }

    /**
     * 根据编号查询一条
     * @access public
     * @author csdeshang 
     * @param int $id 文章id
     * @return array
     */
    public function getOneDocumentById($id) {
        return Db::name('document')->where('document_id',$id)->find();
    }

    /**
     * 根据标识码查询一条
     * @access public
     * @author csdeshang
     * @param type $code 标识码
     * @return type
     */
    public function getOneDocumentByCode($code) {
        return Db::name('document')->where('document_code',$code)->find();
    }

    /**
     * 更新
     * @access public
     * @author csdeshang
     * @param array $data 更新数据
     * @return bool
     */
    public function editDocument($data) {
        return Db::name('document')->where('document_id',$data['document_id'])->update($data);
    }
}

?>
