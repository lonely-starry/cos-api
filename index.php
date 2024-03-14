<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../styles.css">
</head>
<body>
<?php

// 引入腾讯云对象存储的 SDK
require 'vendor/autoload.php';

use Qcloud\Cos\Client;

// 引入配置文件
require 'key/Tencent Key.php';

// 创建腾讯云对象存储客户端
$client = new Client([
    'region' => $region,
    'credentials' => [
        'secretId' => $secretId,
        'secretKey' => $secretKey,
    ],
]);


// 获取参数中的壁纸类型
$wallpaperType = $_GET['type'] ?? 'horizontal';

// 指定壁纸文件夹
$folder = '';
switch ($wallpaperType) {
    case 'horizontal':
        $folder = 'horizontal_wallpapers';
        break;
    case 'vertical':
        $folder = 'catgirl_wallpapers';
        break;
    default:
        // 如果没有指定壁纸类型，则默认使用横屏壁纸
        $folder = 'horizontal_wallpapers';
        break;
}

// 生成加密参数
$timestamp = time();
$nonce = uniqid();
$signature = md5($timestamp . $nonce . $secretKey);

// 从腾讯云对象存储中获取文件列表
$objects = $client->listObjects([
    'Bucket' => $bucket,
    'Prefix' => $folder . '/',
])['Contents'];

// 随机选择一个壁纸文件
$randomObject = $objects[array_rand($objects)];

// 构建自定义域名
$customDomain = 'image.lonelyx.cn';

// 获取壁纸的访问链接并添加WebP自适应功能
$wallpaperUrl = 'https://' . $customDomain . '/' . $randomObject['Key'];

// 检查浏览器是否支持WebP格式
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
    // 如果浏览器支持WebP，则将链接中的图片格式设置为WebP
    $wallpaperUrl .= '?imageMogr2/format/webp';
}

// 输出壁纸的HTML标签
echo '<img src="' . $wallpaperUrl . '" alt="Wallpaper" style="max-width: 100%; height: auto;">';

// 输出加密参数
echo '<input type="hidden" name="timestamp" value="' . $timestamp . '">';
echo '<input type="hidden" name="nonce" value="' . $nonce . '">';
echo '<input type="hidden" name="signature" value="' . $signature . '">';
?>
