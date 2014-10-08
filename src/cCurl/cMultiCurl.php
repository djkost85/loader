<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 12.01.14
 * Time: 17:07
 * Project: get_content
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;


class cMultiCurl extends cCurl{

	public $descriptorArray;
	protected $answerInfo;

	private $countDescriptor;
	private $countStream = 1;
	private $countCurl = 1;
	private $waitExecMSec = 100000;

	public function &getDescriptorArray() {
		return $this->descriptorArray;
	}

	public function setCountCurl($value = 1) {
		if ($this->getCountCurl() != $value) {
			$this->countCurl = $value;
			$this->setCountDescriptor();
			$this->reInit();
		}
	}

	public function getCountCurl() {
		return $this->countCurl;
	}

	public function setCountStream($value = 1) {
		if ($this->getCountStream() != $value) {
			$this->countStream = $value;
			$this->setCountDescriptor();
			$this->reInit();
		}
	}

	public function getCountStream() {
		return $this->countStream;
	}

	private function setCountDescriptor() {
		$this->countDescriptor = $this->getCountCurl() * $this->getCountStream();
	}

	private function getCountDescriptor() {
		return $this->countDescriptor;
	}

	/**
	 * @param int $waitExecMSec
	 */
	public function setWaitExecMSec($waitExecMSec) {
		$this->waitExecMSec = $waitExecMSec;
	}

	/**
	 * @return int
	 */
	public function getWaitExecMSec() {
		return $this->waitExecMSec;
	}



	function __construct(){
		$this->defaultOptions[CURLOPT_FOLLOWLOCATION] = true;
		$this->setDefaultOption(CURLOPT_FOLLOWLOCATION, true);
		$this->setDefaultOption(CURLOPT_MAXREDIRS, 10);
		$this->setCountDescriptor();
		parent::__construct();
	}

	public function init(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		$descriptor['descriptor'] = curl_multi_init();
		if (is_array($descriptorArray) && count($descriptorArray) > $this->getCountDescriptor()) {
			$descriptorArray = array_slice($descriptorArray, 0, $this->getCountDescriptor());
		}
		for ($i = 0; $i < $this->getCountDescriptor(); $i++) {
			if (!isset($descriptorArray[$i]['descriptor_key'])) {
				$descriptorArray[$i]['descriptor_key'] = $this->genDescriptorKey();
			}
			$descriptorArray[$i]['descriptor'] = curl_init();
			$this->addDescriptors($descriptor, $descriptorArray[$i]);
		}
	}

	protected function addDescriptors(&$descriptor, &$descriptorCurl){
		if(is_resource($descriptor['descriptor']) && is_resource($descriptorCurl['descriptor'])){
			return curl_multi_add_handle($descriptor['descriptor'], $descriptorCurl['descriptor']);
		}
		return false;
	}

	protected function exec(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		$running = null;
		do {
			curl_multi_exec($descriptor['descriptor'], $running);
			usleep($this->waitExecMSec);
		} while ($running > 0);
		$answer = array();
		foreach ($descriptorArray as $key => $value){
			$answer[$key] = curl_multi_getcontent($descriptorArray[$key]['descriptor']);
		}
		return $answer;
	}

	public function close(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		foreach ($descriptorArray as $key => &$descriptorCurl) {
			if (isset($descriptorCurl['descriptor'])) {
				if(is_resource($descriptorCurl['descriptor'])){
					$this->removeDescriptors($descriptor, $descriptorCurl);
					curl_close($descriptorCurl['descriptor']);
				}
				unset($descriptorCurl['descriptor']);
				$this->saveOption($descriptorCurl);
			}
		}
		if (isset($descriptor['descriptor']) && is_resource($descriptor['descriptor'])) {
			curl_multi_close($descriptor['descriptor']);
		}
	}

	protected function removeDescriptors(&$descriptor, &$descriptorCurl){
		if(is_resource($descriptor['descriptor']) && is_resource($descriptorCurl['descriptor'])){
			return curl_multi_remove_handle($descriptor['descriptor'], $descriptorCurl['descriptor']);
		}
		return false;
	}

	protected function resetDescriptors(){
		$descriptor =& $this->getDescriptor();
		$descriptorArray =& $this->getDescriptorArray();
		foreach ($descriptorArray as &$descriptorCurl) {
			$this->removeDescriptors($descriptor, $descriptorCurl);
			$this->addDescriptors($descriptor, $descriptorCurl);
			$this->saveOption($descriptorCurl);
		}
	}

	public function genNewKeyStream(){
		$descriptorArray =& $this->getDescriptorArray();
		foreach ($descriptorArray as &$subDescriptor) {
			$subDescriptor['descriptor_key'] = $this->genDescriptorKey();
		}
	}

	public function load($url = array()){
		$goodAnswer = array();
		$countMultiStream = $this->getCountStream();
		$this->setCountCurl(count($url));
		$descriptorArray =& $this->getDescriptorArray();
		$j = 0;
		$urlDescriptorsLink = array();
		foreach ($url as $keyUrl => $valueUrl) {
			for ($i = 0; $i < $countMultiStream; $i++) {
				$urlDescriptorsLink[$keyUrl][] = $j;
				if (isset($descriptorArray[$j]['descriptor'])) {
					$this->setOption($descriptorArray[$j], CURLOPT_URL, $valueUrl);
				}
				$j++;
			}
		}
		foreach ($descriptorArray as &$value){
			$this->setOptions($value);
		}
		$this->resetDescriptors();
		$answer = $this->exec();
		foreach ($answer as $key => &$value) {
			$descriptorArray[$key]['info'] = curl_getinfo($descriptorArray[$key]['descriptor']);
			$descriptorArray[$key]['info']['error'] = curl_error($descriptorArray[$key]['descriptor']);
			$descriptorArray[$key]['info']['header'] = cHeaderHTTP::cutHeader($value);
			$linkKey = $this->getLinkKey($urlDescriptorsLink, $key);
			$goodAnswer[$linkKey][$key] = $value;
		}
		$this->setAnswer($goodAnswer);
		return $this->getAnswer();
	}

	private function getLinkKey($links, $key){
		foreach($links as $linkKey => $linkValue){
			if(in_array($key,$linkValue)){
				return $linkKey;
			}
		}
		return false;
	}

	public function getAnswer($getAllAnswer = false){
		if (!$getAllAnswer) {
			$a = array();
			$descriptorArray = $this->getDescriptorArray();
			foreach ($this->answer as $key => $value) {
				$a[$key] = cStringWork::getBiggestString($value, $descriptorKey);
				$this->answerInfo[$key] = $descriptorArray[$descriptorKey]['info'];
			}
			return $a;
		} else{
			return $this->answer;
		}
	}

	public function getInfo($key){
		return $this->answerInfo[$key];
	}

}