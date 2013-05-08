<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.05.13
 * Time: 20:01
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 *
 * Файл для проверки работоспособности прокси и его функций.
 * Возвращает строку из единиц и нулей. Единица функция работает, ноль не поддерживается.
 * Описание последовательности:
 * Анонимность|REFERER|POST|GET|COOKIE
 */
$str='';
$plus='';
if(!isset($_GET['str']))
{
    $anonim='1';
    if(preg_match("#".preg_quote($_GET['ip'],"#")."#ims",$_SERVER['HTTP_X_FORWARDED_FOR'])) $anonim='0';
    elseif($_SERVER['REMOTE_ADDR']==$_GET['ip']) $anonim='0';
    elseif($_SERVER['HTTP_X_REAL_IP']==$_GET['ip']) $anonim='0';
    $str.=$anonim;
    if($_SERVER['HTTP_REFERER']=="proxy-check.net") $str.='1';
    else $str.='0';
    if($_POST['proxy']=='yandex') $str.='1';
    else $str.='0';
    if($_GET['proxy']=='yandex') $str.='1';
    else $str.='0';
    $plus.="&str=".$str;
    if(!isset($_GET['cookie']))
    {
        if($_GET['proxy']=="yandex") $plus.="&proxy=yandex";
        if(setcookie("test", "set")) header ("Location: ".$_SERVER['PHP_SELF']."?cookie=set".$plus);
        exit;
    }
}
$str.=$_GET['str'];
if(isset($_COOKIE['test'])) $str.='1';
else $str.='0';
echo $str;
