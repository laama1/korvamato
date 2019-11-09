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
		url: "korvamadot/0",
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
			document.getElementById("debug1").innerHTML = msg;
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
});
