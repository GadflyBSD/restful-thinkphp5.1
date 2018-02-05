<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/31
 * Time: 上午9:55
 */

namespace app\api\controller;

use think\facade\Request;

class restful extends basic{
	public function index(){
		return Request::module() . '/' . request()->controller() . '/' . request()->action();
	}
}