<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 13:38
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Тест функций кодирования HTML тегов
 */
use GetContent\cStringWork as cStringWork;
require_once dirname(__FILE__)."/../../include.php";
$html=file_get_contents('test.htm');
$sw= new cStringWork($html);
$sw->encryptTag();
//echo $sw->getText(); // строка с закодироваными тегами
$sw->decryptTag();
//echo $sw->getText(); // строка с декодированными тегами