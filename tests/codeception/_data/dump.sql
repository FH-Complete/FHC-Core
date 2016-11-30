-- Workaround
GRANT SELECT ON TABLE wawi.tbl_konto TO vilesci;
GRANT SELECT ON TABLE campus.tbl_pruefungsstatus TO vilesci;
GRANT SELECT ON TABLE fue.tbl_scrumsprint TO vilesci;
GRANT SELECT ON TABLE fue.tbl_scrumteam TO vilesci;

-- INSERT INTO ci_apikey
INSERT INTO public.ci_apikey (key, level, ignore_limits, date_created) VALUES ('testapikey@fhcomplete.org', NULL, NULL, NOW());

-- DELETE FROM system.tbl_rolleberechtigung
DELETE FROM system.tbl_rolleberechtigung WHERE berechtigung_kurzbz IN (
	'basis/archiv', 
	'basis/ausbildung', 
	'basis/berufstaetigkeit', 
	'basis/beschaeftigungsausmass', 
	'basis/besqual', 
	'basis/bisfunktion', 
	'basis/bisio', 
	'basis/bisorgform', 
	'basis/bisverwendung', 
	'basis/bundesland', 
	'basis/entwicklungsteam', 
	'basis/gemeinde', 
	'basis/hauptberuf', 
	'basis/lgartcode', 
	'basis/mobilitaetsprogramm', 
	'basis/nation', 
	'basis/orgform', 
	'basis/verwendung', 
	'basis/zgv', 
	'basis/zgvdoktor', 
	'basis/zgvgruppe', 
	'basis/zgvmaster', 
	'basis/zweck', 
	'basis/abgabe', 
	'basis/anwesenheit', 
	'basis/beispiel', 
	'basis/content', 
	'basis/contentchild', 
	'basis/contentgruppe', 
	'basis/contentlog', 
	'basis/contentsprache', 
	'basis/coodle', 
	'basis/dms', 
	'basis/erreichbarkeit', 
	'basis/feedback', 
	'basis/freebusy', 
	'basis/freebusytyp', 
	'basis/infoscreen', 
	'basis/legesamtnote', 
	'basis/lvgesamtnote', 
	'basis/lvinfo', 
	'basis/news', 
	'basis/notenschluessel', 
	'basis/notenschluesseluebung', 
	'basis/paabgabe', 
	'basis/paabgabetyp', 
	'basis/pruefung', 
	'basis/pruefungsanmeldung', 
	'basis/pruefungsfenster', 
	'basis/pruefungsstatus', 
	'basis/pruefungstermin', 
	'basis/reservierung', 
	'basis/resturlaub', 
	'basis/studentbeispiel', 
	'basis/studentuebung', 
	'basis/template', 
	'basis/uebung', 
	'basis/veranstaltung', 
	'basis/veranstaltungskategorie', 
	'basis/zeitaufzeichnung', 
	'basis/zeitsperre', 
	'basis/zeitsperretyp', 
	'basis/zeitwunsch', 
	'basis/aktivitaet', 
	'basis/aufwandstyp', 
	'basis/projekt', 
	'basis/projekt_ressource', 
	'basis/projektphase', 
	'basis/projekttask', 
	'basis/ressource', 
	'basis/scrumsprint', 
	'basis/scrumteam', 
	'basis/abschlussbeurteilung', 
	'basis/abschlusspruefung', 
	'basis/akadgrad', 
	'basis/anrechnung', 
	'basis/betreuerart', 
	'basis/ferien', 
	'basis/lehreinheit', 
	'basis/lehreinheitgruppe', 
	'basis/lehreinheitmitarbeiter', 
	'basis/lehrfach', 
	'basis/lehrform', 
	'basis/lehrfunktion', 
	'basis/lehrmittel', 
	'basis/lehrtyp', 
	'basis/lehrveranstaltung', 
	'basis/lvangebot', 
	'basis/lvregel', 
	'basis/lvregeltyp', 
	'basis/moodle', 
	'basis/note', 
	'basis/notenschluesselaufteilung', 
	'basis/notenschluesselzuordnung', 
	'basis/projektarbeit', 
	'basis/projektbetreuer', 
	'basis/projekttyp', 
	'basis/pruefungstyp', 
	'lehre/studienordnung', 
	'lehre/studienordnungstatus', 
	'lehre/studienplan', 
	'basis/studienplatz', 
	'basis/stunde', 
	'basis/stundenplan', 
	'basis/stundenplandev', 
	'basis/vertrag', 
	'basis/vertragsstatus', 
	'basis/vertragstyp', 
	'basis/zeitfenster', 
	'basis/zeugnis', 
	'basis/zeugnisnote', 
	'basis/adresse', 
	'basis/akte', 
	'basis/ampel', 
	'basis/aufmerksamdurch', 
	'basis/aufnahmeschluessel', 
	'basis/aufnahmetermin', 
	'basis/aufnahmetermintyp', 
	'basis/bankverbindung', 
	'basis/benutzer', 
	'basis/benutzerfunktion', 
	'basis/benutzergruppe', 
	'basis/bewerbungstermine', 
	'basis/buchungstyp', 
	'basis/dokument', 
	'basis/dokumentprestudent', 
	'basis/dokumentstudiengang', 
	'basis/erhalter', 
	'basis/fachbereich', 
	'basis/filter', 
	'basis/firma', 
	'basis/firmatag', 
	'basis/firmentyp', 
	'basis/fotostatus', 
	'basis/funktion', 
	'basis/geschaeftsjahr', 
	'basis/gruppe', 
	'basis/kontakt', 
	'basis/kontaktmedium', 
	'basis/kontakttyp', 
	'basis/konto', 
	'basis/lehrverband', 
	'basis/log', 
	'basis/mitarbeiter', 
	'basis/msg_message',
	'basis/message',
	'basis/msg_thread', 
	'basis/notiz', 
	'basis/notizzuordnung', 
	'basis/organisationseinheit', 
	'basis/organisationseinheittyp', 
	'basis/ort', 
	'basis/ortraumtyp', 
	'basis/person', 
	'basis/personfunktionstandort', 
	'basis/preincoming', 
	'basis/preinteressent', 
	'basis/preinteressentstudiengang', 
	'basis/preoutgoing', 
	'basis/prestudent', 
	'basis/prestudentstatus', 
	'basis/raumtyp', 
	'basis/reihungstest', 
	'basis/semesterwochen', 
	'basis/service', 
	'basis/sprache', 
	'basis/standort', 
	'basis/statistik', 
	'basis/status', 
	'basis/student', 
	'basis/studentlehrverband', 
	'basis/studiengang', 
	'basis/studiengangstyp', 
	'basis/studienjahr', 
	'basis/studiensemester', 
	'basis/tag', 
	'basis/variable', 
	'basis/vorlage', 
	'basis/vorlagestudiengang', 
	'basis/appdaten', 
	'basis/benutzerrolle', 
	'basis/berechtigung', 
	'basis/cronjob', 
	'basis/rolle', 
	'basis/rolleberechtigung', 
	'basis/server', 
	'basis/webservicelog', 
	'basis/webservicerecht', 
	'basis/webservicetyp', 
	'basis/ablauf', 
	'basis/antwort', 
	'basis/frage', 
	'basis/gebiet', 
	'basis/kategorie', 
	'basis/kriterien', 
	'basis/pruefling', 
	'basis/vorschlag', 
	'basis/aufteilung', 
	'basis/bestelldetail', 
	'basis/bestelldetailtag', 
	'basis/bestellstatus', 
	'basis/bestellung', 
	'basis/bestellungtag', 
	'basis/betriebsmittel', 
	'basis/betriebsmittelperson', 
	'basis/betriebsmittelstatus', 
	'basis/betriebsmitteltyp', 
	'basis/buchung', 
	'basis/budget', 
	'basis/kostenstelle', 
	'basis/rechnung', 
	'basis/rechnungsbetrag', 
	'basis/rechnungstyp', 
	'basis/zahlungstyp',
	'lehre/studienplan_semester',
	'basis/dms_version',
	'student/stammdaten',
	'mitarbeiter/stammdaten',
	'lehre/vw_studiensemester',
	'lehre/reservierung',
	'lehre/reihungstest',
	'wawi/inventar:begrenzt',
	'fs/dms',
	'system/phrase',
	'system/vorlagestudiengang',
	'system/vorlage',
	'system/appdaten',
	'system/PhrasesLib'
);

-- DELETE FROM system.tbl_berechtigung
DELETE FROM system.tbl_berechtigung WHERE berechtigung_kurzbz IN (
	'basis/archiv', 
	'basis/ausbildung', 
	'basis/berufstaetigkeit', 
	'basis/beschaeftigungsausmass', 
	'basis/besqual', 
	'basis/bisfunktion', 
	'basis/bisio', 
	'basis/bisorgform', 
	'basis/bisverwendung', 
	'basis/bundesland', 
	'basis/entwicklungsteam', 
	'basis/gemeinde', 
	'basis/hauptberuf', 
	'basis/lgartcode', 
	'basis/mobilitaetsprogramm', 
	'basis/nation', 
	'basis/orgform', 
	'basis/verwendung', 
	'basis/zgv', 
	'basis/zgvdoktor', 
	'basis/zgvgruppe', 
	'basis/zgvmaster', 
	'basis/zweck', 
	'basis/abgabe', 
	'basis/anwesenheit', 
	'basis/beispiel', 
	'basis/content', 
	'basis/contentchild', 
	'basis/contentgruppe', 
	'basis/contentlog', 
	'basis/contentsprache', 
	'basis/coodle', 
	'basis/dms', 
	'basis/erreichbarkeit', 
	'basis/feedback', 
	'basis/freebusy', 
	'basis/freebusytyp', 
	'basis/infoscreen', 
	'basis/legesamtnote', 
	'basis/lvgesamtnote', 
	'basis/lvinfo', 
	'basis/news', 
	'basis/notenschluessel', 
	'basis/notenschluesseluebung', 
	'basis/paabgabe', 
	'basis/paabgabetyp', 
	'basis/pruefung', 
	'basis/pruefungsanmeldung', 
	'basis/pruefungsfenster', 
	'basis/pruefungsstatus', 
	'basis/pruefungstermin', 
	'basis/reservierung', 
	'basis/resturlaub', 
	'basis/studentbeispiel', 
	'basis/studentuebung', 
	'basis/template', 
	'basis/uebung', 
	'basis/veranstaltung', 
	'basis/veranstaltungskategorie', 
	'basis/zeitaufzeichnung', 
	'basis/zeitsperre', 
	'basis/zeitsperretyp', 
	'basis/zeitwunsch', 
	'basis/aktivitaet', 
	'basis/aufwandstyp', 
	'basis/projekt', 
	'basis/projekt_ressource', 
	'basis/projektphase', 
	'basis/projekttask', 
	'basis/ressource', 
	'basis/scrumsprint', 
	'basis/scrumteam', 
	'basis/abschlussbeurteilung', 
	'basis/abschlusspruefung', 
	'basis/akadgrad', 
	'basis/anrechnung', 
	'basis/betreuerart', 
	'basis/ferien', 
	'basis/lehreinheit', 
	'basis/lehreinheitgruppe', 
	'basis/lehreinheitmitarbeiter', 
	'basis/lehrfach', 
	'basis/lehrform', 
	'basis/lehrfunktion', 
	'basis/lehrmittel', 
	'basis/lehrtyp', 
	'basis/lehrveranstaltung', 
	'basis/lvangebot', 
	'basis/lvregel', 
	'basis/lvregeltyp', 
	'basis/moodle', 
	'basis/note', 
	'basis/notenschluesselaufteilung', 
	'basis/notenschluesselzuordnung', 
	'basis/projektarbeit', 
	'basis/projektbetreuer', 
	'basis/projekttyp', 
	'basis/pruefungstyp', 
	'lehre/studienordnung', 
	'lehre/studienordnungstatus', 
	'lehre/studienplan', 
	'basis/studienplatz', 
	'basis/stunde', 
	'basis/stundenplan', 
	'basis/stundenplandev', 
	'basis/vertrag', 
	'basis/vertragsstatus', 
	'basis/vertragstyp', 
	'basis/zeitfenster', 
	'basis/zeugnis', 
	'basis/zeugnisnote', 
	'basis/adresse', 
	'basis/akte', 
	'basis/ampel', 
	'basis/aufmerksamdurch', 
	'basis/aufnahmeschluessel', 
	'basis/aufnahmetermin', 
	'basis/aufnahmetermintyp', 
	'basis/bankverbindung', 
	'basis/benutzer', 
	'basis/benutzerfunktion', 
	'basis/benutzergruppe', 
	'basis/bewerbungstermine', 
	'basis/buchungstyp', 
	'basis/dokument', 
	'basis/dokumentprestudent', 
	'basis/dokumentstudiengang', 
	'basis/erhalter', 
	'basis/fachbereich', 
	'basis/filter', 
	'basis/firma', 
	'basis/firmatag', 
	'basis/firmentyp', 
	'basis/fotostatus', 
	'basis/funktion', 
	'basis/geschaeftsjahr', 
	'basis/gruppe', 
	'basis/kontakt', 
	'basis/kontaktmedium', 
	'basis/kontakttyp', 
	'basis/konto', 
	'basis/lehrverband', 
	'basis/log', 
	'basis/mitarbeiter', 
	'basis/msg_message', 
	'basis/message',
	'basis/msg_thread', 
	'basis/notiz', 
	'basis/notizzuordnung', 
	'basis/organisationseinheit', 
	'basis/organisationseinheittyp', 
	'basis/ort', 
	'basis/ortraumtyp', 
	'basis/person', 
	'basis/personfunktionstandort', 
	'basis/preincoming', 
	'basis/preinteressent', 
	'basis/preinteressentstudiengang', 
	'basis/preoutgoing', 
	'basis/prestudent', 
	'basis/prestudentstatus', 
	'basis/raumtyp', 
	'basis/reihungstest', 
	'basis/semesterwochen', 
	'basis/service', 
	'basis/sprache', 
	'basis/standort', 
	'basis/statistik', 
	'basis/status', 
	'basis/student', 
	'basis/studentlehrverband', 
	'basis/studiengang', 
	'basis/studiengangstyp', 
	'basis/studienjahr', 
	'basis/studiensemester', 
	'basis/tag', 
	'basis/variable', 
	'basis/vorlage', 
	'basis/vorlagestudiengang', 
	'basis/appdaten', 
	'basis/benutzerrolle', 
	'basis/berechtigung', 
	'basis/cronjob', 
	'basis/rolle', 
	'basis/rolleberechtigung', 
	'basis/server', 
	'basis/webservicelog', 
	'basis/webservicerecht', 
	'basis/webservicetyp', 
	'basis/ablauf', 
	'basis/antwort', 
	'basis/frage', 
	'basis/gebiet', 
	'basis/kategorie', 
	'basis/kriterien', 
	'basis/pruefling', 
	'basis/vorschlag', 
	'basis/aufteilung', 
	'basis/bestelldetail', 
	'basis/bestelldetailtag', 
	'basis/bestellstatus', 
	'basis/bestellung', 
	'basis/bestellungtag', 
	'basis/betriebsmittel', 
	'basis/betriebsmittelperson', 
	'basis/betriebsmittelstatus', 
	'basis/betriebsmitteltyp', 
	'basis/buchung', 
	'basis/budget', 
	'basis/kostenstelle', 
	'basis/rechnung', 
	'basis/rechnungsbetrag', 
	'basis/rechnungstyp', 
	'basis/zahlungstyp',
	'lehre/studienplan_semester',
	'basis/dms_version',
	'student/stammdaten',
	'mitarbeiter/stammdaten',
	'lehre/vw_studiensemester',
	'lehre/reservierung',
	'lehre/reihungstest',
	'wawi/inventar:begrenzt',
	'fs/dms',
	'system/phrase',
	'system/vorlagestudiengang',
	'system/vorlage',
	'system/appdaten',
	'system/PhrasesLib'
);

-- INSERT Permissions
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/archiv', 'Tbl_archiv');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ausbildung', 'Tbl_ausbildung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/berufstaetigkeit', 'Tbl_berufstaetigkeit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/beschaeftigungsausmass', 'Tbl_beschaeftigungsausmass');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/besqual', 'Tbl_besqual');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisfunktion', 'Tbl_bisfunktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisio', 'Tbl_bisio');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisorgform', 'Tbl_bisorgform');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bisverwendung', 'Tbl_bisverwendung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bundesland', 'Tbl_bundesland');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/entwicklungsteam', 'Tbl_entwicklungsteam');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/gemeinde', 'Tbl_gemeinde');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/hauptberuf', 'Tbl_hauptberuf');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lgartcode', 'Tbl_lgartcode');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/mobilitaetsprogramm', 'Tbl_mobilitaetsprogramm');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/nation', 'Tbl_nation');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/orgform', 'Tbl_orgform');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/verwendung', 'Tbl_verwendung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgv', 'Tbl_zgv');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgvdoktor', 'Tbl_zgvdoktor');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgvgruppe', 'Tbl_zgvgruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zgvmaster', 'Tbl_zgvmaster');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zweck', 'Tbl_zweck');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/abgabe', 'Tbl_abgabe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/anwesenheit', 'Tbl_anwesenheit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/beispiel', 'Tbl_beispiel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/content', 'Tbl_content');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentchild', 'Tbl_contentchild');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentgruppe', 'Tbl_contentgruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentlog', 'Tbl_contentlog');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/contentsprache', 'Tbl_contentsprache');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/coodle', 'Tbl_coodle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dms', 'Tbl_dms');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/erreichbarkeit', 'Tbl_erreichbarkeit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/feedback', 'Tbl_feedback');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/freebusy', 'Tbl_freebusy');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/freebusytyp', 'Tbl_freebusytyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/infoscreen', 'Tbl_infoscreen');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/legesamtnote', 'Tbl_legesamtnote');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvgesamtnote', 'Tbl_lvgesamtnote');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvinfo', 'Tbl_lvinfo');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/news', 'Tbl_news');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluessel', 'Tbl_notenschluessel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluesseluebung', 'Tbl_notenschluesseluebung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/paabgabe', 'Tbl_paabgabe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/paabgabetyp', 'Tbl_paabgabetyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefung', 'Tbl_pruefung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungsanmeldung', 'Tbl_pruefungsanmeldung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungsfenster', 'Tbl_pruefungsfenster');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungsstatus', 'Tbl_pruefungsstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungstermin', 'Tbl_pruefungstermin');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/reservierung', 'Tbl_reservierung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/resturlaub', 'Tbl_resturlaub');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studentbeispiel', 'Tbl_studentbeispiel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studentuebung', 'Tbl_studentuebung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/template', 'Tbl_template');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/uebung', 'Tbl_uebung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/veranstaltung', 'Tbl_veranstaltung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/veranstaltungskategorie', 'Tbl_veranstaltungskategorie');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitaufzeichnung', 'Tbl_zeitaufzeichnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitsperre', 'Tbl_zeitsperre');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitsperretyp', 'Tbl_zeitsperretyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitwunsch', 'Tbl_zeitwunsch');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aktivitaet', 'Tbl_aktivitaet');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufwandstyp', 'Tbl_aufwandstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekt', 'Tbl_projekt');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekt_ressource', 'Tbl_projekt_ressource');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projektphase', 'Tbl_projektphase');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekttask', 'Tbl_projekttask');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ressource', 'Tbl_ressource');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/scrumsprint', 'Tbl_scrumsprint');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/scrumteam', 'Tbl_scrumteam');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/abschlussbeurteilung', 'Tbl_abschlussbeurteilung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/abschlusspruefung', 'Tbl_abschlusspruefung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/akadgrad', 'Tbl_akadgrad');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/anrechnung', 'Tbl_anrechnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betreuerart', 'Tbl_betreuerart');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ferien', 'Tbl_ferien');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehreinheit', 'Tbl_lehreinheit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehreinheitgruppe', 'Tbl_lehreinheitgruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehreinheitmitarbeiter', 'Tbl_lehreinheitmitarbeiter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrfach', 'Tbl_lehrfach');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrform', 'Tbl_lehrform');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrfunktion', 'Tbl_lehrfunktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrmittel', 'Tbl_lehrmittel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrtyp', 'Tbl_lehrtyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrveranstaltung', 'Tbl_lehrveranstaltung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvangebot', 'Tbl_lvangebot');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvregel', 'Tbl_lvregel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lvregeltyp', 'Tbl_lvregeltyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/moodle', 'Tbl_moodle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/note', 'Tbl_note');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluesselaufteilung', 'Tbl_notenschluesselaufteilung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notenschluesselzuordnung', 'Tbl_notenschluesselzuordnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projektarbeit', 'Tbl_projektarbeit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projektbetreuer', 'Tbl_projektbetreuer');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/projekttyp', 'Tbl_projekttyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefungstyp', 'Tbl_pruefungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/studienordnung', 'Tbl_studienordnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/studienordnungstatus', 'Tbl_studienordnungstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/studienplan', 'Tbl_studienplan');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienplatz', 'Tbl_studienplatz');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/stunde', 'Tbl_stunde');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/stundenplan', 'Tbl_stundenplan');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/stundenplandev', 'Tbl_stundenplandev');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vertrag', 'Tbl_vertrag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vertragsstatus', 'Tbl_vertragsstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vertragstyp', 'Tbl_vertragstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeitfenster', 'Tbl_zeitfenster');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeugnis', 'Tbl_zeugnis');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zeugnisnote', 'Tbl_zeugnisnote');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/adresse', 'Tbl_adresse');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/akte', 'Tbl_akte');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ampel', 'Tbl_ampel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufmerksamdurch', 'Tbl_aufmerksamdurch');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufnahmeschluessel', 'Tbl_aufnahmeschluessel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufnahmetermin', 'Tbl_aufnahmetermin');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufnahmetermintyp', 'Tbl_aufnahmetermintyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bankverbindung', 'Tbl_bankverbindung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzer', 'Tbl_benutzer');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzerfunktion', 'Tbl_benutzerfunktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzergruppe', 'Tbl_benutzergruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bewerbungstermine', 'Tbl_bewerbungstermine');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/buchungstyp', 'Tbl_buchungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dokument', 'Tbl_dokument');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dokumentprestudent', 'Tbl_dokumentprestudent');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dokumentstudiengang', 'Tbl_dokumentstudiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/erhalter', 'Tbl_erhalter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/fachbereich', 'Tbl_fachbereich');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/filter', 'Tbl_filter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/firma', 'Tbl_firma');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/firmatag', 'Tbl_firmatag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/firmentyp', 'Tbl_firmentyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/fotostatus', 'Tbl_fotostatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/funktion', 'Tbl_funktion');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/geschaeftsjahr', 'Tbl_geschaeftsjahr');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/gruppe', 'Tbl_gruppe');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kontakt', 'Tbl_kontakt');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kontaktmedium', 'Tbl_kontaktmedium');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kontakttyp', 'Tbl_kontakttyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/konto', 'Tbl_konto');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/lehrverband', 'Tbl_lehrverband');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/log', 'Tbl_log');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/mitarbeiter', 'Tbl_mitarbeiter');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/msg_message', 'Tbl_msg_message');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/msg_thread', 'Tbl_msg_thread');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notiz', 'Tbl_notiz');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/notizzuordnung', 'Tbl_notizzuordnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/organisationseinheit', 'Tbl_organisationseinheit');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/organisationseinheittyp', 'Tbl_organisationseinheittyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ort', 'Tbl_ort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ortraumtyp', 'Tbl_ortraumtyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/person', 'Tbl_person');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/personfunktionstandort', 'Tbl_personfunktionstandort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preincoming', 'Tbl_preincoming');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preinteressent', 'Tbl_preinteressent');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preinteressentstudiengang', 'Tbl_preinteressentstudiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/preoutgoing', 'Tbl_preoutgoing');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/prestudent', 'Tbl_prestudent');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/prestudentstatus', 'Tbl_prestudentstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/raumtyp', 'Tbl_raumtyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/reihungstest', 'Tbl_reihungstest');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/semesterwochen', 'Tbl_semesterwochen');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/service', 'Tbl_service');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/sprache', 'Tbl_sprache');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/standort', 'Tbl_standort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/statistik', 'Tbl_statistik');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/status', 'Tbl_status');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/student', 'Tbl_student');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studentlehrverband', 'Tbl_studentlehrverband');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studiengang', 'Tbl_studiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studiengangstyp', 'Tbl_studiengangstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studienjahr', 'Tbl_studienjahr');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/studiensemester', 'Tbl_studiensemester');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/tag', 'Tbl_tag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/variable', 'Tbl_variable');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vorlage', 'Tbl_vorlage');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vorlagestudiengang', 'Tbl_vorlagestudiengang');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/appdaten', 'Tbl_appdaten');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/benutzerrolle', 'Tbl_benutzerrolle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/berechtigung', 'Tbl_berechtigung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/cronjob', 'Tbl_cronjob');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rolle', 'Tbl_rolle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rolleberechtigung', 'Tbl_rolleberechtigung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/server', 'Tbl_server');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/webservicelog', 'Tbl_webservicelog');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/webservicerecht', 'Tbl_webservicerecht');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/webservicetyp', 'Tbl_webservicetyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/ablauf', 'Tbl_ablauf');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/antwort', 'Tbl_antwort');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/frage', 'Tbl_frage');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/gebiet', 'Tbl_gebiet');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kategorie', 'Tbl_kategorie');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kriterien', 'Tbl_kriterien');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/pruefling', 'Tbl_pruefling');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/vorschlag', 'Tbl_vorschlag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/aufteilung', 'Tbl_aufteilung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestelldetail', 'Tbl_bestelldetail');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestelldetailtag', 'Tbl_bestelldetailtag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestellstatus', 'Tbl_bestellstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestellung', 'Tbl_bestellung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/bestellungtag', 'Tbl_bestellungtag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmittel', 'Tbl_betriebsmittel');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmittelperson', 'Tbl_betriebsmittelperson');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmittelstatus', 'Tbl_betriebsmittelstatus');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/betriebsmitteltyp', 'Tbl_betriebsmitteltyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/buchung', 'Tbl_buchung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/budget', 'Tbl_budget');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/kostenstelle', 'Tbl_kostenstelle');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rechnung', 'Tbl_rechnung');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rechnungsbetrag', 'Tbl_rechnungsbetrag');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/rechnungstyp', 'Tbl_rechnungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/zahlungstyp', 'Tbl_zahlungstyp');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/studienplan_semester', 'Tbl_studienplan_semester');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/dms_version', 'Tbl_dms_version');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('student/stammdaten', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('mitarbeiter/stammdaten', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/vw_studiensemester', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/reservierung', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('lehre/reihungstest', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('wawi/inventar:begrenzt', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('fs/dms', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('basis/message', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/phrase', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/vorlagestudiengang', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/vorlage', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/appdaten', '');
INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES('system/PhrasesLib', '');

-- INSERT link between user admin and permissions
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/archiv', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ausbildung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/berufstaetigkeit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/beschaeftigungsausmass', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/besqual', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisfunktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisio', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisorgform', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bisverwendung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bundesland', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/entwicklungsteam', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/gemeinde', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/hauptberuf', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lgartcode', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/mobilitaetsprogramm', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/nation', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/orgform', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/verwendung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgv', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgvdoktor', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgvgruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zgvmaster', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zweck', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/abgabe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/anwesenheit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/beispiel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/content', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentchild', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentgruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentlog', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/contentsprache', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/coodle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dms', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/erreichbarkeit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/feedback', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/freebusy', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/freebusytyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/infoscreen', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/legesamtnote', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvgesamtnote', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvinfo', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/news', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluessel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluesseluebung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/paabgabe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/paabgabetyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungsanmeldung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungsfenster', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungsstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungstermin', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/reservierung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/resturlaub', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studentbeispiel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studentuebung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/template', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/uebung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/veranstaltung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/veranstaltungskategorie', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitaufzeichnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitsperre', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitsperretyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitwunsch', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aktivitaet', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufwandstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekt', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekt_ressource', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projektphase', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekttask', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ressource', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/scrumsprint', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/scrumteam', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/abschlussbeurteilung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/abschlusspruefung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/akadgrad', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/anrechnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betreuerart', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ferien', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehreinheit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehreinheitgruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehreinheitmitarbeiter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrfach', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrform', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrfunktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrmittel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrtyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrveranstaltung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvangebot', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvregel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lvregeltyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/moodle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/note', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluesselaufteilung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notenschluesselzuordnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projektarbeit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projektbetreuer', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/projekttyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/studienordnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/studienordnungstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/studienplan', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienplatz', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/stunde', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/stundenplan', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/stundenplandev', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vertrag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vertragsstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vertragstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeitfenster', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeugnis', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zeugnisnote', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/adresse', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/akte', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ampel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufmerksamdurch', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufnahmeschluessel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufnahmetermin', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufnahmetermintyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bankverbindung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzer', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzerfunktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzergruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bewerbungstermine', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/buchungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dokument', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dokumentprestudent', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dokumentstudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/erhalter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/fachbereich', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/filter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/firma', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/firmatag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/firmentyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/fotostatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/funktion', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/geschaeftsjahr', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/gruppe', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kontakt', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kontaktmedium', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kontakttyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/konto', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/lehrverband', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/log', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/mitarbeiter', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/msg_message', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/msg_thread', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notiz', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/notizzuordnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/organisationseinheit', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/organisationseinheittyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ortraumtyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/person', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/personfunktionstandort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preincoming', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preinteressent', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preinteressentstudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/preoutgoing', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/prestudent', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/prestudentstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/raumtyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/reihungstest', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/semesterwochen', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/service', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/sprache', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/standort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/statistik', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/status', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/student', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studentlehrverband', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studiengangstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studienjahr', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/studiensemester', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/tag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/variable', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vorlage', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vorlagestudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/appdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/benutzerrolle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/berechtigung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/cronjob', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rolle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rolleberechtigung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/server', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/webservicelog', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/webservicerecht', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/webservicetyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/ablauf', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/antwort', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/frage', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/gebiet', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kategorie', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kriterien', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/pruefling', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/vorschlag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/aufteilung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestelldetail', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestelldetailtag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestellstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestellung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/bestellungtag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmittel', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmittelperson', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmittelstatus', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/betriebsmitteltyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/buchung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/budget', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/kostenstelle', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rechnung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rechnungsbetrag', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/rechnungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/zahlungstyp', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/studienplan_semester', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/dms_version', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('student/stammdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('mitarbeiter/stammdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/vw_studiensemester', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/reservierung', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('lehre/reihungstest', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('wawi/inventar:begrenzt', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('fs/dms', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('basis/message', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('system/phrase', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('system/vorlagestudiengang', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('system/vorlage', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('system/appdaten', 'admin', 'suid');
INSERT INTO system.tbl_rolleberechtigung (berechtigung_kurzbz, rolle_kurzbz, art) VALUES('system/PhrasesLib', 'admin', 'suid');

-- UPDATE tbl_studiengang
UPDATE public.tbl_studiengang SET onlinebewerbung = TRUE;

-- EMPTY public.tbl_preinteressent
DELETE FROM public.tbl_preinteressent;
-- EMPTY public.tbl_prestudentstatus
DELETE FROM public.tbl_prestudentstatus;
-- EMPTY public.tbl_prestudent
DELETE FROM public.tbl_prestudent;
-- EMPTY lehre.tbl_studienplan
DELETE FROM lehre.tbl_studienplan_semester;
-- EMPTY lehre.tbl_studienplan
DELETE FROM lehre.tbl_studienplan;
-- EMPTY lehre.tbl_studienordnung_semester
DELETE FROM lehre.tbl_studienordnung_semester;
-- EMPTY lehre.tbl_studienordnung
DELETE FROM lehre.tbl_studienordnung;
-- EMPTY public.tbl_studienjahr
DELETE FROM public.tbl_studienjahr;
-- EMPTY public.tbl_ort
DELETE FROM public.tbl_ort;
-- EMPTY public.tbl_kontakt
DELETE FROM public.tbl_kontakt WHERE person_id > 2;
-- EMPTY public.tbl_benutzer
DELETE FROM public.tbl_benutzer WHERE person_id > 2;
-- EMPTY public.tbl_preinteressent
DELETE FROM public.tbl_preinteressent WHERE person_id > 2;
-- EMPTY public.tbl_person
DELETE FROM public.tbl_person WHERE person_id > 2;

-- INSERT Persons (public.tbl_person)
INSERT INTO public.tbl_person VALUES (3, NULL, NULL, NULL, NULL, NULL, NULL, 'McKenzie', 'Vicenta', 'Abraham', '2002-12-30', 'Brooksburgh', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'm', NULL, true, '2016-03-27 22:23:20.624239', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '01234567A', false);