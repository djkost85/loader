<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 12.01.14
 * Time: 15:47
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cGetContent {
	/**
	 * @var cSingleCurl
	 */
	public $curl;
	/**
	 * @var cPhantomJS
	 */
	public $phantomjs;
	/**
	 * @var cCookie
	 */
	public $cookie;
	private $_mode;
	private $_key;
	private $_referer = 'http://google.com';

	/**
	 * @param string $mode curl | phantom
	 * @return bool
	 */
	public function setMode($mode) {
		switch($mode){
			case 'curl' :
				$this->_mode = $mode;
				$this->phantomToCurl();
				break;
			case 'phantom':
				$this->_mode = $mode;
				$this->curlToPhantom();
				break;
			default:
				return false;
		}
		return true;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		return $this->_mode;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key) {
		$this->_key = $key;
		$this->curl->setKeyStream($this->getKey());
		$this->phantomjs->setKeyStream($this->getKey());
		$this->cookie->open($this->getKey());
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->_key;
	}

	/**
	 * @param string $referer
	 */
	public function setReferer($referer) {
		$this->_referer = $referer;
		$this->curl->setReferer($this->curl->getDescriptor(), $this->_referer);
		$this->phantomjs->setReferer($this->_referer);
	}

	/**
	 * @return string
	 */
	public function getReferer() {
		return $this->_referer;
	}



	function __construct(){
		$this->curl = new cSingleCurl();
		$this->phantomjs = new cPhantomJS(PHANTOMJS_EXE);
		$this->cookie = new cCookie();
		$this->phantomjs->setDefaultOption('load-images', 'false');
		$this->genKey();
		$this->setMode('curl');
	}

	public function __call($name, $arguments){
		if(method_exists($this->phantomjs, $name)){
			return call_user_func_array(array($this->phantomjs, $name), $arguments);
		}
		if(method_exists($this->curl, $name)) {
			return call_user_func_array(array($this->curl, $name), $arguments);
		}
	}

	public function getContent($url, $checkRegEx='%%'){
		switch($this->getMode()){
			case 'curl':
				$answer = $this->curl->load($url, $checkRegEx);
				break;
			case 'phantom':
				do{
					$answer = $this->phantomjs->renderText($url);
					if($this->checkAnswerValid($answer) && preg_match($checkRegEx, $answer)){
						$answer = $this->encodingAnswerText($answer);
						$this->endRepeat();
						break;
					} else {
						$answer = false;
					}
				}while($this->repeat());
				break;
			default:
				return false;
		}
		$this->setReferer($url);
		return $answer;
	}

	public function genKey(){
		$this->setKey(microtime(true).rand());
	}

	private function curlToPhantom(){
		$cookies = $this->cookie->fromFileCurl();
		$this->cookie->creates($cookies);
		$this->cookie->toFilePhantomJS($cookies);
	}

	private function phantomToCurl(){
		$cookies = $this->cookie->fromFilePhantomJS();
		$this->cookie->creates($cookies);
		$this->cookie->toFileCurl($cookies);
	}

	private function checkAnswerValid($answer){
		return (strlen($answer) >= $this->curl->getMinSizeAnswer());
	}
}