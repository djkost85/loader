<?php
include_once "c_get_content.php";
include_once "c_string_work.php";
/**

get_random_proxy() Пулучить случайный прокси из $proxy_list
get_proxy_list() Получить список прокси в классе
check_proxy($proxy,$key) проверка прокси по средствам сайта ya.ru $proxy адрес прокси сервера, $key ключ в массиве для получения этого адреса
save_proxy_list() вид хранения и сохранения прокси адресов в файл или в БД.
checkAllFreeProxy() полная проверка всех адресов, для последующего сохранения рабочих адресов
get_proxy_list_in_file() получить список прокси из файла
check_all_proxy()//Проверяет весь список прокси по определенным критериям
set_method_get_proxy($new_method_get_proxy="random") метод получения прокси из списка random случайное значение, rent аренда адресов, с привязкой к определенному потоку
get_proxy_checker() // опрос страниц отвечающих за проверку прокси и выбор рабочей
getMyIP() получить IP парсера, используется для проверки анонимности прокси
free_proxy_list() освобождает файл/таблицу от блокировки для использования другими потоками
bloc_proxy_list() Блокирует файл/таблицу чтоб обезопасить от вне очередного доступа в многопоточном режиму
get_proxy($rent_code="",$site_for_use="") метод доступа, для получения прокси, атрибуты служат для использования в режиме rent, $rent_code код орендатора прокси, $site_for_use сайт на котором будет использоваться прокси
get_rented_proxy($rent_code,$site_for_use,$key_address=false) метод доступа для получения арендуемого прокси или для получения нового $key_address адрес для быстрого получения значения в файле конфигураций
search_rental_address($rent_code,$site_for_use,$key_address=false) поиск существующего значения
count_proxy_in_source() Подсчет количества прокси полученых с каждого сайта
remove_all_rent_from_code($rent_code) // удаление всех связей между арендатором и прокси
remove_rent($key_content,$key_renters,$without_saving=0) Удаление одной связи по адресу в массиве с данными
set_rented_proxy($rent_code,$site_for_use) Сдать в аренду прокси арендатору
remove_proxy_in_list($proxy) удаление прокси из списка
get_last_use_proxy() возвращает последний используемый прокси
download_proxy() // загрузка прокси с сайтов поставщиков
add_proxy($proxy)//добавляет в список прокси, если его там нет
**/
//      Не реализовал поддержку храниения в БД и допилить функции
//		Необходимо доделать фильтр для необходимого типа прокси need_anonim_proxy и проверку на передачу COOKIE
/**
 * Class c_proxy
 * Класс для получения актуального списка прокси, проверки работоспособности прокси с определенными сайтами
 * Распределяет адреса между потоками для исключения запросов с одного ip
 * Проверяет функционал который поддерживает прокси
 * Скачивает с представленных источников адреса прокси
 * @author Evgeny Pynykh <bpteam22@gmail.com>
 * @package get_content
 * @version 2.0
 */
class c_proxy
{

    /**
     * Массив состоящий из перечня прокси адресов и информации о них
     * Структура:
     * proxy_list['content'][индекс]["proxy"] адрес прокси сервера
     * proxy_list['content'][индекс]["source_proxy"]  источник прокси
     * proxy_list['content'][индекс]["type_proxy"]  протокол прокси HTTP SOCKS5
     * proxy_list['content'][индекс]["renters"]  нформация об арендаторе адреса прокси
     * proxy_list['content'][индекс]["renters"][индекс]["start_rent"] время начала аренды прокси адреса
     * proxy_list['content'][индекс]["renters"][индекс]["renter_code"] код аренды
     * proxy_list['content'][индекс]["renters"][индекс]["user_site"] сайт на котором используется прокси один прокси могут использовать несколько потоков, главное чтоб ресурсы были разные
     * proxy_list["time"] время последнего обновления
     * proxy_list["count"] количество подходящих прокси
     * proxy_list["url"] URL сайта на котором проверяется прокси
     * proxy_list[""check_word""][индекс] Проверочное слово которое должно быть в ответе с сервера- это регулярное выражение
     * proxy_list["need_function"][индекс] Необходимые функции которые должен поддерживать прокси
     * proxy_list["name_list"] Имя лисат этому имени будет соответствовать имя файла
     * @access protected
     * @var array
     */
    protected $proxy_list;
    /**
     * Адрес папки где храняться файлы для работы класса
     * @access protected
     * @var string
     */
    protected $dir_proxy_file;
    /**
     * Адреса для скачивания списков прокси
     * @access protected
     * @var array
     */
    protected $url_proxy_list;
    /**
     * Время актуальности прокси (в секундах) в профиле
     * @access protected
     * @var int
     */
    protected $storage_time;
    /**
     * Максимально время для аренды прокси, после истечения выдается другой адрес
     * @access protected
     * @var int
     */
    protected $rent_time;
    /**
     * Класс для тестирования и скачивания списков прокси
     * @access protected
     * @var c_get_content
     */
    protected $get_content;
    /**
     * Название метода хранения информации о прокси и адреса прокси серверов
     * В БД db
     * В текстовых файлах file
     * @access protected
     * @var string
     */
    protected $mode_save_data;
    /**
     * Имя текущего файла с адресами прокси и характеристиками
     * @access protected
     * @var string
     */
    protected $file_proxy_list;
    /**
     * Имя списка с адресами прокси и характеристиками
     * @access protected
     * @var string
     */
    protected $name_list;
    /**
     * Указатель на файл $file_proxy_list
     * @access protected
     * @var file_handle
     */
    protected $f_heandle_proxy_list;
    /**
     * Префикс для таблиц в БД хранящих данные о проски и их конфигурации
     * @access protected
     * @var string
     */
    protected $prefix_table_db;
    /**
     * Флаг для выставления опции проверки прокси черес специально зарезервированный сервер на работоспособность прокси
     * перед использованием
     * @access protected
     * @var bool
     */
    protected $need_check_proxy;
    /**
     * Флаг для проверки прокси на анонимность
     * @access protected
     * @var bool
     */
    protected $need_anonim_proxy;
    /**
     * Флаг для проверки функции cookie в прокси сервере
     * @access protected
     * @var bool
     */
    protected $need_proxy_cookie;
    /**
     * IP сервера на котором работает скрипт используется для проверки анонимности прокси
     * @access protected
     * @var string
     */
    protected $server_ip;
    /**
     * Основной URL на странуцу проверки функций прокси сервера
     * @access protected
     * @var string
     */
    protected $check_url_proxy;
    /**
     * Набор URL на странуцы проверки функций прокси сервера если не работает основной
     * @access protected
     * @var array
     */
    protected $check_url_proxy_array;
    /**
     * Массив для хранения адресов для ячеек с информацией об аренде, для осуществления быстрого доступа к данным
     * @access protected
     * @var array
     */
    protected $address_key_rent;
    /**
     * Метод получения адресов прокси
     * "random" получение случайных прокси, безконтрольное распределение адресов
     * "rent" аренда прокси(через один и то-же прокси не могут два потока опрашивать один сайт)
     * @access protected
     * @var string
     */
    protected $method_get_proxy;
    /**
     * Последний использованый прокси
     * @access protected
     * @var string
     */
    protected $last_use_proxy;
    /**
     * Флаг на разрешение даления прокси из списка
     * @access protected
     * @var bool
     */
    protected $remove_proxy;
    /**
     * Флаг подтверждающий блокировку таблици или файл на чтение и запись для текущего потока
     * @access protected
     * @var bool
     */
    protected $access_to_proxy_list;

 /**
  * Конструктор инициализируте переменные значениями по умолчанию
  * @param bool $update флаг при инициализации обновить принудительно список прокси или нет
  * @return \c_proxy
  */
 function __construct($update=true)
{
	$this->storage_time            =86400;
	$this->rent_time               =3600;
	$this->url_proxy_list 		   =array(
										    "cool-proxy.net"=>"http://cool-proxy.net/proxies/http_proxy_list/page:",
										    "seprox.ru"=>"http://seprox.ru/ru/proxy_filter/0_0_0_0_0_0_0_0_0_"
										 );
	$this->get_content             = new c_get_content();
	$this->get_content->set_type_content('html');
	$this->set_method_get_proxy("random");
	$this->dir_proxy_file          ="proxy_files";
	$this->prefix_table_db         ="proxy_list";
	$this->check_url_proxy         ="http://pchecker.vrozetke.com/proxy_checker/anonimCheck.php";
	$this->check_url_proxy_array[] ="http://pchecker.vrozetke.com/proxy_checker/anonimCheck.php";
	$this->check_url_proxy_array[] ="http://free-lance.dyndns.info/proxy_checker/anonimCheck.php";
	$this->check_url_proxy_array[] ="http://kingnothing.koding.com/proxy_checker/anonimCheck.php";
	$this->proxy_list              =array();
	$this->need_check_proxy        =1;
	$this->last_use_proxy          =0;
	$this->mode_save_data          ='file';
	$this->name_list               ='all';
	$this->select_proxy_list($this->name_list);
	$this->set_mode_save_data("file");
	$this->set_remove_proxy(1);
	if($update)$this->update_proxy_list($this->name_list);
	//else $this->proxy_list=$this->get_proxy_list_file_without_lock();
}

/**
 * Закрывает все соединения перед уничтожением объекта
 */
function __destruct()
{
	$this->close_proxy_list();
    unset($this->get_content);
}

/**
 * Обновляет текущий список прокси адресов если истекло время хранения
 * @param bool $force Принудительное обновление
 * @return array Обновленный список прокси
 */
public function update_proxy($force=false)
{
	$proxy=$this->get_proxy_list();
	$this->free_proxy_list();
	$end_term_proxy=time()-$this->storage_time;
	if($proxy && isset($proxy['content']) && count($proxy['content']) && $proxy['time']>$end_term_proxy && !$force)
	{
		$this->proxy_list=$proxy;
		return $this->proxy_list;
	}
	$this->check_all_proxy();
	return $this->proxy_list;
}

    /**
     * @param bool $new_remove_proxy
     */
public function set_remove_proxy($new_remove_proxy)
{
	$this->remove_proxy=$new_remove_proxy;
}

    /**
     * @param bool $new_access_to_proxy_list
     */
public function set_access_to_proxy_list($new_access_to_proxy_list)
{
	$this->access_to_proxy_list=$new_access_to_proxy_list;
}

public function get_access_to_proxy_list()
{
	return $this->access_to_proxy_list;
}

public function get_remove_proxy()
{
	return $this->remove_proxy;
}

    /**
     * Получение абсолютного адреса к папке гда лежат файлы конфигурации прокси листов
     * @return string
     */
public function get_proxy_storage()
{
	return dirname(__FILE__)."/".$this->dir_proxy_file."/";
}

    /**
     * Возвращает ip сервера с которого запущен скрипт или false
     * @return bool|string
     */
public function get_server_ip()
{
	if(isset($this->server_ip)) return $this->server_ip;
	$this->get_content->set_use_proxy(0);
	$this->get_content->set_type_content('html');
	$this->get_content->set_mode_get_content('single');
	for($i=0;$i<10;$i++)
	{
		$this->get_content->get_content("http://2ip.ru/");
		$answer=$this->get_content->get_answer();
		$reg="/<span>\s*Ваш IP адрес:\s*<\/span>\s*<big[^>]*>\s*(?<ip>[^<]*)\s*<\/big>/iUm";
		if(preg_match($reg, $answer,$match)) break;
	}
	if(!$match['ip']) return false;
	return $this->server_ip=$match['ip'];
}

    /**
     * @param bool $new_need_proxy_cookie
     */
public function set_need_proxy_cookie($new_need_proxy_cookie)
{
	$this->need_proxy_cookie=$new_need_proxy_cookie;
}

    /**
     * Поиск сервера из каталога для проверки функций прокси
     * @param string $check_url_proxy_array  url сервера для проверки функций прокси, если не работает выберает другой из каталога
     * @return string возвращает рабочий url для проверки прокси
     */
public function get_proxy_checker($check_url_proxy_array="")
{
	if($check_url_proxy_array==="") $check_url_proxy_array=$this->check_url_proxy_array;
	$get_сontent=new c_get_content();
	$get_сontent->set_use_proxy(0);
	$get_сontent->set_type_content('text');
	$get_сontent->set_mode_get_content('multi');
	$get_сontent->set_count_multi_curl(1);
	$get_сontent->set_min_size_answer(0);
	$get_сontent->get_content($check_url_proxy_array);
	$answer=$get_сontent->get_answer();
	foreach($answer as $key => $value)
	{
		foreach($value as $subKey => $sub_value)
		{
			if(preg_match("/yandex/i",$sub_value))
			{
				$this->check_url_proxy=$check_url_proxy_array[$key];
				return $this->check_url_proxy;
			}
		}
	}
	exit(__FILE__." no checker");
}

    /**
     * Загружает список прокси из внешних источников
     * @return array массив с адресами прокси
     */
public function download_proxy()
{
	$proxy['content']=array();
	foreach ($this->url_proxy_list as $key_proxy_list => $value_proxy_list)
	{
		$tmp_proxy=$this->download_proxy_site($key_proxy_list,$value_proxy_list);
		if(is_array($tmp_proxy) && count($tmp_proxy))
		{
			$proxy['content']=array_merge($proxy['content'],$tmp_proxy['content']);
		}
	}
	return $proxy;
}

    /**
     * Загружает из канкретного сайта списки прокси
     * @param string $key_proxy_list название сайта источника прокси адресов
     * @param string $value_proxy_list ссыка на страницу с прокси
     * @return array прокси адресы
     */
public function download_proxy_site($key_proxy_list,$value_proxy_list)
{
	$get_content= new c_get_content();
	$proxy=array();
	switch($key_proxy_list)
	{
		case "cool-proxy.net":
		//break;
			$get_content->set_type_content("html");
			if(!$page=$get_content->get_content($value_proxy_list."1"."/sort:working_average/direction:asc"))
			{
				$get_content->set_in_cache(1);
				$page=$get_content->get_content($value_proxy_list."1"."/sort:working_average/direction:asc");
				$get_content->set_in_cache(0);
			}
			$reg="#/proxies/http_proxy_list/sort:working_average/direction:asc/page:(?<count_page>\d*)\"#iUm";
			if(preg_match_all($reg, $page, $matches))
			{
				$page_array=$matches['count_page'];
				rsort($page_array);
				$count_page=$page_array[0];
			}
			else
			{
				$count_page=10;
			}
			for($i=1;$i<$count_page;$i++)
			{
				if(!$page=$get_content->get_content($value_proxy_list.$i."/sort:working_average/direction:asc"))
				{
					$get_content->set_in_cache(1);
					$page=$get_content->get_content($value_proxy_list.$i."/sort:working_average/direction:asc");
					$get_content->set_in_cache(0);
				}
				sleep(1);
				$reg="#<td\s*style=\"text.align.left.\s*font.weight.bold.\">(.*)</td>\s*<td>(\d+)</td>#iUm";
				if(preg_match_all($reg, $page, $matches))
				{
					for($j=0;$j<count($matches[1]);$j++)
					{
						$reg="/<span class=\"\d+\">(\d+)<\/span>/iU";
						if(preg_match_all($reg, $matches[1][$j], $matches_proxy))
						{
							$reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
							$is_ip=$matches_proxy[1][0].".".$matches_proxy[1][1].".".$matches_proxy[1][2].".".$matches_proxy[1][3].":".$matches[2][$j];
							if(preg_match($reg,$is_ip))
							{
								$tmp_array['proxy']=trim($is_ip);
								$tmp_array["source_proxy"]=$key_proxy_list;
								$tmp_array["type_proxy"]=$this->set_name_type_proxy('HTTP');
								$proxy['content'][]=$tmp_array;
							}
						}
					}
				}
				else
				{
					break;
				}
			}
			break;
		case "proxyhub.ru":
			$get_content->set_type_content("text");
			$page=$get_content->get_content($value_proxy_list);
			$reg="/\s*(?<proxy>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}),(?<port>\d{1,5}),(?<type_proxy>HTTP|SOCKS5),\s*/iUm";
			//$iTMP=0;
			if(preg_match_all($reg, $page, $matches_proxy))
			{
				foreach ($matches_proxy['proxy'] as $key => $value)
				{
					//$reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
					//$is_ip=$value;
					//if(preg_match($reg,$is_ip))
					//{
					//$iTMP++;
					//if($iTMP>10) break;
						$tmp_array['proxy']=$matches_proxy['proxy'][$key].":".$matches_proxy['port'][$key];
						$tmp_array["source_proxy"]=$key_proxy_list;
						$tmp_array["type_proxy"]=$this->set_name_type_proxy($matches_proxy['type_proxy'][$key]);
						$proxy['content'][]=$tmp_array;
					//}
				}		
			}
			break;
		case "seprox.ru":
		//break;
			$i=0;
			$get_content->set_type_content("html");
			if(!$page=$get_content->get_content($value_proxy_list.$i.".html"))
			{
				$get_content->set_in_cache(1);
				$page=$get_content->get_content($value_proxy_list.$i.".html");
				$get_content->set_in_cache(0);
			}
			if(!$page)
			{
				break;
			}
			if(preg_match("/<div class=\"countResult\">\s* Всего найдено.\s*(\d+)\s*<\/div>/iU", $page, $match)) $count_page=ceil($match[1]/15);
			else $count_page=30;
			do {
				if(!preg_match_all("/<tr class=\"proxyStr\">\s*<td>\s*<script type=\"text\/javascript\">\s*(?<js>[^<]*)\s*<\/script>\s*<\/td>\s*<td>\s*(?<type_proxy>.*)\s*<\/td>/iUm", $page, $matches_secret_code))
				{
					break;
				}
				foreach ($matches_secret_code['js'] as $key_secret_code => $value_secret_code)
				{
					if(!preg_match("/Proxy=String.fromCharCode\(([^\)]*)\)/iU", $value_secret_code,$match_secret_array))
					{
						break;
					}
					$str_secret_code=$value_secret_code;
					$l=explode(",",$match_secret_array[1]);
					foreach ($l as $key => $value)
					{
						$litera[$key]=chr($value);
					}
					foreach ($litera as $key_litera => $value_litera)
					{
						$str_secret_code=preg_replace("/Proxy\[".$key_litera."\]/iU",$value_litera, $str_secret_code);
					}
					$str_secret_code=str_replace("([]+[]+{})[!+[]+!+[]]", "a", $str_secret_code);
					$str_secret_code=str_replace("(![]+[])[+!+[]]", "b", $str_secret_code);
					$str_secret_code=str_replace("([![]]+{})[+!+[]+[+[]]]", "c", $str_secret_code);
					$str_secret_code=str_replace("([]+[]+[][[]])[!+[]+!+[]]", "d", $str_secret_code);
					$str_secret_code=str_replace("(!![]+[])[!+[]+!+[]+!+[]]", "e", $str_secret_code);
					$str_secret_code=str_replace("(![]+[])[+[]]", "f", $str_secret_code);
					$str_secret_code=str_replace("([![]]+[][[]])[+!+[]+[+[]]]", "i", $str_secret_code);
					$str_secret_code=str_replace("([]+[]+[][[]])[+!+[]]", "n", $str_secret_code);
					$str_secret_code=str_replace("([]+[]+{})[+!+[]]", "o", $str_secret_code);
					$str_secret_code=str_replace("(!![]+[])[+!+[]]", "r", $str_secret_code);
					$str_secret_code=str_replace("(!![]+[])[+[]]", "t", $str_secret_code);
					$str_secret_code=str_replace("(!![]+[])[!+[]+!+[]]", "u", $str_secret_code);
					$str_secret_code=str_replace("(+{}+[]+[]+[]+[]+{})[+!+[]+[+[]]]", " ", $str_secret_code);
					$str_secret_code=str_replace("+++", "***", $str_secret_code);
					$str_secret_code=str_replace("+", "", $str_secret_code);
					$str_secret_code=str_replace("***", "+", $str_secret_code);
					$reg="/(?:\(|\+)(\w+)/";
					preg_match_all($reg, $str_secret_code,$matches_secret_var);
					$ip="";
					foreach ($matches_secret_var[1] as $key_ip => $value_ip)
					{
						$reg="/$value_ip='([^']*)'/";
						if(preg_match($reg, $str_secret_code, $match_ip)) $ip.=$match_ip[1];
					}
					//setLog(__FILE__,__LINE__,"IP");
					//setLog(__FILE__,__LINE__,$ip);
					$reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
					$is_ip=$ip;
					if(preg_match($reg,$is_ip))
					{
						$tmp_array['proxy']=trim($is_ip);
						$tmp_array["source_proxy"]=$key_proxy_list;
						$tmp_array["type_proxy"]=$this->set_name_type_proxy($matches_secret_code['type_proxy'][$key_secret_code]);
						$proxy['content'][]=$tmp_array;
					}//setLog(__FILE__,__LINE__,"Не канает этот IP ".$is_ip);
				}
				$i++;
				if(!$page=$get_content->get_content($value_proxy_list.$i.".html"))
				{
					$get_content->set_in_cache(1);
					$page=$get_content->get_content($value_proxy_list.$i.".html");
					$get_content->set_in_cache(0);
				}
				if(!$page)
				{
					if($i<$count_page) continue;
					else break;
				}
			} while($i<$count_page);
			break;
	}		
	unset($get_content);
	return $proxy;
}

    /**
     * Устанавливает фильтр для необходимых прокси
     * @param string $new_name_type_proxy протокол через который работает прокси
     * @return string имя протокола
     */
private function set_name_type_proxy($new_name_type_proxy="http")
{
	if(preg_match("#https#i", $new_name_type_proxy))
	{
		return "https";
	}
	elseif(preg_match("#http#i", $new_name_type_proxy))
	{
		return "http";
	}
	elseif(preg_match("#socks#i", $new_name_type_proxy))
	{
		return "socks";
	}
	else
	{
		return "http";
	}
}

    /**
     * @param string $new_method_get_proxy тип получения прокси адреса
     */
public function set_method_get_proxy($new_method_get_proxy="random")
{
	switch ($new_method_get_proxy)
	{
		case 'random':
			$this->method_get_proxy='random';
			break;
		case 'rent':
			$this->method_get_proxy='rent';
			break;
		
		default:
			# code...
			break;
	}
}

    /**
     * Установка фильтра на анонимность прокси
     * @param bool $new_need_anonim_proxy флаг для фильрации функций прокси
     */
public function set_need_anonim_proxy($new_need_anonim_proxy=true)
{
	$this->need_anonim_proxy=$new_need_anonim_proxy;
}

    /**
     * Установка флага на проверку прокси перед использованием
     * @param bool $new_need_check_proxy
     */
public function set_need_check_proxy($new_need_check_proxy=true)
{
	$this->need_check_proxy=$new_need_check_proxy;
}

public function get_url_proxy_list()
{
	return $this->url_proxy_list;
}

    /**
     * Флаг метода хранения прокси
     * @param string $new_mode_save_data тип хранения прокси file|db
     * @return bool
     */
public function set_mode_save_data($new_mode_save_data)
{
	switch ($new_mode_save_data)
	{
		case 'file':
			$this->mode_save_data="file";
            return true;
			break;
		case 'db':
			$this->mode_save_data="db";
            return true;
			break;
		
		default:
			return false;
			break;
	}
}

    /**
     * Открывает прокси лист
     * @param string $proxy_list имя прокси листа, по умолчанию текущий прокси лист
     */
public function open_proxy_list($proxy_list='')
{
	$this->close_proxy_list();
	$this->f_heandle_proxy_list=fopen($this->file_proxy_list,"c+");
}

    /**
     *Закрывает текущий прокси лист
     */
public function close_proxy_list()
{
	$this->free_proxy_list();
	fclose($this->f_heandle_proxy_list);
    unset($this->f_heandle_proxy_list);
	unset($this->proxy_list);
}

    /**
     * Освобождает прокси лист от блокировки текущим процессом
     * @return bool
     */
public function free_proxy_list()
{
	if(!$this->get_access_to_proxy_list()) return true; // проверяет занят ли этим потоком файл?
	flock($this->f_heandle_proxy_list,LOCK_UN);
	$this->set_access_to_proxy_list(0);
    return false;
}

    /**
     * Блокирует прокси лист от остальных потоков
     * @return bool
     */
public function bloc_proxy_list()
{
	if($this->get_access_to_proxy_list()) return true; // проверяет не блокирован ли этим потоком файл?
	do{
		if(flock($this->f_heandle_proxy_list,LOCK_EX))
		{
			$this->set_access_to_proxy_list(1);
			break;
		}
		setLog(__FILE__,__LINE__,"файл занят");
		sleep(1);
		}while(true);
    return false;
}

    /**
     * Возвращает случайный прокси из текцщего списка
     * @return bool|string
     */
public	function get_random_proxy()
{
	$proxy_list=$this->get_proxy_list_file_without_lock();
	$this->free_proxy_list();
	$count_proxy=count($proxy_list['content']);
	for($i=0;$i<$count_proxy;$i++)
	{
		$proxy=array();
		for($j=0;$j<10;$j++)
		{
			$proxy[$j]=trim($proxy_list['content'][array_rand($proxy_list['content'])]["proxy"]);
		}
		if($good_proxy=$this->check_proxy($proxy))
		{
			if(is_array($good_proxy))
			{
				return current($good_proxy);
			}
			elseif($good_proxy)
			{
				return $good_proxy;
			}
			else
			{
				return 0;
			}
		}
	}
	return "";
}

    /**
     * Возвращает список прокси не взирая на блокировку (только для чтения)
     * @return array список прокси адресов
     */
public function get_proxy_list_file_without_lock()
{
	return json_decode(file_get_contents($this->file_proxy_list),true);
}

    /**
     * Открывает текущий прокси лист с блокировкой
     * @return array прокси лист
     */
public function get_proxy_list_in_file()
{
	$this->bloc_proxy_list();
	$json_proxy=file_get_contents($this->file_proxy_list);
	$this->proxy_list=json_decode($json_proxy,true);
	return $this->proxy_list;
}

    /**
     * Выдает потоку прокси адрес
     * @param string $rent_code код потока арендатора
     * @param string $site_for_use сайт на который будут посылать запросы
     * @return bool|string
     */
public function get_proxy($rent_code="",$site_for_use="")
{
	switch ($this->method_get_proxy)
	{
		case 'random':
			$this->last_use_proxy=$this->get_random_proxy();
			return $this->last_use_proxy;
			break;
		case 'rent':
			if($rent_code=="" || $site_for_use=="") return false;
			$this->last_use_proxy=$this->get_rented_proxy($rent_code,$site_for_use);
			return $this->last_use_proxy;
			break;
		
		default:
			return false;
			break;
	}
}

    /**
     * Добавляет в текущий список новый прокси адрес
     * @param string $proxy адрес прокси сервера
     * @param string $type_proxy протокол прокси
     * @param string $source_proxy источник прокси
     * @return bool
     */
public function add_proxy($proxy,$type_proxy="http",$source_proxy="")
{
	if(!$result=$this->search_proxy_in_list($proxy))
	{
		$tmp_array['proxy']=trim($proxy);
		$tmp_array["source_proxy"]=$source_proxy;
		$tmp_array["type_proxy"]=$type_proxy;
		$this->proxy_list['content'][]=$tmp_array;
		$this->save_proxy_list($this->proxy_list);
	}
    else return false;
}

    /**
     * Получить в аренду прокси адрес
     * @param $rent_code код арендатора
     * @param $site_for_use сайт на который будут поступать запросы
     * @param bool $key_address адрес для быстрого поиска прокси для снятия аренды или удаления
     * @return bool|string
     */
public function get_rented_proxy($rent_code,$site_for_use,$key_address=false)
{
	//Максимальное время ожидания сутки
	for($i=0;$i<1440;$i++)
	{
		$this->proxy_list=$this->get_proxy_list_in_file();
		if($ip_proxy=$this->search_rental_address($rent_code,$site_for_use,$key_address)) return $ip_proxy["proxy"];
		if($ip_proxy=$this->set_rented_proxy($rent_code,$site_for_use))
		{
			//сюда нужно поместить сохранение адреса быстрого доступа к данным о аренде прокси
			$this->save_proxy_list($this->proxy_list);
			return $ip_proxy["proxy"];
		}
		// все прокси заняты, записываем изменения и освобождаем файл. ждем когда освободится
		$this->save_proxy_list($this->proxy_list);
		//setLog(__FILE__,__LINE__," All Proxy busy");
		sleep(60);
	}
	return 0;
}

    /**
     * Поиск прокси по коду арендатора
     * @param string $rent_code код арендатора
     * @param string $site_for_use сайт на который отправляют запросы черз прокси
     * @param bool|array $key_address адрес для быстрого доступа
     * @return bool|string
     */
public function search_rental_address($rent_code,$site_for_use,$key_address=false)
{
	$this->proxy_list=$this->get_proxy_list_in_file();
	// если задан адрес в где лежит информация об аренде, проверяем информацию
	if($key_address)
	{
		if($this->proxy_list['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["renter_code"]==$rent_code && $this->proxy_list['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["user_site"]==$site_for_use)
		{
			$end_term_rent=time()-$this->rent_time;
			// проверяем время аренды прокси
			if($key_address["start_rent"]>$end_term_rent) return $this->proxy_list['content'][$key_address['key_content']]["proxy"];
            else
            {
                $this->remove_rent($key_address['key_content'],$key_address['key_renters']);
                return false;
            }
        }
    }
    $end_term_rent=time()-$this->rent_time;
    // Если нет , то ищем в ручную
    foreach ($this->proxy_list['content'] as $key_content => $value_content)
    {
        //$this->proxy_list['content'][$key_content]["renters"] === $value_content["renters"]
        foreach($value_content["renters"] as $key_renters => $value_renters)
        {
            //$this->proxy_list['content'][$key_content]["renters"][$key_renters]["renter_code"] === $valueRentCode["renter_code"]
            //$this->proxy_list['content'][$key_content]["renters"][$key_renters]["user_site"] === $valueRentCode["user_site"]
            if($value_renters["renter_code"]==$rent_code)// && $value_renters["user_site"]==$site_for_use)
            {
                // проверяем время аренды прокси
		        if($value_renters["start_rent"]>$end_term_rent)
		        {
		        	$return_array["proxy"]=$value_content["proxy"];
		        	$return_array["key_content"]=$key_content;
		        	$return_array["key_renters"]=$key_renters;
		        	return $return_array;
		        }
		        else
		        {
		        	$this->remove_rent($key_content,$key_renters);
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
public function search_proxy_in_list($proxy)
{
	$this->proxy_list=$this->get_proxy_list_in_file();
	foreach ($this->proxy_list['content'] as $key_content => $value_content)
	{
		if($value_content["proxy"]==$proxy)
		{
			$return_array['proxy']=$value_content["proxy"];
			$return_array['key_content']=$key_content;
			return $return_array;
		}
	}
	unset($value_content);
	return false;
}

    /**
     * Ставит пометку в списке прокси что этот прокси арендован
     * @param $rent_code код арендатора
     * @param $site_for_use сайт на который будут посылать запросы через прокси
     * @param string $proxy прокси адрес
     * @return array|bool
     */
protected function set_rented_proxy($rent_code,$site_for_use,$proxy="")
{
	$this->proxy_list=$this->get_proxy_list_in_file();
	if($proxy)
	{
		$result=$this->search_proxy_in_list($proxy);
		$tmp_data['start_rent']=(string)time();
		$tmp_data['renter_code']=(string)$rent_code;
		$tmp_data['user_site']=(string)$site_for_use;
		$this->proxy_list['content'][$result['key_content']]["renters"][]=$tmp_data;
	}
	else
	{
		shuffle($this->proxy_list['content']);
		foreach ($this->proxy_list['content'] as $key_content => $value_content)
		{
			$proxy_use_this_site=0;
			foreach($value_content["renters"] as $key_renters => $value_renters)
			{
				if($value_renters["user_site"]==$site_for_use)
				{
					$proxy_use_this_site=1;
					break;
				}
			}
			unset($value_renters);
			//Если через этот прокси не опрашивается сайт $site_for_use, то привяжем поток к этому прокси
			if(!$proxy_use_this_site)
			{
				$tmp_data['start_rent']=(string)time();
				$tmp_data['renter_code']=(string)$rent_code;
				$tmp_data['user_site']=(string)$site_for_use;
				$this->proxy_list['content'][$key_content]["renters"][]=$tmp_data;
				end($this->proxy_list['content'][$key_content]["renters"]);
				$return_array["proxy"]=$value_content["proxy"];
				$return_array["key_content"]=$key_content;
				$return_array["key_renters"]=key($this->proxy_list['content'][$key_content]["renters"]);
				return $return_array;
			}
		}
		unset($value_content);
		return false;
	}
}

    /**
     * Удаляет аренды с всех прокси для текущего арендатора
     * @param string $rent_code код арендатора
     * @return bool
     */
public function remove_all_rent_from_code($rent_code)
{
	$this->proxy_list=$this->get_proxy_list_in_file();
	if(!isset($this->proxy_list['content'])) return false;
	foreach ($this->proxy_list['content'] as $key_content => $value_content)
	{
		//$this->proxy_list['content'][$key_content]["renters"] === $value_content["renters"]
		foreach($value_content["renters"] as $key_renters => $value_renters)
		{
			//$this->proxy_list['content'][$key_content]["renters"][$key_renters]["renter_code"] === $valueRentCode["renter_code"]
			//$this->proxy_list['content'][$key_content]["renters"][$key_renters]["user_site"] === $valueRentCode["user_site"]
			if($value_renters["renter_code"]==$rent_code)
			{
				$this->remove_rent($key_content,$key_renters,1);
			}
		}
		unset($value_renters);
	}
	unset($value_content);
	$this->save_proxy_list($this->proxy_list);
    return true;
}

    /**
     * Убирает все аренды из текущего списка прокси
     */
public function remove_all_rent()
{
	$this->proxy_list=$this->get_proxy_list_in_file();
	foreach ($this->proxy_list['content'] as $key_content => $value_content)
	{
		//$this->proxy_list['content'][$key_content]["renters"] === $value_content["renters"]
		foreach($value_content["renters"] as $key_renters => $value_renters)
		{
			//$this->proxy_list['content'][$key_content]["renters"][$key_renters]["renter_code"] === $valueRentCode["renter_code"]
			//$this->proxy_list['content'][$key_content]["renters"][$key_renters]["user_site"] === $valueRentCode["user_site"]
			if($value_renters["renter_code"])
			{
				$this->remove_rent($key_content,$key_renters,1);
			}
		}
		unset($value_renters);
	}
	unset($value_content);
	$this->save_proxy_list($this->proxy_list);
}

    /**
     * Удаляет и списка прокси аренду по ключу в списке и коду арендатора
     * @param int $key_content ключ в списке
     * @param int $key_renters ключ арендатора в списке
     * @param bool $without_saving с сохранением в файл
     * @return bool
     */
public function remove_rent($key_content,$key_renters,$without_saving=false)
{
	if(!count($this->proxy_list)) $this->proxy_list=$this->get_proxy_list_in_file();
	if(isset($this->proxy_list['content'][$key_content]["renters"][$key_renters]))
	{
		unset($this->proxy_list['content'][$key_content]["renters"][$key_renters]);
        if(!$without_saving) $this->save_proxy_list($this->proxy_list);
        return true;
    }
	else return false;
}

    /**
     * Убирает аренду по коду арендатора и сайту на который посылают запрос
     * @param $rent_code код арендатора
     * @param $site_for_use сайт на который посылают запросы
     * @return bool
     */
public function remove_rent_to_code_site($rent_code,$site_for_use)
{
	if($result_array=$this->search_rental_address($rent_code,$site_for_use))
	{
		$this->remove_rent($result_array['key_content'],$result_array['key_renters']);
        return true;
	}
    else return false;
}

    /**
     * Удаляет прокси из текущего списка
     * @param string $proxy прокси адрес
     * @return bool
     */
public function remove_proxy_in_list($proxy)
{
	if($this->remove_proxy)
	{
		$this->proxy_list=$this->get_proxy_list_in_file();
		foreach ($this->proxy_list['content'] as $key_content => $value_content)
		{
			if($this->proxy_list['content'][$key_content]['proxy']==$proxy)
			{
				unset($this->proxy_list['content'][$key_content]);
				break;
			}
		}
		unset($value_content);
		$this->save_proxy_list($this->proxy_list);
		return true;
	}
	else
	{
		return false;
	}
}

    /**
     * @return array|bool
     */
    public	function get_proxy_list()
{
	if(isset($this->proxy_list) && count($this->proxy_list) && ($this->proxy_list['time']>(time()-3600)))	return $this->proxy_list;
	switch ($this->mode_save_data)
	{
		case 'file':
			if(!$proxy=$this->get_proxy_list_in_file())
			{
				return false;
			}
			return $this->proxy_list=$proxy;
			break;
		case 'db':
			
			break;
		default:
			# code...
			break;
	}
	return $this->proxy_list;
}
public function get_last_use_proxy()
{
	return $this->last_use_proxy;
}
/* TODO: Обязательный рефакторинг функции check_proxy */
    /**
     * Проверяет прокси адрес
     * @param string|array $proxy прокси адрес
     * @param string $method ХЗ
     * @param array $data ХЗ
     * @return array|int|string
     */
public function check_proxy($proxy,$method="url",$data=array('url'=>"http://ya.ru"))//function
{
	if(!$this->need_check_proxy) return $proxy;
	if($method=='function')
	{
		$this->get_server_ip();
		if(!$url=$this->get_proxy_checker()) return false;
	}
	if(is_string($proxy))
	{
		if(preg_match("/\s*\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}(:+\d+)\s*/",$proxy))
		{
			$this->get_content->set_mode_get_content('single');
			//$this->get_content->set_proxy($proxy);
			$this->get_content->set_option_to_descriptor($this->get_content->get_descriptor(),CURLOPT_PROXY,$proxy);
			$this->get_content->set_check_answer(0);
			$get=array();
//			if($this->need_anonim_proxy) $get[]="ip=".$this->server_ip;
//			if($this->need_proxy_cookie) $get[]="cookie";
			$query="";
			for($i=0;$i<count($get);$i++)
			{
				if(($i+1)<count($get))
				{
					$query.=$get[$i]."&";
				}
				else
				{
					$query.=$get[$i];
				}
			}
			$answer_content=$this->get_content->get_content($url);
			if(preg_match("/yandex/i",$answer_content))
			{
				return $proxy;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	if(is_array($proxy))
	{
		$good_proxy=array();
		$this->get_content->set_mode_get_content('multi');
		$this->get_content->set_count_multi_curl(count($proxy));
		reset($proxy);
		for($i=0;current($proxy)!==false;$i++)
		{
			$this->get_content->set_option_to_descriptor($this->get_content->get_descriptor_array(),CURLOPT_PROXY,current($proxy),$i);
			//$this->get_content->set_proxy(current($proxy),$i);
			next($proxy);
		}
		$this->get_content->set_check_answer(0);
		$get=array();
		//if($this->need_anonim_proxy) $get[]="ip=".$this->server_ip;
		//if($this->need_proxy_cookie) $get[]="cookie";
		$query="";
		for($i=0;$i<count($get);$i++)
		{
			if(($i+1)<count($get))
			{
				$query.=$get[$i]."&";
			}
			else
			{
				$query.=$get[$i];
			}
		}
		$answer_content=$this->get_content->get_content($this->check_url_proxy."?".$query);
		reset($proxy);
		foreach ($answer_content as $key => $value)
		{
			if(preg_match("/yandex/i",$value))
			{
				$good_proxy[key($proxy)]=current($proxy);
			}
			next($proxy);
		}
		unset($value);
		if(count($good_proxy))
		{
			return $good_proxy;
		}
		else
		{
			return false;
		}

	}
}
    /* TODO: Обязательный рефакторинг функции check_proxy_array_to_site */
private function check_proxy_array_to_site($proxy,$url,$check_word)
{
		$this->get_content->set_mode_get_content('multi');
		$this->get_content->set_count_multi_curl(count($proxy));
		reset($proxy);
		for($i=0;current($proxy)!==false;$i++)
		{
			$this->get_content->set_option_to_descriptor($this->get_content->get_descriptor_array(),CURLOPT_PROXY,current($proxy),$i);
			next($proxy);
		}
		$this->get_content->set_check_answer(0);
		$answer_content=$this->get_content->get_content($url);
		reset($proxy);
		$good_proxy=array();
		foreach ($answer_content as $key => $value)
		{
			$test_count=0;
			$count_good_check=0;
			foreach ($check_word as $value_check_word)
			{
				$test_count++;
				if(preg_match($value_check_word,$value))
				{
					$count_good_check++;
				}
			}
			unset($value_check_word);
			if($count_good_check==$test_count)
			{
				$good_proxy[key($proxy)]=current($proxy);
			}
			next($proxy);
		}
		unset($value);
		if(count($good_proxy))
		{
			return $good_proxy;
		}
		else
		{
			return 0;
		}
}
    /* TODO: Обязательный рефакторинг функции check_proxy_array_to_function */
private function check_proxy_array_to_function($proxy,$need_function)
{
		if(!$url=$this->get_proxy_checker()) return 0;
		$this->get_content->set_mode_get_content('multi');
		$this->get_content->set_count_multi_curl(count($proxy));
		reset($proxy);
		for($i=0;current($proxy)!==false;$i++)
		{
			$this->get_content->set_option_to_descriptor($this->get_content->get_descriptor_array(),CURLOPT_PROXY,current($proxy),$i);
			//$this->c_get_content->set_proxy(current($proxy),$i);
			next($proxy);
		}
		$this->get_content->set_check_answer(0);
		$get=array();
		if(array_search("anonim",$need_function))
		{
			if($this->get_server_ip()) $get[]="ip=".$this->server_ip;
		}
		if(array_search("cookie",$need_function)) $get[]="cookie";
		$query="";
		for($i=0;$i<count($get);$i++)
		{
			if(($i+1)<count($get))
			{
				$query.=$get[$i]."&";
			}
			else
			{
				$query.=$get[$i];
			}
		}
		$answer_content=$this->get_content->get_content($url."?".$query);
		reset($proxy);
		$good_proxy=array();
		foreach ($answer_content as $key => $value)
		{
			if(preg_match("/yandex/i",$value))
			{
				$good_proxy[key($proxy)]=current($proxy);
			}
			next($proxy);
		}
		unset($value);
		if(count($good_proxy))
		{
			return $good_proxy;
		}
		else
		{
			return 0;
		}
}

    /**
     * Сохраняет прокси список
     * @param array|bool $proxy_list сохраняемый список прокси
     */
public function save_proxy_list($proxy_list=false)
{
	if(!is_array($proxy_list)) $proxy_list=$this->proxy_list;
	/*if(!isset($proxy_list['content']))
	{
		$this->free_proxy_list();
		return 0;
	}*/
	$proxy_list['time']=time();
	$proxy_list['count']=count($proxy_list['content']);
	$this->proxy_list=$proxy_list;
	switch ($this->mode_save_data)
	{
		case 'file':
			$json_proxy=json_encode($proxy_list);
			$this->bloc_proxy_list();
			file_put_contents($this->file_proxy_list, '');
			rewind($this->f_heandle_proxy_list);
			fwrite($this->f_heandle_proxy_list, $json_proxy);
			$this->free_proxy_list();
			break;
		case 'db':
		//пока не реализована
		/*	if(!mysql_ping())
			{
				//echo "No connect DB";
				return 0;
			}
			$query="CREATE TABLE IF NOT EXISTS `".$this->table_proxy_list_db."` (
					  `id` INT NOT NULL AUTO_INCREMENT,
					  `addressProxy` TEXT,
					  `timeAdd` INT,
					  `type_proxy` TEXT,
					  PRIMARY KEY  (`id`)
					);";
			if(!mysql_query($query)) return 0;
			$query="INSERT INTO '".$this->table_proxy_list_db."' (`addressProxy`, `timeAdd`, `type_proxy`) VALUE ";
			!!if($count=count())!!))
			{
				for($i=0;$i<$count;$i++)
				{
					if($count==($i+1))
					{
						$query.="('".$proxy_list['proxy'][$i]."', ".$proxy_list['time'].", '".$this->type_proxy."') ";
					}
					else
					{
						$query.="('".$proxy_list['proxy'][$i]."', ".$proxy_list['time'].", '".$this->type_proxy."'), ";
					}
				}
				if(!mysql_query($query)) return 0;
			}
			else
			{
				return 0;
			}
			*/
			break;
		default:
			# code...
			break;
	}
}

    /**
     * Считает количество прокси пришедшие с определенных источников
     * @return array
     */
private function count_proxy_in_source()
{
	foreach ($this->proxy_list['content'] as $key => $value)
	{
		$source_proxy[$key]=$this->proxy_list['content'][$key]["source_proxy"];
	}
	unset($value);
	$result=array();
	foreach ($this->url_proxy_list as $key => $value)
	{
		$result[$key]=count(array_keys($source_proxy,$key)); 
	}
	unset($value);
	return $result;
}

    /**
     * Создает профиль прокси адресов
     * @param string $name_list название
     * @param string $check_url проверочный URL
     * @param array $check_word_array Проверочные регулярные выражения
     * @param array $need_function_array Перечень поддерживаемых функций
     */
public function create_proxy_list($name_list,$check_url="http://ya.ru",$check_word_array=array("#yandex#iUm"),$need_function_array=array())
{
	$this->close_proxy_list();
	$this->name_list=$name_list;
	if(file_exists($this->get_proxy_storage().$name_list.".proxy"))
	{
		$this->delete_proxy_list($name_list);
	}
	$this->file_proxy_list=$this->get_proxy_storage().$this->name_list.".proxy";
	$this->open_proxy_list();
	$proxy_list['content']=array();
	$proxy_list['url']=$check_url;
	if($check_word_array)$proxy_list['check_word']=$check_word_array;
	else $proxy_list['check_word']=array("#.*#iUm");
	if($need_function_array)$proxy_list['need_function']=$need_function_array;
	else $proxy_list['need_function']=array();
	$proxy_list['name_list']=$name_list;
	$proxy_list['need_update']=1;
	$this->create_proxy_list_buk($proxy_list);
	$this->save_proxy_list($proxy_list);
}
/* TODO: Добавить время в имя файла копии для сохранения нескольких резервных копий */
    /**
     * Создает резервную копию текущего профиля
     * @param $proxy_list список прокси
     */
protected function create_proxy_list_buk($proxy_list)
{
	$json_proxy=json_encode($proxy_list);
	$buk_file=$this->file_proxy_list.".buk";
	$fh=fopen($buk_file,"c+");
	file_put_contents($buk_file, '');
	rewind($fh);
	fwrite($fh, $json_proxy);
	fclose($fh);
}

    /*TODO: Добавить востановление по дате и имени */
    /**
     * Востанавливает профиль из резервной копии
     */
protected function restore_proxy_list_from_buk()
{
	$buk_file=$this->file_proxy_list.".buk";
	if(file_exists($buk_file))
	{
		$proxy_list=json_decode(file_get_contents($buk_file),true);
		$this->save_proxy_list($proxy_list);
	}
}


    /**
     * Удаляет прокси лист
     * @param $name_list имя прокси листа
     */
    public function delete_proxy_list($name_list)
{
	if(file_exists($this->get_proxy_storage().$name_list.".proxy"))
	{
		unlink($this->get_proxy_storage().$name_list.".proxy");
	}
}

    /**
     * Очищает прокси лист от прокси, но оставляет конфигурацию необходимых функций
     * @param $name_list имя прокси листа
     */
public function clear_proxy_list($name_list)
{
	$this->select_proxy_list($name_list);
	$this->proxy_list['content']=array();
	$this->save_proxy_list($this->proxy_list);
}

    /**
     * Включает регулярное обновление прокси списка или выключает
     * @param $name_list имя прокси списка
     * @param bool $value вкл./выкл.
     */
public function set_update_proxy_list($name_list,$value=true)
{
	$this->select_proxy_list($name_list);
	$this->proxy_list['need_update']=$value;
	$this->save_proxy_list($this->proxy_list);
}

    /**
     * Генерация списка прокси адресов собраных из разных источников в один список с уникальными адресами
     * @param array $proxy_array список прокси
     * @return array
     */
private function get_unique_proxy_ip($proxy_array)
{
	foreach ($proxy_array['content'] as $key => $value)
	//for($key=0;$key<3;$key++)
	{ 
		$ip_proxy[$key]=$proxy_array['content'][$key]["proxy"];
		$source_proxy[$key]=$proxy_array['content'][$key]["source_proxy"];
		$type_proxy[$key]=$this->set_name_type_proxy($proxy_array['content'][$key]["type_proxy"]);
	}
	unset($value);
	$ip_proxy=array_unique($ip_proxy);
	$proxy_array['content']=array();
	foreach ($ip_proxy as $key => $value)
	{ 
		$proxy_array['content'][$key]["proxy"]=$ip_proxy[$key];
		$proxy_array['content'][$key]["source_proxy"]=$source_proxy[$key];
		$proxy_array['content'][$key]["type_proxy"]=$this->set_name_type_proxy($type_proxy[$key]);
	}
	unset($value);
	$proxy_array['content']=array_values($proxy_array['content']);
	return $proxy_array;
}

    /**
     * Обновляет прокси лист
     * @param $name_list имя прокси листа
     * @param bool $force Принудительное обновление
     * @return array обновленный список прокси
     */
public function update_proxy_list($name_list,$force=false)
{
	if($name_list=='all')
	{
		$this->select_proxy_list($name_list);
	}
	else
	{
		$allProxy=$this->select_proxy_list('all');
		$this->select_proxy_list($name_list);
	}
	$this->free_proxy_list();
	$end_term_proxy=time()-$this->storage_time;
	if(($this->proxy_list && isset($this->proxy_list['content']) && count($this->proxy_list['content']) && $this->proxy_list['time']>$end_term_proxy && !$force) || $this->proxy_list['need_update']==0)
	{
		return $this->proxy_list;
	}
	if($name_list=='all')
	{
		$proxy_list['url']=$this->proxy_list['url'];
		$proxy_list['check_word']=$this->proxy_list['check_word'];
		$proxy_list['need_function']=$this->proxy_list['need_function'];
		$proxy_list['name_list']=$this->proxy_list['name_list'];
		$proxy_list['need_update']=$this->proxy_list['need_update'];
		$this->bloc_proxy_list();
		$this->proxy_list=$this->download_proxy();
		$this->proxy_list=$this->get_unique_proxy_ip($this->proxy_list);
		$this->proxy_list['url']=$proxy_list['url'];
		$this->proxy_list['check_word']=$proxy_list['check_word'];
		$this->proxy_list['need_function']=$proxy_list['need_function'];
		$this->proxy_list['name_list']=$proxy_list['name_list'];
		$this->proxy_list['need_update']=$proxy_list['need_update'];
	}
	else $this->proxy_list['content']=$allProxy['content'];
	//$this->save_proxy_list($this->proxy_list);
	//$this->select_proxy_list($name_list);
	$this->check_proxy_list($name_list);
	$this->save_proxy_list($this->proxy_list);
	return $this->proxy_list;
}

    /**
     * Выбор прокси листа
     * @param string $name_list имя прокси листа
     * @return array выбранный прокси лист
     */
public function select_proxy_list($name_list)
{
	$this->close_proxy_list();
	$all_list=$this->get_all_name_proxy_list();
	$this->name_list=$name_list;
	if(array_search($name_list,$all_list)!==false)
	{	
		$this->file_proxy_list=$this->get_proxy_storage().$this->name_list.".proxy";
	}
	else
	{
		$this->file_proxy_list=$this->get_proxy_storage().$this->name_list.".proxy";
		$this->create_proxy_list($name_list,"http://ya.ru",array("#yandex#iUm"));
	}
	$this->open_proxy_list();
	$list_data=file_get_contents($this->file_proxy_list);
	if(!$list_data || !json_decode($list_data))
	{
		$this->restore_proxy_list_from_buk();
	}
	$this->proxy_list=$this->get_proxy_list_file_without_lock();
	$this->free_proxy_list();
	return $this->proxy_list;
}

    /**
     * Возвращает имена всех профилей прокси списков
     * @return array перечень имен списков прокси
     */
public function get_all_name_proxy_list()
{
	$file_list=glob($this->get_proxy_storage()."*.proxy");
	$proxy_list_array=array();
	foreach ($file_list as $key => $value)
	{
		if(preg_match("#/(?<name_list>[^/]+)\.proxy$#iUm", $value, $match))
		{
			$proxy_list_array[]=$match['name_list'];
		}
	}
	return $proxy_list_array;
}

    /**
     * Проверяет список прокси на необходимые функции заданые в этом списке
     * @param string $name_list имя прокси профиля
     */
private function check_proxy_list($name_list="")
{
	if(!$name_list) $name_list=$this->name_list;
	//$this->select_proxy_list($name_list);
	if(count($this->proxy_list['check_word']))$this->check_proxy_to($name_list,'check_word');
	if(count($this->proxy_list['need_function']))$this->check_proxy_to($name_list,'need_function');
}

    /**
     * Проверяет прокси на конкретную функцию
     * @param string $name_list имя списка
     * @param string $method необходимая функция
     */
private function check_proxy_to($name_list="",$method="check_word")
{
	if(!$name_list) $name_list=$this->name_list;
	//$this->select_proxy_list($name_list);
	$good_proxy=array();
	$proxy=array();
	reset($this->proxy_list['content']);
	$part_array_size=100;
	$chonk_array_proxy_list=array_chunk($this->proxy_list['content'], $part_array_size,true);
	$tmp_proxy_list['content']=array();
	foreach ($chonk_array_proxy_list as $keyArray => $part_array_proxy_list)
	{
		$good_proxy=array();
		foreach ($part_array_proxy_list as $key => $proxy_val)
		{
			$good_proxy[$key]=$this->proxy_list['content'][$key]["proxy"];
			$good_proxy_source[$key]=$this->proxy_list['content'][$key]["source_proxy"];
			$good_proxy_type[$key]=$this->set_name_type_proxy($this->proxy_list['content'][$key]["type_proxy"]);
		}
		unset($proxy_val);
		switch ($method)
		{
			case 'check_word':
				$good_proxy=$this->check_proxy_array_to_site($good_proxy,$this->proxy_list['url'],$this->proxy_list['check_word']);
				break;
			case 'need_function':
				$good_proxy=$this->check_proxy_array_to_function($good_proxy,$this->proxy_list['need_function']);
				break;
			default:
				# code...
				break;
		}
		if(is_array($good_proxy))
		{
			foreach ($good_proxy as $key => $good)
			{
				$tmp_proxy_list['content'][$key]["proxy"]=$good;
				$tmp_proxy_list['content'][$key]["source_proxy"]=$good_proxy_source[$key];
				$tmp_proxy_list['content'][$key]["type_proxy"]=$this->set_name_type_proxy($good_proxy_type[$key]);
				$tmp_proxy_list['content'][$key]["renters"][0]["start_rent"]="";
				$tmp_proxy_list['content'][$key]["renters"][0]["renter_code"]="";
				$tmp_proxy_list['content'][$key]["renters"][0]["user_site"]="";
			}
		}
	}
	unset($part_array_proxy_list);
	$this->proxy_list['content']=array_values($tmp_proxy_list['content']);

	$this->save_proxy_list($this->proxy_list);
}

    /**
     * Проверяет все прокси на работоспособность
     */
public function check_all_proxy()
{
	setLog(__FILE__,__LINE__," check All Proxy ");
	$this->need_check_proxy=1;
	$this->proxy_list=$this->download_proxy();
	$this->proxy_list=$this->get_unique_proxy_ip($this->proxy_list);
	//Подсчитаем количество прокси на каждый источник
	$start_count_source=$this->count_proxy_in_source();
	$count_proxy=count($this->proxy_list['content']);
	$good_proxy=array();
	$proxy=array();
	reset($this->proxy_list['content']);
	$part_array_size=100;
	$chonk_array_proxy_list=array_chunk($this->proxy_list['content'], $part_array_size,true);
	$tmp_proxy_list['content']=array();
	foreach ($chonk_array_proxy_list as $keyArray => $part_array_proxy_list)
	{
		$good_proxy=array();
		foreach ($part_array_proxy_list as $key => $proxyVal)
		{
			$good_proxy[$key]=$this->proxy_list['content'][$key]["proxy"];
			$good_proxy_source[$key]=$this->proxy_list['content'][$key]["source_proxy"];
			$good_proxy_type[$key]=$this->set_name_type_proxy($this->proxy_list['content'][$key]["type_proxy"]);
		}
		unset($proxyVal);
		$good_proxy=$this->check_proxy($good_proxy);
		if(is_array($good_proxy))
		{
			foreach ($good_proxy as $key => $good)
			{
				$tmp_proxy_list['content'][$key]["proxy"]=$good;
				$tmp_proxy_list['content'][$key]["source_proxy"]=$good_proxy_source[$key];
				$tmp_proxy_list['content'][$key]["type_proxy"]=$this->set_name_type_proxy($good_proxy_type[$key]);
				$tmp_proxy_list['content'][$key]["renters"][0]["start_rent"]="";
				$tmp_proxy_list['content'][$key]["renters"][0]["renter_code"]="";
				$tmp_proxy_list['content'][$key]["renters"][0]["user_site"]="";
			}
		}
	}
	unset($part_array_proxy_list);
	$this->proxy_list['content']=array_values($tmp_proxy_list['content']);
	$end_count_source=$this->count_proxy_in_source();
	setLog(__FILE__,__LINE__," start proxy count(".$count_proxy.") ");
	foreach ($start_count_source as $key => $value)
	{
		setLog(__FILE__,__LINE__," start in $key = $value ");
	}
	unset($value);
	setLog(__FILE__,__LINE__," good proxy count(".count($this->proxy_list['content']).") ");
	foreach ($end_count_source as $key => $value)
	{
		setLog(__FILE__,__LINE__," good in $key = $value ");
	}
	unset($value);
	$this->save_proxy_list($this->proxy_list);
}

}
?>