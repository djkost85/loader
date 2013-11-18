<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 25.09.13
 * Time: 22:25
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace hidemyass;

use GetContent\cGetContent as cGetContent;
use GetContent\cStringWork as cStringWork;

//return array();
$urlSource = "http://hidemyass.com/proxy-list/";
$nameSource = "hidemyass.com";
$tmpArray["source_proxy"] = $nameSource;
$tmpArray["type_proxy"] = 'http';
$getHidemyassContent = new cGetContent();
$getHidemyassContent->setTypeContent("html");
$proxyHidemyass = array();
do {
	$answerHidemyass = $getHidemyassContent->getContent($urlSource);
	if (!$answerHidemyass) return $proxyHidemyass;
	if (preg_match_all('%<tr\s*class="[^"]*"\s*rel="\d*">(?U)(?<proxyHtml>.*)</tr>%imsu', $answerHidemyass, $matchesHtml)) {
		foreach ($matchesHtml['proxyHtml'] as $proxyHtml) {
			preg_match_all('%\.(?<class>[\w_-]+){display\:\s*inline\s*}%imsu', $proxyHtml, $matchesClass);
			$needClass = implode('|', $matchesClass['class']);
			preg_match_all('%(<(span|div)\s*(style\s*=\s*"\s*display\s*\:\s*inline\s*"|class\s*=\s*"(\d+|' . $needClass . ')")\s*>\s*([^<>]+)\s*|</(span|div|style)>\s*([^"<>]+)\s*)%imsu', $proxyHtml, $matchesProxy);
			preg_match('%</td>\s*<td>\s*(?<port>\d+)\s*</td>%imsu', $proxyHtml, $matchPort);
			$proxyAddress = implode('', $matchesProxy[0]) . ':' . $matchPort['port'];
			$proxyAddress = preg_replace('%<[^<>]*>%imsu', '', $proxyAddress);
			$proxyAddress = preg_replace('%\s+%ms', '', $proxyAddress);
			if (cStringWork::isIp($proxyAddress)) {
				$tmpArray['proxy'] = trim($proxyAddress);
				$proxyHidemyass['content'][] = $tmpArray;
			}
		}
	}
	if (preg_match('%<a\s*href="(?<next>[^"]+)"\s*class="next">Next%imsu', $answerHidemyass, $matchNext)) {
		$urlSource = "http://hidemyass.com" . $matchNext['next'];
	} else {
		unset($urlSource);
	}
	sleep(rand(1, 3));
} while (isset($urlSource));
unset($getHidemyassContent, $answerHidemyass);
return is_array($proxyHidemyass) ? $proxyHidemyass : array();
