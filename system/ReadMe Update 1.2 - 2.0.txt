Anleitung zum Update von FH-Complete Version 1.2 auf Version 2.0
================================================================

Features der Version 2.0:
-------------------------
- Neues umfassenderes Rechtekonzept
- Datenbank UTF-8 kodiert
- Dateien die in der Datenbank gespeichert sind (PDFs, Bilder), sind nun base64 kodiert
- Organisationsstruktur kann über Organisationseinheiten abgebildet werden
- Datenbank Abstraktionsebene für den Einsatz alternativer Datenbanken
- Diverse Bugfixes

Update:
-------
Die folgenden Schritte sind für ein Upgrade von Version 1.2 auf Version 2.0 nötig:

- Updatescript /system/ExtendUidInDatabase.php starten - UID wird auf 32 Zeichen und Räume auf 16 Zeichen erweitert. (Funktioniert auch, wenn die Datenbank bereits als utf8 eingespielt ist) Laufzeit ca 20 min
- Datenbank Dump erstellen
- Datenbank als UTF8 neu einspielen
- Updatescript starten: /system/update12-20.php (Laufzeit: 2 sec)
- Konvertierung der PDFs und Bilder in der Datenbank von Hex auf base64: /system/hextobase64.php Laufzeit < 2 Minuten
- Konvertieren der Testtool-Daten: /system/TesttoolCleanEncoding.php Laufzeit: 2 sec
- Kopieren ALLER Scripten aus dem trunk inclusive der neuen configs (Scripten unter include/Excel, include/xslfo2pdf, include/xslfo2pdf/fpdf nicht vergessen)
- anpassen der configs (diese befinden sich ab sofort unter /config/)
- in der Tabelle public.tbl_vorlagestudiengang muss bei jeder Vorlage im Header das Encoding von "ISO-8859-15" auf "UTF-8" geändert werden
- die Files /trunk/system/xsl/Zeugnis_0_v1.xsl und ZeugnisEng_0_v1.xsl neu in die Tabelle public.tbl_vorlagestudiengang einspielen
  (bei XSL-Vorlagen wird das Attribut content-length nun richtig(er) interpretiert. Deshalb muss an manchen Stellen dieser Wert angepasst werden an die tatsächlichen mm Werte)
- /system/checksystem.php starten
- Fertig!

Die Updatescripte im System Ordner verwenden alle die Config /config/system.config.inc.php
Die Scripten TesttoolCleanEncoding.php und hextobase64.php prüfen nicht ob das Script bereits ausgeführt wurde. 
D.h. sie dürfen nur einmal gestartet werden, da die Daten sonst doppelt kodiert werden!! 