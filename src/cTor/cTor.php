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
	private $ipCountries = array(); //https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	private $geoIpFile = '/usr/share/tor/geoip';
	const DATA_DIRECTORY = '/etc/tor';
	private $pathToConfig = '/etc/tor';
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
	private $geoIpPattern = 'ExitNodes {%s}';
	const KEY_PULL_START = 20000;
	const KEY_PULL_END = 29999;
	private $maxRepeatExecute = 25;
	private $sleepOnExecute = 200000;

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
		if(preg_match('%^\d+$%',$port) && $port >= self::KEY_PULL_START && $port <= self::KEY_PULL_END && $this->isFreePort($port)){
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

	/**
	 * @return int
	 */
	public function getMaxRepeatExecute() {
		return $this->maxRepeatExecute;
	}

	/**
	 * @param int $maxRepeatExecute
	 */
	public function setMaxRepeatExecute($maxRepeatExecute) {
		$this->maxRepeatExecute = $maxRepeatExecute;
	}

	/**
	 * @return int
	 */
	public function getSleepOnExecute() {
		return $this->sleepOnExecute;
	}

	/**
	 * @param int $sleepOnExecute
	 */
	public function setSleepOnExecute($sleepOnExecute) {
		$this->sleepOnExecute = $sleepOnExecute;
	}

	function __construct($port = false){
		$this->file = new cFile();
		$this->file->setLockAccess(true);
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
		//echo $command . "\n";
		$output = array();
		$return_val = null;
		exec($command, $output, $return_val);
		$result = $output ? implode("\n", $output) : $return_val;
		return $result;
	}

	public function start(){
		$result = false;
		if($this->createConfig()) {
			$result = $this->waitExecComplete($this->exePath . ' start tor' . $this->getPort(), true);
		}
		return $result;
	}

	public function stop(){
		return $this->waitExecComplete($this->exePath . ' stop tor' . $this->getPort(), false);
	}

	protected function waitExecComplete($cmd, $need = true){
		$result = false;
		for($i = 0; $i < $this->getMaxRepeatExecute(); $i++) {
			$result = $this->execCommand($cmd);
			if($this->isExist() === $need){
				break;
			}
			usleep($this->getSleepOnExecute());
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
		$result['stop'] = $this->stop();
		$result['start'] = $this->start();
		return $result;
	}

	public function status(){
		$result = $this->execCommand($this->exePath.' status tor'.$this->getPort());
		return $result;
	}

	public function searchFreePort(){
		do {
			for ($port = rand(self::KEY_PULL_START, self::KEY_PULL_END); $port < self::KEY_PULL_END; $port++) {
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
					self::DATA_DIRECTORY,$this->getPort(),
					self::DATA_DIRECTORY,$this->getPort(),
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
							implode('},{',$this->ipCountries)
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
		return (bool)preg_match('%is running%', $this->status());
	}

	public function getPortFileName($port){
		return $this->pathToConfig.'/tor'.$port.'.cfg';
	}

} 