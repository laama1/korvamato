<?php

// AUTHOR: Lasse Pihlainen
// CREATED: 22.10.2017
// Edited: 23.10.2017, 20.12.2017
// LICENSE: BSD


class korvamatodb extends SQLite3 {
    protected $dbpath = "korvamadot.db";
    protected $db;
    protected $DEBUG = 0;

    public function __construct($arg = null) {
        if (!file_exists($this->dbpath)) {
            if ($this->createDB()) {
                echo "Database created.<br>";
            } else {
                echo "Error creating database.<br>";
            }
        } else {
            $this->pi("SQLite table exist.");
        }
    }

    # print errors
    private function pe($arg = null) {
        if ($arg === null || $this->DEBUG == 0) return;
        echo "Error: ".$arg . "<br>\n";
    }

    # print info
    private function pi($arg = null) {
        if ($arg === null || $this->DEBUG == 0) return;
        echo "Info: ".$arg ."<br>\n";
    }

    # debug print ARRAY
    private function pa($arg = null) {
        if ($arg == null || $this->DEBUG == 0) return;
        print_r($arg);
        echo "<br>\n";
    }

    # create new database for our project
    private function createDB () {
        $this->pi("Creating Database for Korvamato");

        $sqlString = "CREATE VIRTUAL TABLE korvamadot using FTS4(NICK,PVM,QUOTE,INFO1,INFO2,CHANNEL,ARTIST,TITLE,LINK1,LINK2,DELETED);";
        $ret = 0;
        try {
            $this->db = new PDO("sqlite:$this->dbpath");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $ret = $this->db->exec($sqlString);
        } catch(PDOException $e) {
            $this->pe("PDO exception: ".$e->getMessage());
        } catch(Exception $e) {
            $this->pe("Exception: " .$e->getMessage());
        }
        /*
        if(!$ret){
            print_r($this->db->errorInfo());
        } else {
            echo "Table created successfully\n";
        }
        */
        $this->db = null;
        return $ret;
    }

    # insert line into Database
    public function insertIntoDB($sqlString = null) {
        if ($sqlString === null) return -1;
        $this->pi("insertIntoDB sqlString: " .$sqlString);
        try {
			$this->db = new PDO("sqlite:$this->dbpath");
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
        return -2;
    }

	public function getResultsFromDBQuery($querystr = null) {
		if ($querystr === null) return -1;
		
		$this->db = new PDO("sqlite:$this->dbpath");
		try {
			if ($pdostmt = $this->db->prepare($querystr)) {
				if ($pdostmt->execute()) {
                    $results = $pdostmt->fetchAll();
                    $this->db = null;
					return $results;
				}
			}
		} catch(PDOException $e) {
            $this->pe("getResultsFromDBQuery: ".$e);
		} catch(Exception $e) {
			$this->pe("getResultsFromDBQuery: ".$e);
        }
        $this->db = null;
		return -2;
	}

    public function getResultHandle($query = null) {
        if ($query === null) return -1;
        $this->pi("getResultHandle, query: $query");
        $this->db = new PDO("sqlite:$this->dbpath");
		try {
			if ($pdostmt = $this->db->prepare($query)) {
				if ($pdostmt->execute()) {
                    $this->pi("Result from query:");
                    $this->pa($pdostmt);
                    $this->db = null;
					return $pdostmt;
				}
			}
            $this->pe("Errör.");
		} catch(PDOException $e) {
            $this->pe("getResultHandle PDOException: ".$e);
		} catch(Exception $e) {
			$this->pe("getResultHandle Exception: ".$e);
        }
        $this->db = null;
		return -2;
    }

}


?>
