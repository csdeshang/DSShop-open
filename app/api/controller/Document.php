<?php

namespace app\api\controller;

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
 * 平台协议控制器
 */
class Document extends MobileMall
{
    public function initialize()
    {
        parent::initialize(); 
    }
    /**
     * @api {POST} api/Document/agreement 用户协议
     * @apiVersion 3.0.6
     * @apiGroup Document
     *
     * @apiParam {String} type 协议类型 agreement 用户协议
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Int} result.document_id  协议ID
     * @apiSuccess {Object} result.document_code  协议代码
     * @apiSuccess {Object} result.document_title  协议标题
     * @apiSuccess {Object} result.document_content  协议内容
     * @apiSuccess {Int} result.document_time  添加时间
     */
    public function agreement() {
        $type=input('param.type');
        if(!$type){
            $type='agreement';
        }
        $doc = model('document')->getOneDocumentByCode($type);
        $doc['document_content']= htmlspecialchars_decode($doc['document_content']);
        ds_json_encode(10000, '',$doc);
    }
}