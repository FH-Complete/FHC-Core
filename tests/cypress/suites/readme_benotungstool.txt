BENOTUNGSTOOL - TESTSUITE
=========================

WARNUNG: Die Suite löscht und schreibt Noten in der Datenbank. Starte sie nur gegen eine
Testinstanz.


1. WAS HIER LIEGT
-----------------

e2e/specs/unit/      Regeln als reine Funktion. Kein Server, keine Datenbank.
e2e/specs/api/noten/ Die API. Ein Test ruft einen Endpunkt auf und prüft die Antwort.
e2e/specs/ui/        Die Oberfläche im Browser.

support/api/         Die API-Aufrufe an einer Stelle. Spiegelt public/js/api/factory/noten.js.
support/pages/       Die Oberfläche an einer Stelle. Selektoren und Klickwege.
support/helpers/     Testdaten, Fehlermeldungen, Fixture-Reset.
tasks/               Datenbankverbindung und SSH-Tunnel. Läuft in Node, nicht im Browser.
suites/noten.js      Verbindet die Suite mit cypress.config.js.
tools/dbCheck.js     Prüft die Fixture ohne HTTP. Gutes erstes Kommando bei Problemen.

Die Konfiguration steht in cypress.config.js. Die Zugangsdaten stehen in
tests/cypress/.env.


2. VORBEREITUNG
---------------

Einmalig:

  npm install

Dann die Zugangsdaten anlegen:

  cp tests/cypress/.env.example tests/cypress/.env

Fülle die Datei aus. Die Kommentare darin erklären jeden Wert. Drei Punkte sind wichtig:

- .env darf nie in Git landen.
- USER_NAME ist der LEKTOR, nicht ein Administrator. Ein Administrator unterrichtet meist nichts,
  dann findet die Suite keine Lehrveranstaltung.
- Von einer Arbeitsstation aus brauchst du den SSH-Tunnel. Die Datenbank nimmt nur den
  Applikationsserver an. Setze NOTEN_SSH_TUNNEL=true und die drei NOTEN_SSH_*-Werte.

Beide Seiten müssen auf dieselbe Datenbank zeigen: die Webinstanz (config/system.config.inc.php,
DB_NAME) und die Suite (NOTEN_DB_NAME in .env). Sonst seedet die Suite die eine Datenbank und prüft
gegen die andere.


3. STARTEN
----------

  npm run noten:check          Fixture prüfen.
  npm run cy:noten:api         Unit- und API-Tests.
  npm run cy:noten:ui          Oberfläche in Chrome.
  npm run cy:open              Cypress interaktiv.

Zwei weitere Skripte prüfen den Punktemodus:

  npm run cy:noten:punkte-on   erwartet CIS_GESAMTNOTE_PUNKTE = true
  npm run cy:noten:punkte-off  erwartet CIS_GESAMTNOTE_PUNKTE = false

Passt die Instanz nicht zur Erwartung, bricht der Lauf sofort ab.


4. DAS ERGEBNIS
---------------------

Am Ende steht eine Tabelle mit vier Spalten.

Passing   Der Test lief und war erfolgreich.
Failing   Der Test lief und fand einen Fehler. Nur das ist ein Problem.
Pending   Der Test hat sich selbst übersprungen. Das ist kein Fehler.
Skipped   Ein Test lief nicht, weil vorher etwas abgebrochen ist.

Pending ist normal. Ein Test überspringt sich, wenn seine Voraussetzung fehlt. Beispiele: der
Punktemodus ist aus, die Frist ist nicht aktiv, die Freigabemail ist nicht erlaubt. Den Grund
schreibt der Test ins Protokoll.

Der aktuelle Sollzustand: API und Unit 84 Tests, 69 grün, 15 pending. UI 30 Tests, 18 grün,
12 pending. Kein roter Test.


5. WENN ETWAS SCHIEFGEHT
------------------------

401 bei allen Tests
  Die Webinstanz und .env zeigen auf verschiedene Datenbanken. Siehe Abschnitt 2.

"Fixture reset unavailable"
  Die Datenbankverbindung fehlt. Prüfe den SSH-Tunnel. Einen Schlüssel mit einem Namen ausserhalb
  der OpenSSH-Standardnamen probiert SSH nie automatisch. Trage ihn in NOTEN_SSH_KEY ein.

Ein Test erwartet eine Fehlermeldung und bekommt eine andere
  Die Phrase fehlt in der Datenbank. Lass system/phrasesupdate.php auf dem Server laufen.

Der nächste Lauf startet nicht
  Ein Cypress-Prozess hängt noch. Beende ihn:  Stop-Process -Name Cypress -Force

Ein Screenshot zeigt eine leere Seite
  Bei API-Tests ist das normal. Diese Tests öffnen keine Seite.


6. KONVENTIONEN
---------------

- Keine festen Ids im Test. Die Noten kommen aus getNoten, die Regelwerte aus getCisConfig. Eine
  andere Installation hat andere Zahlen.
- Warte nach jeder schreibenden Aktion auf den Request, nicht auf die Oberfläche.
- Sprich die Oberfläche über data-cy an, nie über eine CSS-Klasse.
- Setze den Zustand vor dem Test über givenBaseline oder resetNotenState. Verlass dich nie auf den
  Zustand, den ein Test davor hinterlassen hat.
