<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/erhalter.class.php');

	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$reloadstr = "";  // neuladen der liste im oberen frame

	if((isset($_POST["schick"])) || (isset($_POST["schickneu"])))
	{

		$kennzahl = $_POST["studiengang_kz"];
		$kurzbz = $_POST["kurzbz"];
		$kurzbzlang = $_POST["kurzbzlang"];
		$typ = $_POST["typ"];
		$bezeichnung = $_POST["bezeichnung"];
		$english = $_POST["english"];
		$farbe = $_POST["farbe"];
		$email = $_POST["email"];
		$telefon = $_POST["telefon"];
		$max_semester = $_POST["max_semester"];
		$max_verband = $_POST["max_verband"];
		$max_gruppe = $_POST["max_gruppe"];
		$erhalter_kz = $_POST["erhalter_kz"];
		$bescheid = $_POST["bescheid"];
		$bescheidbgbl1 = $_POST["bescheidbgbl1"];
		$bescheidbgbl2 = $_POST["bescheidbgbl1"];
		$bescheidgz = $_POST["bescheidgz"];
		$bescheidvom = $_POST["bescheidvom"];
		$organisationsform = $_POST["organisationsform"];
		$titelbescheidvom = $_POST["titelbescheidvom"];
		if(isset($_POST["aktiv"]))
			$aktiv = $_POST["aktiv"];
		else
			$aktiv = "f";
		$ext_id = $_POST["ext_id"];

		$sg_update = new studiengang($conn);
		$sg_update->studiengang_kz = $kennzahl;
		$sg_update->kurzbz = $kurzbz;
		$sg_update->kurzbzlang = $kurzbzlang;
		$sg_update->typ = $typ;
		$sg_update->bezeichnung = $bezeichnung;
		$sg_update->english = $english;
		$sg_update->farbe = $farbe;
		$sg_update->email = $email;
		$sg_update->telefon = $telefon;
		$sg_update->max_semester = $max_semester;
		$sg_update->max_verband = $max_verband;
		$sg_update->max_gruppe = $max_gruppe;
		$sg_update->erhalter_kz = $erhalter_kz;
		$sg_update->bescheid = $bescheid;
		$sg_update->bescheidbgbl1 = $bescheidbgbl1;
		$sg_update->bescheidbgbl2 = $bescheidbgbl1;
		$sg_update->bescheidgz = $bescheidgz;
		$sg_update->bescheidvom = $bescheidvom;
		$sg_update->organisationsform = $organisationsform;
		$sg_update->titelbescheidvom = $titelbescheidvom;
		$sg_update->aktiv = $aktiv;
		$sg_update->ext_id = $ext_id;

		if (isset($_POST["schickneu"]))
			$sg_update->new = 1;

		if(!$sg_update->save())
			die($sg_update->errormsg);

		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht.location.href='studiengang_uebersicht.php';";
		$reloadstr .= "</script>\n";
	}

	$htmlstr = "";
	$sel = "";
	$chk = "";

	if (isset($_REQUEST['studiengang_kz']))
	{
		$kennzahl = $_REQUEST["studiengang_kz"];
		$sg = new studiengang($conn,$kennzahl);
		if ($sg->errormsg!='')
			die($sg->errormsg);

		$erh = new erhalter($conn);

    	if (!$erh->getAll('kurzbz'))
        	die($erh->errormsg);
		$htmlstr .= "<br><div class='kopf'>Studiengang <b>".$sg->bezeichnung."</b></div>";
		$htmlstr .= "<form action='studiengang_details.php' method='POST' name='studiengangform'>";
		$htmlstr .= "<table class='detail'>\n";


		$htmlstr .= "	<tr><td colspan='3'>&nbsp;</tr>\n";
		$htmlstr .= "	<tr>\n";

		// ertse Spalte start
		$htmlstr .= "		<td valign='top'>\n";

		$htmlstr .= "			<table>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Kennzahl</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='studiengang_kz' size='16' maxlength='4' value='".$sg->studiengang_kz."' onchange='submitable()' readonly></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Kurzbezeichnung</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='kurzbz' size='16' maxlength='3' value='".$sg->kurzbz."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>KurzbezeichnungLang</td>\n";
		$htmlstr .= " 					<td><input class='detail' type='text' name='kurzbzlang' size='16' maxlength='8' value='".$sg->kurzbzlang."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Max Semester</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='max_semester' size='16' maxlength='2' value='".$sg->max_semester."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Max Verband</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='max_verband' size='16' maxlength='1' value='".$sg->max_verband."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Max Gruppe</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='max_gruppe' size='16' maxlength='1' value='".$sg->max_gruppe."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Organisationsform</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='organisationsform' size='16' maxlength='1' value='".$sg->organisationsform."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Ext ID</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='ext_id' size='16' maxlength='16' value='".$sg->ext_id."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td valign='top'>Aktiv</td>\n";
		$htmlstr .= " 					<td>\n";
		if($sg->aktiv == 't')
			$chk = "checked";
		else
			$chk = "";
		$htmlstr .= "						<input type='checkbox' name='aktiv' value='t'".$chk." onchange='submitable()'>";
		$htmlstr .= " 					</td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "			</table>\n";

		$htmlstr .= "		</td>\n";
		// 2. Spalte start
		$htmlstr .= "		<td valign='top'>\n";

		$htmlstr .= "			<table>\n";

		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Erhalter</td>\n";
		$htmlstr .= "					<td\n>";
		$htmlstr .= "						<select name='erhalter_kz' onchange='submitable()'>\n";

		foreach($erh->result as $erhalter)
		{
			if ($sg->erhalter_kz == $erhalter->erhalter_kz)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "							<option value='".$erhalter->erhalter_kz."'".$sel.">".$erhalter->bezeichnung."</option>\n";
		}
		$htmlstr .= "						</select>\n";
		$htmlstr .= "					</td>\n";
		$htmlstr .= "				</tr>\n";

		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Typ</td>\n";
		$htmlstr .= "					<td\n>";
		$htmlstr .= "						<select name='typ' onchange='submitable()'>\n";
		$htmlstr .= "							<option value=''></option>\n";
		if ($sg->typ == "b")
			$sel = " selected";
		else
			$sel = "";
		$htmlstr .= "							<option value='b'".$sel.">Bakk</option>\n";
		if ($sg->typ == "d")
			$sel = " selected";
		else
			$sel = "";
		$htmlstr .= "							<option value='d'".$sel.">Diplom</option>\n";
		if ($sg->typ == "m")
			$sel = " selected";
		else
			$sel = "";
		$htmlstr .= "							<option value='m'".$sel.">Master</option>\n";
		if ($sg->typ == "l")
			$sel = " selected";
		else
			$sel = "";
		$htmlstr .= "							<option value='' disabled>---</option>";
		$htmlstr .= "							<option value='l'".$sel.">LLL</option>\n";
		if ($sg->typ == "e")
			$sel = " selected";
		else
			$sel = "";
		$htmlstr .= "							<option value='e'".$sel.">Erhalter</option>\n";
		$htmlstr .= "						</select>\n";
		$htmlstr .= "					</td>\n";
		//$htmlstr .= "					<td><input class='detail' type='text' name='typ' size='16' maxlength='1' value='".$sg->typ."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";



		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Farbe</td>\n";
		$htmlstr .= " 					<td><input class='detail' type='text' name='farbe' size='16' maxlength='6' value='".$sg->farbe."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Bescheidbgbl1</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='bescheidbgbl1' size='16' maxlength='16' value='".$sg->bescheidbgbl1."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Bescheidbgbl2</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='bescheidbgbl2' size='16' maxlength='16' value='".$sg->bescheidbgbl2."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
				$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Bescheidgz</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='bescheidgz' size='16' maxlength='16' value='".$sg->bescheidgz."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Bescheidvom</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='bescheidvom' size='16' maxlength='10' value='".$sg->bescheidvom."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
				$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Titelbescheidvom</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='titelbescheidvom' size='16' maxlength='10' value='".$sg->titelbescheidvom."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "			</table>\n";

		$htmlstr .= "		</td>\n";
		// 3. Spalte start
		$htmlstr .= "		<td valign='top'>\n";

		$htmlstr .= "			<table>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Bezeichnung</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='bezeichnung' size='50' maxlength='128' value='".$sg->bezeichnung."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>English</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='english' size='50' maxlength='128' value='".$sg->english."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Email</td>\n";
		$htmlstr .= " 					<td><input class='detail' type='text' name='email' size='50' maxlength='64' value='".$sg->email."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Telefon</td>\n";
		$htmlstr .= " 					<td><input class='detail' type='text' name='telefon' size='50' maxlength='32' value='".$sg->telefon."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td valign='top'>Bescheid</td>\n";
		$htmlstr .= " 					<td><textarea name='bescheid' cols='37' rows='5' onchange='submitable()'>".$sg->bescheid."</textarea></td>\n";
		$htmlstr .= "				</tr>\n";

		$htmlstr .= "			</table>\n";

		$htmlstr .= "		</td>\n";

		$htmlstr .= "	</tr>";
		$htmlstr .= "</table>\n";
		$htmlstr .= "<br>\n";
		$htmlstr .= "<div align='right' id='sub'>\n";
		$htmlstr .= "	<span id='submsg' style='color:red; visibility:hidden;'>Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>\n";
		$htmlstr .= "	<input type='submit' value='Speichern' name='schick' disabled>\n";
		$htmlstr .= "	<input type='button' value='Reset' onclick='unchanged()'>\n";
		$htmlstr .= "</div>";
		$htmlstr .= "</form>";
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Studiengang - Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">
function unchanged()
{
		document.studiengangform.reset();
		document.studiengangform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
		checkmail();
		checkdate(document.studiengangform.bescheidvom);
		checkdate(document.studiengangform.titelbescheidvom);
		checkrequired(document.studiengangform.kurzbz);
		checkrequired(document.studiengangform.bezeichnung);
		checkrequired(document.studiengangform.studiengang_kz);
		

}

function checkmail()
{
	if((document.studiengangform.email.value != "")&&(!emailCheck(document.studiengangform.email.value)))
	{
		//document.studiengangform.schick.disabled = true;
		document.studiengangform.email.className="input_error";
		return false;

	}
	else
	{
		document.studiengangform.email.className = "input_ok";
		//document.studiengangform.schick.disabled = false;
		//document.getElementById("submsg").style.visibility="visible";
		return true;
	}
}

function checkdate(feld)
{
	if ((feld.value != "") && (!dateCheck(feld)))
	{
		//document.studiengangform.schick.disabled = true;
		feld.className = "input_error";
		return false;
	}
	else
	{
		if(feld.value != "")
			feld.value = dateCheck(feld);

		feld.className = "input_ok";
		return true;
	}
}

function checkrequired(feld)
{
	if(feld.value == "")
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
	mail = checkmail();
	date1 = checkdate(document.studiengangform.bescheidvom);
	date2 = checkdate(document.studiengangform.titelbescheidvom);
	required1 = checkrequired(document.studiengangform.kurzbz);
	required2 = checkrequired(document.studiengangform.bezeichnung);
	required3 = checkrequired(document.studiengangform.studiengang_kz);

	if((!mail) || (!date1) || (!date2) || (!required1) || (!required2) || (!required3))
	{
		document.studiengangform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
	}
	else
	{
		document.studiengangform.schick.disabled = false;
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