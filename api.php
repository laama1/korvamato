<?php
class RESTapiForKorvamato {
// AUTHOR: Lasse Pihlainen
// CREATED: x.y.z
// Edited: 20.12.2017, 27.2.2018, 13.3.2018
// LICENSE: BSD

	protected $db;
	protected $DEBUG = 0;

	public function __construct() {
		require_once('db.php');
        $this->db = new korvamatodb;
		$this->getMethod();
	}
	
	private function getMethod() {

		// get the HTTP method, path and body of the request
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;
		$pinf = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : false;
		$request = explode('/', trim($pinf, '/'));
		$input = json_decode(file_get_contents('php://input'),true);
		$result = false;

		if ($this->DEBUG == 1) {
			//$this->db->pi("path info: ".$pinf);
			$this->db->pi("method: $method");
			//var_dump($method);
			$this->db->pi("input (json decoded):");
			var_dump($input);
			echo "<br>";

			$this->db->pi("request:");
			var_dump($request);
			echo "<br><br>";
		}
		

		// retrieve the table and key from the path TODO: validate table
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		if ($table == '') {
			$this->db->pe("no table given.");
			$this->printNoTable();
			return;
		}
		if ($this->db->setDB($table) == false) {
			echo json_encode(array("DIED"));
			die();
		}
		$key = array_shift($request);
		//$this->db->pi("key number: $key, table name: $table");
		
		// escape the columns and values from the input object
		$columns = 0;
		$values = null;
		if ($input) {
			$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
			$values = array_values($input);	// TODO: sanitize or bind later
		}
		
		// build the SET part of the SQL command
		$seto = '';
		$set1 = '';
		$set2 = '';
		$sett = '';
		if ($values) {
			for ($i=0; $i < count($columns); $i++) {
				$seto .= ($i>0 ? ', ' : '').'`'.$columns[$i].'` = ';
				$set1 .= ($i>0 ? ', ' : '').$columns[$i];
				//$set2 .=($values[$i]===null ? 'NULL':'"'.$values[$i].'"');
				$set2 .=($i>0 ? ', ' : '').($values[$i]===null ? '?':'?');
				$seto.=($values[$i]==null ? "''":'?'); // bind later
				$seto.=($values[$i]==null ? "?":'?'); // bind later
			}
			$sett = '('.$set1.') VALUES ('.$set2.')';
			$this->db->pi("SQL SET part: $sett");
			//$this->db->pi("SQL SETO part: $seto");
		}
		
		// create SQL based on HTTP method
		switch ($method) {
		case 'GET':
			// Get all from DB if $key not set
			$sql = "select rowid,* from $table".($key ? " WHERE rowid = $key" : ''). " ORDER BY rowid desc LIMIT 100;";
			if ($key == 'latest') {
				$sql = "select rowid,* from $table order by rowid desc limit 1";
			}
			$result = $this->db->getResultsFromDBQuery($sql);
			$this->db->pa($result, "GET result..");
			echo json_encode($result);
			return;
			break;
		case 'PUT':
		case 'PATCH':
			$sql = "update $table set $seto where rowid = $key";
			$result = $this->db->bindSQL($sql, $values);
			break;
		case 'POST':
			$this->db->pa($_POST, "POST (not decoded)");
			$sql = "insert into $table $sett";
			$result = $this->db->bindSQL($sql, $values);
			break;
		case 'DELETE':
			$this->db->pi("DELETE data..");

			$deleted = isset($input['deleted']) ? intval($input['deleted']) : 0;	// 0 = default, not deleted
			$sql2 = "update $table set deleted = ? where rowid = ?";
			$params2 = array($deleted, $key);
			$result = $this->db->bindSQL($sql2, $params2);
			break;
		default:
			echo json_encode(array("nutinbits"));
			return false;
		}
		$this->db->disco();

		// die if SQL statement failed
		if (!$result) {
			http_response_code(404);
			$this->db->pi("No results.");
			die(json_encode(array("kaputt")));
		} else {
			echo json_encode(array($key));
		}
		http_response_code(200);
		
	}

}
$testing = new RESTapiforKorvamato;
?>