<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cUpdateProxy as cUpdateProxy;

//return array();
$urlSource = "http://stopinfection.narod.ru/Proxy.htm";
$nameSource = "stopinfection.narod.ru";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setEncodingAnswer(true);
$curl->setEncodingName('UTF-8');
$curl->setTypeContent("html");
$answerStopinfection = $curl->load($urlSource);
$proxyStopinfectionProxy = array();
if ($answerStopinfection && preg_match_all('#(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})#imsu', $answerStopinfection, $matchesStopinfection)){
	foreach ($matchesStopinfection['ip'] as $valueStopinfection) {
		$proxyStopinfectionProxy[] = trim($valueStopinfection);
	}
}
$updateProxy->saveSource($nameSource, $proxyStopinfectionProxy);
return $proxyStopinfectionProxy;