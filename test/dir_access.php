<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 22.10.2014
 * Time: 17:12
 * Project: fo_realty
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */
$coolLibDir = dirname(__FILE__).'/../../'; // in _coolLib
$loaderDir = $coolLibDir.'loader/';
$gd2ocrDir = $coolLibDir.'gd2-php-ocr/';
$parserDir = $coolLibDir.'parser/';
$posterDir = $coolLibDir.'poster/';
echo "<pre>\n";
clearstatcache(true);
checkDir($loaderDir.'src/cCookie/cookies', 'is_writable', true);

checkDir($loaderDir.'src/cPhantomJS/files', 'is_writable', true);
checkDir($loaderDir.'src/cPhantomJS/script', 'is_readable', true);
checkDir($loaderDir.'src/cPhantomJS/storage', 'is_writable', true);

checkDir($loaderDir.'src/cProxy/proxy_list', 'is_writable', true);
checkDir($loaderDir.'src/cProxy/proxy_list/source', 'is_writable', true);



echo "</pre>\n";



function checkDir($path, $function = 'is_writable', $need = true){
	echo $path . ' ' . $function . ' ' . ($function($path) === $need?'success':'ERROR') . "\n";
}