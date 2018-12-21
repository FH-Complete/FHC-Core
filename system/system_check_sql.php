<?php
/* Copyright (C) 2015 FH Technikum-Wien
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
 *
 * Authors: Manfred kindl <manfred.kindl@technikum-wien.at>
 */

/*
 * Dieses Skript durchläuft alle Dateien vom Typ .sql im Ordner system/sql.
 * Wenn von diesen SQLs Datensätze retourniert werden (die nicht NULL sind) oder Fehler auftreten, werden diese in ein Mail gepackt und an MAIL_ADMIN geschickt.
 */

require_once('../config/global.config.inc.php');
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/mail.class.php');

// Datenbankverbindung herstellen
if (! $db = new basis_db ())
	die ( 'Es konnte keine Verbindung zum Server aufgebaut werden.' );

$slq_import_path = DOC_ROOT.'system/sql';
$mailcontent = '';
$mailheader = '
		<style type="text/css">
		.error
		{
			color: red;
			font-weight: bold;
		}
		.table
		{
			font-size: small;
			cellpadding: 3px;
		}
		.table th
		{
			background: #DCE4EF;
			border: 1px solid #FFF;
			padding: 4px;
			text-align: left;
		}
		.table td
		{
			background-color: #EEEEEE;
			padding: 4px;
		 	vertical-align: top;
		}
		</style>';

// Wenn das Script ueber die Kommandozeile aufgerufen wird, erfolgt keine Authentifizierung
if (php_sapi_name() != 'cli')
{
	$uid = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	
	if(!$rechte->isBerechtigt('admin'))
	{
		exit($rechte->errormsg);
	}
}
$files = glob($slq_import_path.'/*.sql');
$filename = '';
$db_row = '';

if (($files = glob($slq_import_path.'/*.sql')) != false)
{
	foreach ($files as $file)
	{
		$filename = (basename($file));
		$sql = file_get_contents($file);
		
		// Checken, ob letztes Zeichen ; ist, wenn nicht, hinzufügen
		if (substr($sql, -1, 1) != ';')
			$sql = $sql .= '; ';

		// Wenn Fehler auftritt, diesen ins Mail schreiben
		if (! @$result = $db->db_query ($sql))
		{
			$mailcontent .= '<h3>Die Abfrage der Datei "'.$filename.'" hat folgenden Fehler geliefert:</h3>';
			$mailcontent .= '<span class="error">'.$db->db_last_error () . '</span><br>';
			$mailcontent .= '<pre>'.$sql.'</pre><br>';
			continue;
		}
		
		// Wenn mehr als eine row vom SQL zurückkommen und diese nicht NULL ist, diese ins Mail schreiben
		if ($db->db_num_rows($result) > 1 || ($db->db_num_rows($result) == 1 && $db->db_fetch_row($result)[0] != ''))
		{
			$mailcontent .= '<h3>Die Abfrage der Datei "'.$filename.'" hat folgendes Ergebnis geliefert:</h3>';

			// Wenn zu viele Datensätze retourniert werden, abbrechen und Meldung ausgeben
			if ($db->db_num_rows($result) > 1000)
				$mailcontent .= '<span class="error">ACHTUNG! Es wurden mehr als 1000 Datensätze zurückgegeben</span><br>';
			
			$mailcontent .= '<table class="table"><thead><tr>';
			$array = array();
			$result = $db->db_query ($sql);
			$object = $db->db_fetch_object($result);
			$row_array = get_object_vars($object);

			foreach($row_array AS $key => $value)
			{
				$mailcontent .= '<th>'.$key.'</th>';
			}
			$mailcontent .= '</tr></thead><tbody>';
			$counter = 0; // Wenn mehr als 1000 Datensätze retourniert werden, abbrechen
			
			$result = $db->db_query ($sql);
			while($row = $db->db_fetch_object($result))
			{
				if ($counter == 1000)
					break;
				
				$mailcontent .= '<tr>';
				foreach ($row AS $column)
				{
					$mailcontent .= '<td>'.($column === 'f' ? 'false' : ($column === 't' ? 'true' : $column)).'</td>';
				}
				$mailcontent .= '</tr>';
				$counter++;
			}
			$mailcontent .= '</tbody></table>';
		}
	}
}
else 
	$mailcontent = '';

// Wenn Mailcontent nicht leer ist, Mail senden
if ($mailcontent != '')
{
	$mailcontent = $mailheader.$mailcontent; 
	echo $mailcontent;
	
	$mail = new mail(MAIL_ADMIN, 'no-reply', 'Fehler in System Check "addons/bewerbung/cronjobs/system_check.php"', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Inhalt vollständig darzustellen.');
	$mail->setHTMLContent($mailcontent);
	$mail->send();
}
else 
	echo 'Es sind keine Fehler aufgetreten';


