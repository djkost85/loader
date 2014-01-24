<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 21.01.14
 * Time: 15:29
 * Email: bpteam22@gmail.com
 */
if(isset($_GET['lengthCookie'])){
	$lengthCookie = $_GET['lengthCookie'];
	$value = '';
	for($i=1;$i<=$lengthCookie;$i++){
		$value .= '1';
	}
	setcookie('t', $value, time()+100000, '/', '.e.com');
} else {
	var_dump($_COOKIE);
}
