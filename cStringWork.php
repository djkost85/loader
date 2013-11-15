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
	/**
	 * Текст который обрабатывается в классе
	 * @var string
	 */
	private $_text; //Текст который обрабатывается в классе

	function __construct($text = "") {
		$this->_text = $text;
	}

	/**
	 * функция для проверки доступа к необходимым ресурсам системы
	 */
	public function functionCheck() {
		echo "cStringWork->functionCheck {</br>\n";
		$mess = '';
		if (!is_dir(GC_ROOT_DIR . "/strin_work_files")) {
			$mess .= "Warning: folder for class files does not exist</br>\n";
		} else {
			if (!is_readable(GC_ROOT_DIR . "/strin_work_files")) {
				$mess .= "Warning: folder for the cookie does not have the necessary rights to use</br>\n";
			}
		}
		if ($mess) echo $mess . " To work correctly, correct the above class cStringWork requirements</br>\n ";
		else echo "cStringWork ready</br>\n";
		echo "cStringWork->functionCheck }</br>\n";
	}

	/**
	 * Разбивает на массив текст заданной величина скрипт вырезает с сохранением предложений
	 * @param string $text      разбиваемый текст
	 * @param int    $partSize размер части
	 * @param int    $offset    максимальное количество частей 0=бесконечно
	 * @return array
	 */
	public static function dividedText($text = "", $partSize = 4900, $offset = 0) {
		$dividedTextArray = array();
		if (strlen($text) > $partSize) {
			for ($i = 0; ($i < $offset || $offset == 0) && $text; $i++) {
				$partText = substr($text, 0, $partSize);
				preg_match("/^(.*\.)[^\.]*$/i", $partText, $match);
				if (strlen($match[1]) == 0) break;
				$dividedTextArray[] = $match[1];
				$text = trim(str_replace($match[1], "", $text));
			}
		} else {
			$dividedTextArray[] = $text;
		}
		return $dividedTextArray;
	}

	/**
	 * Стирание спец. символов, двойных и более пробелов, табуляций и переводов строки
	 * @param string $text
	 * @param array  $repTextArray массив регулярных выражений для выполнения
	 * @return string
	 */
	public static function clearNote($text = "", $repTextArray = array("/\s+/")) {
		if (is_string($repTextArray)) $text = preg_replace($repTextArray, " ", $text);
		elseif (is_array($repTextArray)) foreach ($repTextArray as $value) $text = preg_replace($value, " ", $text);
		return $text;
	}

	/**
	 * Заменяет HTML код  на хеши, чтоб при пропуске через спец программы не потерять теги(синонимайзей, переводчик)
	 * @param string $text шифруемый текст
	 * @param string $reg  регулярное выражение для поиска шифруемых данных
	 * @return string
	 */
	public function encryptTag($text = "", $reg = "%(<[^<>]*>)%iUsm") {
		if (!$text) $text = $this->_text;
		$count = preg_match_all($reg, $text, $matches);
		for ($i = 0; $i < $count; $i++) {
			$str = $matches[0][$i];
			$this->_cryptTagArray['hash'][$i] = " " . microtime(1) . mt_rand() . " ";
			$this->_cryptTagArray['tag'][$i] = $str;
			$text = preg_replace("#" . preg_quote($this->_cryptTagArray['tag'][$i], '#') . "#ms", $this->_cryptTagArray['hash'][$i], $text);
		}
		return $this->_text = $text;
	}

	/**
	 * Заменяет хеш на HTML код после обработки через функцию encryptTag
	 * @param string $text текст с хешами
	 * @return string
	 */
	public function decryptTag($text = "") {
		if (!$text) $text = $this->_text;
		foreach ($this->_cryptTagArray['hash'] as $key => $value) {
			$text = preg_replace("#" . preg_quote($this->_cryptTagArray['hash'][$key], '#') . "#ms", $this->_cryptTagArray['tag'][$key], $text);
		}
		return $this->_text = $text;
	}

	/**
	 * Вытаскивает доменное имя из url
	 * @param $url исходный адрес
	 * @return bool|string
	 */
	public static function getDomainName($url) {
		$url_data = cStringWork::parseUrl($url);
		return $url_data['host'];
	}

	public function getCryptTagArray() {
		return $this->_cryptTagArray;
	}

	public function getText() {
		return $this->_text;
	}

	public function setText($text) {
		$this->_text = $text;
	}

	/**
	 * Парсит html страницу и вытаскивает содержимое тега
	 * @param string $text        текст в котором ищет
	 * @param string $startTag   открывающий тег
	 * @param bool   $withoutTag возвращать с тегом или без
	 * @return string
	 */
	public static function betweenTag($text = "", $startTag = '<div class="xxx">', $withoutTag = true) {
		if (!preg_match('#<(?<tag>\w+)[^>]*>#im', $startTag, $tag)) return false;
		if (preg_match('#<(?<tag>\w+)\s*[\w-]+=[\"\']+[^\'\"]+[\"\']+[^>]*>#im', $startTag)) {
			preg_match_all("#(?<parametr>[\w-]+=([\"\']?[^\'\"\s]+[\"\']?|[\"\'][^\'\"]+[\"\']))#im", $startTag, $matches);
			$reg = "#<" . preg_quote($tag["tag"]) . "\s*";
			foreach ($matches['parametr'] as $value) $reg .= "[^>]*" . preg_quote($value) . "[^>]*";
			$reg .= ">#im";
			if (!preg_match($reg, $text, $match)) return false;
			$startTag = $match[0];
		} else {
			preg_match('/<(?<tag>[^\s]+)[^>]*>/i', $startTag, $tag);
			preg_match('/<(?<tag>' . preg_quote($tag[1]) . ')[^>]*>/i', $text, $tag);
		}
		unset($match);
		unset($matches);
		$tagName = $tag["tag"];
		unset($tag);
		$openTag = "<" . $tagName;
		$closeTag = "</" . $tagName;
		$text = substr($text, strpos($text, $startTag));
		$countOpenTag = 0;
		$posEnd = 0;
		$countTag = 2 * preg_match_all('#' . preg_quote($openTag, '#') . '#ims', $text, $matches);
		for ($i = 0; $i < $countTag; $i++) {
			$posOpenTag = strpos($text, $openTag, $posEnd);
			$posCloseTag = strpos($text, $closeTag, $posEnd);
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
		if ($withoutTag) $returnText = substr($text, strlen($startTag), $posEnd - strlen($startTag) - 1);
		else $returnText = substr($text, 0, $posEnd + strlen($tagName) + 2);

		return $returnText;
	}

	/**
	 * Аналог встроеной функции parse_url но с дополнительным разбитием на масив параметры query и fragment
	 * @param $url
	 * @return array
	 * scheme Протокол
	 * host имя хоста
	 * port порт
	 * user имя пользователя
	 * pass пароль пользователя
	 * path полный адрес с именем файла
	 * query массив GET запроса [Имя переменной]=Значение
	 * fragment массив ссылок на HTML якоря [Имя якоря]=Значение
	 */
	public static function parseUrl($url) {
		$arrayUrl = parse_url($url);
		if (isset($arrayUrl['query'])) {
			$arrayQuery = explode("&", $arrayUrl['query']);
			unset($arrayUrl['query']);
			foreach ($arrayQuery as $value) {
				$partQuery = explode("=", $value);
				$arrayUrl['query'][$partQuery[0]] = $partQuery[1];
			}
		}
		if (isset($arrayUrl['fragment'])) {
			$arrayFragment = explode("&", $arrayUrl['fragment']);
			unset($arrayUrl['fragment']);
			foreach ($arrayFragment as $value) {
				$partFragment = explode("=", $value);
				$arrayUrl['fragment'][$partFragment[0]] = (isset($partFragment[1]) ? $partFragment[1] : '');
			}
		}
		return $arrayUrl;
	}

	/**
	 * Проверяет строку на соответствие шаблону ip адреса с портом
	 * @param $str
	 * @return bool
	 */
	public static function isIp($str) {
		if (preg_match("#^\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(:{1}\d{1,10})?)\s*$#i", $str)) return true;
		else return false;
	}

	/**
	 * Функция для определения однобайтовой кодировки русского текста
	 * @param string $str строка для определения кодировки
	 * @return string имя кодировки
	 * @author m00t
	 * @url    https://github.com/m00t/detect_encoding
	 */
	public static function getEncodingName($str) {
		if (mb_detect_encoding($str, array('UTF-8'), true) == 'UTF-8') return 'UTF-8';
		$weights = array();
		$specters = array();
		$possibleEncodings = array('windows-1251', 'koi8-r', 'iso8859-5');
		foreach ($possibleEncodings as $encoding) {
			$weights[$encoding] = 0;
			$specters[$encoding] = require GC_ROOT_DIR . '/string_work_files/specters/' . $encoding . '.php';
		}
		foreach (str_split($str, 2) as $key) {
			foreach ($possibleEncodings as $encoding) {
				if (isset($specters[$encoding][$key])) {
					$weights[$encoding] += $specters[$encoding][$key];
				}
			}
		}
		unset($key);
		$sumWeight = array_sum($weights);
		foreach ($weights as $encoding => $weight) {
			if (!$sumWeight) $weights[$encoding] = 0;
			$weights[$encoding] = $sumWeight ? $weight / $sumWeight : 0;
		}
		arsort($weights, SORT_NUMERIC);
		return key($weights);
	}

}