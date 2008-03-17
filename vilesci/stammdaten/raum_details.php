<?php
	require_once('../config.inc.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/ort.class.php');

	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$reloadstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
	$errorstr = ''; //fehler beim insert
	$sel = '';
	$chk = '';

	$sg_var = new ort($conn);
		
	$ort_kurzbz = '';
	$bezeichnung = '';
	$planbezeichnung = '';
	$max_person = '';
	$lehre = "t";
	$reservieren = "f";
	$aktiv = "t";
	$lageplan = '';
	$dislozierung = '';
	$kosten = '';
	$ausstattung = '';
	$stockwerk = '';
	
	$neu = "true";
	
	if(isset($_POST["schick"]))
	{
		$ort_kurzbz = $_POST["ort_kurzbz"];
		$bezeichnung = $_POST["bezeichnung"];
		$planbezeichnung = $_POST["planbezeichnung"];
		$max_person = $_POST["max_person"];
		$lageplan = $_POST["lageplan"];
		$dislozierung = $_POST["dislozierung"];
		$kosten = $_POST["kosten"];
		$ausstattung = $_POST["ausstattung"];
		$stockwerk = $_POST["stockwerk"];

		
		$sg_update = new ort($conn);
		$sg_update->ort_kurzbz = $ort_kurzbz;
		$sg_update->bezeichnung = $bezeichnung;
		$sg_update->planbezeichnung = $planbezeichnung;
		$sg_update->max_person = $max_person;
		$sg_update->lehre = isset($_POST["lehre"]);
		$sg_update->reservieren = isset($_POST["reservieren"]);
		$sg_update->aktiv = isset($_POST["aktiv"]);
		$sg_update->lageplan = $lageplan;
		$sg_update->dislozierung = $dislozierung;
		$sg_update->kosten = $kosten;
		$sg_update->ausstattung = $ausstattung;
		$sg_update->stockwerk = $stockwerk;

		
		if ($_POST["neu"] == "true")
			$sg_update->new = 1;

		if(!$sg_update->save())
		{
			$errorstr .= $sg_update->errormsg;
		}
		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht.location.href='raum_uebersicht.php';";
		$reloadstr .= "</script>\n";
	}



	if ((isset($_REQUEST['ort_kurzbz'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= "true")))
	{
		$ort_kurzbz = $_REQUEST["ort_kurzbz"];
		$sg = new ort($conn,$ort_kurzbz);
		if ($sg->errormsg!='')
			die($sg->errormsg);
		$ort_kurzbz = $sg->ort_kurzbz;
		$bezeichnung = $sg->bezeichnung;
		$planbezeichnung = $sg->planbezeichnung;
		$max_person = $sg->max_person;
		$lehre = $sg->lehre;
		$reservieren = $sg->reservieren;
		$aktiv = $sg->aktiv;
		$lageplan = $sg->lageplan;
		$dislozierung = $sg->dislozierung;
		$kosten = $sg->kosten;
		$ausstattung = $sg->ausstattung;
		$stockwerk = $sg->stockwerk;
		$neu = "false";
	}
		
	$htmlstr .= "<br><div class='kopf'>Raum <b>".$ort_kurzbz."</b></div>\n";
	$htmlstr .= "<form action='raum_details.php' method='POST' name='raumform'>\n";
	$htmlstr .= "<table class='detail'>\n";


	$htmlstr .= "	<tr><td colspan='3'>&nbsp;</tr>\n";
	$htmlstr .= "	<tr>\n";

	// erste Spalte start
	$htmlstr .= "		<td valign='top'>\n";

	$htmlstr .= "			<table>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Kurzbezeichnung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='ort_kurzbz' size='12' maxlength='8' value='".$ort_kurzbz."' onchange='submitable()'></td>\n";
	$htmlstr .= "					<td>Bezeichnung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bezeichnung' size='32' maxlength='30' value='".$bezeichnung."' onchange='submitable()'></td>\n";
	$htmlstr .= "					<td>Planbezeichnung</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='planbezeichnung' size='12' maxlength='5' value='".$planbezeichnung."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Person</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_person' size='12' maxlength='8' value='".$max_person."' onchange='submitable()'></td>\n";
	$htmlstr .= "					<td>Dislozierung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='dislozierung' size='16' maxlength='8' value='".$dislozierung."' onchange='submitable()'></td>\n";
	$htmlstr .= "					<td>Kosten</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='kosten' size='18' maxlength='16' value='".$kosten."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Stockwerk</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='stockwerk' size='8' maxlength='5' value='".$stockwerk."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Lehre</td>\n";
	$htmlstr .= " 					<td>\n";
	if($lehre == 't')
	{
		$chk1 = "checked";
	}
	else
	{
		$chk1 = '';
	}
	$htmlstr .= "					<input type='checkbox' name='lehre' value='t'".$chk1." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "					<td valign='top'>Reservieren</td>\n";
	$htmlstr .= " 					<td>\n";
	if($reservieren == 't')
	{
		$chk2 = "checked";
	}
	else
	{
		$chk2 = '';
	}
	$htmlstr .= "					<input type='checkbox' name='reservieren' value='t'".$chk2." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "					<td valign='top'>Aktiv</td>\n";
	$htmlstr .= " 					<td>\n";
	if($aktiv == 't')
	{
		$chk3 = "checked";
	}
	else
	{
		$chk3 = '';
	}
	$htmlstr .= "					<input type='checkbox' name='aktiv' value='t'".$chk3." onchange='submitable()'>";
	$htmlstr .= " 					</td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Lageplan</td>\n";
	$htmlstr .= " 					<td><textarea name='lageplan' cols='37' rows='5' onchange='submitable()'>".$lageplan."</textarea></td>\n";
	$htmlstr .= " 					<td>\n</td>\n<td>\n</td>\n";
	$htmlstr .= "					<td valign='top'>Ausstattung</td>\n";
	$htmlstr .= " 					<td><textarea name='ausstattung' cols='37' rows='5' onchange='submitable()'>".$ausstattung."</textarea></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "</table>\n";
	$htmlstr .= "<br>\n";
	$htmlstr .= "<div align='right' id='sub'>\n";
	$htmlstr .= "	<span id='submsg' style='color:red; visibility:hidden;'>Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>\n";
	$htmlstr .= "	<input type='hidden' name='neu' value='".$neu."'>";
	$htmlstr .= "	<input type='submit' value='Speichern' name='schick'>\n";
	$htmlstr .= "	<input type='button' value='Reset' onclick='unchanged()'>\n";
	$htmlstr .= "</div>";
	$htmlstr .= "</form>";
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Raum - Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">
function unchanged()
{
		document.raumform.reset();
		document.raumform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
		checkrequired(document.raumform.ort_kurzbz);
}

function checkrequired(feld)
{
	if(feld.value == '')
	{
		feld.className = "input_error";
		return false;
	}
	else
	{
		feld.className = "input_ok";
		return true;
	}
}

function submitable()
{
	required1 = checkrequired(document.raumform.ort_kurzbz);

	if(!required1)
	{
		document.raumform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
	}
	else
	{
		document.raumform.schick.disabled = false;
		document.getElementById("submsg").style.visibility="visible";
	}
}
</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>