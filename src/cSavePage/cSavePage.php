<?php
/**
 * Created by PhpStorm.
 * User: EC
 * Date: 01.10.2014
 * Time: 9:17
 * Project: parser_ge
 * @author: Evgeny Pynykh bpteam22@gmail.com
 */

namespace GetContent;

class cSavePage {

	/**
	 * @var \mysqli
	 */
	private $dbConnect;
	private $dbHost = 'localhost';
	private $dbName = 'save_page';
	private $dbUser = 'Z';
	private $dbPassword = '123456';

	private $tablePrefix;
	private $session;

	/**
	 * @return mixed
	 */
	public function getTablePrefix() {
		return $this->tablePrefix;
	}

	/**
	 * @param mixed $tablePrefix
	 */
	public function setTablePrefix($tablePrefix) {
		$this->tablePrefix = $tablePrefix;
	}

	/**
	 * @return mixed
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * @param mixed $session
	 */
	public function setSession($session) {
		$this->session = $session;
	}



	function __construct($tablePrefix){
		$this->setTablePrefix($tablePrefix);
		$this->initTable();
		$this->setSession(time());
	}

	public function initTable(){
		$query = "CREATE TABLE IF NOT EXISTS `{$this->dbName}`.`sp_{$this->tablePrefix}` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session` VARCHAR(45) NULL,
  `parameter` VARCHAR(255) NULL,
  `url` VARCHAR(255) NULL,
  `options` LONGTEXT NULL,
  `page` LONGTEXT NULL,
  `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
  INDEX `page` (`parameter` ASC, `url` ASC, `session` ASC))
  ENGINE = InnoDB;
";
		$this->query($query);
	}

	private function query($query) {
		$this->connect();
		return $this->dbConnect->query($query);
	}

	private function connect() {
		if(!is_object($this->dbConnect)){
			$this->dbConnect = new \mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
			if ($this->dbConnect->connect_error) {
				echo "Не удалось подключиться к БД.(" . $this->dbConnect->connect_errno . ')' . $this->dbConnect->connect_error;
				return false;
			}
			$this->dbConnect->set_charset("utf8");
		}
		return true;
	}

	private function escape($string){
		$this->connect();
		return $this->dbConnect->real_escape_string($string);
	}

	public function save($parameter, $url, $page, $options = ''){

		$query = sprintf(
			'INSERT INTO `%s`.`sp_%s` (`session`, `parameter`, `url`, `options`, `page`) VALUE (\'%s\', \'%s\', \'%s\', \'%s\', \'%s\')',
			$this->dbName,
			$this->tablePrefix,
			$this->escape($this->session),
			$this->escape($parameter),
			$this->escape($url),
			$this->escape($options),
			$this->escape($page)
		);
		return $this->query($query);

	}

	public function getPage($parameter, $url, $session = false){
		$session = $session ? $session : $this->getLastSession($parameter, $url);
		$query = sprintf(
			"SELECT page FROM `%s`.`sp_%s` WHERE `parameter` = '%s' AND `url` = '%s' AND `session` = '%s' LIMIT 1",
			$this->dbName,
			$this->tablePrefix,
			$this->escape($parameter),
			$this->escape($url),
			$this->escape($session)
		);
		$result = $this->query($query);
		if($result){
			$data = $result->fetch_assoc();
			$result->free();
			return $data['page'];
		} else {
			return false;
		}
	}

	public function getLastSession($parameter = false, $url = false){
		$where = array();
		if($parameter){
			$where[] = sprintf("`parameter` = '%s'", $this->escape($parameter));
		}
		if($url){
			$where[] = sprintf("`url` = '%s'", $this->escape($url));
		}
		$query = sprintf(
			"SELECT MAX(session) as last_session FROM `%s`.`sp_%s` %s",
			$this->dbName,
			$this->tablePrefix,
			$where ? 'WHERE ' . implode( ' AND ', $where) : ''
			);
		$result = $this->query($query);
		$data = $result->fetch_assoc();
		$result->free();
		return $data['last_session'];
	}

	public function getAllSessions($url = ''){
		$where = $url? "WHERE url = '$url'" : '';
		$query = sprintf(
			"SELECT count(session) AS count_row, session, timestamp FROM `%s`.`sp_%s` %s GROUP BY session",
			$this->dbName,
			$this->tablePrefix,
			$where
		);
		return $this->query($query);
	}
} 