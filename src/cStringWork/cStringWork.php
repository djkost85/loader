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
	 * $cryptTagArray['tag'] набор тегов
	 * $cryptTagArray['hash'] набор хешей
	 * Порядок тегов и хешей соответстует их положению в строке.
	 * @var array
	 */
	private $cryptTagArray;
	public static $encodingDetection = array('windows-1251' , 'koi8-r', 'iso8859-5');
	private static $ipRegEx = '(?<address>(?<ips>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\:?(?<port>\d{1,5})?)';
	private static $ABCLatinToCyrillic = array( 'E\'' => 'Э', 'Yu' => 'Ю', 'Ya' => 'Я', 'Ch' => 'Ч', 'Sh' => 'Ш', 'Shh' => 'Щ', 'Zh' => 'Ж', 'Yo' => 'Ё', 'A' => 'А', 'B' => 'Б', 'V' => 'В', 'G' => 'Г', 'D' => 'Д', 'E' => 'Е', 'Z' => 'З', 'I' => 'И', 'J' => 'Й', 'K' => 'К', 'L' => 'Л', 'M' => 'М', 'N' => 'Н', 'O' => 'О', 'P' => 'П', 'R' => 'Р', 'S' => 'С', 'T' => 'Т', 'U' => 'У', 'F' => 'Ф', 'H' => 'Х', 'C' => 'Ц', '"' => 'Ъ', 'Y' => 'Ы', '\'' => 'Ь',);
	private static $ABCCyrillicToLatin = array( 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O','П' => 'P',  'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shh', 'Ъ' => '"', 'Ы' => 'Y', 'Ь' => '\'', 'Э' => 'E\'', 'Ю' => 'Yu', 'Я' => 'Ya',);

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
	 * @param string       $text
	 * @param array|string $repRegExArray массив регулярных выражений для замены на пробел
	 * @param string       $repText
	 * @return string
	 */
	public static function clearNote($text = "", $repRegExArray = array('%\s+%'), $repText = " ") {
		if (is_string($repRegExArray)) {
			$text = preg_replace($repRegExArray, $repText, $text);
		}
		elseif (is_array($repRegExArray)) {
			foreach ($repRegExArray as $value) {
				$text = preg_replace($value, $repText, $text);
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
			$this->cryptTagArray['hash'][$i] = " " . microtime(1) . mt_rand() . " ";
			$this->cryptTagArray['tag'][$i] = $str;
			$text = preg_replace("#" . preg_quote($this->cryptTagArray['tag'][$i], '#') . "#ms", $this->cryptTagArray['hash'][$i], $text);
		}
		return $text;
	}

	/**
	 * Заменяет хеш на HTML код после обработки через функцию encryptTag
	 * @param string $text текст с хешами
	 * @return string
	 */
	public function decryptTag($text) {
		foreach ($this->cryptTagArray['hash'] as $key => $value) {
			$text = preg_replace("#" . preg_quote($this->cryptTagArray['hash'][$key], '#') . "#ms", $this->cryptTagArray['tag'][$key], $text);
		}
		return $text;
	}

	/**
	 * @return array
	 */
	public function getCryptTagArray() {
		return $this->cryptTagArray;
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
		$tagName = self::getTagName($text, $startTag);
		if(!$tagName){
			return false;
		}
		$startPos = mb_strpos($text, $startTag, 0,$encoding);
		$text = mb_substr($text, $startPos, -1,$encoding);
		$posEnd = self::getClosingTagPosition($text, $tagName, $encoding);
		return self::cutTagText($text, $startTag, $tagName, $posEnd, $withoutTag, $encoding);
	}

	protected static function getTagName($text, &$startTag){
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
		return $tag['tag'];
	}

	protected static function getClosingTagPosition($text, $tagName, $encoding){
		$openTag = "<" . $tagName;
		$closeTag = "</" . $tagName;
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
		return $posEnd;
	}

	protected static function cutTagText($text, $startTag, $tagName, $posEnd, $withoutTag, $encoding){
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
			self::explodeQuery($partUrl['query']);
		}
		if (isset($partUrl['fragment'])) {
			self::explodeQuery($partUrl['fragment']);
		}
		if(isset($partUrl['path'])){
			$partPath = pathinfo($partUrl['path']);
			$partUrl['dirname'] = isset($partPath['dirname'])?$partPath['dirname']:'';
			$partUrl['basename'] = isset($partPath['basename'])?$partPath['basename']:'';
			$partUrl['extension'] = isset($partPath['extension'])?$partPath['extension']:'';
			$partUrl['filename'] = isset($partPath['filename']) ? $partPath['filename'] : '';
		}
		return $partUrl;
	}

	protected static function explodeQuery(&$query){
		$arrayFragment = explode("&", $query);
		$query = array();
		foreach ($arrayFragment as $value) {
			$part = explode("=", $value);
			$query[$part[0]] = (isset($part[1]) ? $part[1] : '');
		}
	}

	public static function parseUrl($url){
		return self::parsePath($url);
	}

	public static function checkUrlProtocol($url){
		if (!preg_match("%^(http|https)://%iUm", $url)) $url = "http://" . $url;
		return $url;
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
		return (bool)preg_match('%^' .self::$ipRegEx. '$%i', $str);
	}

	public static function getIp($str){
		if(preg_match_all('%' . self::$ipRegEx . '%ms', $str, $matches)){
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
		$weights = array();
		$specters = array();
		foreach (self::$encodingDetection as $encoding) {
			$weights[$encoding] = 0;
			$specters[$encoding] = require 'phar://'.__DIR__ . '/specters.phar/' . $encoding . '.php';
		}
		foreach (str_split($text, 2) as $key) {
			foreach (self::$encodingDetection as $encoding) {
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
		$encodingName = key($weights);
		unset($weights, $specters, $text);
		return $encodingName;
	}

	/**
	 * ISO 9:1995
	 * @param string $text text need encoding utf-8
	 * @return string
	 */
	public static function translitCyrillicToLatin($text){
		return self::translitText($text, self::$ABCCyrillicToLatin);
	}

	/**
	 * ISO 9:1995
	 * @param string $text text need encoding utf-8
	 * @return string
	 */
	public static function translitLatinToCyrillic($text){
		return self::translitText($text, self::$ABCLatinToCyrillic);
	}

	protected static function translitText($text, $abc){
		foreach($abc as $from => $to){
			$text = preg_replace('%'.preg_quote($from, '%').'%smu', $to, $text);
			$text = preg_replace('%'.preg_quote($from, '%').'%ismu', mb_strtolower($to,'utf-8'), $text);
		}
		return $text;
	}

	/**
	 * @param array $text
	 * @param bool  $bestKey
	 * @return string
	 */
	public static function getBiggestString($text, &$bestKey = false) {
		$bigA = 0;
		$bigKey = 0;
		foreach ($text as $key => $value) {
			$thisA = mb_strlen($value, 'utf-8');
			if ($thisA >= $bigA) {
				$bigA = $thisA;
				$bigKey = $key;
			}
		}
		$bestKey = $bigKey;
		return isset($text[$bigKey]) ? $text[$bigKey] : false;
	}
}