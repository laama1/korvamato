// korvamato scripts.js -file
// (C) LAama1 22.12.2017, 13.3.2018
// LICENSE: BSD

"use strict";
let hide_deleted_lines = 0;
let dataset;


// Delete item
function deleteItem(butid, deleted) {
	var delval = (deleted == "DELETE") ? 1 : 0;
	console.log("Delete " + butid + " button clicked! Deleted: " + delval);
	$.ajax({
		type: "DELETE",
		url: "index.php/korvamadot/" + butid,
		data: '{"deleted" : '+delval+'}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		success: function(msg) {
			//document.getElementById("debug1").innerHTML = msg;
			console.log(msg);
			if (msg.value == 1) blinkRow(msg.rowid, 'red');
			else if (msg.value == 0) blinkRow(msg.rowid, 'lightred');
			updateButton(msg.rowid);
		},
		error: function(msg, textStatus, error) {
			document.getElementById("debug1").innerHTML = msg.responseText;
			console.log("deleteItem Error: %o", msg);
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug1").innerHTML = jqXHR.responseText;
		console.log("deleteItem Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
	return true;
}

// add new item
function addNew() {
	
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
	//console.log("ledit: "+ledit);
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
			//updateLine(0, 'green');
			addLine('afterend', msg);
			//setTimeout(function() {
			//	window.location.reload(true);
			//}, 2000);
		},
		error: function(msg, textStatus, error) {
			document.getElementById("debug1").innerHTML = msg.responseText;
			console.log("error object %o", msg);
			console.log(textStatus);
			console.log(error);
			console.log("fale POST.");
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug1").innerHTML = jqXHR.responseText;
		console.log("addNew Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
}

function addLine(beforeafter, arg) {
	//console.log('type: '+ typeof(arg));
	if (typeof(arg) === 'undefined') {
		return;
	}
	if (arg.DELETED == 1 && hide_deleted_lines == 1) {
		return;
	}
	var row_id = arg['rowid'];
	//console.log('addLine rowid: '+row_id);
	var delclass = arg['DELETED'] == '1' ? 'deleted' : 'undeleted';

	var x = document.getElementById('matotable');
	var new_row = x.rows[2].cloneNode(true);

	new_row.cells[0].innerHTML = row_id;

	new_row.cells[1].childNodes[0].value = arg['NICK'];
	new_row.cells[1].childNodes[0].id = row_id+'_nick';
	
	new_row.cells[2].innerHTML = formatUnixDate(arg['PVM']);
	new_row.cells[2].childNodes[0].id = row_id+'_pvm';
	
	new_row.cells[3].childNodes[0].value = arg['ARTIST'];
	new_row.cells[3].childNodes[0].id = row_id+'_artist';
	
	new_row.cells[4].childNodes[0].value = arg['TITLE'];
	new_row.cells[4].childNodes[0].id = row_id+'_title';
	
	new_row.cells[5].childNodes[0].value = arg['QUOTE'];
	new_row.cells[5].childNodes[0].id = row_id+'_quote';
	
	new_row.cells[6].childNodes[0].value = arg['INFO1'];
	new_row.cells[6].childNodes[0].id = row_id+'_info1';
	
	new_row.cells[7].childNodes[0].value = arg['INFO2'];
	new_row.cells[7].childNodes[0].id = row_id+'_info2';
	
	new_row.cells[8].childNodes[0].value = arg['LINK1'];
	new_row.cells[8].childNodes[0].id = row_id+'_url';
	
	new_row.cells[9].childNodes[0].value = arg['LINK2'];
	new_row.cells[9].childNodes[0].id = row_id+'_link2';
	
	var btn = document.createElement("INPUT");
	btn.setAttribute("type", "button");
	btn.id = 'delete_'+row_id;
	//btn.onclick = 'deleteitem("'+row_id+'","'+arg['DELETED']+'")';
	//btn.addEventListener("click", function(){ deleteItem(row_id, arg['DELETED']);});
	btn.addEventListener("click", function(){ deleteItem(row_id, this.value);});
	btn.value = arg['DELETED'] == 1 ? 'UNDELETE' : 'DELETE';
	//btn.classList.add();
	new_row.cells[10].innerHTML = '';
	new_row.cells[10].appendChild(btn);
	new_row.cells[11].childNodes[0].value = 'UPDATE';
	new_row.cells[11].childNodes[0].id = 'update_'+row_id;
	new_row.cells[11].childNodes[0].removeEventListener('click',addNew);
	new_row.cells[11].childNodes[0].addEventListener('click', function(){ parseForm(row_id);});
	new_row.classList.add(delclass);
	new_row.id = row_id+'_row';
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
	if (beforeafter == 'afterend') {
		var y = x.rows[2];
		y.insertAdjacentElement(beforeafter, new_row);
	} else {
		x.insertAdjacentElement(beforeafter, new_row);
	}
	
	//rownumber++;


}

function clearTable() {
	console.log('clearTable');
	var table = document.getElementById('matotable');
	var r = 0;
	r = table.rows.length -1;
	var row;
	while (row = table.rows[r--]) {
		if (parseInt(row.id) > 0) {
			//console.log('element');
			//console.log(row);
			row.parentNode.removeChild(row);
		} else {
			var breakpoint = 1;
		}
	}
}

function updateTable(arg) {
	console.log('updateTable');
	try {
		dataset = arg;		// save to (overwrite) global variable
		var oblen = Object.keys(arg).length;
		console.log('oblen: '+oblen);
		for (var i = 0; i < oblen; i++) {
			addLine('beforeend', arg[i]);
		}
	} catch (error) {
		console.log('updateTable error!');
		console.log(error);
	}

}

function hideOldest() {
	console.log('hideOldest');
}

function hideDeleted(value) {
	console.log('hideDeleted');
	hide_deleted_lines = value;
}

function reloadButton() {
	console.log('reloadButton');
	dataset = null;
	clearTable();
	reloadData();
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

// get all data again from server
function reloadData() {
	console.log('reloadData');
	$.ajax({
		type: "GET",
		url: "index.php/korvamadot/0",
		//url: "/korvamadot/0",
		//data: '{"quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		success: function(msg) {
			console.log("reloadData Success: ");
			console.log(msg);
			updateTable(msg);
		},
		error: function(msg, textStatus, error) {
			console.log("reloadData error objecti %o <<<", msg);
			console.log("reloadData textStatus:" +textStatus);
			console.log("reloadData msg.responseText: "+msg.responseText);
			console.log("faLe.");
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug1").innerHTML = jqXHR.responseText;
		console.log("reloadData Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
}

// Update item
function parseForm(rowid) {
	console.log('parseForm');
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
			//document.getElementById("debug1").innerHTML = msg.responseText;
			console.log("parseForm error objecti %o <<<", msg);
			console.log("parseForm textStatus:" +textStatus);
			console.log("parseForm msg.responseText: "+msg.responseText);
			console.log("faLe.");
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug1").innerHTML = jqXHR.responseText;
		console.log("parseForm Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
}

function updateLine(butid, color) {
	// update line in the UI view table
	console.log("updateLine >" + butid + "< pressed! Color: "+color);
	blinkRow(butid, color);
}

function updateButton(butid) {
	var oldval = document.getElementById("delete_"+butid).value;
	var newval = 'UNDELETE';
	if (oldval == "UNDELETE") {
		newval = 'DELETE';
	}
	document.getElementById("delete_"+butid).value = newval;
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
	//var elem = '#'+elementid+'_row';
	var elem = elementid+'_row';
	var elementt = document.getElementById(elem);
	var oldclass = elementt.className; //document.getElementById(elementid+'_row').className;
	console.log("blinkRow oldclass: "+oldclass);
	//$(elem).toggleClass("curtains");
	//setTimeout(function() {
		var interval = setInterval(function () {
			//console.log("inside2 count: "+count);
			if (count %2 == 1) {
				//$(elem).className = "blink"+color;
				document.getElementById(elem).className = "blink"+color;
			
				console.log("newclass: blink"+color);
				//count++;
				//return "blink"+color;
			} else {
				//$(elem).className = "blinkred";
				document.getElementById(elem).className = oldclass;
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

document.addEventListener("DOMContentLoaded", function() {
	reloadData();
  });