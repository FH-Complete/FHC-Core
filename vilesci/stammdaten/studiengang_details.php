<?php
	require_once('../config.inc.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/erhalter.class.php');

	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$reloadstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
	$errorstr = ''; //fehler beim insert
	$sel = '';
	$chk = '';

	$sg_var = new studiengang($conn);
	$studiengang_typ_arr = $sg_var->studiengang_typ_arr;
	
	$studiengang_kz = '';
	$kurzbz = '';
	$kurzbzlang = '';
	$typ = '';
	$bezeichnung = '';
	$english = '';
	$farbe = '';
	$email = '';
	$telefon = '';
	$max_semester = '';
	$max_verband = '';
	$max_gruppe = '';
	$erhalter_kz = '';
	$bescheid = '';
	$bescheidbgbl1 = '';
	$bescheidbgbl2 = '';
	$bescheidgz = '';
	$bescheidvom = '';
	$organisationsform = '';
	$titelbescheidvom = '';
	$zusatzinfo_html = '';
	$ext_id = '';
	$aktiv = "t";
	$neu = "true";
	
	if(isset($_POST["schick"]))
	{
		$studiengang_kz = $_POST["studiengang_kz"];
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
		$zusatzinfo_html = $_POST['zusatzinfo_html'];
		if(isset($_POST["aktiv"]))
			$aktiv = $_POST["aktiv"];
		else
			$aktiv = "f";
		$ext_id = $_POST["ext_id"];

		$sg_update = new studiengang($conn);
		$sg_update->studiengang_kz = $studiengang_kz;
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
		$sg_update->orgform_kurzbz = $organisationsform;
		$sg_update->titelbescheidvom = $titelbescheidvom;
		$sg_update->zusatzinfo_html = $zusatzinfo_html;
		$sg_update->aktiv = $aktiv;
		$sg_update->ext_id = $ext_id;

		if ($_POST["neu"] == "true")
			$sg_update->new = 1;

		if(!$sg_update->save())
		{
			$errorstr .= $sg_update->errormsg;
		}
		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht_studiengang.location.href='studiengang_uebersicht.php';";
		$reloadstr .= "</script>\n";
	}



	if ((isset($_REQUEST['studiengang_kz'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= "true")))
	{
		$studiengang_kz = $_REQUEST["studiengang_kz"];
		$sg = new studiengang($conn,$studiengang_kz);
		if ($sg->errormsg!='')
			die($sg->errormsg);
		$studiengang_kz = $sg->studiengang_kz;
		$kurzbz = $sg->kurzbz;
		$kurzbzlang = $sg->kurzbzlang;
		$typ = $sg->typ;
		$bezeichnung = $sg->bezeichnung;
		$english = $sg->english;
		$farbe = $sg->farbe;
		$email = $sg->email;
		$telefon = $sg->telefon;
		$max_semester = $sg->max_semester;
		$max_verband = $sg->max_verband;
		$max_gruppe = $sg->max_gruppe;
		$erhalter_kz = $sg->erhalter_kz;
		$bescheid = $sg->bescheid;
		$bescheidbgbl1 = $sg->bescheidbgbl1;
		$bescheidbgbl2 = $sg->bescheidbgbl2;
		$bescheidgz = $sg->bescheidgz;
		$bescheidvom = $sg->bescheidvom;
		$organisationsform = $sg->orgform_kurzbz;
		$titelbescheidvom = $sg->titelbescheidvom;
		$zusatzinfo_html = $sg->zusatzinfo_html;
		$ext_id = $sg->ext_id;
		$aktiv = $sg->aktiv;
		$neu = "false";
	}
	
	$erh = new erhalter($conn);

   	if (!$erh->getAll('kurzbz'))
       	die($erh->errormsg);
		
	$htmlstr .= "<br><div class='kopf'>Studiengang <b>".$bezeichnung."</b></div>\n";
	$htmlstr .= "<form action='studiengang_details.php' method='POST' name='studiengangform'>\n";
	$htmlstr .= "<table class='detail'>\n";


	$htmlstr .= "	<tr><td colspan='3'>&nbsp;</tr>\n";
	$htmlstr .= "	<tr>\n";

	// ertse Spalte start
	$htmlstr .= "		<td valign='top'>\n";

	$htmlstr .= "			<table>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Kennzahl</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='studiengang_kz' size='16' maxlength='5' value='".$studiengang_kz."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Kurzbezeichnung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='kurzbz' size='16' maxlength='3' value='".$kurzbz."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>KurzbezeichnungLang</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='kurzbzlang' size='16' maxlength='8' value='".$kurzbzlang."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Semester</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_semester' size='16' maxlength='2' value='".$max_semester."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Verband</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_verband' size='16' maxlength='1' value='".$max_verband."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Max Gruppe</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='max_gruppe' size='16' maxlength='1' value='".$max_gruppe."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Organisationsform</td>\n";
	$htmlstr .= "					<td><SELECT name='organisationsform' onchange='submitable()'>";
	$qry = "SELECT orgform_kurzbz FROM bis.tbl_orgform ORDER BY orgform_kurzbz";
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			if($row->orgform_kurzbz == $organisationsform)
				$selected = 'selected';
			else 
				$selected = '';
			
			$htmlstr .= "			<option value='$row->orgform_kurzbz' $selected>$row->orgform_kurzbz</option>";
		}
	}
	$htmlstr .= "                  </SELECT></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Ext ID</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='ext_id' size='16' maxlength='16' value='".$ext_id."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Aktiv</td>\n";
	$htmlstr .= " 					<td>\n";
	if($aktiv == 't')
		$chk = "checked";
	else
		$chk = '';
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
		if ($erhalter_kz == $erhalter->erhalter_kz)
			$sel = " selected";
		else
			$sel = '';
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

	foreach(array_keys($studiengang_typ_arr) as $typkey)
	{
		if ($typ == $typkey)
			$sel = " selected";
		else
			$sel = '';
		$htmlstr .= "							<option value='".$typkey."'".$sel.">".$studiengang_typ_arr[$typkey]."</option>\n";
	}
	$htmlstr .= "						</select>\n";
	$htmlstr .= "					</td>\n";
	$htmlstr .= "				</tr>\n";



	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Farbe</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='farbe' size='16' maxlength='6' value='".$farbe."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidbgbl1</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidbgbl1' size='16' maxlength='16' value='".$bescheidbgbl1."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidbgbl2</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidbgbl2' size='16' maxlength='16' value='".$bescheidbgbl2."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidgz</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidgz' size='16' maxlength='16' value='".$bescheidgz."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bescheidvom</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bescheidvom' size='16' maxlength='10' value='".$bescheidvom."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Titelbescheidvom</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='titelbescheidvom' size='16' maxlength='10' value='".$titelbescheidvom."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "			</table>\n";

	$htmlstr .= "		</td>\n";
	// 3. Spalte start
	$htmlstr .= "		<td valign='top'>\n";

	$htmlstr .= "			<table>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Bezeichnung</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='bezeichnung' size='50' maxlength='128' value='".$bezeichnung."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>English</td>\n";
	$htmlstr .= "					<td><input class='detail' type='text' name='english' size='50' maxlength='128' value='".$english."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Email</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='email' size='50' maxlength='64' value='".$email."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td>Telefon</td>\n";
	$htmlstr .= " 					<td><input class='detail' type='text' name='telefon' size='50' maxlength='32' value='".$telefon."' onchange='submitable()'></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Bescheid</td>\n";
	$htmlstr .= " 					<td><textarea name='bescheid' cols='37' rows='5' onchange='submitable()'>".$bescheid."</textarea></td>\n";
	$htmlstr .= "				</tr>\n";

	$htmlstr .= "			</table>\n";

	$htmlstr .= "		</td>\n";

	$htmlstr .= "	</tr>";
	$htmlstr .= "	<tr>";
	$htmlstr .= "		<td colspan='3'>";
	$htmlstr .= "			<table>\n";
	$htmlstr .= "				<tr>\n";
	$htmlstr .= "					<td valign='top'>Zusatzinfo</td>\n";
	$htmlstr .= " 					<td><textarea name='zusatzinfo_html' cols='50' rows='4' onchange='submitable()'>".htmlentities($zusatzinfo_html)."</textarea></td>\n";
	$htmlstr .= "				</tr>\n";
	$htmlstr .= "			</table>\n";
	$htmlstr .= "		</td>";
	$htmlstr .= "	</tr>";
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
	/*
	if((document.studiengangform.email.value != '')&&(!emailCheck(document.studiengangform.email.value)))
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
	}*/
	return true;
}

function checkdate(feld)
{
	if ((feld.value != '') && (!dateCheck(feld)))
	{
		//document.studiengangform.schick.disabled = true;
		feld.className = "input_error";
		return false;
	}
	else
	{
		if(feld.value != '')
			feld.value = dateCheck(feld);

		feld.className = "input_ok";
		return true;
	}
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