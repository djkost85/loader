<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 13.10.2014
 * Time: 12:57
 * Project: fo_realty
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cTor {

	private $exePath = 'tor';
	private $host = '127.0.0.1';
	private $port = '9050';

	function __construct(){
		$this->start();
	}

	function __destruct(){
		$this->stop();
	}

	public function getTorConnection(){
		return $this->host.':'.$this->port;
	}

	public function execCommand($command){
		$output = array();
		$return_val = null;
		exec($command, $output, $return_val);
		return $output ? implode("\n", $output) : $return_val;
	}

	private function start(){
		$result = $this->execCommand($this->exePath);
		return $result;
	}

	private function stop(){
		$result = $this->execCommand('killall '.$this->exePath);
		return $result;
	}

	public function flush(){
		return $this->execCommand('killall -HUP '.$this->exePath);
	}

	public function restart(){
		$this->stop();
		$this->start();
	}

} 