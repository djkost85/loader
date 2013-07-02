<?php
namespace get_content\c_proxy;
use get_content\c_get_content\c_get_content as c_get_content;
use get_content\c_string_work\c_string_work as c_string_work;
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
     * proxy_list["check_word"][индекс] Проверочное слово которое должно быть в ответе с сервера- это регулярное выражение
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
     * Адрес папки где храняться файлы с proxy списками
     * @access protected
     * @var string
     */
    protected $dir_proxy_list_file;
    /**
     * Имена файлов модулей для скачивания списков прокси
     * @access protected
     * @var array
     */
    protected $file_url_proxy_list;
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
     * Имя листа по умолчанию в котором хранятся все адреса прокси серверов
     * @access protected
     * @var string
     */
    protected $default_list;
    /**
     * Указатель на файл $file_proxy_list
     * @access protected
     * @var $f_heandle_proxy_list
     */
    protected $f_heandle_proxy_list;
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
     * Набор URL на странуцы проверки функций прокси сервера если не работает основной
     * @access protected
     * @var array
     */
    protected $check_url_proxy;
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
     * @return \get_content\c_proxy\c_proxy
     */
 function __construct()
{
	$this->storage_time            =86400;
	$this->rent_time               =3600;
    $this->get_content             = new c_get_content();
	$this->get_content->set_type_content('html');
	$this->set_method_get_proxy("random");
    $this->dir_proxy_file          ="proxy_files";
	$this->dir_proxy_list_file     ="proxy_list";
    $this->dir_url_proxy_list      ="proxy_site_module";
    $this->f_heandle_proxy_list    =NULL;
    $this->file_url_proxy_list 	   =glob($this->get_dir_url_proxy_list()."/*.php");
    $this->check_url_proxy         =require $this->get_dir_proxy_file()."/check_url_list.php";
	$this->proxy_list              =array();
	$this->need_check_proxy        =true;
	$this->last_use_proxy          ='';
    $this->set_default_list('all');
	$this->name_list               =$this->get_default_list_name();
    if(!file_exists($this->get_dir_proxy_list_file()."/".$this->name_list.".proxy")) $this->create_proxy_list($this->name_list);
	$this->select_proxy_list($this->name_list);
	$this->set_remove_proxy(false);
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
     * функция для проверки доступа к необходимым ресурсам системы
     */
public function function_chek()
{
    echo "c_proxy->function_check {</br>\n";
    $mess='';
    if(!is_dir($this->get_dir_proxy_list_file()))
    {
        $mess.="Warning: folder for the proxy profile does not exist</br>\n";
    }
    else
    {
        if(!is_readable($this->get_dir_proxy_list_file()) || !is_writable($this->get_dir_proxy_list_file()))
        {
            $mess.="Warning: folder for the proxy profile does not have the necessary rights to use</br>\n";
        }
        elseif(is_file($this->get_dir_proxy_list_file()."/".$this->get_default_list_name().".proxy"))
        {
            if(!is_readable($this->get_dir_proxy_list_file()."/".$this->get_default_list_name().".proxy") || !is_writable($this->get_dir_proxy_list_file()."/".$this->get_default_list_name().".proxy"))
            {
                $mess.="Warning: file for the default proxy list does not have the necessary rights to use</br>\n";
            }
        }
        else
        {
            $mess.="Warning: file for the default proxy list does not exist</br>\n";
            $mess.="try to create</br>\n";
            $proxy['content']=array();
            $proxy['url']="http://ya.ru";
            $proxy['check_word']=array("#yandex#ims");
            $proxy['need_function']=array();
            $proxy['name_list']=$this->get_default_list_name();
            $proxy['need_update']=true;
            $proxy['time']=time();
            $this->create_proxy_list($this->get_default_list_name());
        }
    }

    if(!class_exists('c_proxy')) $mess.="Warning: c_proxy class is declared, can not work with proxy</br>\n";
    if(!class_exists('c_string_work')) $mess.="Warning: c_string_work class is declared, word processing is not possible</br>\n";
    if($mess) echo $mess." To work correctly, correct the above class c_proxy requirements </br>\n";
    else echo "c_proxy ready</br>\n";
    echo "c_proxy->function_check }</br>\n";
}

/**
 * @param string $default_list
 */
public function set_default_list($default_list)
{
    $this->default_list = $default_list;
}

/**
 * @return string
 */
public function get_default_list_name()
{
    return $this->default_list;
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


public function get_dir_proxy_file()
{
    return dirname(__FILE__)."/".$this->dir_proxy_file;
}
    /**
     * Получение абсолютного адреса к папке гда лежат файлы конфигурации прокси листов
     * @return string
     */
public function get_dir_proxy_list_file()
{
	return $this->get_dir_proxy_file()."/".$this->dir_proxy_list_file;
}

public function get_dir_url_proxy_list()
{
    return $this->get_dir_proxy_file()."/".$this->dir_url_proxy_list;
}

    /**
     * Возвращает ip сервера с которого запущен скрипт или false
     * @return bool|string
     */
public function get_server_ip()
{
	if(isset($this->server_ip)) return $this->server_ip;
    if(c_string_work::is_ip($_SERVER['SERVER_ADDR'])) $this->server_ip=$_SERVER['SERVER_ADDR'];
    else
    {
	$this->get_content->set_use_proxy(0);
	$this->get_content->set_type_content('html');
	$this->get_content->set_mode_get_content('single');
	$this->get_content->get_content("http://2ip.ru/");
	$answer=$this->get_content->get_answer();
	$reg="/<span>\s*Ваш IP адрес:\s*<\/span>\s*<big[^>]*>\s*(?<ip>[^<]*)\s*<\/big>/iUm";
	preg_match($reg, $answer,$match);
	if(!$match['ip'] && !c_string_work::is_ip($match['ip'])) return false;
    $this->server_ip=$match['ip'];
    }
	return $this->server_ip;
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
     * @param string $check_url_proxy  url сервера для проверки функций прокси, если не работает выберает другой из каталога
     * @return string возвращает рабочий url для проверки прокси
     */
public function get_proxy_checker($check_url_proxy="")
{
    if($check_url_proxy==="") $check_url_proxy=$this->check_url_proxy;
	$this->get_content->set_use_proxy(false);
	$this->get_content->set_type_content('text');
    $this->get_content->set_default_setting(CURLOPT_HEADER,false);
	$this->get_content->set_mode_get_content('multi');
    $this->get_content->set_count_multi_stream(1);
	$this->get_content->set_min_size_answer(5);
    $answer=$this->get_content->get_content($check_url_proxy);
	foreach($answer as $key => $value)
	{
		if(preg_match("/^[01]{5}$/i",$value))
		{
			return $this->check_url_proxy[$key];
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
	foreach ($this->file_url_proxy_list as $value_proxy_list)
	{
		$tmp_proxy=require $value_proxy_list;
		if(is_array($tmp_proxy) && count($tmp_proxy))
		{
			$proxy['content']=array_merge($proxy['content'],$tmp_proxy['content']);
		}
	}
	return $proxy;
}

    /**
     * Устанавливает фильтр для необходимых прокси
     * @param string $new_name_type_proxy протокол через который работает прокси
     * @return string|bool имя протокола
     */
private function set_name_type_proxy($new_name_type_proxy="http")
{
    switch($new_name_type_proxy)
    {
        case 'http': return 'http';
        case 'https': return 'https';
        case 'socks': return 'socks';
        default: return false;
    }
}

    /**
     * @param string $new_method_get_proxy тип получения прокси адреса
     * @return bool
     */
public function set_method_get_proxy($new_method_get_proxy="random")
{
	switch ($new_method_get_proxy)
	{
		case 'random':
			$this->method_get_proxy='random';
            return true;
			break;
		case 'rent':
			$this->method_get_proxy='rent';
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

public function get_file_url_proxy_list()
{
	return $this->file_url_proxy_list;
}
    /**
     * Открывает прокси лист
     */
public function open_proxy_list()
{
	$this->close_proxy_list();
	$this->f_heandle_proxy_list=fopen($this->file_proxy_list,"c+");
}

    /**
     * Закрывает текущий прокси лист
     */
public function close_proxy_list()
{
	$this->free_proxy_list();
    if(isset($this->f_heandle_proxy_list) && is_resource($this->f_heandle_proxy_list))
    {
	    fclose($this->f_heandle_proxy_list);
        unset($this->f_heandle_proxy_list);
    }
	unset($this->proxy_list);
}

    /**
     * Освобождает прокси лист от блокировки текущим процессом
     * @return bool
     */
public function free_proxy_list()
{
	if(!$this->get_access_to_proxy_list()) return true; // проверяет занят ли этим потоком файл?
	if(is_resource($this->f_heandle_proxy_list))
    {
        fflush($this->f_heandle_proxy_list);
        flock($this->f_heandle_proxy_list,LOCK_UN);
	    $this->set_access_to_proxy_list(0);
    }
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
        if(is_resource($this->f_heandle_proxy_list))
        {
		    if(flock($this->f_heandle_proxy_list,LOCK_EX))
		    {
		    	$this->set_access_to_proxy_list(1);
		    	return true;
		    }
        }
		// Прокси лист занят
		sleep(5);
		}while(true);
    return false;
}

    /**
     * Возвращает случайный прокси из текцщего списка
     * @return bool|string
     */
public	function get_random_proxy()
{
	$proxy_list=$this->get_proxy_list_in_file();
	$this->free_proxy_list();
	$count_proxy=count($proxy_list['content']);
	for($i=0;$i<$count_proxy/10;$i+=10)
	{
		$proxy=array();
		for($j=0;$j<10;$j++)
		{
			$proxy[$j]['proxy']=trim($proxy_list['content'][array_rand($proxy_list['content'])]["proxy"]);
		}
		if($good_proxy=$this->check_proxy_array($proxy))
		{
			if(is_array($good_proxy))
			{
                $tmp_proxy=current($good_proxy);
				return $tmp_proxy['proxy'];
			}
			else
			{
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
public function get_proxy_list_in_file()
{
    while(true)
    {
        rewind($this->f_heandle_proxy_list);
        clearstatcache(true,$this->file_proxy_list);
        $json_proxy=fread($this->f_heandle_proxy_list,filesize($this->file_proxy_list));
        if(strlen($json_proxy)==filesize($this->file_proxy_list))
        {
            $this->proxy_list=json_decode($json_proxy,true);
            if(isset($this->proxy_list)) break;
        }
        else sleep(1);
    }
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
        $this->bloc_proxy_list();
		$tmp_array['proxy']=trim($proxy);
		$tmp_array["source_proxy"]=$source_proxy;
		$tmp_array["type_proxy"]=$type_proxy;
		$this->proxy_list['content'][]=$tmp_array;
		$this->save_proxy_list($this->proxy_list);
        return true;
	}
    return false;
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
	for($i=0;$i<100;$i++)
	{
        $this->bloc_proxy_list();
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
		sleep(60);
	}
	return false;
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
	//$this->proxy_list=$this->get_proxy_list_in_file();
	// если задан адрес в где лежит информация об аренде, проверяем информацию
	if($key_address)
	{
		if( isset($this->proxy_list['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["renter_code"])
            && $this->proxy_list['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["renter_code"]==$rent_code
            && $this->proxy_list['content'][$key_address['key_content']]["renters"][$key_address['key_renters']]["user_site"]==$site_for_use)
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
        if(!isset($value_content["renters"])) continue;
        foreach($value_content["renters"] as $key_renters => $value_renters)
        {
            if($value_renters["renter_code"]==$rent_code)
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
	//$this->proxy_list=$this->get_proxy_list_in_file();
	if($proxy)
	{
		$result=$this->search_proxy_in_list($proxy);
		$tmp_data['start_rent']=time();
		$tmp_data['renter_code']=$rent_code;
		$tmp_data['user_site']=$site_for_use;
		$this->proxy_list['content'][$result['key_content']]["renters"][]=$tmp_data;
        return true;
	}
	else
	{
		foreach ($this->proxy_list['content'] as $key_content => $value_content)
		{
			$proxy_use_this_site=0;
            if(isset($value_content["renters"]))
            {
			    foreach($value_content["renters"] as $value_renters)
			    {
			    	if($value_renters["user_site"]==$site_for_use)
			    	{
			    		$proxy_use_this_site=1;
			    		break;
			    	}
			    }
			    unset($value_renters);
            }
			//Если через этот прокси не опрашивается сайт $site_for_use, то привяжем поток к этому прокси
			if(!$proxy_use_this_site)
			{
				$tmp_data['start_rent']=time();
				$tmp_data['renter_code']=$rent_code;
				$tmp_data['user_site']=$site_for_use;
				$this->proxy_list['content'][$key_content]["renters"][]=$tmp_data;
				end($this->proxy_list['content'][$key_content]["renters"]);
				$return_array["proxy"]=$value_content["proxy"];
				$return_array["key_content"]=$key_content;
				$return_array["key_renters"]=key($this->proxy_list['content'][$key_content]["renters"]);
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
public function remove_all_rent_from_code($rent_code)
{
    $this->bloc_proxy_list();
    $this->proxy_list=$this->get_proxy_list_in_file();
	if(!isset($this->proxy_list['content']))
    {
        $this->free_proxy_list();
        return false;
    }
    if(isset($this->proxy_list['content']) && is_array($this->proxy_list['content']))
    {
	    foreach ($this->proxy_list['content'] as $key_content => $value_content)
	    {
            if(!isset($value_content["renters"]) || !is_array($value_content["renters"])) continue;
	    	foreach($value_content["renters"] as $key_renters => $value_renters)
	    	{
	    		if($value_renters["renter_code"]==$rent_code)
	    		{
	    			$this->remove_rent($key_content,$key_renters,1);
	    		}
	    	}
	    	unset($value_renters);
	    }
	    unset($value_content);
	    $this->save_proxy_list($this->proxy_list);
    }
    return true;
}

    /**
     * Убирает все аренды из текущего списка прокси
     */
public function remove_all_rent()
{
    $this->bloc_proxy_list();
    $this->proxy_list=$this->get_proxy_list_in_file();
    if(isset($this->proxy_list['content']) && is_array($this->proxy_list['content']))
    {
        foreach ($this->proxy_list['content'] as $key_content => $value_content)
        {
            if(!isset($value_content["renters"]) || !is_array($value_content["renters"])) continue;
            foreach($value_content["renters"] as $key_renters => $value_renters)
            {
                if($value_renters["renter_code"])
                {
                    $this->remove_rent($key_content,$key_renters,1);
                }
            }
            unset($value_renters);
        }
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
    $this->bloc_proxy_list();
	if(isset($this->proxy_list['content'][$key_content]["renters"][$key_renters]))
	{
		unset($this->proxy_list['content'][$key_content]["renters"][$key_renters]);
        if(!$without_saving) $this->save_proxy_list($this->proxy_list);
        else $this->free_proxy_list();
        return true;
    }
	else
    {
        $this->free_proxy_list();
        return false;
    }
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
        $this->bloc_proxy_list();
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
	if(!$proxy=$this->get_proxy_list_in_file())
	{
		return false;
	}
	return $this->proxy_list=$proxy;
}
public function get_last_use_proxy()
{
	return $this->last_use_proxy;
}
    /**
     * Проверяет прокси адрес на работоспособность и на поддерживаетмые функции
     * @param string $proxy прокси адрес
     * @return array|int|string
     */
public function check_proxy($proxy)
{
    if(!$this->need_check_proxy) return $proxy;
    if(is_string($proxy) && c_string_work::is_ip($proxy))
    {
    	$this->get_content->set_mode_get_content('single');
    	$this->get_content->set_use_proxy($proxy);
        $this->get_content->set_min_size_answer(5);
        $this->get_content->set_default_setting(CURLOPT_REFERER,"proxy-check.net");
        $this->get_content->set_default_setting(CURLOPT_POST,true);
        $this->get_content->set_default_setting(CURLOPT_POSTFIELDS,"proxy=yandex");
        $this->get_content->set_type_content('text');
        $this->get_content->set_default_setting(CURLOPT_HEADER,false);
        $this->get_content->set_check_answer(false);
        $answer=$this->get_content->get_content($this->get_proxy_checker().'?ip='.$this->get_server_ip().'&proxy=yandex');
        $this->get_content->restore_default_settings();
        if(preg_match('#^[01]{5}$#',$answer) && preg_match_all('#(?<fun_status>[01])#U',$answer,$matches))
        {
            $info_proxy['proxy']=$proxy;
            $info_proxy['anonim']=$matches['fun_status'][0];
            $info_proxy['referer']=$matches['fun_status'][1];
            $info_proxy['post']=$matches['fun_status'][2];
            $info_proxy['get']=$matches['fun_status'][3];
            $info_proxy['cookie']=$matches['fun_status'][4];
            $info_proxy['last_cheak']=time();
            return $info_proxy;
        }
    }
    return false;
}

    /**
     * Проверяет массив прокси адресов на работоспособность и на поддерживаетмые функции
     * @param array $array_proxy массив прокси адресов
     * @return array|bool
     */
private function check_proxy_array($array_proxy)
{
	if(is_array($array_proxy))
	{
        if(!$this->need_check_proxy) return $array_proxy;
        $good_proxy=array();
        $url=$this->get_proxy_checker().'?ip='.$this->get_server_ip().'&proxy=yandex';
        $this->get_content->set_mode_get_content('multi');
        $this->get_content->set_count_multi_stream(1);
        $this->get_content->set_min_size_answer(5);
        $this->get_content->set_max_number_repeat(0);
        $this->get_content->set_default_setting(CURLOPT_REFERER,"proxy-check.net");
        $this->get_content->set_default_setting(CURLOPT_POST,true);
        $this->get_content->set_default_setting(CURLOPT_POSTFIELDS,"proxy=yandex");
        $this->get_content->set_type_content('text');
        $this->get_content->set_default_setting(CURLOPT_HEADER,false);
        $this->get_content->set_check_answer(false);
        foreach(array_chunk($array_proxy,100) as $value_array_proxy)
        {
            $this->get_content->set_count_multi_curl(count($value_array_proxy));
            $url_array=array();
            reset($value_array_proxy);
            $descriptor_array=&$this->get_content->get_descriptor_array();
            foreach ($descriptor_array as $key => $value)
            {
                $this->get_content->set_option_to_descriptor($descriptor_array[$key],CURLOPT_PROXY,$value_array_proxy[key($value_array_proxy)]['proxy']);
                next($value_array_proxy);
                $url_array[]=$url;
            }
            $answer_content=$this->get_content->get_content($url_array);
            foreach ($answer_content as $key => $value)
		    {
		    	if(preg_match('#^[01]{5}$#',$value) && preg_match_all('#(?<fun_status>[01])#U',$value,$matches))
                {
                    $info_proxy['proxy']     =$value_array_proxy[$key]['proxy'];
                    $info_proxy['anonim']    =$matches['fun_status'][0];
                    $info_proxy['referer']   =$matches['fun_status'][1];
                    $info_proxy['post']      =$matches['fun_status'][2];
                    $info_proxy['get']       =$matches['fun_status'][3];
                    $info_proxy['cookie']    =$matches['fun_status'][4];
                    $info_proxy['last_cheak']=time();
                    $good_proxy[]=$info_proxy;
                }
		    }
		    unset($value);
        }
        $this->get_content->restore_default_settings();
        if(count($good_proxy)) return $good_proxy;
	}
    return false;
}

    /**
     * Проверка доступности сайта через список прокси, отсеевает не рабочие прокси
     * @param array $array_proxy тестовый список прокси
     * @param string $url ссылка на страницу проверки
     * @param array $check_word проверочные регулярные выражения
     * @return array|bool
     */
private function check_proxy_array_to_site($array_proxy,$url,$check_word)
{
    if(!is_array($array_proxy)) return false;
    $good_proxy=array();
    $this->get_content->set_mode_get_content('multi');
    $this->get_content->set_count_multi_stream(1);
    $this->get_content->set_type_content('text');
    $this->get_content->set_default_setting(CURLOPT_HEADER,false);
    $this->get_content->set_default_setting(CURLOPT_POST,false);
    $this->get_content->set_check_answer(false);
    foreach (array_chunk($array_proxy,100) as $value_proxy)
    {
        $this->get_content->set_count_multi_curl(count($value_proxy));
        reset($value_proxy);
        $descriptor_array=&$this->get_content->get_descriptor_array();
        $url_array=array();
        foreach ($descriptor_array as $key => $value)
        {
            $this->get_content->set_option_to_descriptor($descriptor_array[$key],CURLOPT_PROXY,$value_proxy[key($value_proxy)]['proxy']);
	    	next($value_proxy);
            $url_array[]=$url;
	    }
	    $answer_content=$this->get_content->get_content($url_array);
	    reset($value_proxy);
	    foreach ($answer_content as $value)
	    {
	    	$test_count=0;
	    	$count_good_check=0;
	    	foreach ($check_word as $value_check_word)
	    	{
	    		$test_count++;
	    		if(preg_match($value_check_word,$value)) $count_good_check++;
	    	}
	    	unset($value_check_word);
	    	if($count_good_check==$test_count) $good_proxy[]=$value_proxy[key($value_proxy)];
	    	next($value_proxy);
	    }
	    unset($value);
    }
    $this->get_content->restore_default_settings();
	if(count($good_proxy)) return $good_proxy;
	else return false;
}

    /**
     * Возвращает адреса прокси поддерживающие выбранные функции
     * @param array $proxy_list Список прокси
     * @param array $fun_array перечень необходимых функций anonim|referer|post|get|cookie
     * @return array|bool
     */
private function get_proxy_by_function($proxy_list,$fun_array)
{
    if(!is_array($proxy_list)) return false;
    $need_fun=array("anonim"=>"0","referer"=>"0","post"=>"0","get"=>"0","cookie"=>"0");
    foreach ($fun_array as $value) $need_fun[$value]=1;
    $good_proxy=array();
    foreach ($proxy_list as $value)
    {
           if($value['anonim'] >=$need_fun['anonim']
           && $value['referer']>=$need_fun['referer']
           && $value['post']   >=$need_fun['post']
           && $value['get']    >=$need_fun['get']
           && $value['cookie'] >=$need_fun['cookie']
             )
              $good_proxy[]= $value;
    }
    if(count($good_proxy)) return $good_proxy;
    return false;
}
    /**
     * Сохраняет прокси список
     * @param array|bool $proxy_list сохраняемый список прокси
     */
public function save_proxy_list($proxy_list=false)
{
	if(!is_array($proxy_list)) $proxy_list=$this->proxy_list;
	$proxy_list['time']=time();
	$this->proxy_list=$proxy_list;
	$json_proxy=json_encode($proxy_list);
	$this->bloc_proxy_list();
	file_put_contents($this->file_proxy_list, '');
	rewind($this->f_heandle_proxy_list);
	fwrite($this->f_heandle_proxy_list, $json_proxy);
	$this->free_proxy_list();
}

    /**
     * Создает профиль прокси адресов
     * @param string $name_list название
     * @param string $check_url проверочный URL
     * @param array $check_word_array Проверочные регулярные выражения
     * @param array $need_function_array Перечень поддерживаемых функций
     * @param bool $need_update
     */
public function create_proxy_list($name_list,$check_url="http://ya.ru",$check_word_array=array("#yandex#iUm"),$need_function_array=array(),$need_update=true)
{
	$this->close_proxy_list();
	$this->name_list=$name_list;
	if(file_exists($this->get_dir_proxy_list_file()."/".$name_list.".proxy")) $this->delete_proxy_list($name_list);
	$this->file_proxy_list=$this->get_dir_proxy_list_file()."/".$this->name_list.".proxy";
	$this->open_proxy_list();
	$proxy_list['content']=array();
	$proxy_list['url']=$check_url;
	$proxy_list['check_word']=$check_word_array;
    $proxy_list['need_function']=$need_function_array;
	$proxy_list['name_list']=$name_list;
	$proxy_list['need_update']=$need_update;
	$this->create_proxy_list_buk($proxy_list);
	$this->save_proxy_list($proxy_list);
}
    /**
     * Создает резервную копию текущего профиля
     * @param $proxy_list список прокси
     */
protected function create_proxy_list_buk($proxy_list)
{
	$json_proxy=json_encode($proxy_list);
	$buk_file=$this->file_proxy_list.time().".buk";
	$fh=fopen($buk_file,"c+");
	file_put_contents($buk_file, '');
	rewind($fh);
	fwrite($fh, $json_proxy);
	fclose($fh);
}
    /**
     * Удаляет прокси лист
     * @param $name_list имя прокси листа
     */
public function delete_proxy_list($name_list)
{
    if($name_list==$this->name_list) $this->close_proxy_list();
	if(file_exists($this->get_dir_proxy_list_file()."/".$name_list.".proxy"))
	{
		unlink($this->get_dir_proxy_list_file()."/".$name_list.".proxy");
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
    $ip_proxy=array();
	foreach ($proxy_array as $key => $value) $ip_proxy[$key]=$proxy_array[$key]["proxy"];
    $proxy_array_copy=$proxy_array;
	unset($value);
	$ip_proxy=array_unique($ip_proxy);
	$proxy_array=array();
	foreach ($ip_proxy as $key => $value) $proxy_array[$key]=$proxy_array_copy[$key];
	unset($value);
	$proxy_array=array_values($proxy_array);
	return $proxy_array;
}

    /**
     * Обновляет прокси лист
     * @param string $name_list имя прокси листа
     * @param bool $force Принудительное обновление
     * @return array обновленный список прокси
     */
public function update_proxy_list($name_list,$force=false)
{
	if($name_list==$this->get_default_list_name())
    {
        return $this->select_proxy_list($this->get_default_list_name());
    }
	else
	{
		$allProxy=$this->select_proxy_list($this->get_default_list_name());
		$this->select_proxy_list($name_list);
	}
	$this->free_proxy_list();
	$end_term_proxy=time()-$this->storage_time;
	if(
       (
        $this->proxy_list
        && isset($this->proxy_list['content'])
        && count($this->proxy_list['content'])
        && $this->proxy_list['time']
        > $end_term_proxy
        && !$force
       )
        || !$this->proxy_list['need_update']
      )
	{
		return $this->proxy_list;
	}
    $this->proxy_list['content']=$this->get_proxy_by_function($allProxy['content'],$this->proxy_list['need_function']);
    $this->proxy_list['content']=$this->check_proxy_array_to_site($this->proxy_list['content'],$this->proxy_list['url'],$this->proxy_list['check_word']);
	$this->save_proxy_list($this->proxy_list);
	return $this->proxy_list;
}

    /**
     * Обновляет основной список прокси в котором хранятся адреса всех прокси
     * Из него берут информацию другие профили
     * @param bool $force принудительное обновление
     * @return array
     */
public function update_default_proxy_list($force=false)
{
    $old_proxy=$this->select_proxy_list($this->get_default_list_name());
    $this->free_proxy_list();
    $end_term_proxy=time()-$this->storage_time;
    if(
        (
            $this->proxy_list
            && isset($this->proxy_list['content'])
            && count($this->proxy_list['content'])
            && $this->proxy_list['time']
            > $end_term_proxy
            && !$force
        )
        || !$this->proxy_list['need_update']
    )
    {
        return $this->proxy_list;
    }
    $proxy_list=$this->download_proxy();
    $old_proxy['content']=array_merge($old_proxy['content'],$proxy_list['content']);
    $old_proxy['content']=$this->get_unique_proxy_ip($old_proxy['content']);
    $old_proxy['content']=$this->check_proxy_array($old_proxy['content']);
    $this->proxy_list=$old_proxy;
    $this->save_proxy_list($this->proxy_list);
    return $this->proxy_list;
}

    /**
     * Обновление всех прокси листов кроме основного
     * @param $force принудительное обновление
     */
public function update_all_proxy_list($force=false)
{
    $this->update_default_proxy_list($force);
    foreach ($this->get_all_name_proxy_list() as $value)
    {
        $this->update_proxy_list($value,$force);
    }
}
    /**
     * Изменяет настройки прокси листа
     * @param $name_list
     * @param string $check_url
     * @param array $check_word_array
     * @param array $need_function_array
     * @param bool $need_update
     */
public function config_proxy_list($name_list,$check_url="http://bpteam.net",$check_word_array=array("#\+380632359213#ims"),$need_function_array=array(),$need_update=false)
{
    $this->select_proxy_list($name_list);
    $this->proxy_list['url']=$check_url;
    $this->proxy_list['check_word']=$check_word_array;
    $this->proxy_list['need_function']=$need_function_array;
    $this->proxy_list['name_list']=$name_list;
    $this->proxy_list['need_update']=$need_update;
    $this->create_proxy_list_buk($this->proxy_list);
    $this->save_proxy_list($this->proxy_list);
}


    /**
     * Выбор прокси листа
     * @param string $name_list имя прокси листа
     * @return array выбранный прокси лист
     */
public function select_proxy_list($name_list)
{
	$this->close_proxy_list();
	$this->name_list=$name_list;
	if($this->proxy_list_exist($name_list))
	{	
		$this->file_proxy_list=$this->get_dir_proxy_list_file()."/".$this->name_list.".proxy";
	}
	else
	{
		$this->file_proxy_list=$this->get_dir_proxy_list_file()."/".$this->name_list.".proxy";
		$this->create_proxy_list($name_list);
	}
	$this->open_proxy_list();
	$this->proxy_list=$this->get_proxy_list_in_file();
	$this->free_proxy_list();
	return $this->proxy_list;
}

    /**
     * Возвращает имена всех профилей прокси списков
     * @return array перечень имен списков прокси
     * @return array
     */
public function get_all_name_proxy_list()
{
	$file_list=glob($this->get_dir_proxy_list_file()."/"."*.proxy");
	$proxy_list_array=array();
	foreach ($file_list as $value)
	{
		if(preg_match("#/(?<name_list>[^/]+)\.proxy$#iUm", $value, $match))
		{
			$proxy_list_array[]=$match['name_list'];
		}
	}
	return $proxy_list_array;
}

    /**
     * Проверяет существует ли прокси лист
     * @param string $name_list имя прокси листа
     * @return bool
     */
public function proxy_list_exist($name_list)
{
    $all_list=$this->get_all_name_proxy_list();
    if(array_search($name_list,$all_list)!==false)
    {
        return true;
    }
    else return false;
}
}