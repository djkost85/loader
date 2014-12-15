<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 10.10.2014
 * Time: 10:46
 * Project: loader
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cHeaderHTTP as cHeaderHTTP;
echo "cHeaderHTTP<br/>\n";

$functions = array(
	'checkMimeType',
);

runTest($functions, 'cHeaderHTTP_');

function cHeaderHTTP_checkMimeType(){
	$gc = new cHeaderHTTP();
	return $gc->checkMimeType('audio/mpeg', 'file')
	&& $gc->checkMimeType('image/png', 'img')
	&& $gc->checkMimeType('text/html', 'html')
	&& !$gc->checkMimeType('image/png', 'html');
}