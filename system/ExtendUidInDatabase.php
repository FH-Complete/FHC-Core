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
require_once('../vilesci/config.inc.php');

$connstring=CONN_STRING;
$connstring="host=theseus.technikum-wien.at dbname=-devvilesci user= password=";
if(!$conn = pg_connect($connstring))
	die('Keine Verbindung zur DB');

//Alle Tabellen holen die UID als Spalte haben
//die 16 Zeichen lang ist. (atttypmod=20 weil automatisch 4 zeichen dazugezaehlt werden)
$qry="
SELECT 
	attname as spalte, relname as tabelle 
FROM 
	pg_attribute JOIN pg_class on(attrelid=pg_class.oid) JOIN pg_type ON(atttypid=pg_type.oid) 
WHERE 
	attname in ('student_uid','uid','mitarbeiter_uid', 'insertvon','updatevon','koordinator',
	            'lektor','vorsitz','updateaktivvon','freigabevon_uid','vertretung_uid','freigabevon', 'lektor_uid') 
	AND atttypmod=20 
	AND (relname like 'tbl_%' OR relname like 'vw_%')
	AND typname='varchar' ORDER BY tabelle DESC, spalte";
$views=array();
$anzviews=0;
pg_query($conn, 'SET search_path to bis, campus, fue, kommune, lehre, public, sync, testtool');
if($result = pg_query($conn, $qry))
{
	pg_query($conn,'BEGIN');
	while($row = pg_fetch_object($result))
	{
		//Alle Views die Spalten enthalten die geaendert werden loeschen
		if(substr($row->tabelle,0,3)=='vw_')
		{
			$qry_view = "SELECT * FROM pg_views WHERE viewname='$row->tabelle'";
			if($result_view = pg_query($conn, $qry_view))
			{
				if($row_view = pg_fetch_object($result_view))
				{
					$views[$anzviews]['definition']=$row_view->definition;
					$views[$anzviews]['schema']=$row_view->schemaname;
					$views[$anzviews]['viewname']=$row_view->viewname;
					$anzviews++;
					
					$qry_drp_view = "DROP VIEW $row_view->schemaname.$row_view->viewname;";
					echo $qry_drp_view;
					pg_query($conn, $qry_drp_view);
				}
			}
			
		}
		else
		{
			//Spalte in der Tabelle aendern
			$qry_alter="ALTER TABLE $row->tabelle ALTER COLUMN $row->spalte TYPE varchar(32);";
			echo $qry_alter.'<br>';
			
			if(pg_query($conn, $qry_alter))
				echo "$row->tabelle : $row->spalte<br>";
			else 
				echo "Fehler: $qry_alter<br>";
		}
	}
	
	//Views wieder anlegen
	foreach ($views as $view)
	{
		$qry = "CREATE VIEW ".$view['schema'].".".$view['viewname']." AS ".$view['definition'];
		if(pg_query($conn, $qry))
			echo $qry.'<br>';
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
	Grant select on testtool.vw_pruefling to group "admin";
	Grant select on testtool.vw_pruefling to group "web";
	Grant select on testtool.vw_gebiet to group "admin";
	Grant select on testtool.vw_gebiet to group "web";
	Grant select on testtool.vw_frage to group "admin";
	Grant select on testtool.vw_frage to group "web";
	Grant select on testtool.vw_antwort to group "admin";
	Grant select on testtool.vw_antwort to group "web";
	Grant select on testtool.vw_anz_antwort to group "admin";
	Grant select on testtool.vw_anz_antwort to group "web";
	Grant select on testtool.vw_anz_richtig to group "admin";
	Grant select on testtool.vw_anz_richtig to group "web";
	Grant select on testtool.vw_auswertung to group "admin";
	Grant select on testtool.vw_auswertung to group "web";
	Grant select on testtool.vw_auswertung_kategorie to group "admin";
	Grant select on testtool.vw_auswertung_kategorie to group "web";
	Grant select on vw_studiensemester to group "admin";
	Grant select on public.vw_benutzerfunktion to group "admin";
	Grant select on public.vw_prestudent to group "admin";
	';
	echo $qry;
	pg_query($conn, $qry);
	
	pg_query($conn,'COMMIT');
}

?>