<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 07.12.13
 * Time: 19:27
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


/**
 * Class cCookie
 * Работа с cookies и конвертирование в разные форматы (phantomJS cURL)
 * @package GetContent
 */
class cCookie {

	/**
	 * @var string
	 */
	private $_dir;

	/**
	 * @param mixed $dir
	 */
	public function setDir($dir) {
		$this->_dir = $dir;
	}

	/**
	 * @return mixed
	 */
	public function getDir() {
		return $this->_dir;
	}

	/**
	 * @var cList
	 */
	private $_list;

	/**
	 * @param \GetContent\cList $cList
	 */
	public function setList($cList) {
		$this->_list = $cList;
	}

	/**
	 * @return \GetContent\cList
	 */
	public function getList() {
		return $this->_list;
	}

	/**
	 * @var string
	 */
	private $_name = 'cookie';

	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->_name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @param bool|string $name
	 */
	function __construct($name = false){
		$this->_list = new cList();
		if($name){
			$this->open($name);
		}
	}

	/**
	 * @param string $name
	 */
	public function open($name){

	}
	/**
	 * @param string      $name
	 * @param string      $value
	 * @param string      $domain
	 * @param string      $path
	 * @param bool|string $expires
	 * @param bool        $httponly
	 * @param bool        $secure
	 */
	public function create($name, $value, $domain, $path = '/', $expires = false, $httponly = false, $secure = false){
		$cookie['name']     = $name;
		$cookie['value']    = $value;
		$cookie['domain']   = $domain;
		$cookie['path']     = $path;
		$cookie['expires']  = $expires ? $expires : date('l, d-M-y H:i:s e', time() + 86400);
		$cookie['httponly'] = $httponly;
		$cookie['secure']   = $secure;
		$this->_list->write($domain, $name, $cookie);
	}

	/**
	 * @param string      $url
	 * @param bool|string $name
	 */
	public function delete($url, $name = false){

	}

	/**
	 * @param string $text
	 */
	public function fromCurl($text){

	}

	/**
	 * @param string $text
	 */
	public function fromPhantomJS($text){

	}

	/**
	 * @param string $text
	 */
	public function fromHttp($text){

	}

	/**
	 * @param string $text
	 */
	public function fromMetaTeg($text){

	}

	/**
	 * @param array $cookie
	 */
	public function toCurl($cookie){

	}

	/**
	 * @param array $cookie
	 */
	public function toPhantomJS($cookie){

	}

	/**
	 * @param string $url
	 */
	public function getCookieForUrl($url){

	}

	public function getCookieForKey($key){

	}

	/**
	 * Проверяет актуальность cookie
	 * @param string $date
	 * @return bool
	 */
	private function checkExpires($date){
		return (time() > strtotime($date));
	}
} 