<?php
require_once __DIR__ . "/../../../include.php";
ini_set('default_charset', 'utf-8');

if(isset($_POST['proxy'])){
	$proxy = new GetContent\cProxy();
	$proxy->deleteList($_POST['list']);
	$proxy->createList($_POST['list']);
	foreach(\GetContent\cStringWork::getIp($_POST['proxy']) as $proxyAddress){
		$proxy->addProxy($proxyAddress);
	}
	?><p>Список обновлен</p><?
}

?>
<h3>Обновить список прокси</h3>
<form method="post">
	<p><label>
		Имя списка: <input type="text" name="list" value="auto.ru">
	</label></p>
	<label>
		Прокси:
		<p><textarea cols="40" rows="5" name="proxy"></textarea></p>
	</label><br>
<input type="submit" value="Gen">
</form>