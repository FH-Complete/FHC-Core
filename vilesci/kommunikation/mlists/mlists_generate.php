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

include('../../config.inc.php');
include('../../../include/functions.inc.php');
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
	if (!$conn = @pg_pconnect(CONN_STRING))
   		$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden.\n';

   	// aktuelles Studiensemester ermitteln
	$sql_query="SELECT studiensemester_kurzbz FROM tbl_studiensemester WHERE start<=now() ORDER BY start DESC LIMIT 1";
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
	$sql_query="SELECT uid FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name' AND uid NOT IN (SELECT uid FROM tbl_mitarbeiter WHERE lektor)";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name' AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid FROM tbl_mitarbeiter WHERE lektor AND uid NOT LIKE '\\\\_%' AND uid NOT IN (SELECT uid FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name')";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO tbl_personmailgrp VALUES ('$row->uid','$mlist_name', now(), 'mlists_generate.php')";
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
	$sql_query="SELECT uid FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name' AND uid NOT IN (SELECT uid FROM tbl_mitarbeiter WHERE fixangestellt)";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name' AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid FROM tbl_mitarbeiter WHERE fixangestellt AND uid NOT LIKE '\\\\_%' AND uid NOT IN (SELECT uid FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name')";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO tbl_personmailgrp VALUES ('$row->uid','$mlist_name', now(), 'mlists_generate.php')";
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
	$sql_query="SELECT uid FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name' AND uid NOT IN (SELECT uid FROM tbl_mitarbeiter WHERE fixangestellt AND lektor)";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="DELETE FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name' AND uid='$row->uid'";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}
	// Lektoren holen die nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT uid FROM tbl_mitarbeiter WHERE fixangestellt AND lektor AND uid NOT LIKE '\\\\_%' AND uid NOT IN (SELECT uid FROM tbl_personmailgrp WHERE mailgrp_kurzbz='$mlist_name')";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn);
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO tbl_personmailgrp VALUES ('$row->uid','$mlist_name', now(), 'mlists_generate.php')";
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
	$sql_query="SELECT uid,mailgrp_kurzbz FROM tbl_personmailgrp
		WHERE mailgrp_kurzbz LIKE '%\\\\_lkt' AND mailgrp_kurzbz!='tw_lkt' AND mailgrp_kurzbz!='tw_fix_lkt'
		AND (uid,mailgrp_kurzbz) NOT IN
		(SELECT lektor,lower(kurzbz || '_lkt')
			FROM tbl_lehrveranstaltung NATURAL JOIN tbl_studiengang
			WHERE studiensemester_kurzbz='$studiensemester' AND lektor NOT LIKE '\\\\_%')";
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
	// Lektoren holen die noch nicht im Verteiler sind
	echo '<BR>';
	$sql_query="SELECT lektor,lower(kurzbz || '_lkt') AS mlist_name FROM tbl_lehrveranstaltung NATURAL JOIN tbl_studiengang
		WHERE studiensemester_kurzbz='$studiensemester'
		AND lektor NOT LIKE '\\\\_%' AND kurzbz!='TW' AND (lektor,lower(kurzbz || '_lkt')) NOT IN
		(SELECT uid,mailgrp_kurzbz FROM tbl_personmailgrp
			WHERE mailgrp_kurzbz LIKE '%\\\\_lkt' AND mailgrp_kurzbz!='tw_lkt' AND mailgrp_kurzbz!='tw_fix_lkt')";
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).$sql_query;
	while($row=pg_fetch_object($result))
	{
     	$sql_query="INSERT INTO tbl_personmailgrp VALUES ('$row->lektor','$row->mlist_name', now(), 'mlists_generate.php')";
		if(!pg_query($conn, $sql_query))
			$error_msg.=pg_errormessage($conn).$sql_query;
		echo '-';
		flush();
	}


	// **************************************************************
	// Studentenverteiler abgleichen
	// Studenten holen die nicht mehr in den Verteiler gehoeren
	echo '<BR>Studenten-Verteiler werden abgeglichen!<BR>';
	flush();
	$sql_query="SELECT mailgrp_kurzbz,uid FROM tbl_personmailgrp NATURAL JOIN tbl_einheit WHERE (uid, mailgrp_kurzbz) NOT IN (SELECT uid, mailgrp_kurzbz FROM tbl_einheitstudent NATURAL JOIN tbl_einheit WHERE mailgrp_kurzbz IS NOT NULL)";


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


	echo $error_msg;
	?>
	<BR>
	<P>
  		Die Mailinglisten wurden abgeglichen. <BR>
  	</P>
</BODY>
</HTML>