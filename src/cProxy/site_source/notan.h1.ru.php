<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cSingleCurl as cSingleCurl;

$nameSource = "notan.h1.ru";
$proxyNotanProxy = array();
$tmpArray["source"][$nameSource] = true;
$tmpArray["protocol"]['http'] = true;
for($i=1;$i<=10;$i++){
	$urlSource="http://notan.h1.ru/hack/xwww/proxy".$i.".html";
	$curl = new cSingleCurl();
	$curl->setTypeContent("html");
	$answerNotan = $curl->load($urlSource);
	if(!$answerNotan) return $proxyNotanProxy;
	if(!preg_match_all('%<TD\s*class=name>\s*(?<ip>\d+\.\d+\.\d+\.\d+\:\d+)\s*</TD>%ims',$answerNotan,$matchesNotan)) return $proxyNotanProxy;
	foreach ($matchesNotan['ip'] as $valueNotan) {
		$tmpArray['proxy'] = trim($valueNotan);
		$proxyNotanProxy['content'][$tmpArray['proxy']] = $tmpArray;
	}
	sleep(rand(1,3));
}
unset($nameSource, $curl, $answerNotan, $matchesNotan);
return is_array($proxyNotanProxy) ? $proxyNotanProxy : array();
