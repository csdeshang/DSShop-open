<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
protected $filter = ['htmlspecialchars', 'removeEmojis', 'remove_special_words', 'trim'];
}
