<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 13:38
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Тест функций кодирования HTML тегов
 */
use get_content\c_string_work as c_string_work;
require_once dirname(__FILE__)."/../../include.php";
$html=file_get_contents('test.htm');
$sw= new c_string_work($html);
$sw->encrypt_tag();
//echo $sw->get_text(); // строка с закодироваными тегами
$sw->decrypt_tag();
//echo $sw->get_text(); // строка с декодированными тегами