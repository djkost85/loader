<?php
namespace GetContent;

use GetContent\cProxy as c_proxy;
use GetContent\c_string_work as c_string_work;

/**
 * Class c_get_content
 * С помощью основных функций библиотеки cURL посылает http запросы для скачивания контента из сети
 * Умеет работать через прокси сервера, в много поточном режиме с верификацией данных.
 * @author  Evgeny Pynykh <bpteam22@gmail.com>
 * @package GetContent
 * @version 2.0
 */
class c_get_content
{
	/**
	 * Набор настроек по умолчанию для cURL
	 * @access private
	 * @var array
	 * Структура:
	 * $default_settings[CURLOPT_HEADER]= bool для включения заголовков в вывод
	 * $default_settings[CURLOPT_URL]= string url источника данных
	 * $default_settings[CURLOPT_TIMEOUT]= int максимальное время ожидания ответа от запроса
	 * $default_settings[CURLOPT_USERAGENT]= string useragent баузера
	 * $default_settings[CURLOPT_PROXY]= string прокси адрес через который будет проходить запрос
	 * $default_settings[CURLOPT_RETURNTRANSFER]= bool флаг для обозначения возвращения результата в переменную
	 * $default_settings[CURLOPT_REFERER]= string адрес страници с которой перешли на текущую
	 * $default_settings[CURLOPT_FOLLOWLOCATION]= bool следовать переадресации сервера или нет
	 * $default_settings[CURLOPT_POST]= bool врключение отправки post запроса на удаленный сервер
	 * $default_settings[CURLOPT_POSTFIELDS]= string|mixed данные post запроса
	 * $default_settings[CURLOPT_FRESH_CONNECT] = bool TRUE для принудительного использования нового соединения вместо закэшированного.
	 * $default_settings[CURLOPT_HTTPHEADER] array Отправка http заголовков
	 * $default_settings[CURLOPT_FORBID_REUSE] TRUE для принудительного закрытия соединения после завершения его обработки так, чтобы его нельзя было использовать повторно.
	 */
	private $default_settings;
	/**
	 * Пересление всех поддерживаемых настроек для cURL
	 * @var array
	 */
	private $all_setting;
	/**
	 * Флаг для включения запросов через прокси сервера
	 * @var bool
	 */
	private $use_proxy;
	/**
	 * Адрес спрокси или класс для работы с прокси
	 * @var string|cProxy
	 */
	public $proxy;
	/**
	 * Хранит разультаты запросов если режим singele, то string, если multi то array
	 * @var string|array
	 */
	private $answer;
	/**
	 * Дескриптор с текущими настройками и уникальным ключом
	 * @var array
	 * Структура:
	 * $descriptor['descriptor'] дескриптор  cURL
	 * $descriptor['info'] Информация выданная функцией curl_getinfo()
	 * $descriptor['option'][имя опции] = value параметры cURL
	 * $descriptor['descriptor_key'] уникальный ключ дескриптора для аренды прокси и сохранения cookie
	 */
	private $descriptor;
	/**
	 * Список дескрипторов с текущими настройками и уникальным ключом для работы в multi режиме
	 * @var array
	 * $descriptor_array[key]['descriptor'] дескриптор  cURL
	 * $descriptor_array[key]['info'] Информация выданная функцией curl_getinfo()
	 * $descriptor_array[key]['option'][имя опции] = value параметры cURL
	 * $descriptor_array[key]['descriptor_key'] уникальный ключ дескриптора для аренды прокси и сохранения cookie
	 */
	private $descriptor_array;
	/**
	 * Количество потоков cURL в режиме multi
	 * @var int
	 */
	private $count_multi_curl;
	/**
	 * Количество запросов к одному url в режиме multi
	 * @var int
	 */
	private $count_multi_stream;
	/**
	 * Количество дескрипторов которые нужно инициализировать для режима multi
	 * @var int = count_multi_curl*count_multi_stream
	 */
	private $count_multi_descriptor;
	/**
	 * Текущий номер повторного запроса для получения контента
	 * @var int
	 */
	private $number_repeat;
	/**
	 * Максимальное количество разрешенных повторных запросов для получения корректного ответа
	 * @var int
	 */
	private $max_number_repeat; // максимальное количество повторных запросов на получение контента
	/**
	 * Минимальный размер ответа в байтах
	 * @var int
	 */
	private $min_size_answer;
	/**
	 * Тип получаемых данных
	 * @var mixed
	 * [file] Файл
	 * [img] Изображение
	 * [text] Текст
	 * [html] html страницы
	 */
	private $type_content;
	/**
	 * Флаг на включение запроса из кеша поисковых машин если страница не доступна
	 * @var bool
	 */
	private $in_cache;
	/**
	 * Флаг на включение смены кодировки текста
	 * @var bool
	 */
	private $encoding_answer;
	/**
	 * Имя кодировки в которую преобразовывать текст ответа
	 * @var string
	 */
	private $encoding_name;
	/**
	 * Имя кодировки полученого текста
	 * @var string
	 */
	private $encoding_name_answer;
	/**
	 * Флаг на включение проверки ответа на корректность
	 * @var bool
	 */
	private $check_answer;
	/**
	 * Режим скачивания контента
	 * @var string
	 * multi многопоточный режим
	 * string однопоточный режим
	 */
	private $mode_get_content;
	/**
	 * Папка в которую сохраняются файлы cookie
	 * @var string
	 */
	private $dir_cookie;

	/**
	 * Количество одновременных запросов
	 * @var int
	 */
	private $count_requests;

	/**
	 * текущее количество редиректов в одном запросе
	 * @var int
	 */
	private $redirectCount;

	/**
	 * Максимальное количество редиректов для одного запроса
	 * @var  int
	 */
	private $maxRedirect;

	/**
	 * использование статического файла cookie
	 * @var bool
	 */
	private $useStaticCookie;

	/**
	 * имя файла с cookie
	 * @var string
	 */
	private $cookieFile;
	/**
	 * @var string
	 */
	private $referer;
	/**
	 * @return \GetContent\c_get_content
	 */
	function __construct() {
		$this->all_setting = array(
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
		$this->set_dir_cookie("get_content_files/cookie");
		$this->restore_default_settings();
		$this->count_multi_stream = 1;
		$this->count_multi_curl = 1;
		$this->set_count_multi_descriptor();
		$this->set_use_proxy(false);
		$this->set_number_repeat(0);
		$this->set_max_number_repeat(10);
		$this->set_min_size_answer(100);
		$this->set_type_content("text");
		$this->set_in_cache(false);
		$this->set_encoding_answer(false);
		$this->set_encoding_name("UTF-8");
		$this->set_check_answer(false);
		$this->setRedirectCount(0);
		$this->setMaxRedirect(10);
		$this->setReferer('');
		$this->setUseStaticCookie(false);
		$this->set_mode_get_content('single');
	}

	function __destrukt() {
		$this->clear_cookie();
		$this->close_get_content();

	}

	/**
	 * функция для проверки доступа к необходимым ресурсам системы
	 */
	public function function_check() {
		echo "c_get_content->function_check {</br>\n";
		$mess = '';
		if (!function_exists('curl_init')) $mess .= "Error: CURL is not installed</br>\n";
		if (!is_dir($this->get_dir_cookie())) {
			$mess .= "Warning: folder for the cookie does not exist</br>\n";
		} else {
			if (!is_readable($this->get_dir_cookie()) || !is_writable($this->get_dir_cookie())) {
				$mess .= "Warning: folder for the cookie does not have the necessary rights to use</br>\n";
			}
		}
		if (!class_exists('cProxy')) $mess .= "Warning: cProxy class is declared, can not work with proxy</br>\n";
		if (!class_exists('c_string_work')) $mess .= "Warning: c_string_work class is declared, word processing is not possible</br>\n";
		if ($mess) echo $mess . " To work correctly, correct the above class c_get_content requirements </br>\n";
		else echo "c_get_content ready</br>\n";
		echo "c_get_content->function_check }</br>\n";
	}

	/**
	 * Удаляет старые файлы, которые уже не используются
	 * @param int $storage_time время хранения прокси
	 */
	public function clear_cookie($storage_time = 172800) {
		$file_list = glob($this->get_dir_cookie() . "*.cookie");
		foreach ($file_list as $value) {
			if(preg_match("/\/(?<create_time>\d+)(?:\.|\s*)\d*\.cookie$/iU", $value, $match)){
				if ((int)$match['create_time'] < time() - $storage_time) {
					unlink($value);
				}
			}
		}
	}

	/**
	 * Адерс должен быть относительным папке где лежит исходник класса
	 * @param string $new_dir_cookie
	 */
	public function set_dir_cookie($new_dir_cookie) {
		$this->dir_cookie = $new_dir_cookie;
	}

	public function get_dir_cookie() {
		return GC_ROOT_DIR . "/" . $this->dir_cookie . "/";
	}

	public function set_count_requests($val) {
		$this->count_requests = $val;
	}

	public function get_count_requests() {
		return $this->count_requests;
	}

	public function getRedirectCount(){
		return $this->redirectCount;
	}

	public function setRedirectCount($val){
		$this->redirectCount = $val;
	}

	public function getMaxRedirect(){
		return $this->maxRedirect;
	}

	public function setMaxRedirect($val){
		$this->maxRedirect = $val;
	}

	private function useRedirect(){
		$this->setRedirectCount($this->getRedirectCount()+1);
		return ($this->getRedirectCount()<=$this->getMaxRedirect());
	}

	public function setUseStaticCookie($val){
		$this->useStaticCookie = (bool)$val;
	}

	private function getUseStaticCookie(){
		return $this->useStaticCookie;
	}

	public function setCookieFile($val){
		$this->setUseStaticCookie(true);
		$this->cookieFile = $val;
	}

	public function getCookieFile(){
		return $this->cookieFile;
	}

	public function setReferer($val){
		$this->referer = $val;
		$this->set_default_setting(CURLOPT_REFERER, $this->referer);
	}

	public function getReferer(){
		return $this->referer;
	}
	/**
	 * @param int   $option
	 * @param mixed $value
	 */
	public function set_default_setting($option, $value) {
		$this->default_settings[$option] = $value;
	}

	/**
	 * @param int $option
	 * @return mixed
	 */
	public function get_default_setting($option) {
		return $this->default_settings[$option];
	}

	/**
	 * @param array $value
	 * @return bool
	 */
	public function set_default_settings($value) {
		if (is_array($value)) {
			$this->default_settings = $value;
			return true;
		} else return false;
	}

	/**
	 * @return array
	 */
	public function get_default_settings() {
		return $this->default_settings;
	}

	/**
	 * Востанавливает настройки по умолчанию для всех потоков где не изменены настройки дескрипторов
	 */
	public function restore_default_settings() {
		$this->set_default_settings(array(
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
	public function set_use_proxy($value = false) {
		switch ((bool)$value) {
			case true:
				if (is_string($value) && c_string_work::is_ip($value)) $this->proxy = $value;
				elseif (!is_object($this->proxy)) $this->proxy = new cProxy();
				else return false;
				break;
			case false:
				unset($this->proxy);
				$this->proxy = NULL;
				break;
			default:
				return false;
		}
		$this->use_proxy = (bool)$value;
		return true;
	}

	public function get_use_proxy() {
		return $this->use_proxy;
	}

	/**
	 * @param int $value
	 */
	public function set_number_repeat($value = 0) {
		$this->number_repeat = $value;
	}

	public function get_number_repeat() {
		return $this->number_repeat;
	}

	/**
	 * @param int $value
	 */
	public function set_max_number_repeat($value = 10) {
		$this->max_number_repeat = $value;
	}

	public function get_max_number_repeat() {
		return $this->max_number_repeat;
	}

	/**
	 * Проверяет возможность сделать повторный запрос
	 * @return bool
	 */
	private function repeat_get_content() {
		if ($this->get_number_repeat() < $this->get_max_number_repeat()) {
			$this->next_repeat();
			return true;
		} else {
			$this->end_repeat();
			return false;
		}
	}

	/**
	 * Регестрирует повторный запрос
	 */
	private function next_repeat() {
		$num_repeat = $this->get_number_repeat();
		$num_repeat++;
		$this->set_number_repeat($num_repeat);
	}

	/**
	 * Обнуляет счетчик повторных запросов
	 */
	private function end_repeat() {
		$this->set_number_repeat(0);
	}

	/**
	 * @param int $value
	 */
	public function set_min_size_answer($value = 5000) {
		$this->min_size_answer = $value;
	}

	public function get_min_size_answer() {
		return $this->min_size_answer;
	}

	/**
	 * @param string $type_content file|img|text|html
	 * @return bool
	 */
	public function set_type_content($type_content = "text") {
		switch ($type_content) {
			case 'file':
				$this->type_content = 'file';
				$this->set_encoding_answer(false);
				return true;
				break;
			case 'img':
				$this->type_content = 'img';
				$this->set_encoding_answer(false);
				return true;
				break;
			case 'text':
				$this->type_content = 'text';
				return true;
				break;
			case 'html':
				$this->type_content = 'html';
				break;
			default:
				break;
		}
		return false;
	}

	public function get_type_content() {
		return $this->type_content;
	}

	/**
	 * @param bool $value
	 */
	public function set_in_cache($value = false) {
		$this->in_cache = $value;
	}

	public function get_in_cache() {
		return $this->in_cache;
	}

	/**
	 * @param bool $value
	 */
	public function set_encoding_answer($value = false) {
		$this->encoding_answer = $value;
	}

	public function get_encoding_answer() {
		return $this->encoding_answer;
	}

	/**
	 * @param string $value
	 */
	public function set_encoding_name($value = "UTF-8") {
		$this->encoding_name = $this->check_name_encoding($value);
	}

	public function get_encoding_name() {
		return $this->encoding_name;
	}

	/**
	 * @param string $value
	 */
	public function set_encoding_name_answer($value) {
		$this->encoding_name_answer = $this->check_name_encoding($value);
	}

	public function get_encoding_name_answer() {
		return $this->encoding_name_answer;
	}

	/**
	 * Возвращает форматированое имя поддерживаемой кодировки
	 * @param $value название кодировки
	 * @return bool|string
	 */
	private function check_name_encoding($value) {
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
	public function set_check_answer($value = true) {
		$this->check_answer = $value;
	}

	public function get_check_answer() {
		return $this->check_answer;
	}

	/**
	 * @param int $value
	 */
	public function set_count_multi_curl($value = 1) {
		if ($this->get_count_multi_curl() != $value) {
			$this->close_get_content();
			$this->count_multi_curl = $value;
			$this->set_count_multi_descriptor();
			$this->init_get_content();
		}
	}

	public function get_count_multi_curl() {
		return $this->count_multi_curl;
	}

	/**
	 * @param int $value
	 */
	public function set_count_multi_stream($value = 1) {
		if ($this->get_count_multi_stream() != $value) {
			$this->close_get_content();
			$this->count_multi_stream = $value;
			$this->set_count_multi_descriptor();
			$this->init_get_content();
		}
	}

	public function get_count_multi_stream() {
		return $this->count_multi_stream;
	}

	/**
	 * Задает число дескрипторов cURL для подлучение данных
	 */
	private function set_count_multi_descriptor() {
		$this->count_multi_descriptor = $this->get_count_multi_curl() * $this->get_count_multi_stream();
	}

	private function get_count_multi_descriptor() {
		return $this->count_multi_descriptor;
	}

	/**
	 * @param string $new_mode_get_content single|multi
	 * @return bool
	 */
	public function set_mode_get_content($new_mode_get_content = 'single') {
		$this->close_get_content();
		switch ($new_mode_get_content) {
			case 'single':
				$this->mode_get_content = 'single';
				$this->set_default_setting(CURLOPT_FOLLOWLOCATION,false);
				break;
			case 'multi':
				$this->mode_get_content = 'multi';
				if ($this->get_count_multi_curl() < 1) $this->set_count_multi_curl(1);
				$this->set_default_setting(CURLOPT_FOLLOWLOCATION,true);
				break;
			default:
				return false;
		}
		$this->init_get_content();
		return true;
	}

	public function get_mode_get_content() {
		return $this->mode_get_content;
	}

	public function &get_descriptor() {
		return $this->descriptor;
	}

	public function &get_descriptor_array() {
		return $this->descriptor_array;
	}

	/**
	 * Инициализирует дескрипторы cURL
	 */
	private function init_get_content() {
		switch ($this->get_mode_get_content()) {
			case 'single':
				$this->init_single_get_content();
				break;
			case 'multi':
				$this->init_multi_get_content();
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
	private function init_single_get_content() {
		$descriptor =& $this->get_descriptor();
		if (!isset($descriptor['descriptor_key'])) $descriptor['descriptor_key'] = microtime(1) . mt_rand();
		$descriptor['descriptor'] = curl_init();
	}

	/**
	 * Инициализация дескриптора cURL в режиме multi
	 * @return void
	 */
	private function init_multi_get_content() {
		$descriptor =& $this->get_descriptor();
		$descriptor_array =& $this->get_descriptor_array();
		$descriptor['descriptor'] = curl_multi_init();
		if (is_array($descriptor_array) && count($descriptor_array) > $this->get_count_multi_descriptor()) {
			$descriptor_array = array_slice($descriptor_array, 0, $this->get_count_multi_descriptor());
		}
		for ($i = 0; $i < $this->get_count_multi_descriptor(); $i++) {
			if (!isset($descriptor_array[$i]['descriptor_key'])) $descriptor_array[$i]['descriptor_key'] = microtime(1) . mt_rand();
			$descriptor_array[$i]['descriptor'] = curl_init();
			curl_multi_add_handle($descriptor['descriptor'], $descriptor_array[$i]['descriptor']);
		}
	}

	/**
	 * Закрывает инициализированные дескрипторы cURL
	 * @param bool $reinit Переменная для обхода стирания параметров в опциях дескриптора для повторного запроса. Причина: не срабатывания параметров CURLOPT_FRESH_CONNECT и CURLOPT_FORBID_REUSE после примерно трех запросов опция по не известной причине не срабатывают.
	 */
	private function close_get_content($reinit = false) {
		$descriptor =& $this->get_descriptor();
		if (isset($descriptor['descriptor'])) {
			switch ($this->get_mode_get_content()) {
				case 'single':
					$this->close_single_get_content($reinit);
					break;
				case 'multi':
					$this->close_multi_get_content($reinit);
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
	private function close_single_get_content($reinit) {
		$descriptor =& $this->get_descriptor();
		curl_close($descriptor['descriptor']);
		if ($this->get_use_proxy() && is_object($this->proxy)) {
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
	private function close_multi_get_content($reinit) {
		$descriptor =& $this->get_descriptor();
		$descriptor_array =& $this->get_descriptor_array();
		if (is_array($descriptor_array)) {
			foreach ($descriptor_array as $key => $value) {
				if (isset($descriptor_array[$key]['descriptor'])) {
					@curl_multi_remove_handle($descriptor['descriptor'], $descriptor_array[$key]['descriptor']);
					curl_close($descriptor_array[$key]['descriptor']);
					if ($this->get_use_proxy() && is_object($this->proxy)) {
						$this->proxy->removeAllRentFromCode($descriptor_array[$key]['descriptor_key']);
					}
					unset($descriptor_array[$key]['descriptor']);
					if (!$reinit) unset($descriptor_array[$key]['option']);
				}
			}
			unset($value);
		}
		@curl_multi_close($descriptor['descriptor']);
	}

	/**
	 * Повторная инициализация дескрипторов cURL, функция создана по причине не срабатывания параметров CURLOPT_FRESH_CONNECT и CURLOPT_FORBID_REUSE после примерно трех запросов опция по не известной причине не срабатывают.
	 */
	private function reinit_get_content() {
		switch ($this->get_mode_get_content()) {
			case 'single':
				$this->close_single_get_content(true);
				$this->init_single_get_content();
				break;
			case 'multi':
				$this->close_multi_get_content(true);
				$this->init_multi_get_content();
				break;
			default:
				break;
		}
	}

	/**
	 * Выполнение заросов по $url с определением по какому методу осуществлять запрос
	 * @param string|array $url
	 * @param string       $reg регулярное выражение для дополнительной проверки ответа
	 * @return array|string
	 */
	public function get_content($url = "", $reg = '##') {
		if (is_string($url) && $this->get_mode_get_content() != 'single') $url = array($url);
		if (is_array($url) && $this->get_mode_get_content() != 'multi') $this->set_mode_get_content('multi');
		switch ($this->get_mode_get_content()) {
			case 'single':
				$this->get_single_content($url, $reg);
				break;
			case 'multi':
				$this->get_multi_content($url, $reg);
				break;
			default:
				break;
		}
		$this->close_get_content();
		$this->init_get_content();
		return $this->get_answer();
	}

	/**
	 * Совершает зарос в режиме single
	 * @param        $url
	 * @param string $reg регулярное выражение для дополнительной проверки ответа
	 * @return string
	 */
	private function get_single_content($url, $reg) {
		$descriptor =& $this->get_descriptor();
		do {
			if ($this->get_number_repeat() > 0) $this->reinit_get_content();
			$this->set_default_setting(CURLOPT_URL, $url);
			$this->set_options_to_descriptor($descriptor);
			$answer = $this->exec_single_get_content();
			$this->setReferer($url);
			$descriptor['info'] = curl_getinfo($descriptor['descriptor']);
			$descriptor['info']['header'] = $this->getHeader($answer);
			if($this->isRedirect()){
				if($this->useRedirect()){
					$answer = $this->get_single_content(urldecode($descriptor['info']['redirect_url']), $reg);
				} else {
					return false;
				}
			}
			$this->setRedirectCount(0);
			if ($reg && preg_match($reg, $answer)) $reg_answer = true;
			else $reg_answer = false;
			if ((!$this->get_check_answer() || $this->check_answer_valid($answer, $descriptor['info'])) && $reg_answer) {
				$this->answer = $answer;
				$this->end_repeat();
				break;
			} elseif ($this->get_use_proxy() && is_object($this->proxy)) {
				$this->proxy->removeProxyInList($descriptor['option'][CURLOPT_PROXY]);
			}
		} while ($this->repeat_get_content());
		$this->answer = $this->prepare_content($answer);
		return $this->get_answer();
	}

	/**
	 * Совершает запрос в режиме multi
	 * @param        $url
	 * @param string $reg регулярное выражение для дополнительной проверки ответа
	 * @return array
	 */
	private function get_multi_content($url, $reg) {
		$copy_url = $url; //Копируем для создания связи по ключам после удаления из основного массива
		$good_answer = array();
		do {
			if ($this->get_number_repeat() > 0) $this->reinit_get_content();
			$this->set_count_multi_curl(count($url));
			$descriptor_array =& $this->get_descriptor_array();
			$count_multi_stream = $this->get_count_multi_stream();
			$j = 0;
			$url_descriptors = array();
			foreach ($url as $key_url => $value_url) {
				for ($i = 0; $i < $count_multi_stream; $i++) {
					$url_descriptors[$j] = $key_url; //Для связи ключа url и вычисления ключа хорошего ответа
					if (isset($descriptor_array[$j]['descriptor'])) {
						$this->set_option_to_descriptor($descriptor_array[$j], CURLOPT_URL, $value_url);
					}
					$j++;
				}
			}
			foreach ($descriptor_array as $key => $value) $this->set_options_to_descriptor($descriptor_array[$key]);
			unset($value);
			$answer = $this->exec_multi_get_content();
			foreach ($answer as $key => $value) {
				$descriptor_array[$key]['info'] = curl_getinfo($descriptor_array[$key]['descriptor']);
				$descriptor_array[$key]['info']['header'] = $this->getHeader($value);
				$key_good_answer = ($url_descriptors[$key] * $count_multi_stream) + $key % $count_multi_stream;
				if ($reg && preg_match($reg, $value)) $reg_answer = true;
				else $reg_answer = false;
				if (!isset($good_answer[$key_good_answer]) && (!$this->get_check_answer() || $this->check_answer_valid($value, $descriptor_array[$key]['info'])) && $reg_answer) {

					if (isset($url[$url_descriptors[$key]])) unset($url[$url_descriptors[$key]]);
					$good_answer[$key_good_answer] = $value;
				} elseif ($this->get_use_proxy() && is_object($this->proxy)) {
					$this->proxy->removeProxyInList($descriptor_array[$key]['option'][CURLOPT_PROXY]);
				}
			}
			if (count($url) == 0) {
				$this->end_repeat();
				break;
			}
		} while ($this->repeat_get_content());
		foreach ($good_answer as &$value) $value = $this->prepare_content($value);
		$tmp_answer = array();
		$j = 0;
		foreach ($copy_url as $key_url => $value_url) {
			for ($i = 0; $i < $count_multi_stream; $i++) {
				if (isset($good_answer[$j])) $tmp_answer[$key_url][$i] = $good_answer[$j];
				$j++;
			}
		}
		return $this->answer = $tmp_answer;
	}

	/**
	 * Присваивает настройки cURL декскриптору
	 * @param array $descriptor   дескриптор cURL
	 * @param array $option_array список настроек для cURL дексриптора
	 * @return bool
	 */
	public function set_options_to_descriptor(&$descriptor, $option_array = array()) {
		foreach ($this->all_setting as $key_setting) {
			if (isset($option_array[$key_setting])) $this->set_option_to_descriptor($descriptor, $key_setting, $option_array[$key_setting]);
			elseif(isset($descriptor['option'][$key_setting])) $this->set_option_to_descriptor($descriptor,$key_setting,$descriptor['option'][$key_setting]);
			else $this->set_option_to_descriptor($descriptor, $key_setting);
		}
		unset($key_setting);
		if ($this->get_use_proxy() && !isset($descriptor['option'][CURLOPT_PROXY])) {
			if (is_object($this->proxy)) {
				if (is_string($proxy_ip = $this->proxy->getProxy($descriptor['descriptor_key'], c_string_work::get_domain_name($descriptor['option'][CURLOPT_URL]))) && c_string_work::is_ip($proxy_ip))
					$this->set_option_to_descriptor($descriptor, CURLOPT_PROXY, $proxy_ip);
				else $descriptor['option'][CURLOPT_URL] = '';
			} elseif (is_string($this->proxy)) $this->set_option_to_descriptor($descriptor, CURLOPT_PROXY, $this->proxy);
		}
		if($this->getUseStaticCookie() && $this->get_mode_get_content() == 'single'){
			$cookieFile = $this->get_dir_cookie() . $this->getCookieFile() . ".cookie";
		} else {
			$cookieFile = $this->get_dir_cookie() . $descriptor['descriptor_key'] . ".cookie";
		}
		if (!is_writable($cookieFile)) {
			$fh = fopen($cookieFile, "a+");
			fclose($fh);
		}
		$this->set_option_to_descriptor($descriptor, CURLOPT_COOKIEJAR, $cookieFile);
		$this->set_option_to_descriptor($descriptor, CURLOPT_COOKIEFILE, $cookieFile);
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
	public function set_option_to_descriptor(&$descriptor, $option, $value = -2, $key = -2) //
	{
		if ($key != -2) {
			if (array_key_exists($key, $descriptor)) {
				if ($value == -2) $descriptor[$key]['option'][$option] = $this->get_default_setting($option);
				else $descriptor[$key]['option'][$option] = $value;
				if ($this->check_option($descriptor[$key], $option, $descriptor[$key]['option'][$option])) return false;
			}
		} else {
			if ($value == -2) $descriptor['option'][$option] = $this->get_default_setting($option);
			else $descriptor['option'][$option] = $value;
			if ($this->check_option($descriptor, $option, $descriptor['option'][$option])) return true;
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
	private function check_option(&$descriptor, $option, $value = NULL) {
		switch ($option) {
			case CURLOPT_POST:
				if ($value != NULL) $descriptor['option'][$option] = (bool)$value;
				break;
			case CURLOPT_POSTFIELDS:
				if (!$value) {
					unset($descriptor['option'][$option]);
					$this->set_option_to_descriptor($descriptor, CURLOPT_POST, false);
					return true;
				} else $this->set_option_to_descriptor($descriptor, CURLOPT_POST, true);
				break;
			case CURLOPT_URL:
				if (!preg_match("#(http|https)://#iUm", $descriptor['option'][$option])) $descriptor['option'][$option] = "http://" . $value;
				if ($this->get_in_cache()) {
					preg_match("#https?://(?<url>.*)$#iUm", $descriptor['option'][$option], $match);
					$descriptor['option'][$option] = "http://webcache.googleusercontent.com/search?q=cache:" . $match['url'];
					return true;
				}
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
	 * Выполнение запроса cURL
	 * @return mixed
	 */
	private function exec_get_content() {
		switch ($this->get_mode_get_content()) {
			case 'single':
				return $this->exec_single_get_content();
				break;
			case 'multi':
				return $this->exec_multi_get_content();
				break;
			default:
				break;
		}
		return false;
	}


	/**
	 * Выполнение запроса cURL в режиме single
	 * @return string
	 */
	private function exec_single_get_content() {
		$descriptor =& $this->get_descriptor();
		$this->answer = curl_exec($descriptor['descriptor']);
		return $this->answer;
	}

	/**
	 * Выполнение запроса cURL в режиме multi
	 * @return array
	 */
	private function exec_multi_get_content() {
		$descriptor =& $this->get_descriptor();
		$descriptor_array =& $this->get_descriptor_array();
		do {
			curl_multi_exec($descriptor['descriptor'], $running);
			usleep(100);
		} while ($running > 0);
		$this->answer = array();
		foreach ($descriptor_array as $key => $value) $this->answer[$key] = curl_multi_getcontent($descriptor_array[$key]['descriptor']);
		unset($value);
		return $this->answer;
	}

	/**
	 * Возвращает данные полученые после запросов
	 * @param bool $get_all_answer для режима multi, возваращать все или самы большой по размеру
	 * @return array|string
	 */
	public function get_answer($get_all_answer = false) {
		switch ($this->get_mode_get_content()) {
			case 'single':
				return $this->answer;
				break;
			case 'multi':
				if (!$get_all_answer) {
					$a = array();
					foreach ($this->answer as $key => $value) $a[$key] = $this->get_big_answer($value);
					return $a;
				} else return $this->answer;
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
	private function get_big_answer($a) {
		$big_a = 0;
		$big_key = 0;
		foreach ($a as $key => $value) {
			$this_a = strlen($value);
			if ($this_a > $big_a) {
				$big_a = $this_a;
				$big_key = $key;
			}
		}
		return $a[$big_key];
	}

	/**
	 * Проверка ответа на корректность
	 * @param       $answer    Текс ответа
	 * @param array $curl_data массив информации о запросе при помощи функции curl_getinfo()
	 * @return bool
	 */
	private function check_answer_valid($answer, $curl_data) {
		if (!$this->http_code($curl_data['http_code'])) return false;
		if (($curl_data['size_download'] < $curl_data['download_content_length'] && $curl_data['download_content_length'] != -1) || $curl_data['size_download'] < $this->get_min_size_answer()) return false;
		switch ($this->get_type_content()) {
			case 'file':
				if ($this->mime_type($curl_data['content_type'], 'file')) return true;
				break;
			case 'img':
				if ($this->mime_type($curl_data['content_type'], 'img')) return true;
				break;
			case 'html':
				if ($this->mime_type($curl_data['content_type'], 'html') && preg_match('#<\s*/\s*(html|body)[^<>]*>#ims', $answer)) return true;
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
	private function mime_type($mime, $type) {
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
	 * @param int $http_code
	 * @return bool
	 * @internal в будущем планируется вести лог с ошибками и из этой функции будет записываться ошибки
	 * @internal в запросах и дополнительо будет приниматься решения больше на посылать заросы на текуший URL
	 * @internal Пример: Если вернуло ошибку 500, то не повторять запрос
	 */
	private function http_code($http_code) {
		switch ((int)$http_code) {
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
	private function prepare_content($answer) {
		switch ($this->get_type_content()) {
			case 'file':
				break;
			case 'text':
				$answer = $this->encoding_answer_text($answer);
				break;
			case 'html':
				$answer = $this->encoding_answer_text($answer);
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
	private function encoding_answer_text($text = "") {
		if ($this->get_encoding_answer()) {
			$to = $this->get_encoding_name();
			$from = c_string_work::get_encoding_name($text);
			if ($from != $to) $text = iconv($from, $to, $text);
			return $text;
		} else return $text;
	}

	/**
	 * Проверяет заголовки на признак редиректа
	 * @return bool
	 */
	private function isRedirect(){
		return ($this->descriptor['info']['http_code'] == 301 || $this->descriptor['info']['http_code'] == 302);
	}
}
