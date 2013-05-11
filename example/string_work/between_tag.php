<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 13:34
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Тест функции between_tag
 */
use get_content\c_string_work\c_string_work as c_string_work;
require_once "../../include.php";
$html=file_get_contents('test.htm');
$text=c_string_work::between_tag($html,'<div align="left" style="position: absolute; top: 0px; left: 0px;">'); // $text=содержимое тега <div align="left" style="position: absolute; top: 0px; left: 0px;">