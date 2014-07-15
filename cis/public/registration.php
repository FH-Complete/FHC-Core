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
 * 			Manfred Kindl 	<kindlm@technikum-wien.at>
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
require_once('../../include/sprache.class.php');

require_once '../../include/securimage/securimage.php';

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$method = isset($_GET['method'])?$_GET['method']:'';
$message = "";
$datum = new datum(); 

//$studiensemester = new studiensemester(); 
//$std_semester = $studiensemester->getakt();

if(isset($_GET['sprache']))
{
	$sprache = new sprache();
	if($sprache->load($_GET['sprache']))
	{
		setSprache($_GET['sprache']);
	}
	else
		setSprache(DEFAULT_LANGUAGE);
}

$sprache = getSprache(); 
$p = new phrasen($sprache); 
$db = new basis_db();

// Login gestartet
if (isset($_POST['userid'])) 
{
	$login = $_REQUEST['userid']; 
	$person = new person(); 
		
	session_start();
	$person_id=$person->checkZugangscodePerson(trim($login)); 
	
	//Zugangscode wird überprüft
	if($person_id != false)
	{
		$_SESSION['bewerbung/user'] = $login;
		$_SESSION['bewerbung/personId'] = $person_id; 

        
       
		header('Location: bewerbung.php');
		exit;
	}
	else
	{
		$message= "<script type=\"text/javascript\">alert('".$p->t('bewerbung/zugangsdatenFalsch')."')</script>";
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Registration für Studiengänge</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="robots" content="noindex">
		<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link href="../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
	</head>
	<script type="text/javascript">
	function changeSprache(sprache)
	{
		method = '<?php echo $db->convert_html_chars($method);?>';
		
		window.location.href="registration.php?sprache="+sprache+"&method="+method;
	}
    function checkRegistration()
    {
    	if(document.RegistrationLoginForm.vorname.value == "")
        {
            alert("<?php echo $p->t('bewerbung/bitteVornameAngeben')?>");
            return false; 
        }
    	if(document.RegistrationLoginForm.nachname.value == "")
        {
            alert("<?php echo $p->t('bewerbung/bitteNachnameAngeben')?>");
            return false; 
        }
    	if(document.RegistrationLoginForm.geb_datum.value == "")
        {
            alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
            return false; 
        }
	else
	{
	    var gebDat = document.RegistrationLoginForm.geb_datum.value;
	    gebDat = gebDat.split(".");
	    if(gebDat.length !== 3)
	    {
		alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
		return false;
	    }
	    if(gebDat[0].length !==2 && gebDat[1].length !== 2 && gebDat[2].length !== 4)
	    {
		alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
		return false;
	    }
	    	    
	    var date = new Date(gebDat[2], gebDat[1], gebDat[0]);
	    date.setMonth(date.getMonth()-1);

	    gebDat[0] = parseInt(gebDat[0], 10);
	    gebDat[1] = parseInt(gebDat[1], 10)-1;
	    gebDat[2] = parseInt(gebDat[2], 10);
	    
	    if(!(date.getFullYear() === gebDat[2] && date.getMonth() === gebDat[1] && date.getDate() === gebDat[0]))
	    {
		alert("<?php echo $p->t('bewerbung/bitteGeburtsdatumEintragen')?>");
		return false;
	    }  
	}
        if((document.getElementById('geschlechtm').checked == false)&&(document.getElementById('geschlechtw').checked == false))
        {
            alert("<?php echo $p->t('bewerbung/bitteGeschlechtWaehlen')?>");
            return false; 
        }
        if(document.RegistrationLoginForm.email.value == "")
        {
            alert("<?php echo $p->t('bewerbung/bitteEmailAngeben')?>");
            return false; 
        }
        if(document.RegistrationLoginForm.studiensemester_kurzbz.value == "")
        {
            alert("<?php echo $p->t('bewerbung/bitteStudienbeginnWaehlen')?>");
            return false; 
        }
        return true; 
    }
	</script>
	<body class="main">
	<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
	<td class="rand"></td>
	<td class="boxshadow" valign="top" style="padding:10px; background-color: white;">
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
        $std_semester = isset($_REQUEST['studiensemester_kurzbz'])?$_REQUEST['studiensemester_kurzbz']:''; 
        $stg_auswahl = isset($_REQUEST['stg'])?$_REQUEST['stg']:'';
        
        
        $securimage = new Securimage(); 
        if(isset($_POST['submit']))
        {        	
        	// Sicherheitscode wurde falsch eingegeben
            if ($securimage->check($_POST['captcha_code']) == false) 
                $message = '<span class="error">'.$p->t('bewerbung/sicherheitscodeFalsch').'</span><br />';
            elseif (count($studiengaenge)==0)
            	$message = '<span class="error">'.$p->t('bewerbung/bitteStudienrichtungWaehlen').'</span><br />';
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
                $std_semester = $_REQUEST['studiensemester_kurzbz']; 

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
                        $prestudent_status->ausbildungssemester = '1'; 
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
                <td align="left"><a href="'.$_SERVER['PHP_SELF'].'">'.$p->t('bewerbung/login').'</a> &gt; '.$p->t('bewerbung/registration').' </td>
                <td align="right" width="10px"><select style="text-align: right; color: #0086CC; border: 0;" name="select" onchange="changeSprache(this.options[this.selectedIndex].value);">';
                    $sprache2 = new sprache();
					$sprache2->getAll(true);
					foreach($sprache2->result as $row)
					{
						echo ' <option value="'.$row->sprache.'" '.($row->sprache==$sprache?'selected':'').'>'.($row->bezeichnung_arr[getSprache()]).'&nbsp;&nbsp;</option>';
					}
        echo '	</select></td>
            </tr>
        </table>';
        echo $message.'	
            <form action="'.$_SERVER['PHP_SELF'].'?method=registration" method="POST" name="RegistrationLoginForm">
			<table border="0" align="" style="margin-top:4%;margin-left:15%">
	            <tr>
	            	<td colspan="3"><p>'.$p->t('bewerbung/einleitungstext').'</p><br><br></td>
	            </tr>
				<tr>
                    <td width="250px" align="right">'.$p->t('bewerbung/zugangscode').' '.$p->t('bewerbung/fallsVorhanden').':&nbsp;</td>
                    <td><input type="text" class="input_bewerbung" size="30" style="color: #888;" value="&nbsp;'.$p->t('bewerbung/zugangscode').'" name="userid" onfocus="this.value=\'\';this.style.color=\'black\'">&nbsp; <input type="submit" value="'.$p->t('bewerbung/login').'"></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
           <table border = "0" align="" style="margin-left:15%">
                <tr>
					<td width="250px" align="right">'.$p->t('global/vorname').':&nbsp;</td>
					<td><input type="text" class="input_bewerbung" size="40" maxlength="32" name="vorname" value="'.$vorname.'"></td>
				</tr>
				<tr>
					<td align="right">'.$p->t('global/nachname').':&nbsp;</td>
					<td><input type="text" class="input_bewerbung" size="40" maxlength="64" name="nachname" value="'.$nachname.'"></td>
				</tr>
				<tr>
					<td align="right">'.$p->t('global/geburtsdatum').':&nbsp;</td>
					<td><input type="datetime" class="input_bewerbung" size="20" name="geb_datum" value="'.$geb_datum.'"> (dd.mm.yyyy)</td>
				</tr>
                <tr>
					<td align="right">'.$p->t('global/geschlecht').':&nbsp;</td>
					<td>';
            $checked = ($geschlecht =='m')?'checked':'';
            echo'       <input type="radio" name="geschlecht" id="geschlechtm" value="m" '.$checked.'> '.$p->t('global/mann');
            $checked= ($geschlecht == 'w')?'checked':''; 
            echo'       <input type="radio" name="geschlecht" id="geschlechtw" value="w" '.$checked.'> '.$p->t('global/frau').'
	    			</td>
				</tr>			
				<tr>
					<td align="right">'.$p->t('global/emailAdresse').':&nbsp;</td>
					<td><input type="email" class="input_bewerbung" size="40" maxlength="128" name="email" id="email" value="'.$email.'"></td>
				</tr>
				<tr>
					<td align="right">'.$p->t('bewerbung/geplanterStudienbeginn').':&nbsp;</td>
					<td><select id="studiensemester_kurzbz" name="studiensemester_kurzbz">
					<option value="">'.$p->t('bewerbung/bitteWaehlen').'</option>';
                    $stsem = new studiensemester();
					$stsem->getFutureStudiensemester('',4);
					
					foreach($stsem->studiensemester as $row)
					{
						echo ' <option value="'.$row->studiensemester_kurzbz.'" '.($std_semester==$row->studiensemester_kurzbz?'selected':'').'>'.$row->bezeichnung.'</option>';
					}
        	echo '	</select></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>	
				<tr>
					<td valign="top" align="right">'.$p->t('bewerbung/studienrichtung').':&nbsp;</td>
                    <td><table cellpadding="1" cellspacing="0" style="border-spacing:0;">';
                    $stg = new studiengang(); 
                    $stg->getAll('typ,bezeichnung',true);
                    
                    foreach($stg->result as $result)
                    {
			if($result->studiengang_kz > 0)
			{
			    $checked = '';
			    $typ = new studiengang(); 
			    $typ->getStudiengangTyp($result->typ);
			    if(in_array($result->studiengang_kz, $studiengaenge) || $result->studiengang_kz == $stg_auswahl)
				$checked = 'checked';
			    echo '<tr><td></td><td valign="middle"><input type="checkbox" name="studiengaenge[]" value="'.$result->studiengang_kz.'" '.$checked.'>&nbsp;&nbsp;&nbsp;'.$result->bezeichnung.'</td></tr>';
			}
                    }
      echo'		</table></tr>
                <tr>
                    <td align="center"><img id="captcha" src="'.APP_ROOT.'include/securimage/securimage_show.php" alt="CAPTCHA Image" style="border:1px solid;" /><br>
                    <a href="#" onclick="document.getElementById(\'captcha\').src = \''.APP_ROOT.'include/securimage/securimage_show.php?\' + Math.random(); return false">'.$p->t('bewerbung/andereGrafik').'</a></td>
                    <td>'.$p->t('bewerbung/captcha').'<br><input type="text" name="captcha_code" size="10" maxlength="6" />';?>
                    
     <?php           
        echo'       </td>
                </tr>
				<tr>
					<td colspan="2" align="center"><input type="submit" name="submit" value="'.$p->t('bewerbung/registrieren').'" onclick="return checkRegistration()"></td>		
				</tr>
				<tr><td><input type="hidden" name="zugangscode" value="'.uniqid().'"></td></tr>	
			</table>
		</form>';
    }
    else
    {
        /**
         * Login wird angezeigt 
         */
        echo '<table width="100%" border="0">
                <tr>
                    <td align="right" width="10px"><select style="text-align: right; color: #0086CC; border: 0;" name="select" onchange="changeSprache(this.options[this.selectedIndex].value);">';
                    $sprache2 = new sprache();
					$sprache2->getAll(true);
					foreach($sprache2->result as $row)
					{
						echo ' <option value="'.$row->sprache.'" '.($row->sprache==$sprache?'selected':'').'>'.($row->bezeichnung_arr[getSprache()]).'&nbsp;&nbsp;</option>';
					}
        echo '	</select></td>
                </tr>
            </table>';
        echo $message.'  
            <form action ="'.$_SERVER['PHP_SELF'].'" method="POST">
            <table border="0" width="100%">
                <tr>
                    <td align="center" valign="center" style="padding-top: 80px;"><h1>'.$p->t('bewerbung/welcome').'</h1><span style="font-size:1.2em"></span></td>
                </tr>
                <tr >
                    <td align="center" valign="bottom" style="padding-top: 80px;padding-bottom: 80px;"> <img src="../../skin/styles/'.DEFAULT_STYLE.'/logo.png" style="max-width: 400px; max-height: 400px; overflow: hidden;"></td>
                </tr>
            </table>
            <table border ="0" width ="100%">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center">'.$p->t('bewerbung/registrierenOderZugangscode').'</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td align="center"><input class="input_bewerbung" type="text" size="30" style="color: #888;" value="&nbsp;'.$p->t('bewerbung/zugangscode').'" name="userid" onfocus="this.value=\'\';this.style.color=\'black\'"></td>
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
	echo '</td>
<td class="rand">
</td>
</tr>
</table>
</body>
</html>';
    
function sendMail($zugangscode, $email)
{
	global $p, $vorname, $nachname; 
   
	$mail = new mail($email, 'no-reply', $p->t('bewerbung/registration'), $p->t('bewerbung/mailtextHtml'));
	$text = $p->t('bewerbung/mailtext',array($vorname, $nachname, $zugangscode));
    $mail->setHTMLContent($text); 
	if(!$mail->send())
		$msg= '<span class="error">'.$p->t('bewerbung/fehlerBeimSenden').'</span><br /><a href='.$_SERVER['PHP_SELF'].'?method=registration>'.$p->t('bewerbung/zurueckZurAnmeldung').'</a>';
	else
		$msg= $p->t('global/emailgesendetan')." $email!<br><a href=".$_SERVER['PHP_SELF'].">".$p->t('bewerbung/zurueckZurAnmeldung')."</a>";
	
    // sende Nachricht an Assistenz 

	return $msg; 
}