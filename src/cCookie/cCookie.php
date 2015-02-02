<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 07.12.13
 * Time: 19:27
 * Project: get_content
 * @author: Evgeny Pynykh <bpteam22@gmail.com>
 */

namespace GetContent;


/**
 * Class cCookie
 * Работа с cookies и конвертирование в разные форматы (phantomJS cURL)
 * @package GetContent
 */
class cCookie {

	private $_dir;
	/**
	 * @var cList
	 */
	private $_list;
	private $_name = 'cookies';
	private static $_regExCookieDelimiterPhantomJS = '(?:(?:\\\\n)?((\\\\0|\\\\{2}|((\\\\x[0-9a-f]{2}){2}|\\\\x[0-9a-f]{2})|\\\\x[0-9a-f]|\\\\_|\\\\[abtnvfr]|[g-zG-Z]|\W|\\\\\W)))';
	private static $_phantomCookieCountSymbols = array(
	0   => '\0',   1   => '\x1',  2   => '\x2',  3   => '\x3',  4   => '\x4',  5   => '\x5',  6   => '\x6',  7   => '\a',   8   => '\b',   9   => '\t',
	10  => '\n',   11  => '\v',   12  => '\f',   13  => '\r',   14  => '\xe',  15  => '\xf',  16  => '\x10', 17  => '\x11', 18  => '\x12', 19  => '\x13',
	20  => '\x14', 21  => '\x15', 22  => '\x16', 23  => '\x17', 24  => '\x18', 25  => '\x19', 26  => '\x1a', 27  => '\x1b', 28  => '\x1c', 29  => '\x1d',
	30  => '\x1e', 31  => '\x1f', 32  => ' ',    33  => '!',    34  => '\\',   35  => '#',    36  => '$',    37  => '%',    38  => '&',    39  => '\'',
	40  => '(',    41  => ')',    42  => '*',    43  => '+',    44  => ',',    45  => '-',    46  => '.',    47  => '/',    48  => '\x30', 49  => '\x31',
	50  => '\x32', 51  => '\x33', 52  => '\x34', 53  => '\x35', 54  => '\x36', 55  => '\x37', 56  => '\x38', 57  => '\x39', 58  => ':',    59  => ';',
	60  => '<',    61  => '=',    62  => '>',    63  => '?',    64  => '@',    65  => '\x41', 66  => '\x42', 67  => '\x43', 68  => '\x44', 69  => '\x45',
	70  => '\x46', 71  => 'G',    72  => 'H',    73  => 'I',    74  => 'J',    75  => 'K',    76  => 'L',    77  => 'M',    78  => 'N',    79  => 'O',
	80  => 'P',    81  => 'Q',    82  => 'R',    83  => 'S',    84  => 'T',    85  => 'U',    86  => 'V',    87  => 'W',    88  => 'X',    89  => 'Y',
	90  => 'Z',    91  => '[',    92  => '\\',   93  => ']',    94  => '^',    95  => '_',    96  => '`',    97  => '\x61', 98  => '\x62', 99  => '\x63',
	100 => '\x64', 101 => '\x65', 102 => '\x66', 103 => 'g',    104 => 'h',    105 => 'i',    106 => 'j',    107 => 'k',    108 => 'l',    109 => 'm',
	110 => 'n',    111 => 'o',    112 => 'p',    113 => 'q',    114 => 'r',    115 => 's',    116 => 't',    117 => 'u',    118 => 'v',    119 => 'w',
	120 => 'x',    121 => 'y',    122 => 'z',    123 => '{',    124 => '|',    125 => '}',    126 => '~',    127 => '\x7f', 128 => '\x80', 129 => '\x81',
	130 => '\x82', 131 => '\x83', 132 => '\x84', 133 => '\x85', 134 => '\x86', 135 => '\x87', 136 => '\x88', 137 => '\x89', 138 => '\x8a', 139 => '\x8b',
	140 => '\x8c', 141 => '\x8d', 142 => '\x8e', 143 => '\x8f', 144 => '\x90', 145 => '\x91', 146 => '\x92', 147 => '\x93', 148 => '\x94', 149 => '\x95',
	150 => '\x96', 151 => '\x97', 152 => '\x98', 153 => '\x99', 154 => '\x9a', 155 => '\x9b', 156 => '\x9c', 157 => '\x9d', 158 => '\x9e', 159 => '\x9f',
	160 => '\xa0', 161 => '\xa1', 162 => '\xa2', 163 => '\xa3', 164 => '\xa4', 165 => '\xa5', 166 => '\xa6', 167 => '\xa7', 168 => '\xa8', 169 => '\xa9',
	170 => '\xaa', 171 => '\xab', 172 => '\xac', 173 => '\xad', 174 => '\xae', 175 => '\xaf', 176 => '\xb0', 177 => '\xb1', 178 => '\xb2', 179 => '\xb3',
	180 => '\xb4', 181 => '\xb5', 182 => '\xb6', 183 => '\xb7', 184 => '\xb8', 185 => '\xb9', 186 => '\xba', 187 => '\xbb', 188 => '\xbc', 189 => '\xbd',
	190 => '\xbe', 191 => '\xbf', 192 => '\xc0', 193 => '\xc1', 194 => '\xc2', 195 => '\xc3', 196 => '\xc4', 197 => '\xc5', 198 => '\xc6', 199 => '\xc7',
	200 => '\xc8', 201 => '\xc9', 202 => '\xca', 203 => '\xcb', 204 => '\xcc', 205 => '\xcd', 206 => '\xce', 207 => '\xcf', 208 => '\xd0', 209 => '\xd1',
	210 => '\xd2', 211 => '\xd3', 212 => '\xd4', 213 => '\xd5', 214 => '\xd6', 215 => '\xd7', 216 => '\xd8', 217 => '\xd9', 218 => '\xda', 219 => '\xdb',
	220 => '\xdc', 221 => '\xdd', 222 => '\xde', 223 => '\xdf', 224 => '\xe0', 225 => '\xe1', 226 => '\xe2', 227 => '\xe3', 228 => '\xe4', 229 => '\xe5',
	230 => '\xe6', 231 => '\xe7', 232 => '\xe8', 233 => '\xe9', 234 => '\xea', 235 => '\xeb', 236 => '\xec', 237 => '\xed', 238 => '\xee', 239 => '\xef',
	240 => '\xf0', 241 => '\xf1', 242 => '\xf2', 243 => '\xf3', 244 => '\xf4', 245 => '\xf5', 246 => '\xf6', 247 => '\xf7', 248 => '\xf8', 249 => '\xf9',
	250 => '\xfa', 251 => '\xfb', 252 => '\xfc', 253 => '\xfd', 254 => '\xfe', 255 => '\xff',);


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
		return $this->getDir() . '/' . $this->getName() . $prefix . '.cookie';
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
	 * @param array $phantomCookieCountSymbols
	 */
	public static function setPhantomCookieCountSymbols($phantomCookieCountSymbols) {
		self::$_phantomCookieCountSymbols = $phantomCookieCountSymbols;
	}

	/**
	* @return array
	*/
	public static function getPhantomCookieCountSymbols() {
		return self::$_phantomCookieCountSymbols;
	}

	/**
	 * @param int|string $count
	 * @return string
	 */
	public static function getPhantomCookieCountSymbol($count) {
		$symbol = '\\0\\0\\0';
		if(($count/255)>1){
			$symbol .= self::$_phantomCookieCountSymbols[(int)($count/255)] . self::$_phantomCookieCountSymbols[$count%255];
		} else {
			$symbol .= self::$_phantomCookieCountSymbols[$count];
		}
		return $symbol;
	}

	public static function getCookieSymbol($text){
		$regexCookieCountSymbol = '(?:(?:\\\\n)?(?<symbol1>(\\\\0|\\\\{2}|\\\\x[0-9a-f]{2}|\\\\x[0-9a-f]|\\\\_|\\\\\w|\w|\W|\\\\\W))(?<symbol2>(\\\\0|\\\\{2}|\\\\x[0-9a-f]{2}|\\\\x[0-9a-f]|\\\\_|\\\\\w|\w|\W|\\\\\W))(?<symbol3>(\\\\0|\\\\{2}|\\\\x[0-9a-f]{2}|\\\\x[0-9a-f]|\\\\_|\\\\\w|\w|\W|\\\\\W))(?<symbol4>(\\\\0|\\\\{2}|\\\\x[0-9a-f]{2}|\\\\x[0-9a-f]|\\\\_|\\\\\w|\w|\W|\\\\\W)))';
		if(preg_match('%' . $regexCookieCountSymbol . '%ms', $text, $match)){
			return array(1 => $match['symbol1'], 2 => $match['symbol2'], 3 => $match['symbol3'], 4 => $match['symbol4']);
		} else {
			return false;
		}
	}

	/**
	 * @param bool|string $name
	 */
	function __construct($name = false){
		$this->_list = new cList();
		$this->setDir(__DIR__ . '/' . 'cookies');
		if($name){
			$this->open($name);
		}
	}

	function __destruct(){
		$this->deleteCookieFiles();
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
			if(file_exists($value)){
				$fileInfo = stat($value);
				if ($fileInfo['ctime'] < time() - $storageTime){
					unlink($value);
				}
			}
		}
	}

	public function deleteCookieFiles(){
		if(file_exists($this->getFileName())){
			unlink($this->getFileName());
		}
		if(file_exists($this->getFileCurlName())){
			unlink($this->getFileCurlName());
		}
		if(file_exists($this->getFilePhantomJSName())){
			unlink($this->getFilePhantomJSName());
		}
	}

	public static function parsCookieString($text){
		$parametres = array('expires', 'domain', 'path');
		if(preg_match('%(?<name>\w+)\s*=\s*(?<value>[^;]+)%ims', $text, $match)){
			$cookie['name'] = trim($match['name']);
			$cookie['value'] = trim($match['value']);
		} else {
			return false;
		}
		foreach($parametres as $param){
			if(preg_match('%' . $param . '\s*=\s*(?<val>[^;]+)%i', $text, $match)){
				$cookie[$param] = trim($match['val']);
			}
		}
		$cookie['secure'] = (bool)preg_match('%;\s*secure\s*(;|$)%i', $text);
		$cookie['httponly'] = (bool)preg_match('%;\s*httponly\s*(;|$)%i', $text);
		return $cookie;
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

	public function fromFileCurl($fileName = false){
		$text = file_get_contents($fileName ? $fileName : $this->getFileCurlName());
		return self::fromCurl($text);
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public static function fromPhantomJS($text){
		$lines = explode("\n", $text);
		$regexCookieDelimiter = self::$_regExCookieDelimiterPhantomJS;
		$regexCookieLine = "%^cookies=\"?\@Variant\(($regexCookieDelimiter{4}){2}QList\\<QNetworkCookie\\>\\\\0({$regexCookieDelimiter}{4}){2}(?<cookie_str>.*)\)\"?\s*$%ms";
		if(!isset($lines[1]) || !preg_match($regexCookieLine, $lines[1], $match)){
			return array();
		}
		$cookieDelimiter = 'REPLACE_COOKIE_DELIMITER';
		$regEx = "\\\\0\\\\0\\\\0".$regexCookieDelimiter;
		$text = preg_replace("%$regEx%ms", $cookieDelimiter, $match['cookie_str']);
		$cookiesLines = explode($cookieDelimiter, $text);
		$cookies = array();
		foreach ($cookiesLines as $cookieLine) {
			$cookie = self::parsCookieString($cookieLine);
			if($cookie){
				$cookies[$cookie['name']] = $cookie;
			}
		}
		return $cookies;
	}

	public function fromFilePhantomJS($fileName = false){
		$text = file_get_contents($fileName ? $fileName : $this->getFilePhantomJSName());
		return self::fromPhantomJS($text);
	}
	/**
	 * @param string $text
	 * @return array
	 */
	public static function fromHttp($text){
		$cookies = array();
		if(preg_match_all('%Set-Cookie:\s*(?<name>\w+)\s*=\s*(?<value>[^;]+)(?<cookie>.*)%i', $text, $matches)){
			foreach($matches['cookie'] as $cookieLine){
				$cookie = self::parsCookieString($cookieLine);
				if($cookie){
					$cookies[$cookie['name']] = $cookie;
				}
			}
		}
		return $cookies;
	}

	/**
	 * @param string $text
	 * @return array
	 */
	public static function fromMetaTeg($text){
		$cookies = array();
		if(preg_match_all('%(?<cookie><meta[^>]*Set-Cookie[^>]*>)%i', $text, $matches)){
			foreach($matches['cookie'] as $cookieLine){
				if(!preg_match('%content\s*=\s*(\'|")(?<cookieLine>.*))%i', $cookieLine, $match)){
					continue;
				}
				$cookie = self::parsCookieString($match['cookieLine']);
				if($cookie){
					$cookies[$cookie['name']] = $cookie;
				}
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
		       (@$cookie['tailmatch'] ? 'TRUE' : 'FALSE') . "\t" .
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
		return trim($cookie['name'] . '=' . $cookie['value'] . ';' .
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
cookies=\"@Variant(\\0\\0\\0\\x7f\\0\\0\\0\\x16QList<QNetworkCookie>\\0\\0\\0\\0\\x1" . $this->getPhantomCookieCountSymbol($keyCountCookie);
		$end = ")\"
";
		$str = '';
		foreach($cookies as $cookie){
			$cookieStr = $this->toPhantomJS($cookie);
			$str .= $this->getPhantomCookieCountSymbol(mb_strlen($cookieStr)) . $cookieStr;
		}
		$str = $start . $str . $end;
		return file_put_contents($this->getFilePhantomJSName(), $str);
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
	 * ДОЛГО ВЫПОЛНЯЕТСЯ ЕСЛИ МНОГО ИТЕРАЦИЙ
	 * @param string $urlCheck проверочный url для генерации
	 * @param int    $countRepeat
	 */
	public function genPhantomJSCountCookieNumber($urlCheck = 'http://e.com/cPhantomJS/generateSymbolCookieNumber.php', $countRepeat = 255){
		set_time_limit(0);
		$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
		$cookieName = 'zzz_generatorCountCookieNumber';
		$phantomJS->setKeyStream($cookieName);
		$this->setName($cookieName);
		$regEx = self::$_regExCookieDelimiterPhantomJS;
		$regexCookieLine = "%^cookies=\"?\@Variant\(({$regEx}{4}){2}QList\\<QNetworkCookie\\>\\\\0({$regEx}{4}){2}(?<cookie_str>.*)\)\"?\s*$%ms";
		$minSizeLenCookie = 64;
		$startSizeStringCookie = 256;
		$startNumber = $startSizeStringCookie - $minSizeLenCookie + 1;
		$countRepeat = $countRepeat + $startNumber;
		echo "array(";
		for($i = $startNumber ; $i <= $countRepeat ; $i++){
			$phantomJS->renderText($urlCheck . '?lengthCookie='.$i);
			$text = file_get_contents($this->getFilePhantomJSName());
			preg_match($regexCookieLine, $text, $match);
			$symbol = $this->getCookieSymbol($match['cookie_str']);
			if($symbol){
				echo " " . ($i - $startNumber) . " => '" . $symbol['symbol4'] . "',";
			} else {
				echo "no find </br>\n";
				echo $text . "</br>\n";
				break;
			}
		}
		echo ")";
	}
} 