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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Raab <gerald.raab@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
	
$uid=get_uid();

if(isset($_GET['uid']))
{
	// Administratoren duerfen die UID als Parameter uebergeben um die Studienerfolgsbestätigung
	// von anderen Personen anzuzeigen
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
		$uid=$_GET['uid'];
}

if(isset($_GET["prestudent_id"]))
	$prestudent_id = $_GET["prestudent_id"];

if(isset($_GET['lang']) && $_GET['lang']=='en')
	$xsl = 'StudienerfolgEng';
else
	$xsl = 'Studienerfolg';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.$p->t('tools/studienerfolgsbestaetigung').'</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
function createStudienerfolg(prestudent_id)
{
	var stsem = document.getElementById("stsem").value;
	var finanzamt = document.getElementById("finanzamt").checked;
	
	if(finanzamt)
		finanzamt = "&typ=finanzamt";
	else
		finanzamt = "";
    
    if(stsem == "alle")
        alle = "&all=1";
    else
        alle = "";
    
    window.location.href= "../pdfExport.php?xml=studienerfolg.rdf.php&xsl='.$xsl.'&ss="+stsem+"&prestudent_id="+prestudent_id+finanzamt+alle;
}
	function MM_jumpMenu(targ, selObj, restore)
	{
		eval(targ + ".location=\'" + selObj.options[selObj.selectedIndex].value + "\'");

		if(restore)
		{
			selObj.selectedIndex = 0;
		}
	}
</script>
</head>

<body style="margin:5px;">
<h1>'.$p->t('tools/studienerfolgsbestaetigung').'</h1>';

$prestudent = new prestudent();
$prestudent->getPrestudentsFromUid($uid);

if(count($prestudent->result) > 0)
{
	/*** dropdown fuer studiengang ***/
	echo "<div class='contentBox'>";
	echo "<span>".$p->t('global/studiengang')."</span>";
	echo "<SELECT name='stg' onChange=\"MM_jumpMenu('self',this,0)\">";
	echo "<option disabled ".($prestudent_id ? "" : "selected")." value>".$p->t('global/auswaehlen')."</option>";

	foreach ($prestudent->result as $pres)
	{
		$studiengang = new studiengang($pres->studiengang_kz);
		echo "<OPTION ".(isset($prestudent_id) && $prestudent_id == $pres->prestudent_id ? "selected" : "")." value='studienerfolgsbestaetigung.php?prestudent_id=$pres->prestudent_id".(isset($uid)?"&uid=".$uid : "")."'>$studiengang->bezeichnung</OPTION>";
	}
	echo "</SELECT>";
}
else
	echo "<div class='contentBox'>Es wurde keine uid uebergeben und unter ihrem Benutzer konnte kein Student gefunden werden.</div>";	// TODO EINE phrasen?

if(isset($prestudent_id))
{
	echo '<br>'.$p->t('tools/studiensemesterAuswaehlen').'<br><br>';

	$qry = "SELECT distinct studiensemester_kurzbz FROM campus.vw_student JOIN public.tbl_prestudentstatus USING(prestudent_id) WHERE prestudent_id=".$db->db_add_param($prestudent_id, FHC_INTEGER);
	if($result = $db->db_query($qry))
	{
		echo $p->t('global/studiensemester').': <SELECT id="stsem">';
		  echo '<OPTION value="alle">alle Semester</OPTION>';

		$stsem_obj = new studiensemester();
		$stsem = $stsem_obj->getPrevious();

		while($row = $db->db_fetch_object($result))
		{
			if($stsem==$row->studiensemester_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$row->studiensemester_kurzbz.'</OPTION>';
		}

		echo '</SELECT>';
		echo '<br><br><INPUT type="checkbox" id="finanzamt">'.$p->t('tools/vorlageWohnsitzfinanzamt').'<br>';
		echo '<br><br><INPUT type="button" value="'.$p->t('global/erstellen').'" onclick="createStudienerfolg('.$prestudent_id.')" />';
	}
}
echo '
</body>
</html>';		
?>
