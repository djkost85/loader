<?php
require_once dirname(__FILE__) . "/../include.php";

if(isset($_POST['proxy'])){
	$proxy = new GetContent\cProxy();
	$proxy->deleteList($_POST['list']);
	$proxy->createList($_POST['list']);
	foreach(explode("\n", $_POST['proxy']) as $proxyAddress){
		$proxy->addProxy($proxyAddress);
	}

}

?>
<h1>Обновить список прокси</h1>
<form method="post">
	<label>
		<input type="text" name="list" value="auto.ru">
	</label>
	<label>
		Прокси:
		<textarea cols="40" rows="5" name="proxy"></textarea>
	</label><br>Новый прокси с новой строки<br>
<input type="submit" value="Gen">
</form>