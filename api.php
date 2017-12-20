<?php
class RESTapiForKorvamato {
// AUTHOR: Lasse Pihlainen
// CREATED: x.y.z
// Edited: 20.12.2017
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
		$method = $_SERVER['REQUEST_METHOD'];
		$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
		$input = json_decode(file_get_contents('php://input'),true);
		$input2 = json_decode(file_get_contents('php://input'),true);

		$this->pi("server path info:");
		$this->pi($_SERVER['PATH_INFO']);


		$this->pi("input (json decoded):");
		$this->pa($input);
		$this->pi("input2 (json decoded):");
		$this->pa($input2);

		$this->pi("request:");
		$this->pa(json_encode($request));
		$this->pi("POST json encoded:");
		$this->pa(json_encode($_POST) . "<br>\n");
		$this->pi("method: $method");

	
		// retrieve the table and key from the path
		$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
		$key = array_shift($request)+0;
		$this->pi("key: $key, table: $table");
		// escape the columns and values from the input object
		$columns = 0;
		$values = null;
		if ($input) {
			$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
			$values = array_map(function ($value) use ($link) {
				if ($value===null) return null;
				return sqlite_escape_string($link,(string)$value);
			}, array_values($input));
		}
		
		// build the SET part of the SQL command
		$set = '';
		if ($values) {
			for ($i=0; $i < count($columns); $i++) {
				$set.=($i>0 ? ',' : '').'`'.$columns[$i].'`=';
				$set.=($values[$i]===null ? 'NULL':'"'.$values[$i].'"');
			}
		}
		
		// create SQL based on HTTP method
		switch ($method) {
		case 'GET':
			$sql = "select rowid,* from `$table`".($key ? " WHERE rowid=$key" : '');
			$result = $this->db->getResultHandle($sql);
			break;
		case 'PUT':
			$sql = "update `$table` set $set where rowid=$key";
			$result = $this->db->insertIntoDB($sql);
			break;
		case 'POST':
			$sql = "insert into `$table` set $set";
			$result = $this->db->insertIntoDB($sql);
			break;
		case 'DELETE':
			echo "DELETE-E";
			$this->pi("DELETE data:");
			# $this->pa($_DELETE . "<br>\n");
			//$this->pa(json_decode(file_get_contents('php://input')));
			$this->pa(file_get_contents('php://input'));
			#$sql = "delete `$table` where id=$key";

			# undelete
			$sql = "update `$table` set deleted = 0 where rowid=$key";
			# delete
			$sql = "update `$table` set deleted = 1 where rowid=$key";
			$result = $this->db->getResultHandle($sql);
			break;
		}
		
		// die if SQL statement failed
		if (!$result || $result < 0) {
			http_response_code(404);
			// die(mysqli_error());
			$this->pi("No results.");
			die("kaputt");
		} else {
			$this->pi("Yes results.");
			// print results, insert id or affected row count
			$this->pi("Row count: ".$result->rowCount());
		}
		
		if ($method == 'GET') {
			$this->pi("Method: GET");
			//if (!$key) echo '[';
			$this->print_html_header();
			$this->print_js_code();
			echo '<form method="post" id="'.$table.'">';
			$this->print_filters();
			$this->print_table_header();

			$i = 0;
			$line;
			do {
				//echo($i>0 ? ',' : ''.json_encode($line));
				//echo($i>0 ? ',' : ''.json_encode($line));
				$this->print_table_line($line);
				$i++;
				//print_r($line);
				//echo "<br>";
			} while ($line = $result->fetch());


			//for ($i=0; $i < $result->rowCount(); $i++) {
			//	echo ($i>0 ? ',' : '').json_encode($result->fetch());
			//}
			//if (!$key) echo ']';
			echo "</table>\n";
			echo "</form>\n";
			$this->print_html_footer();
		} elseif ($method == 'POST') {
			echo "diipa daapa"; //mysqli_insert_id($link);
		} else {
			echo "tsajaiajajajai"; //sqlite_affected_rows($link);
		}
		
		// close sql connection
		//
	}


	private function print_filters() {
		echo '<a href="hide_deleted">Hide deleted</a> ';
		echo '<a href="hide_oldest">Hide oldest</a>';
	}
	private function print_table_header() {
		echo "<table>";
		echo"<tr><th>ID</th><th>Nick</th><th>Date</th><th>Quote</th>";
		echo "<th>Info1</th><th>Info2</th><th>Link1</th><th>Link2</th>";
		echo "<th>DELETE / UNDELETE</th><th>SAVE changes</th><tr>";
	}

	private function print_table_line($arg = null) {
		if ($arg === null) return;
		$rowid = $arg['rowid'];
		echo "\n";
		//echo '<fieldset name="id_'.$rowid.'">'."\n";
		echo '<tr id="'.$rowid.'"><td>' . $rowid ."</td>";
		echo "<td>" . $arg['NICK'] ."</td>";
		echo "<td>" . date('j.m.Y H:i:s', $arg['PVM']) ."</td>";
		echo '<td><textarea name="quote">' . $arg['QUOTE'] ."</textarea></td>";
		echo '<td><textarea name="info1" class="small">' . $arg['INFO1'] ."</textarea></td>";
		echo '<td><textarea name="info2" class="small">' . $arg['INFO2'] ."</textarea></td>";
		echo '<td><input type="url" name="link1" value="' . $arg['LINK1'] .'"></td>';
		echo '<td><input type="url" name="link2" value="' . $arg['LINK2'] .'"></td>';
		$delvalue = ($arg['DELETED'] == "1") ? "Undelete" : "Delete";
		//echo '<td><a href="'.$rowid.'">'.$delvalue.'</a></td>';
		//echo '<td><input type="submit" name="action" value="DELETE"></td>';
		echo '<td><input type="button" name="method" id="delete'.$rowid.'" value="'.$delvalue.'" onclick="javaz('.$rowid.','.$delvalue.');"></td>';
		echo '<td><input type="submit" name="action" id="save'.$rowid.'" value="SAVE"></td></tr>';
		//echo "\n</fieldset>";
		echo "\n";
	}

	private function print_js_code() {
		echo'<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script>
		function javaz(butid, deleted) {
			alert("Delete " + butid + " button clicked! Delete: " + deleted);
			$.ajax({
    			type: "DELETE",
    			url: butid,
    			data: "{rowid: butid, deleted: delval}",
				contentType: "application/json",
    			success: function(msg) {
        			alert("Results: " + msg);
    			}
			});
  			return true;
		}

		$(document).ready(function() {$id on t
    		$("th").click(function() {
        		<!--$(this).hide();-->
			});
		});

	</script>
';
	}

	private function print_html_header() {
		echo "<!DOCTYPE html>\n<head>\n<title>Korvamadot</title>\n";
		$this->print_css();
		//echo '<script type="text/javascript" src="/scripts.js"></script>';
		echo "</head>\n<body>\n";

	}

	private function print_html_footer() {
		echo "</body>\n</html>";
	}
	private function print_css() {
		echo "<style>\n";
		echo "html {font-size: 16px; font-family: Tahoma, Geneva, sans-serif;}\n";
		echo "table {background-color: #999; border: 1px solid black; border-collapse: collapse; }\n";
		echo "textarea {width: 30rem; height:3rem; background-color: #181818; color: white; border: none; font-weight: bold; font-size: 1rem;}\n";
		echo "textarea.small {width: 20rem; height:2rem; }\n";
		echo "input[type=url] { background-color: #181818; color: white; border: none; padding: 0.3rem;}\n";
		echo "input[type=submit], input[type=button] {background-color: #181818; color: white;
		border: 0px solid black; border-radius: 0.3rem; padding: 0.5rem}\n";
		echo "td {margin: 0.2rem; padding: 0.3rem;}\n";
		echo "th {margin: 0.2rem; background-color: #CCC;}\n";
		echo "tr {border: 2px solid black;}\n";
		echo "fieldset {margin: 0; padding: 0; border: 0px solid black;}\n";
		echo "</style>\n";
	}

    # print errors
    private function pe($arg = null) {
        if ($arg === null || $this->DEBUG == 0) return;
        echo "Error: ".$arg . "<br>\n";
    }

    # print info
    private function pi($arg = null) {
        if ($arg === null || $this->DEBUG == 0) return;
        echo "\n INFO: ".$arg ."<br>\n";
    }

    # debug print ARRAY
    private function pa($arg = null) {
        if ($arg == null || $this->DEBUG == 0) return;
        print_r($arg);
        echo "<br>\n";
    }




}
$testing = new RESTapiforKorvamato;
?>
