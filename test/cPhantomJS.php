<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 16.01.14
 * Time: 13:09
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cPhantomJS as cPhantomJS;
echo "cPhantomJS<br/>\n";

$functions = array(
	'renderText',
	'renderImage',
	'renderPDF',
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
	$ih = imagecreatefromstring($img);
	return imagesy($ih) == $height && imagesx($ih) == $width;
}

function cPhantomJS_renderPDF(){
	$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
	$source = 'http://ya.ru';
	$fileName = 'testFile.pdf';
	$sizePaper = 'A4';
	$orientation = 'portrait';
	$marginCm = 1;
	$phantomJS->renderPdf($source, $fileName, $sizePaper, $orientation, $marginCm);
	return file_exists($phantomJS->getDirForFile() . DIRECTORY_SEPARATOR . $fileName);
}