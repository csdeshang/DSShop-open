<?php
namespace app\api\service\user;
use app\deshang\base\service\BaseUserService;

use app\common\dao\goods\TblGoodsFavoritesDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\enum\goods\TblGoodsEnum;
use app\deshang\exceptions\CommonException;

class UserTblGoodsFavoritesService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }


    // 获取用户商品收藏列表
    public function getTblGoodsFavoritesPages()
    {

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new TblGoodsFavoritesDao())->getWithRelGoodsFavoritesPages($condition);
        return $result;
    }

    // 创建用户商品收藏
    public function createTblGoodsFavorites($goods_id)
    {
        // 检查商品是否存在（包括已删除和系统状态检查）
        $condition = [
            ['id', '=', $goods_id],
            ['is_deleted', '=', 0],
            ['sys_status', '=', TblGoodsEnum::SYS_STATUS_NORMAL]
        ];
        $goods_info = (new TblGoodsDao())->getGoodsInfo($condition);
        if (!$goods_info) {
            throw new CommonException('商品不存在');
        }

        
        // 检查是否已收藏
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['goods_id', '=', $goods_id];
        
        $exists = (new TblGoodsFavoritesDao())->getGoodsFavoritesInfo($condition);
        if (!empty($exists)) {
            throw new CommonException('已收藏该商品');
        }
        
        // 创建收藏记录
        $data = [
            'user_id' => $this->user_id,
            'goods_id' => $goods_id,
        ];
        
        $result = (new TblGoodsFavoritesDao())->createGoodsFavorites($data);

        // 自增商品收藏数
        (new TblGoodsDao())->setGoodsInc([['id', '=', $goods_id]], 'collect_num');

        return $result;

    }
    
    // 删除用户商品收藏
    public function deleteTblGoodsFavorites($goods_id)
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['goods_id', '=', $goods_id];
        
        $result = (new TblGoodsFavoritesDao())->deleteGoodsFavorites($condition);

        // 自减商品收藏数
        (new TblGoodsDao())->setGoodsDec([['id', '=', $goods_id]], 'collect_num');

        return $result;
       
    }

    // 获取用户商品收藏详情
    public function getTblGoodsFavoritesInfo($goods_id)
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['goods_id', '=', $goods_id];
        
        $result = (new TblGoodsFavoritesDao())->getGoodsFavoritesInfo($condition);
        return $result;
    }


    
}