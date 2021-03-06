<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.07.13
 * Time: 15:00
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once __DIR__."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cUpdateProxy as cUpdateProxy;
return array();
$urlSource = "http://2freeproxy.com/wp-content/plugins/proxy/load_proxy.php";
$nameSource = "2freeproxy.com";
$proxyTwofreeproxyProxy = array();
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("text");
$httpHead = array(
	'Host: 2freeproxy.com',
	'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
	'Accept: application/json, text/javascript, */*; q=0.01',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
	'X-Requested-With: XMLHttpRequest',
	'Referer: http://2freeproxy.com/anonymous-proxy.html',
	'Content-Length: 14',
	'Connection: keep-alive',
	'Pragma: no-cache',
	'Cache-Control: no-cache'
);
$curl->setDefaultOption(CURLOPT_HTTPHEADER, $httpHead);
$curl->setDefaultOption(CURLOPT_REFERER, 'http://2freeproxy.com/anonymous-proxy.html');
$curl->setDefaultOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0');
$curl->setDefaultOption(CURLOPT_POST, true);
$curl->setDefaultOption(CURLOPT_POSTFIELDS, 'type=anonymous');
$answerTwofreeproxy = $curl->load($urlSource);
$tmpProxyArray = array();
if ($answerTwofreeproxy) {
	$tmpJsonProxy = json_decode($answerTwofreeproxy, true);
	$tmpProxyArray = explode('<br>', $tmpJsonProxy['proxy']);
}
$httpHead = array(
	'Host: 2freeproxy.com',
	'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0',
	'Accept: application/json, text/javascript, */*; q=0.01',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
	'X-Requested-With: XMLHttpRequest',
	'Referer: http://2freeproxy.com/elite-proxy.html',
	'Content-Length: 10',
	'Connection: keep-alive',
	'Pragma: no-cache',
	'Cache-Control: no-cache'
);
$curl->setDefaultOption(CURLOPT_HTTPHEADER, $httpHead);
$curl->setDefaultOption(CURLOPT_REFERER, 'http://2freeproxy.com/elite-proxy.html');
$curl->setDefaultOption(CURLOPT_POST, true);
$curl->setDefaultOption(CURLOPT_POSTFIELDS, 'type=elite');
$answerTwofreeproxy = $curl->load($urlSource);
$tmpProxyArray2 = array();
if ($answerTwofreeproxy) {
	$tmpJsonProxy = json_decode($answerTwofreeproxy, true);
	$tmpProxyArray2 = explode('<br>', $tmpJsonProxy['proxy']);
}
$tmpProxyNew = array_merge($tmpProxyArray2, $tmpProxyArray);
foreach ($tmpProxyNew as $valuePoststar) {
	if(\GetContent\cStringWork::isIp(trim($valuePoststar))){
		$proxyTwofreeproxyProxy[] = trim($valuePoststar);
	}
}
$updateProxy->saveSource($nameSource, $proxyTwofreeproxyProxy);
return $proxyTwofreeproxyProxy;