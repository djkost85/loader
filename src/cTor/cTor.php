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
	/**
	 * @var cFile
	 */
	private $file;
	private $exePath = '/etc/init.d/tor';
	private $pathToConfig;
	private $host = '127.0.0.1';
	private $port = '9050';
	private $configPattern = 'server = %s
server_port = %d';
	const keyPullStart = 20000;
	const keyPullEnd = 60000;

	/**
	 * @return string
	 */
	public function getHost() {
		return $this->host;
	}

	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * @return string
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param string $port
	 */
	public function setPort($port) {
		if(preg_match('%^\d+$%',$port) && $port >= self::keyPullStart && $port <= self::keyPullEnd && $this->isFreePort($port)){
			$this->port = $port;
		}
	}

	function __construct($port = false){
		$this->file = new cFile();
		$this->file->setLockAccess(true);
		$this->pathToConfig = '/etc/tor';
		if(!$port){
			$this->searchFreePort();
		}
	}

	function __destruct(){
		//$this->stop();
	}

	public function getTorConnection(){
		return $this->host.':'.$this->port;
	}

	private function execCommand($command){
		$output = array();
		$return_val = null;
		exec($command, $output, $return_val);
		return $output ? implode("\n", $output) : $return_val;
	}

	public function start(){
		echo $this->exePath . ' start ' . $this->getPort();
		if($this->createConfig()) {
			$result = $this->execCommand($this->exePath . ' start ' . $this->getPort());
			return $result;
		} else {
			return false;
		}

	}

	public function stop(){
		$result = $this->execCommand($this->exePath.' stop '.$this->getPort());
		$this->file->delete();
		return $result;
	}

	public function stopAll(){
		$result = $this->execCommand('killall tor');
		$this->file->clearDir();
		return $result;
	}

	/*public function flush(){
		return $this->execCommand('killall -HUP '.$this->exePath);
	}*/

	public function restart(){
		$this->stop();
		$this->start();
	}

	public function searchFreePort(){
		do {
			for ($port = rand(self::keyPullStart, self::keyPullEnd); $port < self::keyPullEnd; $port++) {
				if($this->isFreePort($port)) {
					$this->setPort($port);
					return $port;
				}
			}
			echo "Free port not found, wait a minutes\n";
		}while(sleep(60));
		return false;
	}

	public function createConfig(){
		if($this->isFreePort($this->port)){
			if($this->file->open($this->getPortFileName($this->getPort())) && $this->file->lock()){
				$this->file->clear();
				$config = sprintf($this->configPattern, $this->host, $this->getPort());
				return $this->file->write($config);
			}
		}
		return false;
	}

	public function isFreePort($port){
		return !file_exists($this->getPortFileName($port));
	}

	public function getPortFileName($port){
		return $this->pathToConfig.'/'.$port.'.cfg';
	}

} 