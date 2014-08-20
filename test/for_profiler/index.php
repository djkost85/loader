<?php
$data = array(
	'cSingleCurl',
	'cMultiCurl',
);
foreach($data as $class){?>
	<a href="<? echo $class;?>.php?XDEBUG_PROFILE=1"><? echo $class;?></a><br/>
<?}