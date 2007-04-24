<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	require_once('../../vilesci/config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/globals.inc.php');

	if (!$conn = pg_pconnect(CONN_STRING))
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	if(!($result_stg=pg_query($conn, "SELECT studiengang_kz, bezeichnung, lower(typ::varchar(1) || kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz ASC")))
		die(pg_errormessage($conn));
	$num_rows=pg_num_rows($result_stg);

?>
<HTML>
<HEAD>
<TITLE>Mailinglisten</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
		$sql_query="SELECT * FROM public.tbl_gruppe WHERE studiengang_kz=$stg_id AND mailgrp=true ORDER BY gruppe_kurzbz";
		//echo $sql_query;
		if(!($result_mg=pg_query($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_mg=pg_num_rows($result_mg);

		// Mailgroups
		for  ($j=0; $j<$nr_mg; $j++)
		{
			$row_mg=pg_fetch_object($result_mg, $j);
			$mg_kurzbz=$row_mg->gruppe_kurzbz;
			$sql_query='SELECT tbl_benutzergruppe.uid, nachname, vorname '.
				       'FROM campus.vw_benutzer, public.tbl_benutzergruppe '.
				       'WHERE vw_benutzer.uid=tbl_benutzergruppe.uid AND '.
				       "UPPER(gruppe_kurzbz)=UPPER('$mg_kurzbz') AND tbl_benutzergruppe.uid NOT LIKE '\\\\_%' ".
					   'AND studiensemester_kurzbz IS NULL ORDER BY nachname';
			//echo $sql_query;
			if(!($result_person=pg_query($conn, $sql_query)))
				die(pg_errormessage($conn));

			// File Operations
			$name=$mg_kurzbz.'.txt';
			$name=strtolower($name);
			$fp=fopen('../../../mlists/'.$name,"w");
			//$fp=fopen('../../../../mlists/'.$name,"w");

			$nr_person=pg_num_rows($result_person);
			for  ($p=0; $p<$nr_person; $p++)
			{
				$row=pg_fetch_object($result_person, $p);
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
	        WHERE alias<>'' AND (studiengang_kz NOT IN($noalias_kz) OR studiengang_kz is null)
	        ORDER BY nachname, vorname";

	if($result = pg_query($conn, $qry))
	{
		$fp=fopen('../../../mlists/tw_alias.txt',"w");
		while($row=pg_fetch_object($result))
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
