<?php

namespace app\api\controller;

/**
 * 首页控制器
 * 用于展示系统介绍和 API 文档入口
 */
class Index
{
    /**
     * 首页介绍
     * 直接输出 HTML 内容，展示系统介绍和 API 文档链接
     */
    public function index()
    {
        // 设置响应头为 HTML
        header('Content-Type: text/html; charset=utf-8');
        
        // 输出介绍内容
        echo <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSShop API 系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            padding: 50px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            font-size: 42px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
        
        .subtitle {
            color: #666;
            font-size: 20px;
            margin-bottom: 40px;
            font-weight: 300;
        }
        
        .info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 35px;
            margin: 35px 0;
            text-align: left;
        }
        
        .info h2 {
            color: #333;
            font-size: 26px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .info p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .info ul {
            color: #666;
            line-height: 2;
            margin-left: 20px;
            margin-top: 15px;
        }
        
        .info li {
            margin-bottom: 8px;
        }
        
        .links {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .links a {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .links a:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .links a.secondary {
            background: #6c757d;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .links a.secondary:hover {
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.5);
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
            color: #999;
            font-size: 14px;
        }
        
        .footer p {
            margin-bottom: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e9ecef;
            border-radius: 12px;
            font-size: 12px;
            color: #666;
            margin: 0 5px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 32px;
            }
            
            .subtitle {
                font-size: 18px;
            }
            
            .info {
                padding: 25px;
            }
            
            .links a {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>DSShop API 系统</h1>
        <p class="subtitle">平台自营单商户电商系统 API 接口</p>
        
        <div class="info">
            <h2>系统介绍</h2>
            <p><strong>DSShop</strong> 是一个基于 <strong>ThinkPHP 8.0</strong> 开发的<strong>平台自营（B2C）单商户</strong>电商系统。</p>
            <p>无多商户入驻、无商户端；商品、订单、支付与发货均由平台统一运营。</p>
            <p>系统提供以下端侧接口：</p>
            <ul>
                <li>管理端（Admin） - 平台运营与系统配置</li>
                <li>用户端（User） - 用户购物与会员服务</li>
                <li>骑手端（Rider） - 配送服务管理</li>
                <li>技师端（Technician） - 上门服务管理</li>
                <li>博主端（Blogger） - 内容创作与推广</li>
            </ul>
            <p style="margin-top: 20px;">提供完整的商品管理、订单管理、用户管理、支付集成、分销系统等功能模块。</p>
        </div>
        
        <div class="links">
            <a href="https://www.csdeshang.com" target="_blank">官方网站</a>
            <a href="https://help.dsplatform.csdeshang.com/" target="_blank" class="secondary">帮助手册</a>
        </div>
        
        <div class="footer">
            <p><strong>DSShop</strong> - 平台自营单商户电商系统</p>
            <p>
                <span class="badge">ThinkPHP 8.0</span>
                <span class="badge">Vue 3.4</span>
                <span class="badge">UniApp 3.0</span>
                <span class="badge">OpenAPI 3.0</span>
            </p>
            <p style="margin-top: 15px; color: #ccc;">© 2014-至今 德尚网络. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;



        exit;
    }
}