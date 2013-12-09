<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 25.11.13
 * Time: 2:04
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

if(isset($_GET['redirect'])){
	var_dump($_SERVER);
	echo 'REDIRECT!!!';
} else {
	header ("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?redirect=set");
}