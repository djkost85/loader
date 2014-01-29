<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 08.05.13
 * Time: 5:33
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
// "cool-proxy.net"=>"http://cool-proxy.net/proxies/http_proxy_list/page:",

use GetContent\cSingleCurl as cSingleCurl;
use GetContent\cStringWork as cStringWork;

$urlSource = "http://www.cool-proxy.net/proxies/http_proxy_list/page:";
$nameSource = "cool-proxy.net";
$curl = new cSingleCurl();
$curl->setTypeContent("html");
$i = 1;
if (!$content = $curl->getContent($urlSource . $i . "/sort:working_average/direction:asc")) return array();
if (preg_match_all('#/proxies/http_proxy_list/sort:working_average/direction:asc/page:(?<pagenation>\d*)"#iUm', $content, $matches)) {
	$countPage = max($matches['pagenation']);
} else return array();
$proxyCoolProxy = array();
do {
	if ($countProxy = preg_match_all('#<td\s*style=\"text.align.left.\s*font.weight.bold.\"><script type="text/javascript">document\.write\(Base64\.decode\("(?<ip_base64>.*)"\)\)</script></td>\s*<td>(?<port>\d+)</td>#iUms', $content, $matches)) {
		$tmpArray["source"][$nameSource] = true;
		$tmpArray["protocol"]['http'] = true;
		for ($j = 0; $j < $countProxy; $j++) {
			$is_ip = base64_decode($matches['ip_base64'][$j]) . ":" . $matches['port'][$j];
			if (cStringWork::isIp($is_ip)) {
				$tmpArray['proxy'] = trim($is_ip);
				$proxyCoolProxy['content'][$tmpArray['proxy']] = $tmpArray;
			}
		}
	}
	$i++;
	sleep(rand(1, 3));
	$content = $curl->getContent($urlSource . $i . "/sort:working_average/direction:asc");
} while ($i <= $countPage);
unset($urlSource, $nameSource, $curl, $content, $countProxy);
return is_array($proxyCoolProxy) ? $proxyCoolProxy : array();
