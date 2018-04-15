"use strict";
var colour = 1;
var multiplier = 1;
var isrunning = false;
var interval;
var maintimerinterval;
var rownumber = 1;
var xres = null;
console.log("ver 11.");


console.log("Now: "+formatDate(new Date()));


document.addEventListener('mousemove', onMouseUpdate, false);

function onMouseUpdate(e) {
	xres = e.pageX;
}

window.onload = maintimer();

function getLatest() {
	console.log("getLatest ..");
	isrunning = false;
	clearInterval(interval);
	$.ajax({
		type: "GET",
		url: "api.php/auroras/latest",
		data: '{"rowid" : 1, "deleted" : 0}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		headers: {
            "Accept": "application/json; odata=verbose"  
        },
		success: function(msg) {
			console.log("getLatest success Results: %o", msg);
			insRow(msg[0][1], msg[0][2],jsutime(msg[0][3]));
		},
		error: function(msg, textStatus, error) {
			console.log("getLatest Error: %o", msg);
		}
	}).fail(function (msg, textStatus, error) {
		console.log("getLatest FAIL: " + msg.responseText + "<<<<< >textStatus "+textStatus+"<<<<< error: "+error);
		console.log(msg);
		console.log("<<<<< getLatest FAIL.");
	});
	
}

// Get all lines from db
function getLinesFromDB() {
	console.log("getLinesFromDB");
	$.ajax({
		type: "GET",
		url: "api.php/auroras/",
		//data: '{"rowid" : 1, "deleted" : 0}',
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		headers: {  
            "Accept": "application/json; odata=verbose"  
        },
		success: function(msg) {
			console.log("getLinesFromDB success Results: %o", msg);
			populateTable(msg);
		},
		error: function(msg, textStatus, error) {
			console.log("getLinesFromDB Error: %o", msg);
		}
	}).fail(function (jqXHR, textStatus, error) {
		console.log("getLinesFromDB Error: " + jqXHR.responseText + ", textStatus "+textStatus+", error: "+error);
		console.log(jqXHR);
	});
	return true;
}

function populateTable(data) {
	console.log("populate table .."+data.length);
	length = data.length;
	for (var i = 0; i < length; i++) {
		console.log(data[i]);
		var jstime = jsutime(data[i][3]);
		console.log("jstime:" +jstime);
		insRow(data[i][1],data[i][2], jstime);
	}
}

function getHoursFromDate(date) {
	return new Date(date).getHours();
}

function formatDate(date) {
	var day = date.getDate();
	var monthIndex = date.getMonth();
	var year = date.getFullYear();
	var minutes = date.getMinutes();
	var hours = date.getHours();
	return hours + ":" +minutes+ " " +day + '.' + (monthIndex +1) +'.' + year;
}

function jsutime(s) {
    return formatDate(new Date(s * 1e3));//.slice(-13, -5);
}

// data3 = date
function insRow(data1, data2, data3) {
	console.log("insRow .. data1: "+data1+", data2: "+data2+", data3: "+data3);
	var x = document.getElementById('auroratable');
	var new_row = x.rows[0].cloneNode(true);
	var len = x.rows.length;
	if (len > 60) {
		x.removeChild(x.lastChild);
	}
	new_row.cells[0].innerHTML = rownumber;
	new_row.cells[1].innerHTML = data1;
	//new_row.cells[2].innerHTML = data2;
	new_row.cells[2].innerHTML = "";
	new_row.cells[3].innerHTML = data3;
	new_row.cells[4].innerHTML = formatDate(new Date());
	var newcolor = 0;
	//var newbgcolor = 0;
	if (isrunning) {
		newcolor = calculateColor();
	} else {
		newcolor = calculateBG(data1);
	}
	
	var sw = screen.width;
	var value = parseInt((xres/sw*100), 10);
	//var dlblue = calculateDayColor()
	new_row.style.background = "linear-gradient(to right, #000 0%, rgb(0,"+newcolor+",30) "+value+"%, #000 100%)";
	new_row.classList.add('tablerow');
	new_row.onmouseover = function() {editAuroraRowBG2(this)};

	x.insertAdjacentElement('afterbegin', new_row);
	rownumber++;
}

function editAuroraRowBG2(e) {
	var kpvalue = e.cells[1].innerHTML;
	//console.log("editAuroraRowBG2 kpvalue: " +kpvalue);
	var oldcolor = calculateBG(kpvalue);
	var sw = screen.width;
	var value = parseInt((xres/sw*100), 10);
	e.style.background = "linear-gradient(to right, #000 0%, rgb(0,"+oldcolor+",30) "+value+"%, #000 100%)";
}

function editAuroraRowBG(idnbr) {
	var x = document.getElementById('auroratable');
	var editrow = x.rows[idnbr];
	editrow.style.background = "linear-gradient(to right, #000 0%, rgb(0,"+newcolor+",30) "+xres+"%, #000 100%)";
}


function calculateBG(value) {
	var green = 255;
	var returnvalue = 0;
	returnvalue = green/7 * value;
	//console.log("calculateBG new color: "+parseInt(returnvalue)+", value: "+value);
	return parseInt(returnvalue);
}

// param hours: 0-24 or smth
function calculateDayColor(value) {
	var blue = 255;
	var returnvalue = 0;
	returnvalue = 0.5 + blue /24*value;
	return parseInt(returnvalue);
}

function calculateColor() {
	if (colour > 245 || colour < 1) {
		multiplier = 0-multiplier;
	}
	colour = colour + (10*multiplier);
	return colour;
}

function timerdemo() {
	if (isrunning == false) {
		interval = setInterval(function () {
			insRow();
		}, 56);
		console.log("PLAY");
	} else {
		console.log("STOP");
		clearInterval(interval);
	}
	isrunning = !isrunning;
}

function maintimer() {
	maintimerinterval = setInterval(function() {
		getLatest();
	}, 1800000);	// 30 mins
}
