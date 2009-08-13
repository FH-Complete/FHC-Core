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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/mail.class.php');

echo '<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Stundenplan-Check (stpldev-le)</title>
</head>
<body>';

// Startvariablen setzen
$adress='fas_sync@technikum-wien.at';
$adress_stpl='stpl@technikum-wien.at';

$message_stpl='';
$message_sync='';
$count_del=0;
$count_ins=0;
$count_upd=0;
$count_err=0;

$db = new basis_db();

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

if (!$result = $db->db_query($sql_query))
{
	echo $sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
	$message.=$sql_query.' fehlgeschlagen!<BR>'.$db->db_last_error();
}
else
{
	flush();
	while ($row = $db->db_fetch_object($result))
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
$mail = new mail('pam@technikum-wien.at','stpl@technikum-wien.at','Stundenplan update','');
$mail->setHTMLContent($message);
$mail->send();

$message_stpl='<HTML><BODY>'.$message_tmp.$message_stpl.'</BODY></HTML>';

$mail = new mail('stpl@technikum-wien.at','stpl@technikum-wien.at','Stundenplan update','');
$mail->setHTMLContent($message);
$mail->send();
?>
</body>
</html>
