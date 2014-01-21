<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 21.01.14
 * Time: 15:29
 * Email: bpteam22@gmail.com
 */
setcookie('set_test123', 'test_value123', time()+100000, '/', '.test1.ru');
setcookie('set_test1', 'test_value1', time()+100000, '/', '.test1.ru');
var_dump($_COOKIE);