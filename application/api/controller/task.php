<?php

namespace app\api\controller;

use think\exception\ValidateException;
use think\Validate;
use think\Log;

class task extends api
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
    	$param = $this->request->param();
    	$rsa = $this->getRsaKey(null,0);
    	$array = array_merge(['key' => 'uid-0', 'name' => 'GadflyBSD', 'ages' => 42], $param);
    	$return['public_encode'] = publicKeyEncode(json_encode_plus($array), $rsa['data']['server_public']);
    	$return['private_decode'] = json_decode(privateKeyDecode($return['public_encode'], $rsa['data']['server_private']), true);
	    $return['private_encode'] = privateKeyEncode(json_encode_plus($array), $rsa['data']['server_private']);
	    $return['public_decode'] = json_decode(publicKeyDecode($return['private_encode'], $rsa['data']['server_public']), true);
	    Log::write($array,'notice');
    	return $this->response($return);
        //
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id){
	    $rule = [
		    'id'  => 'require|max:5',
	    ];
	    $message  =   [
		    'id.require' => '名称必须',
		    'id.max'     => '名称最多不能超过25个字符',
	    ];
	    $data = [
	    	'id'    => $id,
		    'name'  => 'thinkphp',
		    'age'   => 10,
		    'email' => 'thinkphp@qq.com',
	    ];
	    $validate = new Validate($rule, $message);
	    if(!$validate->check($data)){
		    throw new ValidateException($validate->getError());
	    }else{
		    //throw new \ProgramExeception(10086, '程序异常消息', ['debug' => $data, 'sql' => 'select']);
		    //Log::write('测试日志信息，这是警告级别，并且实时写入','notice');
		    return $this->response($this->request());
	    }
	    //throw new \Custom\ProgramExeceptions(10086,'程序异常消息', ['debug' => 'asaa', 'sql' => 'select']);
    	//exception('自定义异常消息', 100006);
	    //return EXTEND_PATH;
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }

}
