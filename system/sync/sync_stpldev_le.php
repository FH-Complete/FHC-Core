<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Stundenplan-Check (stpldev-le)</title>
</head>
<body>
<?php
require('../../vilesci/config.inc.php');
require('../../include/functions.inc.php');

$conn=pg_connect(CONN_STRING);

// Startvariablen setzen
$adress='fas_sync@technikum-wien.at';
//$adress_stpl='pam@technikum-wien.at';
$adress_stpl='stpl@technikum-wien.at';

$count_del=0;
$count_ins=0;
$count_upd=0;
$count_err=0;

// Mails an die Lektoren und Verbaende
$message='';


// Mail Headers festlegen
$headers= "MIME-Version: 1.0\r\n";
$headers.="Content-Type: text/html; charset=UTF-8\r\n";


$message_begin='Dies ist eine automatische Mail!<BR>Es haben sich folgende Aenderungen in Ihrem Stundenplan ergeben:<BR>';


/**************************************************
 * Datensaetze holen die nicht mehr plausibel sind
 */
echo 'Folgende Lehreinheiten sind nicht plausibel.<BR>';flush();
$sql_query="SELECT DISTINCT lehreinheit_id,studiengang_kz,semester,gruppe_kurzbz,mitarbeiter_uid,ort_kurzbz,
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid AS lehreinheitmitarbeiter_uid 
			FROM lehre.tbl_stundenplandev LEFT OUTER JOIN lehre.tbl_lehreinheitmitarbeiter USING (lehreinheit_id, mitarbeiter_uid) 
			WHERE datum>=now() AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid IS NULL;";
//echo $sql_query.'<BR>';
if (!$result=pg_query($conn, $sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
	$message.=$sql_query.' fehlgeschlagen!<BR>'.pg_last_error($conn);
}
else
{
	flush();
	while ($row=pg_fetch_object($result))
	{
		echo $row->lehreinheit_id.'-'.$row->studiengang_kz.'-'.$row->semester.'-'.$row->gruppe_kurzbz.'-'.$row->mitarbeiter_uid.'-'.$row->ort_kurzbz.'<BR>';
		$message.=$row->lehreinheit_id.'-'.$row->studiengang_kz.'-'.$row->semester.'-'.$row->gruppe_kurzbz.'-'.$row->mitarbeiter_uid.'-'.$row->ort_kurzbz.'<BR>';
	}
	$message.='</table>';
}


/**************************************************
 * Mails verschicken
 */

// Mail an Admin
$message_tmp=$count_upd.' Datens&auml;tze wurden ge&auml;ndert.<BR>
			'.$count_ins.' Datens&auml;tze wurden hinzugef&uuml;gt.<BR>
			'.$count_del.' Datens&auml;tze wurden gel&ouml;scht.<BR>
			'.$count_err.' Fehler sind dabei aufgetreten!<BR><BR>';
echo '<BR>'.$message_tmp;
$message_sync='<HTML><BODY>'.$message_tmp.$message_sync.$message_stpl.'</BODY></HTML>';
mail('pam@technikum-wien.at',"Stundenplan update",$message,$headers."From: stpl@technikum-wien.at");
$message_stpl='<HTML><BODY>'.$message_tmp.$message_stpl.'</BODY></HTML>';
mail('stpl@technikum-wien.at',"Stundenplan update",$message,$headers."From: stpl@technikum-wien.at");
?>
</body>
</html>
