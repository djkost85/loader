<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 16.01.14
 * Time: 13:09
 * Email: bpteam22@gmail.com
 */

require_once __DIR__ . '/cnfg_test.php';
use GetContent\cPhantomJS as cPhantomJS;
echo "cPhantomJS<br/>\n";

$functions = array(
	'renderText',
	'renderImage',
	'renderPDF',
	'sendPost',
	'setReferer',
);

runTest($functions, 'cPhantomJS_');

function cPhantomJS_renderText(){
	$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
	$text = $phantomJS->renderText('http://ya.ru');
	return preg_match('%yandex%ims', $text);
}

function cPhantomJS_renderImage(){
	$source = 'http://ya.ru';
	$width = 1280;
	$height = 720;
	$picFormat = 'PNG';
	$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
	$img = $phantomJS->renderImage($source, $width, $height, $picFormat);
	$imgHead = imagecreatefromstring($img);
	return imagesy($imgHead) == $height && imagesx($imgHead) == $width;
}

function cPhantomJS_renderPDF(){
	$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
	$source = 'http://ya.ru';
	$fileName = $phantomJS->getDirForFile() . DIRECTORY_SEPARATOR . 'testFile.pdf';
	$sizePaper = 'A4';
	$orientation = 'portrait';
	$marginCm = 1;
	$phantomJS->renderPdf($source, $fileName, $sizePaper, $orientation, $marginCm);
	return file_exists($fileName);
}

function cPhantomJS_sendPost(){
	$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
	$post = 'url=vk.com&test=test_post';
	$source = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/support/post_test.php';
	$text = $phantomJS->sendPost($source,$post);
	return preg_match('%test_post%ims', $text);
}

function cPhantomJS_setReferer(){
	$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
	$source = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/support/referer.php';
	$referer = 'http://iamreferer.net';
	$phantomJS->setReferer($referer);
	$text = $phantomJS->renderText($source);
	return preg_match('%iamreferer%ims', $text);
}