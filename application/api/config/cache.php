<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/2/1
 * Time: 下午3:30
 */
return [
	// 驱动方式
	'type'   => 'redis',
	// 缓存保存目录
	'host'   => '127.0.0.1',
	// 缓存前缀
	'prefix' => 'api_',
	// 缓存有效期 0表示永久缓存
	'expire' => 0,
];