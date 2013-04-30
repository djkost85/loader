<?php
include_once "c_proxy.php";
include_once "c_string_work.php";
class c_get_content
{
	private $default_settings; //Настройки по умолчанию, если не задано значение, то берет из этого списка
	//CURLOPT_HEADER - для включения заголовков в вывод. bool
	//CURLOPT_URL - Загружаемый URL. Данный параметр может быть также установлен при инициализации сенса с помощью curl_init(). string
	//CURLOPT_TIMEOUT - Максимально прозволенное количество секунд для выполнения cURL-функций. int
	//CURLOPT_USERAGENT - Содержимое заголовка "User-Agent: ", посылаемого в HTTP-запросе. string
	//CURLOPT_PROXY - HTTP-прокси, через который будут направляться запросы.
	//CURLOPT_RETURNTRANSFER - для возврата результата передачи в качестве строки из curl_exec() вместо прямого вывода в браузер.
	//CURLOPT_REFERER - Содержимое заголовка "Referer: ", который будет использован в HTTP-запросе, с какой страници перешли на $URL.string
	//CURLOPT_FOLLOWLOCATION - для следования любому заголовку "Location: ", отправленному сервером в своем ответе (учтите, что это происходит рекурсивно, PHP будет следовать за всеми посылаемыми заголовками "Location: ", за исключением случая, когда установлена константа CURLOPT_MAXREDIRS).
	//CURLOPT_POST отправлять ли POST запросы 1 да 0 нет
	//CURLOPT_POSTFIELDS Все данные, передаваемые в HTTP POST-запросе. Для передачи файла, укажите перед именем файла @, а также используйте полный путь к файлу. Тип файла также может быть указан с помощью формата ';type=mimetype', следующим за именем файла. Этот параметр может быть передан как в качестве url-закодированной строки, наподобие 'para1=val1&para2=val2&...', так и в виде массива, ключами которого будут имена полей, а значениями - их содержимое.
	private $all_setting;// массив с перечислением всех настроек для cURL
	private $use_proxy; // использовать прокси или нет 1 да 0 нет
	public $proxy; // прокси для использование в cURL, класс __construct для работы с прокси серверами
	private $answer; // возвращенный ответ из функций curl_exec() и curl_multi_exec() c curl_multi_getcontent() в зависимости от режима работы класса
	private $descriptor; // массив с компонентами ['descriptor'] дескриптор инициируемый при помощи функции curl_init() или curl_multi_init() в зависимости от режима работы класса, ['option'][имя опции] значение в value ['descriptor_key'] идентификационный код для дискриптора по которому будут присваиваться файлы cookie, аренда proxy
	private $descriptor_array; // массив массивов с дескрипторами для работы в режиме multi структура опций и  идентификационных кодов схожа с $descriptor descriptor_array[key]['descriptor']
	private $count_multi_curl; // количество дескрипторов для режима multi
	private $number_repeat;// Номер повтора для получения коректного ответа
	private $max_number_repeat; // максимальное количество повторных запросов на получение контента
	private $min_size_answer; //Минимальная длинна ответа от сервера(по байтово)
	private $type_content;//Тип контента (Файл[file]|Текст[text]|html страницы[html])
	private $in_cache;//Если страница не доступна, то забирать контент из кеша гугла
	private $encoding_answer;// Изменять кодировку текста? 1 да 0 нет
	private $encoding_name;//Имя кодировки в которую преобразовывать ответ если включена декадировка
	private $encoding_name_answer; // базовая кадировка текста полученным от донора
	private $check_answer; //Вкл/выкл проверку результата
	private $string_work;//Класс для работы с строкой c_string_work для сжатия, проверки данных
	private $mode_get_content;//Тип скачивания контента multi или single
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
	$this->set_dir_cookie(dirname(__FILE__)."/get_content_files/");
	$this->set_default_settings(array());
	$this->set_default_setting(CURLOPT_HEADER,0);
	$this->set_default_setting(CURLOPT_URL,"http://ya.ru");
	$this->set_default_setting(CURLOPT_TIMEOUT,15);
	$this->set_default_setting(CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1");
	$this->set_default_setting(CURLOPT_RETURNTRANSFER,1);
	$this->set_default_setting(CURLOPT_FOLLOWLOCATION,1);
	$this->set_default_setting(CURLOPT_POSTFIELDS, "");
	$this->set_default_setting(CURLOPT_POST,0);
	$this->set_use_proxy(0);
	$this->set_number_repeat(0);
	$this->set_max_number_repeat(10);
	$this->set_min_size_answer(5000);
	$this->set_type_content("text");
	$this->set_in_cache(0);
	$this->set_encoding_answer(0);
	$this->set_encoding_name("UTF-8");
	$this->set_check_answer(1);
	$this->set_mode_get_content('single');
	$this->string_work =new c_string_work();
	$this->clear_cookie();

}
//тест на доступность ресурсов для работы класса
public function function_check()
{
	if(!function_exists('curl_init')) echo "Error: CURL is not installed\n";
	if(!is_dir($this->get_dir_cookie()))
	{
		echo "Warning: folder for the cookie does not exist\n";
		echo "try to create\n";
		if(!mkdir($this->get_dir_cookie())) echo "Warning: Can not create folder\n";
		else echo "Success: cookie folder is created\n";
	}
	else
	{
		if(!is_readable($this->get_dir_cookie()) || !is_writable($this->get_dir_cookie()))
		{
			echo "Warning: folder for the cookie does not have the necessary rights to use\n";
			echo "trying to change\n";
			if(!chmod($this->get_dir_cookie(),0777)) echo "Warning: I can not change the access rights\n";
			else echo "Success: The rules have changed for the necessary\n";
		}
	}
	if(!class_exists('c_proxy')) echo "Warning: c_proxy class is declared, can not work with proxy\n";
	if(!class_exists('c_string_work')) echo "Warning: c_string_work class is declared, word processing is not possible\n";
}

public function clear_cookie($storage_time=172800)
{
	$file_list = glob($this->get_dir_cookie()."*.cookie");
	foreach ($file_list as $key => $value)
	{
		preg_match("/\/(?<create_time>\d+)(?:\.|\s*)\d*\.cookie$/iU", $value,$match);
		if((int)$match['create_time']<time()-$storage_time)
		{
			unlink($value);
		}
	}
}

public function set_dir_cookie($new_dir_cookie)
{
	$this->dir_cookie=$new_dir_cookie;
}
public function get_dir_cookie()
{
	return $this->dir_cookie;
}

public function set_default_setting($option,$value)
{
	$this->default_settings[$option]=$value;
}
public function get_default_setting($option)
{
	return $this->default_settings[$option];
}
public function set_default_settings($value)
{
	if(is_array($value))
    {
        $this->default_settings=$value;
        return 1;
    }
	else return 0;
}
public function get_default_settings()
{
	return $this->default_settings;
}

public function set_use_proxy($value=0)
{
	switch($value)
	{
		case'1':
		if(!isset($this->proxy) || !is_object($this->proxy))
			{
				$this->proxy=new c_proxy();
			}
			break;
		case'0':
			unset($this->proxy);
			break;
		default:
			unset($this->proxy);
			break;
	}
	$this->use_proxy=$value;
}
public function get_use_proxy()
{
	return $this->use_proxy;
}

public function set_number_repeat($value=0)
{
	$this->number_repeat=$value;
}
public function get_number_repeat()
{
	return $this->number_repeat;
}

public function set_max_number_repeat($value=10)
{
	$this->max_number_repeat=$value;
}
public function get_max_number_repeat()
{
	return $this->max_number_repeat;
}
private function repeat_get_content()
{
	if($this->get_number_repeat()<$this->get_max_number_repeat())
	{
		$this->next_repeat();
		return 1;
	}
	else 
	{
		$this->end_repeat();
		return 0;
	}
}
private function next_repeat()
{
	$num_repeat=$this->get_number_repeat();
	$num_repeat++;
	$this->set_number_repeat($num_repeat);
}
private function end_repeat()
{
	$this->set_number_repeat(0);
}

public function set_min_size_answer($value=5000)
{
	$this->min_size_answer=$value;
}
public function get_min_size_answer()
{
	return $this->min_size_answer;
}

public function set_type_content($type_content="text")
{
	switch($type_content)
	{
		case 'file':
			$this->type_content='file';
			$this->set_default_setting(CURLOPT_HEADER,0);
			$this->set_encoding_answer(0);
			break;
		case 'text':
			$this->type_content='text';
			break;
		case 'html':
			$this->type_content='html';
			break;
		default:
			break;
	}
}
public function get_type_content()
{
	return $this->type_content;
}

public function set_in_cache($value=0)
{
	$this->in_cache=$value;
}
public function get_in_cache()
{
	return $this->in_cache;
}

public function set_encoding_answer($value=0)
{
	$this->encoding_answer=$value;
}
public function get_encoding_answer()
{
	return $this->encoding_answer;
}

public function set_encoding_name($value="UTF-8")
{
	$this->encoding_name=$value;
}
public function get_encoding_name()
{
	return $this->encoding_name;
}

public function set_encoding_name_answer($value)
{
	$this->encoding_name_answer=$value;
}
public function get_encoding_name_answer()
{
	return $this->encoding_name_answer;
}

public function set_check_answer($value=1)
{
	$this->check_answer=$value;
}
public function get_check_answer()
{
	return $this->check_answer;
}

public function set_count_multi_curl($value=1)
{
	$this->close_get_content();
	$this->count_multi_curl=$value;
	$this->init_get_content();
}
public function get_count_multi_curl()
{
	return $this->count_multi_curl;
}

public function set_mode_get_content($new_mode_get_content='single')
{
	$this->close_get_content();
	switch ($new_mode_get_content)
	{
		case 'single':
			$this->mode_get_content='single';
			$this->init_get_content();
			break;
		case 'multi':
			$this->mode_get_content='multi';
			if($this->get_count_multi_curl()<1)$this->set_count_multi_curl(1);
			break;
		default:
			return 0;
			break;
	}
    return 1;
}
public function get_mode_get_content()
{
	return $this->mode_get_content;
}

public function set_proxy($proxy,$key=0)
{
	$this->set_use_proxy(1);
	switch ($this->get_mode_get_content())
	{
		case 'single':
			$descriptor=&$this->get_descriptor();
			$this->proxy->add_proxy($proxy,$descriptor['descriptor_key']);
			break;
		case 'multi':
			$descriptor_array=&$this->get_descriptor_array();
			if(array_key_exists($key,$descriptor_array)) $this->proxy->add_proxy($proxy,$descriptor_array[$key]['descriptor_key']);
			break;
		default:
			return 0;
			break;
	}
}

public function &get_descriptor()
{
	//$descriptor= &$this->descriptor;
	//return $descriptor;
	return $this->descriptor;
}
public function &get_descriptor_array()
{
	//$descriptor= &$this->descriptor_array;
	//return $descriptor;
	return $this->descriptor_array;
}

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
			# code...
			break;
	}
}
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
                                                            $this->string_work->get_domain_name($descriptor['option'][CURLOPT_URL])
                                                          )
                                    );
	$this->set_option_to_descriptor($descriptor,CURLOPT_COOKIEJAR,$this->get_dir_cookie().$descriptor['descriptor_key'].".cookie");
	$this->set_option_to_descriptor($descriptor,CURLOPT_COOKIEFILE,$this->get_dir_cookie().$descriptor['descriptor_key'].".cookie");
	if(!$returnSetOpt=curl_setopt_array($descriptor['descriptor'],$descriptor['option']))
	{
		// :|
	}
}
public function set_option_to_descriptor(&$descriptor,$option,$value=NULL,$key=NULL)//
{
	if($key!==NULL)
	{
		$descriptor=&$this->get_descriptor_array();
		if(array_key_exists($key, $descriptor))
		{
			if(is_null($value)) $descriptor[$key]['option'][$option]=$this->get_default_setting($option);
			else $descriptor[$key]['option'][$option]=$value;
			if($this->check_option($descriptor[$key],$option,$descriptor[$key]['option'][$option])) return 0;
		}
	}
	else
	{
		if(is_null($value)) $descriptor['option'][$option]=$this->get_default_setting($option);
		else $descriptor['option'][$option]=$value;
		if($this->check_option($descriptor,$option,$descriptor['option'][$option])) return 0;
	}
}

private function check_option(&$descriptor,$option,$value=NULL)
{
	switch ($option)
	{
		case CURLOPT_POST:
			if(!$value || !$descriptor['option'][CURLOPT_POSTFIELDS])
			{
				unset($descriptor['option'][$option]);
				return 1;
			}
			break;
		case CURLOPT_POSTFIELDS:
			if(!$value)
			{
				unset($descriptor['option'][$option]);
				return 1;
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
				return 1;
			}
			break;	
		default:
			return 0;
			break;
	}
}

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

public function get_answer($get_all_answer=1)
{
	switch ($this->get_mode_get_content())
	{
		case 'single':
			return $this->answer;
			break;
		case 'multi':
			if($get_all_answer==0)
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
				else
				{
					return $this->get_big_answer($this->answer);
				}
			}
			else
			{
				return $this->answer;
			}
			break;
		default:
			# code...
			break;
	}
}
// Получить максимально большой ответ
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
	if(!is_array($a)) return 0;
	usort($a, 'sort_array_answer');
	return $a[0];
}

private function check_answer_valid($answer)
{
	if(!$this->get_check_answer()) return 1;
	if($this->get_use_proxy() && $this->get_type_content()=="file")
	{
		$reg="/(<!DOCTYPE HTML|<html>|<head>|<title>|<body>|<h1>|<h2>|<h3>)/i";
		if(preg_match($reg, $answer)) return 0;
	}
	if(strlen($answer)>=$this->get_min_size_answer())
	{
		if($this->get_type_content()=="html")
		{
			if(preg_match("|<html[^>]*>.*</html>|iUm", $answer)) return 1;
			else return 0;
		}
		else return 1;
	}
	else return 0;
}

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
				$answer=$this->string_work->clear_note($answer,array("/\s+/","/&nbsp;/i","/\n/i","/\r\n/i"));
				break;
			default:
				break;
		}
	return $answer;
}

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