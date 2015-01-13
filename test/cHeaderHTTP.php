<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 10.10.2014
 * Time: 10:46
 * Project: loader
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once __DIR__ . '/cnfg_test.php';
use GetContent\cHeaderHTTP as cHeaderHTTP;
echo "cHeaderHTTP<br/>\n";

$functions = array(
	'checkMimeType',
);

runTest($functions, 'cHeaderHTTP_');

function cHeaderHTTP_checkMimeType(){
	$http = new cHeaderHTTP();
	return $http->checkMimeType('audio/mpeg', 'file')
	&& $http->checkMimeType('image/png', 'img')
	&& $http->checkMimeType('text/html', 'html')
	&& !$http->checkMimeType('image/png', 'html');
}