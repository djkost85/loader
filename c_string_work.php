<?php
/**
 * Class c_string_work
 * Класс для обработки строки и извлечения необходимой информации используя набор фильтров
 * @author Evgeny Pynykh <bpteam22@gmail.com>
 * @package get_content
 * @version 2.0
 */
namespace get_content\c_string_work;
class c_string_work
{
    /**
     * Массив с тегами и хеш кодами для обработки через синонимайзер или переводчик, чтоб не потерять HTML теги
     * $cript_tag_array['tag'] набор тегов
     * $cript_tag_array['hash'] набор хешей
     * Порядок тегов и хешей соответстует их положению в строке.
     * @var array
     */
    private $cript_tag_array;
    /**
     * Текст который обрабатывается в классе
     * @var string
     */
    private $text;//Текст который обрабатывается в классе

function __construct($text="")
{
	$this->text=$text;
}

    /**
     * функция для проверки доступа к необходимым ресурсам системы
     */
public function function_check()
{
    echo "c_string_work->function_check {</br>\n";
    $mess='';
    if(!is_dir(dirname(__FILE__)."/strin_work_files"))
    {
        $mess.="Warning: folder for class files does not exist</br>\n";
    }
    else
    {
        if(!is_readable(dirname(__FILE__)."/strin_work_files"))
        {
            $mess.="Warning: folder for the cookie does not have the necessary rights to use</br>\n";
        }
    }
    if($mess) echo $mess." To work correctly, correct the above class c_string_work requirements</br>\n ";
    else echo "c_string_work ready</br>\n";
    echo "c_string_work->function_check }</br>\n";
}

    /**
     * Разбивает на массив текст заданной величина скрипт вырезает с сохранением предложений
     * @param string $text разбиваемый текст
     * @param int $part_size размер части
     * @param int $offset максимальное количество частей 0=бесконечно
     * @return array
     */
public static function divided_text($text="", $part_size=4900,$offset=0)
{	
	$divided_text_array=array();
	if(strlen($text)>$part_size)
	{
		for($i=0;($i<$offset || $offset==0) && $text;$i++)
		{
		    $part_text=substr($text,0,$part_size);
		    preg_match("/^(.*\.)[^\.]*$/i",$part_text,$match);
		    if(strlen($match[1])==0) break;
		    $divided_text_array[]=$match[1];
		    $text=trim(str_replace($match[1],"",$text));
		}		
	}
	else
	{
		$divided_text_array[]=$text;
	}
	return $divided_text_array;
}
    /**
     * Стирание спец. символов, двойных и более пробелов, табуляций и переводов строки
     * @param string $text
     * @param array $rep_text_array массив регулярных выражений для выполнения
     * @return string
     */
public static function clear_note($text="",$rep_text_array=array("/\s+/"))
{
		if(is_string($rep_text_array)) $text=preg_replace($rep_text_array, " ", $text);
		elseif(is_array($rep_text_array)) foreach ($rep_text_array as $value) $text=preg_replace($value, " ", $text);
		return $text;
}
    /**
     * Заменяет HTML код  на хеши, чтоб при пропуске через спец программы не потерять теги(синонимайзей, переводчик)
     * @param string $text шифруемый текст
     * @param string $reg регулярное выражение для поиска шифруемых данных
     * @return string
     */
public function encrypt_tag($text="",$reg="/(<[^<>]*>)/iUsm")
{
		if(!$text)$text=$this->text;
		$count=preg_match_all($reg, $text, $matches);
		for($i=0;$i<$count;$i++)
		{
			$str=$matches[0][$i];
			$this->cript_tag_array['hash'][$i]=" ".microtime(1).mt_rand()." ";
			$this->cript_tag_array['tag'][$i]=$str;
			$text=preg_replace("#".preg_quote($this->cript_tag_array['tag'][$i],'#')."#ms", $this->cript_tag_array['hash'][$i], $text);
		}
		return $this->text=$text;
}
    /**
     * Заменяет хеш на HTML код после обработки через функцию encrypt_tag
     * @param string $text текст с хешами
     * @return string
     */
public function decrypt_tag($text="")
{
	if(!$text)$text=$this->text;
    foreach ($this->cript_tag_array['hash'] as $key => $value)
    {
        $text=preg_replace("#".preg_quote($this->cript_tag_array['hash'][$key],'#')."#ms", $this->cript_tag_array['tag'][$key], $text);
    }
	return $this->text=$text;
}

    /**
     * Вытаскивает доменное имя из url
     * @param $url исходный адрес
     * @return bool|string
     */
public static function get_domain_name($url)
{
	$url_data=c_string_work::parse_url($url);
    return $url_data['host'];
}

public function get_cript_tag_array()
{
	return $this->cript_tag_array;
}

public function get_text()
{
	return $this->text;
}

public function set_text($text)
{
	$this->text=$text;
}

    /**
     * Парсит html страницу и вытаскивает содержимое тега
     * @param string $text текст в котором ищет
     * @param string $start_tag открывающий тег
     * @param bool $without_tag возвращать с тегом или без
     * @return string
     */
public static function between_tag($text="",$start_tag='<div class="xxx">',$without_tag=true)
{
	if(!preg_match('#<(?<tag>\w+)[^>]*>#im', $start_tag,$tag)) return "";
	if(preg_match('#<(?<tag>\w+)\s*[\w-]+=[\"\']+[^\'\"]+[\"\']+[^>]*>#im', $start_tag))
	{
		preg_match_all("#(?<parametr>[\w-]+=[\"\']+[^\'\"]+[\"\']+)#im", $start_tag, $matches);
		$reg="#<".preg_quote($tag["tag"])."\s*";
        foreach ($matches['parametr'] as $value) $reg.="[^>]*".preg_quote($value)."[^>]*";
		$reg.=">#im";
		if(!preg_match($reg,$text,$match)) return "";
		$start_tag=$match[0];
	}
	else
	{
		preg_match('/<(?<tag>[^\s]+)[^>]*>/i', $start_tag, $tag);
		preg_match('/<(?<tag>'.preg_quote($tag[1]).')[^>]*>/i', $text, $tag);
	}
	unset($match);
    unset($matches);
	$tag_name=$tag["tag"];
	unset($tag);
	$open_tag="<".$tag_name;
	$close_tag="</".$tag_name;
	$text=substr($text,strpos($text,$start_tag));
    $count_open_tag=0;
	$pos_end=0;
    $count_tag=preg_match_all('#'.preg_quote($open_tag,'#').'#ims',$text);
	for($i=0;$i<$count_tag;$i++)
	{
		$pos_open_tag=strpos($text,$open_tag,$pos_end);
		$pos_close_tag=strpos($text,$close_tag,$pos_end);
		if($pos_open_tag===false)
		{
			$pos_open_tag=$pos_close_tag+1;
		}
		if($pos_open_tag<$pos_close_tag)
		{
			$count_open_tag++;
			$pos_end+=$pos_open_tag+1-$pos_end;
		}
		else
		{
			$count_open_tag--;
			$pos_end+=$pos_close_tag+1-$pos_end;
		}
		if(!$count_open_tag)
		{
			break;
		}
	}
	if($without_tag) $return_text=substr($text,strlen($start_tag),$pos_end-strlen($start_tag)-1);
	else $return_text=substr($text,0,$pos_end+strlen($tag_name)+2);

	return $return_text;
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
public static function parse_url($url)
{
    $array_url=parse_url($url);
    if(isset($array_url['query']))
    {
        $array_query=explode("&",$array_url['query']);
        unset($array_url['query']);
        foreach ($array_query as $value)
        {
            $part_query=explode("=",$value);
            $array_url['query'][$part_query[0]]=$part_query[1];
        }
    }
    if(isset($array_url['fragment']))
    {
        $array_fragment=explode("&",$array_url['fragment']);
        unset($array_url['fragment']);
        foreach ($array_fragment as $value)
        {
            $part_fragment=explode("=",$value);
            $array_url['fragment'][$part_fragment[0]]=$part_fragment[1];
        }
    }
    return $array_url;
}

    /**
     * Проверяет строку на соответствие шаблону ip адреса с портом
     * @param $str
     * @return bool
     */
public static function is_ip($str)
{
    if(preg_match("#^\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(:{1}\d{1,10})?)\s*$#i",$str)) return true;
    else return false;
}

    /**
     * Функция для определения кодировки русского текста
     * @param string $str строка для определения кодировки
     * @return string имя кодировки
     * @author m00t
     * @url https://github.com/m00t/detect_encoding
     */
public static function get_encoding_name($str)
{
    if(mb_detect_encoding($str,array('UTF-8'),true)=='UTF-8') return 'UTF-8';
    $weights = array();
    $specters = array();
    $possible_encodings = array('windows-1251', 'koi8-r', 'iso8859-5');
    foreach ($possible_encodings as $encoding)
    {
        $weights[$encoding] = 0;
        $specters[$encoding] = require 'string_work_files/specters/'.$encoding.'.php';
    }
    foreach(str_split($str,2) as $key)
    {
        foreach ($possible_encodings as $encoding)
        {
            if (isset($specters[$encoding][$key]))
            {
                $weights[$encoding] += $specters[$encoding][$key];
            }
        }
    }
    unset($key);
    $sum_weight = array_sum($weights);
    foreach ($weights as $encoding => $weight)
    {
        $weights[$encoding] = $weight / $sum_weight;
    }
    arsort($weights,SORT_NUMERIC);
    return key($weights);
}

}