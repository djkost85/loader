<?php
include_once "get_content.php";
include_once "string_work.php";
/**
Класс для получения актуального списка прокси
proxy() Конструктор инициализирующий параметры по умолчанию
updateProxy() Обновить список прокси если это требуется
getRandomProxy() Пулучить случайный прокси из $proxyList
getProxyList() Получить список прокси в классе
checkProxy($proxy,$key) проверка прокси по средствам сайта ya.ru $proxy адрес прокси сервера, $key ключ в массиве для получения этого адреса
saveProxyList() вид хранения и сохранения прокси адресов в файл или в БД.
checkAllFreeProxy() полная проверка всех адресов, для последующего сохранения рабочих адресов
getProxyListInFile() получить список прокси из файла
checkAllProxy()//Проверяет весь список прокси по определенным критериям
setMethodGetProxy($new_methodGetProxy="random") метод получения прокси из списка random случайное значение, rent аренда адресов, с привязкой к определенному потоку
getAnonimChecker() // опрос страниц отвечающих за проверку прокси и выбор рабочей
getMyIP() получить IP парсера, используется для проверки анонимности прокси
freeProxyList() освобождает файл/таблицу от блокировки для использования другими потоками
blocProxyList() Блокирует файл/таблицу чтоб обезопасить от вне очередного доступа в многопоточном режиму
getProxy($rentCode="",$siteForUse="") метод доступа, для получения прокси, атрибуты служат для использования в режиме rent, $rentCode код орендатора прокси, $siteForUse сайт на котором будет использоваться прокси
getRentedProxy($rentCode,$siteForUse,$keyAddress=false) метод доступа для получения арендуемого прокси или для получения нового $keyAddress адрес для быстрого получения значения в файле конфигураций
searchRentalAddress($rentCode,$siteForUse,$keyAddress=false) поиск существующего значения
countProxyInSource() Подсчет количества прокси полученых с каждого сайта
removeAllRentFromCode($rentCode) // удаление всех связей между арендатором и прокси
removeRent($keyContent,$keyRenters,$withoutSaving=0) Удаление одной связи по адресу в массиве с данными
setRentedProxy($rentCode,$siteForUse) Сдать в аренду прокси арендатору
removeProxyInList($proxy) удаление прокси из списка
getLastUseProxy() возвращает последний используемый прокси
downloadProxy() // загрузка прокси с сайтов поставщиков
addProxy($proxy)//добавляет в список прокси, если его там нет
**/
//      Не реализовал поддержку храниения в БД и допилить функции
//		Необходимо доделать фильтр для необходимого типа прокси needAnonimProxy и проверку на передачу COOKIE
class proxy
{
	protected $proxyList;//Массив состоящий из перечня прокси адресов и информации о них
	/*
	структура :
	proxyList['content'][индекс]["proxy"] адрес прокси сервера
	proxyList['content'][индекс]["sourceProxy"]  источник прокси
	proxyList['content'][индекс]["typeProxy"]  протокол прокси HTTP SOCKS5
	proxyList['content'][индекс]["renters"]  нформация об арендаторе адреса прокси
	proxyList['content'][индекс]["renters"][индекс]["startRent"] время начала аренды прокси адреса
	proxyList['content'][индекс]["renters"][индекс]["renterCode"] код аренды
	proxyList['content'][индекс]["renters"][индекс]["userSite"] сайт на котором используется прокси один прокси могут использовать несколько потоков, главное чтоб ресурсы были разные
	proxyList["time"] время последнего обновления
	proxyList["count"] количество подходящих прокси
	proxyList["url"] URL сайта на котором проверяется прокси
	proxyList[""checkWord""][индекс] Проверочное слово которое должно быть в ответе с сервера- это регулярное выражение 
	proxyList["needFunction"][индекс] Необходимые функции которые должен поддерживать прокси 
	proxyList["nameList"] Имя лисат этому имени будет соответствовать имя файла
	*/
	protected $urlProxyList;// адреса для скачивания прокси серверов
	protected $storageTime;//Время актуальности прокси (в секундах)
	protected $rentTime;//Время аренды прокси (в секундах)
	protected $getContent; //(класс для тестирования и получения прокси)
	protected $modeSaveData;//тип сохранения прокси адресов [file] в файл [db] в бд
	protected $fileProxyList;//Имя файла в котором храняться адреса прокси
	protected $nameList;// имя списак прокси
	protected $fHeandleProxyList;// Указатель на файл с списком прокси
	protected $tableProxyListDB;//Таблица в БД в которой храниться адреса прокси.
	//protected $typeProxy;//Тип прокси free бесплатный pay платный
	protected $needCheckProxy;//Проверять прокси перед использованием?
	protected $needAnonimProxy;//Проверяет анонимность прокси
	protected $needProxyCookie;// Проверяет поддержку прокси Cookie 
	protected $myIP;//IP сервера на котором работает скрипт
	protected $checkURLProxy;//URL для проверки http заголовков отправляемых прокси
	protected $addressKeyRentCode;// массив для хранения адресов для ячеек с информацией об аренде, для осуществления быстрого доступа к данным(не реализовал, не придумал как эфективней сделать)
	protected $methodGetProxy;//Метод получения адресов прокси  "random" получение случайных прокси, безконтрольное распределение адресов "rent" аренда  прокси(через один и то-же прокси не могут два потока опрашивать один сайт)
	protected $lastUseProxy;// Последний использованый прокси
	protected $removeProxy;// можно удалять прокси из списка?
	protected $accessToProxyList;// показатель состояния доступа к файлу или таблице с прокси из этого потока 1 поток занимает 0 не занимает
 	
function proxy($update=1)
{
	$this->storageTime          =86400;
	$this->rentTime             =3600;
	$this->urlProxyList 		=array(
										/*"cool-proxy.ru"=>"http://cool-proxy.ru/category/proxy-list/anonymous-proxy",*/
										"cool-proxy.net"=>"http://cool-proxy.net/proxies/http_proxy_list/page:",
										//"proxyhub.ru"=>"http://proxyhub.ru/proxies/csv/?type%5B%5D=HTTP&type%5B%5D=SOCKS5&anon%5B%5D=HIA&anon%5B%5D=ANM&anon%5B%5D=NOA&ports=&sort_by=trust&sort_order=desc&per_page=50&code=eb8f7079777901d19150bf817aa7b0ad",
										"seprox.ru"=>"http://seprox.ru/ru/proxy_filter/0_0_0_0_0_0_0_0_0_"
										);
	$this->getContent           = new get_content();
	$this->getContent->setTypeContent('html');
	$this->setMethodGetProxy("random");
	$this->dirProxyFile         ="proxy_files";
	$this->tableProxyListDB     ="proxyList";
	$this->checkURLProxy        ="http://pchecker.vrozetke.com/proxy_checker/anonimCheck.php";
	$this->checkURLProxyArray[] ="http://pchecker.vrozetke.com/proxy_checker/anonimCheck.php";
	$this->checkURLProxyArray[] ="http://62.109.10.91/proxy_checker/anonimCheck.php";
	$this->checkURLProxyArray[] ="http://free-lance.dyndns.info/proxy_checker/anonimCheck.php";
	$this->checkURLProxyArray[] ="http://kingnothing.koding.com/proxy_checker/anonimCheck.php";
	$this->proxyList            =array();
	$this->needCheckProxy       =1;
	$this->lastUseProxy         ="NO USE";
	$this->modeSaveData         ='file';
	$this->nameList             ='all';
	$this->selectProxyList($this->nameList);
	$this->setModeSaveData("file");
	$this->setRemoveProxy(1);
	if($update)$this->updateProxyList($this->nameList);
	//else $this->proxyList=$this->getProxyListInFileWithoutLock();
}

function __destruct()
{
	$this->closeProxyList();
    unset($this->getContent);
}

public function updateProxy($force=0)
{
	$proxy=$this->getProxyList();
	$this->freeProxyList();
	$endTermProxy=time()-$this->storageTime;
	if($proxy && isset($proxy['content']) && count($proxy['content']) && $proxy['time']>$endTermProxy && !$force)
	{
		$this->proxyList=$proxy;
		return $this->proxyList;
	}
	$this->checkAllProxy();
	return $this->proxyList;
}

public function setRemoveProxy($var=1)
{
	$this->removeProxy=$var;
}

public function setAccessToProxyList($var)
{
	$this->accessToProxyList=$var;
}

public function getAccessToProxyList()
{
	return $this->accessToProxyList;
}

public function getRemoveProxy()
{
	return $this->removeProxy;
}

public function getProxyStorage()
{
	return dirname(__FILE__)."/".$this->dirProxyFile."/";
}

public function getMyIP()
{
	if(isset($this->myIP)) return $this->myIP;
	$this->getContent->setUseProxy(0);
	$this->getContent->setTypeContent('html');
	$this->getContent->setModeGetContent('single');
	for($i=0;$i<10;$i++)
	{
		$this->getContent->getContent("http://2ip.ru/");
		$answer=$this->getContent->getAnswer();
		$reg="/<span>\s*Ваш IP адрес:\s*<\/span>\s*<big[^>]*>\s*(?<ip>[^<]*)\s*<\/big>/iUm";
		if(preg_match($reg, $answer,$match)) break;
	}
	if(!$match['ip']) return 0;
	return $this->myIP=$match['ip'];
}

public function setNeedProxyCookie($var=1)
{
	$this->needProxyCookie=$var;
}

public function getAnonimChecker($checkURLProxyArray="")
{
	if($checkURLProxyArray==="") $checkURLProxyArray=$this->checkURLProxyArray;
	$multiGetContent=new get_content();
	$multiGetContent->setUseProxy(0);
	$multiGetContent->setTypeContent('text');
	$multiGetContent->setModeGetContent('multi');
	$multiGetContent->setCountMultiCURL(1);
	$multiGetContent->setMinSizeAnswer(0);
	$multiGetContent->getContent($checkURLProxyArray);
	$answer=$multiGetContent->getAnswer();
	foreach($answer as $key => $value)
	{
		foreach($value as $subKey => $subValue)
		{
			if(preg_match("/yandex/i",$subValue))
			{
				$this->checkURLProxy=$checkURLProxyArray[$key];
				return $this->checkURLProxy;
			}
		}
	}
	exit(__FILE__." no checker");
}

public function downloadProxy()
{
	$proxy['content']=array();
	foreach ($this->urlProxyList as $keyProxyList => $valueProxyList) 
	{
		$tmpProxy=$this->downProxySite($keyProxyList,$valueProxyList);
		if(is_array($tmpProxy) && count($tmpProxy))
		{
			$proxy['content']=array_merge($proxy['content'],$tmpProxy['content']);
		}
	}
	return $proxy;
}

public function downProxySite($keyProxyList,$valueProxyList)
{
	$WP= new cmsWordPress();
	$WP->setleaveHTML('<div> <p> <br>','<br>');
	$proxy=array();
	switch($keyProxyList)
	{
		case "cool-proxy.net":
		//break;
			$WP->getContent->setTypeContent("html");
			if(!$page=$WP->getContent->getContent($valueProxyList."1"."/sort:working_average/direction:asc"))
			{
				$WP->getContent->setInCache(1);
				$page=$WP->getContent->getContent($valueProxyList."1"."/sort:working_average/direction:asc");
				$WP->getContent->setInCache(0);
			}
			$reg="#/proxies/http_proxy_list/sort:working_average/direction:asc/page:(?<countpage>\d*)\"#iUm";
			if(preg_match_all($reg, $page, $matches))
			{
				$pageArray=$matches['countpage'];
				rsort($pageArray);
				$countpage=$pageArray[0];
			}
			else
			{
				$countpage=10;
			}
			for($i=1;$i<$countpage;$i++)
			{
				if(!$page=$WP->getContent->getContent($valueProxyList.$i."/sort:working_average/direction:asc"))
				{
					$WP->getContent->setInCache(1);
					$page=$WP->getContent->getContent($valueProxyList.$i."/sort:working_average/direction:asc");
					$WP->getContent->setInCache(0);
				}
				sleep(1);
				$reg="#<td\s*style=\"text.align.left.\s*font.weight.bold.\">(.*)</td>\s*<td>(\d+)</td>#iUm";
				if(preg_match_all($reg, $page, $matches))
				{
					for($j=0;$j<count($matches[1]);$j++)
					{
						$reg="/<span class=\"\d+\">(\d+)<\/span>/iU";
						if(preg_match_all($reg, $matches[1][$j], $matchesProxy))
						{
							$reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
							$isIP=$matchesProxy[1][0].".".$matchesProxy[1][1].".".$matchesProxy[1][2].".".$matchesProxy[1][3].":".$matches[2][$j];
							if(preg_match($reg,$isIP))
							{
								$tmpArray['proxy']=trim($isIP);
								$tmpArray["sourceProxy"]=$keyProxyList;
								$tmpArray["typeProxy"]=$this->getNameTypeProxy('HTTP');
								$proxy['content'][]=$tmpArray;
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
			$WP->getContent->setTypeContent("text");
			$page=$WP->getContent->getContent($valueProxyList);
			$reg="/\s*(?<proxy>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}),(?<port>\d{1,5}),(?<typeProxy>HTTP|SOCKS5),\s*/iUm";
			//$iTMP=0;
			if(preg_match_all($reg, $page, $matchesProxy))
			{
				foreach ($matchesProxy['proxy'] as $key => $value)
				{
					//$reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
					//$isIP=$value;
					//if(preg_match($reg,$isIP))
					//{
					//$iTMP++;
					//if($iTMP>10) break;
						$tmpArray['proxy']=$matchesProxy['proxy'][$key].":".$matchesProxy['port'][$key];
						$tmpArray["sourceProxy"]=$keyProxyList;
						$tmpArray["typeProxy"]=$this->getNameTypeProxy($matchesProxy['typeProxy'][$key]);
						$proxy['content'][]=$tmpArray;
					//}
				}		
			}
			break;
		case "seprox.ru":
		//break;
			$i=0;
			$WP->getContent->setTypeContent("html");
			if(!$page=$WP->getContent->getContent($valueProxyList.$i.".html"))
			{
				$WP->getContent->setInCache(1);
				$page=$WP->getContent->getContent($valueProxyList.$i.".html");
				$WP->getContent->setInCache(0);
			}
			if(!$page)
			{
				break;
			}
			if(preg_match("/<div class=\"countResult\">\s* Всего найдено.\s*(\d+)\s*<\/div>/iU", $page, $match)) $countPage=ceil($match[1]/15);
			else $countPage=30;
			do {
				if(!preg_match_all("/<tr class=\"proxyStr\">\s*<td>\s*<script type=\"text\/javascript\">\s*(?<js>[^<]*)\s*<\/script>\s*<\/td>\s*<td>\s*(?<typeProxy>.*)\s*<\/td>/iUm", $page, $matchesSecretCode))
				{
					break;
				}
				foreach ($matchesSecretCode['js'] as $keySecretCode => $valueSecretCode)
				{
					if(!preg_match("/Proxy=String.fromCharCode\(([^\)]*)\)/iU", $valueSecretCode,$matchSecretArray)) 
					{
						break;
					}
					$strSecretCode=$valueSecretCode;
					$l=explode(",",$matchSecretArray[1]);
					foreach ($l as $key => $value)
					{
						$litera[$key]=chr($value);
					}
					foreach ($litera as $keyLitera => $valueLitera)
					{
						$strSecretCode=preg_replace("/Proxy\[".$keyLitera."\]/iU",$valueLitera, $strSecretCode);
					}
					$strSecretCode=str_replace("([]+[]+{})[!+[]+!+[]]", "a", $strSecretCode);
					$strSecretCode=str_replace("(![]+[])[+!+[]]", "b", $strSecretCode);
					$strSecretCode=str_replace("([![]]+{})[+!+[]+[+[]]]", "c", $strSecretCode);
					$strSecretCode=str_replace("([]+[]+[][[]])[!+[]+!+[]]", "d", $strSecretCode);
					$strSecretCode=str_replace("(!![]+[])[!+[]+!+[]+!+[]]", "e", $strSecretCode);
					$strSecretCode=str_replace("(![]+[])[+[]]", "f", $strSecretCode);
					$strSecretCode=str_replace("([![]]+[][[]])[+!+[]+[+[]]]", "i", $strSecretCode);
					$strSecretCode=str_replace("([]+[]+[][[]])[+!+[]]", "n", $strSecretCode);
					$strSecretCode=str_replace("([]+[]+{})[+!+[]]", "o", $strSecretCode);
					$strSecretCode=str_replace("(!![]+[])[+!+[]]", "r", $strSecretCode);
					$strSecretCode=str_replace("(!![]+[])[+[]]", "t", $strSecretCode);
					$strSecretCode=str_replace("(!![]+[])[!+[]+!+[]]", "u", $strSecretCode);
					$strSecretCode=str_replace("(+{}+[]+[]+[]+[]+{})[+!+[]+[+[]]]", " ", $strSecretCode);
					$strSecretCode=str_replace("+++", "***", $strSecretCode);
					$strSecretCode=str_replace("+", "", $strSecretCode);
					$strSecretCode=str_replace("***", "+", $strSecretCode);
					$reg="/(?:\(|\+)(\w+)/";
					preg_match_all($reg, $strSecretCode,$matchesSecretVar);
					$ip="";
					foreach ($matchesSecretVar[1] as $keyIP => $valueIP)
					{
						$reg="/$valueIP='([^']*)'/";
						if(preg_match($reg, $strSecretCode, $matchIP)) $ip.=$matchIP[1];
					}
					//setLog(__FILE__,__LINE__,"IP");
					//setLog(__FILE__,__LINE__,$ip);
					$reg="/\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:+\d+)\s*/i";
					$isIP=$ip;
					if(preg_match($reg,$isIP))
					{
						$tmpArray['proxy']=trim($isIP);
						$tmpArray["sourceProxy"]=$keyProxyList;
						$tmpArray["typeProxy"]=$this->getNameTypeProxy($matchesSecretCode['typeProxy'][$keySecretCode]);
						$proxy['content'][]=$tmpArray;
					}//setLog(__FILE__,__LINE__,"Не канает этот IP ".$isIP);
				}
				$i++;
				sleep(1);
				if(!$page=$WP->getContent->getContent($valueProxyList.$i.".html"))
				{
					$WP->getContent->setInCache(1);
					$page=$WP->getContent->getContent($valueProxyList.$i.".html");
					$WP->getContent->setInCache(0);
				}
				if(!$page)
				{
					if($i<$countPage) continue;
					else break;
				}
			} while($i<$countPage);
			break;
	}		
	unset($WP);
	return $proxy;
}

private function getNameTypeProxy($value="http")
{
	if(preg_match("#https#i", $value))
	{
		return "https";
	}
	elseif(preg_match("#http#i", $value))
	{
		return "http";
	}
	elseif(preg_match("#socks#i", $value))
	{
		return "socks";
	}
	else
	{
		return "http";
	}
}

public function setMethodGetProxy($new_methodGetProxy="random")
{
	switch ($new_methodGetProxy)
	{
		case 'random':
			$this->methodGetProxy='random';
			break;
		case 'rent':
			$this->methodGetProxy='rent';
			break;
		
		default:
			# code...
			break;
	}
}

public function setNeedReturnAnswer($new_val=1)
{
	$this->needReturnAnswer=$new_val;
}

public function setNeedAnonimProxy($new_val=1)
{
	$this->needAnonimProxy=$new_val;
}

public function setNeedCheckProxy($new_needCheckProxy=1)
{
	$this->needCheckProxy=$new_needCheckProxy;
}

public function getUrlProxyList()
{
	return $this->urlProxyList;
}

public function setModeSaveData($variable)
{
	switch ($variable)
	{
		case 'file':
			$this->modeSaveData="file";
			break;
		case 'db':
			$this->modeSaveData="db";
			break;
		
		default:
			return 0;
			break;
	}
}

public function openProxyList()
{
	$this->closeProxyList();
	$this->fHeandleProxyList=fopen($this->fileProxyList,"c+");
}

public function closeProxyList()
{
	$this->freeProxyList();
	@fclose($this->fHeandleProxyList);
	$this->fHeandleProxyList=NULL;
	unset($this->proxyList);
}

public function freeProxyList()
{
	if(!$this->getAccessToProxyList()) return 1; // проверяет занят ли этим потоком файл?
	flock($this->fHeandleProxyList,LOCK_UN);
	$this->setAccessToProxyList(0);
}

public function blocProxyList()
{
	if($this->getAccessToProxyList()) return 1; // проверяет не блокирован ли этим потоком файл?
	do{
		if(flock($this->fHeandleProxyList,LOCK_EX))
		{
			$this->setAccessToProxyList(1);
			break;
		}
		setLog(__FILE__,__LINE__,"файл занят");
		sleep(1);
		}while(true);
}

public	function getRandomProxy()
{
	$proxyList=$this->getProxyListInFileWithoutLock();
	$this->freeProxyList();
	$countProxy=count($proxyList['content']);
	for($i=0;$i<$countProxy;$i++)
	{
		$proxy=array();
		for($j=0;$j<10;$j++)
		{
			$proxy[$j]=trim($proxyList['content'][array_rand($proxyList['content'])]["proxy"]);
		}
		if($good_proxy=$this->checkProxy($proxy))
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
// Функция для обхода блокировки файла с прокси, использовать только для чтения
public function getProxyListInFileWithoutLock()
{
	return json_decode(file_get_contents($this->fileProxyList),true);
}

public function getProxyListInFile()
{
	$this->blocProxyList();
	$jsonProxy=file_get_contents($this->fileProxyList);
	$this->proxyList=json_decode($jsonProxy,true);
	return $this->proxyList;
}

public function getProxy($rentCode="",$siteForUse="")
{
	switch ($this->methodGetProxy)
	{
		case 'random':
			$this->lastUseProxy=$this->getRandomProxy();
			return $this->lastUseProxy;
			break;
		case 'rent':
			if($rentCode=="" || $siteForUse=="") return 0;
			$this->lastUseProxy=$this->getRentedProxy($rentCode,$siteForUse);
			return $this->lastUseProxy;
			break;
		
		default:
			return 0;
			break;
	}
}

public function addProxy($proxy,$rentCode="",$siteForUse="")
{
	if(!$result=$this->searchProxyInList($proxy))
	{
		$tmpArray['proxy']=trim($proxy);
		$tmpArray["sourceProxy"]='none';
		$tmpArray["typeProxy"]=$this->getNameTypeProxy('http');
		$this->proxyList['content'][]=$tmpArray;
		$this->saveProxyList($this->proxyList);
	}
	if($rentCode)
	{
		$this->setRentedProxy($rentCode,$siteForUse,$proxy);
		$this->saveProxyList($this->proxyList);
	}
}

public function getRentedProxy($rentCode,$siteForUse,$keyAddress=false)
{
	//Максимальное время ожидания сутки
	for($i=0;$i<144;$i++)
	{
		$this->proxyList=$this->getProxyListInFile();
		if($ipProxy=$this->searchRentalAddress($rentCode,$siteForUse,$keyAddress)) return $ipProxy["proxy"];
		if($ipProxy=$this->setRentedProxy($rentCode,$siteForUse))
		{
			//сюда нужно поместить сохранение адреса быстрого доступа к данным о аренде прокси
			$this->saveProxyList($this->proxyList);
			return $ipProxy["proxy"];
		}
		// все прокси заняты, записываем изменения и освобождаем файл. ждем когда освободится
		$this->saveProxyList($this->proxyList);
		setLog(__FILE__,__LINE__," All Proxy busy");
		sleep(300);
	}
	return 0;
}

public function searchRentalAddress($rentCode,$siteForUse,$keyAddress=false)
{
	$this->proxyList=$this->getProxyListInFile();
	// если задан адрес в где лежит информация об аренде, проверяем информацию
	if($keyAddress)
	{
		if($this->proxyList['content'][$keyAddress['keyContent']]["renters"][$keyAddress['keyRenters']]["renterCode"]==$rentCode && $this->proxyList['content'][$keyAddress['keyContent']]["renters"][$keyAddress['keyRenters']]["userSite"]==$siteForUse)
		{
			$endTermRent=time()-$this->rentTime;
			// проверяем время аренды прокси
			if($valueRenters["startRent"]>$endTermRent) return $this->proxyList['content'][$keyAddress['keyContent']]["proxy"];
            else
                {
                    $this->removeRent($keyAddress['keyContent'],$keyAddress['keyRenters']);
                    return 0;
                }
            }
        }
        $endTermRent=time()-$this->rentTime;
        // Если нет , то ищем в ручную
        foreach ($this->proxyList['content'] as $keyContent => $valueContent)
        {
            //$this->proxyList['content'][$keyContent]["renters"] === $valueContent["renters"]
            foreach($valueContent["renters"] as $keyRenters => $valueRenters)
            {
                //$this->proxyList['content'][$keyContent]["renters"][$keyRenters]["renterCode"] === $valueRentCode["renterCode"]
                //$this->proxyList['content'][$keyContent]["renters"][$keyRenters]["userSite"] === $valueRentCode["userSite"]
                if($valueRenters["renterCode"]==$rentCode)// && $valueRenters["userSite"]==$siteForUse)
                {
                    // проверяем время аренды прокси
				if($valueRenters["startRent"]>$endTermRent)
				{
					$returnArray["proxy"]=$valueContent["proxy"];
					$returnArray["keyContent"]=$keyContent;
					$returnArray["keyRenters"]=$keyRenters;
					return $returnArray;
				}
				else
				{
					$this->removeRent($keyContent,$keyRenters);
					return 0;
				}
			}
		}
		unset($valueRenters);
	}
	unset($valueContent);
	return 0;
}

public function searchProxyInList($proxy)
{
	$this->proxyList=$this->getProxyListInFile();
	foreach ($this->proxyList['content'] as $keyContent => $valueContent)
	{
		if($valueContent["proxy"]==$proxy)
		{
			$returnArray['proxy']=$valueContent["proxy"];
			$returnArray['keyContent']=$keyContent;
			return $returnArray;
		}
	}
	unset($valueContent);
	return 0;
}

protected function setRentedProxy($rentCode,$siteForUse,$proxy="")
{
	$this->proxyList=$this->getProxyListInFile();
	if($proxy)
	{
		$result=$this->searchProxyInList($proxy);
		$tmpData['startRent']=(string)time();
		$tmpData['renterCode']=(string)$rentCode;
		$tmpData['userSite']=(string)$siteForUse;
		$this->proxyList['content'][$result['keyContent']]["renters"][]=$tmpData;
	}
	else
	{
		shuffle($this->proxyList['content']);
		foreach ($this->proxyList['content'] as $keyContent => $valueContent)
		{
			$proxyUseThisSite=0;
			foreach($valueContent["renters"] as $keyRenters => $valueRenters)
			{
				if($valueRenters["userSite"]==$siteForUse) 
				{
					$proxyUseThisSite=1;
					break;
				}
			}
			unset($valueRenters);
			//Если через этот прокси не опрашивается сайт $siteForUse, то привяжем поток к этому прокси
			if(!$proxyUseThisSite)
			{
				$tmpData['startRent']=(string)time();
				$tmpData['renterCode']=(string)$rentCode;
				$tmpData['userSite']=(string)$siteForUse;
				$this->proxyList['content'][$keyContent]["renters"][]=$tmpData;
				end($this->proxyList['content'][$keyContent]["renters"]);
				$returnArray["proxy"]=$valueContent["proxy"];
				$returnArray["keyContent"]=$keyContent;
				$returnArray["keyRenters"]=key($this->proxyList['content'][$keyContent]["renters"]);
				return $returnArray;
			}
		}
		unset($valueContent);
		return 0;
	}
}

public function removeAllRentFromCode($rentCode)
{
	$this->proxyList=$this->getProxyListInFile();
	if(!isset($this->proxyList['content'])) return 0;
	foreach ($this->proxyList['content'] as $keyContent => $valueContent)
	{
		//$this->proxyList['content'][$keyContent]["renters"] === $valueContent["renters"]
		foreach($valueContent["renters"] as $keyRenters => $valueRenters)
		{
			//$this->proxyList['content'][$keyContent]["renters"][$keyRenters]["renterCode"] === $valueRentCode["renterCode"]
			//$this->proxyList['content'][$keyContent]["renters"][$keyRenters]["userSite"] === $valueRentCode["userSite"]
			if($valueRenters["renterCode"]==$rentCode)
			{
				$this->removeRent($keyContent,$keyRenters,1);
			}
		}
		unset($valueRenters);
	}
	unset($valueContent);
	$this->saveProxyList($this->proxyList);
}

public function removeAllRent()
{
	$this->proxyList=$this->getProxyListInFile();
	foreach ($this->proxyList['content'] as $keyContent => $valueContent)
	{
		//$this->proxyList['content'][$keyContent]["renters"] === $valueContent["renters"]
		foreach($valueContent["renters"] as $keyRenters => $valueRenters)
		{
			//$this->proxyList['content'][$keyContent]["renters"][$keyRenters]["renterCode"] === $valueRentCode["renterCode"]
			//$this->proxyList['content'][$keyContent]["renters"][$keyRenters]["userSite"] === $valueRentCode["userSite"]
			if($valueRenters["renterCode"])
			{
				$this->removeRent($keyContent,$keyRenters,1);
			}
		}
		unset($valueRenters);
	}
	unset($valueContent);
	$this->saveProxyList($this->proxyList);
}

public function removeRent($keyContent,$keyRenters,$withoutSaving=0)
{
	if(!count($this->proxyList)) $this->proxyList=$this->getProxyListInFile();
	if(isset($this->proxyList['content'][$keyContent]["renters"][$keyRenters]))
	{
		unset($this->proxyList['content'][$keyContent]["renters"][$keyRenters]);
	}
	if(!$withoutSaving) $this->saveProxyList($this->proxyList);
}

public function removeRentToCodeSite($rentCode,$siteForUse)
{
	if($resultArray=$this->searchRentalAddress($rentCode,$siteForUse))
	{
		$this->removeRent($resultArray['keyContent'],$resultArray['keyRenters']);
	}
}

public function removeProxyInList($proxy)
{
	if($this->removeProxy)
	{
		$this->proxyList=$this->getProxyListInFile();
		foreach ($this->proxyList['content'] as $keyContent => $valueContent)
		{
			if($this->proxyList['content'][$keyContent]['proxy']==$proxy)
			{
				unset($this->proxyList['content'][$keyContent]);
				break;
			}
		}
		unset($valueContent);
		$this->saveProxyList($this->proxyList);
		return 1;
	}
	else
	{
		return 0;
	}
}

public	function getProxyList()
{
	if(isset($this->proxyList) && count($this->proxyList) && ($this->proxyList['time']>(time()-3600)))	return $this->proxyList;
	switch ($this->modeSaveData)
	{
		case 'file':
			if(!$proxy=$this->getProxyListInFile())
			{
				return 0;
			}
			return $this->proxyList=$proxy;
			break;
		case 'db':
			
			break;
		default:
			# code...
			break;
	}
	return $this->proxyList;
}
public function getLastUseProxy()
{
	return $this->lastUseProxy;
}
public function checkProxy($proxy,$method="url",$data=array('url'=>"http://ya.ru"))//function
{
	if(!$this->needCheckProxy) return $proxy;
	if($method=='function')
	{
		$this->getMyIP();
		if(!$url=$this->getAnonimChecker()) return 0;
	}
	if(is_string($proxy))
	{
		if(preg_match("/\s*\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}(:+\d+)\s*/",$proxy))
		{
			$this->getContent->setModeGetContent('single');
			//$this->getContent->setProxy($proxy);
			$this->getContent->setOptionToDescriptor($this->getContent->getDescriptor(),CURLOPT_PROXY,$proxy);
			$this->getContent->setCheckAnswer(0);
			$get=array();
//			if($this->needAnonimProxy) $get[]="ip=".$this->myIP;
//			if($this->needProxyCookie) $get[]="cookie";
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
			$answerContent=$this->getContent->getContent($url);
			if(preg_match("/yandex/i",$answerContent))
			{
				return $proxy;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}
	if(is_array($proxy))
	{
		$good_proxy=array();
		$this->getContent->setModeGetContent('multi');
		$this->getContent->setCountMultiCURL(count($proxy));
		reset($proxy);
		for($i=0;current($proxy)!==false;$i++)
		{
			$this->getContent->setOptionToDescriptor($this->getContent->getDescriptorArray(),CURLOPT_PROXY,current($proxy),$i);
			//$this->getContent->setProxy(current($proxy),$i);
			next($proxy);
		}
		$this->getContent->setCheckAnswer(0);
		$get=array();
		//if($this->needAnonimProxy) $get[]="ip=".$this->myIP;
		//if($this->needProxyCookie) $get[]="cookie";
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
		$answerContent=$this->getContent->getContent($this->checkURLProxy."?".$query);
		reset($proxy);
		foreach ($answerContent as $key => $value)
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
} 

private function checkProxyArrayToSite($proxy,$url,$checkWord)
{
		$this->getContent->setModeGetContent('multi');
		$this->getContent->setCountMultiCURL(count($proxy));
		reset($proxy);
		for($i=0;current($proxy)!==false;$i++)
		{
			$this->getContent->setOptionToDescriptor($this->getContent->getDescriptorArray(),CURLOPT_PROXY,current($proxy),$i);
			next($proxy);
		}
		$this->getContent->setCheckAnswer(0);
		$answerContent=$this->getContent->getContent($url);
		reset($proxy);
		$good_proxy=array();
		foreach ($answerContent as $key => $value)
		{
			$testCount=0;
			$countGoodCheck=0;
			foreach ($checkWord as $keyCheckWord=>$valueCheckWord)
			{
				$testCount++;
				if(preg_match($valueCheckWord,$value))
				{
					$countGoodCheck++;
				}
			}
			unset($valueCheckWord);
			if($countGoodCheck==$testCount)
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

private function checkProxyArrayToFunction($proxy,$needFunction)
{
		if(!$url=$this->getAnonimChecker()) return 0;
		$this->getContent->setModeGetContent('multi');
		$this->getContent->setCountMultiCURL(count($proxy));
		reset($proxy);
		for($i=0;current($proxy)!==false;$i++)
		{
			$this->getContent->setOptionToDescriptor($this->getContent->getDescriptorArray(),CURLOPT_PROXY,current($proxy),$i);
			//$this->getContent->setProxy(current($proxy),$i);
			next($proxy);
		}
		$this->getContent->setCheckAnswer(0);
		$get=array();
		if(array_search("anonim",$needFunction))
		{
			if($this->getMyIP()) $get[]="ip=".$this->myIP;
		}
		if(array_search("cookie",$needFunction)) $get[]="cookie";
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
		$answerContent=$this->getContent->getContent($url."?".$query);
		reset($proxy);
		$good_proxy=array();
		foreach ($answerContent as $key => $value)
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

public function saveProxyList($proxyList='')
{
	if(!is_array($proxyList)) $proxyList=$this->proxyList;
	/*if(!isset($proxyList['content']))
	{
		$this->freeProxyList();
		return 0;
	}*/
	$proxyList['time']=time();
	$proxyList['count']=count($proxyList['content']);
	$this->proxyList=$proxyList;
	switch ($this->modeSaveData)
	{
		case 'file':
			$jsonProxy=json_encode($proxyList);
			$this->blocProxyList();
			file_put_contents($this->fileProxyList, '');
			rewind($this->fHeandleProxyList);
			fwrite($this->fHeandleProxyList, $jsonProxy);
			$this->freeProxyList();
			break;
		case 'db':
		//пока не реализована
		/*	if(!mysql_ping())
			{
				//echo "No connect DB";
				return 0;
			}
			$query="CREATE TABLE IF NOT EXISTS `".$this->tableProxyListDB."` (
					  `id` INT NOT NULL AUTO_INCREMENT,
					  `addressProxy` TEXT,
					  `timeAdd` INT,
					  `typeProxy` TEXT,
					  PRIMARY KEY  (`id`)
					);";
			if(!mysql_query($query)) return 0;
			$query="INSERT INTO '".$this->tableProxyListDB."' (`addressProxy`, `timeAdd`, `typeProxy`) VALUE ";
			!!if($count=count())!!))
			{
				for($i=0;$i<$count;$i++)
				{
					if($count==($i+1))
					{
						$query.="('".$proxyList['proxy'][$i]."', ".$proxyList['time'].", '".$this->typeProxy."') ";
					}
					else
					{
						$query.="('".$proxyList['proxy'][$i]."', ".$proxyList['time'].", '".$this->typeProxy."'), ";
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
//количество прокси пришедшие с определенных источников
private function countProxyInSource()
{
	foreach ($this->proxyList['content'] as $key => $value)
	{
		$sourceProxy[$key]=$this->proxyList['content'][$key]["sourceProxy"];
	}
	unset($value);
	$result=array();
	foreach ($this->urlProxyList as $key => $value)
	{
		$result[$key]=count(array_keys($sourceProxy,$key)); 
	}
	unset($value);
	return $result;
}

public function createProxyList($nameList,$checkUrl="http://ya.ru",$checkWordArray=array("#yandex#iUm"),$needFunctionArray=array())
{
	$this->closeProxyList();
	$this->nameList=$nameList;
	if(file_exists($this->getProxyStorage().$nameList.".proxy"))
	{
		$this->deleteProxyList($nameList);
	}
	$this->fileProxyList=$this->getProxyStorage().$this->nameList.".proxy";
	$this->openProxyList();
	$proxyList['content']=array();
	$proxyList['url']=$checkUrl;
	if($checkWordArray)$proxyList['checkWord']=$checkWordArray;
	else $proxyList['checkWord']=array("#.*#iUm");
	if($needFunctionArray)$proxyList['needFunction']=$needFunctionArray;
	else $proxyList['needFunction']=array();
	$proxyList['nameList']=$nameList;
	$proxyList['needUpdate']=1;
	$this->createProxyListBuk($proxyList);
	$this->saveProxyList($proxyList);
}

protected function createProxyListBuk($proxyList)
{
	$jsonProxy=json_encode($proxyList);
	$bukFile=$this->fileProxyList.".buk";
	$fh=fopen($bukFile,"c+");
	file_put_contents($bukFile, '');
	rewind($fh);
	fwrite($fh, $jsonProxy);
	fclose($fh);
}

protected function restoreProxyListFromBuk()
{
	$bukFile=$this->fileProxyList.".buk";
	if(file_exists($bukFile))
	{
		$proxyList=json_decode(file_get_contents($bukFile),true);
		$this->saveProxyList($proxyList);
	}
}


public function deleteProxyList($nameList)
{
	if(file_exists($this->getProxyStorage().$nameList.".proxy"))
	{
		unlink($this->getProxyStorage().$nameList.".proxy");
	}
}

public function clearProxyList($nameList)
{
	$this->selectProxyList($nameList);
	$this->proxyList['content']=array();
	$this->saveProxyList($this->proxyList);
}

public function setUpdateProxyList($nameList,$value=1)
{
	$this->selectProxyList($nameList);
	$this->proxyList['needUpdate']=$value;
	$this->saveProxyList($this->proxyList);
}

private function getUniqueProxyIP($proxyArray)
{
	foreach ($proxyArray['content'] as $key => $value)
	//for($key=0;$key<3;$key++)
	{ 
		$ipProxy[$key]=$proxyArray['content'][$key]["proxy"];
		$sourceProxy[$key]=$proxyArray['content'][$key]["sourceProxy"];
		$typeProxy[$key]=$this->getNameTypeProxy($proxyArray['content'][$key]["typeProxy"]);
	}
	unset($value);
	$ipProxy=array_unique($ipProxy);
	$proxyArray['content']=array();
	foreach ($ipProxy as $key => $value)
	{ 
		$proxyArray['content'][$key]["proxy"]=$ipProxy[$key];
		$proxyArray['content'][$key]["sourceProxy"]=$sourceProxy[$key];
		$proxyArray['content'][$key]["typeProxy"]=$this->getNameTypeProxy($typeProxy[$key]);
	}
	unset($value);
	$proxyArray['content']=array_values($proxyArray['content']);
	return $proxyArray;
}

public function updateProxyList($nameList,$force=0)
{
	if($nameList=='all')
	{
		$this->selectProxyList($nameList);
	}
	else
	{
		$allProxy=$this->selectProxyList('all');
		$this->selectProxyList($nameList);
	}
	$this->freeProxyList();
	$endTermProxy=time()-$this->storageTime;
	if(($this->proxyList && isset($this->proxyList['content']) && count($this->proxyList['content']) && $this->proxyList['time']>$endTermProxy && !$force) || $this->proxyList['needUpdate']==0)
	{
		return $this->proxyList;
	}
	if($nameList=='all')
	{
		$proxyList['url']=$this->proxyList['url'];
		$proxyList['checkWord']=$this->proxyList['checkWord'];
		$proxyList['needFunction']=$this->proxyList['needFunction'];
		$proxyList['nameList']=$this->proxyList['nameList'];
		$proxyList['needUpdate']=$this->proxyList['needUpdate'];
		$this->blocProxyList();
		$this->proxyList=$this->downloadProxy();
		$this->proxyList=$this->getUniqueProxyIP($this->proxyList);
		$this->proxyList['url']=$proxyList['url'];
		$this->proxyList['checkWord']=$proxyList['checkWord'];
		$this->proxyList['needFunction']=$proxyList['needFunction'];
		$this->proxyList['nameList']=$proxyList['nameList'];
		$this->proxyList['needUpdate']=$proxyList['needUpdate'];
	}
	else $this->proxyList['content']=$allProxy['content'];
	//$this->saveProxyList($this->proxyList);
	//$this->selectProxyList($nameList);
	$this->checkProxyList($nameList);
	$this->saveProxyList($this->proxyList);
	return $this->proxyList;
}

public function selectProxyList($nameList)
{
	$this->closeProxyList();
	$allList=$this->getAllNameProxyList();
	$this->nameList=$nameList;
	if(array_search($nameList,$allList)!==false)
	{	
		$this->fileProxyList=$this->getProxyStorage().$this->nameList.".proxy";
	}
	else
	{
		$this->fileProxyList=$this->getProxyStorage().$this->nameList.".proxy";
		$this->createProxyList($nameList,"http://ya.ru",array("#yandex#iUm"));
	}
	$this->openProxyList();
	$listData=file_get_contents($this->fileProxyList);
	if(!$listData || !json_decode($listData))
	{
		$this->restoreProxyListFromBuk();
	}
	$this->proxyList=$this->getProxyListInFileWithoutLock();
	$this->freeProxyList();
	return $this->proxyList;
}

public function getAllNameProxyList()
{
	$file_list=glob($this->getProxyStorage()."*.proxy");
	$proxyListArray=array();
	foreach ($file_list as $key => $value)
	{
		if(preg_match("#/(?<nameList>[^/]+)\.proxy$#iUm", $value, $match))
		{
			$proxyListArray[]=$match['nameList'];
		}
	}
	return $proxyListArray;
}

private function checkProxyList($nameList="")
{
	if(!$nameList) $nameList=$this->nameList;
	//$this->selectProxyList($nameList);
	if(count($this->proxyList['checkWord']))$this->checkProxyTo($nameList,'checkWord');
	if(count($this->proxyList['needFunction']))$this->checkProxyTo($nameList,'needFunction');
}

private function checkProxyTo($nameList="",$method="checkWord")
{
	if(!$nameList) $nameList=$this->nameList;
	//$this->selectProxyList($nameList);
	$good_proxy=array();
	$proxy=array();
	reset($this->proxyList['content']);
	$partArraySize=100;
	$chonkArrayProxyList=array_chunk($this->proxyList['content'], $partArraySize,true);
	$tmpProxyList['content']=array();
	foreach ($chonkArrayProxyList as $keyArray => $partArrayProxyList)
	{
		$good_proxy=array();
		foreach ($partArrayProxyList as $key => $proxyVal)
		{
			$good_proxy[$key]=$this->proxyList['content'][$key]["proxy"];
			$good_proxySource[$key]=$this->proxyList['content'][$key]["sourceProxy"];
			$good_proxyType[$key]=$this->getNameTypeProxy($this->proxyList['content'][$key]["typeProxy"]);
		}
		unset($proxyVal);
		switch ($method)
		{
			case 'checkWord':
				$good_proxy=$this->checkProxyArrayToSite($good_proxy,$this->proxyList['url'],$this->proxyList['checkWord']);
				break;
			case 'needFunction':
				$good_proxy=$this->checkProxyArrayToFunction($good_proxy,$this->proxyList['needFunction']);
				break;
			default:
				# code...
				break;
		}
		if(is_array($good_proxy))
		{
			foreach ($good_proxy as $key => $good)
			{
				$tmpProxyList['content'][$key]["proxy"]=$good;
				$tmpProxyList['content'][$key]["sourceProxy"]=$good_proxySource[$key];
				$tmpProxyList['content'][$key]["typeProxy"]=$this->getNameTypeProxy($good_proxyType[$key]);
				$tmpProxyList['content'][$key]["renters"][0]["startRent"]="";
				$tmpProxyList['content'][$key]["renters"][0]["renterCode"]="";
				$tmpProxyList['content'][$key]["renters"][0]["userSite"]="";
			}
		}
	}
	unset($partArrayProxyList);
	$this->proxyList['content']=array_values($tmpProxyList['content']);

	$this->saveProxyList($this->proxyList);
}

public function checkAllProxy()
{
	setLog(__FILE__,__LINE__," check All Proxy ");
	$this->needCheckProxy=1;
	$this->proxyList=$this->downloadProxy();
	$this->proxyList=$this->getUniqueProxyIP($this->proxyList);
	//Подсчитаем количество прокси на каждый источник
	$startCountSource=$this->countProxyInSource();
	$countProxy=count($this->proxyList['content']);
	$good_proxy=array();
	$proxy=array();
	reset($this->proxyList['content']);
	$partArraySize=100;
	$chonkArrayProxyList=array_chunk($this->proxyList['content'], $partArraySize,true);
	$tmpProxyList['content']=array();
	foreach ($chonkArrayProxyList as $keyArray => $partArrayProxyList)
	{
		$good_proxy=array();
		foreach ($partArrayProxyList as $key => $proxyVal)
		{
			$good_proxy[$key]=$this->proxyList['content'][$key]["proxy"];
			$good_proxySource[$key]=$this->proxyList['content'][$key]["sourceProxy"];
			$good_proxyType[$key]=$this->getNameTypeProxy($this->proxyList['content'][$key]["typeProxy"]);
		}
		unset($proxyVal);
		$good_proxy=$this->checkProxy($good_proxy);
		if(is_array($good_proxy))
		{
			foreach ($good_proxy as $key => $good)
			{
				$tmpProxyList['content'][$key]["proxy"]=$good;
				$tmpProxyList['content'][$key]["sourceProxy"]=$good_proxySource[$key];
				$tmpProxyList['content'][$key]["typeProxy"]=$this->getNameTypeProxy($good_proxyType[$key]);
				$tmpProxyList['content'][$key]["renters"][0]["startRent"]="";
				$tmpProxyList['content'][$key]["renters"][0]["renterCode"]="";
				$tmpProxyList['content'][$key]["renters"][0]["userSite"]="";
			}
		}
	}
	unset($partArrayProxyList);
	$this->proxyList['content']=array_values($tmpProxyList['content']);
	$endCountSource=$this->countProxyInSource();
	setLog(__FILE__,__LINE__," start proxy count(".$countProxy.") ");
	foreach ($startCountSource as $key => $value)
	{
		setLog(__FILE__,__LINE__," start in $key = $value ");
	}
	unset($value);
	setLog(__FILE__,__LINE__," good proxy count(".count($this->proxyList['content']).") ");
	foreach ($endCountSource as $key => $value)
	{
		setLog(__FILE__,__LINE__," good in $key = $value ");
	}
	unset($value);
	$this->saveProxyList($this->proxyList);
}

}
?>