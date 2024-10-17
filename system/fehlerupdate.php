<?php
/**
 * Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 */

// Array of fehler to be added in the database
$fehlerArr = array(
	array(
		'fehlercode' => 'CORE_ZGV_0001',
		'fehler_kurzbz' => 'zgvDatumInZukunft',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Datum in Zukunft',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_ZGV_0002',
		'fehler_kurzbz' => 'zgvDatumVorGeburtsdatum',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Datum vor Geburtsdatum',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_ZGV_0003',
		'fehler_kurzbz' => 'zgvMasterDatumInZukunft',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Masterdatum in Zukunft',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_ZGV_0004',
		'fehler_kurzbz' => 'zgvMasterDatumVorZgvdatum',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Masterdatum vor Zgvdatum',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_ZGV_0005',
		'fehler_kurzbz' => 'zgvMasterDatumVorGeburtsdatum',
		'fehlercode_extern' => null,
		'fehlertext' => 'ZGV Masterdatum vor Geburtsdatum',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0001',
		'fehler_kurzbz' => 'keinAufenthaltszweckPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Aufenthaltszweck gefunden',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0002',
		'fehler_kurzbz' => 'zuVieleZweckeIncomingPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es sind %s Aufenthaltszwecke eingetragen (max. 1 Zweck für Incomings)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0003',
		'fehler_kurzbz' => 'falscherIncomingZweckPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aufenthaltszweckcode ist %s (für Incomings ist nur Zweck 1, 2, 3 erlaubt)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0004',
		'fehler_kurzbz' => 'outgoingAufenthaltfoerderungfehltPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Keine Aufenthaltsfoerderung angegeben (bei Outgoings >= 29 Tage Monat im Ausland muss mind. 1 gemeldet werden)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0005',
		'fehler_kurzbz' => 'outgoingAngerechneteEctsFehlenPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Angerechnete ECTS fehlen (Meldepflicht bei Outgoings >= 29 Tage Monat im Ausland)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0006',
		'fehler_kurzbz' => 'outgoingErworbeneEctsFehlenPlausi',
		'fehlercode_extern' => null,
		'fehlertext' => 'Erworbene ECTS fehlen (Meldepflicht bei Outgoings >= 29 Tage Monat im Ausland)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),

	/** Plausichecks **/
	array(
		'fehlercode' => 'CORE_INOUT_0007',
		'fehler_kurzbz' => 'IncomingHeimatNationOesterreich',
		'fehlercode_extern' => null,
		'fehlertext' => 'Heimatnation bei Incoming Österreich',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0008',
		'fehler_kurzbz' => 'IncomingOhneIoDatensatz',
		'fehlercode_extern' => null,
		'fehlertext' => 'Incoming hat keinen IO Datensatz (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_INOUT_0009',
		'fehler_kurzbz' => 'IncomingOrGsFoerderrelevant',
		'fehlercode_extern' => null,
		'fehlertext' => 'Incoming oder gemeinsames Studium ist nicht als nicht förderrelevant markiert. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STG_0001',
		'fehler_kurzbz' => 'StgPrestudentUngleichStgStudent',
		'fehlercode_extern' => null,
		'fehlertext' => 'Studiengang des Prestudenten ist ungleich dem Studiengang des Studenten. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STG_0002',
		'fehler_kurzbz' => 'OrgformStgUngleichOrgformPrestudent',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es ist kein Studienplan mit Studiengang (%s) und Organisationsform (%s) des Studenten zugewiesen. (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STG_0003',
		'fehler_kurzbz' => 'PrestudentMischformOhneOrgform',
		'fehlercode_extern' => null,
		'fehlertext' => 'Organisationsform ist für Studierenden/BewerberIn in Mischformstudiengang nicht eingetragen. (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STG_0004',
		'fehler_kurzbz' => 'StgPrestudentUngleichStgStudienplan',
		'fehlercode_extern' => null,
		'fehlertext' => 'Studiengang des Prestudenten passt nicht zu Studiengang des Studienplans. (prestudent_id %s, Studienplan %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0001',
		'fehler_kurzbz' => 'AbbrecherAktiv',
		'fehlercode_extern' => null,
		'fehlertext' => 'AbbrecherIn hat aktiven Benutzer. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0002',
		'fehler_kurzbz' => 'StudentstatusNachAbbrecher',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aktiver Status nach Abbrecher Status. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0003',
		'fehler_kurzbz' => 'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'fehlercode_extern' => null,
		'fehlertext' => 'Ausbildungssemester %s des aktuellen Status stimmt nicht mit Ausbildungssemester %s bei StudentIn (Lehrverband) überein. (student_uid %s, prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0004',
		'fehler_kurzbz' => 'InaktiverStudentAktiverStatus',
		'fehlercode_extern' => null,
		'fehlertext' => 'Inaktiver Benutzer hat aktiven Status. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0005',
		'fehler_kurzbz' => 'InskriptionVorLetzerBismeldung',
		'fehlercode_extern' => null,
		'fehlertext' => 'Datum der Inskription liegt vor dem Datum der letzten BIS-Meldung %s. (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0006',
		'fehler_kurzbz' => 'DatumStudiensemesterFalscheReihenfolge',
		'fehlercode_extern' => null,
		'fehlertext' => 'Datum und Studiensemester sind bei den Status in falscher Reihenfolge. (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0007',
		'fehler_kurzbz' => 'AktiverStudentOhneStatus',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aktive/r StudentIn ohne aktuellen Status (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0008',
		'fehler_kurzbz' => 'StudienplanUngueltig',
		'fehlercode_extern' => null,
		'fehlertext' => 'Studienplan %s ist im Ausbildungssemester %s nicht gültig (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0009',
		'fehler_kurzbz' => 'FalscheAnzahlAbschlusspruefungen',
		'fehlercode_extern' => null,
		'fehlertext' => 'Mehrere oder keine bestandenen Abschlussprüfungen (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0010',
		'fehler_kurzbz' => 'DatumAbschlusspruefungFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Abschlussprüfung Datum (prestudent_id %s, abschlusspruefung_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0011',
		'fehler_kurzbz' => 'DatumSponsionFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Sponsionsdatum (prestudent_id %s, abschlusspruefung_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0012',
		'fehler_kurzbz' => 'BewerberNichtZumRtAngetreten',
		'fehlercode_extern' => null,
		'fehlertext' => 'Bewerber nicht zum Reihungstest angetreten (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0013',
		'fehler_kurzbz' => 'AktSemesterNull',
		'fehlercode_extern' => null,
		'fehlertext' => 'Aktuelles Ausbildungssemester ist 0 (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0014',
		'fehler_kurzbz' => 'AbschlussstatusFehlt',
		'fehlercode_extern' => null,
		'fehlertext' => 'Kein Abschlussstatus (prestudent_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0015',
		'fehler_kurzbz' => 'AktiverStudentstatusOhneKontobuchung',
		'fehlercode_extern' => null,
		'fehlertext' => 'Keine Kontobuchung bei aktivem Studentstatus (prestudent_id %s, Studiensemester %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_STUDENTSTATUS_0016',
		'fehler_kurzbz' => 'DualesStudiumOhneMarkierung',
		'fehlercode_extern' => null,
		'fehlertext' => 'StudentIn in dualem Studiengang nicht als dual markiert (prestudent_id %s, Studienplan %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_PERSON_0001',
		'fehler_kurzbz' => 'GbDatumWeitZurueck',
		'fehlercode_extern' => null,
		'fehlertext' => 'Geburtsdatum vor dem 01.01.1920',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_PERSON_0002',
		'fehler_kurzbz' => 'NationNichtOesterreichAberGemeinde',
		'fehlercode_extern' => null,
		'fehlertext' => 'Nation der Adresse ist ungleich Österreich, es ist aber eine österreichische Gemeinde (%s) angegeben (adresse_id %s)',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_PERSON_0003',
		'fehler_kurzbz' => 'FalscheAnzahlHeimatadressen',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es sind mehrere oder keine Heimatadressen eingetragen',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	),
	array(
		'fehlercode' => 'CORE_PERSON_0004',
		'fehler_kurzbz' => 'FalscheAnzahlZustelladressen',
		'fehlercode_extern' => null,
		'fehlertext' => 'Es sind mehrere oder keine Zustelladressen eingetragen',
		'fehlertyp_kurzbz' => 'error',
		'app' => 'core'
	)
	/** Plausichecks end **/
);

// Loop through the filters array
for ($fehlerCounter = 0; $fehlerCounter < count($fehlerArr); $fehlerCounter++)
{
	$fehler = $fehlerArr[$fehlerCounter]; // single fehler definition

	// add optional fields
	$optional_fields = array('fehlercode_extern');

	foreach ($optional_fields as $optional_field)
	{
		if (!array_key_exists($optional_field, $fehler))
			$fehler[$optional_field] = null;
	}

	// If it's an array and contains the required fields
	if (is_array($fehler)
		&& isset($fehler['fehlercode']) && isset($fehler['fehler_kurzbz'])
		&& isset($fehler['fehlertext']) && isset($fehler['fehlertyp_kurzbz'])
		&& isset($fehler['app']))
	{
		$selectFehlerQuery = 'SELECT 1
								FROM system.tbl_fehler
								WHERE fehlercode = '.$db->db_add_param($fehler['fehlercode']);

		// If no error occurred while loading a fehler from the DB
		if ($dbFehlerDefinition = @$db->db_query($selectFehlerQuery))
		{
			// If NO filters were loaded: insert
			if ($db->db_num_rows($dbFehlerDefinition) == 0)
			{
				$insertFehlerQuery = 'INSERT INTO system.tbl_fehler (
											fehlercode,
											fehler_kurzbz,
											fehlercode_extern,
											fehlertext,
											fehlertyp_kurzbz,
											app
										) VALUES (
											'.$db->db_add_param($fehler['fehlercode']).',
											'.$db->db_add_param($fehler['fehler_kurzbz']).',
											'.$db->db_add_param($fehler['fehlercode_extern']).',
											'.$db->db_add_param($fehler['fehlertext']).',
											'.$db->db_add_param($fehler['fehlertyp_kurzbz']).',
											'.$db->db_add_param($fehler['app']).'
										)';

				if (!@$db->db_query($insertFehlerQuery)) // checks query execution
				{
					echo '<strong>An error occurred while inserting fehler: '.$db->db_last_error().'</strong><br>';
				}
				else
				{
					echo 'Fehler added: '.$fehler['fehlercode'].' - '.$fehler['fehler_kurzbz'].'<br>';
				}
			}
		}
		else // otherwise if errors occurred
		{
			echo '<strong>An error occurred while inserting fehler: '.$db->db_last_error().'</strong><br>';
		}
	}
}
