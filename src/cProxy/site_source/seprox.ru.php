<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 08.05.13
 * Time: 5:33
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Модуль к классу  cProxy для скачивания списка прокси адресов с сайта seprox.ru
 */

require_once dirname(__FILE__)."/../../../include.php";
use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

//return array();
$urlSource = "http://seprox.ru/ru/proxy_filter/0_0_0_0_0_0_0_0_0_";
$nameSource = "seprox.ru";
//return array();
$curl = new cGetContent('cSingleCurl');
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("html");
$pagenation = 0;
$content = $curl->load($urlSource . $pagenation . ".html");
$countPage = 0;
if (preg_match('/<div\s*class=\"countResult\">\s*Всего\s*найдено.\s*(\d+)\s*<\/div>/iUs', $content, $match)) $countPage = ceil($match[1] / 15);
// JavaScript приколы с приведением типов. Расшифровка:
$javascriptEncode = array(
	"a" => "(![]+[])[+!+[]]",
	"b" => "([]+[]+{})[!+[]+!+[]]",
	"c" => "([![]]+{})[+!+[]+[+[]]]",
	"d" => "([]+[]+[][[]])[!+[]+!+[]]",
	"e" => "(!![]+[])[!+[]+!+[]+!+[]]",
	"f" => "(![]+[])[+[]]",
	"i" => "([![]]+[][[]])[+!+[]+[+[]]]",
	"n" => "([]+[]+[][[]])[+!+[]]",
	"o" => "([]+[]+{})[+!+[]]",
	"r" => "(!![]+[])[+!+[]]",
	"t" => "(!![]+[])[+[]]",
	"u" => "(!![]+[])[!+[]+!+[]]",
	" " => "(+{}+[]+[]+[]+[]+{})[+!+[]+[+[]]]",
	"***" => "+++",
	"" => "+",
	"+" => "***"
);
$proxySeprox = array();
do {
	$regEx = '#<tr\s*class="proxyStr">\s*<td>\s*<script\s*type="text/javascript">\s*(?<js>[^<]*)\s*</script>\s*</td>\s*<td>\s*(?<type_proxy>.*)\s*</td>#iUms';
	if (!preg_match_all($regEx, $content, $matchesSecretCode)) break;
	foreach ($matchesSecretCode['js'] as $keySecretCode => $strSecretCode) {
		if (!preg_match('#Proxy=String.fromCharCode\((?<js_code>[^\)]*)\)#iUs', $strSecretCode, $matchSecretArray)) break;
		$lit = explode(",", $matchSecretArray['js_code']);
		$litera = array();
		foreach ($lit as $key => $value) $litera[$key] = chr($value);
		foreach ($litera as $keyLitera => $valueLitera)
			$strSecretCode = preg_replace('#Proxy\[' . $keyLitera . '\]#iUs', $valueLitera, $strSecretCode);
		foreach ($javascriptEncode as $keyJavascript => $valueJavascript)
			$strSecretCode = preg_replace('#' . preg_quote($valueJavascript, '#') . '#', $keyJavascript, $strSecretCode);
		preg_match_all('#(?:\(|\+)(?<ip>\w+)#s', $strSecretCode, $matchesSecretVar);
		$ip = "";
		foreach ($matchesSecretVar['ip'] as $valueIp){
			if (preg_match('#' . $valueIp . '=\'(?<ip>[^\']*)\'#s', $strSecretCode, $matchIp)) $ip .= $matchIp['ip'];
		}
		$ip = trim($ip);
		if (cStringWork::isIp($ip)) {
			$proxySeprox[] = $ip;
		}
	}
	$pagenation++;
	sleep(rand(1, 3));
	if (!$content = $curl->load($urlSource . $pagenation . ".html")) continue;
} while ($pagenation < $countPage);
$updateProxy->saveSource($nameSource, $proxySeprox);
return $proxySeprox;
