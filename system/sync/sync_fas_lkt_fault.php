<?php
	/***************************************************************
	 Script zur Feststellung, welche Daten im FAS nicht durchkommen.
	 ***************************************************************/
	$adress='pam@technikum-wien.at';
	include('../../vilesci/config.inc.php');
	$conn=pg_connect($conn_string);
	$conn_fas=pg_connect($conn_string_fas);

	$headers ="MIME-Version: 1.0\r\n";
	$headers.="Content-type: text/html; charset=UTF-8\r\n";
	$headers.="From: vilesci@technikum-wien.at";

	$bgcolor[0]="#CCCCCC";
	$bgcolor[1]="#DDDDDD";

	$text='<html><head><title>FAS-Synchro mit TEMPUS fehlende Lektoren</title></head>';
	$text.='<body>';

	// Start Check
	//Daten aus vilesci holen
	$sql_query="SELECT tbl_person.*,tbl_mitarbeiter.personalnummer,tbl_mitarbeiter.kurzbz,tbl_mitarbeiter.fixangestellt FROM tbl_person join tbl_mitarbeiter using(uid) WHERE uid NOT LIKE '\\\\_%' ORDER BY nachname";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	// Daten aus dem FAS
	$sql_query="SELECT * FROM fas_view_lektoren_vilesci";
	//echo $sql_query."<br>";
	$result_fas=pg_exec($conn_fas, $sql_query);
	$fehlend=$num_rows-$num_rows_fas;
	$num_rows_fas=pg_numrows($result_fas);
	$text.="Dies ist eine automatische eMail!<BR><BR>";
	$text.="Es wurde eine Ueberpruefung der Daten in der FAS-View fuer Lektoren durchgeführt.<BR>";
	$text.='Anzahl der fehlenden Daten: $fehlend <BR><BR>';
	$text.="Folgende Lektoren scheinen in der FAS-View nicht auf";
	$text.='<TABLE border="0"><TR bgcolor="#D3DCE3"><TH>uid</TH><TH>Titel</TH><TH>Vornamen</TH><TH>Nachname</TH></TR>';
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result,$i);
		$sql_query="SELECT uid FROM fas_view_lektoren_vilesci WHERE uid LIKE '$row->uid'";
		$result_fas=pg_exec($conn_fas, $sql_query);
		if (pg_numrows($result_fas)!=1)
			$text.='<TR bgcolor="'.$bgcolor[$i%2].'"'."><TD>$row->uid</TD><TD>$row->titel</TD><TD>$row->vornamen</TD><TD>$row->nachname</TD></TR>";
	}
	$text.='</TABLE></BODY></HTML>';

	echo $text;

	if (mail($adress,"FAS Synchro mit TEMPUS fehlende Lektoren",$text,$headers))
		echo 'Mail wurde verschickt an '.$adress.'!<br>';
	else
		echo "Mail konnte nicht verschickt werden!<br>";

?>


