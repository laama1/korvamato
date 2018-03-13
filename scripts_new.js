// korvamato scripts.js -file
// (C) LAama1 22.12.2017, 13.3.2018
// LICENSE: BSD

"use strict";

// Delete item
function deleteItem2(butid, deleted) {
	var delval = (deleted == "UNDELETE") ? 0 : 1;
	console.log("Delete " + butid + " button clicked! Deleted: " + delval);
	$.ajax({
		type: "DELETE",
		url: "korvamadot/" + butid,
		data: '{"rowid" : '+butid+', "deleted" : '+delval+'}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		success: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			console.log("deleteItem Results: %o" + msg);
			updateLine(msg);
		},
		error: function(msg, textStatus, error) {
			document.getElementById("debug1").innerHTML = msg.responseText;
			console.log("deleteItem Error: %o", msg);
		}
	});
	return true;
}

// add new item
function addNew(butid) {
	
	var nick,artist,title,quote,url,link2,info1,info2;
	nick = document.getElementById("new_nick").value;
	artist = document.getElementById("new_artist").value;
	title = document.getElementById("new_title").value;
	quote = document.getElementById("new_quote").value;
	url = document.getElementById("new_url").value;
	link2 = document.getElementById("new_link2").value;
	info1 = document.getElementById("new_info1").value;
	info2 = document.getElementById("new_info2").value;
	console.log("Add new line, Nick: " + nick + ", Artist: " + artist + ", Title: " + title + 
	", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +
	", Info1: " + info1 + ", Info2: " + info2+ ", POST next..");

	$.ajax({
		type: "POST",
		url: "korvamadot/0",
		data: '{"nick" : "' + nick + '", "quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "channel" : "www", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		success: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			console.log("addNew Results: " + msg);
			updateLine(msg);
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
		console.log("Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
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
	document.getElementById("debug1").innerHTML = (document.getElementById(rowid+"_row"));
	//print_r(document.getElementById(rowid+"_row"));
	console.log("Parse form Artist: " + artist + ", Title: " + title + ", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +", Info1: " + info1 + ", Info2: " + info2+ ", PATCH request next.\n");
	
	$.ajax({
		type: "PATCH",
		url: "korvamadot/"+rowid,
		data: '{"quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		//contentType: "application/json",
		success: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			console.log("Success: " + msg);
			updateLine(msg);
		},
		error: function(msg, textStatus, error) {
			document.getElementById("debug2").innerHTML = msg.responseText;
			console.log("error objecti %o <<<", msg);
			console.log(textStatus);
			//console.log(error);
			console.log("faLe.");
		}
	}).fail(function (jqXHR, textStatus, error) {
		document.getElementById("debug2").innerHTML = jqXHR.responseText;
		console.log("Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
}

function updateLine(butid) {
	// update line in the UI view table
	console.log("######  Update " + butid + " pressed!");
	blinkRow(butid);
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

function blinkRow(elementid) {
	setTimeout(function() {
		var interval = setInterval(function () {
			$(elementid+'_row').toggleClass(function() {
				count++;
				return "blink";
			})
		if ($count == 2) clearInterval(interval);
		
		}, 600)
	},1000);
}

$(document).ready(function() {
	$("th").click(function() {
	//$(this).hide();
	});
});
