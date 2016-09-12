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
 */
/**
 * Gerneriert die Textfiles fuer die Mailverteiler
 * der Gruppen und das Textfile fuer die Aliase
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/studiensemester.class.php');

	$db = new basis_db();

	if(!($result_stg = $db->db_query("SELECT studiengang_kz, bezeichnung, lower(typ::varchar(1) || kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz ASC")))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($result_stg);
	$ss=new studiensemester();
	$ss_nearest=$ss->getNearest();
	$ss_nearest_to_akt=$ss->getNearestFrom($ss_nearest);

?>
<HTML>
<HEAD>
<TITLE>Mailinglisten</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>

<BODY class="background_main">
<H3>MailingListen </H3>

<?php
	$crlf="\n";
	for ($i=0; $i<$num_rows; $i++)
	{
		$row=pg_fetch_object($result_stg);
     	$stg_id=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT * FROM public.tbl_gruppe WHERE studiengang_kz='".addslashes($stg_id)."' AND mailgrp=true ORDER BY gruppe_kurzbz";

		if(!($result_mg = $db->db_query($sql_query)))
			die($db->db_last_error());
		$nr_mg=$db->db_num_rows($result_mg);

		// Mailgroups
		for  ($j=0; $j<$nr_mg; $j++)
		{
			$row_mg = $db->db_fetch_object($result_mg, $j);
			$mg_kurzbz=$row_mg->gruppe_kurzbz;
			if($row_mg->studiengang_kz==10005 && mb_stripos($mg_kurzbz,'EWU')===0)
			{
				echo "EWU Gruppe $mg_kurzbz wird fuer STSEM ".$ss_nearest." und ".$ss_nearest_to_akt." erstellt";
				// FHTW Warm Up Kurse enthaelt die Teilnehmer des SS auch wenn das WS schon gestartet hat
			$sql_query='SELECT tbl_benutzergruppe.uid, nachname, vorname '.
				       'FROM campus.vw_benutzer, public.tbl_benutzergruppe '.
				       'WHERE vw_benutzer.uid=tbl_benutzergruppe.uid AND '.
				       "UPPER(gruppe_kurzbz)=UPPER('$mg_kurzbz') AND tbl_benutzergruppe.uid NOT LIKE '\\\\_%' ".
					   "AND (studiensemester_kurzbz IS NULL OR studiensemester_kurzbz in(".$db->db_add_param($ss_nearest).",".$db->db_add_param($ss_nearest_to_akt).")) AND aktiv ORDER BY nachname;";
			}
			else
			{
			$sql_query='SELECT tbl_benutzergruppe.uid, nachname, vorname '.
				       'FROM campus.vw_benutzer, public.tbl_benutzergruppe '.
				       'WHERE vw_benutzer.uid=tbl_benutzergruppe.uid AND '.
				       "UPPER(gruppe_kurzbz)=UPPER('$mg_kurzbz') AND tbl_benutzergruppe.uid NOT LIKE '\\\\_%' ".
					   "AND (studiensemester_kurzbz IS NULL OR studiensemester_kurzbz='$ss_nearest') AND aktiv ORDER BY nachname;";
			}
			if(!($result_person = $db->db_query($sql_query)))
				die($db->db_last_error());

			// File Operations
			$name=$mg_kurzbz.'.txt';
			$name=mb_strtolower($name);
			$fp=fopen('../../../mlists/'.$name,"w");
			//$fp=fopen('../../../../mlists/'.$name,"w");

			$nr_person=$db->db_num_rows($result_person);
			for  ($p=0; $p<$nr_person; $p++)
			{
				$row = $db->db_fetch_object($result_person, $p);
				fwrite($fp, '#'.$row->nachname.' '.$row->vorname.$crlf.$row->uid.$crlf);
			}
			fclose($fp);
			echo $name.' created<br>';
			flush();
		}
	}

	//Zusammenbauen der Studiengaenge die keine Alias Adressen bekommen
	$noalias_kz='';
	foreach($noalias as $var)
	{
		if($noalias_kz!='')
			$noalias_kz.=',';
		$noalias_kz.=$var;
	}

	//$qry = "SELECT vornamen, nachname, uid, alias FROM tbl_person where alias<>'' ORDER BY nachname, vornamen";
	$qry = "SELECT vorname, nachname, uid, alias FROM (public.tbl_person JOIN public.tbl_benutzer USING(person_id)) LEFT JOIN public.tbl_student on(uid=student_uid)
	        WHERE
	        	alias<>''";
	if($noalias_kz!='')
		$qry.=" AND (studiengang_kz NOT IN($noalias_kz) OR studiengang_kz is null)";

	$qry.="	AND (tbl_benutzer.aktiv OR
	        		(tbl_benutzer.aktiv=false
	        		AND updateaktivam >= now()-(SELECT CASE public.get_rolle_prestudent (prestudent_id,null)
	        										WHEN 'Abbrecher' THEN '".DEL_ABBRECHER_WEEKS." weeks'::interval
	        										WHEN 'Absolvent' THEN '".DEL_STUDENT_WEEKS." weeks'::interval
	        										ELSE '".DEL_MITARBEITER_WEEKS." weeks'::interval
	        										END
	        									  )
	        		))
	        ORDER BY nachname, vorname";

	if($result = $db->db_query($qry))
	{
		$fp=fopen('../../../mlists/tw_alias.txt',"w");
		while($row = $db->db_fetch_object($result))
		{
			fwrite($fp,"# ".$row->nachname." ".$row->vorname.$crlf);
			fwrite($fp,$row->alias.": ".$row->uid.$crlf);
		}
		fclose($fp);
		echo 'tw_alias.txt created<br>';
	}
	else
	{
		echo 'tw_alias.txt failed<br>';
	}

?>
<P><BR>
  Die Mailinglisten wurden erstellt. <BR>
  Sie k&ouml;nnen nun die erstellten Datein auf den Mail-Server kopieren (<A href="mlists_copy.php">Copy
  Lists</A>).</P>
<P><A href="index.html" class="linkblue">&lt;&lt; Zur&uuml;ck</A></P>
</BODY>
</HTML>
