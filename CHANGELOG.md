# Change Log

## [Unreleased]

### Added
- **[FAS]** Studierendensuche erweitert für leichteres auffinden von Personen mit Sonderzeichen;Suche nach EMail (#email),Telefon(#tel)
- **[FAS]** Die Anzahl der angezeigten Studiensemester im Menübaum ist pro User konfigurierbar
- **[CIS]** Im Coodle können nun auch Gruppen zu Umfragen hinzugefügt werden.
- **[CIS]** Reservierungen im Stundenplan prüft nun die Verfügbarkeit des Raums im Stundenplandev
- **[FAS]** Projektarbeiten können als Final markiert werden
- **[FAS]** Verwaltung von Rechnungsadressen
- **[CIS]** Mitarbeiter und Studierende können nach dem Login im CIS zur Passwortänderung umgeleitet werden wenn dieses seit über einem Jahr nicht geändert wurde
- **[FAS]** Bei Statuswechsel von Studierenden können Gründe für den Statuswechsel angegeben werden
- **[ADDONS]** Addons können Menüpunkte im Vilesci anpassen
- **[ADDONS]** Addons können Noten für die Gesamtnote vorschlagen
- **[CORE]** UserDefinedFields hinzugefügt zur Verwalung von eigenen Eingabefeldern
- **[CORE]** Support für Extensions hinzugefügt - eine Weiterentwicklung der Addons für die Verwendung mit Codeigniter
- **[CORE]** Infocenter Seite hinzugefügt zur zentralen Verwaltung und ZGV Prüfung von Interessenten
- **[CORE]** Unterstützung für Matrikelnummern und BPK - Anbindung an den Datenverbund
- **[CORE]** Digitale Signatur für Dokumente
- **[FAS]** Direkte Zuordnung von Personen zu Lehreinheiten für Wiederholer, Incoming, etc

### CHANGED
- **[CORE]** Berechtigungsprüfung wurde angepasst damit deaktivierte Benutzer keine Berechtigungen mehr haben
- **[FAS]** Mitarbeiterexport exportiert jetzt nur noch die markierten Personen
- **[CORE]** Has many as possible javascripts and css present in the repository were removed. Their lack is overcome by the packages in the composer. In the meanwhile also the versions were updated
- **[CIS]** Die Fotoliste wird jetzt mit unoconv erstellt. Die bestehende Vorlage für den Dokumentenexport muss hier angepasst werden
- **[CORE]** Resturlaubstage und Mehrarbeitsstunden wurden aus dem Core entfernt und können nicht mehr mit FH-Complete verwaltet werden.
- **[CORE]** Personenzusammenlegen agiert intelligenter und kann direkt aus dem FAS erfolgen.
- **[CIS]** Ampeln können jetzt verpflichtend sein und per Mail benachrichtigen


### Updateinfo
- **[CORE]** Infoscreen wurde umbenannt (informationsbildschirm.php)
- **[CORE]** Moodle Schnittstelle wurde aus dem Core entfernt und in ein eigenes Addon verschoben. Moodle Versionen < 2.4 werden nicht mehr unterstützt
- **[CORE]** Update campus.tbl_templates (contentmittitel and contentohnetitel) with system/templates/contentmittitel_xslt_xhtml.xslt andsystem/templates/contentohnetitel_xslt_xhtml.xslt
- **[CORE]** Neue Style Anweisungen (div.header_logo, td.headerbar, div.cis_logo) müssen in Custom Stylesheet hinzugefügt werden

### Removed
- **[CORE]** Removed Support for XSLFO2PDF Documents - use unoconv instead
- **[CORE]** Removed Support for FOP Documents - use unoconv instead

### Deprecated
- **[CORE]** Die folgenden Datenbank Tabellen und Spalten wurden als DEPRECATED markiert und werden mit der nächsten Version entfernt:
	public.tbl_aufnahmeschluessel, public.tbl_aufnahmetermin, public.tbl_aufnahmetermintyp,	public.tbl_preinteressent,
	public.tbl_preinteressentstudiengang, campus.tbl_feedback, campus.tbl_lvinfo, campus.tbl_resturlaub, lehre.tbl_lehrfach
	lehre.tbl_lehrmittel, lehre.tbl_moodle, lehre.tbl_moodle_version, lehre.tbl_studienordnung_semester, lehre.tbl_zeitfenster
	lehre.tbl_zeugnis, fue.tbl_scrumsprint, fue.tbl_scrumteam, wawi.tbl_aufteilung, wawi.tbl_aufteilung_default,
	testtool.tbl_kategorie, testtool.tbl_kriterien, public.tbl_prestudent.rt_punkte1, public.tbl_prestudent.rt_punkte2
	public.tbl_prestudent.rt_punkte3, public.tbl_prestudent.anmeldungreihungstest, public.tbl_prestudent.reihungstest_id
	public.tbl_prestudent.ausstellungsstaat, public.tbl_prestudent.aufnahmeschluessel, lehre.tbl_lehrveranstaltung.old_lehrfach_id, lehre.tbl_projektarbeit.gesamtstunden, lehre.tbl_projektarbeit.faktor,
	lehre.tbl_projektarbeit.stundensatz
- **[CORE]** LV-Infos werden mit der kommenden Version aus dem Core entfernt - Dies ist jetzt ein Addon
- **[CORE]** WaWi wird mit der kommenden Version aus dem Core entfernt- Dies ist jetzt ein Addon
- **[CIS]** Benotungstool/Kreuzerltool wird mit der kommenden Version aus dem Core entfernt
- **[CIS]** Feedback wird mit der kommenden Version aus dem Core entfernt

## [3.2]

### Added

- **[FAS]** Unterstützung für gemeinsame Studien hinzugefügt
- **[FAS]** Inaktive Mitarbeiter sind jetzt ausgegraut
- **[FAS]** Anmerkungsfeld bei Konto-Buchungen hinzugefügt
- **[CIS]** Lehrveranstaltung Menü auf Studienplan Basis und Modularisiert
- **[CIS]** Ampeln koennen nun verpflichtend sein und Sperren die GUI
- **[FAS]** Bei Mitarbeitern können Notizen erfasst werden
- **[FAS]** Im FAS gibt es einen neuen Karteireiter Messages. Hier können Nachrichten an Studierende übermittelt werden. Die Kommunikation des Aufnahme Addons wird über Messages abgewickelt.
- **[FAS]** Neuer Karteireiter Aufnahmetermine ersetzt die Reihungstestauswahl im Karteireiter Prestudent
- **[CORE]** Es können nun beliebig viele Reihungstests pro Person gespeichert werden.
- **[CORE]** Reihungstests können mehrere Räume zugeteilt werden. Die angemeldeten Studierenden können auf diese Räume aufgeteilt werden. Es gibt dann getrennte Anwesenheitslisten für jeden Raum
- **[CORE]** Reihungstests haben jetzt verschiedene Stufen
- **[CORE]** Reihungstests können Studiensemestern zugeordnet werden
- **[CORE]** Reihungstests können Studienpläne zugeordnet werden. Dies legt fest welche Personen sich zu diesen Reihungstests anmelden können.
- **[CORE]** Es gibt eine Anmeldefrist für Reihungstests
- **[CORE]** Codeigniter Framework wird als neue Basis verwendet.
- **[CORE]** REST API für Zugriff auf alle Daten
- **[CORE]** Neues Phrasenmodul für Orgform spezifische Phrasen
- **[CORE]** Der Zugriff auf Reports kann mitgeloggt werden
- **[CORE]** Der Zugriff auf CMS Seiten kann mitgeloggt werden
- **[FAS]** Es ist möglich bei Statusänderungen im FAS automatisierte Nachrichten an die Studierenden zu senden
- **[FAS]** Bewerberakt - Erstellung eines Akts mit allen hochgeladenen Dokumenten einer Person
- **[FAS]** Bewerbungsfristen für einzelen Studienpläne
- **[FAS]** Beim Statuswechsel kann ein Statusgrund hinterlegt werden.
- **[FAS]** Es kann pro Studiengang hinterlegt werden ob ein Dokument nachreichbar ist oder nicht
- **[FAS]** Arbeitsplätze bei Räumen (zB für EDV Säle mit 50 Plätzen aber nur 25 PCs)
- **[FAS]** Zuteilung von Prestudenten zu Aufnahmegruppen
- **[FAS]** Zusätzliches Feld für Uhrzeit bei Abschlussprüfung
- **[FAS]** Reihungstest Dropdown zeigt verfügbare/belegte Plätze an
- **[CORE]** Reihungstest Punkteübernahme oder Prozentpunkte ist konfigurierbar
- **[CIS]** LVPlan Export für Excel
- **[FAS]** Termine Karteireiter im FAS zeigt die Anmerkung aus dem LVPlan an
- **[CIS]** Onlinebewerbungstool greift auf mehrsprachige Statusbezeichnungen zu.
- **[CIS]** Config-Einträge für die Tabellenspalten beim Eintragen der Gesamtnote hinzugefügt.
- **[CIS]** Prüfungsverwaltung: Config-Eintrag hinzugefügt um die Accordion-Elemente ein- und auszublenden.

### Changed
- **[FAS]** Dokumente Menü im FAS neu sortiert um den Lebenszyklus des Studierenden abzubilden
- **[CORE]** LVPlan Update Mail ist nun zweisprachig Deutsch/Englisch
- **[FAS]** Prüfungen im FAS werden nur noch vom aktuell ausgewählten Studiensemester angezeigt. Umschalten auf volle Ansicht möglich
- **[CIS]** LVPlan Begrenzung der 4er Blockung aufgehoben.
- **[CIS]** Im Menü 'Zeitsperren' Link zu Resturlaubsübersicht entfernt. Falls benötigt Verlinkung über CMS möglich.
- **[CORE]** Removed NOT NULL constraint on 'verfasser\_uid' from public.tbl\_notiz
- **[CIS]** Studienplanansicht: Wenn eine LV nicht benotet ist, aber eine kompatible LV mit vorhandener Anrechnung benotet ist wird diese Note angezeigt.
- **[FAS]** Die RDF-Schnittstelle für das Zeugnis prüft bei Anrechnungen ob, die ECTS-Punkte übereinstimmen und wählt bei ungleichen Werten jene der angerechneten LV.
- **[FAS]** Der Ausbildungsvertrag kann nun jederzeit erstellt werden, auch wenn eine Person noch kein Student ist. Wenn in der Vorlage des Ausbildungsvertrags ein Attribut des Studenten-Datensatzen (zB Personenkennzeichen) abgefragt wird und eine Person noch nicht Student ist, kann dieses nicht angedruckt werden und sollte aus der Vorlage entfernt werden.
- **[CORE]** Check Constraint in der Datenbank für SVNR - Diese muss 10, 12 oder 16 Zeichen lang sein

### Updateinfo
- **[FAS]** Für Lehraufträge muss eine Unoconv-Vorlage erstellt werden, da der für xsl-fo notwendige Seitenumbruch-Tag aus dem RDF entfernt wurde.
- **[FAS]** Mehrsprachigkeitsspalte tbl_status.bezeichnung_mehrsprachig wird durch das Updatescript automatisch in den ersten beiden Sprachen mit der status_kurzbz vorbefüllt. Übersetzungen sind anzupassen.
- **[MOODLE]** Neue Webservicefunktion core_user_update_users wird benötigt
- **[CORE]** Kommune wurde aus Core entfernt
- **[DEPRECATED]]** WaWi wurde in ein Addon (FHC-ADDON-WAWI) ausgelagert. Die Funktionalität im Core wird demnächst entfernt.
- **[DEPRECATED]]** LV-Informationen (FHC-ADDON-LVINFO) wurde in ein Addon ausgelagert. Die Funktionalität im Core wird demnächst entfernt
- **[DEPRECATED]]** Punkte1, Punkte2 und Punkte3 in tbl_prestudent werden nicht mehr verwendet und in zukünftigen Versionen entfernt. Diese werden jetzt in tbl_rt_person gespeichert
- **[DEPRECATED]]** anmeldungreihungstest in tbl_prestudent wird nicht mehr verwendet und in zukünftigen Versionen entfernt
- **[CORE]** Spalte php und r wurde aus tbl_statistik entfernt
- **[DEPRECATED]** Spalte ort_kurzbz in tbl_reihungstest wird nicht mehr verwendet und in zukünftigen Versionen entfernt

Zum Update auf diese Version folgen Sie den Anweisungen auf folgender Seite:
https://wiki.fhcomplete.org/doku.php?id=fh-complete:codeigniter

## [3.1.0] - 2015-11-12
### Added

- **[FAS]** Bei Noten können zusätzlich Punkte gespeichert werden. Notenschlüssel für Gesamtnote kann hinterlegt werden
- **[FAS]** Anwesenheiten von Studierenden können erfasst werden
- **[FAS]** Vertragsverwaltung bei Mitarbeiter
- **[FAS]** Dokumente im FAS können mit SHIFT bzw STRG statt als PDF auch als DOC oder ODT erstellt werden
- **[BERECHTIGUNG]** system/changeoutputformat Legt fest ob Dokumente als DOC/ODT exportiert werden dürfen
- **[FAS]** Termine Karteireiter im FAS zeigt den LVPlan von Studierenden/Lehrveranstaltungen
- **[FAS]** Bereits verplante Lektoren können vom FAS aus, aus dem LVPlan gelöscht werden. Zusätzlich wird im FAS angezeigt ob dieser Lektor bereits verplant ist
- **[FAS]** Bereits verplante Gruppen können direkt vom FAS heraus aus dem LV-Plan gelöscht werden wenn diese bereits verplant wurden.
- **[TEMPUS]** Drop auf Lehrstunde Feature für 2 Gruppen die zur selben Zeit im gleichen Raum unterricht haben (Setzt UNR gleich damit es nicht als kollision angezeigt wird)
- **[TEMPUS]** Option zum Anzeigen von allen Einträgen damit auch Incominggruppen und Gruppen aus anderen Studiengängen sichtbar sind
- **[FAS]** Bei Notizen können jetzt zusätzlich Dokumente hochgeladen werden
- **[CORE]** Bei Dokumentenvorlagen können nun Style und content.xml auf einmal erfasst werden, Dokumente können deaktiviert werden, Eigene GUI im Vilesci zum Verwalten der Dokumentenvorlagen
- **[CORE]** Mehrsprachigkeit bei diversen Tabellen (Dokumente, ZGV, ...) hinzugefügt

### Fixed
- **[TEMPUS]** Kollisionsfreie User werden in Verbandsansicht nicht mehr als Kollision angezeigt

### Changed
- **[FAS]** Stundenobergrenze für Lektoren kann jetzt pro Organisationseinheit festgelegt werden. (warn_semesterstunden_frei/fix tbl_organisationseinheit)
- **[BERECHTIGUNG]** lv-plan/gruppenentfernen Lektorenänderung: Lektoren die bereits verplant sind können jetzt auch dann direkt im FAS geändert werden, wenn dadurch eine Kollision entsteht. Vorraussetzung dafür ist, dass ignore_kollision true ist. Wenn ignore_kollision false ist, dann ist die Lektorenänderung nicht mehr möglich. Vorher wurde der Lektor in diesem Fall nur im FAS geändert aber nicht im LVPlan.
- **[FAS]** Ausstellungsstaat der ZGV wird jetzt getrennt für Bachelor und Master erfasst


## [3.0.0] - 2015-02-13
### Added

- **[CORE]** Studienordnungen / Studienpläne
- **[CORE]** Module
- **[CIS]** CIS Redesign
- **[CORE]** Unterstützung für Addons
- **[FAS]** Notizsystem

### Fixed

- **[CORE]** Diverse Bugfixes
