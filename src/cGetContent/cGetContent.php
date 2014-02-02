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

	function __construct(){
		$this->curl = new cSingleCurl();
		$this->phantomjs = new cPhantomJS(PHANTOMJS_EXE);
		$this->cookie = new cCookie();
		$this->phantomjs->setDefaultOption('load-images', 'false');
		$this->genKey();
		$this->setMode('curl');
	}

	public function getContent($url){
		switch($this->getMode()){
			case 'curl':
				return $this->curl->getContent($url);
				break;
			case 'phantom':
				return $this->phantomjs->renderText($url);
				break;
			default:
				return false;
		}
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
}