<?php

// AUTHOR: Lasse Pihlainen
// CREATED: 22.10.2017
// Edited: 23.10.2017, 20.12.2017, 27.2.2018
// LICENSE: BSD


class korvamatodb /*extends SQLite3*/ {
	//protected $dbpath = '../../.irssi/scripts/korvamadot.db';
	protected $dbpath;
	protected $db;
	protected $DEBUG = 1;
	protected $date;

	public function __construct($arg = null) {
		$this->dbpath = dirname(__FILE__).'/korvamadot.db';
		if (!file_exists($this->dbpath)) {
			if ($this->createDB()) {
				echo "Database created.<br>";
			} else {
				echo "Error creating database.<br>";
			}
		} else {
			//$this->pi("SQLite table exist.");
		}
		$this->db = new PDO("sqlite:$this->dbpath");
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->date = date("H:i:s");
	}

	# print errors
	public function pe($arg = null) {
		if ($arg == null || $this->DEBUG != 1) return;
		echo "[ERROR $this->date] ".$arg . "<br><br>\n";
	}

	# print info
	public function pi($arg = null) {
		if ($arg == null || $this->DEBUG != 1) return;
		echo "[INFO $this->date] ".$arg ."<br><br>\n";
	}

	# debug print ARRAY
	public function pa($arg = null, $title = false) {
		if ($arg == null || $this->DEBUG != 1) return;
		echo "[ARRAY $this->date $title] ";
		print_r($arg);
		echo "<br><br>\n";
	}

	# create new database for our project
	private function createDB () {
		$this->pi("Creating Database for Korvamato");

		$sqlString = "CREATE VIRTUAL TABLE korvamadot using FTS4(NICK,PVM,QUOTE,INFO1,INFO2,CHANNEL,ARTIST,TITLE,LINK1,LINK2,DELETED);";
		$ret = 0;
		try {
			$ret = $this->db->exec($sqlString);
		} catch(PDOException $e) {
			$this->pe("PDO exception: ".$e->getMessage());
		} catch(Exception $e) {
			$this->pe("Exception: " .$e->getMessage());
		}
		return $ret;
	}

	public function disco() {
		$this->db = null;
	}
	# insert line into Database
	public function insertIntoDB($sqlString = null) {
		if ($sqlString === null) return false;
		$this->pi("insertIntoDB sqlString: " .$sqlString);
		try {
			//$this->db = new PDO("sqlite:$this->dbpath");
			if ($pdostmt = $this->db->prepare($sqlString)) {
				if ($pdostmt->execute()) {
					$this->db = null;
					return true;
				} else {
					$this->pe("insertIntoDB ERROR.. $sqlString");
					#$this->pa($pdostmt);
				}
			} else {
				$this->pe("insertIntoDB prepare statement error.");//: ".$pdostmt->errorInfo);
			}
		} catch(PDOException $e) {
			$this->pe("insertIntoDB PDOException: ".$e);
		} catch(EXCeption $e) {
			$this->pe("insertIntoDB Exception: ".$e);
		}
		$this->db = null;
		return false;
	}

	public function getResultsFromDBQuery($querystr = null) {
		if ($querystr === null) return -1;
		
		//$this->db = new PDO("sqlite:$this->dbpath");
		try {
			if ($pdostmt = $this->db->prepare($querystr)) {
				if ($pdostmt->execute()) {
					$results = $pdostmt->fetchAll();
					$this->db = null;
					return $results;
				}
			}
			$this->pe("Prepare didn't match!");
		} catch(PDOException $e) {
			$this->pe("getResultsFromDBQuery: ".$e);
		} catch(Exception $e) {
			$this->pe("getResultsFromDBQuery: ".$e);
		}
		$this->db = null;
		return -2;
	}

	public function bindSQL($query = false, $params = false) {
		if ($query == false || $params == false) return false;
		$this->pi("bindSQL query: $query");
		//$this->pa($this->db, "bindSQL this->db");
		$this->pa($params, "bindSQL params");
		try {
			if ($pdostmt = $this->db->prepare($query)) {
				//$this->pa($pdostmt, "bindSQL pdostmt");
				if ($result = $pdostmt->execute($params)) {
					//$this->pa($pdostmt, "bindSQL Result from query");
					return $result;
				}
				$this->pe("bindSQL DB execute erroor.");
				return false;
			}
			$this->pe("bindSQL DB prepare Erröör.");
			return false;
		} catch(PDOException $e) {
			$this->pe("bindSQL PDOException: ".$e);
		} catch(Exception $e) {
			$this->pe("bindSQL Exception: ".$e);
		}
		$this->pe("bindSQL failed..");
		return false;
	}

	public function getResultHandle($query = null) {
		if ($query === null) return false;
		$this->pi("getResultHandle, SQL query: $query");
		//$this->db = new PDO("sqlite:$this->dbpath");
		//$this->pa($this->db, "getResultHandle this->db");
		try {
			if ($pdostmt = $this->db->prepare($query)) {
				if ($line = $pdostmt->execute()) {
					//$this->pa($pdostmt, "getResultHandle Result from query, lines: $line");
					//$this->db = null;
					return $pdostmt;
				}
			}
			$this->pe("getResultHandle DB prepare Errör.");
		} catch(PDOException $e) {
			$this->pe("getResultHandle PDOException: ".$e);
		} catch(Exception $e) {
			$this->pe("getResultHandle Exception: ".$e);
		}
		$this->pe("getResultHandle error..");
		//$this->db = null;
		return false;
	}

}
?>