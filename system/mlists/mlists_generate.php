<?php
/****************************************************************************
 * Script: 			mlists_generate.php
 * Descr:  			Das Skript generiert Mailinglisten in der Datenbanken
 *					fuer Einheiten, Lektoren und  fix Angestellte.
 * Verzweigungen: 	nach einheit_det.php
 *					von einheit_menue.php
 * Author: 			Christian Paminger
 * Erstellt: 		12.9.2005
 * Update: 			14.9.2005 von Christian Paminger
 *****************************************************************************/

include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
$error_msg='';
?>

<HTML>
<HEAD>
	<TITLE>Mailinglisten</TITLE>
	<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>
<BODY>
	<H3>MailingListen abgleich</H3>
	<?php
	if (!$conn = pg_pconnect(CONN_STRING))
   		$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden.\n';

   	// aktuelles Studiensemester ermitteln
	$sql_query="SELECT studiensemester_kurzbz FROM public.vw_studiensemester ORDER BY delta LIMIT 1";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	if($row=pg_fetch_object($result))
		$studiensemester=$row->studiensemester_kurzbz;
	else
		$error_msg.=pg_errormessage($conn).$sql_query;

   	// **************************************************************
	// LektorenVerteiler abgleichen
	$mlist_name='tw_lkt';
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo $mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter JOIN tbl_benutzer ON (mitarbeiter_uid=uid) WHERE lektor AND aktiv)";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid FROM public.tbl_mitarbeiter JOIN tbl_benutzer ON (mitarbeiter_uid=uid) WHERE lektor AND aktiv AND mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe VALUES ('$row->uid','".strtoupper($mlist_name)."', now(), 'mlists_generate')";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}

	// **************************************************************
	// Verteiler fuer alle fixAngestellten abgleichen
	$mlist_name='tw_fix';
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>'.$mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE fixangestellt)";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid FROM public.tbl_mitarbeiter WHERE fixangestellt AND mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe VALUES ('$row->uid','".strtoupper($mlist_name)."', now(), 'mlists_generate')";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}

	// **************************************************************
	// Verteiler fuer alle fixen Lektoren abgleichen
	$mlist_name='tw_fix_lkt';
	// Lektoren holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>'.$mlist_name.' wird abgeglichen!<BR>';
	flush();
	$sql_query="SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE fixangestellt AND lektor)";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name') AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid AS uid FROM public.tbl_mitarbeiter WHERE fixangestellt AND lektor AND mitarbeiter_uid NOT LIKE '\\\\_%' AND mitarbeiter_uid NOT IN (SELECT uid FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$mlist_name'))";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO public.tbl_benutzergruppe VALUES ('$row->uid','".strtoupper($mlist_name)."', now(), 'mlists_generate')";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
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
			studiensemester_kurzbz='$studiensemester' AND mitarbeiter_uid NOT LIKE '\\\\_%')";
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).$sql_query;
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM public.tbl_benutzergruppe WHERE UPPER(gruppe_kurzbz)=UPPER('$row->gruppe_kurzbz') AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die noch nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT mitarbeiter_uid, UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt') AS mlist_name, tbl_studiengang.studiengang_kz
		FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
		WHERE
		tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
		tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
		tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND
		studiensemester_kurzbz='$studiensemester' AND
		mitarbeiter_uid NOT LIKE '\\\\_%' AND tbl_studiengang.studiengang_kz!=0 AND
		(mitarbeiter_uid,UPPER(typ::varchar(1) || tbl_studiengang.kurzbz || '_lkt')) NOT IN
		(SELECT uid, UPPER(gruppe_kurzbz) FROM public.tbl_benutzergruppe
			WHERE gruppe_kurzbz LIKE '%\\\\_LKT' AND UPPER(gruppe_kurzbz)!=UPPER('tw_lkt') AND UPPER(gruppe_kurzbz)!=UPPER('tw_fix_lkt'))";
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).$sql_query;
	while($row=pg_fetch_object($result))
	{
		$sql_query="SELECT * FROM public.tbl_gruppe WHERE gruppe_kurzbz='".strtoupper($row->mlist_name)."'";
		if($res = pg_query($conn, $sql_query))
		{
			if(pg_num_rows($res)<=0)
			{
				$sql_query="INSERT INTO public.tbl_gruppe(gruppe_kurzbz, studiengang_kz, semester, bezeichnung,
							beschreibung, mailgrp, sichtbar, generiert, aktiv, updateamum, updatevon,
							insertamum, insertvon)
							VALUES('".strtoupper($row->mlist_name)."',$row->studiengang_kz, 0,'$row->mlist_name',".
							"'$row->mlist_name', true, true, true, true, now(),'mlists_generate',now(), 'mlists_generate');";
				if(!pg_query($conn, $sql_query))
					echo "<br>Fehler beim Anlegen der Gruppe: $sql_query<br>";
			}
		}
		else
			echo "<br>Fehler:$sql_query";

     	$sql_query="INSERT INTO public.tbl_benutzergruppe VALUES ('$row->mitarbeiter_uid','".strtoupper($row->mlist_name)."', now(), 'mlists_generate')";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}


	// **************************************************************
	// Studentenverteiler abgleichen
	// Studenten holen die nicht mehr in den Verteiler gehoeren
	/*
	echo '<BR>Studenten-Verteiler werden abgeglichen!<BR>';
	flush();
	$sql_query="SELECT gruppe_kurzbz, uid FROM public.tbl_benutzergruppe NATURAL JOIN tbl_einheit
	            WHERE (uid, mailgrp_kurzbz) NOT IN
	            (SELECT uid, mailgrp_kurzbz FROM tbl_einheitstudent NATURAL JOIN tbl_einheit
	            	WHERE mailgrp_kurzbz IS NOT NULL)";


	//echo $sql_query;
	if(!($result=@pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).$sql_query;
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$row->mailgrp_kurzbz' AND uid='$row->uid'";
		if(!@pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Studenten holen die noch nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT * FROM tbl_einheitstudent NATURAL JOIN tbl_einheit WHERE  mailgrp_kurzbz IS NOT NULL
	            AND (uid,mailgrp_kurzbz) NOT IN (SELECT uid,mailgrp_kurzbz FROM tbl_personmailgrp)";
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).$sql_query;
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO tbl_personmailgrp VALUES ('$row->uid','$row->mailgrp_kurzbz', now(), 'mlists_generate.php')";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	*/

	echo $error_msg;
	?>
	<BR>
	<P>
  		Die Mailinglisten wurden abgeglichen. <BR>
  	</P>
</BODY>
</HTML>