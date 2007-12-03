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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/lehrveranstaltung.class.php');
	require_once('../../include/studiengang.class.php');
		
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	$reloadstr = "";  // neuladen der liste im oberen frame	
	$errorstr='';
	$htmlstr='';
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');
	$semester = (isset($_GET['semester'])?$_GET['semester']:'-1');
	
	$stg_arr = array();
	$sprache_arr = array();
	
	if(isset($_POST["del"]))
	{
		

	}
	
	if(isset($_POST["schick"]))
	{
		$lv = new lehrveranstaltung($conn);
		
		if(isset($_POST['lv_id']) && $_POST['lv_id']!='')
		{		
			if($lv->load($_POST['lv_id']))
			{
				$lv->new=false;
			}
		}
		else 
		{
			$lv->new=true;
			$lv->insertamum=date('Y-m-d H:i:s');
			$lv->insertvon = $user;
		}
		
		$lv->kurzbz = $_POST['kurzbz'];
		$lv->bezeichnung = $_POST['bezeichnung'];
		$lv->studiengang_kz = $_POST['studiengang_kz'];
		$lv->semester = $_POST['semester'];
		$lv->sprache = $_POST['sprache'];
		$lv->ects  = str_replace(',','.',$_POST['ects']);
		$lv->semesterstunden = $_POST['semesterstunden'];
		$lv->anmerkung = $_POST['anmerkung'];
		$lv->lehre = isset($_POST['lehre']);
		$lv->lehreverzeichnis = $_POST['lehreverzeichnis'];
		$lv->aktiv = isset($_POST['aktiv']);
		$lv->planfaktor = $_POST['planfaktor'];
		$lv->planlektoren = $_POST['planlektoren'];
		$lv->planpersonalkosten = $_POST['planpersonalkosten'];
		$lv->plankostenprolektor = $_POST['plankostenprolektor'];
		$lv->updateamum = date('Y-m-d H:i:s');
		$lv->updatevon = $user;
		$lv->sort = $_POST['sort'];
		$lv->zeugnis = isset($_POST['zeugnis']);
		$lv->projektarbeit = isset($_POST['projektarbeit']);
		
		if(!$lv->save())
			$errorstr = "Fehler beim Speichern der Daten: $lv->errormsg";
		
		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht.location.href='lehrveranstaltung.php?stg_kz=$lv->studiengang_kz&semester=$lv->semester';";
		$reloadstr .= "</script>\n";		
	}
		
	$sg = new studiengang($conn);
	$sg->getAll('typ, kurzbz', false);
	foreach($sg->result as $studiengang)
	{
		$stg_arr[$studiengang->studiengang_kz] = $studiengang->kuerzel;
	}
	
	$qry = "SELECT * FROM tbl_sprache ORDER BY sprache";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			$sprache_arr[] = $row->sprache;
		}
	}
	
	if (isset($_REQUEST['lv_id']) || isset($_REQUEST['neu']))
	{		
		$lv = new lehrveranstaltung($conn);
		
		if (isset($_REQUEST['lv_id']))
		{
			$lvid = $_REQUEST['lv_id'];
			if (!$lv->load($lvid))
				$htmlstr .= "<br><div class='kopf'>Lehrveranstaltung <b>".$lvid."</b> existiert nicht</div>";
		}
		
		$htmlstr .= "<br><div class='kopf'>Lehrveranstaltung</div>\n";
		$htmlstr .= "<form action='lehrveranstaltung_details.php' method='POST'>\n";
		$htmlstr .= "<input type='hidden' name='lv_id' value='".$lv->lehrveranstaltung_id."'>\n";
		
		$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
		$htmlstr .= "<tr></tr>\n";
				
		$htmlstr .= "	<tr>\n";				
		$htmlstr .= "		<td>Kurzbz</td>";
		$htmlstr .= "		<td><input type='text' name='kurzbz' value='$lv->kurzbz'\n</td>";
		$htmlstr .= "		<td>Bezeichnung</td>";
		$htmlstr .= "		<td colspan='3'><input type='text' name='bezeichnung' value='".htmlentities($lv->bezeichnung, ENT_QUOTES)."' size='60' maxlength='128'></td>\n";
					
		$htmlstr .= "</tr><tr>";
		$htmlstr .= "		<td>Studiengang</td>";
		$htmlstr .= "		<td><select name='studiengang_kz'>\n";
			
		foreach ($stg_arr as $stg_key=>$stg_kurzbz)
		{
			if (($stg_kz!='-1' && $stg_kz==$stg_key) || ($lv->studiengang_kz!='' && $lv->studiengang_kz == $stg_key ))
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$stg_key."' ".$sel.">".$stg_kurzbz."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td>Semester</td>";
		$htmlstr .= "		<td><select name='semester'>\n";
			
		for ($i = 0; $i < 10; $i++)
		{
			if (($semester!='-1' && $semester==$i) || $lv->semester == $i)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$i."' ".$sel.">".$i."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td>Sprache</td>";
		$htmlstr .= "		<td><select name='sprache'>\n";
			
		foreach ($sprache_arr as $sprache)
		{
			if ($lv->sprache == $sprache)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$sprache."' ".$sel.">".$sprache."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "	</tr><tr>\n";
		
		$htmlstr .= "	<td>ECTS</td>";
		$htmlstr .= "	<td><input type='text' name='ects' value='$lv->ects' maxlength='5'></td>";
		$htmlstr .= "	<td>Semesterstunden</td>";
		$htmlstr .= "	<td><input type='text' name='semesterstunden' value='$lv->semesterstunden' maxlength='3'></td>";
		$htmlstr .= "	<td>Anmerkung</td>";
		$htmlstr .= "	<td><input type='text' name='anmerkung' value='$lv->anmerkung' maxlength='64'></td>";
		$htmlstr .= "	</tr><tr>\n";
		
		$htmlstr .= "	<td>Sort</td>";
		$htmlstr .= "	<td><input type='text' name='sort' value='$lv->sort' maxlength='2'></td>";
		$htmlstr .= "	<td>Lehreverzeichnis</td>";
		$htmlstr .= "	<td><input type='text' name='lehreverzeichnis' value='$lv->lehreverzeichnis' maxlength='16'></td>";
		$htmlstr .= "	<td>Planfaktor</td>";
		$htmlstr .= "	<td><input type='text' name='planfaktor' value='$lv->planfaktor' maxlength='3'></td>";
		$htmlstr .= "	</tr><tr>\n";

		$htmlstr .= "	<td>Planlektoren</td>";
		$htmlstr .= "	<td><input type='text' name='planlektoren' value='$lv->planlektoren' maxlength='2'></td>";
		$htmlstr .= "	<td>Planpersonalkosten</td>";
		$htmlstr .= "	<td><input type='text' name='planpersonalkosten' value='$lv->planpersonalkosten' maxlength='7'></td>";
		$htmlstr .= "	<td>Plankostenprolektor</td>";
		$htmlstr .= "	<td><input type='text' name='plankostenprolektor' value='$lv->plankostenprolektor' maxlength='6'></td>";
		$htmlstr .= "	</tr><tr>\n";
		
		$htmlstr .= "	<td>Lehre</td>";
		$htmlstr .= "	<td><input type='checkbox' name='lehre' ".($lv->lehre?'checked':'')."></td>";
		$htmlstr .= "	<td>Aktiv</td>";
		$htmlstr .= "	<td><input type='checkbox' name='aktiv' ".($lv->aktiv?'checked':'')."></td>";			
		$htmlstr .= "	<td>Zeugnis</td>";
		$htmlstr .= "	<td><input type='checkbox' name='zeugnis' ".($lv->zeugnis?'checked':'')."></td>";
		$htmlstr .= "	</tr><tr>\n";
		
		$htmlstr .= "	<td>Projektarbeit</td>";
		$htmlstr .= "	<td><input type='checkbox' name='projektarbeit' ".($lv->projektarbeit?'checked':'')."></td>";
		
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "<br>\n";
		$htmlstr .= "<div align='right' id='sub'>\n";
		$htmlstr .= "	<input type='submit' value='Speichern' name='schick'>\n";
		$htmlstr .= "</div>";
		$htmlstr .= "</form>\n";
			
	}
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Studiengang - Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">

</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>