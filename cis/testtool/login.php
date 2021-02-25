<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 *          Cristina Hainberger <hainberg@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/pruefling.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/studienplan.class.php');
require_once('../../include/ablauf.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/sprache.class.php');
require_once '../../include/phrasen.class.php';
require_once '../../include/datum.class.php';

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

// Start session
session_start();

// Logout (triggered by logout button in menu.php)
if (isset($_GET['logout']) && $_GET['logout'] == true)
{
	// Unset global vars
	unset($_GET['logout']);
	unset($_GET['sprache_user']);
	$_POST = [];
	$_SESSION = [];

	// Destroy session
	session_destroy();

	echo '
		<script language="Javascript">
			location = location.pathname;       // clean the login.php-url from querystring
			parent.menu.location = parent.menu.location.pathname;   // clean the menu.php-url from querystring
			parent.topbar.location = parent.topbar.location.pathname;   // clean the topbar.php-url from querystring
		</script>
	';
}

$gebdatum='';
$date = new datum();

$reload_menu=false;
$alertmsg = '';

$sg_var = new studiengang();

if (isset($_POST['gebdatum']) && $_POST['gebdatum']!='')
{
	$gebdatum = $date->formatDatum($_POST['gebdatum'],'Y-m-d');
}
else
	$gebdatum='';

if (isset($_REQUEST['prestudent']))
{
	$ps = new prestudent($_REQUEST['prestudent']);

	$login_ok = false;
	if (defined('TESTTOOL_LOGIN_BEWERBUNGSTOOL') && TESTTOOL_LOGIN_BEWERBUNGSTOOL && isset($_GET['confirmation']))
	{
		if (isset($_SESSION['bewerbung/personId']) && $ps->person_id == $_SESSION['bewerbung/personId'])
		{
			$login_ok = true;
		}
		else
		{
			$alertmsg .= '<div class="alert alert-danger">Login ist nicht korrekt.
				Bitte schließen Sie ihren Browser und versuchen es erneut
			</div>';
		}
	}
	elseif(!defined('TESTTOOL_LOGIN_BEWERBUNGSTOOL') || TESTTOOL_LOGIN_BEWERBUNGSTOOL == false)
	{
		//Geburtsdatum Pruefen
		if (isset($gebdatum) && $gebdatum == $ps->gebdatum)
		{
			$login_ok = true;
		}
		else
		{
			$alertmsg .= '<div class="alert alert-danger">'.$p->t('testtool/geburtsdatumStimmtNichtUeberein').'</div>';
		}
	}

	if ($login_ok)
	{
		$reihungstest_id='';
		//Freischaltung fuer zugeteilten Reihungstest pruefen
		$rt = new reihungstest();

		// Wenns der Dummy ist dann extra laden
		// An der FHTW gibt es 3 Testuser für den Camus International
		$prestudent_id_dummy_student = (defined('PRESTUDENT_ID_DUMMY_STUDENT')?PRESTUDENT_ID_DUMMY_STUDENT:'');
		if ($prestudent_id_dummy_student==$ps->prestudent_id ||
			(CAMPUS_NAME == 'FH Technikum Wien' && $ps->prestudent_id == 30891) ||
			(CAMPUS_NAME == 'FH Technikum Wien' && $ps->prestudent_id == 30890) ||
			(CAMPUS_NAME == 'FH Technikum Wien' && $ps->prestudent_id == 30889))
		{
			$rt->getReihungstestPerson($ps->person_id);
			if (isset($rt->result[0]))
				$reihungstest_id = $rt->result[0]->reihungstest_id;
			else
			{
				$alertmsg .= '<div class="alert alert-danger">'.$p->t('testtool/reihungstestKannNichtGeladenWerden').'</div>';
			}
		}
		else
		{
			if ($rt->getReihungstestPersonDatum($ps->prestudent_id, date('Y-m-d')))
			{
				// TODO Was ist wenn da mehrere Zurueckkommen?!
				if (isset($rt->result[0]))
					$reihungstest_id = $rt->result[0]->reihungstest_id;
				else
				{
					$alertmsg .= '<div class="alert alert-danger">'.$p->t('testtool/reihungstestKannNichtGeladenWerden').'</div>';
				}
			}
			else
			{
				echo 'Failed:'.$rt->errormsg;
			}
		}
		if ($reihungstest_id != '' && $rt->load($reihungstest_id))
		{
			if ($rt->freigeschaltet)
			{
				// regenerate Session ID after Login
				session_regenerate_id();

				$pruefling = new pruefling();
				if ($pruefling->getPruefling($ps->prestudent_id))
				{
					$studiengang = $pruefling->studiengang_kz;
					$semester = $pruefling->semester;
				}
				else
				{
					$studiengang = $ps->studiengang_kz;
					$ps->getLastStatus($ps->prestudent_id);
					$semester = $ps->ausbildungssemester;
				}
				if ($semester=='')
					$semester=1;

				$_SESSION['prestudent_id']=$_REQUEST['prestudent'];
				$_SESSION['studiengang_kz']=$studiengang;
				$_SESSION['nachname']=$ps->nachname;
				$_SESSION['vorname']=$ps->vorname;
				$_SESSION['gebdatum']=$ps->gebdatum;
				$stg_obj = new studiengang($studiengang);

				$_SESSION['semester']=$semester;
				$_SESSION['reihungstestID'] = $reihungstest_id;
				$stg_obj->getStudiengangTyp($stg_obj->typ);

				// STG und Studienplan mit der höchsten Prio ermitteln
				$firstPrio_studienplan_id = '';
				$firstPrio_studiengang_kz = '';

				//  * wenn STG des eingeloggten Prestudenten vom Typ Bachelor ist, dann höchste Prio aller
				//  Bachelor-STG ermitteln, an denen die Person noch interessiert ist
				//  Wenn STG vom Typ Master, dann wird als firstPrio der STPL bzw. der STG des MasterSTG gesetzt.
				if ($stg_obj->typ == 'b')
				{
					$ps->getActualInteressenten($_REQUEST['prestudent'], true);
				}
				elseif ($stg_obj->typ == 'm')
				{
					$ps->getActualInteressenten($_REQUEST['prestudent'], false, 'm', $studiengang);
				}

				foreach($ps->result as $row)
				{
					if (isset($row->studiengang_kz))
					{
						$firstPrio_studienplan_id = $row->studienplan_id;
						break;
					}
				}
				foreach($ps->result as $row)
				{
					if (isset($row->studiengang_kz))
					{
						$firstPrio_studiengang_kz = $row->studiengang_kz;
						break;
					}
				}
				// Sprachvorgaben zu STG mit höchster Prio ermitteln

				// * 1. Sprache über Ablauf Vorgaben ermitteln
				$ablauf = new Ablauf();
				$ablauf->getAblaufGebiete($firstPrio_studiengang_kz, $firstPrio_studienplan_id);
				$rt_sprache = '';

				if (empty($ablauf->result[0]))
				{
                    $ablauf->getAblaufGebiete($firstPrio_studiengang_kz);
                }

				if (!empty($ablauf->result[0]))
				{
					$rt_sprache = $ablauf->result[0]->sprache;
				}

				// * 2. falls keine Sprache vorhanden -> Sprache über Studienplan ermitteln
				if (empty($rt_sprache))
				{
					$stpl = new Studienplan();
					$stpl->loadStudienplan($firstPrio_studienplan_id);
					$rt_sprache = $stpl->sprache;
				}

				// * 3. falls keine Sprache vorhanden -> Sprache über Studiengang ermitteln
				if (empty($rt_sprache))
				{
					$stg = new Studiengang($firstPrio_studiengang_kz);
					$rt_sprache = $stg->sprache;
				}

				// * 4. Sprache setzen. Falls keine Sprache vorhanden -> DEFAULT language verwenden
				if (empty($rt_sprache))
				{
					$_SESSION['sprache_user'] = DEFAULT_LANGUAGE;
				}
				else
				{
					$_SESSION['sprache_user'] = $rt_sprache;
				}
			}
			else
			{
				$alertmsg .= '<div class="alert alert-danger">'.$p->t('testtool/reihungstestNichtFreigeschalten').'</div>';
			}
		}
		else
		{
			$alertmsg .= '<div class="alert alert-danger">'.$p->t('testtool/reihungstestKannNichtGeladenWerden').'</div>';
		}
	}
}

// Set language of user.
// NOTE: don't move the code in order to check first the right studies' reihungstest language
// (in case it was overruled by other STG with higher priority)

// Start with default language on first login (before any prestudent has been selected)
$sprache_user = DEFAULT_LANGUAGE;
if (isset($_SESSION['sprache_user']) && !empty($_SESSION['sprache_user']))
{
	// If session var already exists, overwrite language var
	// (session var changes e.g. when user selects other language with language-select-menu)
	$sprache_user = $_SESSION['sprache_user'];
}
elseif (isset($_SESSION['prestudent_id']))
{
	// If session var does not exist but prestudent is known, set the session var
	$_SESSION['sprache_user'] = DEFAULT_LANGUAGE;
}

// If language is changed by language select menu, reset language variables
if (isset($_GET['sprache_user']) && !empty($_GET['sprache_user']))
{
	$sprache_user = $_GET['sprache_user'];
	$_SESSION['sprache_user'] = $_GET['sprache_user'];
}

// NOTE: leave phrasen here, as the final users language is not defined until here
$p = new phrasen($sprache_user);

if (isset($_SESSION['prestudent_id']))
{
	$prestudent_id=$_SESSION['prestudent_id'];
}
else
{
	//$prestudent_id=null;
	$ps=new prestudent();
	$datum=date('Y-m-d');
	// An der FHTW wird ein Bewerber nur einmal ausgegeben (1. Prio) falls es mehrere Bewerbungen gibt
	/*if (CAMPUS_NAME == 'FH Technikum Wien')
	{
		$ps->getFirstPrioPrestudentRT($datum);
	}
	else*/
	{
		$ps->getPrestudentRT($datum);
	}
}


if (isset($_SESSION['prestudent_id']) && !isset($_SESSION['pruefling_id']))
{
	$pruefling = new pruefling();

		//wenn kein Prüfling geladen werden kann
	if (!$pruefling->getPruefling($_SESSION['prestudent_id']))
		$pruefling->new = true;
		else
			$pruefling->new = false;

		$pruefling->studiengang_kz = $_SESSION['studiengang_kz'];
		$pruefling->semester = $_SESSION['semester'];

		$pruefling->idnachweis = '';
		$pruefling->registriert = date('Y-m-d H:i:s');
		$pruefling->prestudent_id = $_SESSION['prestudent_id'];
		if ($pruefling->save())
		{
			$_SESSION['pruefling_id']=$pruefling->pruefling_id;
			$reload_menu=true;
		}
}

if (isset($_POST['save']) && isset($_SESSION['prestudent_id']))
{
	$pruefling = new pruefling();
	if ($_POST['pruefling_id']!='')
		if (!$pruefling->load($_POST['pruefling_id']))
			die('Pruefling wurde nicht gefunden');
		else
			$pruefling->new=false;
	else
		$pruefling->new=true;

	$pruefling->studiengang_kz = $_SESSION['studiengang_kz'];
	$pruefling->idnachweis = isset($_POST['idnachweis'])?$_POST['idnachweis']:'';
	$pruefling->registriert = date('Y-m-d H:i:s');
	$pruefling->prestudent_id = $_SESSION['prestudent_id'];
	$pruefling->semester = $_POST['semester'];
	if ($pruefling->save())
	{
		$_SESSION['pruefling_id']=$pruefling->pruefling_id;
		$_SESSION['semester']=$pruefling->semester;
		$reload_menu=true;
	}
}
?><!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" type="text/css"/>
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../vendor/components/jqueryui/themes/base/jquery-ui.min.css" type="text/css"/>
	<script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/ui/i18n/datepicker-de.js"></script>
	<script type="text/javascript" src="../../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript">

	$(document).ready(function()
	{
		$.datepicker.setDefaults( $.datepicker.regional[ "" ] );
		<?php //Wenn Deutsch ausgewaehlt, dann Datepicker auch in Deutsch
		if ($sprache_user=="German")
			echo '$.datepicker.setDefaults( $.datepicker.regional[ "de" ] );
			$( "#datepicker" ).datepicker(
				{
					changeMonth: true,
					changeYear: true,
					defaultDate: "-6570",
					maxDate: -5110,
					yearRange: "-60:+00",
				}
				);';
		else
			echo '$( "#datepicker" ).datepicker({
				dateFormat: "dd.mm.yy",
				changeMonth: true,
				changeYear: true,
				defaultDate: "-6570",
				maxDate: -5110,
				yearRange: "-60:+00",
				});';
		?>

		// If Browser is any other than Mozilla Firefox and the test includes any MathML,
		// show message to use Mozilla Firefox
		var ua = navigator.userAgent;
		if ((ua.indexOf("Firefox") > -1) == false)
		{
			$("#alertmsgdiv").html("<div class='alert alert-danger'>BITTE VERWENDEN SIE DEN MOZILLA FIREFOX BROWSER!<br>(Manche Prüfungsfragen werden sonst nicht korrekt dargestellt.<br><br>PLEASE USE MOZILLA FIREFOX BROWSER!<br>(Otherwise some exam items will not be displayed correctly</div>");
			//alert('BITTE VERWENDEN SIE DEN MOZILLA FIREFOX BROWSER!\n(Manche Prüfungsfragen werden sonst nicht korrekt dargestellt.\n\nPLEASE USE MOZILLA FIREFOX BROWSER!\n(Ohterwise some exam items will not be displayed correctly.)');
		}
	});
	</script>
<?php
	if ($reload_menu)
		echo '<script language="Javascript">parent.menu.location.reload();</script>';
?>
</head>

<body scroll="no">
	<div class="row">

<?php

//REIHUNGSTEST STARTSEITE (nach Login)
if (isset($prestudent_id))
{
	$prestudent = new prestudent($prestudent_id);
	$stg_obj = new studiengang($prestudent->studiengang_kz);
	$pruefling = new pruefling();
	$typ = new studiengang($prestudent->studiengang_kz);
	$typ->getStudiengangTyp($stg_obj->typ);

	// STG mit der höchsten Prio ermitteln
	$ps = new Prestudent();

	//  * prinzipiell STG der session übernehmem
	$firstPrio_studiengang_kz = $prestudent->studiengang_kz;

	//  * wenn STG des eingeloggten Prestudenten vom Typ Bachelor ist, dann höchste Prio aller
	//  Bachelor-STG ermitteln, an denen die Person noch interessiert ist
	if ($typ->typ == 'b')
	{
		$ps->getActualInteressenten($prestudent_id, true, 'b');
		foreach($ps->result as $row_prio)
		{
			if (isset($row_prio->studiengang_kz))
			{
				$firstPrio_studiengang_kz = $row_prio->studiengang_kz;
                $firstPrio_studienplan_id = $row_prio->studienplan_id;
				break;
			}
		}
	}

	// Sprachwahl zu STG mit höchster Prio ermitteln
	$ablauf = new Ablauf();
	$sprachwahl = false;

    $ablauf->getAblaufGebiete($firstPrio_studiengang_kz, $firstPrio_studienplan_id);

    if (empty($ablauf->result[0]))
    {
        $ablauf->getAblaufGebiete($firstPrio_studiengang_kz);
    }

    if (isset($ablauf->result[0])
        && is_bool($ablauf->result[0]->sprachwahl))
	{
	   $sprachwahl = $ablauf->result[0]->sprachwahl;
	}

	// If language can be switched, display language select menu on the top
	if ($sprachwahl)
	{
		$_SESSION['sprache_auswahl'] = true;
	?>
		<script type="text/javascript">
			parent.topbar.location.reload();
		</script>
	<?php
	}
	//Prestudent Informationen
	echo '<div class="col-xs-10 col-sm-9 col-lg-6">';
	echo '
		<h1 style="margin-top: -20px;">'. $p->t('testtool/begruessungstext'). '</h1><br/>
		<p>'. $p->t('testtool/anmeldedaten'). '</p><br/>
	';

	echo '
	  <table class="table table-bordered">
			<tr>
				<td style="width: 50%;"><strong>'.$p->t('zeitaufzeichnung/id').'</strong></td>
				<td>'.$_SESSION['prestudent_id'].'</td>
			</tr>
			<tr>
				<td><strong>'.$p->t('global/name').'</strong></td>
				<td>'.$_SESSION['vorname'].' '.$_SESSION['nachname'].'</td>
			</tr>
			<tr>
				<td><strong>'.$p->t('global/geburtsdatum').'</strong></td>
				<td>'.$date->formatDatum($_SESSION["gebdatum"],"d.m.Y").'</td>
			</tr>
	  </table>
	';
	echo '<br>';
	echo '
		 <p>'. $p->t('testtool/fuerFolgendeStgAngemeldet'). '</p><br>

		 <table class="table table-bordered">
			<thead>
				<tr>
					<th style="width: 50%;">'. $p->t('global/studiengang'). '</th>
					<th>Status</th>
				 </tr>
			</thead>
			<tbody>
		 ';

	//  * wenn Prestudent an 1 - n Bachelor-Studiengängen interessiert ist, dann STG anführen
	if ($typ->typ == 'b')
	{
		$ps_arr = new Prestudent();
		$ps_arr->getActualInteressenten($prestudent_id, false, 'b');

		if (count($ps_arr->result) > 0)
		{
			// Jeweils letzten Status ermitteln (ob Interessent oder Abgewiesener)
			foreach ($ps_arr->result as $ps_obj)
			{
				$ps_tmp = new Prestudent();
				$ps_tmp->getLastStatus($ps_obj->prestudent_id);

				$ps_obj->lastStatus = $ps_tmp->status_kurzbz; // letzten Status dem result array hinzufügen
				$ps_obj->status_mehrsprachig = $ps_tmp->status_mehrsprachig;
			}

			// Falls Status 'Abgewiesene' vorhanden, nach hinten reihen
			usort($ps_arr->result, function($a, $b){
				return strcmp($b->lastStatus, $a->lastStatus); // Order by DESC
			});
			foreach ($ps_arr->result as $ps_obj)
			{
				echo '<tr>';
				$stg = new Studiengang($ps_obj->studiengang_kz);

				if ($ps_obj->lastStatus == "Interessent"
					|| $ps_obj->lastStatus == "Bewerber"
					|| $ps_obj->lastStatus == "Wartender"
					|| $ps_obj->lastStatus == "Aufgenommener")
				{
					echo '<td style="width: 50%;">'. $ps_obj->typ_bz .' '. ($sprache_user == 'English' ? $stg->english : $stg->bezeichnung). ' ('.$ps_obj->orgform_bezeichnung[$sprache_user].')</td>';
					if ($ps_obj->ausbildungssemester == '1')
					{
						echo '<td>'. $p->t('testtool/regulaererEinstieg'). ' (1. Semester)</td>';
					}
					elseif ($ps_obj->ausbildungssemester == '3')
					{
						echo '<td>'. $p->t('testtool/quereinstieg'). ' (3. Semester)</td>';
					}
				}
				// wenn letzter Status \'Abgewiesener\' ist, dann als solchen kennzeichnen
				elseif ($ps_obj->lastStatus == "Abgewiesener")
				{
					echo '
						<td class="text-muted">'. $ps_obj->typ_bz .' '. ($sprache_user == 'English' ? $stg->english : $stg->bezeichnung). '</td>
						<td class="text-muted">'. $ps_obj->status_mehrsprachig[$sprache_user]. '</td>
					';
				}
				echo '</tr>';
			}
		}
	}
	//  * wenn Prestudent an einem Master-Studiengang interessiert ist, dann nur den einen STG anführen
	else
	{
		// Letzten Status für des Prestudenten einholen
		$ps_master = new Prestudent();
		$ps_master->getLastStatus($prestudent_id);
		echo '<td>'. $typ->bezeichnung.' '.($sprache_user=='English'?$stg_obj->english:$stg_obj->bezeichnung).'</td>';
		echo '<td>'. $ps_master->status_mehrsprachig[$sprache_user]. '</td>';
	}

	echo '
		</tbody>
	 </table>
	';

	echo '<br>';

	if ($pruefling->getPruefling($prestudent_id))
	{
		echo '<FORM accept-charset="UTF-8"   action="'. $_SERVER['PHP_SELF'].'"  method="post" enctype="multipart/form-data">';
		echo '<input type="hidden" name="pruefling_id" value="'.$pruefling->pruefling_id.'">';
		echo '<table>';
		//echo '<tr><td>'.$p->t('global/semester').':</td><td><input type="text" name="semester" size="1" maxlength="1" value="'.$pruefling->semester.'">&nbsp;<input type="submit" name="save" value="Semester ändern"></td></tr>';
		//echo '<tr><td>ID Nachweis:</td><td><INPUT type="text" maxsize="50" name="idnachweis" value="'.$pruefling->idnachweis.'"></td></tr>';
		//echo '<tr><td></td><td><input type="submit" name="save" value="Semester ändern"></td>';
		echo '</table>';
		echo '</FORM>';
		echo '<br><br>';
		echo '
			<div class="well well-lg text-center">
				<strong>'.$p->t('testtool/klickenSieAufEinTeilgebiet').'</strong>
			</div>
	   ';
		if ($pruefling->pruefling_id!='')
		{
			$_SESSION['pruefling_id']=$pruefling->pruefling_id;
		}
	}
	else
	{
		echo '<span class="error">'.$p->t('testtool/keinPrueflingseintragVorhanden').'</span>';
	}
	echo '	</div><!--/.col-->';
}
else // LOGIN Site (vor Login)
{
	if (defined('TESTTOOL_LOGIN_BEWERBUNGSTOOL') && TESTTOOL_LOGIN_BEWERBUNGSTOOL)
	{
		echo '<div class="col-xs-11">';
		echo '<div id="alertmsgdiv">'.$alertmsg.'</div>';
		echo $p->t('testtool/einfuehrungsText');

		if (isset($_SESSION['bewerbung/personId']))
		{
			echo '<script>
				function changeconfirmation()
				{
					document.getElementById("confirmationSubmit").disabled = !document.getElementById("confirmationCheckbox").checked;
				}
				</script>';
			echo '<div class="row text-center">
			'.$p->t('testtool/loginNoetig').'<br /><br />
			<form action="login.php">
			<input type="hidden" name="prestudent" value="'.$_REQUEST['prestudent'].'" />
			<input id="confirmationCheckbox" type="checkbox" name="confirmation" onclick="changeconfirmation()" />
			'.$p->t('testtool/confirmationText').'
			<br><br>
			<button id="confirmationSubmit" type="submit" class="btn btn-primary" disabled/>
				'.$p->t('testtool/start').'
			</button>
			</form>';
		}
		else
		{
			echo '<div class="row text-center">
			'.$p->t('testtool/loginNoetig').'<br /><br />
			<form action="'.APP_ROOT.'/addons/bewerbung/cis/." target="_top">
			<button type="submit" class="btn btn-primary" />
				'.$p->t('testtool/login').'
			</button>
			</form>';
		}
		echo '
		</div>';
		echo '</div>';
	}
	else
	{
		$prestudent_id_dummy_student = (defined('PRESTUDENT_ID_DUMMY_STUDENT')?PRESTUDENT_ID_DUMMY_STUDENT:'');
		echo '<div class="col-xs-11">';

		//	Welcome text
		echo '<div id="alertmsgdiv">'.$alertmsg.'</div>';
		echo '
			<div class="row" style="margin-bottom: 10%; margin-top: 3%;">
				<div class="col-xs-6 text-center" style="border-right: 1px solid lightgrey;">
					<h1 style="white-space: normal">Herzlich Willkommen zum Reihungstest</h1><br><br>
					Bitte warten Sie mit dem Login auf die Anweisung der Aufsichtsperson.<br><br>
					Wir wünschen Ihnen einen erfolgreichen Start ins Studium.
				</div>
				<div class="col-xs-6 text-center">
					<h1 style="white-space: normal">Welcome to the placement test</h1> <br><br>
					Please wait for the tutor\'s instructions before you log in.<br><br>
					We wish you a good start to your studies.
				</div>
			</div>
		';

		// Begin form
		echo '<div class="row text-center">';
		echo '<form method="post" class="form-inline">';

		// Name select menu
		echo '<div class="form-group">';
		echo '<label for="select-prestudent" class="col-sm-2 control-label">Name</label>';
		echo '<div class="col-sm-10">';
		echo '<SELECT name="prestudent" id="select-prestudent" class="form-control">';
		echo '<OPTION value="'.$prestudent_id_dummy_student.'">Bitte wählen / Please select...</OPTION>\n';
		foreach($ps->result as $prestd)
		{
			$stg = new studiengang();
			$stg->load($prestd->studiengang_kz);
			if (isset($_REQUEST['prestudent']) && $prestd->prestudent_id==$_REQUEST['prestudent'])
				$selected = 'selected';
			else
				$selected='';
			echo '
					<OPTION value="'.$prestd->prestudent_id.'" '.$selected.'>'.$prestd->nachname.' '.$prestd->vorname.' ('.(strtoupper($stg->typ.$stg->kurzbz)).')</OPTION>\n';
		}
		// An der FHTW gibt es 3 Testuser für den Camus International
		if (CAMPUS_NAME == 'FH Technikum Wien')
		{
			echo '<OPTION value="30891">Testuser Campus International 01</OPTION>\n';
			echo '<OPTION value="30890">Testuser Campus International 02</OPTION>\n';
			echo '<OPTION value="30889">Testuser Campus International 03</OPTION>\n';
		}
		echo '</SELECT>';
		echo '</div>'; // end col-xs
		echo '</div>'; // end form-group

		// Datepicker input
		echo '<div class="form-group"> ';
		echo '<label for="datepicker" class="col-sm-offset-1 col-sm-4 control-label">Geburtsdatum | Date of Birth</label>';
		echo '<div class="col-sm-3">';
		echo '<input type="text" id="datepicker" class="form-control" name="gebdatum" value="'.$date->formatDatum($gebdatum,'d.m.Y').'" placeholder="DD.MM.YYYY">';
		echo '</div>'; // end col-xs
		echo '</div>'; // end form-group

		// Login button
		echo '<button type="submit" class="btn btn-default" value="'.$p->t('testtool/login').'" />'.$p->t('testtool/login').'</button>';

		echo '</form>'; // end form

		echo '</div>';  // end row
		echo '</div>';  // end col-xs-11
	}
}

?>
</div><!--/.row-->
</body>
</html>
