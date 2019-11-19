<?php
require_once('db.php');
class RESTapiForKorvamato {
// AUTHOR: Lasse Pihlainen
// CREATED: x.y.z
// Edited: 20.12.2017, 27.2.2018, 13.3.2018
// LICENSE: BSD
// Requirements: php-json (debian, fedora)

	protected $db;
	protected $DEBUG = 0;
	private $tablename = '';

	public function __construct() {
		
		if ($this->DEBUG) {
			http_response_code(500);
		}

        $this->db = new apidb();
		$this->getMethod();
		//$this->printTable();
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
			$this->db->pi(__FUNCTION__.':'.__LINE__.": method: $method");
			$this->db->pa($input, __FUNCTION__.':'.__LINE__.": input (json decoded)");
			echo "<br>";

			$this->db->pa($request, __FUNCTION__.':'.__LINE__.": request");
			echo "<br><br>";
		}
		

		// retrieve the table and key from the path TODO: validate table
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		if ($table == '') {
			$this->db->pe(__FUNCTION__.':'.__LINE__.": no table given.");
			http_response_code(500);
			$this->printNoTable();
			return;
		}
		$this->tablename = $table;
		if ($this->db->setDB($table) == false) {
			http_response_code(500);
			echo json_encode(array("DIED when setting DB"));
			die();
		}
		$key = array_shift($request);
		//$this->db->pi("key number: $key, table name: $table");

		// escape the columns and values from the input object
		$columns = 0;
		$values = null;
		if ($input) {
			//$this->db->pa($input, "input array");
			//$this->pi("array values:");
			//$this->pa(array_values($input));

			$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
			$values = array_values($input);	// TODO: sanitize or bind later
			//$this->db->pa($columns, "columns");
			//$this->db->pa($values, "values");
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
				//$seto.=($values[$i]==null ? "''":'?'); // bind later
				$seto.=($values[$i]==null ? "?":'?'); // bind later
			}
			$sett = '('.$set1.') VALUES ('.$set2.')';
			$this->db->pi(__FUNCTION__.':'.__LINE__.": SQL SET part: $sett");
			$this->db->pi(__FUNCTION__.':'.__LINE__.": SQL SETO part: $seto");
		}
		
		// create SQL based on HTTP method
		switch ($method) {
		case 'GET':
			if (!isset($key)) {
				//$this->printTable();
				//break;
			}
			// Get all from DB if $key not set
			//$sql = "select rowid,* from $table".($key ? " WHERE rowid = $key" : ''). " ORDER BY rowid asc LIMIT 100;";
			$sql = "select rowid,* from $table".($key ? " WHERE rowid = $key" : ''). " ORDER BY rowid asc;";
			if ($key == 'latest') {
				$sql = "select rowid,* from $table order by rowid desc limit 1";
			}
			$result = $this->db->getResultsFromDBQuery($sql);
			//$this->db->pa($result, __FUNCTION__.':'.__LINE__.": GET result..");
			echo json_encode($result);
			return;
			break;
		case 'PUT':
		case 'PATCH':
			//$sql = "update `$table` set $set where rowid=$key";
			$sql = "update $table set $seto where rowid = $key";
			$result = $this->db->bindSQL($sql, $values);
			if ($result !== false) {
				echo json_encode(array('rowid' => $key));
				return $key;
			}
			break;
		case 'POST':
			$this->db->pa($_POST, __FUNCTION__.':'.__LINE__.": POST (not decoded)");
			//$sql = "insert into `$table` $set";
			//$sql = "insert into $table $sett";
			$sql = "insert into $table $sett; SELECT distinct rowid from korvamadot order by rowid desc;";
			//$this->db->pi("POST SQL: $sql");
			$result = $this->db->bindSQL($sql);
			//$result = $this->db->bindSQL($sql, $values);
			if ($result !== false) {
				echo json_encode(array('rowid' => $result));
				return $result;
			}
			break;
		case 'DELETE':
			$this->db->pi(__FUNCTION__.':'.__LINE__.": DELETE data from table: $table, rowid: $key");

			$deleted = isset($input['deleted']) ? intval($input['deleted']) : 0;	// 0 = default, not deleted
			//$sql = "update `$table` set deleted = $deleted where rowid = $key";
			$sql2 = "update $table set deleted = ? where rowid = ?";
			$params2 = array($deleted, $key);
			$result = $this->db->bindSQL($sql2, $params2);
			if ($result !== false) {
				$this->db->pi(__FUNCTION__.':'.__LINE__.": dodi, coolness!");
				#echo json_encode(array($key));
				echo json_encode(array('rowid' => $key, 'value' => $deleted));
				return json_encode(array('rowid' => $key, 'value' => $deleted));
			}
			break;
		case 'PATCH':
			$this->pi('PATCH REQUEST');

			break;
		default:
			http_response_code(404);
			echo json_encode(array("nutinbits"));
			return false;
		}
		$this->db->disco();

		// die if SQL statement failed
		if (!$result) {
			http_response_code(500);
			$this->db->pi("No results.");
			//die(json_encode(array("kaputt")));
			return(json_encode(array("kaputt")));
		} else {
			echo json_encode(array($key));
		}
		http_response_code(200);
		
	}

	private function print_html_header() {
		echo "<!DOCTYPE html>\n<head>\n\t<title>What's here?</title>\n\t";
		echo '<link rel="stylesheet" type="text/css" href="styles.css">'."\n\t";
		//echo '<script type="text/javascript" charset="utf-8" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>'."\n\t";
		//echo '<script type="text/javascript" charset="utf-8" src="http://lamanzi.vhosti.fi/korvamato/scripts.js"></script>'."\n";
		//echo '<script type="text/javascript" charset="utf-8" src="scripts_new.js"></script>'."\n";
		echo "</head>\n<body>\n";
		echo '<p id="debug1"></p>';
		echo '<p id="debug2"></p>';
	}

	private function printNoTable() {
		$this->print_html_header();
		echo '<p id="debug1">No table defined.</p>';
		echo '<p id="debug2">Try adding "/korvamadot" at the end of url.</p>';
		$this->print_html_footer();
		exit;
	}
	private function printTable() {
		//include dirname(__FILE__).'/'.$this->tablename . '.html';
		//include dirname(__FILE__).'/korvamadot.html';
	}

	private function print_html_footer() {
		echo "\n</body>\n</html>";
	}
}
$testing = new RESTapiforKorvamato;
?>
