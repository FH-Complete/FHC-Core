<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 *          Gerald Simane-Senquens <gerald.simane-sequens@technikum-wien.at>.
 */
/*
 * Aktualisiert in der Datenbank alle UID Felder und verlaengert diese von 16 auf 32 Zeichen
 * Views die UIDs als Spalten enthalten werden geloescht und danach wieder angelegt
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

$db = new basis_db();
//Alle Tabellen holen die UID als Spalte haben
//die 16 Zeichen lang ist.
$qry="
SELECT column_name as spalte, table_name as tabelle, table_schema as schema 
FROM information_schema.columns 
WHERE 
	column_name in('student_uid','uid','mitarbeiter_uid', 'insertvon','updatevon','koordinator',
				'lektor','vorsitz','updateaktivvon','freigabevon_uid','vertretung_uid','freigabevon', 'lektor_uid') 
	AND data_type='character varying' 
	AND character_maximum_length='16' 
ORDER BY table_name DESC, column_name";
$views=array();
$anzviews=0;

if($result = $db->db_query($qry))
{
	$db->db_query('BEGIN');
	while($row = $db->db_fetch_object($result))
	{
		//Alle Views die Spalten enthalten die geaendert werden loeschen
		if(substr($row->tabelle,0,3)=='vw_')
		{
			$qry_view = "SELECT * FROM pg_views WHERE viewname='$row->tabelle'";
			if($result_view = $db->db_query($qry_view))
			{
				if($row_view = $db->db_fetch_object($result_view))
				{
					$views[$anzviews]['definition']=$row_view->definition;
					$views[$anzviews]['schema']=$row_view->schemaname;
					$views[$anzviews]['viewname']=$row_view->viewname;
					$anzviews++;
					
					$qry_drp_view = "DROP VIEW $row_view->schemaname.$row_view->viewname;";
					echo $qry_drp_view;
					$db->db_query($qry_drp_view);
				}
			}
			
		}
		else
		{
			//Spalte in der Tabelle aendern
			$qry_alter="ALTER TABLE $row->schema.$row->tabelle ALTER COLUMN $row->spalte TYPE varchar(32);";
			echo $qry_alter.'<br>';
			
			if($db->db_query($qry_alter))
				echo "$row->tabelle : $row->spalte<br>";
			else 
				echo "<b>Fehler: $qry_alter</b><br>";
		}
	}
	
	// ----------- ort_kurzbz ------------ //
	$qry="SELECT column_name as spalte, table_name as tabelle, table_schema as schema 
			FROM information_schema.columns 
			WHERE 
				column_name='ort_kurzbz'
				AND data_type='character varying' 
				AND character_maximum_length='8' 
			ORDER BY table_name DESC, column_name";
	if($result = $db->db_query($qry))
	{
	
		while($row = $db->db_fetch_object($result))
		{
			//Alle Views die Spalten enthalten die geaendert werden loeschen
			if(substr($row->tabelle,0,3)=='vw_')
			{
				$qry_view = "SELECT * FROM pg_views WHERE viewname='$row->tabelle'";
				if($result_view = $db->db_query($qry_view))
				{
					if($row_view = $db->db_fetch_object($result_view))
					{
						$views[$anzviews]['definition']=$row_view->definition;
						$views[$anzviews]['schema']=$row_view->schemaname;
						$views[$anzviews]['viewname']=$row_view->viewname;
						$anzviews++;
						
						$qry_drp_view = "DROP VIEW $row_view->schemaname.$row_view->viewname;";
						echo $qry_drp_view;
						$db->db_query($qry_drp_view);
					}
				}
			}
			else
			{
				//Spalte in der Tabelle aendern
				$qry_alter="ALTER TABLE $row->schema.$row->tabelle ALTER COLUMN $row->spalte TYPE varchar(16);";
				echo $qry_alter.'<br>';
				
				if($db->db_query($qry_alter))
					echo "$row->tabelle : $row->spalte<br>";
				else 
					echo "<b>Fehler: $qry_alter</b><br>";
			}
		}
	}

	//Views wieder anlegen
	foreach ($views as $view)
	{
		$qry = "CREATE VIEW ".$view['schema'].".".$view['viewname']." AS ".$view['definition'];
		if($db->db_query($qry))
			echo $qry.'<br>';
		else 
			echo '<b>Fehler beim Anlegen der View: '.$qry.'<br>';
	}
	
	//ViewBerechtigungen wieder einspielen
	$qry ='
	Grant select on campus.vw_benutzer to group "admin";
	Grant select on campus.vw_benutzer to group "web";
	Grant select on campus.vw_lehreinheit to group "admin";
	Grant select on campus.vw_lehreinheit to group "web";
	Grant select on campus.vw_mitarbeiter to group "admin";
	Grant select on campus.vw_mitarbeiter to group "web";
	Grant select on campus.vw_reservierung to group "admin";
	Grant select on campus.vw_reservierung to group "web";
	Grant select on campus.vw_stundenplan to group "admin";
	Grant select on campus.vw_stundenplan to group "web";
	Grant select on campus.vw_persongruppe to group "admin";
	Grant select on campus.vw_persongruppe to group "web";
	Grant select on campus.vw_student to group "admin";
	Grant select on campus.vw_student to group "web";
	Grant select on campus.vw_student_lehrveranstaltung to group "admin";
	Grant select on campus.vw_student_lehrveranstaltung to group "web";
	Grant select on lehre.vw_stundenplan to group "admin";
	Grant select on lehre.vw_stundenplan to group "web";
	Grant select on lehre.vw_stundenplandev to group "admin";
	Grant select on lehre.vw_stundenplandev to group "web";
	Grant select on lehre.vw_lva_stundenplan to group "admin";
	Grant select on lehre.vw_lva_stundenplan to group "web";
	Grant select on lehre.vw_lva_stundenplandev to group "admin";
	Grant select on lehre.vw_lva_stundenplandev to group "web";
	Grant select on lehre.vw_reservierung to group "admin";
	Grant select on lehre.vw_reservierung to group "web";
	Grant select on lehre.vw_fas_lehrveranstaltung to group "admin";
	Grant select on lehre.vw_fas_lehrveranstaltung to group "web";
	Grant select on vw_betriebsmittelperson to group "admin";
	Grant select on vw_betriebsmittelperson to group "web";
	Grant select on testtool.vw_ablauf to group "admin";
	Grant select on testtool.vw_ablauf to group "web";
	Grant select on testtool.vw_pruefling to group "admin";
	Grant select on testtool.vw_pruefling to group "web";
	Grant select on testtool.vw_auswertung to group "admin";
	Grant select on testtool.vw_auswertung to group "web";
	Grant select on testtool.vw_auswertung_kategorie to group "admin";
	Grant select on testtool.vw_auswertung_kategorie to group "web";
	Grant select on vw_studiensemester to group "admin";
	Grant select on public.vw_benutzerfunktion to group "admin";
	Grant select on public.vw_prestudent to group "admin";
	GRANT SELECT ON public.vw_prestudent to group web;
	GRANT SELECT ON lehre.vw_stundenplandev_student_unr TO GROUP web;
	GRANT SELECT ON lehre.vw_stundenplandev_student_unr TO GROUP admin;
	';
	echo $qry;
	if(!$db->db_query($qry))
		echo '<b>Fehler bei qry:</b>'.$qry;
	
	if(!$db->db_query('COMMIT'))
		echo '<b>Fehler beim Commit</b>';
	else 
		echo '<br><br><b>Aktualisierung erfolgreich</b>';
}

?>