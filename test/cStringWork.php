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

require_once dirname(__FILE__) . '/cnfg_test.php';
use GetContent\cStringWork as cStringWork;
echo "cStringWork<br/>\n";

$functions = array(
	'divideOnSentence',
	'encryptDecryptTag',
	'betweenTag',
	'translitCyrillicToLatin',
);

runTest($functions, 'cStringWork_');

function cStringWork_divideOnSentence(){
	$textArray = array('hello world!','Do you test a function?','Yes, I test the function.','Привет мир!','Ты тестируешь функцию?','Да я тестирую функцию.');
	$divideText = cStringWork::divideText(implode(' ', $textArray), 25);
	return !array_diff($textArray, $divideText);
}

function cStringWork_encryptDecryptTag(){
	$text = 'Hello</br><h1>WOW</h1><p>in teg text</p>';
	$st = new cStringWork();
	$encryptText = $st->encryptTag($text);
	$decryptText = $st->decryptTag($encryptText);
	return $text == $decryptText;
}

function cStringWork_betweenTag(){
	$text = '<p>test</p><div>I am test <div class="test">Hi<div> you are cool
	Проверка UTF8</div></div>:)</div>';
	$inTag = cStringWork::betweenTag($text, '<div class="test">');
	$whithTag = cStringWork::betweenTag($text, '<div class="test">', false);
	return $inTag == 'Hi<div> you are cool
	Проверка UTF8</div>' && $whithTag == '<div class="test">Hi<div> you are cool
	Проверка UTF8</div></div>';
}

function cStringWork_translitCyrillicToLatin(){
	$text = 'Генадий выпил водки и занялся йогой. Вот алкаш!';
	$translitText = cStringWork::translitCyrillicToLatin($text);
	return $translitText == 'Genadij vypil vodki i zanyalsya jogoj. Vot alkash!';
}