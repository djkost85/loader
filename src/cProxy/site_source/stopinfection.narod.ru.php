<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;

//return array();
$urlSource = "http://stopinfection.narod.ru/Proxy.htm";
$nameSource = "stopinfection.narod.ru";
$curl = new cSingleCurl();
$curl->setEncodingAnswer(true);
$curl->setEncodingName('UTF-8');
$curl->setTypeContent("html");
$answerStopinfection = $curl->load($urlSource);
$proxyStopinfectionProxy = array();
if (!$answerStopinfection) return array();
if (!preg_match_all('#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu', $answerStopinfection, $matchesStopinfection)) return array();
$tmpArray["source"][$nameSource] = true;
$tmpArray["protocol"]['http'] = true;
foreach ($matchesStopinfection['ip'] as $valueStopinfection) {
	$tmpArray['proxy'] = trim($valueStopinfection);
	$proxyStopinfectionProxy['content'][$tmpArray['proxy']] = $tmpArray;
}
unset($urlSource, $nameSource, $curl, $answerStopinfection, $matchesStopinfection, $valueStopinfection);
return is_array($proxyStopinfectionProxy) ? $proxyStopinfectionProxy : array();
