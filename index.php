<?php
class RESTapiForKorvamato {
// AUTHOR: Lasse Pihlainen
// CREATED: x.y.z
// Edited: 20.12.2017, 27.2.2018, 13.3.2018
// LICENSE: BSD

	protected $db;
	protected $DEBUG = 1;

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
		//$input2 = file_get_contents('php://input');
		if ($this->DEBUG == 3) {
			$this->db->pi("path info: ".$pinf);
			$this->db->pi("method: $method");
			//var_dump($method);
			$this->db->pi("input (json decoded):");
			var_dump($input);
			echo "<br>";
			$this->db->pi("input values print_r:");
			print_r($input);
			echo "<br>";
			echo "<br><br>";
			//$this->db->pi("input2 (not decoded):");
			//var_dump($input2);
			//echo "<br><br>";

			$this->db->pi("request:");
			var_dump($request);
			echo "<br><br>";

			//$this->db->pi("method: $method");
		}
		

		// retrieve the table and key from the path TODO: validate table
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		if ($table == '') {
			$this->db->pe("no table: $table.");
			$this->printNoTable();
			return;
		}
		$key = intval(array_shift($request)+0);
		$this->db->pi("key number: $key, table name: $table");
		
		// escape the columns and values from the input object
		$columns = 0;
		$values = null;
		if ($input) {
			$this->db->pa($input, "input array");
			//$this->pi("array values:");
			//$this->pa(array_values($input));

			$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
			$values = array_values($input);	// TODO: sanitize or bind later
			//$this->db->pa($columns, "columns");
			//$this->db->pa($values, "values");
		}
		
		// build the SET part of the SQL command
		$set = '';
		if ($values) {
			// tested for POST only
			for ($i=0; $i < count($columns); $i++) {
				$set .= ($i>0 ? ', ' : '').'`'.$columns[$i].'` = ';
				//$set .= ($i>0 ? ', ' : '').$columns[$i].' = ';
				// $set.=($values[$i]===null ? 'NULL':'"'.$values[$i].'"');
				//$set.=($values[$i]==null ? "''":'?'); // bind later
				$set.=($values[$i]==null ? "?":'?'); // bind later
			}
			// $set = '('.$set1 . ') VALUES (' . $set2.')';
			$this->db->pi("SQL SET part: $set");
		}
		
		// create SQL based on HTTP method
		switch ($method) {
		case 'GET':
			//$sql = "select rowid,* from `$table`".($key ? " WHERE rowid=$key" : '');
			$sql = "select rowid,* from $table".($key ? " WHERE rowid = $key" : ''). " ORDER BY rowid desc";
			$result = $this->db->getResultHandle($sql);
			$this->db->pa($result, "GET result..");
			$this->print_html_header();

			$this->print_filters();
			$this->print_table_header();
			$this->add_line_row();
			$i = 0;
			$line;

			while ($line = $result->fetch()) {
				//if (!isset($line)) echo "line not set! i: $i ";
				$this->print_table_line($line);
				$i++;
				//$this->db->pi("index: $i");
			} 

			echo "</table>\n";
			$this->print_html_footer();
			break;
		case 'PUT':
			//$sql = "update `$table` set $set where rowid=$key";
			$sql = "update $table set $set where rowid = $key";
			$result = $this->db->insertIntoDB($sql);
			if ($result !== false) return $key;
			//return false;
			break;
		case 'POST':
			$this->db->pa($_POST, "POST (not decoded)");
			//$sql = "insert into `$table` $set";
			$sql = "insert into $table set $set";
			//$this->db->pi("POST SQL: $sql");
			//$result = $this->db->insertIntoDB($sql);
			$result = $this->db->bindSQL($sql, $values);
			if ($result !== false) return $key;
			//return false;
			break;
		case 'DELETE':
			$this->db->pi("DELETE data..");

			$deleted = isset($input['deleted']) ? intval($input['deleted']) : 0;	// 0 = default, not deleted
			//$sql = "update `$table` set deleted = $deleted where rowid = $key";
			$sql2 = "update $table set deleted = ? where rowid = ?";
			$params2 = array($deleted, $key);
			$result = $this->db->bindSQL($sql2, $params2);
			if ($result !== false) return $key;
			//return false;
			break;
		case 'PATCH':
			#$this->pi("PATCH REQUEST");
			$sql = "update $table set $set where rowid = $key";
			$this->db->pi("PATCH!!! $sql");
			$result = $this->db->bindSQL($sql, $values);
			if ($result !== false) return $key;
			//return false;
			break;
		}
		
		// die if SQL statement failed
		if (!$result) {
			http_response_code(404);
			// die(mysqli_error());
			$this->db->pi("No results.");
			die("kaputt");
		} else {
			//$this->db->pa($result, "YES results");
			// print results, insert id or affected row count
			//$this->pi("Row count: ".$result->rowCount());
		}

		$this->db->disco();
	}


	private function print_filters() {
		echo '<a href="hide_deleted">Hide deleted</a> ';
		echo '<a href="hide_oldest">Hide oldest</a>';
	}

	private function print_table_header() {
		echo "<table>";
		echo"<tr><th>ID</th><th>Nick</th><th>Date</th><th>Artist</th><th>Title</th><th>Quote/Lyrics</th>";
		echo "<th>Info1</th><th>Info2</th><th>Link1</th><th>Link2</th>";
		echo "<th>DELETE / UNDELETE</th><th>SAVE changes</th><tr>";
	}

	private function add_line_row($arg = null) {
		echo '<tr id="new_row"><td>(_*_)</td>';
		echo '<td><input id="new_nick" type="text" name="nick" size="15" maxlength="100" value=""></td>';
		echo '<td><p id="new_date"></p></td>';
		echo '<td><input id="new_artist" type="text" name="artist" value=""></td>';
		echo '<td><input id="new_title" type="text" name="title" value=""></td>';
		echo '<td><textarea id="new_quote" name="quote"></textarea></td>';
		echo '<td><textarea id="new_info1" name="info1" class="small"></textarea></td>';
		echo '<td><textarea id="new_info2" name="info2" class="small"></textarea></td>';
		echo '<td><input id="new_url" type="url" name="link1" value=""></td>';
		echo '<td><input id="new_link2" type="url" name="link2" value=""></td>';
		echo '<td>(_*_)</td>';
		echo '<td><input type="button" name="action" id="addnew" value="SAVE" onclick="addNew(\'new\');"></td></tr>';
		//$delvalue = ($arg['DELETED'] == "1") ? "UNDELETE" : "DELETE";
		//echo '<td><a href="'.$rowid.'">'.$delvalue.'</a></td>';
		//echo '<td><input type="submit" name="action" value="DELETE"></td>';
		//echo '<td><input type="button" name="method" id="delete'.$rowid.'" value="'.$delvalue.'" onclick="deleteitem('.$rowid.',\''.$delvalue.'\');"></td>';
		//echo '<td><input type="button" name="action" id="update'.$rowid.'" value="UPDATE" onclick="parseForm('.$rowid.');"></td></tr>';
		//echo "\n</fieldset>";
		echo "\n";
	}

	private function print_table_line($arg = null) {
		if ($arg === null) return;
		$rowid = $arg['rowid'];
		echo "\n";
		//echo '<fieldset name="id_'.$rowid.'">'."\n";
		echo '<tr id="'.$rowid.'_row"><td>' . $rowid ."</td>";
		echo '<td><p id="'.$rowid.'_nick">' . $arg['NICK'] ."</p></td>";
		echo '<td><p id="'.$rowid.'_date">' . date('j.m.Y H:i:s', $arg['PVM']) ."</p></td>";
		echo '<td><input id="'.$rowid.'_artist" type="text" name="artist" value="'.$arg['ARTIST'].'"></td>';
		echo '<td><input id="'.$rowid.'_title" type="text" name="title" value="'.$arg['TITLE'].'"></td>';
		echo '<td><textarea id="'.$rowid.'_quote" name="quote">' . $arg['QUOTE'] ."</textarea></td>";
		echo '<td><textarea id="'.$rowid.'_info1" name="info1" class="small">' . $arg['INFO1'] ."</textarea></td>";
		echo '<td><textarea id="'.$rowid.'_info2" name="info2" class="small">' . $arg['INFO2'] ."</textarea></td>";
		echo '<td><input id="'.$rowid.'_url" type="url" name="link1" value="' . $arg['LINK1'] .'"></td>';
		echo '<td><input id="'.$rowid.'_link2" type="url" name="link2" value="' . $arg['LINK2'] .'"></td>';
		$delvalue = ($arg['DELETED'] == "1") ? "UNDELETE" : "DELETE";
		//echo '<td><a href="'.$rowid.'">'.$delvalue.'</a></td>';
		//echo '<td><input type="submit" name="action" value="DELETE"></td>';
		echo '<td><input type="button" name="method" id="delete'.$rowid.'" value="'.$delvalue.'" onclick="deleteItem2('.$rowid.',\''.$delvalue.'\');"></td>';
		echo '<td><input type="button" name="action" id="update'.$rowid.'" value="UPDATE" onclick="parseForm('.$rowid.');"></td></tr>';
		//echo "\n</fieldset>";
		echo "\n";
	}

	private function print_html_header() {
		echo "<!DOCTYPE html>\n<head>\n\t<title>Korvamadot</title>\n\t";
		//echo '<link rel="stylesheet" type="text/css" href="http://lamanzi.vhosti.fi/korvamato/styles.css">'."\n\t";
		echo '<link rel="stylesheet" type="text/css" href="styles.css">'."\n\t";
		echo '<script type="text/javascript" charset="utf-8" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>'."\n\t";
		//echo '<script type="text/javascript" charset="utf-8" src="http://lamanzi.vhosti.fi/korvamato/scripts.js"></script>'."\n";
		echo '<script type="text/javascript" charset="utf-8" src="scripts_new.js"></script>'."\n";
		echo "</head>\n<body>\n";
		echo '<p id="debug1"></p>';
		echo '<p id="debug2"></p>';
	}

	private function printNoTable() {
		$this->print_html_header();
		echo '<p id="debug1">No table defined.</p>';
		echo '<p id="debug2">Try adding "/korvamadot" at the end of url.</p>';
		$this->print_html_footer();
	}

	private function print_html_footer() {
		echo "\n</body>\n</html>";
	}
}
$testing = new RESTapiforKorvamato;
?>
