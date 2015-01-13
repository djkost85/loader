<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 25.01.14
 * Time: 22:46
 * Project: get_content
 * @author: Evgeny Pynykh <bpteam22@gmail.com>
 * @link bpteam.net
 */

require_once __DIR__ . '/cnfg_test.php';
use GetContent\cStringWork as cStringWork;
echo "cStringWork<br/>\n";

$functions = array(
	'divideOnSentence',
	'encryptDecryptTag',
	'betweenTag',
	'translitCyrillicToLatin',
	'clearNote',
	'getEncodingName',
);

runTest($functions, 'cStringWork_');

function cStringWork_divideOnSentence(){
	$textArray = array('hello world!','Do you test a function?','Yes, I test the function.','Привет мир!','Ты тестируешь функцию?','Да я тестирую функцию.');
	$divideText = cStringWork::divideText(implode(' ', $textArray), 25);
	return !array_diff($textArray, $divideText);
}

function cStringWork_encryptDecryptTag(){
	$text = 'Hello</br><h1>WOW</h1><p>in teg text</p>';
	$stringWork = new cStringWork();
	$encryptText = $stringWork->encryptTag($text);
	$decryptText = $stringWork->decryptTag($encryptText);
	return $text == $decryptText;
}

function cStringWork_betweenTag(){
	$text = '<p>test</p><div>I am test <div class="test">Hi<div> you are cool
Проверка UTF8</div></div>:)</div>';
	$inTag = cStringWork::betweenTag($text, '<div class="test">');
	$whithTag = cStringWork::betweenTag($text, '<div class="test">', false);
	$resultInTag = 'Hi<div> you are cool
Проверка UTF8</div>';
	$resultWhithTag = '<div class="test">Hi<div> you are cool
Проверка UTF8</div></div>';
	return $inTag == $resultInTag && $whithTag == $resultWhithTag;
}

function cStringWork_translitCyrillicToLatin(){
	$text = 'Генадий выпил водки и занялся йогой. Вот алкаш!';
	$translitText = cStringWork::translitCyrillicToLatin($text);
	return $translitText == 'Genadij vypil vodki i zanyalsya jogoj. Vot alkash!';
}

function cStringWork_clearNote(){
	$text = '<h1>Hello Мир!!!     			 фывпфывап asdfsd agjas;dgl
	Как<h1> tak!';
	$trueText = ' Hello Мир!!! фывпфывап asdfsd agjas;dgl Как tak!';
	return $trueText == cStringWork::clearNote( $text, array('%<[^>]+>%ims', '%\s+%',));
}

function cStringWork_getEncodingName(){
	$dir = __DIR__;
	$text_cp1251 = file_get_contents($dir . '/support/cp1251.txt');
	$text_utf8 = file_get_contents($dir . '/support/utf8.txt');
	$cp1251 = cStringWork::getEncodingName($text_cp1251);
	$utf8 = cStringWork::getEncodingName($text_utf8);
	return $cp1251 == 'windows-1251' && $utf8 == 'UTF-8';
}