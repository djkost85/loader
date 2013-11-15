<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 11.05.13
 * Time: 13:34
 * Project: GetContent
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Тест функции betweenTag
 */
use GetContent\cStringWork as c_string_work;
require_once dirname(__FILE__)."/../../include.php";
$html=file_get_contents('test.htm');
$text=cStringWork::between_tag($html,'<div align="left" style="position: absolute; top: 0px; left: 0px;">'); // $text=содержимое тега <div align="left" style="position: absolute; top: 0px; left: 0px;">