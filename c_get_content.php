<?php
/**
 * Class c_get_content
 * С помощью основных функций библиотеки cURL посылает http запросы для скачивания контента из сети
 * Умеет работать через прокси сервера, в много поточном режиме с верификацией данных.
 * @author Evgeny Pynykh <bpteam22@gmail.com>
 * @package get_content
 * @version 2.0
 */
namespace get_content\c_get_content;
use get_content\c_proxy\c_proxy as c_proxy;
use get_content\c_string_work\c_string_work as c_string_work;
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
     */
    private $default_settings;
    /**
     * Пересление всех поддерживаемых настроек для cURL
     * @var array
     */
    private $all_setting;// массив с перечислением всех настроек для cURL
    /**
     * Флаг для включения запросов через прокси сервера
     * @var bool
     */
    private $use_proxy;
    /**
     * Адрес спрокси или класс для работы с прокси
     * @var string|c_proxy
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
    private $dir_cookie;// Папка где храняться файлы cookie

    /**
     * @return \get_content\c_get_content\c_get_content
     */
function __construct()
{
	$this->all_setting =array(
                              CURLOPT_HEADER,
                              CURLOPT_URL,
                              CURLOPT_TIMEOUT,
                              CURLOPT_USERAGENT,
                              CURLOPT_RETURNTRANSFER,
                              CURLOPT_FOLLOWLOCATION,
                              CURLOPT_REFERER,
                              CURLOPT_POST,
                              CURLOPT_POSTFIELDS
                              );
	$this->set_dir_cookie("get_content_files/cookie");
	$this->restore_default_settings();
	$this->set_use_proxy(false);
	$this->set_number_repeat(0);
	$this->set_max_number_repeat(0);
	$this->set_min_size_answer(100);
	$this->set_type_content("text");
	$this->set_in_cache(false);
	$this->set_encoding_answer(false);
	$this->set_encoding_name("UTF-8");
	$this->set_check_answer(false);
	$this->set_mode_get_content('single');
}

function __destrukt()
{
    $this->clear_cookie();
    $this->close_get_content();

}
    /**
     * функция для проверки доступа к необходимым ресурсам системы
     */
public function function_check()
{
    echo "c_get_content->function_check {</br>\n";
    $mess='';
	if(!function_exists('curl_init')) $mess.="Error: CURL is not installed</br>\n";
	if(!is_dir($this->get_dir_cookie()))
	{
		$mess.="Warning: folder for the cookie does not exist</br>\n";
	}
	else
	{
		if(!is_readable($this->get_dir_cookie()) || !is_writable($this->get_dir_cookie()))
		{
            $mess.="Warning: folder for the cookie does not have the necessary rights to use</br>\n";
		}
	}
	if(!class_exists('c_proxy')) $mess.="Warning: c_proxy class is declared, can not work with proxy</br>\n";
	if(!class_exists('c_string_work')) $mess.="Warning: c_string_work class is declared, word processing is not possible</br>\n";
    if($mess) echo $mess." To work correctly, correct the above class c_get_content requirements </br>\n";
    else echo "c_get_content ready</br>\n";
    echo "c_get_content->function_check }</br>\n";
}

    /**
     * Удаляет старые файлы, которые уже не используются
     * @param int $storage_time время хранения прокси
     */
public function clear_cookie($storage_time=172800)
{
	$file_list = glob($this->get_dir_cookie()."*.cookie");
	foreach ($file_list as $value)
	{
		preg_match("/\/(?<create_time>\d+)(?:\.|\s*)\d*\.cookie$/iU", $value,$match);
		if((int)$match['create_time']<time()-$storage_time)
		{
			unlink($value);
		}
	}
}

    /**
     * Адерс должен быть относительным папке где лежит исходник класса
     * @param string $new_dir_cookie
     */
public function set_dir_cookie($new_dir_cookie)
{
	$this->dir_cookie=$new_dir_cookie;
}
public function get_dir_cookie()
{
	return dirname(__FILE__)."/".$this->dir_cookie."/";
}

    /**
     * @param int $option
     * @param mixed $value
     */
public function set_default_setting($option,$value)
{
	$this->default_settings[$option]=$value;
}

    /**
     * @param int $option
     * @return mixed
     */
public function get_default_setting($option)
{
	return $this->default_settings[$option];
}

    /**
     * @param array $value
     * @return bool
     */
public function set_default_settings($value)
{
	if(is_array($value))
    {
        $this->default_settings=$value;
        return true;
    }
	else return false;
}

    /**
     * @return array
     */
public function get_default_settings()
{
	return $this->default_settings;
}

public function restore_default_settings()
{
    $this->set_default_settings(array(
        CURLOPT_HEADER => false,
        CURLOPT_URL => "http://ya.ru",
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.64 Safari/537.31",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_REFERER => '',
        CURLOPT_POSTFIELDS => '',
        CURLOPT_POST => false
    ));
}
    /**
     * Включает/выключает работу через прокси и может установить прокси который задаст пользователь
     * @param bool|string $value
     * @return bool
     */
public function set_use_proxy($value=false)
{
    switch((bool)$value)
	{
		case true:
            if(is_string($value) && c_string_work::is_ip($value)) $this->proxy=$value;
            elseif(!is_object($this->proxy)) $this->proxy=new c_proxy();
			else return false;
            break;
		case false:
			unset($this->proxy);
			break;
        default: return false;
	}
	$this->use_proxy=(bool)$value;
    return true;
}
public function get_use_proxy()
{
	return $this->use_proxy;
}

    /**
     * @param int $value
     */
public function set_number_repeat($value=0)
{
	$this->number_repeat=$value;
}
public function get_number_repeat()
{
	return $this->number_repeat;
}

    /**
     * @param int $value
     */
public function set_max_number_repeat($value=10)
{
	$this->max_number_repeat=$value;
}
public function get_max_number_repeat()
{
	return $this->max_number_repeat;
}

    /**
     * Проверяет возможность сделать повторный запрос
     * @return bool
     */
private function repeat_get_content()
{
	if($this->get_number_repeat()<$this->get_max_number_repeat())
	{
		$this->next_repeat();
		return true;
	}
	else 
	{
		$this->end_repeat();
		return false;
	}
}

    /**
     * Регестрирует повторный запрос
     */
private function next_repeat()
{
	$num_repeat=$this->get_number_repeat();
	$num_repeat++;
	$this->set_number_repeat($num_repeat);
}

    /**
     * Обнуляет счетчик повторных запросов
     */
private function end_repeat()
{
	$this->set_number_repeat(0);
}

    /**
     * @param int $value
     */
public function set_min_size_answer($value=5000)
{
	$this->min_size_answer=$value;
}
public function get_min_size_answer()
{
	return $this->min_size_answer;
}

    /**
     * @param string $type_content file|img|text|html
     * @return bool
     */
public function set_type_content($type_content="text")
{
	switch($type_content)
	{
		case 'file':
			$this->type_content='file';
			$this->set_default_setting(CURLOPT_HEADER,0);
			$this->set_encoding_answer(0);
            return true;
			break;
        case 'img':
            $this->type_content='img';
            $this->set_default_setting(CURLOPT_HEADER,0);
            $this->set_encoding_answer(0);
            return true;
            break;
		case 'text':
			$this->type_content='text';
            return true;
			break;
		case 'html':
			$this->type_content='html';
			break;
		default:
            break;
	}
    return false;
}
public function get_type_content()
{
	return $this->type_content;
}

    /**
     * @param bool $value
     */
public function set_in_cache($value=false)
{
	$this->in_cache=$value;
}
public function get_in_cache()
{
	return $this->in_cache;
}

    /**
     * @param bool $value
     */
public function set_encoding_answer($value=false)
{
	$this->encoding_answer=$value;
}
public function get_encoding_answer()
{
	return $this->encoding_answer;
}

    /**
     * @param string $value
     */
public function set_encoding_name($value="UTF-8")
{
	$this->encoding_name=$value;
}
public function get_encoding_name()
{
	return $this->encoding_name;
}

    /**
     * @param string $value
     */
public function set_encoding_name_answer($value)
{
	$this->encoding_name_answer=$value;
}
public function get_encoding_name_answer()
{
	return $this->encoding_name_answer;
}

    /**
     * @param bool $value
     */
public function set_check_answer($value=true)
{
	$this->check_answer=$value;
}
public function get_check_answer()
{
	return $this->check_answer;
}

    /**
     * @param int $value
     */
public function set_count_multi_curl($value=1)
{
    if($this->get_count_multi_curl()!=$value)
    {
	    $this->close_get_content();
	    $this->count_multi_curl=$value;
        $this->set_count_multi_descriptor();
	    $this->init_get_content();
    }
}
public function get_count_multi_curl()
{
	return $this->count_multi_curl;
}
    /**
     * @param int $value
     */
public function set_count_multi_stream($value=1)
{
    if($this->get_count_multi_stream()!=$value)
    {
        $this->close_get_content();
        $this->count_multi_stream=$value;
        $this->set_count_multi_descriptor();
        $this->init_get_content();
    }
}
public function get_count_multi_stream()
{
    return $this->count_multi_stream;
}
private function set_count_multi_descriptor()
{
    $this->count_multi_descriptor=$this->get_count_multi_curl()*$this->get_count_multi_stream();
}

private function get_count_multi_descriptor()
{
    return $this->count_multi_descriptor;
}
    /**
     * @param string $new_mode_get_content single|multi
     * @return bool
     */
public function set_mode_get_content($new_mode_get_content='single')
{
	$this->close_get_content();
	switch ($new_mode_get_content)
	{
		case 'single':
			$this->mode_get_content='single';
			$this->init_get_content();
            return true;
			break;
		case 'multi':
			$this->mode_get_content='multi';
			if($this->get_count_multi_curl()<1)$this->set_count_multi_curl(1);
			return true;
            break;
		default:
			return false;
			break;
	}
}
public function get_mode_get_content()
{
	return $this->mode_get_content;
}

public function &get_descriptor()
{
	return $this->descriptor;
}
public function &get_descriptor_array()
{
	return $this->descriptor_array;
}

    /**
     * Инициализирует дескрипторы cURL
     */
private function init_get_content()
{
	switch ($this->get_mode_get_content())
	{
		case 'single':
			$this->init_single_get_content($this->get_descriptor());
			break;
		case 'multi':
			$this->init_multi_get_content($this->get_descriptor(), $this->get_descriptor_array());
			break;
		default:
			# code
			break;
	}
}

    /**
     * Инициализация дескриптора cURL в режиме single
     * @param array &$descriptor Ссылка на дескриптор cURL
     * @return void
     */
private function init_single_get_content(&$descriptor)
{
    if(!isset($descriptor['descriptor_key'])) $descriptor['descriptor_key']=microtime(1).mt_rand();
    if(!file_exists($this->get_dir_cookie().$descriptor['descriptor_key'].".cookie"))
    {
        $fh=fopen($this->get_dir_cookie().$descriptor['descriptor_key'].".cookie","w");
        fclose($fh);
    }
    $descriptor['descriptor']=curl_init();
}

    /**
     * Инициализация дескриптора cURL в режиме multi
     * @param array &$descriptor Ссылка на мульти дескриптор cURL
     * @param array &$descriptor_array ссылка на массив cURL дескрипторов
     * @return void
     */
private function init_multi_get_content(&$descriptor, &$descriptor_array)
{
    $descriptor['descriptor']=curl_multi_init();
    if(is_array($descriptor_array))
    {
        $descriptor_array=array_slice($descriptor_array, 0, $this->get_count_multi_descriptor());
    }
    for($i=0;$i<$this->get_count_multi_descriptor();$i++)
    {
        if(!isset($descriptor_array[$i]['descriptor_key'])) $descriptor_array[$i]['descriptor_key']=microtime(1).mt_rand();
        if(!file_exists($this->get_dir_cookie().$descriptor_array[$i]['descriptor_key'].".cookie"))
        {
            $fh=fopen($this->get_dir_cookie().$descriptor_array[$i]['descriptor_key'].".cookie","w");
            fclose($fh);
        }
        $descriptor_array[$i]['descriptor']=curl_init();
        curl_multi_add_handle($descriptor['descriptor'],$descriptor_array[$i]['descriptor']);
    }
}
    /**
     * Закрывает инициализированные дескрипторы cURL
     */
private function close_get_content()
{
	$descriptor=&$this->get_descriptor();
	if(isset($descriptor['descriptor']))
	{
		switch ($this->get_mode_get_content())
		{
			case 'single':
				$this->close_single_get_content($descriptor);
				break;
			case 'multi':
				$this->close_multi_get_content($descriptor, $this->get_descriptor_array());
				break;
			default:
				break;
		}	
	}
}

    /**
     * Закрывает инициализированные cURL дескриптроры в режиме single
     * @param array &$descriptor ссылка на cURL дескриптор
     * @return void
     */
private function close_single_get_content(&$descriptor)
{
    curl_close($descriptor['descriptor']);
    if($this->get_use_proxy() && is_object($this->proxy))
    {
        $this->proxy->remove_all_rent_from_code($descriptor['descriptor_key']);
    }
    unset($descriptor['descriptor']);
    unset($descriptor['option']);
}
    /**
     * Закрывает инициализированные cURL дескриптроры в режиме multi
     * @param array &$descriptor Ссылка на мульти дескриптор cURL
     * @param array &$descriptor_array ссылка на массив cURL дескрипторов
     * @return void
     */
private function close_multi_get_content(&$descriptor, &$descriptor_array)
{
    foreach ($descriptor_array as $key => $value)
    {
        if(isset($descriptor_array[$key]['descriptor']))
        {
            @curl_multi_remove_handle($descriptor['descriptor'],$descriptor_array[$key]['descriptor']);
            curl_close($descriptor_array[$key]['descriptor']);
            if($this->get_use_proxy() && is_object($this->proxy))
            {
                $this->proxy->remove_all_rent_from_code($descriptor_array[$key]['descriptor_key']);
            }
            unset($descriptor_array[$key]['descriptor']);
            unset($descriptor_array[$key]['option']);
        }
    }
    unset($value);
    @curl_multi_close($descriptor['descriptor']);
}
    /**
     * Выполнение заросов по $url с определением по какому методу осуществлять запрос
     * @param string|array $url
     * @return array|int|string
     */
public function get_content($url="")
{
	if(is_string($url) && $this->get_mode_get_content()!='single') $this->set_mode_get_content('single');
	if(is_array($url) && $this->get_mode_get_content()!='multi') $this->set_mode_get_content('multi');
	switch ($this->get_mode_get_content())
	{
			case 'single':
                $this->set_default_setting(CURLOPT_URL,$url);
				$this->get_single_content($this->get_descriptor());
				break;
			case 'multi':
				$this->set_count_multi_curl(count($url));
				$descriptor=&$this->get_descriptor();
                $descriptor_array=&$this->get_descriptor_array();
                foreach ($url as $value_url)
				{
                    foreach ($descriptor_array as &$descriptor_value)
                    {
                        if(isset($descriptor_value['descriptor']))
                        {
                            $this->set_option_to_descriptor($descriptor_value,CURLOPT_URL,$value_url);
                        }
                    }
				}
				$answer=$this->get_multi_content($descriptor,$descriptor_array);
				$tmp_answer=array();
				$j=0;
				foreach ($url as $key_url => $value_url)
				{
					for ($i=0;$i<$this->get_count_multi_stream();$i++)
					{
						if(isset($answer[$j])) $tmp_answer[$key_url][$i]=$answer[$j];
						$j++;
					}
				}
				$this->answer=$tmp_answer;
				$this->set_count_multi_curl(1);
                $this->set_count_multi_stream(1);
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
     * @param array $descriptor ссылка на дескриптор cURL
     * @return string
     */
private function get_single_content(&$descriptor=NULL)
{
	if($descriptor==NULL) $descriptor=&$this->get_descriptor();
	do{
		$this->set_options_to_descriptor($descriptor);
		$answer=$this->exec_get_content($descriptor);
        $descriptor['info']=curl_getinfo($descriptor['descriptor']);
		if(!$this->get_check_answer() && $this->check_answer_valid($descriptor['info']))
		{
			$this->answer=$answer;
			$this->end_repeat();
			break;
		}
		elseif($this->get_use_proxy() && is_object($this->proxy))
		{
			$this->proxy->remove_proxy_in_list($descriptor['option'][CURLOPT_PROXY]);
		}
	}while($this->repeat_get_content());
    $answer=$this->prepare_content($answer);
	return $answer;
}

    /**
     * Совершает запрос в режиме multi
     * @param array $descriptor multi дескриптор сURL
     * @param array $descriptor_array набор дескрипторов cURL приналдежащих multi дескриптору cURL
     * @return array
     */
private function get_multi_content(&$descriptor=NULL,&$descriptor_array=NULL)
{
	if($descriptor==NULL) $descriptor=&$this->get_descriptor();
	if($descriptor_array==NULL) $descriptor_array=&$this->get_descriptor_array();
	do{
		foreach($descriptor_array as $key => $value) $this->set_options_to_descriptor($descriptor_array[$key]);
		unset($value);
		$answer=$this->exec_get_content($descriptor,$descriptor_array);
		$good_answer=array();
		foreach ($answer as $key => $value)
		{
			$descriptor_array[$key]['info']=curl_getinfo($descriptor_array[$key]['descriptor']);
			if(!$this->get_check_answer() && $this->check_answer_valid($descriptor_array[$key]['info'])) $good_answer[$key]=$value;
			elseif($this->get_use_proxy() && is_object($this->proxy))
				{
					$this->proxy->remove_proxy_in_list($descriptor_array[$key]['option'][CURLOPT_PROXY]);
				}
		}
		if(count($good_answer))
		{
			$this->end_repeat();
			break;
		}
	}while($this->repeat_get_content());
    foreach($good_answer as &$value) $value=$this->prepare_content($value);
	$this->answer=$good_answer;
	return $this->get_answer();
}

    /**
     * Присваивает настройки cURL декскриптору
     * @param array $descriptor дескриптор cURL
     * @param array $option_array список настроек для cURL дексриптора
     * @return bool
     */
public function set_options_to_descriptor(&$descriptor,$option_array=array())
{
	foreach ($this->all_setting as $key_setting)
	{
		if(isset($option_array[$key_setting])) $this->set_option_to_descriptor($descriptor,$key_setting,$option_array[$key_setting]);
		elseif(isset($descriptor['option'][$key_setting])) $this->set_option_to_descriptor($descriptor,$key_setting,$descriptor['option'][$key_setting]);
		else $this->set_option_to_descriptor($descriptor,$key_setting);
	}
	unset($key_setting);
	if($this->get_use_proxy() && !isset($descriptor['option'][CURLOPT_PROXY]))
    {
        if(is_object($this->proxy))
        {
            $this->set_option_to_descriptor(
                                            $descriptor,
                                            CURLOPT_PROXY,
                                            $this->proxy->get_proxy(
                                                                    $descriptor['descriptor_key'],
                                                                    c_string_work::get_domain_name($descriptor['option'][CURLOPT_URL])
                                                                  )
                                            );
        }
        elseif(is_string($this->proxy))$this->set_option_to_descriptor($descriptor, CURLOPT_PROXY, $this->proxy);
    }
    $this->set_option_to_descriptor($descriptor,CURLOPT_COOKIEJAR,$this->get_dir_cookie().$descriptor['descriptor_key'].".cookie");
	$this->set_option_to_descriptor($descriptor,CURLOPT_COOKIEFILE,$this->get_dir_cookie().$descriptor['descriptor_key'].".cookie");
	if($returnSetOpt=curl_setopt_array($descriptor['descriptor'],$descriptor['option'])) return true;
    else return false; // :| ошибка в присваивании параметров

}

    /**
     * Присваивает конкретную настройку для cURL дескриптора
     * @param array $descriptor ссылка на cURL дескриптор
     * @param int $option имя параметра для cURL дескриптора
     * @param mixed $value значение опции для cURL дескриптора
     * @param int $key ключ для дескриптора в режиме multi
     * @return bool
     */
public function set_option_to_descriptor(&$descriptor,$option,$value=NULL,$key=NULL)//
{
	if($key!==NULL)
	{
		if(array_key_exists($key, $descriptor))
		{
			if(is_null($value)) $descriptor[$key]['option'][$option]=$this->get_default_setting($option);
			else $descriptor[$key]['option'][$option]=$value;
			if($this->check_option($descriptor[$key],$option,$descriptor[$key]['option'][$option])) return false;
		}
	}
	else
	{
		if(is_null($value)) $descriptor['option'][$option]=$this->get_default_setting($option);
		else $descriptor['option'][$option]=$value;
		if($this->check_option($descriptor,$option,$descriptor['option'][$option])) return true;
	}
    return true;
}

    /**
     * проверяет на корректность опции и включает/выключает зависимые опции в дескрипторе cURL
     * @param array $descriptor дескриптор cURL
     * @param int $option имя параметра
     * @param mixed $value значение параметра
     * @return bool
     */
private function check_option(&$descriptor,$option,$value=NULL)
{
	switch ($option)
	{
		case CURLOPT_POST:
			if((bool)$value) $descriptor['option'][$option]=(bool)$value;
			break;
		case CURLOPT_POSTFIELDS:
			if(!$value)
			{
				unset($descriptor['option'][$option]);
                $this->set_option_to_descriptor($descriptor,CURLOPT_POST,false);
				return true;
			}
			else $this->set_option_to_descriptor($descriptor,CURLOPT_POST,true);
			break;
		case CURLOPT_URL:
			if(!preg_match("#(http|https)://#iUm", $descriptor['option'][$option])) $descriptor['option'][$option]="http://".$value;
			if($this->get_in_cache())
			{
				preg_match("#https?://(?<url>.*)$#iUm", $descriptor['option'][$option], $match);
				$descriptor['option'][$option]="http://webcache.googleusercontent.com/search?q=cache:".$match['url'];
				return true;
			}
			break;	
		default:
			break;
	}
    return false;
}

    /**
     * Выполнение запроса cURL
     * @param array $descriptor дескриптор cURL или multi_cURL
     * @param array $descriptor_array набор дескрипторов для режима multi
     * @return mixed
     */
private function exec_get_content(&$descriptor,&$descriptor_array=NULL)
{
	switch ($this->get_mode_get_content())
	{
		case 'single':
            return $this->exec_single_get_content($descriptor);
			break;
		case 'multi':
            return $this->exec_multi_get_content($descriptor, $descriptor_array);
			break;
		default:
			break;
	}
    return false;
}


    /**
     * Выполнение запроса cURL в режиме single
     * @param array $descriptor дескриптор cURL
     * @return string
     */
private function exec_single_get_content(&$descriptor)
{
    $this->answer=curl_exec($descriptor['descriptor']);
    return $this->answer;
}

    /**
     * Выполнение запроса cURL в режиме multi
     * @param array $descriptor дескриптор multi_cURL
     * @param array $descriptor_array набор дескрипторов cURL
     * @return array
     */
private function exec_multi_get_content(&$descriptor,&$descriptor_array)
{
    do{
        curl_multi_exec($descriptor['descriptor'],$running);
        usleep(100);
    }while($running > 0);
    $this->answer=array();
    foreach($descriptor_array as $key => $value) $this->answer[$key]=curl_multi_getcontent($descriptor_array[$key]['descriptor']);
    unset($value);
    return $this->answer;
}
    /**
     * Возвращает данные полученые после запросов
     * @param bool $get_all_answer для режима multi, возваращать все или самы большой по размеру
     * @return array|string
     */
public function get_answer($get_all_answer=false)
{
	switch ($this->get_mode_get_content())
	{
		case 'single':
			return $this->answer;
			break;
		case 'multi':
			if(!$get_all_answer)
			{
				//if(is_array(current($this->answer)))
				//{
				$a=array();
				foreach ($this->answer as $key => $value) $a[$key]=$this->get_big_answer($value);
				return $a;
				//}
				//else return $this->get_big_answer($this->answer);
			}
			else return $this->answer;
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
private function get_big_answer($a)
{
	if(!function_exists("sort_array_answer"))
	{
		function sort_array_answer($a, $b)
		{
			if (strlen($a) < strlen($b)) return 1;
            elseif (strlen($a) == strlen($b)) return 0;
            else return -1;
		}
	}
	if(!is_array($a)) return false;
	usort($a, 'sort_array_answer');
	return $a[0];
}

    /**
     * Проверка ответа на корректность
     * @param array $curl_data массив информации о запросе при помощи функции curl_getinfo()
     * @return bool
     */
private function check_answer_valid($curl_data)
{
    if(!$this->http_code($curl_data['http_code'])) return false;
    if($curl_data['size_download']<$curl_data['download_content_length'] || $curl_data['size_download']<$this->get_min_size_answer()) return false;
    switch($this->get_type_content())
    {
        case 'file':
            if($this->mime_type($curl_data['content_type'],'file')) return true;
            break;
        case 'img':
            if($this->mime_type($curl_data['content_type'],'img')) return true;
            break;
        case 'html':
            if($this->mime_type($curl_data['content_type'],'html')) return true;
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
private function mime_type($mime,$type)
{
    switch($type)
    {
        case 'file':
            return true;
            break;
        case 'img':
            if(preg_match('#^image/(gif|p?jpeg|png|svg\+xml|tiff|vnd\.microsoft\.icon|vnd\.wap\.wbmp)$#i',$mime)) return true;
            else return false;
            break;
        case 'html':
            if(preg_match('#^text/html$#i',$mime)) return true;
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
private function http_code($http_code)
{
    switch((int)$http_code)
    {
        case 100: return false;
        case 101: return false;
        case 102: return false;
        case 200: return true;
        case 201: return true;
        case 202: return true;
        case 203: return true;
        case 204: return true;
        case 205: return true;
        case 206: return true;
        case 207: return true;
        case 226: return true;
        case 300: return false;
        case 301: return false;
        case 302: return false;
        case 303: return false;
        case 304: return false;
        case 305: return false;
        case 306: return false;
        case 307: return false;
        case 400: return false;
        case 401: return false;
        case 402: return false;
        case 403: return false;
        case 404: return false;
        case 405: return false;
        case 406: return false;
        case 407: return false;
        case 408: return false;
        case 409: return false;
        case 410: return false;
        case 411: return false;
        case 412: return false;
        case 413: return false;
        case 414: return false;
        case 415: return false;
        case 416: return false;
        case 417: return false;
        case 422: return false;
        case 423: return false;
        case 424: return false;
        case 425: return false;
        case 426: return false;
        case 428: return false;
        case 429: return false;
        case 431: return false;
        case 449: return false;
        case 451: return false;
        case 456: return false;
        case 499: return false;
        case 500: return false;
        case 501: return false;
        case 502: return false;
        case 503: return false;
        case 504: return false;
        case 505: return false;
        case 506: return false;
        case 507: return false;
        case 508: return false;
        case 509: return false;
        case 510: return false;
        case 511: return false;
        default: false;
    }
    return false;
}
    /**
     * Подготовка ответа к выдаче
     * @param $answer
     * @return string
     */
private function prepare_content($answer)
{
	switch ($this->get_type_content())
		{
			case 'file':
				break;
			case 'text':
				$answer=$this->encoding_answer_text($answer);
				break;
			case 'html':
				$answer=$this->encoding_answer_text($answer);
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
private function encoding_answer_text($text="")
{
	if($this->get_encoding_answer()) return iconv($this->get_encoding_name(), c_string_work::get_encoding_name($text), $text);
	else return $text;
}

}
