<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 03.02.14
 * Time: 8:48
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . "/../include.php";

use GetContent\cMultiCurl as cMultiCurl;

$url = $url = array('http://ya.ru', 'http://vk.com', 'http://google.com', 'http://yandex.ru', 'http://youtube.com', 'vkontakte.ru');
$curl = new cMultiCurl();
$curl->setCountCurl(count($url));
$descriptors = $curl->getDescriptorArray();
foreach ($descriptors as &$descriptor) {
	$curl->setOption($descriptor,  CURLOPT_REFERER, 'http://google.com');
}