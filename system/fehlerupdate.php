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
	)
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
