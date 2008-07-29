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
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/firma.class.php');
	
	if (!$conn = pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	// ******* INIT ********
	$user = get_uid();
	$htmlstr = '';
	$errorstr = '';
	$reloadstr = '';
	$error = false;
	$firma_id = (isset($_REQUEST["firma_id"])?$_REQUEST['firma_id']:'');
	$name = (isset($_POST['name'])?$_POST['name']:'');
	$adresse = (isset($_POST['adresse'])?$_POST['adresse']:'');
	$email = (isset($_POST['email'])?$_POST['email']:'');
	$telefon = (isset($_POST['telefon'])?$_POST['telefon']:'');
	$fax = (isset($_POST['fax'])?$_POST['fax']:'');
	$anmerkung = (isset($_POST['anmerkung'])?$_POST['anmerkung']:'');
	$firmentyp_kurzbz = (isset($_POST['typ'])?$_POST['typ']:'');
	$schule = isset($_POST['schule']);
	
	// ******* SPEICHERN ********
	if(isset($_POST['save']))
	{
		$firma = new firma($conn);
		
		if($firma_id!='')
		{
			if(!$firma->load($firma_id))
			{
				$error = true;
			}
			else 
			{
				$firma->new = false;
			}
		}
		else 
		{
			$firma->insertamum = date('Y-m-d H:i:s');
			$firma->insertvon = $user;
			$firma->new = true;
		}
		
		if(!$error)
		{
			$firma->name = $name;
			$firma->adresse = $adresse;
			$firma->email = $email;
			$firma->telefon = $telefon;
			$firma->fax = $fax;
			$firma->anmerkung = $anmerkung;
			$firma->firmentyp_kurzbz = $firmentyp_kurzbz;
			$firma->updateamum = date('Y-m-d H:i:s');
			$firma->updatevon = $user;
			$firma->schule = $schule;
			
			if($firma->save())
			{
				$reloadstr .= "<script type='text/javascript'>\n";
				$reloadstr .= "	parent.uebersicht_firma.location.href='firma_uebersicht.php';";
				$reloadstr .= " window.top.opener.StudentProjektarbeitFirmaRefresh();";
				$reloadstr .= "</script>\n";
			}
			else
			{
				$errorstr = 'Datensatz konnte nicht gespeichert werden: '.$firma->errormsg;
			}
		}
	}
	
	// ******* FORMULAR **********
	$firma = new firma($conn);
	if($firma_id!='')
	{
		if (!$firma->load($firma_id))
		{
			$htmlstr .= "<br><div class='kopf'>Firma mit der ID <b>".$firma_id."</b> existiert nicht</div>";
		}
	}
	else 
	{
		//Bei neuen Firmen wird standardmaessig Partnerfirma ausgewaehlt
		$firma->firmentyp_kurzbz='Partnerfirma';
	}
		

	$htmlstr .= "<form action='firma_details.php' method='POST' name='firma'>\n";
	$htmlstr .= "<input type='hidden' name='firma_id' value='".$firma->firma_id."'>\n";
	
	$htmlstr .= "<br><div class='kopf'>Firma</div>\n";
	$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
	$htmlstr .= "<tr></tr>\n";
			
	$htmlstr .= "	<tr>\n";
	$htmlstr .= "		<td>Name: </td>";
	$htmlstr .= "		<td colspan='3'><input type='text' name='name' value='".htmlentities($firma->name)."' size='80' maxlength='128' /></td>\n";
	$htmlstr .= "		<td>Typ: </td>";		
	$htmlstr .= "		<td><select name='typ'>\n";

	$qry = "SELECT firmentyp_kurzbz FROM public.tbl_firmentyp ORDER BY firmentyp_kurzbz";
	
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			if ($firma->firmentyp_kurzbz == $row->firmentyp_kurzbz)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "				<option value='".$row->firmentyp_kurzbz."' ".$sel.">".$row->firmentyp_kurzbz."</option>";
		}
	}
	$htmlstr .= "		</select></td></tr><tr>\n";
		
	$htmlstr .= "		<td>EMail: </td>";
	$htmlstr .= "		<td><input type='text' name='email' value='".htmlentities($firma->email)."' maxlength='128' /></td>\n";
	$htmlstr .= "		<td>Telefon: </td>";
	$htmlstr .= "		<td><input type='text' name='telefon' value='".htmlentities($firma->telefon)."' maxlength='32' /></td>\n";
	$htmlstr .= "		<td>Fax: </td>";
	$htmlstr .= "		<td><input type='text' name='fax' value='".htmlentities($firma->fax)."' maxlength='32' /></td>\n";
	$htmlstr .= "</tr><tr valign='top'>";
	$htmlstr .= "		<td>Adresse: </td>";
	$htmlstr .= "		<td><input type='text' name='adresse' value='".htmlentities($firma->adresse)."' maxlength='256'></td>\n";
	$htmlstr .= "		<td>Anmerkung: </td>";
	$htmlstr .= "		<td><textarea name='anmerkung'/>".htmlentities($firma->anmerkung)."</textarea></td>\n";
	$htmlstr .= "		<td>Schule: </td>";
	$htmlstr .= "		<td><input type='checkbox' name='schule' ".($firma->schule?'checked':'')."<br><input type='submit' name='save' value='speichern'></td>";
	$htmlstr .= "	</tr></table>\n";
	$htmlstr .= "</form>\n";
				
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Firma - Details</title>
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

</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>