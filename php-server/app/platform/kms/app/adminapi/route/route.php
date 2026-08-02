<?php



use think\facade\Route;
use app\adminapi\middleware\AdminAuthorizeToken;
use app\adminapi\middleware\AdminAuthorizeRole;
use app\adminapi\middleware\AdminAuthorizeLog;




Route::group('kms', function () {

    Route::get('goods/pages', 'app\platform\kms\app\adminapi\controller\goods\KmsGoods@getKmsGoodsPages');
    Route::post('goods/batch-soft-delete', 'app\platform\kms\app\adminapi\controller\goods\KmsGoods@softDeleteKmsGoods');
    Route::post('goods', 'app\platform\kms\app\adminapi\controller\goods\KmsGoods@addKmsGoods');
    Route::get('goods/:id/edit', 'app\platform\kms\app\adminapi\controller\goods\KmsGoods@getKmsGoodsUpdateInfo');
    Route::put('goods/:id', 'app\platform\kms\app\adminapi\controller\goods\KmsGoods@updateKmsGoods');

    Route::get('lessons/list', 'app\platform\kms\app\adminapi\controller\kms\KmsLesson@getKmsLessonList');
    Route::get('lessons/:id', 'app\platform\kms\app\adminapi\controller\kms\KmsLesson@getKmsLessonDetail');
    Route::post('lessons', 'app\platform\kms\app\adminapi\controller\kms\KmsLesson@createKmsLesson');
    Route::put('lessons/:id', 'app\platform\kms\app\adminapi\controller\kms\KmsLesson@updateKmsLesson');
    Route::delete('lessons/:id', 'app\platform\kms\app\adminapi\controller\kms\KmsLesson@deleteKmsLesson');



})->middleware([
    AdminAuthorizeToken::class,
    AdminAuthorizeRole::class,
    AdminAuthorizeLog::class
]);


