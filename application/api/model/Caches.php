<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/2/1
 * Time: 下午5:27
 */

namespace app\api\model;

use think\Model;
use think\facade\Cache;
use assistant\ProgramExeception;

class Caches extends model{
	protected $name = 'cache_map';
	protected $pk = 'id';

	public function buildMap(){
		$cache = Cache::init();
		$handler = $cache->handler();
		foreach($this->column('prefix') AS $val){
			$keys = $handler->keys($val.'*');
			$handler->del($keys);
		}
		if($this->insertAll(config('cache_map')) == count(config('cache_map')))
			return count(config('cache_map'));
		else
			throw new ProgramExeception(600, '向数据库添加缓存映射时出现错误！', config('cache_map'));
	}

	public function getRsaKey($change, $uid = 0){
		$cache = ['key' => 'rsa-'.$uid, 'type' => 'rsa', 'pk' => $uid, 'uid' => $uid];
		if(in_array($change, ['createRsaKey', 'changeRsaKey'])){
			return $this->getCache($cache, true);
		}else{
			return $this->getCache($cache);
		}
	}

	/**
	 * 验证客户端发送过来的缓存合法性请求
	 * @param $param
	 * @return 返回请求结果
	 * 		type:	请求返回类型
	 * 		verify:	是否验证结果, true -- 需要重新缓存, false -- 不需要
	 * 		cache:	缓存的校验数据 {name: '', pk: '', md5: '', sha1: ''}
	 * 		data:	缓存的列表或详情数据, 所有列表数据 {name: [],...}
	 */
	public function verifyCache($param, $uid = 0, $refresh = false){
		$cache = array();
		foreach ($param AS $key => $val){
			$place = explode('-', $val['key']);
			$val['uid'] = $uid;
			$val['pk'] = ($val['pk'])?$val['pk']:$place[1];
			$val['type'] = $val['type']?$val['type']:$place[0];
			$val['md5'] = $val['md5']?$val['md5']:md5(time());
			$val['sha1'] = $val['sha1']?$val['sha1']:sha1(time());
			$cache[$val['key']] = $this->getCache($val, $refresh);
		}
		return $cache;
	}

	/**
	 * # 校验指定数据的md5和sha1, 与服务器对应 则返回array('result' => true), 否则返回该缓存数据
	 * @param $param
	 * @param bool $refresh
	 * @return array
	 */
	public function getCache($param, $refresh = false){
		if($refresh){
			Cache::rm($param['key']);
			$verify = false;
		}else{
			$cache = Cache::get($param['key']);
			if($cache && is_array($cache)){
				$verify = json_decode($cache, true);
				$verify['action'] = 'getCache';
			}else{
				$verify = false;
			}
		}
		if(!$verify && !is_array($verify)){
			$data = ($param['key'] == 'rsa-0')?createRsaKey($param['pk']):null;
			$cache = $this->setCache($param['key'], $data);
			$verify = $cache['response'];
			$verify['action'] = 'setCache';
		}
		if(isset($param['md5']) && $param['md5'] == $verify['md5'] && isset($param['sha1']) && $param['sha1'] == $verify['sha1'])
			return ['verify' => true, 'name' => $param['type'], 'key' => $param['key']];
		else
			return array_merge(['verify' => false, 'name' => $param['type']], $verify);
	}

	/**
	 * # 缓存指定数据
	 * @param      $key
	 * @param null $data
	 * @return array
	 */
	public function setCache($key, $data=null){
		$place = explode('-', $key);
		if(is_null($data)){
			$map = $this->where('prefix', $place[0])->find();
			$object = $this->name($map['db_table']);
			if(!is_null($map['db_where'])) $object->where(str_replace($map['db_where'], '?', $place[1]));
			if(!is_null($map['db_order'])) $object->where($map['db_order']);
			if(!is_null($map['db_group'])) $object->where($map['db_group']);
			if(!is_null($map['db_limit'])) $object->where($map['db_limit']);
			$data = $object->select();
		}
		if(!$data || is_null($data)){
			if(Cache::get($key)) Cache::rm($key);
			throw new ProgramExeception(300, '缓存数据时出错, 数据库相关数据获取失败, 请与管理员联系');
		}else{
			$verify = array('md5' => md5(serialize($data)), 'sha1' => sha1(serialize($data)), 'key' => $key);
			if(count($place) >= 2) $verify['pk'] = $place[count($place)-1];
			$response = array_merge($verify, ['data' => $data]);
			Cache::set($key, json_encode_plus($response), 2592000);
			return array('type' => 'Success', 'msg' => '已经成功缓存数据!', 'response' => $response);
		}
	}

}