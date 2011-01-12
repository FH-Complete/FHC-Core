<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/wawi.config.inc.php');
require_once('auth.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/firma.class.php');
require_once('../include/standort.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/adresse.class.php');
require_once('../include/nation.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Firma</title>	
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php 
$user=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/firma'))
	die('Sie haben keine Berechtigung für diese Seite');
$method = isset($_GET['method'])?$_GET['method']:'';
$strasse = '';
$name = '';
$plz = '';
$ort = '';
$telefon = '';
$fax = '';
$email = '';
$nation = 'A';

if(isset($_POST['save']))
{
	if(!isset($_POST['strasse']) || !isset($_POST['name']) || !isset($_POST['plz']) || !isset($_POST['ort']) ||
	   !isset($_POST['telefon']) || !isset($_POST['fax']) || !isset($_POST['email']))
		die('Ungueltige Parameteruebergabe');
		
	$error = false;
	$strasse = $_POST['strasse'];
	$name = $_POST['name'];
	$plz = $_POST['plz'];
	$ort = $_POST['ort'];
	$telefon = $_POST['telefon'];
	$fax = $_POST['fax'];
	$email = $_POST['email'];
	$nation = $_POST['nation'];
	
	$errormsg='';
	if($email!='' && !mb_strstr($email,'@'))
	{
		$errormsg = 'Email muss ein @ enthalten';
		$error = true;
	}
	if($name=='')
	{
		$errormsg = 'Es muss ein Firmennamen eingetragen werden!';
		$error = true;
	}
	$db = new basis_db();
	
	$db->db_query('BEGIN;');
	
	if(!$error)
	{
		
		$firma = new firma();
		$firma->firmentyp_kurzbz='Firma';
		$firma->name=$name;
		$firma->schule=false;
		$firma->gesperrt=false;
		$firma->aktiv=true;
		$firma->insertamum = date('Y-m-d H:i:s');
		$firma->insertvon = $user;
		$firma->new = true;
		
		if($firma->save())
		{
			$adresse = new adresse();
					
			$adresse->strasse = $strasse;
			$adresse->plz = $plz;
			$adresse->ort = $ort;
			$adresse->nation = $nation;
			$adresse->zustelladresse = true;
			$adresse->heimatadresse = false;
			$adresse->insertamum = date('Y-m-d H:i:s');
			$adresse->insertvon = $user;
			$adresse->new = true;
			
			if($adresse->save())
			{		
				$standort = new standort();
				$standort->firma_id = $firma->firma_id;
				$standort->adresse_id = $adresse->adresse_id;
				$standort->kurzbz = mb_substr($firma->name, 0,16);
				$standort->insertamum = date('Y-m-d H:i:s');
				$standort->insertvon = $user;
				$standort->new = true;
				
				if($standort->save())
				{
					if($fax!='')
					{
						$kontakt = new kontakt();
						$kontakt->kontakttyp='fax';
						$kontakt->standort_id = $standort->standort_id;
						$kontakt->kontakt = $fax;
						$kontakt->new = true;
						
						if(!$kontakt->save())
						{
							$errormsg.=$kontakt->errormsg;
							$error = true;
						}
					}
					if($telefon!='')
					{
						$kontakt = new kontakt();
						$kontakt->kontakttyp='telefon';
						$kontakt->standort_id = $standort->standort_id;
						$kontakt->kontakt = $telefon;
						$kontakt->new = true;
						
						if(!$kontakt->save())
						{
							$errormsg.=$kontakt->errormsg;
							$error = true;
						}
					}
					
					if($email!='')
					{
						$kontakt = new kontakt();
						$kontakt->kontakttyp='email';
						$kontakt->standort_id = $standort->standort_id;
						$kontakt->kontakt = $email;
						$kontakt->new = true;
						
						if(!$kontakt->save())
						{
							$errormsg.=$kontakt->errormsg;
							$error = true;
						}
					}
				}
				else
				{
					$errormsg.='Standort:'.$standort->errormsg;
					$error = true;
				}
			}
			else
			{
				$errormsg.='Adresse:'.$adresse->errormsg;
				$error=true;
			}
		}
		else
		{
			$errormsg.='Firma:'.$firma->errormsg;
			$error=true;
		}
	}
			
	if($error)
	{
		echo '<span class="error">Fehler: '.$errormsg.'</span>';
		$db->db_query('ROLLBACK;');
		$method='new';
	}
	else
	{
		$db->db_query('COMMIT;');
		echo 'Die Firma wurde erfolgreich gespeichert';
	}	
}

if($method=='new')
{
	echo '<h1>Neue Firma</h1>';
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	echo '
	<table>
	<tr>
		<td>Name:</td>
		<td><input type="text" name="name" maxlength="128" size="50" value="'.$name.'"/></td>
	</tr>
	<tr>
		<td>Strasse:</td>
		<td><input type="text" name="strasse" size="30" maxlength="256" value="'.$strasse.'"/></td>
	</tr>
	<tr>
		<td>Plz / Ort:</td>
		<td><input type="text" size="6" name="plz" maxlength="16" value="'.$plz.'"/><input type="text" name="ort" maxlength="256" value="'.$ort.'"/></td>
	</tr>
	<tr>
		<td>Nation:</td>
		<td>
		';
	$nat = new nation();
	$nat->getAll();
	
	echo '<SELECT name="nation">';
	
	foreach($nat->nation as $row)
	{
		if($row->code==$nation)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->code.'" '.$selected.'>'.$row->kurztext.'</OPTION>';
	}
	echo '</SELECT>';
	echo '
		</td>
	</tr>
	<tr>
		<td>Telefon:</td>
		<td><input type="text" name="telefon" maxlength="128" value="'.$telefon.'"/></td>
	</tr>
	<tr>
		<td>Fax:</td>
		<td><input type="text" name="fax" maxlength="128" value="'.$fax.'"/></td>
	</tr>
	<tr>
		<td>E-Mail:</td>
		<td><input type="text" name="email" maxlength="128" value="'.$email.'"/></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="save" value="Speichern"/></td>
	</tr>
	</table>
	';
	
	echo '</form>';
}
?>
</html>
</body>