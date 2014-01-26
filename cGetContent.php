<?php
namespace GetContent;
/**
 * Class cGetContent
 * С помощью основных функций библиотеки cURL посылает http запросы для скачивания контента из сети
 * Умеет работать через прокси сервера, в много поточном режиме с верификацией данных.
 * @author  Evgeny Pynykh <bpteam22@gmail.com>
 * @package GetContent
 * @version 2.0
 */
class cGetContent
{
	/**
	 * Набор настроек по умолчанию для cURL
	 * @access private
	 * @var array
	 * Структура:
	 * $_defaultSettings[CURLOPT_HEADER]= bool для включения заголовков в вывод
	 * $_defaultSettings[CURLOPT_URL]= string url источника данных
	 * $_defaultSettings[CURLOPT_TIMEOUT]= int максимальное время ожидания ответа от запроса
	 * $_defaultSettings[CURLOPT_USERAGENT]= string useragent баузера
	 * $_defaultSettings[CURLOPT_PROXY]= string прокси адрес через который будет проходить запрос
	 * $_defaultSettings[CURLOPT_RETURNTRANSFER]= bool флаг для обозначения возвращения результата в переменную
	 * $_defaultSettings[CURLOPT_REFERER]= string адрес страници с которой перешли на текущую
	 * $_defaultSettings[CURLOPT_FOLLOWLOCATION]= bool следовать переадресации сервера или нет
	 * $_defaultSettings[CURLOPT_POST]= bool врключение отправки post запроса на удаленный сервер
	 * $_defaultSettings[CURLOPT_POSTFIELDS]= string|mixed данные post запроса
	 * $_defaultSettings[CURLOPT_FRESH_CONNECT] = bool TRUE для принудительного использования нового соединения вместо закэшированного.
	 * $_defaultSettings[CURLOPT_HTTPHEADER] array Отправка http заголовков
	 * $_defaultSettings[CURLOPT_FORBID_REUSE] TRUE для принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно.
	 */
	private $_defaultSettings;
	/**
	 * Пересление всех поддерживаемых настроек для cURL
	 * @var array
	 */
	private $_allSetting;
	/**
	 * Флаг для включения запросов через прокси сервера
	 * @var bool
	 */
	private $_useProxy;
	/**
	 * Адрес спрокси или класс для работы с прокси
	 * @var string|cProxy
	 */
	public $proxy;
	/**
	 * Хранит разультаты запросов если режим singele, то string, если multi то array
	 * @var string|array
	 */
	private $_answer;
	/**
	 * Дескриптор с текущими настройками и уникальным ключом
	 * @var array
	 * Структура:
	 * $_descriptor['descriptor'] дескриптор  cURL
	 * $_descriptor['info'] Информация выданная функцией curl_getinfo()
	 * $_descriptor['option'][имя опции] = value параметры cURL
	 * $_descriptor['descriptor_key'] уникальный ключ дескриптора для аренды прокси и сохранения cookie
	 */
	private $_descriptor;
	/**
	 * Список дескрипторов с текущими настройками и уникальным ключом для работы в multi режиме
	 * @var array
	 * $_descriptorArray[key]['descriptor'] дескриптор  cURL
	 * $_descriptorArray[key]['info'] Информация выданная функцией curl_getinfo()
	 * $_descriptorArray[key]['option'][имя опции] = value параметры cURL
	 * $_descriptorArray[key]['descriptor_key'] уникальный ключ дескриптора для аренды прокси и сохранения cookie
	 */
	private $_descriptorArray;
	/**
	 * Количество потоков cURL в режиме multi
	 * @var int
	 */
	private $_countMultiCurl;
	/**
	 * Количество запросов к одному url в режиме multi
	 * @var int
	 */
	private $_countMultiStream;
	/**
	 * Количество дескрипторов которые нужно инициализировать для режима multi
	 * @var int = _countMultiCurl*_countMultiStream
	 */
	private $_countMultiDescriptor;
	/**
	 * Текущий номер повторного запроса для получения контента
	 * @var int
	 */
	private $_numberRepeat;
	/**
	 * Максимальное количество разрешенных повторных запросов для получения корректного ответа
	 * @var int
	 */
	private $_maxNumberRepeat; // максимальное количество повторных запросов на получение контента
	/**
	 * Минимальный размер ответа в байтах
	 * @var int
	 */
	private $_minSizeAnswer;
	/**
	 * Тип получаемых данных
	 * @var mixed
	 * [file] Файл
	 * [img] Изображение
	 * [text] Текст
	 * [html] html страницы
	 */
	private $_typeContent;
	/**
	 * Флаг на включение запроса из кеша поисковых машин если страница не доступна
	 * @var bool
	 */
	private $_inCache;
	/**
	 * Флаг на включение смены кодировки текста
	 * @var bool
	 */
	private $_encodingAnswer;
	/**
	 * Имя кодировки в которую преобразовывать текст ответа
	 * @var string
	 */
	private $_encodingName;
	/**
	 * Имя кодировки полученого текста
	 * @var string
	 */
	private $_encodingNameAnswer;
	/**
	 * Флаг на включение проверки ответа на корректность
	 * @var bool
	 */
	private $_checkAnswer;
	/**
	 * Режим скачивания контента
	 * @var string
	 * multi многопоточный режим
	 * string однопоточный режим
	 */
	private $_modeGetContent;
	/**
	 * Папка в которую сохраняются файлы cookie
	 * @var string
	 */
	private $_dirCookie;

	/**
	 * Количество одновременных запросов
	 * @var int
	 */
	private $_countRequests;

	/**
	 * текущее количество редиректов в одном запросе
	 * @var int
	 */
	private $_redirectCount;

	/**
	 * Максимальное количество редиректов для одного запроса
	 * @var  int
	 */
	private $_maxRedirect;

	/**
	 * использование статического файла cookie
	 * @var bool
	 */
	private $_useStaticCookie;

	/**
	 * имя файла с cookie
	 * @var string
	 */
	private $_cookieFile;
	/**
	 * @var string
	 */
	private $_referer;
	/**
	 * @return \GetContent\cGetContent
	 */
	function __construct() {
		$this->_allSetting = array(
			CURLOPT_HEADER,
			CURLOPT_URL,
			CURLOPT_TIMEOUT,
			CURLOPT_USERAGENT,
			CURLOPT_RETURNTRANSFER,
			CURLOPT_FOLLOWLOCATION,
			CURLOPT_REFERER,
			CURLOPT_POST,
			CURLOPT_POSTFIELDS,
			CURLOPT_FRESH_CONNECT,
			CURLOPT_FORBID_REUSE,
			CURLOPT_HTTPHEADER
		);
		$this->setDirCookie("get_content_files/cookie");
		$this->restoreDefaultSettings();
		$this->_countMultiStream = 1;
		$this->_countMultiCurl = 1;
		$this->setCountMultiDescriptor();
		$this->setUseProxy(false);
		$this->setNumberRepeat(0);
		$this->setMaxNumberRepeat(10);
		$this->setMinSizeAnswer(1);
		$this->setTypeContent("text");
		$this->setInCache(false);
		$this->setEncodingAnswer(true);
		$this->setEncodingName("UTF-8");
		$this->setCheckAnswer(false);
		$this->setRedirectCount(0);
		$this->setMaxRedirect(10);
		$this->setUseStaticCookie(false);
		$this->setModeGetContent('single');
	}

	function __destrukt() {
		$this->clearCookie();
		$this->closeGetContent();

	}

	/**
	 * функция для проверки доступа к необходимым ресурсам системы
	 */
	public function functionCheck() {
		echo "cGetContent->functionCheck {</br>\n";
		$mess = '';
		if (!function_exists('curl_init')) $mess .= "Error: CURL is not installed</br>\n";
		if (!is_dir($this->getDirCookie())) {
			$mess .= "Warning: folder for the cookie does not exist</br>\n";
		} else {
			if (!is_readable($this->getDirCookie()) || !is_writable($this->getDirCookie())) {
				$mess .= "Warning: folder for the cookie does not have the necessary rights to use</br>\n";
			}
		}
		if (!class_exists('cProxy')) $mess .= "Warning: cProxy class is declared, can not work with proxy</br>\n";
		if (!class_exists('cStringWork')) $mess .= "Warning: cStringWork class is declared, word processing is not possible</br>\n";
		if ($mess) echo $mess . " To work correctly, correct the above class cGetContent requirements </br>\n";
		else echo "cGetContent ready</br>\n";
		echo "cGetContent->functionCheck }</br>\n";
	}

	/**
	 * Удаляет старые файлы, которые уже не используются
	 * @param int $storageTime время хранения прокси
	 */
	public function clearCookie($storageTime = 172800) {
		$fileList = glob($this->getDirCookie() . "*.cookie");
		foreach ($fileList as $value) {
			if(preg_match("#/(?<create_time>\d+)(?:\.|\s*)\d*\.cookie$#iU", $value, $match)){
				if ((int)$match['create_time'] < time() - $storageTime) {
					unlink($value);
				}
			}
		}
	}

	/**
	 * Адерс должен быть относительным папке где лежит исходник класса
	 * @param string $val
	 */
	public function setDirCookie($val) {
		$this->_dirCookie = $val;
	}

	public function getDirCookie() {
		return GC_ROOT_DIR . "/" . $this->_dirCookie . "/";
	}

	public function setCountRequests($val) {
		$this->_countRequests = $val;
	}

	public function getCountRequests() {
		return $this->_countRequests;
	}

	public function getRedirectCount(){
		return $this->_redirectCount;
	}

	public function setRedirectCount($val){
		$this->_redirectCount = $val;
	}

	public function getMaxRedirect(){
		return $this->_maxRedirect;
	}

	public function setMaxRedirect($val){
		$this->_maxRedirect = $val;
	}

	private function useRedirect(){
		$this->setRedirectCount($this->getRedirectCount()+1);
		return ($this->getRedirectCount()<=$this->getMaxRedirect());
	}

	public function setUseStaticCookie($val){
		$this->_useStaticCookie = (bool)$val;
	}

	private function getUseStaticCookie(){
		return $this->_useStaticCookie;
	}

	public function setCookieFile($val){
		$this->setUseStaticCookie(true);
		$this->_cookieFile = $val;
	}

	public function getCookieFile(){
		return $this->_cookieFile;
	}

	public function setReferer(&$descriptor,$val){
		$this->_referer = $val;
		$this->setOptionToDescriptor($descriptor, CURLOPT_REFERER, $this->_referer);
	}

	public function getReferer(){
		return $this->_referer;
	}
	/**
	 * @param int   $option
	 * @param mixed $value
	 */
	public function setDefaultSetting($option, $value) {
		$this->_defaultSettings[$option] = $value;
	}

	/**
	 * @param int $option
	 * @return mixed
	 */
	public function getDefaultSetting($option) {
		return $this->_defaultSettings[$option];
	}

	/**
	 * @param array $value
	 * @return bool
	 */
	public function setDefaultSettings($value) {
		if (is_array($value)) {
			$this->_defaultSettings = $value;
			return true;
		} else return false;
	}

	/**
	 * @return array
	 */
	public function getDefaultSettings() {
		return $this->_defaultSettings;
	}

	/**
	 * Востанавливает настройки по умолчанию для всех потоков где не изменены настройки дескрипторов
	 */
	public function restoreDefaultSettings() {
		$this->setDefaultSettings(array(
			CURLOPT_HEADER => true,
			CURLOPT_URL => "http://ya.ru",
			CURLOPT_TIMEOUT => 30,
			CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.101 Safari/537.36",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_REFERER => '',
			CURLOPT_POSTFIELDS => '',
			CURLOPT_POST => false,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_FORBID_REUSE => true,
			CURLOPT_HTTPHEADER => array()
		));
	}

	/**
	 * Включает/выключает работу через прокси и может установить прокси который задаст пользователь
	 * @param bool|string $value
	 * @return bool
	 */
	public function setUseProxy($value = false) {
		switch ((bool)$value) {
			case true:
				if (is_string($value)){
					if(cStringWork::isIp($value)){
						$this->proxy = $value;
					} else {
						$value = false;
					}
				} elseif(!is_object($this->proxy)) {
					$this->proxy = new old_cProxy();
				}
				break;
			case false:
				$value = false;
				break;
			default:
				return false;
		}
		$this->_useProxy = (bool)$value;
		return true;
	}

	public function getUseProxy() {
		return $this->_useProxy;
	}

	/**
	 * @param int $value
	 */
	public function setNumberRepeat($value = 0) {
		$this->_numberRepeat = $value;
	}

	public function getNumberRepeat() {
		return $this->_numberRepeat;
	}

	/**
	 * @param int $value
	 */
	public function setMaxNumberRepeat($value = 10) {
		$this->_maxNumberRepeat = $value;
	}

	public function getMaxNumberRepeat() {
		return $this->_maxNumberRepeat;
	}

	/**
	 * Проверяет возможность сделать повторный запрос
	 * @return bool
	 */
	private function repeatGetContent() {
		if ($this->getNumberRepeat() < $this->getMaxNumberRepeat()) {
			$this->nextRepeat();
			return true;
		} else {
			$this->endRepeat();
			return false;
		}
	}

	/**
	 * Регестрирует повторный запрос
	 */
	private function nextRepeat() {
		$num_repeat = $this->getNumberRepeat();
		$num_repeat++;
		$this->setNumberRepeat($num_repeat);
	}

	/**
	 * Обнуляет счетчик повторных запросов
	 */
	private function endRepeat() {
		$this->setNumberRepeat(0);
	}

	/**
	 * @param int $value
	 */
	public function setMinSizeAnswer($value = 5000) {
		$this->_minSizeAnswer = $value;
	}

	public function getMinSizeAnswer() {
		return $this->_minSizeAnswer;
	}

	/**
	 * @param string $typeContent file|img|text|html
	 * @return bool
	 */
	public function setTypeContent($typeContent = "text") {
		switch ($typeContent) {
			case 'file':
				$this->_typeContent = 'file';
				$this->setEncodingAnswer(false);
				return true;
				break;
			case 'img':
				$this->_typeContent = 'img';
				$this->setEncodingAnswer(false);
				return true;
				break;
			case 'text':
				$this->_typeContent = 'text';
				$this->setEncodingAnswer(true);
				return true;
				break;
			case 'html':
				$this->_typeContent = 'html';
				$this->setEncodingAnswer(true);
				break;
			default:
				break;
		}
		return false;
	}

	public function getTypeContent() {
		return $this->_typeContent;
	}

	/**
	 * @param bool $value
	 */
	public function setInCache($value = false) {
		$this->_inCache = $value;
	}

	public function getInCache() {
		return $this->_inCache;
	}

	/**
	 * @param bool $value
	 */
	public function setEncodingAnswer($value = false) {
		$this->_encodingAnswer = $value;
	}

	public function getEncodingAnswer() {
		return $this->_encodingAnswer;
	}

	/**
	 * @param string $value
	 */
	public function setEncodingName($value = "UTF-8") {
		$this->_encodingName = $this->checkNameEncoding($value);
	}

	public function getEncodingName() {
		return $this->_encodingName;
	}

	/**
	 * @param string $value
	 */
	public function setEncodingNameAnswer($value) {
		$this->_encodingNameAnswer = $this->checkNameEncoding($value);
	}

	public function getEncodingNameAnswer() {
		return $this->_encodingNameAnswer;
	}

	/**
	 * Возвращает форматированое имя поддерживаемой кодировки
	 * @param $value название кодировки
	 * @return bool|string
	 */
	private function checkNameEncoding($value) {
		switch (true) {
			case preg_match('#1251#', $value):
				$value = 'windows-1251';
				break;
			case preg_match('#utf-?8#i', $value):
				$value = 'UTF-8';
				break;
			case preg_match('#koi8-r#i', $value):
				$value = 'koi8-r';
				break;
			case preg_match('#iso8859-?5#i', $value):
				$value = 'iso8859-5';
				break;
			default:
				$value = false;
				break;
		}
		return $value;
	}

	/**
	 * @param bool $value
	 */
	public function setCheckAnswer($value = true) {
		$this->_checkAnswer = $value;
	}

	public function getCheckAnswer() {
		return $this->_checkAnswer;
	}

	/**
	 * @param int $value
	 */
	public function setCountMultiCurl($value = 1) {
		if ($this->getCountMultiCurl() != $value) {
			$this->closeGetContent();
			$this->_countMultiCurl = $value;
			$this->setCountMultiDescriptor();
			$this->initGetContent();
		}
	}

	public function getCountMultiCurl() {
		return $this->_countMultiCurl;
	}

	/**
	 * @param int $value
	 */
	public function setCountMultiStream($value = 1) {
		if ($this->getCountMultiStream() != $value) {
			$this->closeGetContent();
			$this->_countMultiStream = $value;
			$this->setCountMultiDescriptor();
			$this->initGetContent();
		}
	}

	public function getCountMultiStream() {
		return $this->_countMultiStream;
	}

	/**
	 * Задает число дескрипторов cURL для подлучение данных
	 */
	private function setCountMultiDescriptor() {
		$this->_countMultiDescriptor = $this->getCountMultiCurl() * $this->getCountMultiStream();
	}

	private function getCountMultiDescriptor() {
		return $this->_countMultiDescriptor;
	}

	/**
	 * @param string $val single|multi
	 * @return bool
	 */
	public function setModeGetContent($val = 'single') {
		$this->closeGetContent();
		switch ($val) {
			case 'single':
				$this->_modeGetContent = 'single';
				$this->setDefaultSetting(CURLOPT_FOLLOWLOCATION,false);
				break;
			case 'multi':
				$this->_modeGetContent = 'multi';
				if ($this->getCountMultiCurl() < 1) $this->setCountMultiCurl(1);
				$this->setDefaultSetting(CURLOPT_FOLLOWLOCATION,true);
				break;
			default:
				return false;
		}
		$this->initGetContent();
		return true;
	}

	public function getModeGetContent() {
		return $this->_modeGetContent;
	}

	public function &getDescriptor() {
		return $this->_descriptor;
	}

	public function &getDescriptorArray() {
		return $this->_descriptorArray;
	}

	/**
	 * Инициализирует дескрипторы cURL
	 */
	private function initGetContent() {
		switch ($this->getModeGetContent()) {
			case 'single':
				$this->initSingleGetContent();
				break;
			case 'multi':
				$this->initMultiGetContent();
				break;
			default:
				# code
				break;
		}
	}

	/**
	 * Инициализация дескриптора cURL в режиме single
	 * @return void
	 */
	private function initSingleGetContent() {
		$descriptor =& $this->getDescriptor();
		if (!isset($descriptor['descriptor_key'])) $descriptor['descriptor_key'] = microtime(1) . mt_rand();
		$descriptor['descriptor'] = curl_init();
	}

	/**
	 * Инициализация дескриптора cURL в режиме multi
	 * @return void
	 */
	private function initMultiGetContent() {
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		$descriptor['descriptor'] = curl_multi_init();
		if (is_array($descriptorArray) && count($descriptorArray) > $this->getCountMultiDescriptor()) {
			$descriptorArray = array_slice($descriptorArray, 0, $this->getCountMultiDescriptor());
		}
		for ($i = 0; $i < $this->getCountMultiDescriptor(); $i++) {
			if (!isset($descriptorArray[$i]['descriptor_key'])) $descriptorArray[$i]['descriptor_key'] = microtime(1) . mt_rand();
			$descriptorArray[$i]['descriptor'] = curl_init();
			curl_multi_add_handle($descriptor['descriptor'], $descriptorArray[$i]['descriptor']);
		}
	}

	/**
	 * Закрывает инициализированные дескрипторы cURL
	 * @param bool $reinit Переменная для обхода стирания параметров в опциях дескриптора для повторного запроса. Причина: не срабатывания параметров CURLOPT_FRESH_CONNECT и CURLOPT_FORBID_REUSE после примерно трех запросов опция по не известной причине не срабатывают.
	 */
	private function closeGetContent($reinit = false) {
		$descriptor =& $this->getDescriptor();
		if (isset($descriptor['descriptor'])) {
			switch ($this->getModeGetContent()) {
				case 'single':
					$this->closeSingleGetContent($reinit);
					break;
				case 'multi':
					$this->closeMultiGetContent($reinit);
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Закрывает инициализированные cURL дескриптроры в режиме single
	 * @param $reinit Переменная для обхода стирания параметров в опциях дескриптора для повторного запроса. Причина: не срабатывания параметров CURLOPT_FRESH_CONNECT и CURLOPT_FORBID_REUSE после примерно трех запросов опция по не известной причине не срабатывают.
	 * @return void
	 */
	private function closeSingleGetContent($reinit) {
		$descriptor =& $this->getDescriptor();
		curl_close($descriptor['descriptor']);
		if ($this->getUseProxy() && is_object($this->proxy)) {
			$this->proxy->removeAllRentFromCode($descriptor['descriptor_key']);
		}
		unset($descriptor['descriptor']);
		if (!$reinit) unset($descriptor['option']);
	}

	/**
	 * Закрывает инициализированные cURL дескриптроры в режиме multi
	 * @param $reinit Переменная для обхода стирания параметров в опциях дескриптора для повторного запроса. Причина: не срабатывания параметров CURLOPT_FRESH_CONNECT и CURLOPT_FORBID_REUSE после примерно трех запросов опция по не известной причине не срабатывают.
	 * @return void
	 */
	private function closeMultiGetContent($reinit) {
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		if (is_array($descriptorArray)) {
			foreach ($descriptorArray as $key => $value) {
				if (isset($descriptorArray[$key]['descriptor'])) {
					@curl_multi_remove_handle($descriptor['descriptor'], $descriptorArray[$key]['descriptor']);
					curl_close($descriptorArray[$key]['descriptor']);
					if ($this->getUseProxy() && is_object($this->proxy)) {
						$this->proxy->removeAllRentFromCode($descriptorArray[$key]['descriptor_key']);
					}
					unset($descriptorArray[$key]['descriptor']);
					if (!$reinit) unset($descriptorArray[$key]['option']);
				}
			}
			unset($value);
		}
		@curl_multi_close($descriptor['descriptor']);
	}

	/**
	 * Повторная инициализация дескрипторов cURL, функция создана по причине не срабатывания параметров CURLOPT_FRESH_CONNECT и CURLOPT_FORBID_REUSE после примерно трех запросов опция по не известной причине не срабатывают.
	 */
	private function reinitGetContent() {
		switch ($this->getModeGetContent()) {
			case 'single':
				$this->closeSingleGetContent(true);
				$this->initSingleGetContent();
				break;
			case 'multi':
				$this->closeMultiGetContent(true);
				$this->initMultiGetContent();
				break;
			default:
				break;
		}
	}

	/**
	 * Выполнение заросов по $url с определением по какому методу осуществлять запрос
	 * @param string|array $url
	 * @param string $reg регулярное выражение для дополнительной проверки ответа
	 * @return array|string
	 */
	public function getContent($url = "", $reg = '##') {
		if (is_string($url) && $this->getModeGetContent() != 'single') $url = array($url);
		if (is_array($url) && $this->getModeGetContent() != 'multi') $this->setModeGetContent('multi');
		switch ($this->getModeGetContent()) {
			case 'single':
				$this->getSingleContent($url, $reg);
				break;
			case 'multi':
				$this->getMultiContent($url, $reg);
				break;
			default:
				break;
		}
		$this->closeGetContent();
		$this->initGetContent();
		return $this->getAnswer();
	}

	/**
	 * Совершает зарос в режиме single
	 * @param        $url
	 * @param string $reg регулярное выражение для дополнительной проверки ответа
	 * @return string
	 */
	private function getSingleContent($url, $reg) {
		$descriptor =& $this->getDescriptor();
		do {
			if ($this->getNumberRepeat() > 0) $this->reinitGetContent();
			$this->setOptionToDescriptor($descriptor, CURLOPT_URL, $url);
			$this->setOptionsToDescriptor($descriptor);
			$answer = $this->execSingleGetContent();
			$this->setReferer($descriptor, $url);
			$descriptor['info'] = curl_getinfo($descriptor['descriptor']);
			$descriptor['info']['header'] = $this->getHeader($answer);
			if($this->isRedirect()){
				if($this->useRedirect()){
					$answer = $this->getSingleContent($descriptor['info']['redirect_url'], $reg);
				} else {
					return false;
				}
			}
			$this->setRedirectCount(0);
			if ($reg && preg_match($reg, $answer)) $regAnswer = true;
			else $regAnswer = false;
			if ((!$this->getCheckAnswer() || $this->checkAnswerValid($answer, $descriptor['info'])) && $regAnswer) {
				$this->_answer = $answer;
				$this->endRepeat();
				break;
			} elseif ($this->getUseProxy() && is_object($this->proxy)) {
				$this->proxy->removeProxyInList($descriptor['option'][CURLOPT_PROXY]);
			}
		} while ($this->repeatGetContent());
		$this->_answer = $this->prepareContent($answer);
		return $this->getAnswer();
	}

	/**
	 * Совершает запрос в режиме multi
	 * @param        $url
	 * @param string $reg регулярное выражение для дополнительной проверки ответа
	 * @return array
	 */
	private function getMultiContent($url, $reg) {
		$copyUrl = $url; //Копируем для создания связи по ключам после удаления из основного массива
		$goodAnswer = array();
		do {
			if ($this->getNumberRepeat() > 0) $this->reinitGetContent();
			$this->setCountMultiCurl(count($url));
			$descriptorArray =& $this->getDescriptorArray();
			$countMultiStream = $this->getCountMultiStream();
			$j = 0;
			$urlDescriptors = array();
			foreach ($url as $keyUrl => $valueUrl) {
				for ($i = 0; $i < $countMultiStream; $i++) {
					$urlDescriptors[$j] = $keyUrl; //Для связи ключа url и вычисления ключа хорошего ответа
					if (isset($descriptorArray[$j]['descriptor'])) {
						$this->setOptionToDescriptor($descriptorArray[$j], CURLOPT_URL, $valueUrl);
					}
					$j++;
				}
			}
			foreach ($descriptorArray as $key => $value) $this->setOptionsToDescriptor($descriptorArray[$key]);
			unset($value);
			$answer = $this->execMultiGetContent();
			foreach ($answer as $key => $value) {
				$descriptorArray[$key]['info'] = curl_getinfo($descriptorArray[$key]['descriptor']);
				$descriptorArray[$key]['info']['header'] = $this->getHeader($value);
				$keyGoodAnswer = ($urlDescriptors[$key] * $countMultiStream) + $key % $countMultiStream;
				if ($reg && preg_match($reg, $value)) $regAnswer = true;
				else $regAnswer = false;
				if (!isset($goodAnswer[$keyGoodAnswer]) && (!$this->getCheckAnswer() || $this->checkAnswerValid($value, $descriptorArray[$key]['info'])) && $regAnswer) {

					if (isset($url[$urlDescriptors[$key]])) unset($url[$urlDescriptors[$key]]);
					$goodAnswer[$keyGoodAnswer] = $value;
				} elseif ($this->getUseProxy() && is_object($this->proxy)) {
					$this->proxy->removeProxyInList($descriptorArray[$key]['option'][CURLOPT_PROXY]);
				}
			}
			if (count($url) == 0) {
				$this->endRepeat();
				break;
			}
		} while ($this->repeatGetContent());
		foreach ($goodAnswer as &$value) $value = $this->prepareContent($value);
		$tmpAnswer = array();
		$j = 0;
		foreach ($copyUrl as $keyUrl => $valueUrl) {
			for ($i = 0; $i < $countMultiStream; $i++) {
				if (isset($goodAnswer[$j])) $tmpAnswer[$keyUrl][$i] = $goodAnswer[$j];
				$j++;
			}
		}
		return $this->_answer = $tmpAnswer;
	}

	/**
	 * Присваивает настройки cURL декскриптору
	 * @param array $descriptor   дескриптор cURL
	 * @param array $optionArray список настроек для cURL дексриптора
	 * @return bool
	 */
	public function setOptionsToDescriptor(&$descriptor, $optionArray = array()) {
		foreach ($this->_allSetting as $keySetting) {
			if (isset($optionArray[$keySetting])) $this->setOptionToDescriptor($descriptor, $keySetting, $optionArray[$keySetting]);
			elseif(isset($descriptor['option'][$keySetting])) $this->setOptionToDescriptor($descriptor,$keySetting,$descriptor['option'][$keySetting]);
			else $this->setOptionToDescriptor($descriptor, $keySetting);
		}
		unset($keySetting);
		if ($this->getUseProxy()) {
			if (is_object($this->proxy)) {
				if (
					is_string($proxyIp = $this->proxy->getProxy($descriptor['descriptor_key'], cStringWork::getDomainName($descriptor['option'][CURLOPT_URL])))
					&& cStringWork::isIp($proxyIp)
					){
					$this->setOptionToDescriptor($descriptor, CURLOPT_PROXY, $proxyIp);
				} else {
					$descriptor['option'][CURLOPT_URL] = '';
				}
			} elseif (is_string($this->proxy)){
				$this->setOptionToDescriptor($descriptor, CURLOPT_PROXY, $this->proxy);
			}
		} else {
			unset($descriptor['option'][CURLOPT_PROXY]);
		}
		if($this->getUseStaticCookie() && $this->getModeGetContent() == 'single'){
			$cookieFile = $this->getDirCookie() . $this->getCookieFile() . ".cookie";
		} else {
			$cookieFile = $this->getDirCookie() . $descriptor['descriptor_key'] . ".cookie";
		}
		if (!is_writable($cookieFile)) {
			$fh = fopen($cookieFile, "a+");
			fclose($fh);
		}
		$this->setOptionToDescriptor($descriptor, CURLOPT_COOKIEJAR, $cookieFile);
		$this->setOptionToDescriptor($descriptor, CURLOPT_COOKIEFILE, $cookieFile);
		if (curl_setopt_array($descriptor['descriptor'], $descriptor['option'])) return true;
		else return false; // :| ошибка в присваивании параметров

	}

	/**
	 * Присваивает конкретную настройку для cURL дескриптора
	 * @param array $descriptor ссылка на cURL дескриптор
	 * @param int   $option     имя параметра для cURL дескриптора
	 * @param mixed $value      значение опции для cURL дескриптора
	 * @param int   $key        ключ для дескриптора в режиме multi
	 * @return bool
	 */
	public function setOptionToDescriptor(&$descriptor, $option, $value = -2, $key = -2) //
	{
		if ($key != -2) {
			if (array_key_exists($key, $descriptor)) {
				if ($value == -2) $descriptor[$key]['option'][$option] = $this->getDefaultSetting($option);
				else $descriptor[$key]['option'][$option] = $value;
				if ($this->checkOption($descriptor[$key], $option, $descriptor[$key]['option'][$option])) return false;
			}
		} else {
			if ($value == -2) $descriptor['option'][$option] = $this->getDefaultSetting($option);
			else $descriptor['option'][$option] = $value;
			if ($this->checkOption($descriptor, $option, $descriptor['option'][$option])) return true;
		}
		return true;
	}

	/**
	 * проверяет на корректность опции и включает/выключает зависимые опции в дескрипторе cURL
	 * @param array $descriptor дескриптор cURL
	 * @param int   $option     имя параметра
	 * @param mixed $value      значение параметра
	 * @return bool
	 */
	private function checkOption(&$descriptor, $option, $value = NULL) {
		switch ($option) {
			case CURLOPT_POST:
				if ($value != NULL) $descriptor['option'][$option] = (bool)$value;
				break;
			case CURLOPT_POSTFIELDS:
				if (!$value) {
					unset($descriptor['option'][$option]);
					$this->setOptionToDescriptor($descriptor, CURLOPT_POST, false);
					return true;
				} else $this->setOptionToDescriptor($descriptor, CURLOPT_POST, true);
				break;
			case CURLOPT_URL:
				if (!preg_match("#(http|https)://#iUm", $descriptor['option'][$option])) $descriptor['option'][$option] = "http://" . $value;
				if ($this->getInCache()) {
					preg_match("#https?://(?<url>.*)$#iUm", $descriptor['option'][$option], $match);
					$descriptor['option'][$option] = "http://webcache.googleusercontent.com/search?q=cache:" . $match['url'];
					return true;
				}
				break;
			case CURLOPT_PROXY:
				$this->_useProxy = true;
				break;
			case CURLOPT_HTTPHEADER:
				if (!is_array($value) || !count($value)) unset($descriptor['option'][$option]);
				break;
			default:
				break;
		}
		return false;
	}

	/**
	 * Вырезает HTTP заголовки из ответа
	 * @param $answer
	 * @return string
	 */
	private function getHeader(&$answer){
		$header = '';
		if($answer){
			do{
				if(preg_match("%(?<head>^[^<>]*HTTP/\d+\.\d+.*)(\r\n\r\n|\r\r|\n\n)%Ums",$answer,$data)){
					$header[] = $data['head'];
					$answer = trim(preg_replace('%'.preg_quote($data['head'],'%').'%ims', '', $answer));
				} else {
					break;
				}
			}while(true);
		}
		return $header;
	}

	/**
	 * Выполнение запроса cURL в режиме single
	 * @return string
	 */
	private function execSingleGetContent() {
		$descriptor =& $this->getDescriptor();
		$this->_answer = curl_exec($descriptor['descriptor']);
		return $this->_answer;
	}

	/**
	 * Выполнение запроса cURL в режиме multi
	 * @return array
	 */
	private function execMultiGetContent() {
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		do {
			curl_multi_exec($descriptor['descriptor'], $running);
			usleep(100);
		} while ($running > 0);
		$this->_answer = array();
		foreach ($descriptorArray as $key => $value) $this->_answer[$key] = curl_multi_getcontent($descriptorArray[$key]['descriptor']);
		unset($value);
		return $this->_answer;
	}

	/**
	 * Возвращает данные полученые после запросов
	 * @param bool $getAllAnswer для режима multi, возваращать все или самы большой по размеру
	 * @return array|string
	 */
	public function getAnswer($getAllAnswer = false) {
		switch ($this->getModeGetContent()) {
			case 'single':
				return $this->_answer;
				break;
			case 'multi':
				if (!$getAllAnswer) {
					$a = array();
					foreach ($this->_answer as $key => $value) $a[$key] = $this->getBigAnswer($value);
					return $a;
				} else return $this->_answer;
				break;
			default:
				break;
		}
		return false;
	}

	/**
	 * Получить максимально большой ответ из набора
	 * @param $a набор ответов на заросы multi_cURL
	 * @return bool|string
	 */
	private function getBigAnswer($a) {
		$bigA = 0;
		$bigKey = 0;
		foreach ($a as $key => $value) {
			$thisA = strlen($value);
			if ($thisA > $bigA) {
				$bigA = $thisA;
				$bigKey = $key;
			}
		}
		return $a[$bigKey];
	}

	/**
	 * Проверка ответа на корректность
	 * @param       $answer    Текс ответа
	 * @param array $curlData массив информации о запросе при помощи функции curl_getinfo()
	 * @return bool
	 */
	private function checkAnswerValid($answer, $curlData) {
		if (!$this->httpCode($curlData['http_code'])) return false;
		if (($curlData['size_download'] < $curlData['download_content_length'] && $curlData['download_content_length'] != -1) || $curlData['size_download'] < $this->getMinSizeAnswer()) return false;
		switch ($this->getTypeContent()) {
			case 'file':
				if ($this->mimeType($curlData['content_type'], 'file')) return true;
				break;
			case 'img':
				if ($this->mimeType($curlData['content_type'], 'img')) return true;
				break;
			case 'html':
				if ($this->mimeType($curlData['content_type'], 'html') && preg_match('#<\s*/\s*(html|body)[^<>]*>#ims', $answer)) return true;
				break;
			default:
				break;
		}
		return false;
	}

	/**
	 * Провераяет соответствие MIME тип полученого ответом на запрос с ожидаемым
	 * @param string $mime имя MIME типа
	 * @param string $type тип ожидаемого контента
	 * @return bool
	 */
	private function mimeType($mime, $type) {
		switch ($type) {
			case 'file':
				return true;
				break;
			case 'img':
				if (preg_match('#image/(gif|p?jpeg|png|svg\+xml|tiff|vnd\.microsoft\.icon|vnd\.wap\.wbmp)#i', $mime)) return true;
				else return false;
				break;
			case 'html':
				if (preg_match('#text/html#i', $mime)) return true;
				else return false;
				break;
		}
		return false;
	}

	/**
	 * Проверает HTTP код ответа на запрос
	 * @url http://goo.gl/KKiFi
	 * @param int $httpCode
	 * @return bool
	 * @internal в будущем планируется вести лог с ошибками и из этой функции будет записываться ошибки
	 * @internal в запросах и дополнительо будет приниматься решения больше на посылать заросы на текуший URL
	 * @internal Пример: Если вернуло ошибку 500, то не повторять запрос
	 */
	private function httpCode($httpCode) {
		switch ((int)$httpCode) {
			case 100:
				return false;
			case 101:
				return false;
			case 102:
				return false;
			case 200:
				return true;
			case 201:
				return true;
			case 202:
				return true;
			case 203:
				return true;
			case 204:
				return true;
			case 205:
				return true;
			case 206:
				return true;
			case 207:
				return true;
			case 226:
				return true;
			case 300:
				return false;
			case 301:
				return false;
			case 302:
				return false;
			case 303:
				return false;
			case 304:
				return false;
			case 305:
				return false;
			case 306:
				return false;
			case 307:
				return false;
			case 400:
				return false;
			case 401:
				return false;
			case 402:
				return false;
			case 403:
				return false;
			case 404:
				return false;
			case 405:
				return false;
			case 406:
				return false;
			case 407:
				return false;
			case 408:
				return false;
			case 409:
				return false;
			case 410:
				return false;
			case 411:
				return false;
			case 412:
				return false;
			case 413:
				return false;
			case 414:
				return false;
			case 415:
				return false;
			case 416:
				return false;
			case 417:
				return false;
			case 422:
				return false;
			case 423:
				return false;
			case 424:
				return false;
			case 425:
				return false;
			case 426:
				return false;
			case 428:
				return false;
			case 429:
				return false;
			case 431:
				return false;
			case 449:
				return false;
			case 451:
				return false;
			case 456:
				return false;
			case 499:
				return false;
			case 500:
				return false;
			case 501:
				return false;
			case 502:
				return false;
			case 503:
				return false;
			case 504:
				return false;
			case 505:
				return false;
			case 506:
				return false;
			case 507:
				return false;
			case 508:
				return false;
			case 509:
				return false;
			case 510:
				return false;
			case 511:
				return false;
			default:
				false;
		}
		return false;
	}

	/**
	 * Подготовка ответа к выдаче
	 * @param $answer
	 * @return string
	 */
	private function prepareContent($answer) {
		switch ($this->getTypeContent()) {
			case 'file':
				break;
			case 'text':
				$answer = $this->encodingAnswerText($answer);
				break;
			case 'html':
				$answer = $this->encodingAnswerText($answer);
				break;
			default:
				break;
		}
		return $answer;
	}

	/**
	 * Преобразует кодировку текста в необходимую
	 * @param string $text текс который нухно перекодировать
	 * @return string текст с измененой кодировкой
	 */
	private function encodingAnswerText($text = "") {
		if ($this->getEncodingAnswer()) {
			$to = $this->getEncodingName();
			$from = cStringWork::getEncodingName($text);
			if ($from != $to) $text = iconv($from, $to, $text);
			return $text;
		} else return $text;
	}

	/**
	 * Проверяет заголовки на признак редиректа
	 * @return bool
	 */
	private function isRedirect(){
		return ($this->_descriptor['info']['http_code'] == 301 || $this->_descriptor['info']['http_code'] == 302);
	}
}
