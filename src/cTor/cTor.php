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
	private $ipCountries = array();
	private $geoIpFile = '/usr/share/tor/geoip';
	const dataDirectory = '/etc/tor';
	private $pathToConfig;
	private $host = '127.0.0.1';
	private $port = '9050';
	private $config;
	private $configPattern = 'SocksListenAddress %s
SocksPort %d
PidFile %s/tor%d.pid
RunAsDaemon 1
DataDirectory %s/tor%d
ControlPort %d
ORPort %d
ORListenAddress %s:%d
Nickname tor%d
DirPort %d
DirListenAddress %s:%d';
	private $geoIpPattern = 'ExcludeNodes {%s}
GeoIPFile %s';
	const keyPullStart = 20000;
	const keyPullEnd = 29999;

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
	 * @return string|integer
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
			$this->file->open($this->getPortFileName($this->getPort()));
		}
	}

	public function getControlPort(){
		return $this->getPort() + 10000;
	}

	public function getORPort(){
		return $this->getPort() + 20000;
	}

	public function getDirPort(){
		return $this->getPort() + 30000;
	}

	/**
	 * @param array $ipCountries
	 */
	public function setIpCountries($ipCountries) {
		$this->ipCountries = is_array($ipCountries)?$ipCountries:array($ipCountries);
	}

	/**
	 * @return string
	 */
	public function getConfig() {
		return $this->config;
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
		$this->stop();
		$this->file->delDir($this->pathToConfig.'/tor'.$this->getPort());
		$this->file->delete();
	}

	public function getTorConnection(){
		return $this->host.':'.$this->port;
	}

	private function execCommand($command){
		echo $command . "\n";
		$output = array();
		$return_val = null;
		exec($command, $output, $return_val);
		$result = $output ? implode("\n", $output) : $return_val;
		return $result;
	}

	public function start(){
		if($this->createConfig()) {
			$result = $this->execCommand($this->exePath . ' start tor' . $this->getPort());
			return $result;
		} else {
			return false;
		}

	}

	public function stop(){
		for($i = 0; $i < 10; $i++) {
			$result = $this->execCommand($this->exePath . ' stop tor' . $this->getPort());
			if(!$this->isExist()){
				break;
			}
			usleep(100000);
		}
		return $result;
	}

	public function stopAll(){
		$result = $this->execCommand('killall tor');
		return $result;
	}

	/*public function flush(){
		return $this->execCommand('killall -HUP '.$this->exePath);
	}*/

	public function restart(){
		$this->stop();
		$this->start();
	}

	public function status(){
		$result = $this->execCommand($this->exePath.' status tor'.$this->getPort());
		return $result;
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
			if($this->file->lock()){
				$this->file->clear();
				$this->config = sprintf(
					$this->configPattern,
					$this->host,
					$this->getPort(),
					self::dataDirectory,$this->getPort(),
					self::dataDirectory,$this->getPort(),
					$this->getControlPort(),
					$this->getORPort(),
					$this->host,$this->getORPort(),
					$this->getPort(),
					$this->getDirPort(),
					$this->host,$this->getDirPort()
				);
				if($this->ipCountries){
					$this->config .= "\n" . sprintf(
							$this->geoIpPattern,
							implode('},{',$this->ipCountries),
							$this->geoIpFile
						);
				}
				return $this->file->write($this->config);
			}
		}
		return false;
	}

	public function isFreePort($port){
		return !file_exists($this->getPortFileName($port)) || $this->getPortFileName($port) == $this->file->getName();
	}

	public function isExist(){
		return preg_match('%is running%', $this->status());
	}

	public function getPortFileName($port){
		return $this->pathToConfig.'/tor'.$port.'.cfg';
	}

} 