<?php
include_once "proxy.php";
include_once "get_content.php";
/**
Класс для обработки строки и извлечения необходимой информации используя набор фильтров
Методы
string_work($text) Конструктор в качестве параметра принимает текст для обработки
dividedText($text, $partSize,$offset)//Разбивает строку по предложениям для обработки через переводчик или синонимайзер в котором есть лимит на размер строки. $text - обрабатываемый текст, $partSize - максимальный размер строки для обрезки, $offset - максимальное количество частей для нарезки.
[НЕ РЕАЛИЗОВАННО] clearNote($text,$repTextArray) уберает из текста не нужные знаки. $text обрабатываемый текст, $repTextArray массив состоящий из стираемых элементов.
encryptTag($text,$reg) заменяет необходимый текст(HTML теги) на хеш для обработки через переводчик или синонимайзер и тд. $text обрабатываемый текст, $reg регулярное выражение по которому выбераются теги.
decryptTag($text,$criptTagArray) Возвращают обратно в текст HTML теги после обработки функцией encryptTag(). $text текст с зашифроваными частями, $criptTagArray набор асоциаций, тегов и хешей.
betweenTag($text,$startTag,$withoutTag) вырезает контент в пределах тега. $text текст в котором осуществляется поиск, $startTag искомый тег, $withoutTag оставлять тег или без него.
getDomainName($url) возвращает доменное имя сайта без субдоменов и структуры каталогов
[НЕ РЕАЛИЗОВАННО] compareText($text1,$text2,$register=1) ищет различия в строках между $text1 и $text2, $register обращать внимание на регистр или нет.
**/
class string_work
{
	private $criptTagArray;//Массив с тегами и хеш кодами для обработки через синонимайзер или переводчик, чтоб не потерять HTML теги. $criptTagArray['tag'] - набор тегов $criptTagArray['hash'] - набор хешей. Порядок тегов и хешей соответстует их значению в строке.
	private $dividedTextArray;//Массив разбитого текста для обработки через синонимайзер или переводчик
	private $text;//Текст который обрабатывается в классе

function string_work($text="")
{
	$this->text=$text;
}
//Разбивает на массив текст заданной величина скрипт вырезает полностью предложение
public function dividedText($text="", $partSize=4900,$offset=0)
{	
	if($text=="")$text=$this->text;
	$this->dividedTextArray=array();
	if(strlen($text)>$partSize)
	{
		for($i=0;($i<$offset || $offset==0) && $text;$i++)
		{
		$partText=substr($text,0,$partSize);
		$reg="/^(.*\.)[^\.]*$/i";
		preg_match($reg,$partText,$matches);
		if(strlen($matches[1])==0) break;
		$this->dividedTextArray[]=$matches[1];
		$text=trim(str_replace($matches[1],"",$text));
		}		
	}
	else
	{
		$this->dividedTextArray[]=$text;
	}
	return $this->dividedTextArray;
}
//Стирание спец. символов, двойных и более пробелов, табуляций и переводов строки [НЕ РЕАЛИЗОВАННО]
public function clearNote($text="",$repTextArray=array("/\s+/"))
{
		if($text==="")$text=$this->text;
		if(!is_array($repTextArray)) $repTextArray=(array)$repTextArray;
		foreach ($repTextArray as $key => $value)
		{
			$text=preg_replace($value, " ", $text);
		}
		return $this->text=$text;
}
//Заменяет HTML код  на хещ, чтоб при пропуске через спец программы не потерять теги(синонимайзей, переводчик)
public function encryptTag($text="",$reg="/(<[^<>]*>)/iU")
{
		if(!$text)$text=$this->text;
		preg_match_all($reg, $text, $matches);
		for($i=0;$i<count($matches[0]);$i++)
		{
			$str=$matches[0][$i];
			$this->criptTagArray['hash'][$i]=md5($str);
			$this->criptTagArray['tag'][$i]=$str;
			$text=str_replace($this->criptTagArray['tag'][$i], $this->criptTagArray['hash'][$i], $text);
		}
		return $this->text=$text;
}
//Заменяет хеш на HTML код после обработки через функцию encryptTag 
public function decryptTag($text="",$criptTagArray=array())
{
	if(count($criptTagArray))$criptTagArray=$this->criptTagArray;
	if(!$text)$text=$this->text;
	for($i=0;$i<count($criptTagArray['hash']);$i++)
	{
		$text=str_replace($criptTagArray['hash'][$i], $criptTagArray['tag'][$i], $text);
	}
	return $this->text=$text;
}

public function getDomainName($url)
{
	$reg="/(?:http:\/\/|\s*)[-\w\.]*(?<domain>(?:[-\w]+)\.+(?:[-\w]+))(?:\/|$|\?)/iU";
	if(preg_match($reg, $url,$match)) return $match['domain'];
	else return 0;
}

public function getCriptTagArray()
{
	return $this->criptTagArray;
}

public function getText()
{
	return $this->text;
}

public function setText($text)
{
	$this->text=$text;
}
//Получить контент в нутри тега
public static function betweenTag($text="",$startTag='<div class="xxx">',$withoutTag=1)
{
	$count=0;
	//Проверяем тег это или нет
	if(!preg_match('#<(?<tag>\w+)[^>]*>#im', $startTag,$tag)) return "";
	// Есть ли параметры
	if(preg_match('#<(?<tag>\w+)\s*[\w-]+=[\"\']+[^\'\"]+[\"\']+[^>]*>#im', $startTag))
	{
		// Выдергиваем все параметры
		$countParametr=preg_match_all("#(?<parametr>[\w-]+=[\"\']+[^\'\"]+[\"\']+)#im", $startTag, $matches);
		// Составляем регулярное выражение
		$reg="#<".$tag["tag"]."\s*";
		//var_dump($matches["parametr"]);
		do{
			$reg.="[^>]*".current($matches["parametr"])."[^>]*";
		}while(next($matches["parametr"]));
		$reg.=">#im";
		if(!preg_match($reg,$text,$match)) return "";
		$startTag=$match[0];
	}
	else
	{
		preg_match('/<(?<tag>[^\s]+)[^>]*>/i', $startTag, $tag);
		preg_match('/<(?<tag>'.preg_quote($tag[1]).')[^>]*>/i', $text, $tag);
	}
	unset($match);
	$tagName=$tag["tag"];
	unset($tag);
	$openTag="<".$tagName;
	$closeTag="</".$tagName;
	$text=substr($text,strpos($text,$startTag));
	$posEnd=0;
	for($i=0;$i<1000;$i++)
	{
		$posOpenTag=strpos($text,$openTag,$posEnd);
		$posCloseTag=strpos($text,$closeTag,$posEnd);
		if($posOpenTag===false)
		{
			$posOpenTag=$posCloseTag+1;
			//$posEnd+=$posCloseTag+1-$posEnd;
			//break;
		}
		if($posOpenTag<$posCloseTag)
		{
			$count++;
			$posEnd+=$posOpenTag+1-$posEnd;
		}
		else
		{
			$count--;
			$posEnd+=$posCloseTag+1-$posEnd;
		}
		if(!$count)
		{
			break;
		}
	}
	if($withoutTag)
	{
		$returnText=substr($text,strlen($startTag),$posEnd-strlen($startTag)-1);
	}
	else
	{
		$returnText=substr($text,0,$posEnd+strlen($tagName)+2);
	}
	return $returnText;
}

//Разбираем URL на массив
public static function parse_url($url)
{
    /*
     * scheme Протокол
     * host имя хоста
     * port порт
     * user имя пользователя
     * pass пароль пользователя
     * path полный адрес с именем файла
     * query массив GET запроса [Имя переменной]=Значение
     * fragment массив ссылок на HTML якоря [Имя якоря]=Значение
     */
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