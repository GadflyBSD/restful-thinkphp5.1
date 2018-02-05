<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/31
 * Time: 上午11:08
 */

namespace app\api\model;

use think\Model;
use think\facade\Cache;
use think\facade\Debug;

class basic extends Model{
	/**
	 * 通过CURL或者封装过的Snoopy方式像微信服务器发送指令,GET或者POST方法提交数据返回结果
	 * @param        $url       提交数据的接收地址,如果是GET方法,该地址不包含?后的数据
	 * @param        $data      提交的数据GET方式为?后的部分,POST为一个表单的JSON数据
	 * @param string $method    数据提交的方法,GET(默认)或者POST
	 * @param bool   $ssl       是否SSL加密,默认为True
	 *
	 * @return string   返回服务器返回的结果
	 */
	protected function curlData($url, $data, $method='GET', $ssl=true){
		switch($method){
			case 'GET':
				$getUrl = $url.'?'.$data;
				$ch = curl_init($getUrl);
				curl_setopt($ch, CURLOPT_URL, $getUrl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				break;
			case 'POST':
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 500);
		if($ssl){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$results= curl_exec($ch);
		curl_close($ch);
		return $results;
	}
}