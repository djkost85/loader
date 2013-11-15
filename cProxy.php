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
	protected $listType;

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
		$this->dir_url_proxy_list = "proxy_site_module";
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
	 * @param bool $new_remove_proxy
	 */
	public function setRemoveProxy($new_remove_proxy) {
		$this->_removeProxy = $new_remove_proxy;
	}

	/**
	 * @param bool $new_access_to_proxy_list
	 */
	public function setAccessToProxyList($new_access_to_proxy_list) {
		$this->_accessToProxyList = $new_access_to_proxy_list;
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
		return $this->listType;
	}

	public function setListType($val){
		$this->listType = $val;
	}

	public function getList(){
		switch($this->getListType()){
			case 'dynamic':
				$proxy_list = $this->getProxyListInFile();
				$this->freeProxyList();
				break;
			case 'static':
				$proxy_list = $this->_proxyList;
				break;
			default:
				$proxy_list = $this->_proxyList;
		}
		return $proxy_list;
	}

	/**
	 * Получение абсолютного адреса к папке гда лежат файлы конфигурации прокси листов
	 * @return string
	 */
	public function getDirProxyListFile() {
		return $this->getDirProxyFile() . "/" . $this->_dirProxyListFile;
	}

	public function getDirUrlProxyList() {
		return $this->getDirProxyFile() . "/" . $this->dir_url_proxy_list;
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
			if (preg_match($reg, $answer, $match) && !isset($match['ip']) || !$match['ip'] || !cStringWork::isIp($match['ip'])) return false;
			$this->_serverIp = $match['ip'];
		}
		return $this->_serverIp;
	}

	/**
	 * @param bool $new_need_proxy_cookie
	 */
	public function setNeedProxyCookie($new_need_proxy_cookie) {
		$this->_needProxyCookie = $new_need_proxy_cookie;
	}

	/**
	 * Поиск сервера из каталога для проверки функций прокси
	 * @param string $check_url_proxy url сервера для проверки функций прокси, если не работает выберает другой из каталога
	 * @return string возвращает рабочий url для проверки прокси
	 */
	public function getProxyChecker($check_url_proxy = "") {
		if ($check_url_proxy === "") $check_url_proxy = $this->_checkUrlProxy;
		$this->_getContent->setUseProxy(false);
		$this->_getContent->setTypeContent('text');
		$this->_getContent->setModeGetContent('multi');
		$this->_getContent->setCountMultiStream(1);
		$this->_getContent->setMinSizeAnswer(5);
		$answer = $this->_getContent->getContent($check_url_proxy);
		foreach ($answer as $key => $value) {
			if (preg_match("/^[01]{5}/i", $value)) {
				return $this->_checkUrlProxy[$key];
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
		foreach ($this->_fileUrlProxyList as $value_proxy_list) {
			$tmp_proxy = require $value_proxy_list;
			if (is_array($tmp_proxy) && count($tmp_proxy)) {
				$proxy['content'] = array_merge($proxy['content'], $tmp_proxy['content']);
			}
		}
		return $proxy;
	}

	public function testDownloadProxy($module_name) {
		foreach ($this->_fileUrlProxyList as $value_proxy_list) {
			if (preg_match('#' . preg_quote($module_name, '#') . '#ims', $value_proxy_list)) {
				$tmp_proxy = require $value_proxy_list;
				return $tmp_proxy;
			}
		}
	}

	/**
	 * Устанавливает фильтр для необходимых прокси
	 * @param string $new_name_type_proxy протокол через который работает прокси
	 * @return string|bool имя протокола
	 */
	private function setNameTypeProxy($new_name_type_proxy = "http") {
		switch ($new_name_type_proxy) {
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
	 * @param string $new_method_get_proxy тип получения прокси адреса
	 * @return bool
	 */
	public function setMethodGetProxy($new_method_get_proxy = "random") {
		switch ($new_method_get_proxy) {
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
	 * @param bool $new_need_anonim_proxy флаг для фильрации функций прокси
	 */
	public function setNeedAnonimProxy($new_need_anonim_proxy = true) {
		$this->_needAnonimProxy = $new_need_anonim_proxy;
	}

	/**
	 * Установка флага на проверку прокси перед использованием
	 * @param bool $new_need_check_proxy
	 */
	public function setNeedCheckProxy($new_need_check_proxy = true) {
		$this->_needCheckProxy = $new_need_check_proxy;
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
		$proxy_list = $this->getList();
		$count_check = 10;
		$count_proxy = count($proxy_list['content']);
		if (is_array($proxy_list['content'])) {
			if ($count_proxy < $count_check) $count_check = $count_proxy;
		} else return false;
		for ($i = 0; $i < $count_proxy; $i += $count_check) {
			$proxy = array();
			for ($j = 0; $j < $count_check; $j++) {
				$proxy[$j]['proxy'] = trim($proxy_list['content'][array_rand($proxy_list['content'])]["proxy"]);
			}
			if ($good_proxy = $this->checkProxyArray($proxy)) {
				if (is_array($good_proxy)) {
					$tmp_proxy = current($good_proxy);
					return $tmp_proxy['proxy'];
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
			$json_proxy = fread($this->_fHeandleProxyList, filesize($this->_fileProxyList));
			if (strlen($json_proxy) == filesize($this->_fileProxyList)) {
				$this->_proxyList = json_decode($json_proxy, true);
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
	 * @param string $rent_code    код потока арендатора
	 * @param string $site_for_use сайт на который будут посылать запросы
	 * @return bool|string
	 */
	public function getProxy($rent_code = "", $site_for_use = "") {
		switch ($this->_methodGetProxy) {
			case 'random':
				$this->_lastUseProxy = $this->getRandomProxy();
				return $this->_lastUseProxy;
				break;
			case 'rent':
				if ($rent_code == "" || $site_for_use == "") return false;
				$this->_lastUseProxy = $this->getRentedProxy($rent_code, $site_for_use);
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
	 * @param string $type_proxy   протокол прокси
	 * @param string $source_proxy источник прокси
	 * @return bool
	 */
	public function addProxy($proxy, $type_proxy = "http", $source_proxy = "") {
		if (!$result = $this->searchProxyInList($proxy)) {
			$this->blocProxyList();
			$tmp_array['proxy'] = trim($proxy);
			$tmp_array["source_proxy"] = $source_proxy;
			$tmp_array["type_proxy"] = $type_proxy;
			$this->_proxyList['content'][] = $tmp_array;
			$this->saveProxyList($this->_proxyList);
			return true;
		}
		return false;
	}

	/**
	 * Получить в аренду прокси адрес
	 * @param      $rent_code    код арендатора
	 * @param      $site_for_use сайт на который будут поступать запросы
	 * @param bool $key_address  адрес для быстрого поиска прокси для снятия аренды или удаления
	 * @return bool|string
	 */
	public function getRentedProxy($rent_code, $site_for_use, $key_address = false) {
		for ($i = 0; $i < 100; $i++) {
			$this->blocProxyList();
			$this->_proxyList = $this->getProxyListInFile();
			if ($ip_proxy = $this->searchRentalAddress($rent_code, $site_for_use, $key_address)) return $ip_proxy["proxy"];
			if ($ip_proxy = $this->setRentedProxy($rent_code, $site_for_use)) {
				//сюда нужно поместить сохранение адреса быстрого доступа к данным о аренде прокси
				$this->saveProxyList($this->_proxyList);
				return $ip_proxy["proxy"];
			}
			// все прокси заняты, записываем изменения и освобождаем файл. ждем когда освободится
			$this->saveProxyList($this->_proxyList);
			sleep(60);
		}
		return false;
	}

	/**
	 * Поиск прокси по коду арендатора
	 * @param string     $rent_code    код арендатора
	 * @param string     $site_for_use сайт на который отправляют запросы черз прокси
	 * @param bool|array $key_address  адрес для быстрого доступа
	 * @return bool|string
	 */
	public function searchRentalAddress($rent_code, $site_for_use, $key_address = false) {
		//$this->_proxyList=$this->getProxyListInFile();
		// если задан адрес в где лежит информация об аренде, проверяем информацию
		if ($key_address) {
			if (isset($this->_proxyList['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["renter_code"])
				&& $this->_proxyList['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["renter_code"] == $rent_code
				&& $this->_proxyList['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["user_site"] == $site_for_use
			) {
				$end_term_rent = time() - $this->_rentTime;
				// проверяем время аренды прокси
				if ($key_address["start_rent"] > $end_term_rent) return $this->_proxyList['content'][$key_address['key_content']]["proxy"];
				else {
					$this->removeRent($key_address['key_content'], $key_address['key_renters']);
					return false;
				}
			}
		}
		$end_term_rent = time() - $this->_rentTime;
		// Если нет , то ищем в ручную
		foreach ($this->_proxyList['content'] as $key_content => $value_content) {
			if (!isset($value_content["renters"])) continue;
			foreach ($value_content["renters"] as $key_renters => $value_renters) {
				if ($value_renters["renter_code"] == $rent_code) {
					// проверяем время аренды прокси
					if ($value_renters["start_rent"] > $end_term_rent) {
						$return_array["proxy"] = $value_content["proxy"];
						$return_array["key_content"] = $key_content;
						$return_array["key_renters"] = $key_renters;
						return $return_array;
					} else {
						$this->removeRent($key_content, $key_renters);
						return false;
					}
				}
			}
			unset($value_renters);
		}
		unset($value_content);
		return false;
	}

	/**
	 * Поиск адрес прокси в текущем списке
	 * @param string $proxy адрес прокси
	 * @return array|bool
	 */
	public function searchProxyInList($proxy) {
		$this->_proxyList = $this->getProxyListInFile();
		foreach ($this->_proxyList['content'] as $key_content => $value_content) {
			if ($value_content["proxy"] == $proxy) {
				$return_array['proxy'] = $value_content["proxy"];
				$return_array['key_content'] = $key_content;
				return $return_array;
			}
		}
		unset($value_content);
		return false;
	}

	/**
	 * Ставит пометку в списке прокси что этот прокси арендован
	 * @param        $rent_code    код арендатора
	 * @param        $site_for_use сайт на который будут посылать запросы через прокси
	 * @param string $proxy        прокси адрес
	 * @return array|bool
	 */
	protected function setRentedProxy($rent_code, $site_for_use, $proxy = "") {
		if ($proxy) {
			$result = $this->searchProxyInList($proxy);
			$tmp_data['start_rent'] = time();
			$tmp_data['renter_code'] = $rent_code;
			$tmp_data['user_site'] = $site_for_use;
			$this->_proxyList['content'][$result['key_content']]["renters"][] = $tmp_data;
			return true;
		} else {
			foreach ($this->_proxyList['content'] as $key_content => $value_content) {
				$proxy_use_this_site = 0;
				if (isset($value_content["renters"])) {
					foreach ($value_content["renters"] as $value_renters) {
						if ($value_renters["user_site"] == $site_for_use) {
							$proxy_use_this_site = 1;
							break;
						}
					}
					unset($value_renters);
				}
				//Если через этот прокси не опрашивается сайт $site_for_use, то привяжем поток к этому прокси
				if (!$proxy_use_this_site) {
					$tmp_data['start_rent'] = time();
					$tmp_data['renter_code'] = $rent_code;
					$tmp_data['user_site'] = $site_for_use;
					$this->_proxyList['content'][$key_content]["renters"][] = $tmp_data;
					end($this->_proxyList['content'][$key_content]["renters"]);
					$return_array["proxy"] = $value_content["proxy"];
					$return_array["key_content"] = $key_content;
					$return_array["key_renters"] = key($this->_proxyList['content'][$key_content]["renters"]);
					return $return_array;
				}
			}
			unset($value_content);
		}
		return false;
	}

	/**
	 * Удаляет аренды с всех прокси для текущего арендатора
	 * @param string $rent_code код арендатора
	 * @return bool
	 */
	public function removeAllRentFromCode($rent_code) {
		$this->blocProxyList();
		$this->_proxyList = $this->getProxyListInFile();
		if (!isset($this->_proxyList['content'])) {
			$this->freeProxyList();
			return false;
		}
		if (isset($this->_proxyList['content']) && is_array($this->_proxyList['content'])) {
			foreach ($this->_proxyList['content'] as $key_content => $value_content) {
				if (!isset($value_content["renters"]) || !is_array($value_content["renters"])) continue;
				foreach ($value_content["renters"] as $key_renters => $value_renters) {
					if ($value_renters["renter_code"] == $rent_code) {
						$this->removeRent($key_content, $key_renters, 1);
					}
				}
				unset($value_renters);
			}
			unset($value_content);
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
			foreach ($this->_proxyList['content'] as $key_content => $value_content) {
				if (!isset($value_content["renters"]) || !is_array($value_content["renters"])) continue;
				foreach ($value_content["renters"] as $key_renters => $value_renters) {
					if ($value_renters["renter_code"]) {
						$this->removeRent($key_content, $key_renters, 1);
					}
				}
				unset($value_renters);
			}
		}
		unset($value_content);
		$this->saveProxyList($this->_proxyList);
	}

	/**
	 * Удаляет и списка прокси аренду по ключу в списке и коду арендатора
	 * @param int  $key_content    ключ в списке
	 * @param int  $key_renters    ключ арендатора в списке
	 * @param bool $without_saving с сохранением в файл
	 * @return bool
	 */
	public function removeRent($key_content, $key_renters, $without_saving = false) {
		$this->blocProxyList();
		if (isset($this->_proxyList['content'][$key_content]["renters"][$key_renters])) {
			unset($this->_proxyList['content'][$key_content]["renters"][$key_renters]);
			if (!$without_saving) $this->saveProxyList($this->_proxyList);
			else $this->freeProxyList();
			return true;
		} else {
			$this->freeProxyList();
			return false;
		}
	}

	/**
	 * Убирает аренду по коду арендатора и сайту на который посылают запрос
	 * @param $rent_code    код арендатора
	 * @param $site_for_use сайт на который посылают запросы
	 * @return bool
	 */
	public function removeRentToCodeSite($rent_code, $site_for_use) {
		if ($result_array = $this->searchRentalAddress($rent_code, $site_for_use)) {
			$this->removeRent($result_array['key_content'], $result_array['key_renters']);
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
			foreach ($this->_proxyList['content'] as $key_content => $value_content) {
				if ($this->_proxyList['content'][$key_content]['proxy'] == $proxy) {
					unset($this->_proxyList['content'][$key_content]);
					break;
				}
			}
			unset($value_content);
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
		foreach(explode("\n",$proxy) as $proxy){
			$this->_proxyList['content']['proxy'] = $proxy;
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
			$info_proxy = $this->genProxyInfo($proxy, $answer, $descriptor['info']);
			if ($info_proxy) {
				$good_proxy[] = $info_proxy;
			}
		}
		return false;
	}

	/**
	 * Проверяет массив прокси адресов на работоспособность и на поддерживаетмые функции
	 * @param array $array_proxy массив прокси адресов
	 * @return array|bool
	 */
	private function checkProxyArray($array_proxy) {
		if (is_array($array_proxy)) {
			if (!$this->_needCheckProxy) return $array_proxy;
			$good_proxy = array();
			$url = $this->getProxyChecker() . '?ip=' . $this->getServerIp() . '&proxy=yandex';
			$this->_getContent->setModeGetContent('multi');
			$this->_getContent->setCountMultiStream(1);
			$this->_getContent->setMinSizeAnswer(5);
			$this->_getContent->setMaxNumberRepeat(0);
			$this->_getContent->setDefaultSetting(CURLOPT_REFERER, "proxy-check.net");
			$this->_getContent->setDefaultSetting(CURLOPT_POST, true);
			$this->_getContent->setDefaultSetting(CURLOPT_POSTFIELDS, "proxy=yandex");
			$this->_getContent->setTypeContent('text');
			$this->_getContent->setDefaultSetting(CURLOPT_HEADER, false);
			$this->_getContent->setCheckAnswer(false);
			foreach (array_chunk($array_proxy, 200) as $value_array_proxy) {
				$this->_getContent->setCountMultiCurl(count($value_array_proxy));
				$url_array = array();
				$descriptor_array =& $this->_getContent->getDescriptorArray();
				foreach ($descriptor_array as $key => $value) {
					$this->_getContent->setOptionToDescriptor($descriptor_array[$key], CURLOPT_PROXY, $value_array_proxy[$key]['proxy']);
					$url_array[] = $url;
				}
				$answer_content = $this->_getContent->getContent($url_array);
				foreach ($answer_content as $key => $value) {
					$info_proxy = $this->genProxyInfo($value_array_proxy[$key], $value, $descriptor_array[$key]['info']);
					if ($info_proxy) {
						$good_proxy[] = $info_proxy;
					}
				}
				unset($value);
			}
			$this->_getContent->restoreDefaultSettings();
			if (count($good_proxy)) return $good_proxy;
		}
		return false;
	}

	/**
	 * Проверка доступности сайта через список прокси, отсеевает не рабочие прокси
	 * @param array  $array_proxy тестовый список прокси
	 * @param string $url         ссылка на страницу проверки
	 * @param array  $check_word  проверочные регулярные выражения
	 * @return array|bool
	 */
	private function checkProxyArrayToSite($array_proxy, $url, $check_word) {
		if (!is_array($array_proxy)) return false;
		$good_proxy = array();
		$this->_getContent->setModeGetContent('multi');
		$this->_getContent->setCountMultiStream(1);
		$this->_getContent->setTypeContent('text');
		$this->_getContent->setDefaultSetting(CURLOPT_HEADER, false);
		$this->_getContent->setDefaultSetting(CURLOPT_POST, false);
		$this->_getContent->setCheckAnswer(false);
		foreach (array_chunk($array_proxy, 100) as $value_proxy) {
			$this->_getContent->setCountMultiCurl(count($value_proxy));
			$descriptor_array =& $this->_getContent->getDescriptorArray();
			$url_array = array();
			foreach ($descriptor_array as $key => $value) {
				$this->_getContent->setOptionToDescriptor($descriptor_array[$key], CURLOPT_PROXY, $value_proxy[$key]['proxy']);
				$url_array[] = $url;
			}
			$answer_content = $this->_getContent->getContent($url_array);
			foreach ($answer_content as $key => $value) {
				$test_count = 0;
				$count_good_check = 0;
				foreach ($check_word as $value_check_word) {
					$test_count++;
					if (preg_match($value_check_word, $value)) $count_good_check++;
				}
				unset($value_check_word);
				if ($count_good_check == $test_count) $good_proxy[] = $value_proxy[$key];
			}
			unset($value);
		}
		$this->_getContent->restoreDefaultSettings();
		if (count($good_proxy)) return $good_proxy;
		else return false;
	}

	/**
	 * Возвращает адреса прокси поддерживающие выбранные функции
	 * @param array $proxy_list Список прокси
	 * @param array $fun_array  перечень необходимых функций anonym|referer|post|get|cookie
	 * @return array|bool
	 */
	public function getProxyByFunction($proxy_list, $fun_array = array()) {
		if (!is_array($proxy_list)) return false;
		$good_proxy = array();
		foreach ($proxy_list as $challenger) {
			$approach = false;
			if (count($fun_array)) {
				foreach ($fun_array as $name_function => $value_function) {
					if (in_array($name_function, $this->_proxyFunction) && $challenger[$name_function] >= $value_function) {
						if ($name_function == 'country') {
							if ($value_function === $challenger[$name_function]) {
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
				$good_proxy[] = $challenger;
			}
		}
		if (count($good_proxy)) return $good_proxy;
		return false;
	}

	/**
	 * Генерация информации о прокси для хранения в листе активных прокси
	 * @param array|string $proxy
	 * @param string       $answer
	 * @param null|array   $curl_info
	 * @return array|bool
	 */
	private function genProxyInfo($proxy, $answer, $curl_info = null) {
		if (preg_match('#^[01]{5}#', $answer) && preg_match_all('#(?<fun_status>[01])#U', $answer, $matches)) {
			if (is_string($proxy)) {
				$info_proxy['proxy'] = $proxy;
				$info_proxy['source_proxy'] = '';
				$info_proxy['type_proxy'] = '';
			} else {
				$info_proxy['proxy'] = $proxy['proxy'];
				$info_proxy['source_proxy'] = $proxy['source_proxy'];
				$info_proxy['type_proxy'] = $proxy['type_proxy'];
			}
			$info_proxy['anonym'] = (int)$matches['fun_status'][0];
			$info_proxy['referer'] = (int)$matches['fun_status'][1];
			$info_proxy['post'] = (int)$matches['fun_status'][2];
			$info_proxy['get'] = (int)$matches['fun_status'][3];
			$info_proxy['cookie'] = (int)$matches['fun_status'][4];
			$info_proxy['last_check'] = time();
			if (preg_match('%(?<ip>\d+\.\d+\.\d+.\d+)\:\d+%ims', $info_proxy['proxy'], $match)) {
				$country_name = @geoip_country_name_by_name($match['ip']);
				$info_proxy['country'] = $country_name ? $country_name : 'no country';
			} else {
				$info_proxy['country'] = 'no country';
			}
			if ($curl_info) {
				$info_proxy['starttransfer'] = $curl_info['starttransfer_time'];
				$info_proxy['upload'] = $curl_info['speed_upload'];
				$info_proxy['download'] = $curl_info['speed_download'];
			} else {

			}
			return $info_proxy;
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет прокси список
	 * @param array|bool $proxy_list сохраняемый список прокси
	 */
	public function saveProxyList($proxy_list = false) {
		if (!is_array($proxy_list)) $proxy_list = $this->_proxyList;
		$proxy_list['time'] = time();
		$this->_proxyList = $proxy_list;
		$json_proxy = json_encode($proxy_list);
		$this->blocProxyList();
		file_put_contents($this->_fileProxyList, '');
		rewind($this->_fHeandleProxyList);
		fwrite($this->_fHeandleProxyList, $json_proxy);
		$this->freeProxyList();
	}

	/**
	 * Создает профиль прокси адресов
	 * @param string $name_list           название
	 * @param string $check_url           проверочный URL
	 * @param array  $check_word_array    Проверочные регулярные выражения
	 * @param array  $need_function_array Перечень поддерживаемых функций
	 * @param bool   $need_update
	 */
	public function createProxyList($name_list, $check_url = "http://ya.ru", $check_word_array = array("#yandex#iUm"), $need_function_array = array(), $need_update = false) {
		$this->closeProxyList();
		$this->_nameList = $name_list;
		if (file_exists($this->getDirProxyListFile() . "/" . $name_list . ".proxy")) $this->deleteProxyList($name_list);
		$this->_fileProxyList = $this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy";
		$this->openProxyList();
		$proxy_list['content'] = array();
		$proxy_list['url'] = $check_url;
		$proxy_list['check_word'] = $check_word_array;
		$proxy_list['need_function'] = $need_function_array;
		$proxy_list['name_list'] = $name_list;
		$proxy_list['need_update'] = $need_update;
		$this->createProxyListBuk($proxy_list);
		$this->saveProxyList($proxy_list);
	}

	/**
	 * Создает резервную копию текущего профиля
	 * @param $proxy_list список прокси
	 */
	protected function createProxyListBuk($proxy_list) {
		$json_proxy = json_encode($proxy_list);
		$buk_file = $this->_fileProxyList . time() . ".buk";
		$fh = fopen($buk_file, "c+");
		file_put_contents($buk_file, '');
		rewind($fh);
		fwrite($fh, $json_proxy);
		fclose($fh);
	}

	/**
	 * Удаляет прокси лист
	 * @param $name_list имя прокси листа
	 */
	public function deleteProxyList($name_list) {
		if ($name_list == $this->_nameList) $this->closeProxyList();
		if (file_exists($this->getDirProxyListFile() . "/" . $name_list . ".proxy")) {
			unlink($this->getDirProxyListFile() . "/" . $name_list . ".proxy");
		}
	}

	/**
	 * Очищает прокси лист от прокси, но оставляет конфигурацию необходимых функций
	 * @param $name_list имя прокси листа
	 */
	public function clearProxyList($name_list) {
		$this->selectProxyList($name_list);
		$this->_proxyList['content'] = array();
		$this->saveProxyList($this->_proxyList);
	}

	/**
	 * Включает регулярное обновление прокси списка или выключает
	 * @param      $name_list имя прокси списка
	 * @param bool $value     вкл./выкл.
	 */
	public function setUpdateProxyList($name_list, $value = true) {
		$this->selectProxyList($name_list);
		$this->_proxyList['need_update'] = $value;
		$this->saveProxyList($this->_proxyList);
	}

	/**
	 * Генерация списка прокси адресов собраных из разных источников в один список с уникальными адресами
	 * @param array $proxy_array список прокси
	 * @return array
	 */
	private function getUniqueProxyIp($proxy_array) {
		$ip_proxy = array();
		foreach ($proxy_array as $key => $value){
			$ip_proxy[$key] = $proxy_array[$key]["proxy"];
		}
		$proxy_array_copy = $proxy_array;
		unset($value);
		$ip_proxy = array_unique($ip_proxy);
		$proxy_array = array();
		foreach ($ip_proxy as $key => $value){
			$proxy_array[$key] = $proxy_array_copy[$key];
		}
		unset($value);
		$proxy_array = array_values($proxy_array);
		return $proxy_array;
	}

	/**
	 * Обновляет прокси лист
	 * @param string $name_list имя прокси листа
	 * @param bool   $force     Принудительное обновление
	 * @return array обновленный список прокси
	 */
	public function updateProxyList($name_list, $force = false) {
		if ($name_list == $this->getDefaultListName()) {
			return $this->selectProxyList($this->getDefaultListName());
		} else {
			$allProxy = $this->selectProxyList($this->getDefaultListName());
			$this->selectProxyList($name_list);
		}
		$this->freeProxyList();
		$end_term_proxy = time() - $this->_storageTime;
		if (
			(
				isset($this->_proxyList)
				&& is_array($this->_proxyList)
				&& isset($this->_proxyList['content'])
				&& count($this->_proxyList['content'])
				&& $this->_proxyList['time'] > $end_term_proxy
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
		$end_term_proxy = time() - $this->_storageTime;
		if (
			$this->_proxyList
			&& isset($this->_proxyList['content'])
			&& is_array($this->_proxyList['content'])
			&& count($this->_proxyList['content'])
			&& $this->_proxyList['time']
			> $end_term_proxy
			&& !$force
		) {
			return $this->_proxyList;
		}
		$proxy_list = $this->downloadProxy();
		$archive = $this->getArchiveProxy();
		$archive_proxy_list = array();
		$tmp['source_proxy'] = 'archive';
		$tmp['type_proxy'] = 'http';
		foreach ($archive as $proxy) {
			$tmp['proxy'] = $proxy;
			$archive_proxy_list[] = $tmp;
		}
		$new_proxy = array();
		foreach ($proxy_list['content'] as $proxy) {
			$new_proxy[] = $proxy['proxy'];
		}
		$this->saveInArchive($new_proxy);

		$old_proxy['content'] = array_merge($proxy_list['content'], $archive_proxy_list);
		$old_proxy['content'] = $this->getUniqueProxyIp($old_proxy['content']);
		$old_proxy['content'] = $this->checkProxyArray($old_proxy['content']);
		$this->_proxyList = $old_proxy;
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
	 * @param        $name_list
	 * @param string $check_url
	 * @param array  $check_word_array
	 * @param array  $need_function_array
	 * @param bool   $need_update
	 */
	public function configProxyList($name_list, $check_url = "http://bpteam.net", $check_word_array = array("#\+380632359213#ims"), $need_function_array = array(), $need_update = false) {
		$this->selectProxyList($name_list);
		$this->_proxyList['url'] = $check_url;
		$this->_proxyList['check_word'] = $check_word_array;
		$this->_proxyList['need_function'] = $need_function_array;
		$this->_proxyList['name_list'] = $name_list;
		$this->_proxyList['need_update'] = $need_update;
		$this->createProxyListBuk($this->_proxyList);
		$this->saveProxyList($this->_proxyList);
	}


	/**
	 * Выбор прокси листа
	 * @param string $name_list имя прокси листа
	 * @return array выбранный прокси лист
	 */
	public function selectProxyList($name_list) {
		$this->closeProxyList();
		$this->setListType('dynamic');
		$this->_nameList = $name_list;
		if ($this->proxyListExist($name_list)) {
			$this->_fileProxyList = $this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy";
		} else {
			$this->_fileProxyList = $this->getDirProxyListFile() . "/" . $this->_nameList . ".proxy";
			$this->createProxyList($name_list);
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
		$file_list = glob($this->getDirProxyListFile() . "/" . "*.proxy");
		$proxy_list_array = array();
		foreach ($file_list as $value) {
			if (preg_match("#/(?<name_list>[^/]+)\.proxy$#iUm", $value, $match)) {
				$proxy_list_array[] = $match['name_list'];
			}
		}
		return $proxy_list_array;
	}

	/**
	 * Проверяет существует ли прокси лист
	 * @param string $name_list имя прокси листа
	 * @return bool
	 */
	public function proxyListExist($name_list) {
		$all_list = $this->getAllNameProxyList();
		if (array_search($name_list, $all_list) !== false) {
			return true;
		} else return false;
	}
}