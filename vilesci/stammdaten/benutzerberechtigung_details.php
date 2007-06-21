<?php
	require_once('../config.inc.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/fachbereich.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/berechtigung.class.php');
	require_once('../../include/studiensemester.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/benutzer.class.php');
	
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$reloadstr = "";  // neuladen der liste im oberen frame
	$htmlstr = "";
	$errorstr = ""; //fehler beim insert
	$sel = "";
	$chk = "";
	$fb_arr = array();
	$sg_arr = array();
	$b_arr = array();
	$st_arr = array();
	
	$benutzerberechtigung_id = "";
	$art = "";
	$fachbereich_kurzbz = "";
	$studiengang_kurzbz = "";
	$berechtigung_kurzbz = "";
	$uid = "";
	$studiensemester_kz = "";
	$start = "";
	$ende = "";
	$neu = false;
	
	if(isset($_POST["del"]))
	{
		$benutzerberechtigung_id = $_POST["benutzerberechtigung_id"];
		$art = $_POST["art"];
		$fachbereich_kurzbz = $_POST["fachbereich_kurzbz"];
		$studiengang_kz = $_POST["studiengang_kz"];
		$berechtigung_kurzbz = $_POST["berechtigung_kurzbz"];
		$uid = $_POST["uid"];
		$studiensemester_kurzbz = $_POST["studiensemester_kurzbz"];
		$start = $_POST["start"];
		$ende = $_POST["ende"];
		
		$ber = new benutzerberechtigung($conn);
		if(!$ber->delete($benutzerberechtigung_id))
			$errorstr .= "Datensatz konnte nicht gel&ouml;scht werden!";
		
		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
		$reloadstr .= "</script>\n";

	}
	
	if(isset($_POST["schick"]))
	{
		$benutzerberechtigung_id = $_POST["benutzerberechtigung_id"];
		$art = $_POST["art"];
		$fachbereich_kurzbz = $_POST["fachbereich_kurzbz"];
		$studiengang_kz = $_POST["studiengang_kz"];
		$berechtigung_kurzbz = $_POST["berechtigung_kurzbz"];
		$uid = $_POST["uid"];
		$studiensemester_kurzbz = $_POST["studiensemester_kurzbz"];
		$start = $_POST["start"];
		$ende = $_POST["ende"];
		
		$ber = new benutzerberechtigung($conn);
		if (isset($_POST["neu"]))
			$ber->new = true;
		
		$ber->benutzerberechtigung_id = $benutzerberechtigung_id;
		$ber->art = $art;
		$ber->fachbereich_kurzbz = $fachbereich_kurzbz;
		$ber->studiengang_kz = $studiengang_kz;
		$ber->berechtigung_kurzbz = $berechtigung_kurzbz;
		$ber->uid = $uid;
		$ber->studiensemester_kurzbz = $studiensemester_kurzbz;
		$ber->start = $start;
		$ber->ende = $ende;
		
		if(!$ber->save()){
			if (!$ber->new)
				$errorstr .= "Datensatz konnte nicht upgedatet werden!";
			else
				$errorstr .= "Datensatz konnte nicht gespeichert werden!";
		}
		if ($ber->new)
		{
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "	parent.uebersicht.location.href='benutzerberechtigung_uebersicht.php';";
			$reloadstr .= "</script>\n";
		}
	}
	
	$fb = new fachbereich($conn);
	$fb->getAll();
	foreach($fb->result as $fachbereich)
	{
		$fb_arr[] = $fachbereich->fachbereich_kurzbz;
	}

	$b = new berechtigung($conn);
	$b->getAll();
	foreach($b->result as $berechtigung)
	{
		$b_arr[] = $berechtigung->berechtigung_kurzbz;
	}
	
	$st = new studiensemester($conn);
	$st->getAll();
	foreach($st->studiensemester as $studiensemester)
	{
		$st_arr[] = $studiensemester->studiensemester_kurzbz;
	}
	
	$sg = new studiengang($conn);
	$sg->getAll();
	foreach($sg->result as $studiengang)
	{
		$sg_arr[$studiengang->studiengang_kz] = $studiengang->kurzbzlang;
	}
	$sgkeys_arr = array_keys($sg_arr);
	
	
	if (isset($_REQUEST["uid"]))
	{
		
		$uid = $_REQUEST["uid"];
		
		$ben = new benutzer($conn);
		if (!$ben->load($uid))
			$htmlstr .= "<br><div class='kopf'>Benutzer <b>".$uid."</b> existiert nicht</div>";
		else
		{
			$rights = new benutzerberechtigung($conn);
			$rights->getberechtigungen($uid,$all=true);
	
			$htmlstr .= "<br><div class='kopf'>Berechtigungen <b>".$uid."</b></div>\n";
			$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
			$htmlstr .= "<tr></tr>\n";
			$htmlstr .= "<tr><td>Kurzbz</td><td>Art</td><td>Sudiengang</td><td>Fachbereich</td><td>Semester</td><td>Start</td><td>Ende</td><td></td><td></td><td></td></tr>\n";
			foreach($rights->berechtigungen as $b)
			{
				$htmlstr .= "<form action='benutzerberechtigung_details.php' method='POST' name='berechtigung".$b->benutzerberechtigung_id."'>\n";
				$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value='".$b->benutzerberechtigung_id."'>\n";
				$htmlstr .= "<input type='hidden' name='uid' value='".$b->uid."'>\n";
				$htmlstr .= "	<tr id='".$b->benutzerberechtigung_id."'>\n";
				
				$htmlstr .= "		<td><select name='berechtigung_kurzbz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
				$htmlstr .= "			<option value=''></option>\n";
				for ($i = 0; $i < sizeof($b_arr); $i++)
				{
					if ($b->berechtigung_kurzbz == $b_arr[$i])
						$sel = " selected";
					else
						$sel = "";
					$htmlstr .= "				<option value='".$b_arr[$i]."' ".$sel.">".$b_arr[$i]."</option>";
				}
				$htmlstr .= "		</select></td>\n";
				
				$htmlstr .= "		<td><input type='text' name='art' value='".$b->art."' size='5' maxlength='5' onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";
				
				$htmlstr .= "		<td><select name='studiengang_kz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
				$htmlstr .= "			<option value=''></option>\n";
				
				foreach ($sgkeys_arr as $sgkey)
				{
					if ($b->studiengang_kz == $sgkey && $b->studiengang_kz != null)
						$sel = " selected";
					else
						$sel = "";
					$htmlstr .= "				<option value='".$sgkey."' ".$sel.">".$sg_arr[$sgkey]."</option>";
				}
				$htmlstr .= "		</select></td>\n";
				
				
				$htmlstr .= "		<td><select name='fachbereich_kurzbz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
				$htmlstr .= "			<option value=''></option>\n";
				for ($i = 0; $i < sizeof($fb_arr); $i++)
				{
					if ($b->fachbereich_kurzbz == $fb_arr[$i])
						$sel = " selected";
					else
						$sel = "";
					$htmlstr .= "				<option value='".$fb_arr[$i]."' ".$sel.">".$fb_arr[$i]."</option>";
				}
				$htmlstr .= "		</select></td>\n";
				
				$htmlstr .= "		<td><select name='studiensemester_kurzbz' onchange='markier(\"".$b->benutzerberechtigung_id."\")'>\n";
				$htmlstr .= "			<option value=''></option>\n";
				for ($i = 0; $i < sizeof($st_arr); $i++)
				{
					if ($b->studiensemester_kurzbz == $st_arr[$i])
						$sel = " selected";
					else
						$sel = "";
					$htmlstr .= "				<option value='".$st_arr[$i]."' ".$sel.">".$st_arr[$i]."</option>";
				}
				$htmlstr .= "		</select></td>\n";
				
				$htmlstr .= "		<td><input type='text' name='start' value='".$b->start."' size='10' maxlength='10' onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";
				$htmlstr .= "		<td><input type='text' name='ende' value='".$b->ende."' size='10' maxlength='10' onchange='markier(\"".$b->benutzerberechtigung_id."\")'></td>\n";
				
				$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
				$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen'></td>";
				$htmlstr .= "		<td><input type='reset' name='reset' value='C' onmouseup='unmarkier(\"".$b->benutzerberechtigung_id."\")'></td>";
				$htmlstr .= "	</tr>\n";
				$htmlstr .= "</form>\n";
		
			}
			
			$htmlstr .= "<form action='benutzerberechtigung_details.php' method='POST' name='berechtigung_neu'>\n";
			$htmlstr .= "<input type='hidden' name='neu' value='1'>\n";
			$htmlstr .= "<input type='hidden' name='benutzerberechtigung_id' value=''>\n";
			$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
			$htmlstr .= "	<tr id='neu'>\n";
			
			$htmlstr .= "		<td><select name='berechtigung_kurzbz' onchange='markier(\"neu\")'>\n";
			$htmlstr .= "			<option value=''></option>\n";
			for ($i = 0; $i < sizeof($b_arr); $i++)
			{
				
				$sel = "";
				$htmlstr .= "				<option value='".$b_arr[$i]."' ".$sel.">".$b_arr[$i]."</option>";
			}
			$htmlstr .= "		</select></td>\n";
			
			$htmlstr .= "		<td><input type='text' name='art' value='' size='5' maxlength='5' onchange='markier(\"neu\")'></td>\n";
			
			$htmlstr .= "		<td><select name='studiengang_kz' onchange='markier(\"neu\")'>\n";
			$htmlstr .= "			<option value=''></option>\n";
			
			foreach ($sgkeys_arr as $sgkey)
			{
				$sel = "";
				$htmlstr .= "				<option value='".$sgkey."' ".$sel.">".$sg_arr[$sgkey]."</option>";
			}
			$htmlstr .= "		</select></td>\n";
			
			
			$htmlstr .= "		<td><select name='fachbereich_kurzbz' onchange='markier(\"neu\")'>\n";
			$htmlstr .= "			<option value=''></option>\n";
			for ($i = 0; $i < sizeof($fb_arr); $i++)
			{
				$sel = "";
				$htmlstr .= "				<option value='".$fb_arr[$i]."' ".$sel.">".$fb_arr[$i]."</option>";
			}
			$htmlstr .= "		</select></td>\n";
			
			$htmlstr .= "		<td><select name='studiensemester_kurzbz' onchange='markier(\"neu\")'>\n";
			$htmlstr .= "			<option value=''></option>\n";
			for ($i = 0; $i < sizeof($st_arr); $i++)
			{
				$sel = "";
				$htmlstr .= "				<option value='".$st_arr[$i]."' ".$sel.">".$st_arr[$i]."</option>";
			}
			$htmlstr .= "		</select></td>\n";
			
			$htmlstr .= "		<td><input type='text' name='start' value='' size='10' maxlength='10' onchange='markier(\"neu\")'></td>\n";
			$htmlstr .= "		<td><input type='text' name='ende' value='' size='10' maxlength='10' onchange='markier(\"neu\")'></td>\n";
			
			$htmlstr .= "		<td><input type='submit' name='schick' value='neu'></td>";
			$htmlstr .= "		<td></td>";
			$htmlstr .= "		<td><input type='reset' name='reset' value='C' onmouseup='unmarkier(\"neu\")'></td>";
			$htmlstr .= "	</tr>\n";
			$htmlstr .= "</form>\n";
			
			$htmlstr .= "</table>\n";
			
		}

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

function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
	  return true;
	return false;
}

function markier(id)
{
	document.getElementById(id).style.background = "#FC988D";
}

function unmarkier(id)
{
	document.getElementById(id).style.background = "#eeeeee";
}

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