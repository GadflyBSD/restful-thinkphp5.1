<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/24
 * Time: 下午5:25
 */

namespace app\api\controller;

use think\Controller;
use think\facade\App;
use think\facade\Cache;
use think\facade\Debug;
use think\facade\Request;
//use app\api\model\Caches;
use assistant\ProgramExeception;

class basic extends Controller{
	protected $request;

	public function _initialize(){
		if(config('app_debug')) Debug::remark('begin');
		if(config('allow_cors_request')){
			header("Content-type: json; charset=utf-8");
			header('Access-Control-Allow-Origin:*');
			header('Access-Control-Allow-Methods:POST, GET, PUT, OPTIONS, DELETE');
			header('Access-Control-Allow-Headers:x-requested-with,content-type');
			header("Access-Control-Allow-Credentials: true");
		}
		$this->request = Request::param();
	}

	public function _empty(){
		throw new ProgramExeception(203, '请求异常：未指定控制器方法');
	}

	public function restful(){
		return $this->response($this->run($this->request($this->request->param())));
	}

	public function cli(){
		return $this->response($this->run($this->request($this->getCliArgs())));
	}

	/**
	 * 获取当前模块、控制器和方法的名称
	 * @return array
	 */
	public function current(){
		return [
			'type'      => 'Success',
			'code'      => 200,
			'message'   => '成功获取当前模块、控制器和方法的名称',
			'module'    => Request::module(),
			'controller'=> Request::controller(),
			'action'    => Request::action()];
	}

	public function test(){
		/*$redis = new \Redis;
		$redis->connect(config('redis.host'), config('redis.port'), 1);
		$keys = $redis->keys('*dg*');
		return $redis->del($keys);*/
		//$data = db::name('exception')->where('code', 450)->select();
		//$redis->set('exception_450_list', json_encode($data));
		//return json_decode($redis->get('exception_450_list'));
		/*$cache = Cache::init();
		$handler = $cache->handler();
		//$data = db::name('exception')->where('code', 10501)->select();
		//Cache::set('exception_10501_list', json_encode($data));
		$keys = $handler->keys('*');
		$handler->del($keys);
		return $handler->keys('*');*/
		$Caches = model('Caches');
		//$return = db::name('cache_map')->insertAll(config('cache_map'));
		return $Caches->createMap();
	}

	protected function run($param){
		$return = [];
		if(!is_null($param['route']))
			$return['route'] = $this->router(array_merge($param['route'], ['data' => $param['data']]));
		if(!is_null($param['merge']))
			$return['merge'] = $this->router($param['merge']);
		if(!is_null($param['check']))
			$return['check'] = $this->verifyCache($param['check'], $param['whose']['uid']);
		return $return;
	}

	/**
	 * # API 接口路由模式
	 * @param $param array
	 *      * module:       请求模块名称，默认为当前模块
	 *      * contorller：   请求控制器名称，默认为当前控制器
	 *      * action：       请求方法名称， 默认为当前方法
	 *      * model：        请求模型名称， 默认为`restful`模型
	 *
	 * @return array
	 */
	protected function router($param){
		$module = (isset($param['module']))?ucfirst($param['module']):Request::module();
		$controller = (isset($param['controller']))?ucfirst($param['controller']):Request::controller();
		$action = (isset($param['action']))?ucfirst($param['action']):Request::action();
		if(isset($param['model'])){
			$model = ucfirst($param['model']);
			if(method_exists(App::model($module.'/'.$model), $action))
				return call_user_func(array(App::model($module.'/'.$model), $action), $param['data']);
			else
				throw new ProgramExeception(201, '系统在'.$model.'模型中没有找到'.$action.'方法', $param);
		}else{
			if(method_exists(App::controller($module.'/'.$controller), $action))
				return call_user_func(array(App::controller($module.'/'.$controller), $action), $param['data']);
			else
				throw new ProgramExeception(202, '系统在'.$controller.'控制器中没有找到'.$action.'方法', $param);
		}
	}

	/**
	 * # 处理请求数据
	 * @return array
	 */
	protected function request($request){
		$param = [
			'data'      => Request::has('data')?value_to_array($request['data']):[],
			'whose'     => Request::has('whose')?value_to_array($request['whose']):null,
			'route'     => Request::has('route')?value_to_array($request['route']):null,
			'check'     => Request::has('check')?value_to_array($request['check']):null,
			'merge'     => Request::has('merge')?value_to_array($request['merge']):null,
		];
		if(!is_null($param['whose'])){
			if(isset($param['whose']['value']))
				$whose = privateKeyDecode($param['whose']['value']);
			else
				$whose = $param['whose'];
		}else{
			$whose = ['uid' => 0];
		}
		$rsa = $this->getRsaKey(null, $whose['uid']);
		$signature = $this->_signature([
			'secret' => Request::has('secret')?$request['secret']:null,
			'sign' => Request::has('sign')?$request['sign']:null
		]);
		if($signature['type'] == 'Success'){
			if(Request::has('rsa_data')){
				if(is_string($request['rsa_data']))
					$rsa_data = json_decode(privateKeyDecode($param['rsa_data'], $rsa['data']['server_private']), true);
				else if(is_array($request['rsa_data']))
					$rsa_data = $request['rsa_data'];
				else
					$rsa_data = [];
			}else{
				$rsa_data = [];
			}
			if(!is_null($param['route'])){
				if(isset($param['route']['value']))
					$route = json_decode(privateKeyDecode($param['route']['value'], $rsa['data']['server_private']), true);
				else
					$route = $param['route'];
			}else{
				$route = null;
			}
			return [
				'data'  => array_merge($param['data'] ,$rsa_data),
				'whose' => $whose,
				'check' => $param['check'],
				'route' => $route,
				'merge' => $param['merge']
			];
		}
	}

	/**
	 * 获取命令行参数
	 * @return array
	 */
	protected function getCliArgs(){
		$server = Request::server();
		$controllerAndModule = explode('/',$server['argv'][1]);
		$argv = [
			'url'       => $server['argv'][0],
			'controller'=> $controllerAndModule[0],
			'module'    => $controllerAndModule[1],
		];
		array_splice($server['argv'], 0, 2);
		foreach($server['argv'] AS $val){
			$args = explode('=',$val);
			$argv[$args[0]] = $args[1];
		}
		return $argv;
	}

	/**
	 * # 封装请求返回数据
	 * @param        $data  返回的数据
	 * @param string $msg   返回提示
	 * @param string $type  返回类型
	 * @param string $code  返回代码
	 * @return array
	 */
	protected function response($data, $msg = '操作成功！', $type = 'Success', $code = '200'){
		if(config('app_debug')){
			Debug::remark('begin');
			$debug = [
				'request'   => [
					'url'       => Request::url(true),
					'header'    => Request::header(),
					'module'    => Request::module(),
					'controller'=> Request::controller(),
					'action'    => Request::action(),
					'route'     => Request::route(),
					'dispatch'  => Request::dispatch(),
					'request'   => Request::param(),
					'method'    => Request::method(),
					'ip'        => Request::ip(),
				],
				'range'     => [
					'debugTime' => Debug::getRangeTime('begin','end',6).'s',
					'debugMem'  => Debug::getRangeMem('begin','end'),
				]
			];
		}else{
			$debug = [];
		}
		return array_merge(['code' => $code, 'type' => $type, 'message' => $msg, 'data' => $data], $debug);
	}

	/**
	* 从数据库中获取需要缓存数据
	* @param $param
	* @return mixed
	*/
	protected function getDataBySQL($param){
		switch ($param['type']){
			case 'rsa':
				return createRsaKey($param['pk']);
				break;
			case 'provice':
				$data = array('type' => 'provice', 'pk' => $param['pk']);
				$return = D('Position')->getList(array('data' => $data));
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取省级数据时出错, 请与管理员联系');
				break;
			case 'city':
				$data = array('type' => 'city', 'pk' => $param['pk']);
				$return = D('Position')->getList(array('data' => $data));
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取市级数据时出错, 请与管理员联系');
				break;
			case 'county':
				$data = array('type' => 'county', 'pk' => $param['pk']);
				$return = D('Position')->getList(array('data' => $data));
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取区县级数据时出错, 请与管理员联系');
				break;
			case 'town':
				$data = array('type' => 'town', 'pk' => $param['pk']);
				$return = D('Position')->getList(array('data' => $data));
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取乡镇级数据时出错, 请与管理员联系');
				break;
			case 'village':
				$data = array('type' => 'village', 'pk' => $param['pk']);
				$return = D('Position')->getList(array('data' => $data));
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取村/社区级数据时出错, 请与管理员联系');
				break;
			case 'user':
				$return = D('MustachUser')->getBindUserInfo($param['pk']);
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取指定用户绑定数据时出错, 请与管理员联系');
				break;
			case 'account':
				$return = D('MustachUser')->getAccountInfo($param['pk']);
				if(strtolower($return['type']) == 'success')
					return $return['data'];
				else
					return array('type' => 'Error', 'msg' => '获取指定用户统计数据时出错, 请与管理员联系', 'return' => $return);
				break;
		}
	}

	/**
	 * 签名验证方法, 签名认证成功后将请求数据转换成数组格式返回
	 * @param $param
	 * @return mixed
	 */
	private function _signature($param){
		if(config('need_signature')){
			if($param['secret'] && ($param['secret'] == self::secret && APP_DEBUG)){
				return ['type'  => 'Success', 'msg' => '用户请求签名验签成功！'];
			}else{
				if($param['sign'] && !is_null($param['sign'])){
					if($param['sign'] == strtoupper(bin2hex(\Encryption::encrypt($param['request'], self::secret)))){
						return ['type'  => 'Success', 'msg' => '用户请求签名验签成功！'];
					}else{
						$debug = ['sign' => $param['sign'], 'signature' => strtoupper(bin2hex(\Encryption::encrypt($param['request'], self::secret)))];
						throw new ProgramExeception(101, '用户请求签名验证出错!', $debug);
					}
				}else{
					throw new ProgramExeception(102, '用户请求签名验证不能为空!');
				}
			}
		}else{
			return ['type'  => 'Success', 'msg' => '用户请求签名验签成功！'];
		}
	}
}