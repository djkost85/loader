<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ec
 * Date: 26.09.13
 * Time: 20:25
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use get_content\c_proxy\c_proxy as c_proxy;
require_once dirname(__FILE__)."/../../include.php";
set_time_limit(3600);
$proxy = new c_proxy();
$proxy->select_proxy_list('all');
$list = $proxy->get_proxy_list();
$data = array();
$data['cookie']=0;
$data['get']=0;
$data['post']=0;
$data['referer']=0;
$data['anonym']=0;

foreach ($list['content'] as $proxy) {
    if(!isset($data[$proxy['source_proxy']])) $data[$proxy['source_proxy']]=0;
    $data[$proxy['source_proxy']]++;
    $data['cookie'] += $proxy['cookie'];
    $data['get'] += $proxy['get'];
    $data['post'] += $proxy['post'];
    $data['referer'] += $proxy['referer'];
    $data['anonym'] += $proxy['anonym'];
}
foreach ($data as $source_proxy => $count) {
    echo '<p>'.$source_proxy.':'.$count.'</p>';
}
