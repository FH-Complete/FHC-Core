<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*
 * Ermoeglicht das Anmelden zu Freifaechern
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/phrasen.class.php');

if (!$db = new basis_db())
	$db = false;
$sprache = getSprache();
$p = new phrasen($sprache);

$user = get_uid();
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<title><?php echo $p->t('freifach/freifaecherAnmeldungsuebersicht');?></title>
	</head>
	<body>
	<h1><?php echo $p->t('freifach/freifaecherAnmeldungsuebersicht');?></h1>
	<?php echo $p->t('freifach/bitteFreifachAuswaehlen');?>
	<br />
<?php
$lvid = trim(isset($_POST['lvid'])?$_POST['lvid']:'');

//Aktuelles Studiensemester holen
$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getaktorNext();

$lv_obj = new lehrveranstaltung();
if ($lv_obj->load_lva('0',null,null,true,null,'bezeichnung'))
{
	echo "<FORM method='POST' name='frmauswahl'>";
	echo "<SELECT name='lvid' onchange='window.document.frmauswahl.submit();'>";
	if ($lvid == '')
		echo "\n<OPTION value='0' selected>--".$p->t('freifach/auswahl')."--</OPTION>";
	foreach ($lv_obj->lehrveranstaltungen as $row)
	{
		if ($lvid == $row->lehrveranstaltung_id)
			echo "\n<OPTION value='$row->lehrveranstaltung_id' selected>$row->bezeichnung</OPTION>";
		else
			echo "\n<OPTION value='$row->lehrveranstaltung_id'>$row->bezeichnung</OPTION>";
	}
	echo "\n</SELECT>";
	echo "\n</FORM>";
}
else
{
	die($p->t('freifach/fehlerBeimAuslesenFreifach'));
}

//Wenn das Formular abgeschickt wurde
if ($lvid != '')
{
	$qry = "SELECT
				vorname,
				nachname,
				uid,
				tbl_student.semester as semester,
				tbl_studiengang.kurzbzlang
			FROM
				campus.vw_benutzer
				LEFT JOIN
				(public.tbl_student LEFT JOIN public.tbl_studiengang using (studiengang_kz)) ON (student_uid = uid)
			WHERE
				uid IN (SELECT uid FROM campus.tbl_benutzerlvstudiensemester
				        WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($stsem).")
			ORDER BY
				nachname, vorname";

	if ($result = $db->db_query($qry))
	{
		$ff = array();
		$content='';

		$mailto=array();
		$mailto_idx=0;
		$content .= "<table>\n  <tr class='liste'><th></th><th>".$p->t('global/nachname')."</th><th>".$p->t('global/vorname')."</th><th>".$p->t('global/mail')."</th><th>".$p->t('global/studiengang')."</th><th>".$p->t('global/semester')."</th></tr>";
		$i=0;
		while ($row = $db->db_fetch_object($result))
		{
			$i++;
			$content .= "\n<tr class='liste".($i%2)."'><td>$i</td><td>$row->nachname</td><td>$row->vorname</td><td><a href='mailto:$row->uid@technikum-wien.at'>$row->uid@technikum-wien.at</a></td><td align='center'>$row->kurzbzlang</td><td align='center'>$row->semester</td></tr>";

			if (isset($mailto[$mailto_idx]) && mb_strlen($mailto[$mailto_idx])>450)
				$mailto_idx++;

			if (isset($mailto[$mailto_idx]))
				$mailto[$mailto_idx]=$mailto[$mailto_idx].',';
			else
				$mailto[$mailto_idx]='';
			$mailto[$mailto_idx]=$mailto[$mailto_idx].$row->uid.'@'.DOMAIN;
		}
		$content .= "</table>";

		if ($i == 0)
		{
			echo "<b>".$p->t('freifach/keineAnmeldungenFuerDiesesFreifach')."</b>";
		}
		else
		{
			echo $content;
			echo "<br />";
			echo "<script>
				function openMail()
				{";
			if (count($mailto) > 1)
				echo "alert('Aufgrund der großen Anzahl an Empfängern, muss die Nachricht auf mehrere E-Mails aufgeteilt werden!');";
			foreach ($mailto as $val)
				echo "window.location.href='mailto:".$val."';\n";
			echo '
				}
				</script>';
			echo '<a href="#Mail" onclick="openMail()">'.$p->t('freifach/MailAnAlleSenden').'</a>';
		}
	}
	else
		echo $p->t('freifach/fehlerBeimAuslesen');
}

?>
</body>
</html>