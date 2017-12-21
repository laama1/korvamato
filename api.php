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
		$input2 = file_get_contents('php://input');

		//$this->pi("server path info:");
		//$this->pi($_SERVER['PATH_INFO']);


		$this->pi("input (json decoded):");
		$this->pa($input);
		$this->pi("input (not decoded):");
		$this->pa($input2);

		$this->pi("request:");
		$this->pa($request);

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
			$this->pi("POST (not encoded):");
			$this->pa($_POST);
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
		case 'PATCH':
			echo "PATCH!!!";
			$this->pa(file_get_contents('php://input'));
			return;
			break;
		}
		
		// die if SQL statement failed
		if (!$result) {
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
			//echo '<form method="post" id="'.$table.'">';
			echo '<p id="debug1"></p>';
			echo '<p id="debug2"></p>';
			$this->print_filters();
			$this->print_table_header();
			$this->add_line_row();

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
			//echo "</form>\n";
			$this->print_html_footer();
		} elseif ($method == 'POST') {
			echo "POST OK";
		} elseif ($method == 'DELETE') {
			echo "DELETE OK";
		} elseif ($method == 'PATCH') {
			echo "PATCH OK";
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
		echo"<tr><th>ID</th><th>Nick</th><th>Date</th><th>Artist</th><th>Title</th><th>Quote/Lyrics</th>";
		echo "<th>Info1</th><th>Info2</th><th>Link1</th><th>Link2</th>";
		echo "<th>DELETE / UNDELETE</th><th>SAVE changes</th><tr>";
	}

	private function add_line_row($arg = null) {
		echo '<tr id="new_row"><td>(_*_)</td>';
		echo '<td><input id="new_nick" type="text" name="nick" value=""></td>';
		echo '<td><p id="new_date"></p></td>';
		echo '<td><input id="new_artist" type="text" name="artist" value=""></td>';
		echo '<td><input id="new_title" type="text" name="title" value=""></td>';
		echo '<td><textarea id="new_quote" name="quote"></textarea></td>';
		echo '<td><textarea id="new_info1" name="info1" class="small"></textarea></td>';
		echo '<td><textarea id="new_info2" name="info2" class="small"></textarea></td>';
		echo '<td><input id="new_url" type="url" name="link1" value=""></td>';
		echo '<td><input id="new_link2" type="url" name="link2" value=""></td>';
		echo '<td>(_*_)</td>';
		echo '<td><input type="button" name="action" id="addnew" value="SAVE" onclick="addNew();"></td></tr>';
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
		echo '<td><input type="button" name="method" id="delete'.$rowid.'" value="'.$delvalue.'" onclick="deleteItem('.$rowid.',\''.$delvalue.'\');"></td>';
		echo '<td><input type="button" name="action" id="update'.$rowid.'" value="UPDATE" onclick="parseForm('.$rowid.');"></td></tr>';
		//echo "\n</fieldset>";
		echo "\n";
	}

	private function print_js_code() {
		echo'<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script>
		"use strict";
		function deleteItem(butid, deleted) {
			var delval = (deleted == "UNDELETE") ? 0 : 1;
			alert("Delete " + butid + " button clicked! Deleted: " + delval);
			$.ajax({
    			type: "DELETE",
    			url: "korvamadot/" + butid,
    			data: "{rowid: butid, deleted: delval}",
				dataType: "json",
				contentType: "application/json; charset=utf-8",
    			success: function(msg) {
        			alert("Results: " + msg);
    			},
				error: function(msg) {
					alert("Error: " + msg);
				}
			});
  			return true;
		}

		function postItem(butid) {
			alert("Post " + butid + " pressed!");
			$.ajax({
    			type: "POST",
    			url: "korvamadot/" + butid,
    			data: "{rowid: butid, deleted: delval}",
				dataType: "json",
				contentType: "application/json; charset=utf-8",
    			success: function(msg) {
        			alert("Results: " + msg);
    			},
				error: function(msg) {
					alert("Error: " + msg);
				}
			});	
		}
		function parseForm(rowid) {
			var artist,title,quote,url,link2,info1,info2;
			artist = document.getElementById(rowid+"_artist").value;
			title = document.getElementById(rowid+"_title").value;
			quote = document.getElementById(rowid+"_quote").value;
			url = document.getElementById(rowid+"_url").value;
			link2 = document.getElementById(rowid+"_link2").value;
			info1 = document.getElementById(rowid+"_info1").value;
			info2 = document.getElementById(rowid+"_info2").value;
			document.getElementById("debug1").innerHTML = (document.getElementById(rowid+"_row"));
			<!--print_r(document.getElementById(rowid+"_row"));-->
			alert("Artist: " + artist + ", Title: " + title + 
			", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +
			", Info1: " + info1 + ", Info2: " + info2);
       		$.ajax({
         		type: "PATCH",
				url: rowid,
         		data: "{}",
				dataType: "json",
				contentType: "application/json; charset=utf-8",
    			success: function(msg) {
        			alert("Results: " + msg);
    			},
				error: function(msg) {
					alert("Error: " + msg);
				}
       		});
     	}

		function updateLine(butid) {
			alert("Update " + butid + " pressed!");
		}
		function print_r(printthis, returnoutput) {
			var output = "";

			if($.isArray(printthis) || typeof(printthis) == "object") {
				for(var i in printthis) {
					output += i + " : " + print_r(printthis[i], true) + "\n";
				}
			} else {
				output += printthis;
			}
			if(returnoutput && returnoutput == true) {
				return output;
			} else {
				alert(output);
			}
		}
		$(document).ready(function() {
    		$("th").click(function() {
        		<!--$(this).hide();-->
			});
		});

	</script>
';
	}

	private function print_html_header() {
		echo "<!DOCTYPE html>\n<head>\n<title>Korvamadot</title>\n";
		echo '<link rel="stylesheet" type="text/css" href="/styles.css">'."\n";
		//echo '<script type="text/javascript" src="/scripts.js"></script>';
		echo "</head>\n<body>\n";

	}

	private function print_html_footer() {
		echo "</body>\n</html>";
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
