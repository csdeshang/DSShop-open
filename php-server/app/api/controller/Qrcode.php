<?php

namespace app\api\controller;

use app\deshang\base\controller\BaseApiController;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

/**
 * @OA\Tag(name="api/Qrcode", description="二维码生成接口")
 */
class Qrcode extends BaseApiController {

    /**
     * 生成二维码并返回图片数据
     * @OA\Get(
     *     path="/api/qrcodes",
     *     tags={"api/Qrcode"},
     *     summary="生成二维码并返回图片数据",
     *     description="生成二维码并返回base64编码的图片数据",
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         required=true,
     *         description="二维码内容",
     *         @OA\Schema(type="string", example="https://www.example.com")
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         description="二维码大小，默认300",
     *         @OA\Schema(type="integer", default=300, example=300)
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
    public function create()
    {
        $content = input('param.content', '', 'trim'); // 二维码内容
        $size = input('param.size', 300, 'intval'); // 二维码大小
        
        if (empty($content)) {
            return ds_json_error('二维码内容不能为空');
        }
        
        try {
            // 创建二维码
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($content)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size($size)
                ->margin(10)
                ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->build();
            
            // 返回base64编码的图片
            return ds_json_success('生成成功', [
                'base64' => $result->getDataUri()
            ]);
        } catch (\Exception $e) {
            return ds_json_error('生成二维码失败：' . $e->getMessage());
        }
    }
    
    /**
     * 生成二维码并直接输出图片
     * @OA\Get(
     *     path="/api/qrcodes/image",
     *     tags={"api/Qrcode"},
     *     summary="生成二维码并直接输出图片",
     *     description="生成二维码并直接输出PNG格式图片",
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         required=true,
     *         description="二维码内容",
     *         @OA\Schema(type="string", example="https://www.example.com")
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         description="二维码大小，默认300",
     *         @OA\Schema(type="integer", default=300, example=300)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="返回PNG图片",
     *         @OA\MediaType(
     *             mediaType="image/png",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     )
     * )
     */
    public function image()
    {
        $content = input('param.content', '', 'trim'); // 二维码内容
        $size = input('param.size', 300, 'intval'); // 二维码大小
        
        if (empty($content)) {
            echo '二维码内容不能为空';
            exit;
        }
        
        try {
            // 创建二维码
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($content)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size($size)
                ->margin(10)
                ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->build();
            
            // 直接输出图片
            header('Content-Type: ' . $result->getMimeType());
            echo $result->getString();
            exit;
        } catch (\Exception $e) {
            echo '生成二维码失败：' . $e->getMessage();
            exit;
        }
    }
}