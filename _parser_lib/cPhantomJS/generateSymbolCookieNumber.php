<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 21.01.14
 * Time: 15:29
 * Email: bpteam22@gmail.com
 */
	$lengthCookie = $_GET['lengthCookie'];
	$value = '';
	for($i=1;$i<=$lengthCookie;$i++){
		$value .= '1';
	}
	setcookie('t', $value, time()+100000, '/', '.t.ru');
//var_dump($_COOKIE);