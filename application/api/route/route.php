<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/25
 * Time: 下午12:59
 */
//use think\Route;
Route::rule('restful','api/restful/index');
Route::resource('task','Api/api/Task');
/*return [
	'restful'        =>  'Api/api/restful'
];*/
