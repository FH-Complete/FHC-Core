/**
*
* HTTP Request Klasse
* stammt aus Artikel von phpPatterns (http://www.phppatterns.com/index.php/article/articleview/82/1/2/)
* leicht modifiziert
*/

//Configuration Details
//const SERVER_URL = "/xul/server.php";
// wir uebergeben das als Parameter
//End Configuration

function doLogin()
{
	var username = document.getElementById('loginUser').value;
	var password = document.getElementById('loginPass').value;

	req = new phpRequest();
	req.add('username',username);
	req.add('password',password);

	var response = req.execute();
	alert(response);
}

//Start phpRequest Object
function phpRequest(server_url,uname,passw)
{
	this.parms = new Array();
	this.parmsIndex = 0;
	this.execute = phpRequestExecute;
	this.executePOST = phpRequestExecutePOST;
	this.add = phpRequestAdd;
	this.server = server_url;
	this.uname = uname;
	this.passw = passw;
}

function phpRequestAdd(name,value)
{
	this.parms[this.parmsIndex] = new Pair(name,value);
	this.parmsIndex++;
}

function phpRequestExecute()
{
	var targetURL = this.server;

	try {
		var httpRequest = new XMLHttpRequest();
	}catch (e){
		alert('Error creating the connection!');
		return;
	}

	try {
		var txt = "?";
		for(var i in this.parms) {
			txt = txt+'&'+this.parms[i].name+'='+encodeURIComponent(this.parms[i].value);
		}
		//alert('sende '+txt);
		//Two options here, only uncomment one of these
		//GET REQUEST
		httpRequest.open("GET", targetURL+txt, false, '','');

		//POST REQUEST EXAMPLE
		/*
		httpRequest.open("POST", targetURL+txt, false, null, null);
		httpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		*/
		httpRequest.send('');

	}catch (e){
		alert('An error has occured calling the external site: '+e);
		return false;
	}

	switch(httpRequest.readyState) {
		case 1,2,3:
			alert('Bad Ready State: '+httpRequest.status);
			return false;
		break;
		case 4:
			if(httpRequest.status !=200) {
				alert('The server respond with a bad status code: '+httpRequest.status);
				return false;
			} else {
				var response = httpRequest.responseText;
			}
		break;
	}

	return response;
}


function phpRequestExecutePOST()
{
	var targetURL = this.server;

	try
	{
		var httpRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		alert('Error creating the connection!');
		return;
	}

	try
	{
		var txt = "";
		for(var i in this.parms)
		{
			txt = txt+'&'+this.parms[i].name+'='+encodeURIComponent(this.parms[i].value);
		}
		//alert('sende '+txt);

		//POST REQUEST
		httpRequest.open("POST", targetURL, false, '', '');
		httpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		httpRequest.send(txt);

	}
	catch (e)
	{
		alert('An error has occured calling the external site: '+e);
		return false;
	}

	switch(httpRequest.readyState)
	{
		case 1,2,3:
			alert('Bad Ready State: '+httpRequest.status);
			return false;
		break;
		case 4:
			if(httpRequest.status !=200)
			{
				alert('The server respond with a bad status code: '+httpRequest.status);
				return false;
			}
			else
			{
				var response = httpRequest.responseText;
			}
		break;
	}

	return response;
}

function Pair(name,value)
{
	this.name = name;
	this.value = value;
}
