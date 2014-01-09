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
	private $_name = 'cookies';

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

	public function getFileName($prefix = ''){
		return $this->getDir() . '/' . $this->getName() . $prefix . '.cookie';
	}

	public function getFileCurlName(){
		return $this->getFileName('-curl');
	}

	public function getFilePhantomJSName(){
		return $this->getFileName('-phantomjs');
	}

	/**
	 * @param bool|string $name
	 */
	function __construct($name = false){
		$this->_list = new cList();
		$this->setDir(dirname(__FILE__) . '/cookies');
		if($name){
			$this->open($name);
		}
	}

	/**
	 * @param string $name
	 */
	public function open($name){
		$this->setName($name);
		$this->_list->open($this->getFileName());
	}

	/**
	 * @param string      $name
	 * @param string      $value
	 * @param string      $domain
	 * @param string      $path
	 * @param bool|string $expires
	 * @param bool        $httponly передавать только в http заголовоках
	 * @param bool        $secure
	 * @param bool        $tailmatch флаг точного совпадения доменного имени сайта
	 */
	public function create($name, $value, $domain, $path = '/', $expires = false, $httponly = false, $secure = false, $tailmatch = true){
		$cookie['name']     = $name;
		$cookie['value']    = $value;
		$cookie['tailmatch']= $tailmatch ? $tailmatch : true;
		$cookie['domain']   = $domain;
		$cookie['path']     = $path;
		$cookie['expires']  = $expires ? $expires : date('l, d-M-y H:i:s e', time() + 86400);
		$cookie['httponly'] = $httponly ? $httponly : false;
		$cookie['secure']   = $secure ? $secure : false;
		$urlData = cStringWork::parseUrl($domain);
		$this->_list->addLevel($urlData['domain']);
		$this->_list->write($urlData['domain'], $cookie, $name);
		$this->_list->update();
	}

	/**
	 * @param string      $url
	 * @param bool|string $name
	 */
	public function delete($url, $name = false){
		$urlData = cStringWork::parseUrl($url);
		if($name){
			$this->_list->deleteLevel($name, $urlData['domain']);
		} else {
			$this->_list->deleteLevel($urlData['domain']);
		}
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public static function fromCurl($text){
		$lines = explode("\n", $text);
		$count = count($lines);
		$cookies = array();
		for($i = 4 ; $i < $count ; $i++){
			$fields = explode("\t", $lines[$i]);
			$cookie['name']     = $fields[5];
			$cookie['value']    = $fields[6];
			$cookie['tailmatch']= $fields[1] == 'TRUE';
			$cookie['domain']   = preg_replace('%^\#HttpOnly_%ims', '', $fields[0]);
			$cookie['path']     = $fields[2];
			$cookie['expires']  = date('l, d-M-y H:i:s e', $fields[4]);
			$cookie['httponly'] = (bool)preg_match('%^\#HttpOnly_%ims', $lines[$i]);
			$cookie['secure']   = $fields[3] == 'TRUE';
			$cookies[] = $cookie;
		}
		return $cookies;
	}

	public function fromFileCurl(){
		$text = file_get_contents($this->getFileCurlName());
		$cookies = $this->fromCurl($text);
		foreach($cookies as $cookie){
			$this->create($cookie['name'],
			              $cookie['value'],
			              $cookie['domain'],
			              $cookie['path'],
			              $cookie['expires'],
			              $cookie['httponly'],
			              $cookie['secure'],
			              $cookie['tailmatch']);
		}
		return $cookies;
	}

	/**
	 * @param string $text
	 */
	public static function fromPhantomJS($text){

	}

	public function fromFilePhantomJS(){
		$text = file_get_contents($this->getFilePhantomJSName());
		$cookies = $this->fromPhantomJS($text);
		foreach($cookies as $cookie){
			$this->create($cookie['name'],
			              $cookie['value'],
			              $cookie['domain'],
			              $cookie['path'],
			              $cookie['expires'],
			              $cookie['httponly'],
			              $cookie['secure'],
			              $cookie['tailmatch']);
		}
		return $cookies;
	}
	/**
	 * @param string $text
	 * @return array
	 */
	public function fromHttp($text){
		$cookies = array();
		if(preg_match_all('%Set-Cookie:\s*(?<name>\w)\s*=\s*(?<value>[^;]*)(?<cookie>.*)%i', $text, $matches)){
			$parametres = array('expires', 'domain', 'path');
			foreach($matches['cookie'] as $key => $cookieLine){
				$cookie = array();
				$cookie['name']  = $matches['name'][$key];
				$cookie['value'] = $matches['value'][$key];
				foreach($parametres as $param){
					if(preg_match('%' . $param . '\s*=\s*(?<val>[^;])*%i', $cookieLine, $match)){
						$cookie[$param] = $match['val'];
					}
				}
				$cookie['secure'] = (bool)preg_match('%;\s*secure\s*(;|$)%i', $cookieLine);
				$cookie['httponly'] = (bool)preg_match('%;\s*httponly\s*(;|$)%i', $cookieLine);
				$cookies[] = $cookie;
			}
		}
		return $cookies;
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public function fromMetaTeg($text){
		$cookies = array();
		if(preg_match_all('%(?<cookie><meta[^>]*Set-Cookie[^>]*>)%i', $text, $matches)){
			$parametres = array('expires', 'domain', 'path');
			foreach($matches['cookie'] as $key => $cookieLine){
				preg_match('%content\s*=\s*(\'|")(?<data>\s*(?<name>\w)\s*=\s*(?<value>[^;]*).*)%i', $cookieLine, $match);
				$cookie = array();
				$cookie['name']  = $match['name'][$key];
				$cookie['value'] = $match['value'][$key];
				foreach($parametres as $param){
					if(preg_match('%' . $param . '\s*=\s*(?<val>[^;])*%i', $cookieLine, $match)){
						$cookie[$param] = $match['val'];
					}
				}
				$cookie['secure'] = (bool)preg_match('%;\s*secure\s*(;|$)%i', $cookieLine);
				$cookie['httponly'] = (bool)preg_match('%;\s*httponly\s*(;|$)%i', $cookieLine);
				$cookies[] = $cookie;
			}
		}
		return $cookies;
	}

	/**
	 * @param $cookie
	 * @return string
	 */
	public static function toCurl($cookie){
		return ($cookie['httponly']?'#HttpOnly_':'') .
		       $cookie['domain'] . "\t" .
		       ($cookie['tailmatch'] ? 'TRUE' : 'FALSE') . "\t" .
		       $cookie['path'] . "\t" .
		       ($cookie['secure'] ? 'TRUE' : 'FALSE') . "\t" .
		       ($cookie['expires'] ? strtotime($cookie['expires']) : 1) . "\t" .
		       $cookie['name'] . "\t" .
		       $cookie['value'];
	}

	public function toFileCurl($url){
		$cookies = $this->getActualCookies($url);
		$str = "\n\n\n\n";
		foreach($cookies as $cookie){
			$str .= $this->toCurl($cookie) . "\n";
		}
		return file_put_contents($this->getFileCurlName(), $str);
	}
	/**
	 * @param array $cookie
	 */
	public function toPhantomJS($cookie){

	}

	public function toFilePhantomJS(){

	}

	/**
	 * @param string $url
	 * @return bool
	 */
	public function getCookies($url){
		$urlData = cStringWork::parseUrl($url);
		return $this->_list->getLevel($urlData['domain']);
	}

	public function getActualCookies($url){
		$cookies = $this->getCookies($url);
		foreach($cookies as $key => $cookie){
			if(!$this->checkExpires($cookie['expires'])){
				unset($cookies[$key]);
			}
		}
		return $cookies;
	}

	/**
	 * Проверяет актуальность
	 * @param string $date
	 * @return bool
	 */
	private function checkExpires($date){
		return (time() > strtotime($date));
	}
} 