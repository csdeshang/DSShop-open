<?php

namespace app\api\service\video;

use app\deshang\base\service\BaseApiService;
use app\common\dao\video\VideoCategoryDao;

/**
 * 视频分类服务类
 * 
 * 负责视频分类的业务逻辑处理
 */
class VideoCategoryService extends BaseApiService
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 获取视频分类列表
     * 
     * @param array $data 查询参数
     * @return array 分类列表
     */
    public function getVideoCategoryList($data)
    {
        $condition = [];
        
        // 添加分类类型筛选
        if (!empty($data['type'])) {
            $condition[] = ['type', '=', $data['type']];
        }
        
        // 只显示启用的分类
        $condition[] = ['is_show', '=', 1];
        
        $result = (new VideoCategoryDao())->getVideoCategoryList($condition, '*', 'sort asc, id asc');
        return $result;
    }
    

} 