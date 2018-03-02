<?php

/**
 * Created by PhpStorm.
 * User: POP
 * Date: 2018-01-20
 *
 * 链式调用, out()输出, 其他可选
 * eg:
 * --------------------------
 * $outputJson = new POP_OutputJson();
 * $outputJson
 *      ->code('return code')
 *      ->data('return data')
 *      ->msg('return msg')
 *      ->info('return info')
 *      ->out();  // {"code":"return code","message":"return msg","data":"return data","info":"return info"}
 * --------------------------
 * $outputJson
 *      ->code('return code', 'myCode') // 第二个参数为自定义键名, 默认 code, 第一个参数为code值，默认为 0
 *      ->data('return data', 'myData') // 第二个参数为自定义键名, 默认 data, 第一个参数为data值，默认为 null
 *      ->msg('return msg', 'myMsg') // 第二个参数为自定义键名, 默认 message, 第一个参数为msg值，默认为 null
 *      ->info('return info','myInfo') // 第二个参数为自定义键名, 默认 info, 第一个参数为info值，默认为 null
 *      ->out();  // {"myCode":"return code","myMsg":"return msg","myData":"return data","myInfo":"return info"}
 * --------------------------
 * 也可以初始化时传入构造参数, 第一个参数修改code默认值, 第二个参数为关联数组, 修改默认键名
 * $outputJson = new POP_OutputJson(100, array('code'=>'retCode','msg'=>'message'));
 * $outputJson
 *      ->data('return data')
 *      ->msg('return msg')
 *      ->info('return info')
 *      ->out();  // {"retCode":100,"message":"return msg","data":"return data","info":"return info"}
 * --------------------------
 * 在CI控制器中使用
 * $this->load->library('POP_OutputJson');
 * $outputJson = $this->pop_outputjson;
 * $outputJson->code()->data()->info()->msg()->out();
 * --------------------------
 */
class POP_OutputJson
{
	// 返回结果code值
	private $retCode = 0;
	// 返回信息
	private $retMsg = '';
	// 返回结果数据
	private $retData = null;
	// 返回结果附加数据
	private $retInfo = null;
	// 返回json的键
	private $retKeys = array(
		'code' => 'code',
		'msg'  => 'message',
		'data' => 'data',
		'info' => 'info');
	// 输出json后是否exit，默认true
	private $exit = true;
	// 不转义斜杠
	private $unEscapedSlashes = true;
	// 不转义unicode
	private $unEscapedUnicode = true;
	private $instance;

	/**
	 * JsonOutput constructor.
	 * @param int $codeDefault 默认返回成功code值
	 * @param array $keys 返回json键名，如array('code'=>'retCode','msg'=>'message'), 输出json时对应键名便是修改后的值
	 */
	public function __construct($codeDefault = 0, $keys = array())
	{
		if (!$this->instance) {
			$this->instance = $this;
		}
		$this->retCode = $codeDefault;
		foreach ($this->retKeys as $key => $value) {
			if (isset($keys[$key]) && !empty($keys[$key])) {
				$this->retKeys[$key] = $keys[$key];
			}
		}
	}

	/**
	 * 设置输出结果的code
	 * @param int $code code
	 * @param string $key code的键名
	 * @return POP_OutputJson
	 */
	public function code($code = 1, $key = '')
	{
		$this->retCode = $code;
		$this->retKeys['code'] = empty($key) ? $this->retKeys['code'] : $key;
		return $this->instance;
	}

	/**
	 * 设置输出结果的msg
	 * @param string $msg msg
	 * @param string $key msg的键名
	 * @return POP_OutputJson
	 */
	public function msg($msg = '', $key = '')
	{
		$this->retMsg = $msg;
		$this->retKeys['msg'] = empty($key) ? $this->retKeys['msg'] : $key;
		return $this->instance;
	}

	/**
	 * 设置输出结果的data
	 * @param null $data data
	 * @param string $key data的键名
	 * @return POP_OutputJson
	 */
	public function data($data = null, $key = '')
	{
		$this->retData = $data;
		$this->retKeys['data'] = empty($key) ? $this->retKeys['data'] : $key;
		return $this->instance;
	}

	/**
	 * 设置输出结果的info
	 * @param null $info info
	 * @param string $key info的键名
	 * @return POP_OutputJson
	 */
	public function info($info = null, $key = '')
	{
		$this->retInfo = $info;
		$this->retKeys['info'] = empty($key) ? $this->retKeys['info'] : $key;
		return $this->instance;
	}

	/**
	 * 设置输出json后是否exit
	 * @param bool $exit 默认true，即输出json后exit
	 * @return POP_OutputJson
	 */
	public function isExit($exit = true)
	{
		$this->exit = $exit;
		return $this->instance;
	}

	/**
	 * 设置是否不转义斜杠（仅在php >= 5.4）
	 * @param bool $flg 默认true，不转义斜杠
	 * @return POP_OutputJson
	 */
	public function unEscapedSlashes($flg = true)
	{
		$this->unEscapedSlashes = $flg;
		return $this->instance;
	}

	/**
	 * 设置是否不转义unicode（仅在php >= 5.4）
	 * @param bool $flg 默认true，不转义unicode
	 * @return POP_OutputJson
	 */
	public function unEscapedUnicode($flg = true)
	{
		$this->unEscapedUnicode = $flg;
		return $this->instance;
	}

	/**
	 * 输出json到浏览器
	 */
	public function out()
	{
		$data = array(
			$this->retKeys['code'] => $this->retCode,
			$this->retKeys['msg']  => $this->retMsg,
			$this->retKeys['data'] => $this->retData,
			$this->retKeys['info'] => $this->retInfo
		);

		$options = 0;
		if (defined('JSON_UNESCAPED_SLASHES') && $this->unEscapedSlashes) {
			$options = $options | JSON_UNESCAPED_SLASHES;
		}
		if (defined('JSON_UNESCAPED_UNICODE') && $this->unEscapedUnicode) {
			$options = $options | JSON_UNESCAPED_UNICODE;
		}

		if ($this->isIE()) {
			header('Content-type: text/html;charset=UTF-8');
		} else {
			header('Content-type: application/json;charset=UTF-8');
		}

		if ($options != 0) {
			echo json_encode($data, $options);
		} else {
			echo json_encode($data);
		}
		$this->exit && exit();
	}

	/**
	 * @return bool|int
	 */
	private function isIE()
	{
		$isIE = strpos($_SERVER['HTTP_USER_AGENT'], "Triden");
		return $isIE;
	}
}