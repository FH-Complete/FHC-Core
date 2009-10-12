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
/****************************************************************************
 * Script: 			mlists_generate.php
 * Descr:  			Das Skript generiert Mailinglisten in der Datenbanken
 *					fuer Einheiten, Lektoren und  fix Angestellte.
 * Author: 			Christian Paminger
 * Erstellt: 		12.9.2005
 * Update: 			14.9.2005 von Christian Paminger
 *****************************************************************************/

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiensemester.class.php');
$error_msg='';
?>

<HTML>
<HEAD>
	<TITLE>Mailinglisten</TITLE>
	<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>
<BODY>
	<H3>MailingListen abgleich</H3>
	<?php
	$db = new basis_db();
	
   	// aktuelles Studiensemester ermitteln
	$sql_query="SELECT studiensemester_kurzbz FROM public.vw_studiensemester ORDER BY delta LIMIT 1";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	if($row = $db->db_fetch_object($result))
		$studiensemester=$row->studiensemester_kurzbz;
	else
		$error_msg.= $db->db_last_error().$sql_query;
	
	$stsem_obj = new studiensemester();
	
	if(mb_substr($studiensemester,0,1)=='W')
		$stsem2 = $stsem_obj->getPreviousFrom($studiensemester);
	else 
		$stsem2 = $stsem_obj->getNextFrom($studiensemester);
	
	function setGeneriert($gruppe)
	{
		$db = new basis_db();
		$qry = "UPDATE public.tbl_gruppe SET generiert=true WHERE UPPER(gruppe_kurzbz)=UPPER('".addslashes($gruppe)."')";
		$db->db_query($qry);
	}
	
   	// **************************************************************
	// LektorenVerteiler abgleichen
	$mlist_name='tw_lkt';
	setGeneriert($mlist_name);
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo $mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('".addslashes($mlist_name)."') AND uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) WHERE lektor AND aktiv)";
	
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();

	while($row = $db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) WHERE lektor AND aktiv AND mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.= $db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','".strtoupper($mlist_name)."', now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}

	// **************************************************************
	// Sekretariats-Verteiler abgleichen
	$mlist_name='tw_sek';
	setGeneriert($mlist_name);
	// Personen holen die nicht mehr in den Verteiler gehoeren
	echo $mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe 
				WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND 
				uid NOT IN (SELECT mitarbeiter_uid 
							FROM 
								public.tbl_mitarbeiter 
								JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
								JOIN public.tbl_benutzerfunktion USING(uid) 
							WHERE aktiv AND funktion_kurzbz='ass' AND
							(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
							(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Personen holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT distinct mitarbeiter_uid AS uid 
				FROM 
					public.tbl_mitarbeiter 
					JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
					JOIN public.tbl_benutzerfunktion USING(uid) 
				WHERE 
					aktiv AND 
					tbl_benutzerfunktion.funktion_kurzbz='ass' AND 
					(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
					(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) AND
					mitarbeiter_uid NOT LIKE '\\\\_%' AND 
					mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe 
											WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, studiensemester_kurzbz, updateamum, updatevon, insertamum, insertvon) VALUES ('$row->uid','".strtoupper($mlist_name)."',null, null, null, now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	
	// **************************************************************
	// Studiengangsleiter-Verteiler abgleichen
	$mlist_name='tw_stgl';
	setGeneriert($mlist_name);
	// Personen holen die nicht mehr in den Verteiler gehoeren
	echo $mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe 
				WHERE 
					UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND 
					uid NOT IN (SELECT mitarbeiter_uid 
								FROM 
									public.tbl_mitarbeiter 
									JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
									JOIN public.tbl_benutzerfunktion USING(uid) 
									JOIN public.tbl_studiengang USING(oe_kurzbz)
								WHERE tbl_benutzer.aktiv AND (funktion_kurzbz='Leitung' OR funktion_kurzbz='gLtg') AND
								(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
								(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Personen holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid 
				FROM 
					public.tbl_mitarbeiter 
					JOIN public.tbl_benutzer ON (mitarbeiter_uid=uid) 
					JOIN public.tbl_benutzerfunktion USING(uid) 
					JOIN public.tbl_studiengang USING(oe_kurzbz)
				WHERE 
					tbl_benutzer.aktiv AND 
					tbl_benutzerfunktion.funktion_kurzbz='Leitung' AND 
					(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
					(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) AND
					mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, studiensemester_kurzbz, updateamum, updatevon, insertamum, insertvon) VALUES ('$row->uid','".strtoupper($mlist_name)."',null, null, null, now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	
	// **************************************************************
	// Verteiler fuer alle fixAngestellten abgleichen
	$mlist_name='tw_fix';
	setGeneriert($mlist_name);
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>'.$mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) WHERE fixangestellt AND aktiv)";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) WHERE fixangestellt AND aktiv AND mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','".strtoupper($mlist_name)."', now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}

	// **************************************************************
	// Verteiler fuer alle fixen Lektoren abgleichen
	$mlist_name='tw_fix_lkt';
	setGeneriert($mlist_name);
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>'.$mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) WHERE aktiv AND fixangestellt AND lektor)";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) WHERE fixangestellt AND lektor AND aktiv AND mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','".strtoupper($mlist_name)."', now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}


	// **************************************************************
	// Lektoren-Verteiler innerhalb der Studiengaenge abgleichen
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>Lektoren-Verteiler der Studiengaenge werden abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid, gruppe_kurzbz FROM public.tbl_benutzergruppe
		WHERE gruppe_kurzbz LIKE '%\\\\_LKT' AND UPPER(gruppe_kurzbz)!=UPPER('tw_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_fix_lkt')
		AND (uid,UPPER(gruppe_kurzbz)) NOT IN
		(SELECT mitarbeiter_uid,UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt')
			FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
			WHERE
			tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND
			(studiensemester_kurzbz='$studiensemester' OR
			 studiensemester_kurzbz='$stsem2') AND mitarbeiter_uid NOT LIKE '\\\\_%')";
	//echo $sql_query;
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error().$sql_query;
	while($row=$db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$row->gruppe_kurzbz') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die noch nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT distinct mitarbeiter_uid, UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt') AS mlist_name, tbl_studiengang.studiengang_kz
		FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
		WHERE
		tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
		tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
		tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND
		(studiensemester_kurzbz='$studiensemester' OR
		 studiensemester_kurzbz='$stsem2') AND
		mitarbeiter_uid NOT LIKE '\\\\_%' AND tbl_studiengang.studiengang_kz!=0 AND
		(mitarbeiter_uid,UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt')) NOT IN
		(SELECT uid, UPPER(gruppe_kurzbz) FROM public.tbl_benutzergruppe
			WHERE gruppe_kurzbz LIKE '%\\\\_LKT' AND UPPER(gruppe_kurzbz)!=UPPER('tw_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_fix_lkt'))";
	//echo $sql_query;
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error().$sql_query;
	while($row=$db->db_fetch_object($result))
	{
		$sql_query="SELECT * FROM public.tbl_gruppe WHERE gruppe_kurzbz='".strtoupper($row->mlist_name)."'";
		if($res = $db->db_query($sql_query))
		{
			if($db->db_num_rows($res)<=0)
			{
				setGeneriert($row->mlist_name);
				$sql_query="INSERT INTO public.tbl_gruppe(gruppe_kurzbz, studiengang_kz, semester, bezeichnung,
							beschreibung, mailgrp, sichtbar, generiert, aktiv, updateamum, updatevon,
							insertamum, insertvon)
							VALUES('".strtoupper($row->mlist_name)."',$row->studiengang_kz, 0,'$row->mlist_name',".
							"'$row->mlist_name', true, true, true, true, now(),'mlists_generate',now(), 'mlists_generate');";
				if(!$db->db_query($sql_query))
					echo "<br>Fehler beim Anlegen der Gruppe: $sql_query<br>";
			}
		}
		else
			echo "<br>Fehler:$sql_query";

     	$sql_query="INSERT INTO public.tbl_benutzergruppe(uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->mitarbeiter_uid','".strtoupper($row->mlist_name)."', now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	
	// **************************************************************
	// StudentenvertreterVerteiler abgleichen
	// Studenten holen die nicht mehr in den Verteiler gehoeren
	
	echo 'Studentenvertreterverteiler werden abgeglichen!<BR>';
	flush();
	$sql_query="SELECT gruppe_kurzbz, uid FROM public.tbl_benutzergruppe JOIN public.tbl_gruppe USING(gruppe_kurzbz) WHERE gruppe_kurzbz LIKE '%_STDV' AND uid not in (SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='stdv' AND (SELECT studiengang_kz FROM public.tbl_studiengang WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1)=tbl_gruppe.studiengang_kz) AND tbl_gruppe.studiengang_kz!='0'";
	if(!($result=$db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$row->gruppe_kurzbz') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}

	// Studenten holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid, (SELECT gruppe_kurzbz FROM public.tbl_gruppe WHERE studiengang_kz=(SELECT studiengang_kz FROM public.tbl_studiengang WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1) AND gruppe_kurzbz like '%_STDV') as gruppe_kurzbz FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='stdv' AND uid NOT in(Select uid from public.tbl_benutzergruppe JOIN public.tbl_gruppe USING(gruppe_kurzbz) WHERE studiengang_kz=(SELECT studiengang_kz FROM public.tbl_studiengang WHERE oe_kurzbz=tbl_benutzerfunktion.oe_kurzbz LIMIT 1) AND gruppe_kurzbz Like '%_STDV')";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
		if($row->gruppe_kurzbz!='')
		{
			setGeneriert($row->gruppe_kurzbz);
	     	$sql_query="INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','".mb_strtoupper($row->gruppe_kurzbz)."', now(), 'mlists_generate')";
			if(!$db->db_query($sql_query))
				$error_msg.=$db->db_last_error().$sql_query;
			echo '-';
			flush();
		}
	}
	
	//tw_stdv abgleichen
    flush();
    setGeneriert('TW_STDV');
	$sql_query="SELECT gruppe_kurzbz, uid FROM public.tbl_benutzergruppe WHERE gruppe_kurzbz='TW_STDV' AND uid not in (SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='stdv')";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('tw_stdv') AND uid='$row->uid'";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}
	
	// Studenten holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='stdv' AND uid NOT in(Select uid from public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)= UPPER('TW_STDV'))";
	if(!($result = $db->db_query($sql_query)))
		$error_msg.=$db->db_last_error();
	while($row = $db->db_fetch_object($result))
	{
	   	$sql_query="INSERT INTO public.tbl_benutzergruppe (uid, gruppe_kurzbz, insertamum, insertvon) VALUES ('$row->uid','TW_STDV', now(), 'mlists_generate')";
		if(!$db->db_query($sql_query))
			$error_msg.=$db->db_last_error().$sql_query;
		echo '-';
		flush();
	}

	echo $error_msg;
	?>
	<BR>
	<P>
  		Die Mailinglisten wurden abgeglichen. <BR>
  	</P>
</BODY>
</HTML>
