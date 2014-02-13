<?php
/* Copyright (C) 2009 fhcomplete.org
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
 *          Gerald Simane-Sequens <gerald.simane@technikum-wien.at>.
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/wochenplan.class.php');
require_once('../../../include/reservierung.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p=new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
	


$uid=get_uid();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
	<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<TITLE><?php echo $p->t('lvplan/lehrveranstaltungsplan');?> Technikum-Wien</TITLE>
	<script type="text/javascript">
		<!--
		function MM_jumpMenu(targ,selObj,restore)
		{ //v3.0
	  		eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
	  		if (restore)
	  			selObj.selectedIndex=0;
		}
		
        function toggle_checkboxes(obj)
        {
			var f = obj.form;
			var regExp = /reserve[1-7]_[1-9][1-6]?/;
			for (var i = 0; i < f.elements.length; i++)
			{
				var e = f.elements[i];
				if((e.name).match(regExp))
					e.checked = f.check_all.checked;
			}
		}
		-->
	</script>
	<LINK rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
	<script src="../../../include/js/flexcroll.js" type="text/javascript" ></script>
</HEAD>

<BODY id="inhalt">
<div class="flexcroll" style="outline: none;">
<h1><?php echo $p->t('lvplan/wochenplan');?></h1>
<table class="tabcontent">
<tr>
<td>
<a href="index.php"><?php echo $p->t('lvplan/hauptmenue');?></a><br>
<?php echo '<a href="../../../cms/content.php?content_id='.$p->t("dms_link/lvPlanFAQ").'" class="hilfe" target="_blank">'.$p->t("global/hilfe").'</a>'; ?>
</td>
</tr>
</table>

<?php
/****************************************************************************
 * Script: 			stpl_week.php
 * Descr:  			Das Script dient zum Navigieren im Stundenplan.
 *					Ein Lektor kann auch einen Saal reservieren
 * Verzweigungen: 	nach stpl_detail.php
 *					von index.php
 * Author: 			Christian Paminger
 * Erstellt: 		21.8.2001
 * Update: 			15.11.2004 von Christian Paminger
 *****************************************************************************/

//$type='ort';
//$ort_kurzbz='EDV6.08';
//$datum=1102260015;

// Deutsche Umgebung
//$loc_de=setlocale(LC_ALL, 'de_AT@euro', 'de_AT','de_DE@euro', 'de_DE');
//setlocale(LC_ALL, $loc_de);

// Variablen uebernehmen
if (isset($_GET['type']))
	$type=$_GET['type'];
if (isset($_POST['type']))
	$type=$_POST['type'];
if (isset($_GET['datum']))
	$datum=$_GET['datum'];
if (isset($_POST['datum']))
	$datum=$_POST['datum'];

if (isset($_GET['ort_kurzbz']))
	$ort_kurzbz=$_GET['ort_kurzbz'];
else if (isset($_POST['ort_kurzbz']))
	$ort_kurzbz=$_POST['ort_kurzbz'];
else
	$ort_kurzbz=null;

if (isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];

if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else if (isset($_POST['stg_kz']))
	$stg_kz=$_POST['stg_kz'];
else
	$stg_kz=null;

if (isset($_POST['sem']))
	$sem=$_POST['sem'];
else if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=null;

if (isset($_POST['ver']))
	$ver=$_POST['ver'];
else if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;

if (isset($_POST['grp']))
	$grp=$_POST['grp'];
else if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;

if (isset($_POST['gruppe_kurzbz']))
	$gruppe_kurzbz=$_POST['gruppe_kurzbz'];
else if (isset($_GET['gruppe_kurzbz']))
	$gruppe_kurzbz=$_GET['gruppe_kurzbz'];
else
	$gruppe_kurzbz=null;

if (isset($_POST['user_uid']))
	$user_uid=$_POST['user_uid'];
if (isset($_POST['reserve']))
	$reserve=$_POST['reserve'];
if (isset($_POST['beschreibung']))
	$beschreibung=$_POST['beschreibung'];
if (isset($_POST['titel']))
	$titel=$_POST['titel'];

//Parameter pruefen
if($stg_kz!='' && !is_numeric($stg_kz))
	die('Studiengang ist ungueltig');
if($sem!='' && !is_numeric($sem))
	die('Semester ist ungueltig');
if(strlen($ver)>2)
	die('Verband ist ungueltig');
if(strlen($grp)>2)
	die('Gruppe ist ungueltig');
if(isset($datum) && !is_numeric($datum))
	die('Datum ist ungueltig');
if(!check_ort($ort_kurzbz))
	die('Ort ist ungueltig');

$berechtigung=new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if ($berechtigung->isBerechtigt('lehre/reservierung:begrenzt', null, 'sui'))
	$raumres=true;
else
	$raumres=false;
unset($berechtigung);
// Authentifizierung
if (check_student($uid))
	$user='student';
elseif (check_lektor($uid))
	$user='lektor';
else
{
	die($p->t('global/userNichtGefunden'));
	//GastAccountHack
	//$user='student';
}

    // User bestimmen
if (!isset($type))
	$type=$user;
if (!isset($pers_uid))
	$pers_uid=$uid;

if (isset($_POST['reserve']))
	$reserve=$_POST['reserve'];
else if (isset($_GET['reserve']))
	$reserve=$_GET['reserve'];	
// Reservieren
if (isset($reserve) && $raumres)
{
	$ort_obj = new ort();
	if(!$ort_obj->load($ort_kurzbz))
		die($p->t('lvplan/raumExistiertNicht'));
	
	if(!$erg_std=$db->db_query("SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
	{
		die($db->db_last_error());
	}

	$num_rows_std=$db->db_num_rows($erg_std);
	$count=0;
	for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
	{
		for ($j=0;$j<$num_rows_std;$j++)
		{
			$stunde=$db->db_result($erg_std,$j,'"stunde"');
			$var='reserve'.$t.'_'.$stunde;
				
			if (isset($_REQUEST[$var]))
			{
				$datum_res=$_REQUEST[$var];

				$reservierung = new reservierung();
				
				if(!$reservierung->isReserviert($ort_kurzbz, $datum_res, $stunde))
				{
					if (empty($_REQUEST['titel']) && empty($_REQUEST['beschreibung']))
						echo "<br>".$p->t('lvplan/titelUndBeschreibungFehlt')."! <br>";
					else if (empty($_REQUEST['titel']) )
						echo "<br>".$p->t('lvplan/titelFehlt')."! <br>";
					else if ( empty($_REQUEST['beschreibung']))
						echo "<br>".$p->t('lvplan/beschreibungFehlt')."! <br>";
					else
					{			 
	  					$reservierung = new reservierung();
		  				$reservierung->datum = $datum_res;
				  		$reservierung->ort_kurzbz = $ort_kurzbz;
					  	$reservierung->stunde = $stunde;
	  					$reservierung->beschreibung = $_REQUEST['beschreibung'];
		  				$reservierung->titel = $_REQUEST['titel'];
		  				$reservierung->insertamum=date('Y-m-d H:i:s');
		  				$reservierung->insertvon=$uid;
		  				
		  				if(isset($_REQUEST['studiengang_kz']))
		  				{
		  					$reservierung->studiengang_kz = $_REQUEST['studiengang_kz'];
		  					$reservierung->semester = $_REQUEST['semester'];
		  					$reservierung->verband = $_REQUEST['verband'];
		  					$reservierung->gruppe = $_REQUEST['gruppe'];
		  					$reservierung->gruppe_kurzbz = $_REQUEST['gruppe_kurzbz'];
		  					$reservierung->uid = $_REQUEST['user_uid'];
		  				}
		  				else
		  				{
			  				$reservierung->studiengang_kz='0';
			  				$reservierung->uid = $uid;
		  				}
		  				
	  					if(!$reservierung->save(true))
		  					echo $reservierung->errormsg;
	            		else
	         				$count++;
            		}    
				}
				else
				{
					echo "<br>$ort_kurzbz ".$p->t('lvplan/bereitsReserviert').": $datum_res - Stunde $stunde <br>";
				}	
			}
		}
	}
}

// Stundenplan erstellen
$stdplan=new wochenplan($type);
if (!isset($datum))
	$datum=time();

// Benutzergruppe
$stdplan->user=$user;
// aktueller Benutzer
$stdplan->user_uid=$uid;

// Zusaetzliche Daten laden
if (! $stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$gruppe_kurzbz) )
{
	die($stdplan->errormsg);
}

//echo 'Datum:'.$datum.'<BR>';
// Stundenplan einer Woche laden
if (! $stdplan->load_week($datum))
{
	die($stdplan->errormsg);
}

// Kopfbereich drucken
if (! $stdplan->draw_header())
{
	die($stdplan->errormsg);
}

// Stundenplan der Woche drucken
$stdplan->draw_week($raumres,$uid);

if (isset($count))
	echo "Es wurde".($count!=1?'n':'')." $count Stunde".($count!=1?'n':'')." reserviert!<BR>";
?>

<P><br><?php echo $p->t('lvplan/fehlerUndFeedback');?> <A class="Item" href="mailto:<?php echo MAIL_LVPLAN?>"><?php echo $p->t('lvplan/lvKoordinationsstelle');?></A>.</P>
</div>
</BODY>
</HTML>
