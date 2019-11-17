// korvamato scripts.js -file
// (C) LAama1 22.12.2017, 13.3.2018
// LICENSE: BSD

"use strict";

// Delete item
function deleteItem(butid, deleted) {
	var delval = (deleted == "DELETE") ? 1 : 0;
	console.log("Delete " + butid + " button clicked! Deleted: " + delval);
	$.ajax({
		type: "DELETE",
		url: "korvamadot/" + butid,
		data: '{"rowid" : '+butid+', "deleted" : '+delval+'}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		success: function(msg) {
			//document.getElementById("debug1").innerHTML = msg;
			console.log("deleteItem success Results: %o", msg);
			if (delval == 1) updateLine(msg, 'red');
			else if (delval == 0) updateLine(msg, 'lightred');
			updateButton(msg);
		},
		error: function(msg, textStatus, error) {
			document.getElementById("debug1").innerHTML = msg.responseText;
			console.log("deleteItem Error: %o", msg);
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug2").innerHTML = jqXHR.responseText;
		console.log("deleteItem Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
	return true;
}

// add new item
function addNew(butid) {
	
	var nick,artist,title,quote,url,link2,info1,info2, ledit;
	nick = document.getElementById("new_nick").value;
	artist = document.getElementById("new_artist").value;
	title = document.getElementById("new_title").value;
	quote = document.getElementById("new_quote").value;
	url = document.getElementById("new_url").value;
	link2 = document.getElementById("new_link2").value;
	info1 = document.getElementById("new_info1").value;
	info2 = document.getElementById("new_info2").value;
	//ledit = Date.time();
	//ledit = this.getTime()/1000|0;
	//ledit = Date.now().getUnixTime();
	var date = new Date();
	ledit = date.toISOString();
	console.log("ledit: "+ledit);
	//console.log("Add new line, Nick: " + nick + ", Artist: " + artist + ", Title: " + title + 
	//", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +
	//", Info1: " + info1 + ", Info2: " + info2+ ", POST next..");

	$.ajax({
		type: "POST",
		url: "index.php/korvamadot/0",
		//data: '{"nick" : "' + nick + '", "quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "channel" : "www", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '", "lastedit" : ' +ledit+'}',
		data: '{"nick" : "' + nick + '", "quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "channel" : "www", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		success: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			console.log("addNew Results: %o", msg);
			updateLine(0, 'green');
			setTimeout(function() {
				window.location.reload(true);
			}, 2000);
		},
		error: function(msg, textStatus, error) {
			document.getElementById("debug2").innerHTML = msg.responseText;
			console.log("error object %o", msg);
			console.log(textStatus);
			console.log(error);
			console.log("fale POST.");
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug2").innerHTML = jqXHR.responseText;
		console.log("addNew Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
}

function addLine(arg) {
	//console.log('type: '+ typeof(arg));
	if (typeof(arg) === 'undefined') {
		return;
	}
	console.log('addLine rowid: '+arg['rowid']);
	var delclass = arg['DELETED'] == '1' ? 'deleted' : 'undeleted';
	//echo '<fieldset name="id_'.$rowid.'">'."\n";
	/*print '<tr id="' +rowid+ '_row" class="'+delclass+'"><td>' +rowid+ '</td>';
	echo '<td><p id="'+rowid+'_nick">' +$arg['NICK']+'</p></td>';
	echo '<td><p id="'+rowid+'_date">' . date('j.m.Y H:i:s', $arg['PVM']) .'</p></td>';
	echo '<td><input id="'+rowid+'_artist" type="text" name="artist" value="'+$arg['ARTIST']+'"></td>';
	echo '<td><input id="'+rowid+'_title" type="text" name="title" value="'.$arg['TITLE'].'"></td>';
	echo '<td><textarea id="'+rowid+'_quote" name="quote">' . $arg['QUOTE'] .'</textarea></td>';
	echo '<td><textarea id="'+rowid+'_info1" name="info1" class="small">' . $arg['INFO1'] .'</textarea></td>';
	echo '<td><textarea id="'+rowid+'_info2" name="info2" class="small">' . $arg['INFO2'] .'</textarea></td>';
	echo '<td><input id="'+rowid+'_url" type="url" name="link1" value="' . $arg['LINK1'] .'"></td>';
	echo '<td><input id="'+rowid+'_link2" type="url" name="link2" value="' . $arg['LINK2'] .'"></td>';
	$delvalue = ($arg['DELETED'] == "1") ? "UNDELETE" : "DELETE";
	//echo '<td><a href="'.$rowid.'">'.$delvalue.'</a></td>';
	//echo '<td><input type="submit" name="action" value="DELETE"></td>';
	echo '<td><input type="button" name="method" id="delete'+rowid+'" value="'.$delvalue.'" onclick="deleteItem('+rowid+',\''.$delvalue.'\');"></td>';
	echo '<td><input type="button" name="action" id="update'+rowid+'" value="UPDATE" onclick="parseForm('+rowid+');"></td></tr>';

*/
	//console.log("insRow .. data1: "+data1+", data2: "+data2+", data3: "+data3);
	var x = document.getElementById('matotable');
	var new_row = x.rows[2].cloneNode(true);
	//var new_row = x.cloneNode(true);
	/*var len = x.rows.length;
	if (len > 60) {
		x.removeChild(x.lastChild);
	}*/
	new_row.cells[0].innerHTML = arg['rowid'];
	new_row.cells[1].childNodes[0].value = arg['NICK'];
	new_row.cells[2].innerHTML = formatUnixDate(arg['PVM']);
	new_row.cells[3].childNodes[0].value = arg['ARTIST'];
	new_row.cells[4].childNodes[0].value = arg['TITLE'];
	new_row.cells[5].childNodes[0].value = arg['QUOTE'];
	new_row.cells[6].childNodes[0].value = arg['INFO1'];
	new_row.cells[7].childNodes[0].value = arg['INFO2'];
	new_row.cells[8].childNodes[0].value = arg['LINK1'];
	new_row.cells[9].childNodes[0].value = arg['LINK2'];
	
	var btn = document.createElement("INPUT");
	btn.setAttribute("type", "button");
	btn.id = 'delete_'+arg['rowid'];
	//btn.onclick = 'deleteitem("'+arg['rowid']+'","'+arg['DELETED']+'")';
	btn.addEventListener("click", function(){ deleteItem(arg['rowid'], arg['deleted']);});
	btn.value = arg['DELETED'] == 1 ? 'UNDELETE' : 'DELETE';
	btn.classList.add();
	new_row.cells[10].innerHTML = '';
	new_row.cells[10].appendChild(btn);
	new_row.cells[11].childNodes[0].value = 'UPDATE';
	new_row.classList.add(delclass);
	var newcolor = 0;
	//var newbgcolor = 0;
	/*if (isrunning) {
		newcolor = calculateColor();
	} else {
		newcolor = calculateBG(data1);
	}*/
	
	/*var sw = screen.width;
	var value = parseInt((xres/sw*100), 10);
	//var dlblue = calculateDayColor()
	new_row.style.background = "linear-gradient(to right, #000 0%, rgb(0,"+newcolor+",30) "+value+"%, #000 100%)";
	new_row.classList.add('tablerow');
	new_row.onmouseover = function() {editAuroraRowBG2(this)};
*/
	x.insertAdjacentElement('beforeend', new_row);
	//rownumber++;


}

function updateTable(arg) {
	var alength = arg.length;
	console.log("populate table .."+alength);
	var obj = JSON.parse(arg);

	$.each(obj,function(index, value){
		//console.log('My array has at position ' + index + ', this value: ' + value);
	});


	for (var i = 0; i < alength; i++) {
		console.log(obj[i]);
		//var jstime = jsutime(data[i][3]);
		//console.log("jstime:" +jstime);
		console.log(obj[i]);
		//addLine(obj[i][0], obj[i][2], obj[i]);
		addLine(obj[i]);
	}
}

function hideOldest() {

}

function hideDeleted() {

}

function formatUnixDate(timestamp) {
	var tempdate = new Date(timestamp*1000);
	return formatDate(tempdate);
}

function formatDate(date) {
	var day = date.getDate();
	var monthIndex = date.getMonth();
	var year = date.getFullYear();
	var minutes = date.getMinutes();
	var hours = date.getHours();
	return hours + ":" +minutes+ " " +day + '.' + (monthIndex +1) +'.' + year;
}

function reloadData() {
$.ajax({
	type: "GET",
	url: "index.php/korvamadot/",
	//data: '{"quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
	//dataType: "json",
	//contentType: "application/json; charset=utf-8",
	//contentType: "application/json",
	success: function(msg) {
		document.getElementById("debug1").innerHTML = '<pre>'+msg+'</pre>';
		//console.log("reloadData Success: " + msg);
		updateTable(msg);
	},
	error: function(msg, textStatus, error) {
		//document.getElementById("debug2").innerHTML = msg.responseText;
		console.log("parseForm error objecti %o <<<", msg);
		console.log("parseForm textStatus:" +textStatus);
		console.log("parseForm msg.responseText: "+msg.responseText);
		console.log("faLe.");
	}
}).fail(function (jqXHR, textStatus, error) {
	document.getElementById("debug2").innerHTML = jqXHR.responseText;
	console.log("parseForm Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
	console.log(jqXHR);
});
}

// Update item
function parseForm(rowid) {
	var artist,title,quote,url,link2,info1,info2;
	artist = document.getElementById(rowid+"_artist").value;
	title = document.getElementById(rowid+"_title").value;
	quote = document.getElementById(rowid+"_quote").value;
	url = document.getElementById(rowid+"_url").value;
	link2 = document.getElementById(rowid+"_link2").value;
	info1 = document.getElementById(rowid+"_info1").value;
	info2 = document.getElementById(rowid+"_info2").value;
	//document.getElementById("debug1").innerHTML = (document.getElementById(rowid+"_row"));
	//print_r(document.getElementById(rowid+"_row"));
	console.log("Parse form Artist: " + artist + ", Title: " + title + ", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +", Info1: " + info1 + ", Info2: " + info2+ ", PUT request next.\n");
	console.log("rowid: "+rowid);
	$.ajax({
		//type: "PATCH",
		type: "PUT",
		url: "korvamadot/"+rowid,
		data: '{"quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		//contentType: "application/json",
		success: function(msg) {
			document.getElementById("debug1").innerHTML = '<pre>'+msg+'</pre>';
			console.log("parseForm Success: " + msg);
			updateLine(msg, 'yellow');
		},
		error: function(msg, textStatus, error) {
			//document.getElementById("debug2").innerHTML = msg.responseText;
			console.log("parseForm error objecti %o <<<", msg);
			console.log("parseForm textStatus:" +textStatus);
			console.log("parseForm msg.responseText: "+msg.responseText);
			console.log("faLe.");
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug2").innerHTML = jqXHR.responseText;
		console.log("parseForm Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
}

function updateLine(butid, color) {
	// update line in the UI view table
	console.log("######  Update >" + butid + "< pressed! Color: "+color);
	blinkRow(butid, color);
}

function updateButton(butid) {
	var oldval = document.getElementById("delete"+butid).value;
	var newval = 'UNDELETE';
	if (oldval == "UNDELETE") {
		newval = 'DELETE';
	}
	document.getElementById("delete"+butid).value = newval;
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
		console.log(output);
	}
}

function blinkHideRow(elementid) {

}

function blinkRow(elementid, color) {
	console.log("blinkRow param: "+elementid+ ", color: "+color);
	var count = 0;
	var elem = '#'+elementid+'_row';
	var elementt = document.getElementById(elementid+'_row');
	var oldclass = document.getElementById(elementid+'_row').className;
	console.log("blinkRow oldclass: "+oldclass);
	//$(elem).toggleClass("curtains");
	//setTimeout(function() {
		var interval = setInterval(function () {
			//console.log("inside2 count: "+count);
			if (count %2 == 1) {
				//$(elem).className = "blink"+color;
				document.getElementById(elementid+'_row').className = "blink"+color;
			
				console.log("newclass: blink"+color);
				//count++;
				//return "blink"+color;
			} else {
				//$(elem).className = "blinkred";
				document.getElementById(elementid+'_row').className = oldclass;
				console.log("newclass: "+oldclass);
			}
			
			if (count > 2) {
				clearInterval(interval);
				if (color == 'red') {
					//$(elem).slideToggle("slow");
					 //$(elem).toggleClass("curtains");
				}
			}
			count++;
		}, 300)
	//},100);
}

$(document).ready(function() {
	$("th").click(function() {
	//$(this).hide();
	});
	reloadData();
});
