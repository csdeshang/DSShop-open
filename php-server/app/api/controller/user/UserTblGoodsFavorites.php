<?php

namespace app\api\controller\user;
use app\deshang\base\controller\BaseUserController;
use app\api\service\user\UserTblGoodsFavoritesService;

/**
 * @OA\Tag(name="api/user/UserTblGoodsFavorites", description="用户商品收藏接口")
 */
class UserTblGoodsFavorites extends BaseUserController
{

    /**
     * @OA\Get(
     *     path="/api/user/goods-favorites/pages",
     *     tags={"api/user/UserTblGoodsFavorites"},
     *     summary="获取用户商品收藏列表",
     *     description="获取当前用户的商品收藏列表",
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getTblGoodsFavoritesPages(){
        $result = (new UserTblGoodsFavoritesService())->getTblGoodsFavoritesPages();
        return ds_json_success('操作成功',$result);
    }

    /**
     * @OA\Post(
     *     path="/api/user/goods-favorites/{id}",
     *     tags={"api/user/UserTblGoodsFavorites"},
     *     summary="创建用户商品收藏",
     *     description="收藏指定的商品",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function createTblGoodsFavorites($id){
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserTblGoodsFavoritesValidate.create');
        
        $result = (new UserTblGoodsFavoritesService())->createTblGoodsFavorites($id);
        return ds_json_success('操作成功',$result);
    }

    /**
     * @OA\Delete(
     *     path="/api/user/goods-favorites/{id}",
     *     tags={"api/user/UserTblGoodsFavorites"},
     *     summary="删除用户商品收藏",
     *     description="取消收藏指定的商品",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function deleteTblGoodsFavorites($id){
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserTblGoodsFavoritesValidate.delete');
        
        $result = (new UserTblGoodsFavoritesService())->deleteTblGoodsFavorites($id);
        return ds_json_success('操作成功',$result);
    }

    /**
     * @OA\Get(
     *     path="/api/user/goods-favorites/{id}",
     *     tags={"api/user/UserTblGoodsFavorites"},
     *     summary="获取用户商品收藏详情",
     *     description="获取指定商品的收藏详情",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="商品ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getTblGoodsFavoritesInfo($id){
        $this->validate(['id' => $id], 'app\api\controller\user\validate\UserTblGoodsFavoritesValidate.info');
        
        $result = (new UserTblGoodsFavoritesService())->getTblGoodsFavoritesInfo($id);
        return ds_json_success('操作成功',$result);
    }

}





