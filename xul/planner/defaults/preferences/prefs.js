// pref("toolkit.defaultChromeURI", "https://user:pass@vilesci.technikum-wien.at/content/planner.xul.php");
// Entry Point for Extension
pref("toolkit.defaultChromeURI", "chrome://planner/content/browser-overlay.xul");
// Entry Point for Browser
// pref("browser.chromeURL", "chrome://planner/content/planner.xul") 

/* debugging prefs */
pref("browser.dom.window.dump.enabled", true);
pref("javascript.options.showInConsole", true);
pref("javascript.options.strict", true);
pref("nglayout.debug.disable_xul_cache", true);
pref("nglayout.debug.disable_xul_fastload", true);

/* added to allow <label class="text-links" ... /> to work */
pref("network.protocol-handler.expose.http", false);
pref("network.protocol-handler.warn-external.http", false);

/* Remote XUL */
user_pref("signed.applets.codebase_principal_support", true);

user_pref("capability.principal.codebase.p0.granted", "UniversalXPConnect");
user_pref("capability.principal.codebase.p0.id", "http://fhcomplete.technikum-wien.at");
user_pref("capability.principal.codebase.p0.subjectName", "");
user_pref("capability.principal.codebase.p1.granted", "UniversalXPConnect");
user_pref("capability.principal.codebase.p1.id", "http://calva.technikum-wien.at");
user_pref("capability.principal.codebase.p1.subjectName", "");
user_pref("capability.principal.codebase.p2.granted", "UniversalXPConnect");
user_pref("capability.principal.codebase.p2.id", "https://vilesci.technikum-wien.at");
user_pref("capability.principal.codebase.p2.subjectName", "");
user_pref("capability.principal.codebase.p3.granted", "UniversalXPConnect");
user_pref("capability.principal.codebase.p3.id", "http://dev.technikum-wien.at");
user_pref("capability.principal.codebase.p3.subjectName", "");
user_pref("capability.principal.codebase.p3.granted", "UniversalXPConnect");
user_pref("capability.principal.codebase.p3.id", "http://localhost");
user_pref("capability.principal.codebase.p3.subjectName", "");

