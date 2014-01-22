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
	 * @param string $dir
	 */
	public function setDir($dir) {
		$this->_dir = $dir;
	}

	/**
	 * @return string
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

	private static $_regExCookieDelimiterPhantomJS = '(?:(?:\\\\n)?\\\\0\\\\0\\\\0(?:\\\\0)?((\\\\{2})|((\\\\x\w{2}){1,2})|(\w)|(\_)|(\\\\x\w)|(\\\\\w)|(\W)|(\\\\\W)){1})';
	private static $_phantomCookieCountSymbol = array(0 => '\0',  1 => '\x1', 2 => '\x2', 3 => '\x3', 4 => '\x4', 5 => '\x5', 6 => '\x6', 7 => '\a', 8 => '\b', 9 => '\t', 10 => '\n', 11 => '\v', 12 => '\f', 13 => '\r', 14 => '\xe', 15 => '\xf', 16 => '\x10', 17 => '\x11', 18 => '\x12', 19 => '\x13', 20 => '\x14', 21 => '\x15', 22 => '\x16', 23 => '\x17', 24 => '\x18', 25 => '\x19', 26 => '\x1a', 27 => '\x1b', 28 => '\x1c', 29 => '\x1d', 30 => '\x1e', 31 => '\x1f', 32 => ' ', 33 => '!', 34 => '\"', 35 => '#', 36 => '$', 37 => '%', 38 => '&', 39 => '\'', 40 => '(', 41 => ')', 42 => '*', 43 => '+', 44 => ',', 45 => '-', 46 => '.', 47 => '/', 48 => '\x30', 49 => '\x31', 50 => '\x32',);

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
			if(is_array($fields) && isset($fields[5])){
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
		$lines = explode("\n", $text);
		$regexCookieDelimiter = self::$_regExCookieDelimiterPhantomJS;
		$regexCookieLine = "%^cookies=\"\@Variant\($regexCookieDelimiter{2}QList\\<QNetworkCookie\\>$regexCookieDelimiter{3}(?<cookie_str>.+)\)\"\s*$%ims";
		if(!preg_match($regexCookieLine, $lines[1], $match)){
			return array();
		}
		$cookieDelimiter = 'REPLACE_COOKIE_DELIMITER';
		var_dump($match['cookie_str']);
		$text = preg_replace("%$regexCookieDelimiter%ims", $cookieDelimiter, $match['cookie_str']);
		var_dump($text);
		$cookiesLines = explode($cookieDelimiter, $text);
		$parametres = array('expires', 'domain', 'path');
		$cookies = array();
		foreach ($cookiesLines as $cookieLine) {
			$cookie = array();
			if(preg_match('%(?<name>\w+)\s*=\s*(?<value>[^;]+)%ims', $cookieLine, $match)){
				$cookie['name'] = trim($match['name']);
				$cookie['value'] = trim($match['value']);
			} else {
				continue;
			}
			foreach($parametres as $param){
				if(preg_match('%' . $param . '\s*=\s*(?<val>[^;]+)%i', $cookieLine, $match)){
					$cookie[$param] = trim($match['val']);
				}
			}
			$cookie['secure'] = (bool)preg_match('%;\s*secure\s*(;|$)%i', $cookieLine);
			$cookie['httponly'] = (bool)preg_match('%;\s*httponly\s*(;|$)%i', $cookieLine);
			$cookies[$cookie['name']] = $cookie;
		}
		return $cookies;
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
			              $cookie['secure']);
		}
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
	 * @param array $cookie
	 * @return string
	 */
	public function toPhantomJS($cookie){
		return ($cookie['name'] . '=' . $cookie['value'] . ';' .
		                ' expires=' . $cookie['expires'] . ';' .
		                ($cookie['secure'] ?' secure;' : '') .
		                ($cookie['httponly'] ?' HttpOnly;' : '') .
		                ' domain=' . $cookie['domain'] . ';' .
		                ' path=' . $cookie['path']
		);
	}

	public function toFilePhantomJS($cookies){
		$keyCountCookie = is_array($cookies) ? count($cookies) : 0;
		$start = "[General]
cookies=\"@Variant(\\0\\0\\0\\x7f\\0\\0\\0\\x16QList<QNetworkCookie>\\0\\0\\0\\0\\x1\\0\\0\\0" . chr($keyCountCookie);
		$end = ")\"
";
		$str = '';
		foreach($cookies as $cookie){
			$cookieStr = $this->toPhantomJS($cookie);
			$str .= "\\0\\0\\0" . chr(mb_strlen($cookieStr));
		}
		$str = $start . $str . $end;
		return file_put_contents($this->getFilePhantomJSName(), $str);
	}

	private function getPhantomJsNumber($count){

	}

	/**
	 * @param string $url
	 * @return array|null
	 */
	public function getCookies($url){
		$this->update();
		return $this->_list->getLevel(cStringWork::getDomainName($url));
	}

	public function getAllCookies(){
		$allCookies = array();
		$cookies = $this->_list->getLevel($this->_list->getMainLevelName());
		if(is_array($cookies)){
			foreach ($cookies as $cookie) {
				if(is_array($cookie)){
					$allCookies = array_merge($allCookies, $cookie);
				}
			}
		}
		return $allCookies;
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

	/**
	 * Генерирует массив с ключами которым обозначает phantomJS количество cookie или длинну строки cookie
	 * @param bool   $symbolCount Генерация количества cookies[true] или длинны сроки[false].
	 * @param string $urlCheck    проверочный url для генерации
	 */
	public function genPhantomJSCountCookieNumber($symbolCount = true, $urlCheck = 'http://test1.ru/get_content-php-curl-proxy/_parser_lib/cPhantomJS/generateSymbolCookieNumber.php'){

		$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
		$cookieName = 'zzz_generatorCountCookieNumber';
		$phantomJS->setCookieFile($cookieName);
		$this->setName($cookieName);
		$regEx = self::$_regExCookieDelimiterPhantomJS;
		$regexCookieCountSymbol = '(?:(?:\\\\n)?\\\\0\\\\0\\\\0(?:\\\\0)?(?<symbol>(\\\\{2})|((\\\\x\w{2}){1,2})|(\w)|(\_)|(\\\\x\w)|(\\\\\w)|(\W)|(\\\\\W)){1})';
		$regexCookieLine = "%^cookies=\"?\@Variant\($regEx{2}QList\\<QNetworkCookie\\>{$regEx}{$regexCookieCountSymbol}{$regEx}(?<cookie_str>.*)\)\"?\s*$%ims";
		echo "array(";
		if($symbolCount){
			$countCookie = 50;
			for($i=0;$i<=$countCookie;$i++){
				$phantomJS->renderText($urlCheck . '?countCookie='.$i);
				$text = file_get_contents($this->getFilePhantomJSName());
				if(preg_match($regexCookieLine, $text, $match)){
					echo " " . $i . " => '" . $match['symbol'] . "',";
				} else {
					echo "preg_match false </br>\n";
					echo $text . "</br>\n";
				}
			}
		} else {
			$lengthCookie = 1;
			for($i=0;$i<=$lengthCookie;$i++){
				$phantomJS->renderText($urlCheck . '?lengthCookie='.$i);
				$text = file_get_contents($this->getFilePhantomJSName());
				if(preg_match($regexCookieLine, $text, $match)){
					echo " " . $i . " => '" . $match['symbol'] . "',";
				} else {
					echo "preg_match false </br>\n";
					echo $text . "</br>\n";
				}
			}
		}
		echo ")";
		//file_put_contents($this->getFilePhantomJSName(),'');
	}
} 