<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 12.05.13
 * Time: 1:08
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 * Удаление файлов с cookie которым больше часа
 */
use get_content\c_get_content\c_get_content as c_get_content;
require_once "../../include.php";
$get_content=new c_get_content();
$get_content->clear_cookie(3600);