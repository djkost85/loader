<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 25.09.13
 * Time: 22:25
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

$urlSource = "http://foxtools.ru/Proxy?page=";
$nameSource = "foxtools.ru";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$curl->setTypeContent("html");
$proxyFoxtools = array();
for ($nom = 1; $nom < 50; $nom++) {
	$urlPage = $urlSource . $nom;
	$answerFoxtools = $curl->load($urlPage);
	if (!$answerFoxtools) break;
	$answerFoxtools = cStringWork::betweenTag($answerFoxtools, '<table style="width:100%" id="theProxyList">');
	if (!preg_match_all('%<td\s*style="[^"]*">(?<ip>\d+.\d+.\d+.\d+)</td>\s*<td\s*style="[^"]*">(?<port>\d+)</td>%imsu', $answerFoxtools, $matchesIp)) break;
	foreach ($matchesIp['ip'] as $key => $proxyIp) {
		$proxyAddress = $proxyIp . ':' . $matchesIp['port'][$key];
		if (cStringWork::isIp($proxyAddress)) {
			$proxyFoxtools[] = trim($proxyAddress);
		}
	}
	sleep(rand(1, 3));
}
$updateProxy->saveSource($nameSource, $proxyFoxtools);
return $proxyFoxtools;