<?php
/**
 * Created by JetBrains PhpStorm.
 * User: EC
 * Date: 04.05.13
 * Time: 20:01
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 *
 * Файл для проверки работоспособности прокси и его функций. распологаете в удобном месте у себя или на другом сервере
 * Прописываете адрес к нему в check_url_list.php
 * Возвращает строку из единиц и нулей. Единица функция работает, ноль не поддерживается.
 * Описание последовательности:
 * Анонимность|REFERER|POST|GET|COOKIE
 */
$str='';
$plus='';
$headLogFile=dirname(__FILE__).'/head.log';
if(!isset($_GET['str']))
{
    $anonym='1';
    $ipHostHead = array('REMOTE_ADDR','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP');
    foreach ($_SERVER as $key => $value) {
        if(in_array($key,$ipHostHead) && preg_match("%".preg_quote($_GET['ip'],"%")."%ims",$value)){
            $anonym = 0;
        } elseif(preg_match("%".preg_quote($_GET['ip'],"%")."%ims",$value) && is_writable($headLogFile)){
            $fh = fopen($headLogFile,'+a');
            fwrite($fh,$key."\t".$_GET['ip']."\t->\t".$value."\n");
            fclose($fh);
        }
    }
    $str.=$anonym;
    if($_SERVER['HTTP_REFERER']=="proxy-check.net") $str.='1';
    else $str.='0';
    if($_POST['proxy']=='yandex') $str.='1';
    else $str.='0';
    if($_GET['proxy']=='yandex') $str.='1';
    else $str.='0';
    $plus.="&str=".$str;
    if(!isset($_GET['cookie']))
    {
        if(setcookie("test", "set")) header ("Location: ".$_SERVER['PHP_SELF']."?cookie=set".$plus);
        exit;
    }
}
$str.=$_GET['str'];
if(isset($_COOKIE['test'])) $str.='1';
else $str.='0';
echo $str;
