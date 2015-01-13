<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 19.02.14
 * Time: 12:13
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

use GetContent\cPhantomJS as cPhantomJS;
if(isset($_POST['url'])){
require_once __DIR__ . "/../include.php";

$phantomJS = new cPhantomJS(PHANTOMJS_EXE);
var_dump($_POST['url']);
$text = $phantomJS->renderText($_POST['url']);
echo $text;
} else {
?>
<form method="post">
	<input type="text" name="url"><input type="submit" name="sub" value="GO!">
</form>
<?}?>