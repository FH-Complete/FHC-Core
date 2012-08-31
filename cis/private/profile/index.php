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
require_once('../../../include/person.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/betriebsmittel.class.php');
require_once('../../../include/betriebsmittelperson.class.php');
require_once('../../../include/betriebsmitteltyp.class.php');  
require_once('../../../include/phrasen.class.php');
require_once('../../../include/betriebsmittel_betriebsmittelstatus.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/fotostatus.class.php');

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

if(!$ansicht && isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case 'foto_freigabe':
			$benutzer = new benutzer();
			if($benutzer->load($uid))
			{
				$person = new person();
				if($person->load($benutzer->person_id))
				{
					$person->foto_sperre=false;
					$person->new=false;
					$person->save();
				}
			}
			break;
		case 'foto_sperre':
			$benutzer = new benutzer();
			if($benutzer->load($uid))
			{
				$person = new person();
				if($person->load($benutzer->person_id))
				{
					$person->foto_sperre=true;
					$person->new=false;
					$person->save();
				}
			}
			break;
	}
}
		
$stg = '';

$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($stg_obj->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kurzbzlang;
	
if(check_lektor($uid))
{
	$user = new mitarbeiter();
	$type = 'mitarbeiter';
}
else
{
	$user = new student();
	$type='student';
}

if(!$user->load($uid))
	die($p->t('profil/esWurdenKeineProfileGefunden'));

if ($type=='mitarbeiter')
{
	$vorwahl = '';
	$kontakt = new kontakt();
	$kontakt->loadFirmaKontakttyp($user->standort_id,'telefon');
	$vorwahl = $kontakt->kontakt;	
}

// Mail-Groups
if(!($erg_mg=$db->db_query("SELECT gruppe_kurzbz, beschreibung FROM campus.vw_persongruppe WHERE mailgrp AND uid='$uid'  ".(isset($semester)?" and semester=$semester ":'')."  ORDER BY gruppe_kurzbz")))
	die($db->db_last_error());
$nr_mg=$db->db_num_rows($erg_mg);
	
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>'.$p->t('profil/profil').'</title>
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
	<h1>'.$p->t('profil/profil').'</h1>
';
		
if(!$user->bnaktiv)
{
	if(!$ansicht)
	{
		
		if ($type=='student')
			$message = $p->t('profil/inaktivStudent');
		elseif($type=='mitarbeiter')
			$message = $p->t('profil/inaktivMitarbeiter');
		else
			$message = $p->t('profil/inaktivSonstige');		
	}
	else
		$message = $p->t('profil/AccountInaktiv');
		
	echo "<span style='color: red;'>$message</span>"; 
}

echo '
<table class="tabcontent">
	<tr>
  		<td colspan="2" class="MarkLine" width="60%" height="100" valign="top">
   		<table width="100%">
   			<tr>
   				<td>
   		<P><br>
		'.$p->t('global/username').': '.$user->uid.'<br>
		'.$p->t('global/titel').': '.$user->titelpre.' <br>
  		'.$p->t('global/vorname').': '.$user->vorname.'  '.$user->vornamen.'<br>
   		'.$p->t('global/nachname').': '.$user->nachname.'<br>
  		'.$p->t('global/postnomen').': '.$user->titelpost.'<br>';
        		
if(!$ansicht)
{
	echo '	'.$p->t('global/geburtsdatum').': '.$datum_obj->formatDatum($user->gebdatum,'d.m.Y')."<br>
	".$p->t('global/geburtsort').": $user->gebort<br>";
        		
}
        
echo '
	</P>
    </td>
      		<td align="right">';
//Foto anzeigen

echo '<br>';
if(!($ansicht && $user->foto_sperre))
	echo '<img id="personimage" src="../../public/bild.php?src=person&person_id='.$user->person_id.'" alt="'.$user->person_id.'" height="100px" width="75px">';

if(!$ansicht)
{
	//Foto Upload nur möglich wenn das Bild noch nicht akzeptiert wurde
	$fs = new fotostatus();
	if(!$fs->akzeptiert($user->person_id))
		echo "<br><a href='#BildUpload' onclick='window.open(\"../bildupload.php?person_id=$user->person_id\",\"BildUpload\", \"height=500,width=500,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>".$p->t('profil/bildHochladen')."</a>";
}
if($user->foto_sperre)
	echo '<br><b>'.$p->t('profil/profilfotoGesperrt').'</b>';
	
if(!$ansicht)
{
	if($user->foto_sperre)
		echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=foto_freigabe" title="'.$p->t('profil/infotextSperre').'">'.$p->t('profil/fotofreigeben').'</a>';
	else
		echo '<br><a href="'.$_SERVER['PHP_SELF'].'?action=foto_sperre" title="'.$p->t('profil/infotextSperre').'">'.$p->t('profil/fotosperren').'</a>';
}
	
echo '</td></tr></table>';
echo '
	<P>
    <b>'.$p->t('profil/email').'</b><br>
    '.$p->t('profil/intern').': <a class="Item" href="mailto:'.$user->uid.'@'.DOMAIN.'">'.$user->uid.'@'.DOMAIN.'</a><br>';

if($user->alias!='' && (!isset($user->studiengang_kz) || !in_array($user->studiengang_kz,$noalias)))
{
	echo $p->t('profil/alias').": <a class='Item' href='mailto:$user->alias@".DOMAIN."'>$user->alias@".DOMAIN."</a>";
}

echo '</P>';
if($user->homepage!='')
	echo "<P><b>".$p->t('profil/homepage')."</b><br><a href='$user->homepage' target='_blank'>$user->homepage</a></p>";
echo '<p>';
echo '
	</p>
    <br>
    </td>
    <td rowspan="2" valign="top">';
      			
echo '<P>';
$studiengang = new studiengang();
if ($type=='student')
{	
	$studiengang->load($user->studiengang_kz);
	
	echo "
	<b>".$p->t('profil/student')."</b><br><br>
	".$p->t('global/studiengang').": $studiengang->bezeichnung<br>
	".$p->t('global/semester').": $user->semester<br>
	".$p->t('global/verband').": $user->verband<br>
	".$p->t('global/gruppe').": $user->gruppe<br>
	".$p->t('profil/martrikelnummer').": $user->matrikelnr<br />";
   		
    if(!$ansicht)
    {
    	echo "
	   		<br />
	   		<A class='Item' href='../lehre/notenliste.php'>".$p->t('profil/leistungsbeurteilung')."</a><br />";
    }
}
		
if ($type=='mitarbeiter')
{
	echo "
		<P>
		<b>".$p->t('profil/mitarbeiter')."</b><br><br>
	".$p->t('profil/kurzzeichen').": $user->kurzbz<BR>";
		
						
	if($user->telefonklappe!='')
		echo $p->t('profil/telefonTw').": $vorwahl $user->telefonklappe<BR><BR>";
		
	if(!$ansicht)
	{
		echo '
			<A class="Item" href="zeitwunsch.php?uid='.$user->uid.'">'.$p->t('profil/zeitwuensche').'</A><BR>
			<A class="Item" href="lva_liste.php?uid='.$user->uid.'">'.$p->t('lvaliste/lehrveranstaltungen').'</A><br>';
			//<A class="Item" href="freebusy.php">'.$p->t('freebusy/titel').'</A>';
	}
}
		
if(!$ansicht)
{
	
	echo '<br><br><b>FH-Ausweis Status</b><br>';
	$bm = new betriebsmittel();
	if($bm->zutrittskarteAusgegeben($user->uid))
	{
		echo '<br>FH Ausweis wurde bereits ausgegeben';
	}
	else
	{
		$fs = new fotostatus();
		if($fs->getLastFotoStatus($user->person_id))
		{
			echo '<br>Foto '.$fs->fotostatus_kurzbz.' am '.$datum_obj->formatDatum($fs->datum, 'd.m.Y');
			switch($fs->fotostatus_kurzbz)
			{
				case 'abgewiesen':
					echo '<br>Laden Sie bitte ein gueltiges Foto hoch';
					break;
				case 'hochgeladen':
					echo '<br>Foto wurde noch nicht akzeptiert';
					break;
				case 'akzeptiert':
					if($bm->zutrittskartePrinted($user->uid))
					{
						echo '<br>FH Ausweis gedruckt am '.$datum_obj->formatDatum($bm->insertamum,'d.m.Y');
						$geliefertts = $datum_obj->mktime_fromtimestamp($bm->insertamum);
						$abholungsdatum = $datum_obj->jump_day($geliefertts, 1);	
						echo '<br>FH Ausweis abholbereit am Empfang ab '.date('d.m.Y',$abholungsdatum);	
					}
					else
						echo '<br>FH Ausweis wurde noch nicht gedruckt';
					break;
					
				default:
					echo '<br>Laden Sie bitte ein gültiges Foto hoch';
					break;
			}
		}
		else
		{
			echo '<br>Ihr Foto wurde noch nicht geprüft';
		}
	}
	//Funktionen
	$qry = "SELECT 
				*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
				 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
			FROM 
				public.tbl_benutzerfunktion 
				JOIN public.tbl_funktion USING(funktion_kurzbz) 
				JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
			WHERE 
				uid=".$db->db_add_param($uid)." AND 
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
			
    if ($oBetriebsmittelperson->getBetriebsmittelPerson($user->person_id))
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
    
	echo "</P>";
		
	echo '	
    	</td>
  	</tr>
  	<TR>
    	<TD colspan="2" valign="top">
     		<P><B>'.$p->t('mailverteiler/mailverteiler').'</B><BR><BR>
    	';
	//Mailverteiler
	if(!$ansicht)
		echo "<SMALL>".$p->t('profil/sieSindMitgliedInFolgendenVerteilern').":</SMALL>";
	else
		echo "<SMALL>".$p->t('profil/derUserIstInFolgendenVerteilern',array($user->uid)).":</SMALL>";
       
	echo '<table>';
	  		
  	for($i=0;$i<$nr_mg;$i++)
	{
		$row=$db->db_fetch_object($erg_mg,$i);
		echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($row->gruppe_kurzbz)).'@'.DOMAIN.'">'.strtolower($row->gruppe_kurzbz).'&nbsp;</TD>';
    	echo "<TD>&nbsp;$row->beschreibung</TD></TR>";
	}
	
	if (isset($user->matrikelnr))
	{
		echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($studiengang->kuerzel)).'_std@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).'_std&nbsp;</TD>';
    	echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel</TD></TR>";
		echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($studiengang->kuerzel)).trim($user->semester).'@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).$user->semester.'&nbsp;</TD>';
   		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel $user->semester</TD></TR>";
		echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($studiengang->kuerzel)).trim($user->semester).strtolower(trim($user->verband)).'@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).$user->semester.strtolower($user->verband).'&nbsp;</TD>';
   		echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel $user->semester$user->verband</TD></TR>";
   		if($user->gruppe!='')
   		{
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower(trim($studiengang->kuerzel)).trim($user->semester).strtolower(trim($user->verband)).trim($user->gruppe).'@'.DOMAIN.'">'.strtolower($studiengang->kuerzel).$user->semester.strtolower($user->verband).$user->gruppe.'&nbsp;</TD>';
   			echo "<TD>&nbsp;".$p->t('profil/alleStudentenVon')." $studiengang->kuerzel $user->semester$user->verband$user->gruppe</TD><TD></TD></TR>";
   		}
	}
	echo '</table>';
	
		$mail = MAIL_ADMIN;
		if(!isset($user->studiengang_kz) || $user->studiengang_kz=='')
		{
			$user->studiengang_kz = 0;
		}
		
		//Wenn eine Assistentin fuer diesen Studiengang eingetragen ist,
		//dann werden die aenderungswuesche an diese Adresse gesendet
		if($studiengang->email!='')
			$mail = $studiengang->email;
		else
			$mail = MAIL_ADMIN;
		
		if($user->studiengang_kz=='0')
			$mail = MAIL_GST;

		
			
		if(!$ansicht)
		{
			echo "
			<br><br>
			".$p->t('profil/solltenDatenNichtStimmen')." <a class='Item' href=\"mailto:$mail?subject=Datenkorrektur&body=Die%20Profildaten%20fuer%20User%20'$user->uid'%20sind%20nicht%20korrekt.%0D
			Hier die richtigen Daten:%0DNachname:%20$user->nachname%0DVorname:%20$user->vorname%0DGeburtsdatum:%20$user->gebdatum
			%0DGeburtsort:%20$user->gebort%0DTitelPre:%20$user->titelpre%0DTitelPost:%20$user->titelpost
			%0D%0D***%0DPlatz fuer weitere (nicht angefuehrte Daten)%0D***\">".$p->t('profil/zustaendigeAssistenz')."</a>";
		
		}
	echo '
       	</P>
    	</TD>
  	</TR>
  	';
	echo '
			</table>
			<BR>';

?>
</body>
</html>
