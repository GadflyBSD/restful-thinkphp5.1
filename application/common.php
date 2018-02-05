<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start, $length, $charset="utf-8", $suffix=true) {
	if(function_exists("mb_substr"))
		$slice = mb_substr($str, $start, $length, $charset);
	elseif(function_exists('iconv_substr')) {
		$slice = iconv_substr($str,$start,$length,$charset);
		if(false === $slice) {
			$slice = '';
		}
	}else{
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice.'...' : $slice;
}

/**
 * 返回中文不进行编码和路径不加斜杠的json格式数据, 针对PHP 5.4以上或以下均有效
 * @param $array
 *
 * @return mixed|string
 */
function json_encode_plus($array){
	if(version_compare(PHP_VERSION, '5.4.0') >= 0){
		return json_encode($array, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}else{
		arrayRecursive($array, 'urlencode', true);
		return str_replace("\\/", "/", urldecode(json_encode($array)));
	}
}

function arrayRecursive(&$array, $function, $apply_to_keys_also = false){
	static $recursive_counter = 0;
	if (++$recursive_counter > 1000) {
		die('possible deep recursion attack');
	}
	foreach($array as $key => $value){
		if(is_array($value)){
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		}else{
			$array[$key] = $function($value);
		}
		if($apply_to_keys_also && is_string($key)){
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter--;
}

//对象转数组,使用get_object_vars返回对象属性组成的数组
function objectToArray($obj){
	$arr = is_object($obj) ? get_object_vars($obj) : $obj;
	if(is_array($arr)){
		return array_map(__FUNCTION__, $arr);
	}else{
		return $arr;
	}
}

//数组转对象
function arrayToObject($arr){
	if(is_array($arr)){
		return (object) array_map(__FUNCTION__, $arr);
	}else{
		return $arr;
	}
}

/**
 * 判断是否是索引数组
 * @param $array
 * @return bool
 */
function is_assoc($array) {
	if(is_array($array)) {
		$keys = array_keys($array);
		return $keys != array_keys($keys);
	}
	return false;
}

/**
 * 判断数据是否是json数据
 * @param $string
 * @return bool
 */
function is_json($string) {
	$param = trim(trim($string, '"'), "'");
	return (!is_null(json_decode($param)) && (json_last_error() == JSON_ERROR_NONE));
}

/**
 * 将数据（包括数组、json字符串和其他类型数据）转换成数组格式
 * @param $param
 * @return array|mixed
 */
function value_to_array($string){
	$param = trim(trim($string, '"'), "'");
	if(is_string($param))
		return is_json($param)?json_decode($param,true):['value' => $param];
	elseif(is_array($param))
		return $param;
	else
		return array('value' => $param);
}

/**
 * 创建生成服务器和客户端RSA密钥
 *
 * @return array|mixed
 */
function createRsaKey($pk){
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
		$config = ['config' => config('openssl_cnf')];
	}else{
		$config = [
			"digest_alg"        => "sha512",
			"private_key_bits"  => 512,
			"private_key_type"  => OPENSSL_KEYTYPE_RSA,
		];
	}
	$res1 = openssl_pkey_new($config);
	openssl_pkey_export($res1, $privateKeyService, null, $config);
	$publicKeyService = openssl_pkey_get_details($res1);
	$res2 = openssl_pkey_new($config);
	openssl_pkey_export($res2, $privateKeyClient, null, $config);
	$publicKeyClient = openssl_pkey_get_details($res2);
	return [
		'key'				=> 'rsa-'.$pk,
		'type'              => 'Success',
		'server_public'		=> $publicKeyService['key'],
		'server_private'	=> $privateKeyService,
		'client_public'		=> $publicKeyClient['key'],
		'client_private'	=> $privateKeyClient,
		'create_dateline'	=> time(),
	];
}

/**
 * 公钥加密
 *
 * @param string 明文
 * @param string 证书文件（.crt）
 *
 * @return string 密文（base64编码）
 *
 * //JS->PHP 测试
 * $txt_en = $_POST['password'];
 * $txt_en = base64_encode(pack("H*", $txt_en));
 * $file = 'ssl/server.pem';
 * $txt_de = $this->privateKeyDecode($txt_en, $file, TRUE);
 * var_dump($txt_de);
 * //PHP->PHP 测试
$encrypt = $this->_publicKeyEncode('{"name":"公钥加密私钥解密测试","password":"dg123456"}');
$decrypt = $this->_privateKeyDecode($encrypt);
echo '<h2>公钥加密, 私钥解密</h2>';
echo 'encode: <p>'.$encrypt.'</p><br>';
echo 'dncode: '.$decrypt.'<br>';
echo '<br><hr>';
$encrypt = $this->_privateKeyEncode('{"name":"私钥加密公钥解密测试","password":"pw123456"}');
$decrypt = $this->_publicKeyDecode($encrypt);
echo '<h2>私钥加密, 公钥解密</h2>';
echo 'encode: <p>'.$encrypt.'</p><br>';
echo 'dncode: '.$decrypt.'<br>';
echo '<br><hr>';
 */
function publicKeyEncode($sourcestr, $key, $tojs = FALSE){
	//$pubkeyid = openssl_get_publickey(file_get_contents(self::PUBLIC_KEY));
	$pubkeyid = openssl_get_publickey($key);
	$padding = $tojs?OPENSSL_NO_PADDING:OPENSSL_PKCS1_PADDING;
	if(openssl_public_encrypt($sourcestr, $crypttext, $pubkeyid, $padding)){
		return base64_encode("".$crypttext);
	}
}

/**
 * 公钥解密
 * @param string    $crypttext   需解密的字符串
 * @param bool      $fromjs      密文是否来源于JS的RSA加密
 *
 * @return string|void      解密后的字符串
 */
function publicKeyDecode($crypttext, $key, $fromjs = FALSE){
	//$pubkeyid = openssl_get_publickey(file_get_contents(self::PUBLIC_KEY));
	$pubkeyid = openssl_get_publickey($key);
	$padding = $fromjs ? OPENSSL_NO_PADDING : OPENSSL_PKCS1_PADDING;
	$sourcestr = '';
	if(openssl_public_decrypt(base64_decode($crypttext), $sourcestr, $pubkeyid, $padding)){
		return $fromjs ? rtrim(strrev($sourcestr), "/0") : "".$sourcestr;
	}
	return ;
}

/**
 * 私钥加密
 * @param $sourcestr    需加密的数据字符串
 *
 * @return string       加密后的字符串
 */
function privateKeyEncode($sourcestr, $key, $tojs = FALSE){
	//$prikeyid = openssl_get_privatekey(file_get_contents(self::PRIVATE_KEY));
	$prikeyid = openssl_get_privatekey($key);
	$padding = $tojs?OPENSSL_NO_PADDING:OPENSSL_PKCS1_PADDING;
	if(openssl_private_encrypt($sourcestr, $crypttext, $prikeyid, $padding)){
		return base64_encode("".$crypttext);
	}
}


/**
 * 私钥解密
 *
 * @param string    $crypttext 密文（二进制格式且base64编码）
 * @param bool      $fromjs    密文是否来源于JS的RSA加密
 *
 * @return string 明文
 */
function privateKeyDecode($crypttext, $key, $fromjs = FALSE){
	//$prikeyid = openssl_get_privatekey(file_get_contents(self::PRIVATE_KEY));
	$prikeyid = openssl_pkey_get_private($key);
	$padding = $fromjs ? OPENSSL_NO_PADDING : OPENSSL_PKCS1_PADDING;
	$sourcestr = '';
	openssl_private_decrypt(base64_decode($crypttext), $sourcestr, $prikeyid, $padding);
	return $fromjs ? rtrim(strrev($sourcestr), "/0") : $sourcestr;
}