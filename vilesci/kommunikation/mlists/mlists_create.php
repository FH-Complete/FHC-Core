<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($result_stg=pg_exec($conn, "SELECT studiengang_kz, bezeichnung, kurzbz FROM tbl_studiengang ORDER BY kurzbz ASC")))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($result_stg);
?>
<HTML>
<HEAD>
<TITLE>Mailinglisten</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</HEAD>

<BODY class="background_main">
<H3>MailingListen </H3>


<?php
	$crlf="\n";
	for ($i=0; $i<$num_rows; $i++)
	{
		$row=pg_fetch_object($result_stg, $i);
     	$stg_id=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT * FROM tbl_mailgrp WHERE studiengang_kz=$stg_id ORDER BY mailgrp_kurzbz";
		//echo $sql_query;
		if(!($result_mg=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_mg=pg_numrows($result_mg);

		// Mailgroups
		for  ($j=0; $j<$nr_mg; $j++)
		{
			$row_mg=pg_fetch_object($result_mg, $j);
			$mg_kurzbz=$row_mg->mailgrp_kurzbz;
			$sql_query='SELECT uid, nachname, vornamen '.
				       'FROM tbl_person as p join tbl_personmailgrp using(uid) '.
				       'WHERE tbl_personmailgrp.mailgrp_kurzbz=\''.$mg_kurzbz.'\' '."AND p.uid NOT LIKE '\\\\_%'".
					   'ORDER BY nachname';
			//echo $sql_query;
			if(!($result_person=pg_exec($conn, $sql_query)))
				die(pg_errormessage($conn));

			// File Operations
			$name=$mg_kurzbz.'.txt';
			$name=strtolower($name);
			$fp=fopen('../../../../mlists/'.$name,"w");
			//$fp=fopen('../../../../mlists/'.$name,"w");
			
			$nr_person=pg_numrows($result_person);
			for  ($p=0; $p<$nr_person; $p++)
			{
				$row=pg_fetch_object($result_person, $p);
				fwrite($fp, '#'.$row->nachname.' '.$row->vornamen.$crlf.$row->uid.$crlf);
			}
			fclose($fp);
			echo $name.' created<br>';
			flush();
		}
	}
	
	$qry = "SELECT vornamen, nachname, uid, alias FROM tbl_person where alias<>'' ORDER BY nachname, vornamen";
	if($result = pg_query($conn, $qry))
	{
		$fp=fopen('../../../../mlists/tw_alias.txt',"w");
		while($row=pg_fetch_object($result))
		{
			fwrite($fp,"# ".$row->nachname." ".$row->vornamen.$crlf);
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