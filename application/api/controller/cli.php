<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/25
 * Time: 下午3:19
 */

namespace app\api\controller;


class cli extends basic{
	public function test(){
		$args = $this->getCliArgs();
		return $this->response($args);
	}

	public function cr_rsa(){
		return $this->response($this->createRsaKey(0));
	}
}