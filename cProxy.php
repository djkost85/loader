<?php
namespace GetContent;
/**
 * Class cProxy
 * Класс для получения актуального списка прокси, проверки работоспособности прокси с определенными сайтами
 * Распределяет адреса между потоками для исключения запросов с одного ip
 * Проверяет функционал который поддерживает прокси
 * Скачивает с представленных источников адреса прокси
 * @author  Evgeny Pynykh <bpteam22@gmail.com>
 * @package GetContent
 * @version 2.0
 */
class cProxy
{

	/**
	 * Массив состоящий из перечня прокси адресов и информации о них
	 * Структура:
	 * _proxyList['content'][индекс]["proxy"] адрес прокси сервера
	 * _proxyList['content'][индекс]["source_proxy"]  источник прокси
	 * _proxyList['content'][индекс]["type_proxy"]  протокол прокси HTTP SOCKS5
	 * _proxyList['content'][индекс]["renters"]  нформация об арендаторе адреса прокси
	 * _proxyList['content'][индекс]["renters"][индекс]["start_rent"] время начала аренды прокси адреса
	 * _proxyList['content'][индекс]["renters"][индекс]["renter_code"] код аренды
	 * _proxyList['content'][индекс]["renters"][индекс]["user_site"] сайт на котором используется прокси один прокси могут использовать несколько потоков, главное чтоб ресурсы были разные
	 * _proxyList["time"] время последнего обновления
	 * _proxyList["count"] количество подходящих прокси
	 * _proxyList["url"] URL сайта на котором проверяется прокси
	 * _proxyList["check_word"][индекс] Проверочное слово которое должно быть в ответе с сервера- это регулярное выражение
	 * _proxyList["need_function"][индекс] Необходимые функции которые должен поддерживать прокси
	 * _proxyList["name_list"] Имя лисат этому имени будет соответствовать имя файла
	 * @access protected
	 * @var array
	 */
	protected $_proxyList;
	/**
	 * Адрес папки где храняться файлы для работы класса
	 * @access protected
	 * @var string
	 */
	protected $_dirProxyFile;
	/**
	 * Адрес папки где храняться файлы с proxy списками
	 * @access protected
	 * @var string
	 */
	protected $_dirProxyListFile;
	/**
	 * Папка в которой находятся модули для парсинга
	 * @access protected
	 * @var string
	 */
	protected $_dirUrlProxyList;
	/**
	 * Имена файлов модулей для скачивания списков прокси
	 * @access protected
	 * @var array
	 */
	protected $_fileUrlProxyList;
	/**
	 * Время актуальности прокси (в секундах) в профиле
	 * @access protected
	 * @var int
	 */
	protected $_storageTime;
	/**
	 * Максимально время для аренды прокси, после истечения выдается другой адрес
	 * @access protected
	 * @var int
	 */
	protected $_rentTime;
	/**
	 * Класс для тестирования и скачивания списков прокси
	 * @access protected
	 * @var cGetContent
	 */
	protected $_getContent;
	/**
	 * Имя текущего файла с адресами прокси и характеристиками
	 * @access protected
	 * @var string
	 */
	protected $_fileProxyList;
	/**
	 * Имя списка с адресами прокси и характеристиками
	 * @access protected
	 * @var string
	 */
	protected $_nameList;

	/**
	 * Имя листа по умолчанию в котором хранятся все адреса прокси серверов
	 * @access protected
	 * @var string
	 */
	protected $_defaultList;
	/**
	 * Указатель на файл $_fileProxyList
	 * @access protected
	 * @var $f_heandle_proxy_list
	 */
	protected $_fHeandleProxyList;
	/**
	 * Флаг для выставления опции проверки прокси черес специально зарезервированный сервер на работоспособность прокси
	 * перед использованием
	 * @access protected
	 * @var bool
	 */
	protected $_needCheckProxy;
	/**
	 * Флаг для проверки прокси на анонимность
	 * @access protected
	 * @var bool
	 */
	protected $_needAnonimProxy;
	/**
	 * Флаг для проверки функции cookie в прокси сервере
	 * @access protected
	 * @var bool
	 */
	protected $_needProxyCookie;
	/**
	 * IP сервера на котором работает скрипт используется для проверки анонимности прокси
	 * @access protected
	 * @var string
	 */
	protected $_serverIp;
	/**
	 * Набор URL на странуцы проверки функций прокси сервера если не работает основной
	 * @access protected
	 * @var array
	 */
	protected $_checkUrlProxy;
	/**
	 * Массив для хранения адресов для ячеек с информацией об аренде, для осуществления быстрого доступа к данным
	 * @access protected
	 * @var array
	 */
	protected $_addressKeyRent;
	/**
	 * Метод получения адресов прокси
	 * "random" получение случайных прокси, безконтрольное распределение адресов
	 * "rent" аренда прокси(через один и то-же прокси не могут два потока опрашивать один сайт)
	 * @access protected
	 * @var string
	 */
	protected $_methodGetProxy;
	/**
	 * Последний использованый прокси
	 * @access protected
	 * @var string
	 */
	protected $_lastUseProxy;
	/**
	 * Флаг на разрешение даления прокси из списка
	 * @access protected
	 * @var bool
	 */
	protected $_removeProxy;
	/**
	 * Флаг подтверждающий блокировку таблици или файл на чтение и запись для текущего потока
	 * @access protected
	 * @var bool
	 */
	protected $_accessToProxyList;

	/**
	 * Список поддерживаемых функций прокси
	 * @var array
	 */
	protected $_proxyFunction;

	/**
	 * файл с архивными прокси
	 * @var string
	 */
	protected $_archiveProxyName;

	/**
	 * тип списка прокси
	 * @var string
	 */
	protected $_listType;

	/**
	 * Конструктор инициализируте переменные значениями по умолчанию
	 * @return \GetContent\cProxy
	 */
	function __construct() {
		$this->_storageTime = 72000;
		$this->_rentTime = 3600;
		$this->_getContent = new cGetContent();
		$this->_getContent->setTypeContent('html');
		$this->_getContent->setEncodingAnswer(true);
		$this->setMethodGetProxy("random");
		$this->_dirProxyFile = "proxy_files";
		$this->_dirProxyListFile = "proxy_list";
		$this->_dirUrlProxyList = "proxy_site_module";
		$this->_proxyFunction = array(
			'anonym',
			'referer',
			'post',
			'get',
			'cookie',
			'country'
		);
		$this->_fHeandleProxyList = NULL;
		$this->_fileUrlProxyList = glob($this->getDirUrlProxyList() . "/*.php");
		$this->_checkUrlProxy = require $this->getDirProxyFile() . "/check_url_list.php";
		$this->_proxyList = array();
		$this->_needCheckProxy = true;
		$this->_lastUseProxy = '';
		$this->setDefaultList('all');
		$this->setArchiveProxyName('archive');
		$this->_nameList = $this->getDefaultListName();
		if (!file_exists($this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy")) {
			$this->createProxyList($this->_nameList);
		}
		$this->selectProxyList($this->_nameList);
		$this->setRemoveProxy(false);
	}

	/**
	 * Закрывает все соединения перед уничтожением объекта
	 */
	function __destruct() {
		if($this->getListType() == 'dynamic') $this->closeProxyList();
		unset($this->_getContent);
	}

	/**
	 * функция для проверки доступа к необходимым ресурсам системы
	 */
	public function functionChek() {
		echo "cProxy->functionCheck {</br>\n";
		$mess = '';
		if (!is_dir($this->getDirProxyListFile())) {
			$mess .= "Warning: folder for the proxy profile does not exist</br>\n";
		} else {
			if (!is_readable($this->getDirProxyListFile()) || !is_writable($this->getDirProxyListFile())) {
				$mess .= "Warning: folder for the proxy profile does not have the necessary rights to use</br>\n";
			} elseif (is_file($this->getDirProxyListFile() . "/" . $this->getDefaultListName() . ".proxy")) {
				if (!is_readable($this->getDirProxyListFile() . "/" . $this->getDefaultListName() . ".proxy") || !is_writable($this->getDirProxyListFile() . "/" . $this->getDefaultListName() . ".proxy")) {
					$mess .= "Warning: file for the default proxy list does not have the necessary rights to use</br>\n";
				}
			} else {
				$mess .= "Warning: file for the default proxy list does not exist</br>\n";
				$mess .= "try to create</br>\n";
				$proxy['content'] = array();
				$proxy['url'] = "http://ya.ru";
				$proxy['check_word'] = array("#yandex#ims");
				$proxy['need_function'] = array();
				$proxy['name_list'] = $this->getDefaultListName();
				$proxy['need_update'] = true;
				$proxy['time'] = time();
				$this->createProxyList($this->getDefaultListName());
			}
		}

		if (!class_exists('cProxy')) $mess .= "Warning: cProxy class is declared, can not work with proxy</br>\n";
		if (!class_exists('cStringWork')) $mess .= "Warning: cStringWork class is declared, word processing is not possible</br>\n";
		if ($mess) echo $mess . " To work correctly, correct the above class cProxy requirements </br>\n";
		else echo "cProxy ready</br>\n";
		echo "cProxy->functionCheck }</br>\n";
	}

	/**
	 * @param string $default_list
	 */
	public function setDefaultList($default_list) {
		$this->_defaultList = $default_list;
	}

	public function getArchiveProxyName() {
		return $this->_archiveProxyName;
	}

	public function setArchiveProxyName($value) {
		$this->_archiveProxyName = $value;
	}

	private function getArchiveProxy() {
		$archive = explode("\n", file_get_contents($this->getDirProxyListFile() . "/" . $this->getArchiveProxyName() . ".archive"));
		return $archive;
	}

	private function saveInArchive(array $proxy) {
		$archive = $this->getArchiveProxy();
		$archive = array_merge($archive, $proxy);
		$archive = array_unique($archive);
		$string_proxy = implode("\n", $archive);
		file_put_contents($this->getDirProxyListFile() . "/" . $this->getArchiveProxyName() . ".archive", $string_proxy);
	}

	/**
	 * @return string
	 */
	public function getDefaultListName() {
		return $this->_defaultList;
	}

	/**
	 * @param bool $val
	 */
	public function setRemoveProxy($val) {
		$this->_removeProxy = $val;
	}

	/**
	 * @param bool $val
	 */
	public function setAccessToProxyList($val) {
		$this->_accessToProxyList = $val;
	}

	public function getAccessToProxyList() {
		return $this->_accessToProxyList;
	}

	public function getRemoveProxy() {
		return $this->_removeProxy;
	}


	public function getDirProxyFile() {
		return GC_ROOT_DIR . '/' . $this->_dirProxyFile;
	}

	public function getListType(){
		return $this->_listType;
	}

	public function setListType($val){
		$this->_listType = $val;
	}

	public function getList(){
		switch($this->getListType()){
			case 'dynamic':
				$proxyList = $this->getProxyListInFile();
				$this->freeProxyList();
				break;
			case 'static':
				$proxyList = $this->_proxyList;
				break;
			default:
				$proxyList = $this->_proxyList;
		}
		return $proxyList;
	}

	/**
	 * Получение абсолютного адреса к папке гда лежат файлы конфигурации прокси листов
	 * @return string
	 */
	public function getDirProxyListFile() {
		return $this->getDirProxyFile() . "/" . $this->_dirProxyListFile;
	}

	public function getDirUrlProxyList() {
		return $this->getDirProxyFile() . "/" . $this->_dirUrlProxyList;
	}

	/**
	 * Возвращает ip сервера с которого запущен скрипт или false
	 * @return bool|string
	 */
	public function getServerIp() {
		if (isset($this->_serverIp)) return $this->_serverIp;
		if (false && isset($_SERVER['SERVER_ADDR']) && cStringWork::isIp($_SERVER['SERVER_ADDR'])) {
			$this->_serverIp = $_SERVER['SERVER_ADDR'];
		} else {
			$this->_getContent->setUseProxy(false);
			$this->_getContent->setTypeContent('html');
			$this->_getContent->setModeGetContent('single');
			$this->_getContent->getContent("http://2ip.ru/");
			$answer = $this->_getContent->getAnswer();
			$reg = "/<span>\s*Ваш IP адрес:\s*<\/span>\s*<big[^>]*>\s*(?<ip>[^<]*)\s*<\/big>/iUm";
			if (preg_match($reg, $answer, $match) && !isset($match['ip']) || !$match['ip'] || !cStringWork::isIp($match['ip'])){
				exit('NO SERVER IP');
			}
			$this->_serverIp = $match['ip'];
		}
		return $this->_serverIp;
	}

	/**
	 * @param bool $val
	 */
	public function setNeedProxyCookie($val) {
		$this->_needProxyCookie = $val;
	}

	/**
	 * Поиск сервера из каталога для проверки функций прокси
	 * @param string $checkUrlProxy url сервера для проверки функций прокси, если не работает выберает другой из каталога
	 * @return string возвращает рабочий url для проверки прокси
	 */
	public function getProxyChecker($checkUrlProxy = "") {
		if ($checkUrlProxy === "") $checkUrlProxy = $this->_checkUrlProxy;
		$this->_getContent->setUseProxy(false);
		$this->_getContent->setTypeContent('text');
		$this->_getContent->setModeGetContent('multi');
		$this->_getContent->setCountMultiStream(1);
		$this->_getContent->setMinSizeAnswer(5);
		$answer = $this->_getContent->getContent($checkUrlProxy);
		if(is_array($answer)){
			foreach ($answer as $key => $value) {
				if (preg_match("/^[01]{5}/i", $value)) {
					return $this->_checkUrlProxy[$key];
				}
			}
		} else {
			if (preg_match("/^[01]{5}/i", $answer)) {
				return $this->_checkUrlProxy;
			}
		}
		exit(__FILE__ . " no checker");
	}

	/**
	 * Загружает список прокси из внешних источников
	 * @return array массив с адресами прокси
	 */
	public function downloadProxy() {
		$proxy['content'] = array();
		foreach ($this->_fileUrlProxyList as $valueProxyList) {
			$tmpProxy = require $valueProxyList;
			if (is_array($tmpProxy) && count($tmpProxy)) {
				$proxy['content'] = array_merge($proxy['content'], $tmpProxy['content']);
			}
		}
		return $proxy;
	}

	public function testDownloadProxy($moduleName) {
		foreach ($this->_fileUrlProxyList as $valueProxyList) {
			if (preg_match('#' . preg_quote($moduleName, '#') . '#ims', $valueProxyList)) {
				$tmp_proxy = require $valueProxyList;
				return $tmp_proxy;
			}
		}
	}

	/**
	 * Устанавливает фильтр для необходимых прокси
	 * @param string $val протокол через который работает прокси
	 * @return string|bool имя протокола
	 */
	private function setNameTypeProxy($val = "http") {
		switch ($val) {
			case 'http':
				return 'http';
			case 'https':
				return 'https';
			case 'socks':
				return 'socks';
			default:
				return false;
		}
	}

	/**
	 * @param string $val тип получения прокси адреса
	 * @return bool
	 */
	public function setMethodGetProxy($val = "random") {
		switch ($val) {
			case 'random':
				$this->_methodGetProxy = 'random';
				return true;
				break;
			case 'rent':
				$this->_methodGetProxy = 'rent';
				return true;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Установка фильтра на анонимность прокси
	 * @param bool $val флаг для фильрации функций прокси
	 */
	public function setNeedAnonimProxy($val = true) {
		$this->_needAnonimProxy = $val;
	}

	/**
	 * Установка флага на проверку прокси перед использованием
	 * @param bool $val
	 */
	public function setNeedCheckProxy($val = true) {
		$this->_needCheckProxy = $val;
	}

	public function getFileUrlProxyList() {
		return $this->_fileUrlProxyList;
	}

	/**
	 * Открывает прокси лист
	 */
	public function openProxyList() {
		$this->closeProxyList();
		$this->_fHeandleProxyList = fopen($this->_fileProxyList, "c+");
	}

	/**
	 * Закрывает текущий прокси лист
	 */
	public function closeProxyList() {
		$this->freeProxyList();
		if (isset($this->_fHeandleProxyList) && is_resource($this->_fHeandleProxyList)) {
			fclose($this->_fHeandleProxyList);
			unset($this->_fHeandleProxyList);
		}
		unset($this->_proxyList);
	}

	/**
	 * Освобождает прокси лист от блокировки текущим процессом
	 * @return bool
	 */
	public function freeProxyList() {
		if (!$this->getAccessToProxyList()) return true; // проверяет занят ли этим потоком файл?
		if (is_resource($this->_fHeandleProxyList)) {
			fflush($this->_fHeandleProxyList);
			flock($this->_fHeandleProxyList, LOCK_UN);
			$this->setAccessToProxyList(0);
		}
		return false;
	}

	/**
	 * Блокирует прокси лист от остальных потоков
	 * @return bool
	 */
	public function blocProxyList() {
		if ($this->getAccessToProxyList()) return true; // проверяет не блокирован ли этим потоком файл?
		do {
			if (is_resource($this->_fHeandleProxyList)) {
				if (flock($this->_fHeandleProxyList, LOCK_EX)) {
					$this->setAccessToProxyList(1);
					return true;
				}
			}
			// Прокси лист занят
			sleep(5);
		} while (true);
		return false;
	}

	/**
	 * Возвращает случайный прокси из текцщего списка
	 * @return bool|string
	 */
	public function getRandomProxy() {
		$proxyList = $this->getList();
		$countCheck = 10;
		$countProxy = count($proxyList['content']);
		if (is_array($proxyList['content'])) {
			if ($countProxy < $countCheck) $countCheck = $countProxy;
		} else return false;
		for ($i = 0; $i < $countProxy; $i += $countCheck) {
			$proxy = array();
			for ($j = 0; $j < $countCheck; $j++) {
				$proxy[$j]['proxy'] = trim($proxyList['content'][array_rand($proxyList['content'])]["proxy"]);
			}
			if ($goodProxy = $this->checkProxyArray($proxy)) {
				if (is_array($goodProxy)) {
					$tmpProxy = current($goodProxy);
					return $tmpProxy['proxy'];
				} else {
					return false;
				}
			}
		}
		return false;
	}

	/**
	 * Открывает текущий прокси лист с блокировкой
	 * @return array прокси лист
	 */
	public function getProxyListInFile() {
		while (true) {
			rewind($this->_fHeandleProxyList);
			clearstatcache(true, $this->_fileProxyList);
			$jsonProxy = fread($this->_fHeandleProxyList, filesize($this->_fileProxyList));
			if (strlen($jsonProxy) == filesize($this->_fileProxyList)) {
				$this->_proxyList = json_decode($jsonProxy, true);
				if ($this->_proxyList) {
					if (!is_array($this->_proxyList['content'])) {
						$this->_proxyList['content'] = array();
					}
					break;
				}
			} else {
				sleep(1); // Прокси лист занят
			}
		}
		return $this->_proxyList;
	}

	/**
	 * Выдает потоку прокси адрес
	 * @param string $rentCode    код потока арендатора
	 * @param string $siteForUse сайт на который будут посылать запросы
	 * @return bool|string
	 */
	public function getProxy($rentCode = "", $siteForUse = "") {
		switch ($this->_methodGetProxy) {
			case 'random':
				$this->_lastUseProxy = $this->getRandomProxy();
				return $this->_lastUseProxy;
				break;
			case 'rent':
				if ($rentCode == "" || $siteForUse == "") return false;
				$this->_lastUseProxy = $this->getRentedProxy($rentCode, $siteForUse);
				return $this->_lastUseProxy;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Добавляет в текущий список новый прокси адрес
	 * @param string $proxy        адрес прокси сервера
	 * @param string $typeProxy   протокол прокси
	 * @param string $sourceProxy источник прокси
	 * @return bool
	 */
	public function addProxy($proxy, $typeProxy = "http", $sourceProxy = "") {
		if (!$result = $this->searchProxyInList($proxy)) {
			$this->blocProxyList();
			$tmpArray['proxy'] = trim($proxy);
			$tmpArray["source_proxy"] = $sourceProxy;
			$tmpArray["type_proxy"] = $typeProxy;
			$this->_proxyList['content'][] = $tmpArray;
			$this->saveProxyList($this->_proxyList);
			return true;
		}
		return false;
	}

	/**
	 * Получить в аренду прокси адрес
	 * @param      $rentCode    код арендатора
	 * @param      $siteForUse сайт на который будут поступать запросы
	 * @param bool $keyAddress  адрес для быстрого поиска прокси для снятия аренды или удаления
	 * @return bool|string
	 */
	public function getRentedProxy($rentCode, $siteForUse, $keyAddress = false) {
		for ($i = 0; $i < 100; $i++) {
			$this->blocProxyList();
			$this->_proxyList = $this->getProxyListInFile();
			if ($ipProxy = $this->searchRentalAddress($rentCode, $siteForUse, $keyAddress)) return $ipProxy["proxy"];
			if ($ipProxy = $this->setRentedProxy($rentCode, $siteForUse)) {
				//сюда нужно поместить сохранение адреса быстрого доступа к данным о аренде прокси
				$this->saveProxyList($this->_proxyList);
				return $ipProxy["proxy"];
			}
			// все прокси заняты, записываем изменения и освобождаем файл. ждем когда освободится
			$this->saveProxyList($this->_proxyList);
			sleep(60);
		}
		return false;
	}

	/**
	 * Поиск прокси по коду арендатора
	 * @param string     $rentCode    код арендатора
	 * @param string     $siteForUse сайт на который отправляют запросы черз прокси
	 * @param bool|array $keyAddress  адрес для быстрого доступа
	 * @return bool|string
	 */
	public function searchRentalAddress($rentCode, $siteForUse, $keyAddress = false) {
		//$this->_proxyList=$this->getProxyListInFile();
		// если задан адрес в где лежит информация об аренде, проверяем информацию
		if ($keyAddress) {
			if (isset($this->_proxyList['content'][$keyAddress['key_content']]["renters"][$keyAddress['key_renters']]["renter_code"])
				&& $this->_proxyList['content'][$keyAddress['key_content']]["renters"][$keyAddress['key_renters']]["renter_code"] == $rentCode
				&& $this->_proxyList['content'][$keyAddress['key_content']]["renters"][$keyAddress['key_renters']]["user_site"] == $siteForUse
			) {
				$endTermRent = time() - $this->_rentTime;
				// проверяем время аренды прокси
				if ($keyAddress["start_rent"] > $endTermRent) return $this->_proxyList['content'][$keyAddress['key_content']]["proxy"];
				else {
					$this->removeRent($keyAddress['key_content'], $keyAddress['key_renters']);
					return false;
				}
			}
		}
		$endTermRent = time() - $this->_rentTime;
		// Если нет , то ищем в ручную
		foreach ($this->_proxyList['content'] as $keyContent => $valueContent) {
			if (!isset($valueContent["renters"])) continue;
			foreach ($valueContent["renters"] as $keyRenters => $valueRenters) {
				if ($valueRenters["renter_code"] == $rentCode) {
					// проверяем время аренды прокси
					if ($valueRenters["start_rent"] > $endTermRent) {
						$returnArray["proxy"] = $valueContent["proxy"];
						$returnArray["key_content"] = $keyContent;
						$returnArray["key_renters"] = $keyRenters;
						return $returnArray;
					} else {
						$this->removeRent($keyContent, $keyRenters);
						return false;
					}
				}
			}
			unset($valueRenters);
		}
		unset($valueContent);
		return false;
	}

	/**
	 * Поиск адрес прокси в текущем списке
	 * @param string $proxy адрес прокси
	 * @return array|bool
	 */
	public function searchProxyInList($proxy) {
		$this->_proxyList = $this->getProxyListInFile();
		foreach ($this->_proxyList['content'] as $keyContent => $valueContent) {
			if ($valueContent["proxy"] == $proxy) {
				$returnArray['proxy'] = $valueContent["proxy"];
				$returnArray['key_content'] = $keyContent;
				return $returnArray;
			}
		}
		unset($valueContent);
		return false;
	}

	/**
	 * Ставит пометку в списке прокси что этот прокси арендован
	 * @param        $rentCode    код арендатора
	 * @param        $siteForUse сайт на который будут посылать запросы через прокси
	 * @param string $proxy        прокси адрес
	 * @return array|bool
	 */
	protected function setRentedProxy($rentCode, $siteForUse, $proxy = "") {
		if ($proxy) {
			$result = $this->searchProxyInList($proxy);
			$tmpData['start_rent'] = time();
			$tmpData['renter_code'] = $rentCode;
			$tmpData['user_site'] = $siteForUse;
			$this->_proxyList['content'][$result['key_content']]["renters"][] = $tmpData;
			return true;
		} else {
			foreach ($this->_proxyList['content'] as $keyContent => $valueContent) {
				$proxyUseThisSite = 0;
				if (isset($valueContent["renters"])) {
					foreach ($valueContent["renters"] as $valueRenters) {
						if ($valueRenters["user_site"] == $siteForUse) {
							$proxyUseThisSite = 1;
							break;
						}
					}
					unset($valueRenters);
				}
				//Если через этот прокси не опрашивается сайт $site_for_use, то привяжем поток к этому прокси
				if (!$proxyUseThisSite) {
					$tmpData['start_rent'] = time();
					$tmpData['renter_code'] = $rentCode;
					$tmpData['user_site'] = $siteForUse;
					$this->_proxyList['content'][$keyContent]["renters"][] = $tmpData;
					end($this->_proxyList['content'][$keyContent]["renters"]);
					$returnArray["proxy"] = $valueContent["proxy"];
					$returnArray["key_content"] = $keyContent;
					$returnArray["key_renters"] = key($this->_proxyList['content'][$keyContent]["renters"]);
					return $returnArray;
				}
			}
			unset($valueContent);
		}
		return false;
	}

	/**
	 * Удаляет аренды с всех прокси для текущего арендатора
	 * @param string $rentCode код арендатора
	 * @return bool
	 */
	public function removeAllRentFromCode($rentCode) {
		$this->blocProxyList();
		$this->_proxyList = $this->getProxyListInFile();
		if (!isset($this->_proxyList['content'])) {
			$this->freeProxyList();
			return false;
		}
		if (isset($this->_proxyList['content']) && is_array($this->_proxyList['content'])) {
			foreach ($this->_proxyList['content'] as $keyContent => $valueContent) {
				if (!isset($valueContent["renters"]) || !is_array($valueContent["renters"])) continue;
				foreach ($valueContent["renters"] as $keyRenters => $valueRenters) {
					if ($valueRenters["renter_code"] == $rentCode) {
						$this->removeRent($keyContent, $keyRenters, 1);
					}
				}
				unset($valueRenters);
			}
			unset($valueContent);
			$this->saveProxyList($this->_proxyList);
		}
		return true;
	}

	/**
	 * Убирает все аренды из текущего списка прокси
	 */
	public function removeAllRent() {
		$this->blocProxyList();
		$this->_proxyList = $this->getProxyListInFile();
		if (isset($this->_proxyList['content']) && is_array($this->_proxyList['content'])) {
			foreach ($this->_proxyList['content'] as $keyContent => $valueContent) {
				if (!isset($valueContent["renters"]) || !is_array($valueContent["renters"])) continue;
				foreach ($valueContent["renters"] as $keyRenters => $valueRenters) {
					if ($valueRenters["renter_code"]) {
						$this->removeRent($keyContent, $keyRenters, 1);
					}
				}
				unset($valueRenters);
			}
		}
		unset($valueContent);
		$this->saveProxyList($this->_proxyList);
	}

	/**
	 * Удаляет и списка прокси аренду по ключу в списке и коду арендатора
	 * @param int  $keyContent    ключ в списке
	 * @param int  $keyRenters    ключ арендатора в списке
	 * @param bool $withoutSaving с сохранением в файл
	 * @return bool
	 */
	public function removeRent($keyContent, $keyRenters, $withoutSaving = false) {
		$this->blocProxyList();
		if (isset($this->_proxyList['content'][$keyContent]["renters"][$keyRenters])) {
			unset($this->_proxyList['content'][$keyContent]["renters"][$keyRenters]);
			if (!$withoutSaving) $this->saveProxyList($this->_proxyList);
			else $this->freeProxyList();
			return true;
		} else {
			$this->freeProxyList();
			return false;
		}
	}

	/**
	 * Убирает аренду по коду арендатора и сайту на который посылают запрос
	 * @param $rentCode    код арендатора
	 * @param $siteForUse сайт на который посылают запросы
	 * @return bool
	 */
	public function removeRentToCodeSite($rentCode, $siteForUse) {
		if ($resultArray = $this->searchRentalAddress($rentCode, $siteForUse)) {
			$this->removeRent($resultArray['key_content'], $resultArray['key_renters']);
			return true;
		} else return false;
	}

	/**
	 * Удаляет прокси из текущего списка
	 * @param string $proxy прокси адрес
	 * @return bool
	 */
	public function removeProxyInList($proxy) {
		if ($this->_removeProxy) {
			$this->blocProxyList();
			$this->_proxyList = $this->getProxyListInFile();
			foreach ($this->_proxyList['content'] as $keyContent => $valueContent) {
				if ($this->_proxyList['content'][$keyContent]['proxy'] == $proxy) {
					unset($this->_proxyList['content'][$keyContent]);
					break;
				}
			}
			unset($valueContent);
			$this->saveProxyList($this->_proxyList);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return array|bool
	 */
	public function getProxyList() {
		if (isset($this->_proxyList) && count($this->_proxyList) && ($this->_proxyList['time'] > (time() - 3600))) return $this->_proxyList;
		if (!$proxy = $this->getProxyListInFile()) {
			return false;
		}
		return $this->_proxyList = $proxy;
	}

	public function getLastUseProxy() {
		return $this->_lastUseProxy;
	}

	public function loadProxy($url){
		$proxy = file_get_contents($url);
		$this->setListType('static');
		$this->setMethodGetProxy('random');
		$this->_proxyList = array();
		if(preg_match_all('#(?<proxy>(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(:{1}\d{1,10})))#ims',$proxy,$matches)){
			foreach($matches['proxy'] as $findProxy){
				$this->_proxyList['content'][] = array('proxy' => $findProxy);
			}
		}
	}

	/**
	 * Проверяет прокси адрес на работоспособность и на поддерживаетмые функции
	 * @param string $proxy прокси адрес
	 * @return array|int|string
	 */
	public function checkProxy($proxy) {
		if (!$this->_needCheckProxy) return $proxy;
		if (is_string($proxy) && cStringWork::isIp($proxy)) {
			$this->_getContent->setModeGetContent('single');
			$this->_getContent->setUseProxy($proxy);
			$this->_getContent->setMinSizeAnswer(5);
			$this->_getContent->setDefaultSetting(CURLOPT_REFERER, "proxy-check.net");
			$this->_getContent->setDefaultSetting(CURLOPT_POST, true);
			$this->_getContent->setDefaultSetting(CURLOPT_POSTFIELDS, "proxy=yandex");
			$this->_getContent->setTypeContent('text');
			$this->_getContent->setDefaultSetting(CURLOPT_HEADER, false);
			$this->_getContent->setCheckAnswer(false);
			$answer = $this->_getContent->getContent($this->getProxyChecker() . '?ip=' . $this->getServerIp() . '&proxy=yandex');
			$descriptor = $this->_getContent->getDescriptor();
			$this->_getContent->restoreDefaultSettings();
			$infoProxy = $this->genProxyInfo($proxy, $answer, $descriptor['info']);
			if ($infoProxy) {
				$goodProxy[] = $infoProxy;
			}
		}
		return false;
	}

	/**
	 * Проверяет массив прокси адресов на работоспособность и на поддерживаетмые функции
	 * @param array $arrayProxy массив прокси адресов
	 * @return array|bool
	 */
	private function checkProxyArray($arrayProxy) {
		if (is_array($arrayProxy)) {
			if (!$this->_needCheckProxy) return $arrayProxy;
			$goodProxy = array();
			$url = $this->getProxyChecker() . '?ip=' . $this->getServerIp() . '&proxy=yandex';
			$this->_getContent->setModeGetContent('multi');
			$this->_getContent->setCountMultiStream(1);
			$this->_getContent->setMinSizeAnswer(5);
			$this->_getContent->setMaxNumberRepeat(0);
			$this->_getContent->setDefaultSetting(CURLOPT_REFERER, "proxy-check.net");
			$this->_getContent->setDefaultSetting(CURLOPT_POST, true);
			$this->_getContent->setDefaultSetting(CURLOPT_POSTFIELDS, "proxy=yandex");
			$this->_getContent->setTypeContent('text');
			//$this->_getContent->setDefaultSetting(CURLOPT_HEADER, false);
			$this->_getContent->setCheckAnswer(false);
			foreach (array_chunk($arrayProxy, 200) as $valueArrayProxy) {
				$this->_getContent->setCountMultiCurl(count($valueArrayProxy));
				$urlArray = array();
				$descriptorArray =& $this->_getContent->getDescriptorArray();
				foreach ($descriptorArray as $key => $value) {
					$this->_getContent->setOptionToDescriptor($descriptorArray[$key], CURLOPT_PROXY, $valueArrayProxy[$key]['proxy']);
					$urlArray[] = $url;
				}
				$answerContent = $this->_getContent->getContent($urlArray);
				foreach ($answerContent as $key => $value) {
					$infoProxy = $this->genProxyInfo($valueArrayProxy[$key], $value, $descriptorArray[$key]['info']);
					if ($infoProxy) {
						$goodProxy[] = $infoProxy;
					}
				}
			}
			$this->_getContent->restoreDefaultSettings();
			if (count($goodProxy)) return $goodProxy;
		}
		return false;
	}

	/**
	 * Проверка доступности сайта через список прокси, отсеевает не рабочие прокси
	 * @param array  $arrayProxy тестовый список прокси
	 * @param string $url         ссылка на страницу проверки
	 * @param array  $checkWord  проверочные регулярные выражения
	 * @return array|bool
	 */
	private function checkProxyArrayToSite($arrayProxy, $url, $checkWord) {
		if (!is_array($arrayProxy)) return false;
		$goodProxy = array();
		$this->_getContent->setModeGetContent('multi');
		$this->_getContent->setCountMultiStream(1);
		$this->_getContent->setTypeContent('text');
		$this->_getContent->setDefaultSetting(CURLOPT_HEADER, false);
		$this->_getContent->setDefaultSetting(CURLOPT_POST, false);
		$this->_getContent->setCheckAnswer(false);
		foreach (array_chunk($arrayProxy, 100) as $valueProxy) {
			$this->_getContent->setCountMultiCurl(count($valueProxy));
			$descriptorArray =& $this->_getContent->getDescriptorArray();
			$urlArray = array();
			foreach ($descriptorArray as $key => $value) {
				$this->_getContent->setOptionToDescriptor($descriptorArray[$key], CURLOPT_PROXY, $valueProxy[$key]['proxy']);
				$urlArray[] = $url;
			}
			$answerContent = $this->_getContent->getContent($urlArray);
			foreach ($answerContent as $key => $value) {
				$testCount = 0;
				$countGoodCheck = 0;
				foreach ($checkWord as $valueCheckWord) {
					$testCount++;
					if (preg_match($valueCheckWord, $value)) $countGoodCheck++;
				}
				unset($valueCheckWord);
				if ($countGoodCheck == $testCount) $goodProxy[] = $valueProxy[$key];
			}
			unset($value);
		}
		$this->_getContent->restoreDefaultSettings();
		if (count($goodProxy)) return $goodProxy;
		else return false;
	}

	/**
	 * Возвращает адреса прокси поддерживающие выбранные функции
	 * @param array $proxyList Список прокси
	 * @param array $funArray  перечень необходимых функций anonym|referer|post|get|cookie
	 * @return array|bool
	 */
	public function getProxyByFunction($proxyList, $funArray = array()) {
		if (!is_array($proxyList)) return false;
		$goodProxy = array();
		foreach ($proxyList as $challenger) {
			$approach = false;
			if (count($funArray)) {
				foreach ($funArray as $nameFunction => $valueFunction) {
					if (in_array($nameFunction, $this->_proxyFunction) && $challenger[$nameFunction] >= $valueFunction) {
						if ($nameFunction == 'country') {
							if ($valueFunction === $challenger[$nameFunction]) {
								$approach = true;
							} else {
								$approach = false;
								break;
							}
						} else {
							$approach = true;
						}
					} else {
						$approach = false;
						break;
					}
				}
			} else {
				$approach = true;
			}
			if ($approach) {
				$goodProxy[] = $challenger;
			}
		}
		if (count($goodProxy)) return $goodProxy;
		return false;
	}

	/**
	 * Генерация информации о прокси для хранения в листе активных прокси
	 * @param array|string $proxy
	 * @param string       $answer
	 * @param null|array   $curlInfo
	 * @return array|bool
	 */
	private function genProxyInfo($proxy, $answer, $curlInfo = null) {
		if (preg_match('#^[01]{5}#', $answer) && preg_match_all('#(?<fun_status>[01])#U', $answer, $matches)) {
			if (is_string($proxy)) {
				$infoProxy['proxy'] = $proxy;
				$infoProxy['source_proxy'] = '';
				$infoProxy['type_proxy'] = '';
			} else {
				$infoProxy['proxy'] = $proxy['proxy'];
				$infoProxy['source_proxy'] = $proxy['source_proxy'];
				$infoProxy['type_proxy'] = $proxy['type_proxy'];
			}
			$infoProxy['anonym'] = (int)$matches['fun_status'][0];
			$infoProxy['referer'] = (int)$matches['fun_status'][1];
			$infoProxy['post'] = (int)$matches['fun_status'][2];
			$infoProxy['get'] = (int)$matches['fun_status'][3];
			$infoProxy['cookie'] = (int)$matches['fun_status'][4];
			$infoProxy['last_check'] = time();
			if (preg_match('%(?<ip>\d+\.\d+\.\d+.\d+)\:\d+%ims', $infoProxy['proxy'], $match)) {
				$countryName = @geoip_country_name_by_name($match['ip']);
				$infoProxy['country'] = $countryName ? $countryName : 'no country';
			} else {
				$infoProxy['country'] = 'no country';
			}
			if ($curlInfo) {
				$infoProxy['starttransfer'] = $curlInfo['starttransfer_time'];
				$infoProxy['upload'] = $curlInfo['speed_upload'];
				$infoProxy['download'] = $curlInfo['speed_download'];
			} else {

			}
			return $infoProxy;
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет прокси список
	 * @param array|bool $proxyList сохраняемый список прокси
	 */
	public function saveProxyList($proxyList = false) {
		if (!is_array($proxyList)) $proxyList = $this->_proxyList;
		$proxyList['time'] = time();
		$this->_proxyList = $proxyList;
		$jsonProxy = json_encode($proxyList);
		$this->blocProxyList();
		file_put_contents($this->_fileProxyList, '');
		rewind($this->_fHeandleProxyList);
		fwrite($this->_fHeandleProxyList, $jsonProxy);
		$this->freeProxyList();
	}

	/**
	 * Создает профиль прокси адресов
	 * @param string $nameList           название
	 * @param string $checkUrl           проверочный URL
	 * @param array  $checkWordArray    Проверочные регулярные выражения
	 * @param array  $needFunctionArray Перечень поддерживаемых функций
	 * @param bool   $needUpdate
	 */
	public function createProxyList($nameList, $checkUrl = "http://ya.ru", $checkWordArray = array("#yandex#iUm"), $needFunctionArray = array(), $needUpdate = false) {
		$this->closeProxyList();
		$this->_nameList = $nameList;
		if (file_exists($this->getDirProxyListFile() . "/" . $nameList . ".proxy")) $this->deleteProxyList($nameList);
		$this->_fileProxyList = $this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy";
		$this->openProxyList();
		$proxyList['content'] = array();
		$proxyList['url'] = $checkUrl;
		$proxyList['check_word'] = $checkWordArray;
		$proxyList['need_function'] = $needFunctionArray;
		$proxyList['name_list'] = $nameList;
		$proxyList['need_update'] = $needUpdate;
		$this->createProxyListBuk($proxyList);
		$this->saveProxyList($proxyList);
	}

	/**
	 * Создает резервную копию текущего профиля
	 * @param $proxyList список прокси
	 */
	protected function createProxyListBuk($proxyList) {
		$jsonProxy = json_encode($proxyList);
		$bukFile = $this->_fileProxyList . time() . ".buk";
		$fh = fopen($bukFile, "c+");
		file_put_contents($bukFile, '');
		rewind($fh);
		fwrite($fh, $jsonProxy);
		fclose($fh);
	}

	/**
	 * Удаляет прокси лист
	 * @param $nameList имя прокси листа
	 */
	public function deleteProxyList($nameList) {
		if ($nameList == $this->_nameList) $this->closeProxyList();
		if (file_exists($this->getDirProxyListFile() . "/" . $nameList . ".proxy")) {
			unlink($this->getDirProxyListFile() . "/" . $nameList . ".proxy");
		}
	}

	/**
	 * Очищает прокси лист от прокси, но оставляет конфигурацию необходимых функций
	 * @param $nameList имя прокси листа
	 */
	public function clearProxyList($nameList) {
		$this->selectProxyList($nameList);
		$this->_proxyList['content'] = array();
		$this->saveProxyList($this->_proxyList);
	}

	/**
	 * Включает регулярное обновление прокси списка или выключает
	 * @param      $nameList имя прокси списка
	 * @param bool $value     вкл./выкл.
	 */
	public function setUpdateProxyList($nameList, $value = true) {
		$this->selectProxyList($nameList);
		$this->_proxyList['need_update'] = $value;
		$this->saveProxyList($this->_proxyList);
	}

	/**
	 * Генерация списка прокси адресов собраных из разных источников в один список с уникальными адресами
	 * @param array $proxyArray список прокси
	 * @return array
	 */
	private function getUniqueProxyIp($proxyArray) {
		$ipProxy = array();
		foreach ($proxyArray as $key => $value){
			$ipProxy[$key] = $proxyArray[$key]["proxy"];
		}
		$proxyArrayCopy = $proxyArray;
		unset($value);
		$ipProxy = array_unique($ipProxy);
		$proxyArray = array();
		foreach ($ipProxy as $key => $value){
			$proxyArray[$key] = $proxyArrayCopy[$key];
		}
		unset($value);
		$proxyArray = array_values($proxyArray);
		return $proxyArray;
	}

	/**
	 * Обновляет прокси лист
	 * @param string $nameList имя прокси листа
	 * @param bool   $force     Принудительное обновление
	 * @return array обновленный список прокси
	 */
	public function updateProxyList($nameList, $force = false) {
		if ($nameList == $this->getDefaultListName()) {
			return $this->selectProxyList($this->getDefaultListName());
		} else {
			$allProxy = $this->selectProxyList($this->getDefaultListName());
			$this->selectProxyList($nameList);
		}
		$this->freeProxyList();
		$endTermProxy = time() - $this->_storageTime;
		if (
			(
				isset($this->_proxyList)
				&& is_array($this->_proxyList)
				&& isset($this->_proxyList['content'])
				&& count($this->_proxyList['content'])
				&& $this->_proxyList['time'] > $endTermProxy
				&& !$force
			)
			|| (!isset($this->_proxyList['need_update']) || !$this->_proxyList['need_update'])
		) {
			return $this->_proxyList;
		}
		$this->_proxyList['content'] = $this->getProxyByFunction($allProxy['content'], $this->_proxyList['need_function']);
		$this->_proxyList['content'] = $this->checkProxyArrayToSite($this->_proxyList['content'], $this->_proxyList['url'], $this->_proxyList['check_word']);
		$this->saveProxyList($this->_proxyList);
		return $this->_proxyList;
	}

	/**
	 * Обновляет основной список прокси в котором хранятся адреса всех прокси
	 * Из него берут информацию другие профили
	 * @param bool $force принудительное обновление
	 * @return array
	 */
	public function updateDefaultProxyList($force = false) {
		$this->selectProxyList($this->getDefaultListName());
		$endTermProxy = time() - $this->_storageTime;
		if (
			$this->_proxyList
			&& isset($this->_proxyList['content'])
			&& is_array($this->_proxyList['content'])
			&& count($this->_proxyList['content'])
			&& $this->_proxyList['time']
			> $endTermProxy
			&& !$force
		) {
			return $this->_proxyList;
		}
		$proxyList = $this->downloadProxy();
		$archive = $this->getArchiveProxy();
		$archiveProxyList = array();
		$tmp['source_proxy'] = 'archive';
		$tmp['type_proxy'] = 'http';
		foreach ($archive as $proxy) {
			$tmp['proxy'] = $proxy;
			$archiveProxyList[] = $tmp;
		}
		$newProxy = array();
		foreach ($proxyList['content'] as $proxy) {
			$newProxy[] = $proxy['proxy'];
		}
		$this->saveInArchive($newProxy);

		$oldProxy['content'] = array_merge($proxyList['content'], $archiveProxyList);
		$oldProxy['content'] = $this->getUniqueProxyIp($oldProxy['content']);
		$oldProxy['content'] = $this->checkProxyArray($oldProxy['content']);
		$this->_proxyList = $oldProxy;
		$this->saveProxyList($this->_proxyList);
		return $this->_proxyList;
	}

	/**
	 * Обновление всех прокси листов кроме основного
	 * @param $force принудительное обновление
	 */
	public function updateAllProxyList($force = false) {
		$this->updateDefaultProxyList($force);
		foreach ($this->getAllNameProxyList() as $value) {
			$this->updateProxyList($value, $force);
		}
	}

	/**
	 * Изменяет настройки прокси листа
	 * @param        $nameList
	 * @param string $checkUrl
	 * @param array  $checkWordArray
	 * @param array  $needFunctionArray
	 * @param bool   $needUpdate
	 */
	public function configProxyList($nameList, $checkUrl = "http://bpteam.net", $checkWordArray = array("#\+380632359213#ims"), $needFunctionArray = array(), $needUpdate = false) {
		$this->selectProxyList($nameList);
		$this->_proxyList['url'] = $checkUrl;
		$this->_proxyList['check_word'] = $checkWordArray;
		$this->_proxyList['need_function'] = $needFunctionArray;
		$this->_proxyList['name_list'] = $nameList;
		$this->_proxyList['need_update'] = $needUpdate;
		$this->createProxyListBuk($this->_proxyList);
		$this->saveProxyList($this->_proxyList);
	}


	/**
	 * Выбор прокси листа
	 * @param string $nameList имя прокси листа
	 * @return array выбранный прокси лист
	 */
	public function selectProxyList($nameList) {
		$this->closeProxyList();
		$this->setListType('dynamic');
		$this->_nameList = $nameList;
		if ($this->proxyListExist($nameList)) {
			$this->_fileProxyList = $this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy";
		} else {
			$this->_fileProxyList = $this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy";
			$this->createProxyList($nameList);
		}
		$this->openProxyList();
		$this->_proxyList = $this->getProxyListInFile();
		$this->freeProxyList();
		return $this->_proxyList;
	}

	/**
	 * Возвращает имена всех профилей прокси списков
	 * @return array перечень имен списков прокси
	 * @return array
	 */
	public function getAllNameProxyList() {
		$fileList = glob($this->getDirProxyListFile() . "/" . "*.proxy");
		$proxyListArray = array();
		foreach ($fileList as $value) {
			if (preg_match("#/(?<name_list>[^/]+)\.proxy$#iUm", $value, $match)) {
				$proxyListArray[] = $match['name_list'];
			}
		}
		return $proxyListArray;
	}

	/**
	 * Проверяет существует ли прокси лист
	 * @param string $nameList имя прокси листа
	 * @return bool
	 */
	public function proxyListExist($nameList) {
		$allList = $this->getAllNameProxyList();
		if (array_search($nameList, $allList) !== false) {
			return true;
		} else return false;
	}
}