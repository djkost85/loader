<?php
namespace GetContent;
/**
 * Class cStringWork
 * Класс для обработки строки и извлечения необходимой информации используя набор фильтров
 * @author  Evgeny Pynykh <bpteam22@gmail.com>
 * @package GetContent
 * @version 2.0
 */
class cStringWork
{
	/**
	 * Массив с тегами и хеш кодами для обработки через синонимайзер или переводчик, чтоб не потерять HTML теги
	 * $_cryptTagArray['tag'] набор тегов
	 * $_cryptTagArray['hash'] набор хешей
	 * Порядок тегов и хешей соответстует их положению в строке.
	 * @var array
	 */
	private $_cryptTagArray;
	private static $_ipRegEx = '(?<address>(?<ips>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\:?(?<port>\d{1,10})?)';

	/**
	 * Разбивает на массив текст заданной величина скрипт вырезает с сохранением предложений
	 * @param string $text      разбиваемый текст
	 * @param int    $partSize  Максимальное количество символов в одной части
	 * @param int    $offset    максимальное количество частей 0 = бесконечно
	 * @return array
	 */
	public static function divideText($text = "", $partSize = 100, $offset = 0) {
		$parts = array();
		if (mb_strlen($text,'utf-8') >= $partSize) {
			for ($i = 0; ($i < $offset || $offset === 0) && $text; $i++) {
				$partText = mb_substr($text, 0, $partSize, 'utf-8');
				preg_match('%^(.+[\.\?\!]|$).*%imsuU', $partText, $match);
				if (mb_strlen($match[1],'utf-8') == 0) break;
				$parts[] = $match[1];
				$text = trim(preg_replace('%' . preg_quote($match[1], '%') . '%ms', '', $text, 1));
			}
		} else {
			$parts[] = $text;
		}
		return $parts;
	}

	/**
	 * Стирание спец. символов, двойных и более пробелов, табуляций и переводов строки
	 * @param string $text
	 * @param array|string  $repTextArray массив регулярных выражений для замены на пробел
	 * @return string
	 */
	public static function clearNote($text = "", $repTextArray = array('%\s+%')) {
		if (is_string($repTextArray)) {
			$text = preg_replace($repTextArray, " ", $text);
		}
		elseif (is_array($repTextArray)) {
			foreach ($repTextArray as $value) {
				$text = preg_replace($value, " ", $text);
			}
		}
		return $text;
	}

	/**
	 * Заменяет HTML код  на хеши, чтоб при пропуске через спец программы(синонимайзей, переводчик) не потерять теги
	 * @param string $text шифруемый текст
	 * @param string $reg  регулярное выражение для поиска шифруемых данных
	 * @return string
	 */
	public function encryptTag($text, $reg = "%(<[^<>]*>)%iUsm") {
		$count = preg_match_all($reg, $text, $matches);
		for ($i = 0; $i < $count; $i++) {
			$str = $matches[0][$i];
			$this->_cryptTagArray['hash'][$i] = " " . microtime(1) . mt_rand() . " ";
			$this->_cryptTagArray['tag'][$i] = $str;
			$text = preg_replace("#" . preg_quote($this->_cryptTagArray['tag'][$i], '#') . "#ms", $this->_cryptTagArray['hash'][$i], $text);
		}
		return $text;
	}

	/**
	 * Заменяет хеш на HTML код после обработки через функцию encryptTag
	 * @param string $text текст с хешами
	 * @return string
	 */
	public function decryptTag($text) {
		foreach ($this->_cryptTagArray['hash'] as $key => $value) {
			$text = preg_replace("#" . preg_quote($this->_cryptTagArray['hash'][$key], '#') . "#ms", $this->_cryptTagArray['tag'][$key], $text);
		}
		return $text;
	}

	/**
	 * @return array
	 */
	public function getCryptTagArray() {
		return $this->_cryptTagArray;
	}

	/**
	 * Парсит html страницу и вытаскивает содержимое тега
	 * @param string $text       текст в котором ищет
	 * @param string $startTag   открывающий тег
	 * @param bool   $withoutTag возвращать с тегом или без
	 * @param string $encoding
	 * @return string
	 */
	public static function betweenTag($text, $startTag = '<div class="xxx">', $withoutTag = true, $encoding="UTF-8") {
		if (!preg_match('%<(?<tag>\w+)[^>]*>%im', $startTag, $tag)){
			return false;
		}
		if (preg_match('%<(?<tag>\w+)\s*[\w-]+=\s*[\"\']?[^\'\"]+[\"\']?[^>]*>%im', $startTag)) {
			preg_match_all('%(?<parametr>[\w-]+=([\"\'][^\'\">]*[\"\']|[\"\']?[^\'\">]*[\"\']?))%im', $startTag, $matches);
			$reg = '%<' . preg_quote($tag["tag"], '%');
			foreach ($matches['parametr'] as $value) {
				$reg .= '[^>]*' . preg_quote($value, '%') . '[^>]*';
			}
			$reg .= '>%im';
			if (!preg_match($reg, $text, $match)) {
				return false;
			}
			$startTag = $match[0];
		} else {
			preg_match('%<(?<tag>\w+)[^>]*>%i', $startTag, $tag);
			preg_match('%<(?<tag>' . preg_quote($tag['tag'], '%') . ')[^>]*>%i', $text, $tag);
		}
		$tagName = $tag['tag'];
		$openTag = "<" . $tagName;
		$closeTag = "</" . $tagName;
		$startPos = mb_strpos($text, $startTag, 0,$encoding);
		$text = mb_substr($text, $startPos, -1,$encoding);
		$countOpenTag = 0;
		$posEnd = 0;
		$countTag = 2 * preg_match_all('%' . preg_quote($openTag, '%') . '%ims', $text, $matches);
		for ($i = 0; $i < $countTag; $i++) {
			$posOpenTag = mb_strpos($text, $openTag, $posEnd,$encoding);
			$posCloseTag = mb_strpos($text, $closeTag, $posEnd,$encoding);
			if ($posOpenTag === false) {
				$posOpenTag = $posCloseTag + 1;
			}
			if ($posOpenTag < $posCloseTag) {
				$countOpenTag++;
				$posEnd += $posOpenTag + 1 - $posEnd;
			} else {
				$countOpenTag--;
				$posEnd += $posCloseTag + 1 - $posEnd;
			}
			if (!$countOpenTag) {
				break;
			}
		}
		if($withoutTag){
		$start = mb_strlen($startTag,$encoding);
		$length = $posEnd - mb_strlen($startTag,$encoding) - 1;
		} else {
			$start = 0;
			$length = $posEnd + mb_strlen($tagName,$encoding) + 2;
		}
		return mb_substr($text, $start, $length, $encoding);
	}

	/**
	 * Аналог встроеной функции parse_url + pathinfo но с дополнительным разбитием на масив параметры query и fragment и path
	 * @param $url
	 * @return array
	 * scheme Протокол
	 * host имя хоста
	 * domain домен второго уровня
	 * port порт
	 * user имя пользователя
	 * pass пароль пользователя
	 * path полный адрес с именем файла
	 * query массив GET запроса [Имя переменной]=Значение
	 * fragment массив ссылок на HTML якоря [Имя якоря]=Значение
	 */
	public static function parsePath($url) {
		$partUrl = parse_url($url);
		if (isset($partUrl['query'])) {
			$arrayQuery = explode("&", $partUrl['query']);
			unset($partUrl['query']);
			foreach ($arrayQuery as $value) {
				$partQuery = explode("=", $value);
				$partUrl['query'][$partQuery[0]] = $partQuery[1];
			}
		}
		if (isset($partUrl['fragment'])) {
			$arrayFragment = explode("&", $partUrl['fragment']);
			unset($partUrl['fragment']);
			foreach ($arrayFragment as $value) {
				$partFragment = explode("=", $value);
				$partUrl['fragment'][$partFragment[0]] = (isset($partFragment[1]) ? $partFragment[1] : '');
			}
		}
		if(isset($partUrl['path'])){
			$partPath = pathinfo($partUrl['path']);
			$partUrl['dirname'] = $partPath['dirname'];
			$partUrl['basename'] = $partPath['basename'];
			$partUrl['extension'] = $partPath['extension'];
			$partUrl['filename'] = isset($partPath['filename']) ? $partPath['filename'] : '';
		}
		return $partUrl;
	}

	public static function parseUrl($url){
		return self::parsePath($url);
	}

	/**
	 * @param string $url исходный адрес
	 * @param int    $level
	 * @return bool|string
	 */
	public static function getDomainName($url, $level = 2) {
		$partUrl = self::parsePath($url);
		$levelRegEx = array();
		for($i = 0; $i < $level; $i++) $levelRegEx[] = '[^\.]+';
		$fullDomain = isset($partUrl['host']) ? $partUrl['host'] : $partUrl['path'];
		return preg_match('%(?<domain>' . implode('\.', $levelRegEx) . ')($|/|\s)%ims', $fullDomain, $match) ? $match['domain'] : false;
	}

	/**
	 * Проверяет строку на соответствие шаблону ip адреса с портом
	 * @param $str
	 * @return bool
	 */
	public static function isIp($str) {
		return (bool)preg_match('%^' .self::$_ipRegEx. '$%i', $str);
	}

	public static function getIp($str){
		if(preg_match_all('%' . self::$_ipRegEx . '%ms', $str, $matches)){
			return $matches['address'];
		}
		return array();
	}

	/**
	 * support encoding UTF-8 windows-1251 koi8-r iso8859-5
	 * @param string $text строка для определения кодировки
	 * @return string имя кодировки
	 * @author m00t
	 * @url    https://github.com/m00t/detect_encoding
	 */
	public static function getEncodingName($text) {
		if (mb_detect_encoding($text, array('UTF-8'), true) == 'UTF-8') {
			return 'UTF-8';
		}
		$encodingDetection = array('windows-1251' , 'koi8-r', 'iso8859-5');
		$weights = array();
		$specters = array();
		foreach ($encodingDetection as $encoding) {
			$weights[$encoding] = 0;
			$specters[$encoding] = require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'specters' . DIRECTORY_SEPARATOR . $encoding . '.php';
		}
		foreach (str_split($text, 2) as $key) {
			foreach ($encodingDetection as $encoding) {
				if (isset($specters[$encoding][$key])) {
					$weights[$encoding] += $specters[$encoding][$key];
				}
			}
		}
		$sumWeight = array_sum($weights);
		foreach ($weights as $encoding => $weight) {
			if (!$sumWeight) $weights[$encoding] = 0;
			$weights[$encoding] = $sumWeight ? $weight / $sumWeight : 0;
		}
		arsort($weights, SORT_NUMERIC);
		return key($weights);
	}

	/**
	 * ISO 9:1995
	 * @param $text
	 * @return mixed
	 */
	public static function translitCyrillicToLatin($text){
		$abc = array( 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O','П' => 'P',  'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shh', 'Ъ' => '"', 'Ы' => 'Y', 'Ь' => '\'', 'Э' => 'E\'', 'Ю' => 'Yu', 'Я' => 'Ya',);
		foreach($abc as $rus => $eng){
			$text = preg_replace('%'.preg_quote($rus, '%').'%smu', $eng, $text);
			$text = preg_replace('%'.preg_quote($rus, '%').'%ismu', strtolower($eng), $text);
		}
		return $text;
	}

	/**
	 * @param array $text
	 * @return string
	 */
	public static function getBiggestString($text) {
		$bigA = 0;
		$bigKey = 0;
		foreach ($text as $key => $value) {
			$thisA = mb_strlen($value, 'utf-8');
			if ($thisA > $bigA) {
				$bigA = $thisA;
				$bigKey = $key;
			}
		}
		return $text[$bigKey];
	}
}