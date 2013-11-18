<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 25.09.13
 * Time: 22:25
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace foxtools;

use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;

//return array();
$urlSource = "http://foxtools.ru/Proxy?page=";
$nameSource = "foxtools.ru";
$getFoxtoolsContent = new cGetContent();
$getFoxtoolsContent->setTypeContent("html");
$proxyFoxtools = array();
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
for ($nom = 1; $nom < 50; $nom++) {
	$urlPage = $urlSource . $nom;
	$answerFoxtools = $getFoxtoolsContent->getContent($urlPage);
	if (!$answerFoxtools) return $proxyFoxtools;
	$answerFoxtools = cStringWork::betweenTag($answerFoxtools, '<table style="width:100%" id="theProxyList">');
	if (!preg_match_all('%<td\s*style="[^"]*">(?<ip>\d+.\d+.\d+.\d+)</td>\s*<td\s*style="[^"]*">(?<port>\d+)</td>%imsu', $answerFoxtools, $matchesIp)) break;
	foreach ($matchesIp['ip'] as $key => $proxyIp) {
		$proxyAddress = $proxyIp . ':' . $matchesIp['port'][$key];
		if (cStringWork::isIp($proxyAddress)) {
			$tmpArray['proxy'] = trim($proxyAddress);
			$proxyFoxtools['content'][] = $tmpArray;
		}
	}
	sleep(rand(1, 3));
}
unset($urlSource, $nameSource, $getFoxtoolsContent, $urlPage, $answerFoxtools, $matchesIp);
return is_array($proxyFoxtools) ? $proxyFoxtools : array();
