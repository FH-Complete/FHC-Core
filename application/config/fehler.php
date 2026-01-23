<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

$config['fehler'] = array(
	array(
		'fehlercode' => 'CORE_ZGV_0001',
		'fehler_kurzbz' => 'zgvDatumInZukunft',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Datum in Zukunft',
		'fehlertyp_kurzbz' => 'error',
		'app' => array('core'),
		'producerLibName' => null,
		'resolverLibName' => 'CORE_ZGV_0001',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_ZGV_0002',
		'fehler_kurzbz' => 'zgvDatumVorGeburtsdatum',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Datum vor Geburtsdatum',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_ZGV_0002',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_ZGV_0003',
		'fehler_kurzbz' => 'zgvMasterDatumInZukunft',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Masterdatum in Zukunft',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_ZGV_0003',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_ZGV_0004',
		'fehler_kurzbz' => 'zgvMasterDatumVorZgvdatum',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Masterdatum vor Zgvdatum',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_ZGV_0004',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_ZGV_0005',
		'fehler_kurzbz' => 'zgvMasterDatumVorGeburtsdatum',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Masterdatum vor Geburtsdatum',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_ZGV_0005',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0001',
		'fehler_kurzbz' => 'keinAufenthaltszweckPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Aufenthaltszweck gefunden',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_INOUT_0001',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0002',
		'fehler_kurzbz' => 'zuVieleZweckeIncomingPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es sind %s Aufenthaltszwecke eingetragen (max. 1 Zweck für Incomings)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_INOUT_0002',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0003',
		'fehler_kurzbz' => 'falscherIncomingZweckPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aufenthaltszweckcode ist %s (für Incomings ist nur Zweck 1, 2, 3 erlaubt)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_INOUT_0003',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0004',
		'fehler_kurzbz' => 'outgoingAufenthaltfoerderungfehltPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Keine Aufenthaltsfoerderung angegeben (bei Outgoings >= 29 Tage Monat im Ausland muss mind. 1 gemeldet werden)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_INOUT_0004',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0005',
		'fehler_kurzbz' => 'outgoingAngerechneteEctsFehlenPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Angerechnete ECTS fehlen (Meldepflicht bei Outgoings >= 29 Tage Monat im Ausland)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_INOUT_0005',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0006',
		'fehler_kurzbz' => 'outgoingErworbeneEctsFehlenPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Erworbene ECTS fehlen (Meldepflicht bei Outgoings >= 29 Tage Monat im Ausland)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_INOUT_0006',
		'producerIsResolver' => false
	),

	/** Plausichecks **/
	array(
		'fehlercode' => 'CORE_INOUT_0007',
		'fehler_kurzbz' => 'IncomingHeimatNationOesterreich',
		'fehlercode_extern' => null,
		'fehlertext' => 'Heimatnation bei Incoming Österreich',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'IncomingHeimatNationOesterreich',
		'resolverLibName' => 'CORE_INOUT_0007',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0008',
		'fehler_kurzbz' => 'IncomingOhneIoDatensatz',
		'fehlercode_extern' => null,
		'fehlertext' => 'Incoming hat keinen IO Datensatz (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'IncomingOhneIoDatensatz',
		'resolverLibName' => 'CORE_INOUT_0008',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_INOUT_0009',
		'fehler_kurzbz' => 'IncomingOrGsFoerderrelevant',
		'fehlercode_extern' => null,
		'fehlertext' => 'Incoming oder gemeinsames Studium ist nicht als nicht förderrelevant markiert. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'IncomingOrGsFoerderrelevant',
		'resolverLibName' => 'CORE_INOUT_0009',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STG_0001',
		'fehler_kurzbz' => 'StgPrestudentUngleichStgStudent',
		'fehlercode_extern' => null,
		'fehlertext' => 'Studiengang des Prestudenten ist ungleich dem Studiengang des Studenten. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'StgPrestudentUngleichStgStudent',
		'resolverLibName' => 'CORE_STG_0001',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STG_0002',
		'fehler_kurzbz' => 'OrgformStgUngleichOrgformPrestudent',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es ist kein Studienplan mit Studiengang (%s) und Organisationsform (%s) des Studenten zugewiesen. (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'OrgformStgUngleichOrgformPrestudent',
		'resolverLibName' => 'CORE_STG_0002',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STG_0003',
		'fehler_kurzbz' => 'PrestudentMischformOhneOrgform',
		'fehlercode_extern' => null,
		'fehlertext' => 'Organisationsform ist für Studierenden/BewerberIn in Mischformstudiengang nicht eingetragen. (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'PrestudentMischformOhneOrgform',
		'resolverLibName' => 'CORE_STG_0003',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STG_0004',
		'fehler_kurzbz' => 'StgPrestudentUngleichStgStudienplan',
		'fehlercode_extern' => null,
		'fehlertext' => 'Studiengang des Prestudenten passt nicht zu Studiengang des Studienplans. (prestudent_id %s, Studienplan %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'StgPrestudentUngleichStgStudienplan',
		'resolverLibName' => 'CORE_STG_0004',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0001',
		'fehler_kurzbz' => 'AbbrecherAktiv',
		'fehlercode_extern' => null,
		'fehlertext' => 'AbbrecherIn hat aktiven Benutzer. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'AbbrecherAktiv',
		'resolverLibName' => null,
		'producerIsResolver' => true
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0002',
		'fehler_kurzbz' => 'StudentstatusNachAbbrecher',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aktiver Status nach Abbrecher Status. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'StudentstatusNachAbbrecher',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0002',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0003',
		'fehler_kurzbz' => 'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'fehlercode_extern' => null,
		'fehlertext' => 'Ausbildungssemester %s des aktuellen Status stimmt nicht mit Ausbildungssemester %s bei StudentIn (Lehrverband) überein. (student_uid %s, prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'warning',
		'app' => 'core',
		'producerLibName' => 'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0003',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0004',
		'fehler_kurzbz' => 'InaktiverStudentAktiverStatus',
		'fehlercode_extern' => null,
		'fehlertext' => 'Inaktiver Benutzer hat aktiven Status. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'warning',
		'app' => 'core',
		'producerLibName' => 'InaktiverStudentAktiverStatus',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0004',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0005',
		'fehler_kurzbz' => 'InskriptionVorLetzerBismeldung',
		'fehlercode_extern' => null,
		'fehlertext' => 'Datum der Inskription liegt vor dem Datum der letzten BIS-Meldung %s. (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'InskriptionVorLetzerBismeldung',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0005',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0006',
		'fehler_kurzbz' => 'DatumStudiensemesterFalscheReihenfolge',
		'fehlercode_extern' => null,
		'fehlertext' => 'Datum und Studiensemester sind bei den Status in falscher Reihenfolge. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'DatumStudiensemesterFalscheReihenfolge',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0006',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0007',
		'fehler_kurzbz' => 'AktiverStudentOhneStatus',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aktive/r StudentIn ohne aktuellen Status (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'AktiverStudentOhneStatus',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0007',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0008',
		'fehler_kurzbz' => 'StudienplanUngueltig',
		'fehlercode_extern' => null,
		'fehlertext' => 'Studienplan %s ist im Ausbildungssemester %s nicht gültig (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'StudienplanUngueltig',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0008',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0009',
		'fehler_kurzbz' => 'FalscheAnzahlAbschlusspruefungen',
		'fehlercode_extern' => null,
		'fehlertext' => 'Mehrere oder keine bestandenen Abschlussprüfungen (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'FalscheAnzahlAbschlusspruefungen',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0009',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0010',
		'fehler_kurzbz' => 'DatumAbschlusspruefungFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Abschlussprüfung Datum (prestudent_id %s, abschlusspruefung_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'DatumAbschlusspruefungFehlt',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0010',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0011',
		'fehler_kurzbz' => 'DatumSponsionFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Sponsionsdatum (prestudent_id %s, abschlusspruefung_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'DatumSponsionFehlt',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0011',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0012',
		'fehler_kurzbz' => 'BewerberNichtZumRtAngetreten',
		'fehlercode_extern' => null,
		'fehlertext' => 'Bewerber nicht zum Reihungstest angetreten (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'warning',
		'app' => 'core',
		'producerLibName' => 'BewerberNichtZumRtAngetreten',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0012',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0013',
		'fehler_kurzbz' => 'AktSemesterNull',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aktuelles Ausbildungssemester ist 0 (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'AktSemesterNull',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0013',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0014',
		'fehler_kurzbz' => 'AbschlussstatusFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Abschlussstatus (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'warning',
		'app' => 'core',
		'producerLibName' => 'AbschlussstatusFehlt',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0014',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0015',
		'fehler_kurzbz' => 'AktiverStudentstatusOhneKontobuchung',
		'fehlercode_extern' => null,
		'fehlertext' => 'Keine Kontobuchung bei aktivem Studentstatus (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'AktiverStudentstatusOhneKontobuchung',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0015',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0016',
		'fehler_kurzbz' => 'DualesStudiumOhneMarkierung',
		'fehlercode_extern' => null,
		'fehlertext' => 'StudentIn in dualem Studiengang nicht als dual markiert (prestudent_id %s, Studienplan %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'DualesStudiumOhneMarkierung',
		'resolverLibName' => 'CORE_STUDENTSTATUS_0016',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_PERSON_0001',
		'fehler_kurzbz' => 'GbDatumWeitZurueck',
		'fehlercode_extern' => null,
		'fehlertext' => 'Geburtsdatum vor dem 01.01.1920',
		'fehlertyp_kurzbz' => 'warning',
		'app' => 'core',
		'producerLibName' => 'GbDatumWeitZurueck',
		'resolverLibName' => 'CORE_PERSON_0001',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_PERSON_0002',
		'fehler_kurzbz' => 'NationNichtOesterreichAberGemeinde',
		'fehlercode_extern' => null,
		'fehlertext' => 'Nation der Adresse ist ungleich Österreich, es ist aber eine österreichische Gemeinde (%s) angegeben (adresse_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'NationNichtOesterreichAberGemeinde',
		'resolverLibName' => 'CORE_PERSON_0002',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_PERSON_0003',
		'fehler_kurzbz' => 'FalscheAnzahlHeimatadressen',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es sind mehrere oder keine Heimatadressen eingetragen',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'FalscheAnzahlHeimatadressen',
		'resolverLibName' => 'CORE_PERSON_0003',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_PERSON_0004',
		'fehler_kurzbz' => 'FalscheAnzahlZustelladressen',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es sind mehrere oder keine Zustelladressen eingetragen',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => 'FalscheAnzahlZustelladressen',
		'resolverLibName' => 'CORE_PERSON_0004',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_PERSON_0005',
		'fehler_kurzbz' => 'geburtsnationFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Geburtsnation nicht vorhanden',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_PERSON_0005',
		'producerIsResolver' => false
	),
	array(
		'fehlercode' => 'CORE_PERSON_0006',
		'fehler_kurzbz' => 'uhstatPersonkennungFehltCore',
		'fehlercode_extern' => null,
		'fehlertext' => 'Personkennung fehlt (vBpk AS, vBpk BF oder Ersatzkennzeichen fehlt)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core',
		'producerLibName' => null,
		'resolverLibName' => 'CORE_PERSON_0006',
		'producerIsResolver' => false
	)
);
