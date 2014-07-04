<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 22:15
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cUpdateProxy as cUpdateProxy;

$nameSource = "notan.h1.ru";
$proxyNotanProxy = array();
$updateProxy = new cUpdateProxy();
for($i=1;$i<=10;$i++){
	$urlSource="http://notan.h1.ru/hack/xwww/proxy".$i.".html";
	$curl = new cSingleCurl();
	$curl->setTypeContent("html");
	$answerNotan = $curl->load($urlSource);
	if(!$answerNotan) break;
	if(!preg_match_all('%<TD\s*class=name>\s*(?<ip>\d+\.\d+\.\d+\.\d+\:\d+)\s*</TD>%ims',$answerNotan,$matchesNotan)) break;
	foreach ($matchesNotan['ip'] as $valueNotan) {
		$proxyNotanProxy[] = trim($valueNotan);
	}
	sleep(rand(1,3));
}
$updateProxy->saveSource($nameSource, $proxyNotanProxy);
return $proxyNotanProxy;