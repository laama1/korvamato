"use strict";
function deleteItem(butid, deleted) {
	var delval = (deleted == "UNDELETE") ? 0 : 1;
	alert("Delete " + butid + " button clicked! Deleted: " + delval);
	$.ajax({
		type: "DELETE",
		url: "korvamadot/" + butid,
		data: '{"rowid" : butid, "deleted" : "delval"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		done: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			alert("Results: " + msg);
		},
		fail: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			alert("Error: " + msg);
		}
	});
	return true;
}

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
	alert("Nick: " + nick + ", Artist: " + artist + ", Title: " + title + 
	", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +
	", Info1: " + info1 + ", Info2: " + info2);

	$.ajax({
		type: "POST",
		url: "korvamadot/0",
		data: '{"nick" : "' + nick + '", "quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "channel" : "www", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		done: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			alert("Results: " + msg);
		},
		fail: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
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
	//print_r(document.getElementById(rowid+"_row"));
	alert("Artist: " + artist + ", Title: " + title + 
	", Quote: " + quote + ", URL: " + url + ", Link2: " + link2 +
	", Info1: " + info1 + ", Info2: " + info2);
	$.ajax({
		type: "PATCH",
		url: "korvamadot/"+rowid,
		data: '{"quote" : "' + quote + '", "info1" : "' + info1 + '", "info2" : "' + info2 + '", "artist" : "' + artist + '", "title" : "' + title + '", "link1" : "' + url + '", "link2" : "' + link2 + '"}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		done: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
			alert("Results: " + msg);
		},
		fail: function(msg) {
			document.getElementById("debug1").innerHTML = msg;
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
	//$(this).hide();
	});
});
