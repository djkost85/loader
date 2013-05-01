<?php
include_once "c_proxy.php";
include_once "c_get_content.php";
/*
Класс для обработки строки и извлечения необходимой информации используя набор фильтров
Методы
__construct($text) Конструктор в качестве параметра принимает текст для обработки
divided_text($text, $part_size,$offset)//Разбивает строку по предложениям для обработки через переводчик или синонимайзер в котором есть лимит на размер строки. $text - обрабатываемый текст, $part_size - максимальный размер строки для обрезки, $offset - максимальное количество частей для нарезки.
[НЕ РЕАЛИЗОВАННО] clear_note($text,$rep_text_array) уберает из текста не нужные знаки. $text обрабатываемый текст, $rep_text_array массив состоящий из стираемых элементов.
encrypt_tag($text,$reg) заменяет необходимый текст(HTML теги) на хеш для обработки через переводчик или синонимайзер и тд. $text обрабатываемый текст, $reg регулярное выражение по которому выбераются теги.
decrypt_tag($text,$cript_tag_array) Возвращают обратно в текст HTML теги после обработки функцией encrypt_tag(). $text текст с зашифроваными частями, $cript_tag_array набор асоциаций, тегов и хешей.
between_tag($text,$start_tag,$without_tag) вырезает контент в пределах тега. $text текст в котором осуществляется поиск, $start_tag искомый тег, $without_tag оставлять тег или без него.
get_domain_name($url) возвращает доменное имя сайта без субдоменов и структуры каталогов
[НЕ РЕАЛИЗОВАННО] compareText($text1,$text2,$register=1) ищет различия в строках между $text1 и $text2, $register обращать внимание на регистр или нет.
*/

/**
 * Class c_string_work
 * Класс для обработки строки и извлечения необходимой информации используя набор фильтров
 * @author Evgeny Pynykh <bpteam22@gmail.com>
 * @package get_content
 * @version 2.0
 */
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
     * Массив состоящий из частей одного большого текста разбитый на примерно равные части не более заданых знаков,
     * с сохранением структуры предложений.
     * @var array
     */
    private $divided_text_array;
    /**
     * Текст который обрабатывается в классе
     * @var string
     */
    private $text;//Текст который обрабатывается в классе

function __construct($text="")
{
	$this->text=$text;
}
//
    /**
     * Разбивает на массив текст заданной величина скрипт вырезает с сохранением предложений
     * @param string $text разбиваемый текст
     * @param int $part_size размер части
     * @param int $offset максимальное количество частей 0=бесконечно
     * @return array
     */
public function divided_text($text="", $part_size=4900,$offset=0)
{	
	if($text=="")$text=$this->text;
	$this->divided_text_array=array();
	if(strlen($text)>$part_size)
	{
		for($i=0;($i<$offset || $offset==0) && $text;$i++)
		{
		    $part_text=substr($text,0,$part_size);
		    preg_match("/^(.*\.)[^\.]*$/i",$part_text,$match);
		    if(strlen($match[1])==0) break;
		    $this->divided_text_array[]=$match[1];
		    $text=trim(str_replace($match[1],"",$text));
		}		
	}
	else
	{
		$this->divided_text_array[]=$text;
	}
	return $this->divided_text_array;
}
    /**
     * Стирание спец. символов, двойных и более пробелов, табуляций и переводов строки
     * @param string $text
     * @param array $rep_text_array массив регулярных выражений для выполнения
     * @return string
     */
    public function clear_note($text="",$rep_text_array=array("/\s+/"))
{
		if($text==="")$text=$this->text;
		if(!is_array($rep_text_array) && is_string($rep_text_array)) $text=preg_replace($rep_text_array, " ", $text);
		else foreach ($rep_text_array as $key => $value) $text=preg_replace($value, " ", $text);
		return $this->text=$text;
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
			$this->cript_tag_array['hash'][$i]=md5($str);
			$this->cript_tag_array['tag'][$i]=$str;
			$text=str_replace($this->cript_tag_array['tag'][$i], $this->cript_tag_array['hash'][$i], $text);
		}
		return $this->text=$text;
}
    /**
     * Заменяет хеш на HTML код после обработки через функцию encrypt_tag
     * @param string $text текст с хешами
     * @param array $cript_tag_array массив с ключами
     * @return string
     */
public function decrypt_tag($text="",$cript_tag_array=array())
{
	if(count($cript_tag_array))$cript_tag_array=$this->cript_tag_array;
	if(!$text)$text=$this->text;
	for($i=0;$i<count($cript_tag_array['hash']);$i++)
	{
		$text=str_replace($cript_tag_array['hash'][$i], $cript_tag_array['tag'][$i], $text);
	}
	return $this->text=$text;
}

/*TODO: Вынести функцию в parse_url*/
    /**
     * Вытаскивает доменное имя из url
     * @param $url исходный адрес
     * @return bool|string
     */
public function get_domain_name($url)
{
	$reg="/(?:http:\/\/|\s*)[-\w\.]*(?<domain>(?:[-\w]+)\.+(?:[-\w]+))(?:\/|$|\?)/iU";
	if(preg_match($reg, $url,$match)) return $match['domain'];
	else return 0;
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
	$count=0;
	//Проверяем тег это или нет
	if(!preg_match('#<(?<tag>\w+)[^>]*>#im', $start_tag,$tag)) return "";
	// Есть ли параметры
	if(preg_match('#<(?<tag>\w+)\s*[\w-]+=[\"\']+[^\'\"]+[\"\']+[^>]*>#im', $start_tag))
	{
		// Выдергиваем все параметры
		$countParametr=preg_match_all("#(?<parametr>[\w-]+=[\"\']+[^\'\"]+[\"\']+)#im", $start_tag, $matches);
		// Составляем регулярное выражение
		$reg="#<".$tag["tag"]."\s*";
		//var_dump($matches["parametr"]);
		do{
			$reg.="[^>]*".current($matches["parametr"])."[^>]*";
		}while(next($matches["parametr"]));
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
	$tag_name=$tag["tag"];
	unset($tag);
	$open_tag="<".$tag_name;
	$close_tag="</".$tag_name;
	$text=substr($text,strpos($text,$start_tag));
	$pos_end=0;
	for($i=0;$i<1000;$i++)
	{
		$pos_open_tag=strpos($text,$open_tag,$pos_end);
		$pos_close_tag=strpos($text,$close_tag,$pos_end);
		if($pos_open_tag===false)
		{
			$pos_open_tag=$pos_close_tag+1;
			//$pos_end+=$pos_close_tag+1-$pos_end;
			//break;
		}
		if($pos_open_tag<$pos_close_tag)
		{
			$count++;
			$pos_end+=$pos_open_tag+1-$pos_end;
		}
		else
		{
			$count--;
			$pos_end+=$pos_close_tag+1-$pos_end;
		}
		if(!$count)
		{
			break;
		}
	}
	if($without_tag)
	{
		$return_text=substr($text,strlen($start_tag),$pos_end-strlen($start_tag)-1);
	}
	else
	{
		$return_text=substr($text,0,$pos_end+strlen($tag_name)+2);
	}
	return $return_text;
}

    /**
     * Аналог встроеной функции parse_url но с дополнительным разбитием на масив параметры query и fragment
     * @param $url
     * @return mixed
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

}
?>