<?php
include_once "proxy.php";
include_once "string_work.php";
class get_content
{
	private $defaultSettings; //Настройки по умолчанию, если не задано значение, то берет из этого списка
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
	private $allSetting;// массив с перечислением всех настроек для cURL
	private $useProxy; // использовать прокси или нет 1 да 0 нет
	public $proxy; // прокси для использование в cURL, класс proxy для работы с прокси серверами
	private $answer; // возвращенный ответ из функций curl_exec() и curl_multi_exec() c curl_multi_getcontent() в зависимости от режима работы класса
	private $descriptor; // массив с компонентами ['descriptor'] дескриптор инициируемый при помощи функции curl_init() или curl_multi_init() в зависимости от режима работы класса, ['option'][имя опции] значение в value ['idCode'] идентификационный код для дискриптора по которому будут присваиваться файлы cookie, аренда proxy
	private $descriptorArray; // массив массивов с дескрипторами для работы в режиме multi структура опций и  идентификационных кодов схожа с $descriptor descriptorArray[key]['descriptor']
	private $countMultiCURL; // количество дескрипторов для режима multi
	private $numberRepeat;// Номер повтора для получения коректного ответа
	private $maxNumberRepeat; // максимальное количество повторных запросов на получение контента
	private $minSizeAnswer; //Минимальная длинна ответа от сервера(по байтово)
	private $typeContent;//Тип контента (Файл[file]|Текст[text]|html страницы[html])
	private $inCache;//Если страница не доступна, то забирать контент из кеша гугла
	private $encodingAnswer;// Изменять кодировку текста? 1 да 0 нет
	private $encodingName;//Имя кодировки в которую преобразовывать ответ если включена декадировка
	private $encodingNameAnswer; // базовая кадировка текста полученным от донора
	private $checkAnswer; //Вкл/выкл проверку результата
	private $stringWork;//Класс для работы с строкой string_work для сжатия, проверки данных
	private $modeGetContent;//Тип скачивания контента multi или single
	private $dirCookie;// Папка где храняться файлы cookie

function get_content()
{
	$this->allSetting =array(CURLOPT_HEADER,CURLOPT_URL,CURLOPT_TIMEOUT,CURLOPT_USERAGENT,CURLOPT_RETURNTRANSFER,CURLOPT_FOLLOWLOCATION,CURLOPT_POST,CURLOPT_POSTFIELDS);
	$this->setDirCookie(dirname(__FILE__)."/get_content_files/");
	$this->setDefaultSettings(array());
	$this->setDefaultSetting(CURLOPT_HEADER,0);
	$this->setDefaultSetting(CURLOPT_URL,"http://ya.ru");
	$this->setDefaultSetting(CURLOPT_TIMEOUT,15);
	$this->setDefaultSetting(CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.75 Safari/537.1");
	$this->setDefaultSetting(CURLOPT_RETURNTRANSFER,1);
	$this->setDefaultSetting(CURLOPT_FOLLOWLOCATION,1);
	$this->setDefaultSetting(CURLOPT_POSTFIELDS, "");
	$this->setDefaultSetting(CURLOPT_POST,0);
	$this->setUseProxy(0);
	$this->setNumberRepeat(0);
	$this->setMaxNumberRepeat(10);
	$this->setMinSizeAnswer(5000);
	$this->setTypeContent("text");
	$this->setInCache(0);
	$this->setEncodingAnswer(0);
	$this->setEncodingName("UTF-8");
	$this->setCheckAnswer(1);
	$this->setModeGetContent('single');
	$this->stringWork =new string_work();
	$this->clearCookie();

}
//тест на доступность ресурсов для работы класса
public function functionCheck()
{
	if(!function_exists('curl_init')) echo "Error: CURL is not installed\n";
	if(!is_dir($this->getDirCookie()))
	{
		echo "Warning: folder for the cookie does not exist\n";
		echo "try to create\n";
		if(!mkdir($this->getDirCookie())) echo "Warning: Can not create folder\n";
		else echo "Success: cookie folder is created\n";
	}
	else
	{
		if(!is_readable($this->getDirCookie()) || !is_writable($this->getDirCookie()))
		{
			echo "Warning: folder for the cookie does not have the necessary rights to use\n";
			echo "trying to change\n";
			if(!chmod($this->getDirCookie(),0777)) echo "Warning: I can not change the access rights\n";
			else echo "Success: The rules have changed for the necessary\n";
		}
	}
	if(!class_exists('proxy')) echo "Warning: proxy class is declared, can not work with proxy\n";
	if(!class_exists('string_work')) echo "Warning: string_work class is declared, word processing is not possible\n";
}

public function clearCookie($time=172800)
{
	$file_list = glob($this->getDirCookie()."*.cookie");
	foreach ($file_list as $key => $value)
	{
		preg_match("/\/(?<time>\d+)(?:\.|\s*)\d*\.cookie$/iU", $value,$match);
		if((int)$match['time']<time()-$time)
		{
			unlink($value);
		}
	}
	unset($value);
}

public function setDirCookie($value)
{
	$this->dirCookie=$value;
}
public function getDirCookie()
{
	return $this->dirCookie;
}

public function setDefaultSetting($option,$value)
{
	$this->defaultSettings[$option]=$value;
}
public function getDefaultSetting($option)
{
	return $this->defaultSettings[$option];
}
public function setDefaultSettings($value)
{
	if(is_array($value))
    {
        $this->defaultSettings=$value;
        return 1;
    }
	else return 0;
}
public function getDefaultSettings()
{
	return $this->defaultSettings;
}

public function setUseProxy($value=0)
{
	switch($value)
	{
		case'1':
		if(!isset($this->proxy) || !is_object($this->proxy))
			{
				$this->proxy=new proxy();
			}
			break;
		case'0':
			unset($this->proxy);
			break;
		default:
			unset($this->proxy);
			break;
	}
	$this->useProxy=$value;
}
public function getUseProxy()
{
	return $this->useProxy;
}

public function setNumberRepeat($value=0)
{
	$this->numberRepeat=$value;
}
public function getNumberRepeat()
{
	return $this->numberRepeat;
}

public function setMaxNumberRepeat($value=10)
{
	$this->maxNumberRepeat=$value;
}
public function getMaxNumberRepeat()
{
	return $this->maxNumberRepeat;
}
private function repeatGetContent()
{
	if($this->getNumberRepeat()<$this->getMaxNumberRepeat())
	{
		$this->nextRepeat();
		return 1;
	}
	else 
	{
		$this->endRepeat();
		return 0;
	}
}
private function nextRepeat()
{
	$numRepeat=$this->getNumberRepeat();
	$numRepeat++;
	$this->setNumberRepeat($numRepeat);
}
private function endRepeat()
{
	$this->setNumberRepeat(0);
}

public function setMinSizeAnswer($value=5000)
{
	$this->minSizeAnswer=$value;
}
public function getMinSizeAnswer()
{
	return $this->minSizeAnswer;
}

public function setTypeContent($typeContent="text")
{
	switch($typeContent)
	{
		case 'file':
			$this->typeContent='file';
			$this->setDefaultSetting(CURLOPT_HEADER,0);
			$this->setEncodingAnswer(0);
			break;
		case 'text':
			$this->typeContent='text';
			break;
		case 'html':
			$this->typeContent='html';
			break;
		default:
			break;
	}
}
public function getTypeContent()
{
	return $this->typeContent;
}

public function setInCache($value=0)
{
	$this->inCache=$value;
}
public function getInCache()
{
	return $this->inCache;
}

public function setEncodingAnswer($value=0)
{
	$this->encodingAnswer=$value;
}
public function getEncodingAnswer()
{
	return $this->encodingAnswer;
}

public function setEncodingName($value="UTF-8")
{
	$this->encodingName=$value;
}
public function getEncodingName()
{
	return $this->encodingName;
}

public function setEncodingNameAnswer($value)
{
	$this->encodingNameAnswer=$value;
}
public function getEncodingNameAnswer()
{
	return $this->encodingNameAnswer;
}

public function setCheckAnswer($value=1)
{
	$this->checkAnswer=$value;
}
public function getCheckAnswer()
{
	return $this->checkAnswer;
}

public function setCountMultiCURL($value=1)
{
	$this->closeGetContent();
	$this->countMultiCURL=$value;
	$this->initGetContent();
}
public function getCountMultiCURL()
{
	return $this->countMultiCURL;
}

public function setModeGetContent($new_modeGetContent='single')
{
	$this->closeGetContent();
	switch ($new_modeGetContent)
	{
		case 'single':
			$this->modeGetContent='single';
			$this->initGetContent();
			break;
		case 'multi':
			$this->modeGetContent='multi';
			if($this->getCountMultiCURL()<1)$this->setCountMultiCURL(1);
			break;
		default:
			return 0;
			break;
	}
    return 1;
}
public function getModeGetContent()
{
	return $this->modeGetContent;
}

public function setProxy($proxy,$key=0)
{
	$this->setUseProxy(1);
	switch ($this->getModeGetContent())
	{
		case 'single':
			$descriptor=&$this->getDescriptor();
			$this->proxy->addProxy($proxy,$descriptor['idCode']);
			break;
		case 'multi':
			$descriptorArray=&$this->getDescriptorArray();
			if(array_key_exists($key,$descriptorArray)) $this->proxy->addProxy($proxy,$descriptorArray[$key]['idCode']);
			break;
		default:
			return 0;
			break;
	}
}

public function &getDescriptor()
{
	//$descriptor= &$this->descriptor;
	//return $descriptor;
	return $this->descriptor;
}
public function &getDescriptorArray()
{
	//$descriptor= &$this->descriptorArray;
	//return $descriptor;
	return $this->descriptorArray;
}

private function initGetContent()
{
	$descriptor=&$this->getDescriptor();
	switch ($this->getModeGetContent())
	{
		case 'single':
			if(!isset($descriptor['idCode'])) $descriptor['idCode']=microtime(1).mt_rand();
			if(!file_exists($this->getDirCookie().$descriptor['idCode'].".cookie"))
			{
				$fh=fopen($this->getDirCookie().$descriptor['idCode'].".cookie","w");
				fclose($fh);
			}
			$descriptor['descriptor']=curl_init();
			break;
		case 'multi':
			$descriptor['descriptor']=curl_multi_init();
			$descriptorArray=&$this->getDescriptorArray();
			if(is_array($descriptorArray))
			{
				$descriptorArray=array_slice($descriptorArray, 0, $this->getCountMultiCURL());
			}
			for($i=0;$i<$this->getCountMultiCURL();$i++)
			{
				if(!isset($descriptorArray[$i]['idCode'])) $descriptorArray[$i]['idCode']=microtime(1).mt_rand();
				if(!file_exists($this->getDirCookie().$descriptorArray[$i]['idCode'].".cookie"))
				{
					$fh=fopen($this->getDirCookie().$descriptorArray[$i]['idCode'].".cookie","w");
					fclose($fh);
				}
				$descriptorArray[$i]['descriptor']=curl_init();
				curl_multi_add_handle($descriptor['descriptor'],$descriptorArray[$i]['descriptor']);
			}
			break;
		default:
			# code...
			break;
	}
}
private function closeGetContent()
{
	$descriptor=&$this->getDescriptor();
	if(isset($descriptor['descriptor']))
	{
		switch ($this->getModeGetContent())
		{
			case 'single':
				if(isset($descriptor['descriptor']))
				{
					curl_close($descriptor['descriptor']);
					if($this->getUseProxy())
					{
						$this->proxy->removeAllRentFromCode($descriptor['idCode']);
					}
					unset($descriptor['descriptor']);
					unset($descriptor['option']);
				}
				break;
			case 'multi':
				$descriptorArray=&$this->getDescriptorArray();
				foreach ($descriptorArray as $key => $value)
				{
					if(isset($descriptorArray[$key]['descriptor']))
					{
						@curl_multi_remove_handle($descriptor['descriptor'],$descriptorArray[$key]['descriptor']);
						curl_close($descriptorArray[$key]['descriptor']);
						if($this->getUseProxy())
						{
							$this->proxy->removeAllRentFromCode($descriptorArray[$key]['idCode']);
						}
						unset($descriptorArray[$key]['descriptor']);
						unset($descriptorArray[$key]['option']);
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

public function getContent($url="")
{
	if($url && is_string($url)) $this->setDefaultSetting(CURLOPT_URL,$url);
	if(is_array($url))
	{
		if($this->getModeGetContent()=='single') $this->setModeGetContent('multi');
	}
	switch ($this->getModeGetContent())
	{
			case 'single':
				$descriptor=&$this->getDescriptor();
				$this->getSingleContent($descriptor);
				break;
			case 'multi':
				$descriptor=&$this->getDescriptor();
				$descriptorArray=&$this->getDescriptorArray();
				if(is_string($url))
				{
					$this->getMultiContent($descriptor,$descriptorArray);
				}
				elseif(is_array($url))
				{
					$countUrl=count($url);
					$countMultiCURL=$this->getCountMultiCURL();
					$countDescriptor=$countUrl*$countMultiCURL;
					$this->setCountMultiCURL($countDescriptor);
					$tmpKeyArray=array();
					reset($descriptorArray);
					foreach ($url as $keyUrl => $valueUrl)
					{
						for ($i=0;$i<$countMultiCURL;$i++)
						{ 
							if(isset($descriptorArray[key($descriptorArray)]['descriptor']))
							{
								$tmpKeyArray[$keyUrl][$i]=key($descriptorArray);
								$this->setOptionToDescriptor($descriptorArray[key($descriptorArray)],CURLOPT_URL,$valueUrl);//,key($descriptorArray)
							}
							next($descriptorArray);
						}
					}
					$answer=$this->getMultiContent($descriptor,$descriptorArray);
					//reset($answer);
					$tmpAnswer=array();
					$j=0;
					foreach ($url as $keyUrl => $valueUrl)
					{
						for ($i=0;$i<$countMultiCURL;$i++)
						{
							if(isset($answer[$j])) $tmpAnswer[$keyUrl][$i]=$answer[$j];
							//if(isset($tmpKeyArray[$keyUrl][$i]))
							//{
							//	$tmpAnswer[$keyUrl][$i]=current($answer);
							//	next($answer);
							//}
							$j++;
						}
					}
					$this->answer=$tmpAnswer;
					$this->setCountMultiCURL($countMultiCURL);
				}
				break;
			default:
				# code...
				break;
	}
	$this->closeGetContent();
	$this->initGetContent();
	return $this->getAnswer();
}

private function getSingleContent(&$descriptor=NULL)
{
	if($descriptor==NULL) $descriptor=&$this->getDescriptor();
	do{
		$this->setOptionsToDescriptor($descriptor);
		$answer=$this->execGetContent($descriptor);
		$answer=$this->prepareContent($answer);
		if($this->checkAnswerValid($answer))
		{
			$this->answer=$answer;
			$this->endRepeat();
			break;
		}
		elseif($this->getUseProxy())
		{
			$this->proxy->removeProxyInList($descriptor['option'][CURLOPT_PROXY]);
		}
	}while($this->repeatGetContent());
	return $answer;
}

private function getMultiContent(&$descriptor=NULL,&$descriptorArray=NULL)
{
	if($descriptor==NULL) $descriptor=&$this->getDescriptor();
	if($descriptorArray==NULL) $descriptorArray=&$this->getDescriptorArray();
	do{
		foreach($descriptorArray as $key => $value)
		{
			$this->setOptionsToDescriptor($descriptorArray[$key]);
		}
		unset($value);
		$answer=$this->execGetContent($descriptor);
		$good_answer=array();
		foreach ($answer as $key => $value)
		{
			$value=$this->prepareContent($value);	
			if($this->checkAnswerValid($value))
			{
				$good_answer[$key]=$value;		
			}
			else
			{
				if($this->useProxy==1 && is_object($this->proxy))
				{
					$this->proxy->removeProxyInList($descriptorArray[$key]['option'][CURLOPT_PROXY]);
				}
			}
		}
		if(count($good_answer)>0)
		{
			$this->endRepeat();
			break;
		}
	}while($this->repeatGetContent());
	$this->answer=$good_answer;
	return $good_answer; 
}

public function setOptionsToDescriptor(&$descriptor,$optionArray=array())
{
	foreach ($this->allSetting as $keySetting)
	{
		if(isset($optionArray[$keySetting])) $this->setOptionToDescriptor($descriptor,$keySetting,$optionArray[$keySetting]);
		elseif(isset($descriptor['option'][$keySetting])) $this->setOptionToDescriptor($descriptor,$keySetting,$descriptor['option'][$keySetting]);
		else $this->setOptionToDescriptor($descriptor,$keySetting);
	}
	unset($keySetting);
	if($this->getUseProxy() && !isset($descriptor['option'][CURLOPT_PROXY])) $this->setOptionToDescriptor($descriptor,CURLOPT_PROXY,$this->proxy->getProxy($descriptor['idCode'],$this->stringWork->getDomainName($descriptor['option'][CURLOPT_URL])));
	$this->setOptionToDescriptor($descriptor,CURLOPT_COOKIEJAR,$this->getDirCookie().$descriptor['idCode'].".cookie");
	$this->setOptionToDescriptor($descriptor,CURLOPT_COOKIEFILE,$this->getDirCookie().$descriptor['idCode'].".cookie");
	if(!$returnSetOpt=curl_setopt_array($descriptor['descriptor'],$descriptor['option']))
	{
		// :|
	}
}
public function setOptionToDescriptor(&$descriptor,$option,$value=NULL,$key=NULL)//
{
	if($key!==NULL)
	{
		$descriptor=&$this->getDescriptorArray();
		if(array_key_exists($key, $descriptor))
		{
			if(is_null($value)) $descriptor[$key]['option'][$option]=$this->getDefaultSetting($option);
			else $descriptor[$key]['option'][$option]=$value;
			if($this->checkOption($descriptor[$key],$option,$descriptor[$key]['option'][$option])) return 0;
		}
	}
	else
	{
		if(is_null($value)) $descriptor['option'][$option]=$this->getDefaultSetting($option);
		else $descriptor['option'][$option]=$value;
		if($this->checkOption($descriptor,$option,$descriptor['option'][$option])) return 0;
	}
}

private function checkOption(&$descriptor,$option,$value=NULL)
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
				$this->setOptionToDescriptor($descriptor,CURLOPT_POST,1);
			}
			break;
		case CURLOPT_URL:
			if(!preg_match("#(http|https)://#iUm", $descriptor['option'][$option]))  $this->setOptionToDescriptor($descriptor,$option,"http://".$value);
			if($this->getInCache())
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

private function execGetContent(&$descriptor=NULL,&$descriptorArray=NULL)
{
	switch ($this->getModeGetContent())
	{
		case 'single':
			if(!$descriptor) $descriptor=&$this->getDescriptor();
			$this->answer=curl_exec($descriptor['descriptor']);
			return $this->answer;
			break;
		case 'multi':
			if(!$descriptor) $descriptor=&$this->getDescriptor();
			if(!$descriptorArray) $descriptorArray=&$this->getDescriptorArray();
			do {
			    	$error=curl_multi_exec($descriptor['descriptor'],$running);
			    	usleep(100);
				} while($running > 0);
			$this->answer=array();
			foreach($descriptorArray as $key => $value)
			{
				$this->answer[$key]=curl_multi_getcontent($descriptorArray[$key]['descriptor']);
			}	
			unset($value);
			return $this->answer;
			break;
		default:
			# code...
			break;
	}
}

public function getAnswer($getAllAnswer=1)
{
	switch ($this->getModeGetContent()) 
	{
		case 'single':
			return $this->answer;
			break;
		case 'multi':
			if($getAllAnswer==0)
			{
				if(is_array(current($this->answer)))
				{
					$a=array();
					foreach ($this->answer as $key => $value)
					{
						$a[$key]=$this->getBigAnswer($value);
					}
					return $a;
				}
				else
				{
					return $this->getBigAnswer($this->answer);
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
private function getBigAnswer($a)
{
	if(!function_exists("sortArrayAnswer"))
	{
		function sortArrayAnswer($a, $b)
		{
			if (strlen($a) < strlen($b)) { return 1; } elseif (strlen($a) == strlen($b)) { return 0; } else { return -1; }
		}
	}
	if(!is_array($a)) return 0;
	usort($a,'sortArrayAnswer');
	return $a[0];
}

private function checkAnswerValid($answer)
{
	if(!$this->getCheckAnswer()) return 1;
	if($this->getUseProxy() && $this->getTypeContent()=="file")
	{
		$reg="/(<!DOCTYPE HTML|<html>|<head>|<title>|<body>|<h1>|<h2>|<h3>)/i";
		if(preg_match($reg, $answer)) return 0;
	}
	if(strlen($answer)>=$this->getMinSizeAnswer())
	{
		if($this->getTypeContent()=="html")
		{
			if(preg_match("|<html[^>]*>.*</html>|iUm", $answer)) return 1;
			else return 0;
		}
		else return 1;
	}
	else return 0;
}

private function prepareContent($answer)
{
	switch ($this->getTypeContent()) 
		{
			case 'file':
				break;
			case 'text':
				$answer=$this->encodingAnswerText($answer);
				break;
			case 'html':
				$answer=$this->encodingAnswerText($answer);
				$answer=$this->stringWork->clearNote($answer,array("/\s+/","/&nbsp;/i","/\n/i","/\r\n/i"));
				break;
			default:
				break;
		}
	return $answer;
}

private function encodingAnswerText($text="")
{
	if($this->getEncodingAnswer())
	{
		$to=$this->getEncodingName();
		$from=$this->checkEncodingAnswer($text);
		return mb_convert_encoding($text,$to,$from);
	}
	else
	{
		return $text;
	}
}

private function checkEncodingAnswer($text="")
{
	$reg="/Content.Type.*text.html.*charset=([^\s\"']+)(?:\s|\"|'|;)/iU";
	if(preg_match($reg, $text,$match))
	{
		if(preg_match("/1251/",$match[1])) $code="cp1251";
		else $code=$match[1];
		$this->setEncodingNameAnswer($code);
	}
	else
	{
		$this->setEncodingNameAnswer(mb_detect_encoding($text));
	}
	return $this->getEncodingNameAnswer();
}

}
?>