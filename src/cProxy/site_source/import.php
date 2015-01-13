<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 14.05.13
 * Time: 3:35
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

require_once __DIR__."/../../../include.php";

use GetContent\cStringWork as cStringWork;
use GetContent\cUpdateProxy as cUpdateProxy;


$urlSource = __DIR__ . "/../proxy_list/source/import.page";
$nameSource = "import";

$updateProxy = new cUpdateProxy();
$fh = fopen($urlSource, 'r');
while($answer = fgets($fh)){
	if (preg_match_all('#(?<ip>\s*(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:{1}\d{1,10})\s*)#ims', $answer, $matches)){
		foreach ($matches['ip'] as $value) {
			$value = trim($value);
			if(cStringWork::isIp($value)){
				$proxy[] = $value;
			}
		}
	}
}
$updateProxy->saveSource($nameSource, $proxy);
return $proxy;