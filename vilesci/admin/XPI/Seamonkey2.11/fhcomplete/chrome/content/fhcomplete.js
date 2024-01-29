var serverURL='';
var tempus_url='';
var fas_url='';
var planner_url='';
var default_app='';
var app_load_delay;

function init(loadapp)
{
	// URL aus den Preferences holen
	var prefs = Components.classes["@mozilla.org/preferences-service;1"]
         .getService(Components.interfaces.nsIPrefService)
         .getBranch("extensions.fhcomplete.");
	prefs.QueryInterface(Components.interfaces.nsIPrefBranch2);
      
	serverURL = prefs.getCharPref("url");
	fas_url = prefs.getCharPref("fas_url");
	tempus_url = prefs.getCharPref("tempus_url");
	planner_url = prefs.getCharPref("planner_url");
	default_app = prefs.getCharPref("default_app");
	app_load_delay = prefs.getCharPref("app_load_delay");

	// Remote XUL für diese URL aktivieren
	var uri = Components.classes["@mozilla.org/network/io-service;1"]
		.getService(Components.interfaces.nsIIOService)
		.newURI(serverURL, null, null);
	Components.classes["@mozilla.org/permissionmanager;1"]
		.getService(Components.interfaces.nsIPermissionManager)
		.add(uri, 'allowXULXBL', Components.interfaces.nsIPermissionManager.ALLOW_ACTION);

	if(loadapp==undefined)
	{
		// URL nach kurzem Delay setzten da sonst die Authentifizierung nicht greift
		window.setTimeout(function (){
			LoadApp(default_app);
		}, app_load_delay);
	}
}

function OpenApp(app)
{
	init(false);
	switch(app)
	{
		case 'fas': 
			url = 'chrome://fhcomplete/content/fas.xul';
			break;
		case 'tempus': 
			url = 'chrome://fhcomplete/content/tempus.xul';
			break
		case 'planner':
			url = 'chrome://fhcomplete/content/planner.xul';
			break;
		default:
			url = 'chrome://fhcomplete/content/appchoose.xul';
			break;
	}
	window.openDialog(url, "_blank",
                    "chrome,all,dialog=no", null,
                    "charset=" + window.content.document.characterSet);

/*	window.setTimeout(function(){
		LoadApp(app);
	}, 500);*/
}

function getAppUrl(app)
{
	var url = serverURL;

	switch(app)
	{
		case 'fas': 
			url = url+fas_url;
			break;
		case 'tempus': 
			url = url+tempus_url;
			break
		case 'planner':
			url = url+planner_url;
			break;
		default:
			url = 'chrome://fhcomplete/content/appchoose.xul';
			break;
	}
	return url;
}

function LoadApp(app)
{
	url = getAppUrl(app);
	document.getElementById('fhcomplete_browser').setAttribute('src',url);	 
}
