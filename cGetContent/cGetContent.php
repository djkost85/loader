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


	private $_curl;
	private $_phantomjs;
	private $_mode;
	private $_key;

	/**
	 * @param mixed $mode
	 */
	public function setMode($mode) {
		$this->_mode = $mode;
	}

	/**
	 * @return mixed
	 */
	public function getMode() {
		return $this->_mode;
	}

	/**
	 * @param mixed $key
	 */
	public function setKey($key) {
		$this->_key = $key;
		$this->_curl->setKeyStream($this->getKey());
		$this->_phantomjs->setKeyStream($this->getKey());
	}

	/**
	 * @return mixed
	 */
	public function getKey() {
		return $this->_key;
	}

	function __construct(){
		$this->_curl = new cSingleCurl();
		$this->_phantomjs = new cPhantomJS(PHANTOMJS_EXE);
		$this->genKey();
		$this->setMode('curl');
	}

	public function genKey(){
		$this->setKey(microtime(true).rand());
	}

	private function curlToPhantom(){

	}
}