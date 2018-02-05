<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/23
 * Time: 下午3:23
 */
namespace think;
/*ini_set("display_errors","On");
error_reporting(E_ALL);*/
/**
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define('RUNTIME_PATH', __DIR__ .'/../../../Runtime/api.tp5.cn/');
// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象
// 执行应用并响应
Container::get('app')->bind('api/api')->run()->send();