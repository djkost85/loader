<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 2:36
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";
use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

$urlSource = "http://www.poststar.ru/proxy.htm";
$nameSource = "poststar.ru";
$curl = new cGetContent('cSingleCurl');
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("html");
$answerPoststar = $curl->load($urlSource);
$answerPoststar = cStringWork::betweenTag($answerPoststar, '<table width="730" border="0" align="center">');
$proxyPoststarProxy = array();
if(preg_match_all('#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims', $answerPoststar, $matchesPoststar)){
	foreach ($matchesPoststar['ip'] as $valuePoststar) {
		$valuePoststar = trim($valuePoststar);
		if(cStringWork::isIp($valuePoststar)){
			$proxyPoststarProxy[] = $valuePoststar;
		}
	}
}
$updateProxy->saveSource($nameSource, $proxyPoststarProxy);
return $proxyPoststarProxy;