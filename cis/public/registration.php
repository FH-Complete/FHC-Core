<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once '../../config/cis.config.inc.php';
require_once '../../include/phrasen.class.php';
require_once '../../include/person.class.php'; 
require_once '../../include/studiengang.class.php';
require_once '../../include/datum.class.php';
require_once '../../include/mail.class.php';
require_once '../../include/prestudent.class.php'; 
require_once '../../include/preinteressent.class.php'; 
require_once '../../include/kontakt.class.php'; 
require_once '../../include/studiensemester.class.php'; 
require_once '../../include/datum.class.php'; 

require_once '../../include/securimage/securimage.php';

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$method = isset($_GET['method'])?$_GET['method']:'';
$message = "&nbsp";
$sprache = getSprache(); 
$p=new phrasen($sprache); 
$datum = new datum(); 

$studiensemester = new studiensemester(); 
$std_semester = $studiensemester->getakt();

// Login gestartet
if (isset($_POST['userid'])) 
{
	$login = $_REQUEST['userid']; 
	$person = new person(); 
		
	session_start();
	$person_id=$person->checkZugangscodePerson(trim($login)); 
	
	//Zugangscode wird  überprüft
	if($person_id != false)
	{
		$_SESSION['bewerbung/user'] = $login;
		$_SESSION['bewerbung/personId'] = $person_id; 

        
       
		header('Location: bewerbung.php');
		exit;
	}
	else
	{
		$message= "<span id='error' style='color:red;'>".$p->t('incoming/ungueltigerbenutzer')."</span>";
	}
}

?>
<html>
	<head>
		<title>Registration für Studiengänge</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="robots" content="noindex">
		<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
	</head>
	<body>
    <?php 
    
    /**
     * Maske zum Registrieren wird angezeigt
     * Nach erfolgreicher Registration wird eine Benutzer ID erstellt und an den Benutzer geschickt 
     */
    if($method == 'registration')
    {   
        // Falls Sicherheitscode falsch ist - übergebene Werte speichern und vorausfüllen
        $date = new datum();

        $vorname = isset($_REQUEST['vorname'])?$_REQUEST['vorname']:''; 
        $nachname = isset($_REQUEST['nachname'])?$_REQUEST['nachname']:''; 
        $geb_datum = isset($_REQUEST['geb_datum'])?$_REQUEST['geb_datum']:''; 
        $geschlecht = isset($_REQUEST['geschlecht'])?$_REQUEST['geschlecht']:''; 
        $email = isset($_REQUEST['email'])?$_REQUEST['email']:''; 
        $studiengaenge = isset($_REQUEST['studiengaenge'])?$_REQUEST['studiengaenge']:array();
        
        $stg_auswahl = isset($_REQUEST['stg'])?$_REQUEST['stg']:'';
        
        $securimage = new Securimage(); 
        if(isset($_POST['submit']))
        {
            // Sicherheitscode wurde falsch eingegeben
            if ($securimage->check($_POST['captcha_code']) == false) 
                $message = '<span class="error">Der eingegebene Sicherheitscode war falsch.</span><br />';
            else
            {
                // Person anlegen
                $person = new person();  

                $vorname = $_REQUEST['vorname']; 
                $nachname =$_REQUEST['nachname']; 
                $geb_datum = $date->formatDatum($_REQUEST['geb_datum'], 'Y-m-d'); 
                $geschlecht = $_REQUEST['geschlecht']; 
                $email = $_REQUEST['email']; 
                $zugangscode = uniqid(); 

                $person->nachname = $nachname; 
                $person->vorname = $vorname; 
                $person->gebdatum = $geb_datum; 
                $person->geschlecht = $geschlecht; 
                $person->aktiv = true; 	
                $person->zugangscode = $zugangscode; 
                $person->insertamum = date('Y-m-d H:i:s'); 
                $person->updateamum = date('Y-m-d H:i:s'); 
                $person->new = true; 

                if(!$person->save())
                    die('Fehler beim Anlegen der Person aufgetreten.'); 
                
                // Email Kontakt zu Person speichern
                $kontakt = new kontakt(); 
                $kontakt->person_id = $person->person_id; 
                $kontakt->kontakttyp = "email"; 
                $kontakt->kontakt = $email; 
                $kontakt->insertamum = date('Y-m-d H:i:s'); 
                $kontakt->updateamum = date('Y-m-d H:i:s'); 
                $kontakt->new = true; 

                if(!$kontakt->save())
                    die('Fehler beim speichern des Kontaktes');
                
                $anzStg = count($studiengaenge);
                
                // ab wieviel ausgewählten Studiengängen kommt Student ins Preinteressententool
                if(count($studiengaenge) < ANZAHL_PREINTERESSENT)
                {
                    // Prestudenten anlegen
                    for($i = 0; $i<$anzStg; $i++)
                    {
                        $prestudent = new prestudent(); 
                        $prestudent->person_id = $person->person_id;
                        $prestudent->studiengang_kz = $studiengaenge[$i]; 
                        $prestudent->aufmerksamdurch_kurzbz = 'k.A.';
                        $prestudent->insertamum = date('Y-m-d H:i:s'); 
                        $prestudent->updateamum = date('Y-m-d H:i:s');
                        $prestudent->reihungstestangetreten = false; 
                        $prestudent->new = true; 
                        if(!$prestudent->save())
                            die('Fehler beim anlegen des Prestudenten');
                        
                        // Interessenten Status anlegen
                        $prestudent_status = new prestudent(); 
                        $prestudent_status->load($prestudent->prestudent_id); 
                        $prestudent_status->status_kurzbz = 'Interessent'; 
                        $prestudent_status->studiensemester_kurzbz = $std_semester; 
                        $prestudent_status->ausbildungssemester = '0'; 
                        $prestudent_status->datum = date("Y-m-d H:m:s"); 
                        $prestudent_status->insertamum = date("Y-m-d H:m:s"); 
                        $prestudent_status->insertvon = ''; 
                        $prestudent_status->updateamum = date("Y-m-d H:m:s"); 
                        $prestudent_status->updatevon = ''; 
                        $prestudent_status->new = true; 
                        if(!$prestudent_status->save_rolle())
                            die('Fehler beim anlegen der Rolle'); 
                        
                    }
                }
                else
                {
                    // Preinteressent anlegen
                    $timestamp = time(); 
                    $preInteressent = new preinteressent(); 
                    $preInteressent->person_id = $person->person_id; 
                    $preInteressent->aufmerksamdurch_kurzbz = 'k.A.';
                    $preInteressent->kontaktmedium_kurzbz = 'bewerbungonline'; 
                    $preInteressent->erfassungsdatum = date('Y-m-d', $timestamp);
                    $preInteressent->insertamum = date('Y-m-d H:i:s'); 
                    $preInteressent->updateamum = date('Y-m-d H:i:s'); 
                    $preInteressent->new = true; 
                    if(!$preInteressent->save())
                        die('Fehler beim anlegen des Preinteressenten');
                    
                    // Zuordnungen anlegen
                    
                    for($i = 0; $i<$anzStg; $i++)
                    {
                        $preIntZuordnung = new preinteressent(); 
                        $preIntZuordnung->preinteressent_id = $preInteressent->preinteressent_id;
                        $preIntZuordnung->studiengang_kz = $studiengaenge[$i]; 
                        $preIntZuordnung->prioritaet = '1';
                        $preIntZuordnung->insertamum = date('Y-m-d H:i:s'); 
                        $preIntZuordnung->updateamum = date('Y-m-d H:i:s'); 
                        $preIntZuordnung->new = true; 
                        if(!$preIntZuordnung->saveZuordnung())
                            die('Fehler beim anlegen des Preinteressenten');
                    }
                    
                }

                //Email schicken
                echo sendMail($zugangscode, $email); 
                exit(); 
            }
        }
        
        // User sieht Registrationsmaske
        echo '		<table width="100%" border="0">
            <tr>
                <td align="left"><a href="'.$_SERVER['PHP_SELF'].'">Login</a> &gt; Registration </td>
            </tr>
        </table>';
        echo $message.'	
            <form action="'.$_SERVER['PHP_SELF'].'?method=registration" method="POST" name="RegistrationLoginForm">
			<table border = "0" align="" style="margin-top:4%;margin-left:15%">
				<tr>
                    <td width="250px">Code (falls vorhanden):</td>
                    <td><input type="text" size="30" value="Zugangscode" name ="userid" onfocus="this.value=\'\';">&nbsp; <input type="submit" value="Login"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
                <form action="'.$_SERVER['PHP_SELF'].'?method=registration" method="POST" name="RegistrationForm">
           <table border = "0" align="" style="margin-left:15%">
                <tr>
					<td width="250px">'.$p->t('global/vorname').'</td>
					<td><input type="text" size="40" maxlength="32" name="vorname" value="'.$vorname.'"></td>
				</tr>
				<tr>
					<td>'.$p->t('global/nachname').'</td>
					<td><input type="text" size="40" maxlength="64" name="nachname" value="'.$nachname.'"></td>
				</tr>
				<tr>
					<td>'.$p->t('global/geburtsdatum').'</td>
					<td><input type="text" size="20" name="geb_datum" value="'.$geb_datum.'" onfocus="this.value=""\"; > (dd.mm.yyyy)</td>
				</tr>
                <tr>
					<td>'.$p->t('global/geschlecht').'</td>
					<td>';
            $checked = ($geschlecht =='m')?'checked':'';
            echo'       <input type="radio" name="geschlecht" value="m" '.$checked.'> '.$p->t('global/mann');
            $checked= ($geschlecht == 'w')?'checked':''; 
            echo'       <input type="radio" name="geschlecht" value="w" '.$checked.'> '.$p->t('global/frau').'
	    			</td>
				</tr>	
				<tr>
					<td>&nbsp;</td>
				</tr>		
				<tr>
					<td>E-Mail</td>
					<td><input type="email" size="40" maxlength="128" name="email" id="email" value="'.$email.'"></td>
				</tr>	
				<tr>
					<td>'.$p->t('global/studienrichtung').':</td>
                    <td></td>
                    <td></td>
                </tr>';
                    $stg = new studiengang(); 
                    $stg->getAll();
                    
                    foreach($stg->result as $result)
                    {
                        $checked = '';
                        if(in_array($result->studiengang_kz, $studiengaenge) || $result->studiengang_kz == $stg_auswahl)
                            $checked = 'checked';
                        echo '<tr><td></td><td>'.$result->bezeichnung.':</td><td><input type="checkbox" name="studiengaenge[]" value="'.$result->studiengang_kz.'" '.$checked.'></td></tr>';
                    }
      echo'
                <tr>
                    <td><img id="captcha" src="'.APP_ROOT.'include/securimage/securimage_show.php" alt="CAPTCHA Image" /></td>
                    <td><input type="text" name="captcha_code" size="10" maxlength="6" />';?>
                    

                <a href="#" onclick="document.getElementById('captcha').src = '<?php echo APP_ROOT;?>include/securimage/securimage_show.php?' + Math.random(); return false">[ Anderer Code ]</a>
     <?php           
        echo'       </td>
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
                if(document.RegistrationForm.email.value == "")
                {
                    alert("Keine E-Mail Adresse angegeben.");
                    return false; 
                }
                return true; 
            }
        </script>';
    }
    else
    {
        /**
         * Login wird angezeigt 
         */
        echo '<table width="100%" border="0">
                <tr>
                    <td align="left"></td>
                </tr>
            </table>    
            <form action ="'.$_SERVER['PHP_SELF'].'" method="POST">
            <table border ="0" width ="100%" height="40%">
                <tr height="50%">
                    <td align ="center" valign="center"><h3>'.$p->t('ktu/welcome').'</h3><span style="font-size:1.2em"></span></td>
                </tr>
                <tr >
                    <td align="center" valign="bottom"> <img src="../../skin/styles/ktu/KTULogo.jpg"></td>
                </tr>
            </table>
            <table border ="0" width ="100%">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"><a href="'.$_SERVER['PHP_SELF'].'?method=registration">'.$p->t('incoming/registration').'</a></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"><input type="text" size="30" value="UserID" name ="userid" onfocus="this.value=\'\';"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"><input type="submit" value="Login" name="submit"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center">';
                    
                if(isset($errormsg))
                    echo $errormsg; 
                echo '</tr>
            </table>
            </form>';
            }
	echo '</body>
</html>';
    
function sendMail($zugangscode, $email)
{
	global $p, $vorname, $nachname; 
   
	$mail = new mail($email, 'no-reply', 'Registration', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
	$text = 'Sehr geehrteR Herr/Frau '.$vorname.' '.$nachname.'.<br><br>
        Vielen Dank für Ihr Interesse an einem Studiengang der Katholisch Theologischen Universität Linz. <br> 
        Um sich für einen Studiengang zu bewerben verwenden Sie bitte folgenden Link und Zugangscode: <br><br>
        <a href="ktu.technikum-wien.at/cis/public/registration.php">Link zur Bewerbung</a><br>
        Zugangscode: '.$zugangscode.' <br><br>
        Mit freundlichen Grüßen, <br>
        KTU Linz';
    $mail->setHTMLContent($text); 
	if(!$mail->send())
		$msg= '<span class="error">Fehler beim Senden des Mails</span><br /><a href='.$_SERVER['PHP_SELF'].'?method=registration>Zurück zur Anmeldung</a>';
	else
		$msg= $p->t('global/emailgesendetan')." $email!<br><a href=".$_SERVER['PHP_SELF'].">Zurück zur Anmeldung</a>";
	
    // sende Nachricht an Assistenz 

	return $msg; 
}