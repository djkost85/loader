<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 27.02.14
 * Time: 9:05
 * Email: bpteam22@gmail.com
 */

require_once dirname(__FILE__) . "/../../../include.php";
use GetContent\cProxy AS cProxy;
?>
<form method="post">
	<input type="text" name="list_name" value="<?=isset($_POST['list_name'])?$_POST['list_name']:''?>"> List name<br/>
<?
if(isset($_POST['list_name'])){
	$proxy = new cProxy();
	$proxy->selectList($_POST['list_name']);
	if(isset($_POST['send_form'])){
		$proxy->setListOption('url', $_POST['url']);
		$proxy->setListOption('check_word', explode("\n",$_POST['check_word']));
		$functions = array();
		foreach ($proxy->getListFunction() as $function) {
			if($function == 'country') continue;
			if(isset($_POST['function_'.$function])){
				$functions[] = $function;
			}
		}
		if($_POST['country']){
			$proxy->setListOption('country', explode("\n",$_POST['country']));
		}
		$proxy->setListOption('function', $functions);
		$proxy->setListOption('need_update', isset($_POST['need_update']));
	}
?>
	<input name="send_form" type="hidden" value="create">
	<input name="url" type="text" value="<?=$proxy->getListOption('url')?>"> url<br/>
	<textarea name="check_word"><?=implode("\n", $proxy->getListOption('check_word'))?></textarea>check_word<br/>
	<textarea name="country"><?=implode("\n", $proxy->getListOption('country'))?></textarea>country<br/>
	<?foreach ($proxy->getListFunction() as $function) {
		if($function == 'country') continue;
	?>
		<input name="function_<?=$function?>" type="checkbox" <?=(in_array($function, $proxy->getListOption('function')))?'checked':''?>><?=$function?> <br/>
	<?
	}
	?>
	<input type="checkbox" name="need_update" <?=$proxy->getListOption('need_update')?'checked':''?>>need_update<br/>
<?
}
?>
	<input type="submit" name="s1" value="Go!">
</form>