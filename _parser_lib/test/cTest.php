<?php
/**
 * Created by PhpStorm.
 * User: EC_l
 * Date: 15.01.14
 * Time: 17:50
 * Email: bpteam22@gmail.com
 */

namespace Test;


class cTest {

	public $functions;

	public final function runTest(){
		$start = microtime(true);
		echo date("[H:i:s Y/m/d]", $start)."\n<br>\n";
		echo "<table border='1' cellpadding='2'>";
		foreach($this->functions as $function){
			echo "<tr>";
			echo "<td> $function </td>";
			$funStart = microtime(true);
			if($this->$function()){
				echo "<td> success </td>";
			} else {
				echo "<td> <b> ERROR </b> </td>";
				break;
			}
			$funTime = microtime(true) - $funStart;
			echo " <td> [$funTime] </td>";
			echo "</tr>";
		}
		echo "</table>";
		$end = microtime(true);
		echo date('[H:i:s Y/m/d]', $end)."\n<br>\n";
		echo '[~'.($end-$start).']';
	}
} 