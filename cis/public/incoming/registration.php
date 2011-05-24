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

require_once '../../../config/cis.config.inc.php';
require_once '../../../include/person.class.php';
require_once '../../../include/nation.class.php';
require_once '../../../include/functions.inc.php';
require_once '../../../include/phrasen.class.php';
require_once '../../../include/adresse.class.php';
require_once '../../../include/kontakt.class.php'; 
require_once '../../../include/preincoming.class.php'; 
require_once '../../../include/mail.class.php';


if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$nation = new nation(); 
$nation->getAll($ohnesperre = true); 

$sprache = getSprache(); 
$p=new phrasen($sprache); 

$date = new datum(); 

?>
<html>
	<head>
		<title>Incoming-Registration</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
	</head>
	<body bgcolor="F2F2F2">
<?php 	

	echo '		<table width="100%" border="0">
				<tr>
					<td align="left"><a href="index.php">Login</a> &gt; Registration </td>
					<td align ="right">		
					'.$p->t("global/sprache").'
					<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> | 
					<a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br> </td>
				</tr>
				
			</table>';
	
if(!isset($_POST['submit']))
{			
echo '	<form action="registration.php" method="POST" name="RegistrationForm">
			<table border = "0" style="margin-left:40%; margin-top:5%;">
				<tr>
					<td>'.$p->t('global/titel').' Pre</td>
					<td><input type="text" size="20" maxlength="64" name="titel_pre"></td>
				</tr>
				<tr>
					<td>'.$p->t('global/vorname').'</td>
					<td><input type="text" size="40" maxlength="32" name="vorname"></td>
				</tr>
				<tr>
					<td>'.$p->t('global/nachname').'</td>
					<td><input type="text" size="40" maxlength="64" name="nachname"></td>
				</tr>
				<tr>
					<td>'.$p->t('global/titel').' Post</td>
					<td><input type="text" size="20" maxlength="32" name="titel_post"></td>
				</tr>
				<tr>
					<td>'.$p->t('global/geburtsdatum').'</td>
					<td><input type="text" size="20" name="geb_datum" value="" onfocus="this.value=""\"; > (dd.mm.YYYY)</td>
				</tr>
				<tr>
					<td>'.$p->t('global/staatsbuergerschaft').'</td>
					 
					 <td><SELECT name="staatsbuerger">\n 
					 <option value="staat_auswahl">-- select --</option>\n';
					foreach ($nation->nation as $nat)
					{
						echo '<option value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
					}
							
	echo'		</tr>		
				<tr>
					<td>'.$p->t('global/geschlecht').'</td>
					<td>    <input type="radio" name="geschlecht" value="m" checked> '.$p->t('global/mann').'
	    					<input type="radio" name="geschlecht" value="w"> '.$p->t('global/frau').'
	    			</td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>'.$p->t('global/strasse').'</td>
					<td><input type="text" size="40" maxlength="256" name="strasse"></td>
				</tr>	
				<tr>
					<td>'.$p->t('global/plz').'</td>
					<td><input type="text" size="20" maxlength="16" name="plz"></td>
				</tr>				
				<tr>
					<td>'.$p->t('global/ort').'</td>
					<td><input type="text" size="40" maxlength="256" name="ort"></td>
				</tr>				
				<tr>
					<td>Nation</td>
					<td><SELECT name="nation">\n 
					<option value="nat_auswahl">-- select --</option>\n';
					foreach ($nation->nation as $nat)
					{
						echo '<option value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
					}
							
	echo '		</tr>				
				<tr>
					<td>E-Mail</td>
					<td><input type="text" size="40" maxlength="128" name="email"></td>
				</tr>	
				<tr>
					<td>'.$p->t('global/anmerkung').'</td>
					<td><textarea name="anmerkung" cols="31" rows="5"></textarea></td>
				</tr>	
				<tr>
					<td colspan="2" align = "center"><input type="submit" name="submit" value="Registration" onclick="return checkRegistration()"></td>		
				</tr>
				<tr><td><input type="hidden" name="zugangscode" value="'.uniqid().'"></td></tr>	
			</table>
		</form>
	
	<script type="text/javascript">
		function checkRegistration()
		{
			if(document.RegistrationForm.nachname.value == "")
			{
				alert("Kein Nachname angegeben.");
				return false; 
			}
			
			if(document.RegistrationForm.staatsbuerger.options[0].selected == true) 
			{
				alert("Keine Staatsbürgerschaft ausgewählt.");
				return false; 
			}
			
			if(document.RegistrationForm.nation.options[0].selected == true) 
			{
				alert("Keine Nation ausgewählt.");
				return false; 
			}
			if(document.RegistrationForm.email.value == "")
			{
				alert("Keine E-Mail Adresse angegeben.");
				return false; 
			}
			return true; 
		}
		</script>';
}
echo'	</body>
</html>';

if(isset($_REQUEST['submit']))
{	
	$person = new person(); 
	$adresse = new adresse();
	$kontakt = new kontakt();
	$preincoming = new preincoming();   
	
	$titel_pre = $_REQUEST['titel_pre'];
	$vorname = $_REQUEST['vorname']; 
	$nachname =$_REQUEST['nachname']; 
	$titel_post = $_REQUEST['titel_post'];
	$geb_datum = $date->formatDatum($_REQUEST['geb_datum'], 'Y-m-d'); 
	$staatsbuerger = $_REQUEST['staatsbuerger']; 
	$geschlecht = $_REQUEST['geschlecht']; 
	$strasse = $_REQUEST['strasse']; 
	$plz = $_REQUEST['plz']; 
	$ort = $_REQUEST['ort']; 
	$nation_code = $_REQUEST['nation']; 
	$email = $_REQUEST['email']; 
	$anmerkung = $_REQUEST['anmerkung']; 
	$zugangscode = uniqid(); 
	
	$person->staatsbuergerschaft = $staatsbuerger; 
	$person->titelpost = $titel_post; 
	$person->titelpre = $titel_pre; 
	$person->nachname = $nachname; 
	$person->vorname = $vorname; 
	$person->gebdatum = $geb_datum; 
	$person->anmerkungen = $anmerkung; 
	$person->geschlecht = $geschlecht; 
	$person->aktiv = true; 	
	$person->zugangscode = $zugangscode; 
	$person->new = true; 
	
	if(!$person->save())
		die('Fehler beim Anlegen der Person aufgetreten.'); 
	
	$adresse->person_id = $person->person_id; 
	$adresse->strasse = $strasse; 
	$adresse->plz = $plz; 
	$adresse->ort = $ort; 
	$adresse->nation = $nation_code; 
	$adresse->heimatadresse = true; 
	$adresse->zustelladresse = true; 
	$adresse->new = true; 

	if(!$adresse->save())
		die('Fehler beim Anlegen der Adresse aufgetreten.');  

	$kontakt->person_id = $person->person_id; 
	$kontakt->kontakttyp = "email"; 
	$kontakt->kontakt = $email; 
	$kontakt->new = true; 
	
	if(!$kontakt->save())
		die('Fehler beim Anlegen des Kontaktes aufgetreten.');

	$preincoming->person_id = $person->person_id; 
	$preincoming->aktiv = true; 
	$preincoming->bachelorthesis = false; 
	$preincoming->masterthesis = false; 
	$preincoming->uebernommen = false; 
	$preincoming->new = true; 
	$preincoming->insertamum = date('Y-m-d H:i:s');
	$preincoming->updateamum = date('Y-m-d H:i:s');

	if(!$preincoming->save())
	{
		echo $preincoming->errormsg; 
		die('Fehler beim Anlegen des Preincoming aufgetreten.'); 
	}		
	echo sendMail($zugangscode, $email); 
}

function sendMail($zugangscode, $email)
{
	global $p; 
	$emailtext= "Dies ist eine automatisch generierte E-Mail.<br><br>";
	$emailtext.= "Vielen Dank für Ihre Registrierung an der Fachhochschule Technikum Wien.<br><br>Sie wurden erfolgreich am System registriert.<br><br><br>Mit Hilfe Ihrer UID: <b>".$zugangscode."</b> können Sie sich in unserem 
	System (<a href=\"".APP_ROOT."/cis/public/incoming\">Link zur Anmeldung</a>) anmelden und Ihre Daten bearbeiten.<br><br><br> 
	Mit freundlichen Grüßen, <br><br>FH Technikum Wien International<br>Center for International Relations & Cross-Cultural Education<br>UAS Technikum Wien<br>Hoechstaedtplatz 5, 1200 Wien, AUSTRIA"; 
	
	$mail = new mail($email, 'no-reply', 'Incoming-Registration', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$mail->setHTMLContent($emailtext); 
	if(!$mail->send())
		$msg= '<span class="error">Fehler beim Senden des Mails</span><br />';
	else
		$msg= $p->t('global/emailgesendetan')." $email!<br>";
	
	return $msg; 
}

?>