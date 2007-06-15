/* !!!
 * DIESE WERTE MUESSEN GEAENDERT WERDEN
 */
const APP_DISPLAY_NAME = "FASo";
const APP_NAME = "FASo";
const APP_PACKAGE = "/tw/fasonline";
const APP_VERSION = "V1.0";

const APP_JAR_FILE = "fasonline.jar";
const APP_CONTENT_FOLDER = "content/";
const APP_LOCALE_FOLDER  = "locale/de-AT/fasonline/";
const APP_SKIN_FOLDER  = "skin/";
/* aus
 */

initInstall(APP_NAME, APP_PACKAGE, APP_VERSION);

var chromef = getFolder("Profile", "chrome");
var instFlags = PROFILE_CHROME;

var err = addFile(APP_PACKAGE, APP_VERSION, "chrome/" + APP_JAR_FILE, chromef, null);

if(err >= SUCCESS) { 
	var jar = getFolder(chromef, APP_JAR_FILE);
	registerChrome(CONTENT | instFlags, jar, APP_CONTENT_FOLDER);
	//registerChrome(LOCALE  | instFlags, jar, APP_LOCALE_FOLDER);
	//registerChrome(SKIN  | instFlags, jar, APP_SKIN_FOLDER);
	err = performInstall();
	if(err == SUCCESS) {
		alert(APP_NAME + " " + APP_VERSION + " wurde erfolgreich installiert.\n"
			+"Bitte starten Sie den Browser neu bevor Sie die Anwendung starten.");
	} else { 
		alert("Install failed. Error code:" + err);
		cancelInstall(err);
	}
} else { 
	alert("Failed to create " +APP_JAR_FILE +"\n"
		+"You probably don't have appropriate permissions \n"
		+"(write access to Profile/chrome directory). \n"
		+"_____________________________\nError code:" + err);
	cancelInstall(err);
}

