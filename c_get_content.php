<?php
include_once "c_proxy.php";
include_once "c_string_work.php";
/**
 * Class c_get_content
 * С помощью основных функций библиотеки cURL посылает http запросы для скачивания контента из сети
 * Умеет работать через прокси сервера, в много поточном режиме с верификацией данных.
 * @author Evgeny Pynykh <bpteam22@gmail.com>
 * @package get_content
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
     * $descriptor['option'][имя опции] = value параметры cURL
     * $descriptor['descriptor_key'] уникальный ключ дескриптора для аренды прокси и сохранения cookie
     */
    private $descriptor;
    /**
     * Список дескрипторов с текущими настройками и уникальным ключом для работы в multi режиме
     * @var array
     * $descriptor_array[key]['descriptor'] дескриптор  cURL
     * $descriptor_array[key]['option'][имя опции] = value параметры cURL
     * $descriptor_array[key]['descriptor_key'] уникальный ключ дескриптора для аренды прокси и сохранения cookie
     */
    private $descriptor_array;
    /**
     * Количество потоков в режиме multi
     * @var int
     */
    private $count_multi_curl;
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
     * Класс для изменения данных текста, проверки кодировки, сжатия данных
     * @var c_string_work
     */
    private $string_work;
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

function __construct()
{
	$this->all_setting =array(
                              CURLOPT_HEADER,
                              CURLOPT_URL,
                              CURLOPT_TIMEOUT,
                              CURLOPT_USERAGENT,
                              CURLOPT_RETURNTRANSFER,
                              CURLOPT_FOLLOWLOCATION,
                              CURLOPT_POST,
                              CURLOPT_POSTFIELDS
                              );
	$this->set_dir_cookie("/get_content_files/");
	$this->set_default_settings(array());
	$this->set_default_setting(CURLOPT_HEADER,0);
	$this->set_default_setting(CURLOPT_URL,"http://ya.ru");
	$this->set_default_setting(CURLOPT_TIMEOUT,15);
	$this->set_default_setting(CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1");
	$this->set_default_setting(CURLOPT_RETURNTRANSFER,1);
	$this->set_default_setting(CURLOPT_FOLLOWLOCATION,1);
	$this->set_default_setting(CURLOPT_POSTFIELDS, "");
	$this->set_default_setting(CURLOPT_POST,0);
	$this->set_use_proxy(false);
	$this->set_number_repeat(0);
	$this->set_max_number_repeat(10);
	$this->set_min_size_answer(5000);
	$this->set_type_content("text");
	$this->set_in_cache(false);
	$this->set_encoding_answer(false);
	$this->set_encoding_name("UTF-8");
	$this->set_check_answer(true);
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
	return dirname(__FILE__).$this->dir_cookie;
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

    /**
     * Включает/выключает работу через прокси
     * @param bool $value
     */
public function set_use_proxy($value=false)
{
    $value=(bool)$value;
	switch($value)
	{
		case true:
		if(!is_object($this->proxy) && !is_string($this->proxy)) $this->proxy=new c_proxy();
			break;
		case false:
			unset($this->proxy);
			break;
        default: return false;
	}
	$this->use_proxy=$value;
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
		default: return false;
			break;
	}
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
public function set_count_multi_curl($value=2)
{
	$this->close_get_content();
	$this->count_multi_curl=$value;
	$this->init_get_content();
}
public function get_count_multi_curl()
{
	return $this->count_multi_curl;
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
	$descriptor=&$this->get_descriptor();
	switch ($this->get_mode_get_content())
	{
		case 'single':
			if(!isset($descriptor['descriptor_key'])) $descriptor['descriptor_key']=microtime(1).mt_rand();
			if(!file_exists($this->get_dir_cookie().$descriptor['descriptor_key'].".cookie"))
			{
				$fh=fopen($this->get_dir_cookie().$descriptor['descriptor_key'].".cookie","w");
				fclose($fh);
			}
			$descriptor['descriptor']=curl_init();
			break;
		case 'multi':
			$descriptor['descriptor']=curl_multi_init();
			$descriptor_array=&$this->get_descriptor_array();
			if(is_array($descriptor_array))
			{
				$descriptor_array=array_slice($descriptor_array, 0, $this->get_count_multi_curl());
			}
			for($i=0;$i<$this->get_count_multi_curl();$i++)
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
			break;
		default:
			# code
			break;
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
				if(isset($descriptor['descriptor']))
				{
					curl_close($descriptor['descriptor']);
					if($this->get_use_proxy())
					{
						$this->proxy->remove_all_rent_from_code($descriptor['descriptor_key']);
					}
					unset($descriptor['descriptor']);
					unset($descriptor['option']);
				}
				break;
			case 'multi':
				$descriptor_array=&$this->get_descriptor_array();
				foreach ($descriptor_array as $key => $value)
				{
					if(isset($descriptor_array[$key]['descriptor']))
					{
						@curl_multi_remove_handle($descriptor['descriptor'],$descriptor_array[$key]['descriptor']);
						curl_close($descriptor_array[$key]['descriptor']);
						if($this->get_use_proxy())
						{
							$this->proxy->remove_all_rent_from_code($descriptor_array[$key]['descriptor_key']);
						}
						unset($descriptor_array[$key]['descriptor']);
						unset($descriptor_array[$key]['option']);
					}
				}
				unset($value);
				@curl_multi_close($descriptor['descriptor']);
				break;
			default:
				# code...
				break;
		}	
	}
}

    /**
     * Выполнение заросов по $url с определением по какому методу осуществлять запрос
     * @param string|array $url
     * @return array|int|string
     */
public function get_content($url="")
{
	if($url && is_string($url)) $this->set_default_setting(CURLOPT_URL,$url);
	if(is_array($url))
	{
		if($this->get_mode_get_content()=='single') $this->set_mode_get_content('multi');
	}
	switch ($this->get_mode_get_content())
	{
			case 'single':
				$descriptor=&$this->get_descriptor();
				$this->get_single_content($descriptor);
				break;
			case 'multi':
				$descriptor=&$this->get_descriptor();
				$descriptor_array=&$this->get_descriptor_array();
				if(is_string($url))
				{
					$this->get_multi_content($descriptor,$descriptor_array);
				}
				elseif(is_array($url))
				{
					$count_url=count($url);
					$count_multi_curl=$this->get_count_multi_curl();
					$count_descriptor=$count_url*$count_multi_curl;
					$this->set_count_multi_curl($count_descriptor);
					$tmp_key_array=array();
					reset($descriptor_array);
					foreach ($url as $key_url => $value_url)
					{
						for ($i=0;$i<$count_multi_curl;$i++)
						{ 
							if(isset($descriptor_array[key($descriptor_array)]['descriptor']))
							{
								$tmp_key_array[$key_url][$i]=key($descriptor_array);
								$this->set_option_to_descriptor($descriptor_array[key($descriptor_array)],CURLOPT_URL,$value_url);//,key($descriptor_array)
							}
							next($descriptor_array);
						}
					}
					$answer=$this->get_multi_content($descriptor,$descriptor_array);
					//reset($answer);
					$tmp_answer=array();
					$j=0;
					foreach ($url as $key_url => $value_url)
					{
						for ($i=0;$i<$count_multi_curl;$i++)
						{
							if(isset($answer[$j])) $tmp_answer[$key_url][$i]=$answer[$j];
							//if(isset($tmp_key_array[$key_url][$i]))
							//{
							//	$tmp_answer[$key_url][$i]=current($answer);
							//	next($answer);
							//}
							$j++;
						}
					}
					$this->answer=$tmp_answer;
					$this->set_count_multi_curl($count_multi_curl);
				}
				break;
			default:
				# code...
				break;
	}
	$this->close_get_content();
	$this->init_get_content();
	return $this->get_answer();
}

    /**
     * Совершает зарос в режиме single
     * @param cURL $descriptor ссылка на дескриптор cURL
     * @return string
     */
private function get_single_content(&$descriptor=NULL)
{
	if($descriptor==NULL) $descriptor=&$this->get_descriptor();
	do{
		$this->set_options_to_descriptor($descriptor);
		$answer=$this->exec_get_content($descriptor);
		$answer=$this->prepare_content($answer);
		if($this->check_answer_valid($answer))
		{
			$this->answer=$answer;
			$this->end_repeat();
			break;
		}
		elseif($this->get_use_proxy())
		{
			$this->proxy->remove_proxy_in_list($descriptor['option'][CURLOPT_PROXY]);
		}
	}while($this->repeat_get_content());
	return $answer;
}

    /**
     * Совершает запрос в режиме multi
     * @param multi_cURL $descriptor multi дескриптор сURL
     * @param cURL $descriptor_array набор дескрипторов cURL приналдежащих multi дескриптору cURL
     * @return array
     */
private function get_multi_content(&$descriptor=NULL,&$descriptor_array=NULL)
{
	if($descriptor==NULL) $descriptor=&$this->get_descriptor();
	if($descriptor_array==NULL) $descriptor_array=&$this->get_descriptor_array();
	do{
		foreach($descriptor_array as $key => $value)
		{
			$this->set_options_to_descriptor($descriptor_array[$key]);
		}
		unset($value);
		$answer=$this->exec_get_content($descriptor);
		$good_answer=array();
		foreach ($answer as $key => $value)
		{
			$value=$this->prepare_content($value);
			if($this->check_answer_valid($value))
			{
				$good_answer[$key]=$value;		
			}
			else
			{
				if($this->use_proxy==1 && is_object($this->proxy))
				{
					$this->proxy->remove_proxy_in_list($descriptor_array[$key]['option'][CURLOPT_PROXY]);
				}
			}
		}
		if(count($good_answer)>0)
		{
			$this->end_repeat();
			break;
		}
	}while($this->repeat_get_content());
	$this->answer=$good_answer;
	return $good_answer; 
}

    /**
     * Присваивает настройки cURL декскриптору
     * @param cURL $descriptor дескриптор cURL
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
    $this->set_option_to_descriptor(
                                    $descriptor,
                                    CURLOPT_PROXY,
                                    $this->proxy->get_proxy(
                                                            $descriptor['descriptor_key'],
                                                            c_string_work::get_domain_name($descriptor['option'][CURLOPT_URL])
                                                          )
                                    );
	$this->set_option_to_descriptor($descriptor,CURLOPT_COOKIEJAR,$this->get_dir_cookie().$descriptor['descriptor_key'].".cookie");
	$this->set_option_to_descriptor($descriptor,CURLOPT_COOKIEFILE,$this->get_dir_cookie().$descriptor['descriptor_key'].".cookie");
	if($returnSetOpt=curl_setopt_array($descriptor['descriptor'],$descriptor['option'])) return true;
    else return false; // :| ошибка в присваивании параметров

}

    /**
     * Присваивает конкретную настройку для cURL дескриптора
     * @param cURL $descriptor ссылка на cURL дескриптор
     * @param int $option имя параметра для cURL дескриптора
     * @param mixed $value значение опции для cURL дескриптора
     * @param int $key ключ для дескриптора в режиме multi
     * @return bool
     */
public function set_option_to_descriptor(&$descriptor,$option,$value=NULL,$key=NULL)//
{
	if($key!==NULL)
	{
		$descriptor=&$this->get_descriptor_array();
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
		if($this->check_option($descriptor,$option,$descriptor['option'][$option])) return false;
	}
}

    /**
     * проверяет на корректность опции и включает/выключает зависимые опции в дескрипторе cURL
     * @param cURL $descriptor дескриптор cURL
     * @param int $option имя параметра
     * @param mixed $value значение параметра
     * @return bool
     */
private function check_option(&$descriptor,$option,$value=NULL)
{
	switch ($option)
	{
		case CURLOPT_POST:
			if(!$value || !$descriptor['option'][CURLOPT_POSTFIELDS])
			{
				unset($descriptor['option'][$option]);
				return true;
			}
			break;
		case CURLOPT_POSTFIELDS:
			if(!$value)
			{
				unset($descriptor['option'][$option]);
				return true;
			}
			else
			{
				$this->set_option_to_descriptor($descriptor,CURLOPT_POST,1);
			}
			break;
		case CURLOPT_URL:
			if(!preg_match("#(http|https)://#iUm", $descriptor['option'][$option]))  $this->set_option_to_descriptor($descriptor,$option,"http://".$value);
			if($this->get_in_cache())
			{
				preg_match("#http://(?<url>.*)$#iUm", $descriptor['option'][$option], $match);
				$descriptor['option'][$option]="http://webcache.googleusercontent.com/search?q=cache:".$match['url'];
				return true;
			}
			break;	
		default:
			return false;
			break;
	}
}

    /**
     * Выполнение запроса cURL
     * @param cURL $descriptor дескриптор cURL или multi_cURL
     * @param array $descriptor_array набор дескрипторов для режима multi
     * @return mixed
     */
private function exec_get_content(&$descriptor=NULL,&$descriptor_array=NULL)
{
	switch ($this->get_mode_get_content())
	{
		case 'single':
			if(!$descriptor) $descriptor=&$this->get_descriptor();
			$this->answer=curl_exec($descriptor['descriptor']);
			return $this->answer;
			break;
		case 'multi':
			if(!$descriptor) $descriptor=&$this->get_descriptor();
			if(!$descriptor_array) $descriptor_array=&$this->get_descriptor_array();
			do {
			    	$error=curl_multi_exec($descriptor['descriptor'],$running);
			    	usleep(100);
				} while($running > 0);
			$this->answer=array();
			foreach($descriptor_array as $key => $value)
			{
				$this->answer[$key]=curl_multi_getcontent($descriptor_array[$key]['descriptor']);
			}	
			unset($value);
			return $this->answer;
			break;
		default:
			# code...
			break;
	}
}

    /**
     * Возвращает данные полученые после запросов
     * @param bool $get_all_answer для режима multi, возваращать все или самы большой по размеру
     * @return array|string
     */
public function get_answer($get_all_answer=true)
{
	switch ($this->get_mode_get_content())
	{
		case 'single':
			return $this->answer;
			break;
		case 'multi':
			if(!$get_all_answer)
			{
				if(is_array(current($this->answer)))
				{
					$a=array();
					foreach ($this->answer as $key => $value)
					{
						$a[$key]=$this->get_big_answer($value);
					}
					return $a;
				}
				else return $this->get_big_answer($this->answer);
			}
			else return $this->answer;
			break;
		default:
			# code...
			break;
	}
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
     * @param string $answer
     * @return bool
     */
private function check_answer_valid($answer)
{
	if(!$this->get_check_answer()) return true;
	if($this->get_use_proxy() && $this->get_type_content()=="file")
	{
		$reg="/(<!DOCTYPE HTML|<html>|<head>|<title>|<body>|<h1>|<h2>|<h3>)/i";
		if(preg_match($reg, $answer)) return false;
	}
	if(strlen($answer)>=$this->get_min_size_answer())
	{
		if($this->get_type_content()=="html")
		{
			if(preg_match("|<html[^>]*>.*</html>|iUm", $answer)) return true;
			else return false;
		}
		else return true;
	}
	else return false;
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
				$answer=c_string_work::clear_note($answer,array("/\s+/","/&nbsp;/i","/\n/i","/\r\n/i"));
				break;
			default:
				break;
		}
	return $answer;
}
/*TODO: Вынести эту функцию в c_string_work*/
private function encoding_answer_text($text="")
{
	if($this->get_encoding_answer())
	{
		$to=$this->get_encoding_name();
		$from=$this->check_encoding_answer($text);
		return mb_convert_encoding($text,$to,$from);
	}
	else
	{
		return $text;
	}
}
    /*TODO: Вынести эту функцию в c_string_work*/
private function check_encoding_answer($text="")
{
	$reg="/Content.Type.*text.html.*charset=([^\s\"']+)(?:\s|\"|'|;)/iU";
	if(preg_match($reg, $text,$match))
	{
		if(preg_match("/1251/",$match[1])) $code="cp1251";
		else $code=$match[1];
		$this->set_encoding_name_answer($code);
	}
	else
	{
		$this->set_encoding_name_answer(mb_detect_encoding($text));
	}
	return $this->get_encoding_name_answer();
}

}
?>