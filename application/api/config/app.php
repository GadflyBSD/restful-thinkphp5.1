<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/25
 * Time: 上午10:21
 */
return [
	'app_debug'             => true,            // 应用调试模式
	'app_trace'             => true,            // 应用Trace
	'auto_bind_module'      => true,            // 入口自动绑定模块
	'show_error_msg'        => false,
	'default_return_type'   => 'json',          // 默认输出类型
	'default_ajax_return'   => 'json',          // 默认AJAX 数据返回格式,可选json xml ...
	'allow_cors_request'    => true,            // 允许跨域请求
	'need_signature'        => false,           // 请求需要验签
	/*'log' =>  [
		'type'                => 'socket',
		'host'                => 'slog.thinkphp.cn',
		//日志强制记录到配置的client_id
		'force_client_ids'    => ['slog_8d97b1'],
		//限制允许读取日志的client_id
		'allow_client_ids'    => ['slog_8d97b1'],
	],*/
	'exception_to_sql'	    => true,            // 异常是否记录到数据库中
	'exception_handle'	    => '\assistant\CustomHandle',
	'openssl_cnf'           => 'D:\xampp\php\extras\openssl\openssl.cnf',
	'cache_map' => [
		[
			'title'     => '用户详情数据缓存',
			'prefix'    => 'user',
			'db_table'  => 'user_info_view',
			'db_primary'=> 'id',
			'db_where'  => 'id=?'
		],
		[
			'title'     => '用户列表数据缓存',
			'prefix'    => 'user_list',
			'db_table'  => 'user_list_view',
			'db_primary'=> 'id',
			'db_where'  => null
		],
		[
			'title'     => '用户密钥数据缓存',
			'prefix'    => 'rsa',
			'db_table'  => 'user_rsa_view',
			'db_primary'=> 'id',
			'db_where'  => 'id=?'
		],
	]
];