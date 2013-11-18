<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
namespace stopinfection;

use GetContent\cGetContent as cGetContent;

//return array();
$urlSource = "http://stopinfection.narod.ru/Proxy.htm";
$nameSource = "cool-tests.com";
$getStopinfectionContent = new cGetContent();
$getStopinfectionContent->setEncodingAnswer(true);
$getStopinfectionContent->setEncodingName('UTF-8');
$getStopinfectionContent->setTypeContent("html");
$answerStopinfection = $getStopinfectionContent->getContent($urlSource);
$proxyStopinfectionProxy = array();
if (!$answerStopinfection) return array();
if (!preg_match_all('#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu', $answerStopinfection, $matchesStopinfection)) return array();
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
foreach ($matchesStopinfection['ip'] as $valueStopinfection) {
	$tmpArray['proxy'] = trim($valueStopinfection);
	$proxyStopinfectionProxy['content'][] = $tmpArray;
}
unset($urlSource, $nameSource, $getStopinfectionContent, $answerStopinfection, $matchesStopinfection, $valueStopinfection);
return is_array($proxyStopinfectionProxy) ? $proxyStopinfectionProxy : array();
