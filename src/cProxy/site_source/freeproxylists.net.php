<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 08.05.13
 * Time: 5:33
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once dirname(__FILE__)."/../../../include.php";
use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;

return array();
$urlSource = "http://www.freeproxylists.net/ru/";
$nameSource = "freeproxylists.net";
$curl = new cSingleCurl();
$updateProxy = new cUpdateProxy();
$proxyArray = array();
do {
	$answer = $curl->load($urlSource);
	var_dump($urlSource,$answer);
	$curl->setDefaultOption(CURLOPT_REFERER, $urlSource);
	$answer = $curl->load('http://www.freeproxylists.net/php/h.php');
	var_dump($urlSource,$answer);
	exit;
	if (!$answer) break;
	if (preg_match_all('%IPDecode\("(?<encoded_ip>[^"]+)"\)</script>\s*</td>\s*<td\s*align="center">\s*(?<port>\d+)\s*</td>%imsu', $answer, $matchesHtml)) {
		foreach ($matchesHtml['encoded_ip'] as $key => $proxy) {
			$proxy = trim(urldecode($proxy));
			$proxyAddress = $proxy . ':' . $matchesHtml['port'][$key];
			if (cStringWork::isIp($proxyAddress)) {
				$proxyArray[] = $proxyAddress;
			}
		}
	}
	if (preg_match('%<a\s*href="\./\?page=(?<next>\d+)">Следующая%imsu', $answer, $matchNext)) {
		$urlSource = "http://www.freeproxylists.net/ru/?page=" . $matchNext['next'];
	} else {
		unset($urlSource);
	}
	sleep(rand(1, 3));
	if(!isset($urlSource)){
		break;
	}
} while (true);
$updateProxy->saveSource($nameSource, $proxyArray);
return $proxyArray;