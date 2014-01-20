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
	 * @param string $name
	 */
	public function setName($name) {
		$this->_name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	public function getFileName($prefix = ''){
		return $this->getDir() . DIRECTORY_SEPARATOR . $this->getName() . $prefix . '.cookie';
	}

	public function getFileCurlName(){
		return $this->getFileName('-curl');
	}

	public function getFilePhantomJSName(){
		return $this->getFileName('-phantomjs');
	}

	/**
	 * @return mixed
	 */
	public static function getGmtOffset() {
		return ((int)date('O')/100 * 3600);
	}

	/**
	 * @param bool|string $name
	 */
	function __construct($name = false){
		$this->_list = new cList();
		$this->setDir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cookies');
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
		if(!file_exists($this->getFilePhantomJSName())){
			file_put_contents($this->getFilePhantomJSName(), '');
		}
		if(!file_exists($this->getFileCurlName())){
			file_put_contents($this->getFileCurlName(), '');
		}
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
		$cookie['tailmatch']= (bool)$tailmatch;
		$cookie['domain']   = $domain;
		$cookie['path']     = $path;
		$cookie['expires']  = $expires ? $expires : date('D, d-M-y H:i:s', time() + 86400 - self::getGmtOffset()) . ' GMT';
		$cookie['httponly'] = (bool)$httponly;
		$cookie['secure']   = (bool)$secure;
		$domain = cStringWork::getDomainName($domain);
		$this->_list->addLevel($domain);
		$this->_list->write($domain, $cookie, $name);
		$this->update();
	}

	public function creates($cookies){
		foreach($cookies as $cookie){
			@$this->create($cookie['name'], $cookie['value'], $cookie['domain'], $cookie['path'], $cookie['expires'], $cookie['httponly'], $cookie['secure'], $cookie['tailmatch']);
		}
	}

	public function update(){
		$this->_list->update();
	}
	/**
	 * @param string      $url
	 * @param bool|string $name
	 */
	public function delete($url, $name = false){
		$domain = cStringWork::getDomainName($url);
		if($name){
			$this->_list->clear($name, $domain);
		} else {
			$this->_list->clear($domain);
		}
	}

	public function deleteOldCookieFile($storageTime = 172800){
		$fileList = glob($this->getDir() . "/*.cookie");
		foreach ($fileList as $value) {
			$fileInfo = stat($value);
			if ($fileInfo['ctime'] < time() - $storageTime){
				unlink($value);
			}
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
			$fields = array_map('trim', explode("\t", $lines[$i]));
			$cookie['name']     = $fields[5];
			$cookie['value']    = $fields[6];
			$cookie['tailmatch']= $fields[1] == 'TRUE';
			$cookie['domain']   = preg_replace('%^\#HttpOnly_%ims', '', $fields[0]);
			$cookie['path']     = $fields[2];
			$cookie['expires']  = date('D, d-M-y H:i:s', $fields[4] - self::getGmtOffset()) . " GMT";
			$cookie['httponly'] = (bool)preg_match('%^\#HttpOnly_%ims', $lines[$i]);
			$cookie['secure']   = $fields[3] == 'TRUE';
			$cookies[$cookie['name']] = $cookie;
		}
		return $cookies;
	}

	public function fromFileCurl(){
		$text = file_get_contents($this->getFileCurlName());
		$cookies = $this->fromCurl($text);
		$this->creates($cookies);
		return $cookies;
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public static function fromPhantomJS($text){
		$phantomCookies = json_decode($text, true);
		$cookies = array();
		foreach ($phantomCookies as $cookie) {
			$cookie['expires'] = date('D, d-M-y H:i:s', $cookie['expiry'] - self::getGmtOffset()) . ' GMT';
			$cookies[$cookie['name']] = $cookie;
		}
		return $cookies;
	}

	public function fromFilePhantomJS(){
		/**
		 * @var cPhantomJS
		 */
		$phantom = new cPhantomJS(PHANTOMJS_EXE);
		$phantom->setCookieFile($this->getName());
		$cookies = $phantom->getCookie();
		$cookies = $this->fromPhantomJS($cookies);
		$this->creates($cookies);
		return $cookies;
	}
	/**
	 * @param string $text
	 * @return array
	 */
	public function fromHttp($text){
		$cookies = array();
		if(preg_match_all('%Set-Cookie:\s*(?<name>\w+)\s*=\s*(?<value>[^;]+)(?<cookie>.*)%i', $text, $matches)){
			$parametres = array('expires', 'domain', 'path');
			foreach($matches['cookie'] as $key => $cookieLine){
				$cookie = array();
				$cookie['name']  = $matches['name'][$key];
				$cookie['value'] = $matches['value'][$key];
				foreach($parametres as $param){
					if(preg_match('%' . $param . '\s*=\s*(?<val>[^;]+)%i', $cookieLine, $match)){
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
			foreach($matches['cookie'] as $cookieLine){
				if(!preg_match('%content\s*=\s*(\'|")\s*(?<name>\w+)\s*=\s*(?<value>[^;]+)%i', $cookieLine, $match)){
					continue;
				}
				$cookie = array();
				$cookie['name']  = $match['name'];
				$cookie['value'] = $match['value'];
				foreach($parametres as $param){
					if(preg_match('%' . $param . '\s*=\s*(?<val>[^;]+)%i', $cookieLine, $match)){
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

	public function toFileCurl($cookies){
		$str = "\n\n\n\n";
		$cookiesLines = array();
		foreach($cookies as $cookie){
			$cookiesLines[] = $this->toCurl($cookie);
		}
		$str .= implode("\n",$cookiesLines);
		return file_put_contents($this->getFileCurlName(), $str);
	}

	/**
	 * @param array $cookies
	 * @return array
	 */
	public function toPhantomJS($cookies){
		return array_values($cookies);
	}

	public function toFilePhantomJS($cookies){
		/**
		 * @var cPhantomJS
		 */
		$phantom = new cPhantomJS(PHANTOMJS_EXE);
		$phantom->setCookieFile($this->getName());
		$cookies = $this->toPhantomJS($cookies);
		return $phantom->addCookie($cookies);
	}

	/**
	 * @param string $url
	 * @return array|null
	 */
	public function getCookies($url){
		$this->update();
		return $this->_list->getLevel(cStringWork::getDomainName($url));
	}

	public function getActualCookies($url){
		$cookies = $this->getCookies($url);
		foreach($cookies as $key => $cookie){
			if(!$this->checkExpiresCookie($cookie['expires'])){
				unset($cookies[$key]);
			}
		}
		return $cookies;
	}

	/**
	 * @param string $date
	 * @return bool
	 */
	private function checkExpiresCookie($date){
		return (time() > strtotime($date));
	}
} 