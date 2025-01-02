<?php
define('BASE_PATH', dirname(__DIR__));
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<pre>';

// 基本信息
echo "基本信息:\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "当前目录: " . __DIR__ . "\n";
echo "文档根目录: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "基础路径: " . BASE_PATH . "\n";

// 目录权限
echo "\n目录权限:\n";
$dirs = [
    '../config',
    '../storage',
    '../storage/logs',
    '../storage/cache'
];

foreach ($dirs as $dir) {
    $fullPath = realpath(BASE_PATH . '/' . ltrim($dir, '../'));
    echo $dir . ": ";
    if (!$fullPath) {
        echo "不存在\n";
        continue;
    }
    echo "存在 - ";
    echo "权限: " . substr(sprintf('%o', fileperms($fullPath)), -4);
    echo " - " . (is_writable($fullPath) ? "可写" : "不可写") . "\n";
}

// PHP扩展
echo "\nPHP扩展:\n";
$required = ['pdo', 'pdo_mysql', 'redis', 'curl', 'json', 'mbstring'];
foreach ($required as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "已安装" : "未安装") . "\n";
}

// 服务器信息
echo "\n服务器信息:\n";
echo "服务器软件: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "服务器地址: " . $_SERVER['SERVER_ADDR'] . "\n";
echo "服务器名称: " . $_SERVER['SERVER_NAME'] . "\n";

echo '</pre>'; 