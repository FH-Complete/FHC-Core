<?php
	/***************************************************************
	 Script zur Feststellung, welche Daten im FAS nicht durchkommen.
	 ***************************************************************/
	$adress='pam@technikum-wien.at';
	include('../../vilesci/config.inc.php');
	$conn=pg_connect($conn_string);
	$conn_fas=pg_connect($conn_string_fas);

	$headers ="MIME-Version: 1.0\r\n";
	$headers.="Content-type: text/html; charset=iso-8859-1\r\n";
	$headers.="From: vilesci@technikum-wien.at";

	$bgcolor[0]="#CCCCCC";
	$bgcolor[1]="#DDDDDD";

	$text='<html><head><title>FAS-Synchro mit TEMPUS fehlerhafte Studenten</title></head>';
	$text.='<body>';
	// Start Check
	$sql_query="SELECT * FROM fas_view_student_vilesci WHERE uid NOT IN ";
	$sql_query.="(SELECT uid FROM fas_view_student_vilesci WHERE semester >0 AND semester <8 AND";
	$sql_query.=" verband IS NOT NULL AND uid IS NOT NULL AND uid NOT LIKE '') ORDER BY kennzahl,nachname"; // LIMIT 5";
	//echo $sql_query."<br>";
	$result=pg_exec($conn_fas, $sql_query);
	$num_rows=pg_numrows($result);
	$num_fields=pg_numfields($result);
	$text.="Dies ist eine automatische eMail!<BR><BR>";
	$text.="Es wurde eine Ueberpruefung der Daten in der FAS-View fuer Studenten durchgeführt.<BR>";
	$text.="Anzahl der fehlerhaften Daten: $num_rows <BR><BR>";
	$text.="Folgende Studenten haben fehlerhafte Daten im FAS<BR><BR>";
	$text.='<TABLE border="0"><TR><TH>STG_KZ</TH><TH>uid</TH><TH>Titel</TH><TH>Vornamen</TH><TH>Nachname</TH><TH>Matrikelnr</TH>';
	$text.='<TH>Semester</TH><TH>Verband</TH><TH>Gruppe</TH></TR>';
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result,$i);
		$text.='<TR bgcolor="'.$bgcolor[$i%2].'"'."><TD>$row->kennzahl</TD><TD>$row->uid</TD><TD>$row->titel</TD><TD>$row->vornamen</TD><TD>$row->nachname</TD><TD>$row->perskz</TD>";
		$text.="<TD>$row->semester</TD><TD>$row->verband</TD><TD>$row->gruppe</TD></TR>";
	}
	$text.='</TABLE></BODY></HTML>';

	echo $text;

	if (mail($adress,"FAS Synchro mit TEMPUS fehlerhafte Studenten",$text,$headers))
		echo 'Mail wurde verschickt an '.$adress.'!<br>';
	else
		echo "Mail konnte nicht verschickt werden!<br>";

?>
