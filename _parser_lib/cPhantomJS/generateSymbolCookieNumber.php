<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 21.01.14
 * Time: 15:29
 * Email: bpteam22@gmail.com
 */
if($_GET['countCookie']){
	$countCookie = $_GET['countCookie'];
	for($i=0;$i<=$countCookie;$i++){
		if(!$i) continue;
		setcookie('set_test' . $i, 'test_value123', time()+100000, '/', '.test1.ru');
	}
} elseif($_GET['lengthCookie']){
	$lengthCookie = $_GET['lengthCookie'];
	$value = '';
	for($i=1;$i<=$lengthCookie;$i++){
		$value .= '1';
	}
	setcookie('n', $value, time()+100000, '/', '.t.ru');
}
//var_dump($_COOKIE);