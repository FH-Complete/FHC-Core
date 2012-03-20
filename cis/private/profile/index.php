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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <	gerald.simane-sequens@technikum-wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/fckeditor/fckeditor.php');
require_once('../../../include/person.class.php');
require_once('../../../include/safehtml/safehtml.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/betriebsmittel.class.php');
require_once('../../../include/betriebsmittelperson.class.php');
require_once('../../../include/betriebsmitteltyp.class.php');  
require_once('../../../include/phrasen.class.php');
require_once('../../../include/betriebsmittel_betriebsmittelstatus.class.php');

$sprache = getSprache(); 
$p=new phrasen($sprache);
  
if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
      
$uid=get_uid();
	
$datum_obj = new datum();
	
$ansicht=false; //Wenn ein anderer User sich das Profil ansieht (Bei Personensuche)
if(isset($_GET['uid']))
{
	$uid=stripslashes($_GET['uid']);
	$ansicht=true;
}
		
$stg = '';

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($stg_obj->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kurzbzlang;
	
if(!($erg=$db->db_query("SELECT * FROM campus.vw_benutzer WHERE uid='$uid'")))
	die($db->db_last_error());
$num_rows=$db->db_num_rows($erg);
if ($num_rows==1)
{
	$person_id=$db->db_result($erg,0,"person_id");
	$vorname=$db->db_result($erg,0,"vorname");
	$vornamen=$db->db_result($erg,0,"vornamen");
	$nachname=$db->db_result($erg,0,"nachname");
	$gebdatum=$db->db_result($erg,0,"gebdatum");
	$gebort=$db->db_result($erg,0,"gebort");
	$titelpre=$db->db_result($erg,0,"titelpre");
	$titelpost=$db->db_result($erg,0,"titelpost");
	$email=$db->db_result($erg,0,"uid").'@'.DOMAIN;
	$email_alias=$db->db_result($erg,0,"alias");
	$hp=$db->db_result($erg,0,"homepage");
	$aktiv=$db->db_result($erg,0,"aktiv");
	$foto=$db->db_result($erg,0,"foto");
}
if(!($erg_stud=$db->db_query("SELECT studiengang_kz, semester, verband, gruppe, matrikelnr, typ::varchar(1) || kurzbz AS stgkz, tbl_studiengang.bezeichnung AS stgbz FROM public.tbl_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE student_uid='$uid'")))
	die($db->db_last_error());
$stud_num_rows=$db->db_num_rows($erg_stud);

if ($stud_num_rows==1)
{
	$stg=$db->db_result($erg_stud,0,"studiengang_kz");
	$stgbez=$db->db_result($erg_stud,0,"stgbz");
	$stgkz=$db->db_result($erg_stud,0,"stgkz");
	$semester=$db->db_result($erg_stud,0,"semester");
	$verband=$db->db_result($erg_stud,0,"verband");
	$gruppe=$db->db_result($erg_stud,0,"gruppe");
	$matrikelnr=$db->db_result($erg_stud,0,"matrikelnr");
}
if(!($erg_lekt=$db->db_query("SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'")))
	die($db->db_last_error());
		
$lekt_num_rows=$db->db_num_rows($erg_lekt);
if ($lekt_num_rows==1)
{
	$row=$db->db_fetch_object($erg_lekt,0);
	$kurzbz=$row->kurzbz;
	$tel=$row->telefonklappe;

	$vorwahl = '';
	if($tel != "")
	{
		$vorwahl = '+43 1 333 40 77-';
		if($row->standort_id!='')
		{
			$qry = "SELECT kontakt FROM public.tbl_kontakt WHERE standort_id='$row->standort_id' AND kontakttyp='telefon'";
			if($result_tel = $db->db_query($qry))
				if($row_tel = $db->db_fetch_object($result_tel))
					$vorwahl = $row_tel->kontakt;
		}
	}	
}

// Mail-Groups
if(!($erg_mg=$db->db_query("SELECT gruppe_kurzbz, beschreibung FROM campus.vw_persongruppe WHERE mailgrp AND uid='$uid'  ".(isset($semester)?" and semester=$semester ":'')."  ORDER BY gruppe_kurzbz")))
	die($db->db_last_error());
$nr_mg=$db->db_num_rows($erg_mg);
	
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Profil</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script language="Javascript">
	<!--
		function RefreshImage()
		{
			window.location.reload();
		}
	-->
	</script>
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tr>
		<td class='ContentHeader'><font class='ContentHeader'>&nbsp;Userprofil</font></td>
	  </tr>
	</table>
<?php
	/*
	Results: <?php echo $num_rows; ?><br>
	Username: <?php echo $uid; ?><br><br>
	*/
	if ($num_rows==1)
	{
		if(isset($_POST['savekurzbeschreibung']) && !$ansicht)
		{
			$person = new person();
			$person->load($person_id);
			
			//Remove Script Tags and other stuff
			$parser = new SafeHTML();
			$result = $parser->parse($_POST['kurzbeschreibung']);
			
			$person->kurzbeschreibung = $result;
			$person->updateamum = date('Y-m-d H:i:s');
			$person->updatevon = $uid;
			if($person->save())
				echo '<b>Kurzbeschreibung wurde erfolgreich gespeichert</b>';
			else 
				echo '<span class="error">Fehler beim Speichern der Kurzbeschreibung</span>';
		}
		
		if($aktiv=='f')
		{
			if(!$ansicht)
			{
				$message = "Wir möchten Sie darauf aufmerksam machen, dass Ihr Benutzerdatensatz deaktiviert wurde. Durch diese Deaktivierung wurden Sie auch aus allen Email-Verteilern gelöscht. <br><br>";
				if ($stud_num_rows==1)
					$message .= "Sollte innerhalb von 6 Monaten (für Studierende) bzw. 3 Wochen (für AbbrecherInnen) nach der Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch<br>";
				elseif($lekt_num_rows==1)
					$message .= "Sollte innerhalb von 12 Monaten nach der Deaktivierung keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch<br>";
				else 
					$message .= "Sollte innerhalb der nächsten Tagen keine neuerliche Aktivierung Ihres Benutzerdatensatzes erfolgen, dann werden automatisch auch<br>";
				$message .= "- Ihr Account, <br>";
				$message .= "- Ihre Mailbox (inkl. aller E-Mails) und<br>";
				$message .= "- Ihr Home-Verzeichnis (inkl. aller Dateien) gelöscht.<br><br>";
				$message .= "Falls es sich bei der Deaktivierung um einen Irrtum handelt, würden wir Sie bitten, sich umgehend mit Ihrer Studiengangsassistenz in Verbindung zu setzen.<br>";
		
				echo "<span style='color: red;'>Achtung!<br>$message</span>";
			}
			else 
				echo "<span style='color: red;'>Achtung: Dieser Account ist nicht mehr aktiv</span>";
		}
		echo '
		<table class="tabcontent">
  		<tr>
    		<td colspan="2" class="MarkLine" width="60%">
    		<table width="100%"><tr><td>
      		<P><br>
      			'.$p->t('global/username').': '.$uid.'<br>
      			'.$p->t('global/titel').': '.$titelpre.' <br>
        		'.$p->t('global/vorname').': '.$vorname.'  '.$vornamen.'<br>
        		'.$p->t('global/nachname').': '.$nachname.'<br>
        		'.$p->t('global/postnomen').': '.$titelpost.'<br>';
        		
		if(!$ansicht)
		{
        	echo '	'.$p->t('global/geburtsdatum').': '.$datum_obj->formatDatum($gebdatum,'d.m.Y')."<br>
        			".$p->t('global/geburtsort').": $gebort<br>";
        		
        }
        
        echo '
      		</P>
      		</td>
      		<td align="right">';
        //Foto anzeigen oder Upload Button
        if($foto!='')
        	echo '<img id="personimage" src="../../public/bild.php?src=person&person_id='.$person_id.'" alt="'.$person_id.'" height="100px">';
        else
        {
        	if(!$ansicht)
        		echo "<a href='#BildUpload' onclick='window.open(\"../bildupload.php?person_id=$person_id\",\"BildUpload\", \"height=100,width=500,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>".$p->t('profil/bildHochladen')."</a>";
        }
      	echo '
      		</td></tr></table>
      		<P>
      			<b>'.$p->t('profil/email').'</b><br>
        		'.$p->t('profil/intern').': <a class="Item" href="mailto:'.$uid.'@'.DOMAIN.'">'.$uid.'@'.DOMAIN.'</a><br>';

		if($email_alias!='' && !in_array($stg,$noalias))
		{
			echo $p->t('profil/alias').": <a class='Item' href='mailto:$email_alias@".DOMAIN."'>$email_alias@".DOMAIN."</a>";
		}
    echo '</P>';
		if($hp!='')
			echo "<P><b>".$p->t('profil/homepage')."</b><br><a href='$hp' target='_blank'>$hp</a></p>";
		echo '<p>';
		echo '
      		</p>
        	<br>
    		</td>
    		<td rowspan="2">';
      			
		echo '<P>';
		if ($stud_num_rows==1)
		{
			echo "
				<b>".$p->t('profil/student')."</b><br><br>
			".$p->t('global/studiengang').": $stgbez<br>
			".$p->t('global/semester').": $semester<br>
			".$p->t('global/verband').": $verband<br>
			".$p->t('global/gruppe').": $gruppe<br>
    		".$p->t('profil/martrikelnummer').": $matrikelnr<br />";
    		
    		if(!$ansicht)
    		{
    			echo "
		    		<br />
		    		<A class='Item' href='../lehre/notenliste.php'>".$p->t('profil/leistungsbeurteilung')."</a><br />";
    		}
		}
		
		if ($lekt_num_rows==1)
		{
			echo "
				<P>
				<b>Lektor</b><br><br>
			".$p->t('profil/kurzzeichen').": $kurzbz<BR>";
			
								
			if($tel!='')
				echo $p->t('profil/telefonTw').": $vorwahl $tel<BR><BR>";
				
			if(!$ansicht)
			{
				echo '
					<A class="Item" href="zeitwunsch.php?uid='.$uid.'">'.$p->t('profil/zeitwuensche').'</A><BR>
					<A class="Item" href="lva_liste.php?uid='.$uid.'">'.$p->t('lvaliste/lehrveranstaltungen').'</A><br>
					<A class="Item" href="freebusy.php">'.$p->t('freebusy/titel').'</A>';
			}
		}
		
		if(!$ansicht)
		{
			//Funktionen
			$qry = "SELECT 
						*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
						 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
					FROM 
						public.tbl_benutzerfunktion 
						JOIN public.tbl_funktion USING(funktion_kurzbz) 
						JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
					WHERE 
						uid='$uid' AND 
						(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
						(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())";
			
			if($result_funktion = $db->db_query($qry))
			{
				if($db->db_num_rows($result_funktion)>0)
				{
					echo '<br><br><b>'.$p->t('profil/funktionen').'</b><table><tr class="liste"><th>'.$p->t('global/bezeichnung').'</th><th>'.$p->t('global/organisationseinheit').'</th><th>'.$p->t('global/semester').'</th><th>'.$p->t('global/institut').'</th></tr>';

					while($row_funktion = $db->db_fetch_object($result_funktion))
					{
						echo "<tr class='liste1'><td>$row_funktion->bf_bezeichnung</td><td nowrap>".$row_funktion->organisationseinheittyp_kurzbz.' '.$row_funktion->oe_bezeichnung."</td><td>$row_funktion->semester</td><td>$row_funktion->fachbereich_kurzbz</td></tr>";
					}
					echo '</table>';
				}
			}

    		// Betriebsmittel Personen
    		$oBetriebsmittelperson = new betriebsmittelperson();
    		$oBetriebsmittelperson->result=array();
    		$oBetriebsmittelperson->errormsg='';
			
    		if ($oBetriebsmittelperson->getBetriebsmittelPerson($person_id))
	        {
	      		if (is_array($oBetriebsmittelperson->result) && count($oBetriebsmittelperson->result)>0)
	    	  	{
	    			echo '<br><br><b>'.$p->t('profil/entlehnteBetriebsmittel').'</b>
	    				<table>
	    					<tr class="liste">
	    						<th>'.$p->t('profil/betriebsmittel').'</th>
	    						<th>'.$p->t('profil/nummer').'</th>
	    						<th>'.$p->t('profil/ausgegebenAm').'</th>
	    					</tr>';
	   
		            for ($i=0;$i<count($oBetriebsmittelperson->result);$i++)
		            {
		                if (empty($oBetriebsmittelperson->result[$i]->retouram) )
		                {
		                	$bm = new betriebsmittel_betriebsmittelstatus();
		                	if($bm->load_last_betriebsmittel_id($oBetriebsmittelperson->result[$i]->betriebsmittel_id) 
		                	&& $bm->betriebsmittelstatus_kurzbz<>'vorhanden')
		                	{
		                		continue;
		                	}		                	
		      				echo "<tr class='liste1'>
		      						<td>".$oBetriebsmittelperson->result[$i]->betriebsmitteltyp.' '.$oBetriebsmittelperson->result[$i]->beschreibung."</td>
		      						<td>".$oBetriebsmittelperson->result[$i]->nummer.' '.$oBetriebsmittelperson->result[$i]->inventarnummer."</td>
		      						<td>".$datum_obj->formatDatum($oBetriebsmittelperson->result[$i]->ausgegebenam,'d.m.Y')."</td>
		      					</tr>";
		                	
		                }
		            }
		    		echo '</table>';
				}
	        }
		}
    
		if(!$ansicht)
		{
			echo "";
			echo "";
		}
		echo "</P>";
		
		echo '	
    		</td>
  		</tr>
  		<TR>
    		<TD colspan="2">
      		<P><B>'.$p->t('mailverteiler/mailverteiler').'</B><BR><BR>
      		';
		//Mailverteiler
		if(!$ansicht)
			echo "<SMALL>".$p->t('profil/sieSindMitgliedInFolgendenVerteilern').":</SMALL>";
		else
			echo "<SMALL>".$p->t('profil/derUserIstInFolgendenVerteilern',array($uid)).":</SMALL>";
        
		echo '
        	</P>
    		</TD>
    		<TD> </TD>
  		</TR>';
  		
  		for($i=0;$i<$nr_mg;$i++)
		{
			$row=$db->db_fetch_object($erg_mg,$i);
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($row->gruppe_kurzbz)).'@'.DOMAIN.'">'.strtolower($row->gruppe_kurzbz).'&nbsp;</TD>';
    		echo "<TD>&nbsp;$row->beschreibung</TD><TD></TD></TR>";
		}
		
		if (isset($matrikelnr))
		{
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($stgkz)).'_std@'.DOMAIN.'">'.strtolower($stgkz).'_std&nbsp;</TD>';
    		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $stgbez</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($stgkz)).trim($semester).'@'.DOMAIN.'">'.strtolower($stgkz).$semester.'&nbsp;</TD>';
    		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $stgkz $semester</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($stgkz)).trim($semester).strtolower(trim($verband)).'@'.DOMAIN.'">'.strtolower($stgkz).$semester.strtolower($verband).'&nbsp;</TD>';
    		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $stgkz $semester$verband</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($stgkz)).trim($semester).strtolower(trim($verband)).trim($gruppe).'@'.DOMAIN.'">'.strtolower($stgkz).$semester.strtolower($verband).$gruppe.'&nbsp;</TD>';
    		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $stgkz $semester$verband$gruppe</TD><TD></TD></TR>";
		}

		$mail = MAIL_ADMIN;
		if($stg=='')
		{
			$stg = 0;
		}
		
		//Wenn eine Assistentin fuer diesen Studiengang eingetragen ist,
		//dann werden die aenderungswuesche an diese Adresse gesendet
		$qry = "SELECT email FROM public.tbl_studiengang where studiengang_kz='$stg'";
		if($row=$db->db_fetch_object($db->db_query($qry)))
		{
			if($row->email!='')
				$mail = $row->email;
			else
				$mail = MAIL_ADMIN;
		}
		if($stg=='0')
			$mail = MAIL_GST;

		echo '
			</table>
			<BR>';
			
		if(!$ansicht)
		{
			//Wenn eine OEH Kandidatur vorhanden ist, WYSIWYG Editor anzeigen
			$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oeh-kandidatur' AND uid='$uid'";
			if($result = $db->db_query($qry))
			{
				if($db->db_num_rows($result)>0)
				{
					$person = new person();
					$person->load($person_id);
					echo '<hr>';
					echo '<b>'.$p->t('profil/kurzbeschreibungFuerOeh').':</b><br>';
					echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
					
					// Automatically calculates the editor base path based on the _samples directory.
					// This is usefull only for these samples. A real application should use something like this:
					// $oFCKeditor->BasePath = '/fckeditor/' ;	// '/fckeditor/' is the default value.
					$sBasePath = $_SERVER['PHP_SELF'] ;
					$sBasePath = '../../../include/fckeditor/';
					
					$oFCKeditor = new FCKeditor('kurzbeschreibung') ;
					
					$oFCKeditor->BasePath	= $sBasePath ;
					$oFCKeditor->Value		= $person->kurzbeschreibung;
					$oFCKeditor->Create() ;
					
					echo '
					    <br>
					    <input type="submit" value="'.$p->t('global/speichern').'" name="savekurzbeschreibung">
					  </form>';
				}
			}
			
			echo "
			<HR>
			".$p->t('profil/solltenDatenNichtStimmen')." <a class='Item' href=\"mailto:$mail?subject=Datenkorrektur&body=Die%20Profildaten%20fuer%20User%20'$uid'%20sind%20nicht%20korrekt.%0D
			Hier die richtigen Daten:%0DNachname:%20$nachname%0DVorname:%20$vorname%0DGeburtsdatum:%20$gebdatum
			%0DGeburtsort:%20$gebort%0DTitelPre:%20$titelpre%0DTitelPost:%20$titelpost
			%0D%0D***%0DPlatz fuer weitere (nicht angefuehrte Daten)%0D***\">".$p->t('profil/zustaendigeAssistenz')."</a>";
		
		}
	}
	else
	{
		echo '		
		<br><br>
		'.$p->t('profil/esWurdenKeineProfileGefunden').'.
		<br>
		'.$p->t('profil/wendenSieSichAn').' <a class="Item" href="mailto:'.MAIL_ADMIN.'?subject=Profilfehler&body=Es wurden zuviele oder zuwenige Profile fuer User '.$uid.' gefunden. %0DBitte kontrollieren sie die Datenbank!%0D%0DMeine Daten sind:%0DNachname:%0DVornamen:%0D...">'.$p->t('profil/adminstration').'</a>';
	}
?>
</body>
</html>
