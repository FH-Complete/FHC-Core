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
/**
 * Das Script dient zum Navigieren im Stundenplan und zur Reservierung von Raeumen
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/wochenplan.class.php');
require_once('../../../include/reservierung.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/stundenplan.class.php');

$sprache = getSprache();
$p=new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$uid=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

// Variablen uebernehmen
if (isset($_GET['type']))
	$type=$_GET['type'];
if (isset($_POST['type']))
	$type=$_POST['type'];
if (isset($_GET['datum']))
	$datum=$_GET['datum'];
if (isset($_POST['datum']))
	$datum=$_POST['datum'];

// Uses urlencode to avoid XSS issues
if (isset($_GET['ort_kurzbz']))
	$ort_kurzbz = urlencode($_GET['ort_kurzbz']);
else if (isset($_POST['ort_kurzbz']))
	$ort_kurzbz = urlencode($_POST['ort_kurzbz']);
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

if (isset($_GET['lva']))
	$lva=$_GET['lva'];
else if (isset($_POST['lva']))
	$lva=$_POST['lva'];
else
	$lva=null;

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

?><!DOCTYPE html>
<HTML>
<HEAD>
	<META charset="UTF-8">
	<TITLE><?php echo $p->t('lvplan/lehrveranstaltungsplan').' '.CAMPUS_NAME;?></TITLE>
	<?php include('../../../include/meta/jquery.php');?>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript">
		<!--
		function MM_jumpMenu(targ,selObj,restore)
		{
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

		$(document).ready(function() {
			$("#user_uid").autocomplete({
				source: "lvplan_autocomplete.php?autocomplete=mitarbeiter",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(var i in ui.content)
					{
						ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
						ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					}
				},
				select: function(event, ui)
				{
					var uid = ui.item.uid;

					if ($("#lecturer_"+uid).length <= 0)
					{
						$("#user_uid").after("<input type='hidden' name='lecturer_uids[]' value='" + uid + "' id='lecturer_" + uid + "'>");
						$("#firstinputrow").after("<tr id='lecturerrow_"+uid+"'>" +
							"<td colspan='9'></td>" +
							"<td class='lecturercell'>" +
							"<div class='lecturercellname'>"+ui.item.value+"</div>" +
							"<div class='lecturercelldelete'>" +
							"<img src='../../../skin/images/delete_x.png' id='deleteLecturer_"+uid+"' width='11px' height='11px' title='<?php echo $p->t('global/löschen');?>'>" +
							"</div>" +
							"</td></tr>");
						$("#deleteLecturer_"+uid).click(
							function() {
								$("#lecturer_"+uid).remove();
								$("#lecturerrow_"+uid).remove();
							}
						);
					}
					$("#user_uid").val("");

					return false;
				}
			});
			$("select[name='studiengang_kz']").change(function() {
				var studiengang_kz = $("select[name='studiengang_kz']").val();
				$.ajax({
					url: "lvplan_autocomplete.php",
					data: { 'autocomplete':'getSemester',
						'stg_kz':studiengang_kz
					},
					type: "POST",
					dataType: "json",
					success: function(data)
					{
						$("select[name='semester']").empty();
						$("select[name='semester']").append('<option value="">*</option>');
						$.each(data, function(i, data){
							$("select[name='semester']").append('<option value="'+data+'">'+data+'</option>');
						});
					},
					error: function(data)
					{
						alert("Fehler beim Laden der Daten");
					}
				});
			})

			$("select[name='semester']").change(function() {
				var studiengang_kz = $("select[name='studiengang_kz']").val();
				var semester = $("select[name='semester']").val();
				$.ajax({
					url: "lvplan_autocomplete.php",
					data: { 'autocomplete':'getVerband',
						'stg_kz':studiengang_kz,
						'sem':semester
					},
					type: "POST",
					dataType: "json",
					success: function(data)
					{
						$("select[name='verband']").empty();
						$("select[name='verband']").append('<option value="">*</option>');
						$.each(data, function(i, data){
							$("select[name='verband']").append('<option value="'+data+'">'+data+'</option>');
						});
					},
					error: function(data)
					{
						alert("Fehler beim Laden der Daten");
					}
				});
			})

			$("select[name='verband']").change(function() {
				var studiengang_kz = $("select[name='studiengang_kz']").val();
				var semester = $("select[name='semester']").val();
				var verband = $("select[name='verband']").val();
				$.ajax({
					url: "lvplan_autocomplete.php",
					data: { 'autocomplete':'getGruppe',
						'stg_kz':studiengang_kz,
						'sem':semester,
						'ver':verband
					},
					type: "POST",
					dataType: "json",
					success: function(data)
					{
						$("select[name='gruppe']").empty();
						$("select[name='gruppe']").append('<option value="">*</option>');
						$.each(data, function(i, data){
							$("select[name='gruppe']").append('<option value="'+data+'">'+data+'</option>');
						});
					},
					error: function(data)
					{
						alert("Fehler beim Laden der Daten");
					}
				});
			})
		});
		-->
	</script>
	<?php
	// ADDONS laden
	$addon_obj = new addon();
	$addon_obj->loadAddons();
	foreach($addon_obj->result as $addon)
	{
		if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
			echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php"></script>';
	}

	// Wenn Seite fertig geladen ist Addons aufrufen
	echo '
	<script>
	$( document ).ready(function()
	{
		if(typeof addon  !== \'undefined\')
		{
			for(i in addon)
			{
				addon[i].init("cis/private/lvplan/stpl_week.php", {ort_kurzbz:\''.$ort_kurzbz.'\'});
			}
		}
	});
	</script>
	';
	?>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY id="inhalt">
<h1><?php echo $p->t('lvplan/wochenplan');?></h1>
<table class="tabcontent">
<tr>
<td>
<a href="index.php"><?php echo $p->t('lvplan/hauptmenue');?></a><br>
<?php if ($p->t("dms_link/lvPlanFAQ")!='') echo '<a href="../../../cms/content.php?content_id='.$p->t("dms_link/lvPlanFAQ").'" class="hilfe" target="_blank">'.$p->t("global/hilfe").'</a>'; ?>
</td>
</tr>
</table>

<?php
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

if (isset($_GET['reservtodelete']))
	$reservtodelete=$_GET['reservtodelete'];

// Loeschen von Reservierungen
if (isset($reservtodelete))
{
	if (is_array($reservtodelete))
	{
		foreach ($reservtodelete as $delete_id)
		{
			if (!is_numeric($delete_id))
				die('ungueltige ID');
		}

		$reservierung = new reservierung();
		$reservdelcount = 0;
		$reservberechtigt = $rechte->isBerechtigt('lehre/reservierung:begrenzt', null, 'suid');

		foreach ($reservtodelete as $delete_id)
		{
			if ($reservierung->load($delete_id))
			{
				if ($reservberechtigt && ($reservierung->insertvon==$uid || $reservierung->uid==$uid))
				{
					if($reservierung->delete($delete_id))
						$reservdelcount++;
					else
						echo $reservierung->errormsg;
				}
				else
				{
					echo '<b>'.$p->t('global/keineBerechtigung').'</b><br>';
				}
			}
			else
				echo '<b>'.$p->t('global/fehleraufgetreten').'!</b><br>';
		}
	}
	else
		die('<b>ungueltige IDs</b><br>');
}

// Reservieren
elseif (isset($reserve) && $raumres)
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

				// Pruefen ob der Raum im Stundenplan und Stundenplandev frei ist
				$stpl = new stundenplan('stundenplan');
				$stpldev = new stundenplan('stundenplandev');

				if((!$stpl->isBelegt($ort_kurzbz, $datum_res, $stunde)
				&& !$stpldev->isBelegt($ort_kurzbz, $datum_res, $stunde))  || $rechte->isBerechtigt('lehre/reservierungAdvanced'))
				{
					$reservierung = new reservierung();

					if(!$reservierung->isReserviert($ort_kurzbz, $datum_res, $stunde) || $rechte->isBerechtigt('lehre/reservierungAdvanced'))
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

							if(isset($_REQUEST['lecturer_uids']) && !empty($_REQUEST['lecturer_uids'] && isset($_REQUEST['studiengang_kz'])))
							{
								$lecturer_uids = $_REQUEST['lecturer_uids'];
								foreach ($lecturer_uids as $lecturer_uid)
								{
									$reservierung->studiengang_kz = $_REQUEST['studiengang_kz'];
									$reservierung->semester = $_REQUEST['semester'];
									$reservierung->verband = $_REQUEST['verband'];
									$reservierung->gruppe = $_REQUEST['gruppe'];
									$reservierung->gruppe_kurzbz = $_REQUEST['gruppe_kurzbz'];
									$reservierung->uid = $lecturer_uid;

									if(!$reservierung->save(true))
										echo $reservierung->errormsg;
									else
										$count++;
								}
							}
							else
							{
								$reservierung->studiengang_kz='0';
								$reservierung->uid = $uid;
								if(!$reservierung->save(true))
									echo $reservierung->errormsg;
								else
									$count++;
							}
						}
					}
					else
					{
						echo "<br>$ort_kurzbz ".$p->t('lvplan/bereitsReserviert').": $datum_res - Stunde $stunde <br>";
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
if (! $stdplan->load_data($type,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$gruppe_kurzbz,null,$lva) )
{
	die(htmlentities($stdplan->errormsg));
}

// Stundenplan einer Woche laden
if (! $stdplan->load_week($datum))
{
	die(htmlentities($stdplan->errormsg));
}

// Kopfbereich drucken
if (! $stdplan->draw_header())
{
	die(htmlentities($stdplan->errormsg));
}

// Stundenplan der Woche drucken
if($ort_kurzbz == 'all')
	$stdplan->draw_week ($raumres, $uid, false);
else
	$stdplan->draw_week($raumres,$uid);

if (isset($count))
	echo "Es wurde".($count!=1?'n':'')." $count Stunde".($count!=1?'n':'')." reserviert!<BR>";
if (isset($reservdelcount))
	echo "Es wurde".($reservdelcount!=1?'n':'')." $reservdelcount Stunde".($reservdelcount!=1?'n':'')." gel&ouml;scht!<BR>";
?>

<P><br><?php echo $p->t('lvplan/fehlerUndFeedback');?> <A class="Item" href="mailto:<?php echo MAIL_LVPLAN?>"><?php echo $p->t('lvplan/lvKoordinationsstelle');?></A>.</P>
</BODY>
</HTML>
