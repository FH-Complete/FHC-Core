<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 *			Cristina Hainberger		<hainberg@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/ampel.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/benutzerfunktion.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/person.class.php');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);

$person = new person();
$person->getPersonFromBenutzer($user);


$show = (isset($_POST['show']) ? $_POST['show'] : 'aktuell');											//show: alle / aktuell
$is_popup = (isset($_GET['verpflichtend']) && $_GET['verpflichtend'] == true) ? true : false;
//Leiter OEs holen
$benutzerfunktion = new benutzerfunktion();
$benutzerfunktion->getBenutzerFunktionen('Leitung', '', '', $user);

$organisationseinheit = new organisationseinheit();
$oes=array();
foreach ($benutzerfunktion->result as $row)
{
	$oe = $organisationseinheit->getChilds($row->oe_kurzbz);
	$oes = array_merge($oe, $oes);
}

//Berechtigungs OEs holen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if($rechte->isBerechtigt('basis/ampeluebersicht'))
{
	$oes_berechtigung = $rechte->getOEkurzbz('basis/ampeluebersicht');
	$oes = array_merge($oes_berechtigung, $oes);
}
array_unique($oes);

//actual studiensemester
$studiensemester = new studiensemester();
$ss_akt = $studiensemester->getakt();

//semesterstart
$studiensemester = new studiensemester($ss_akt);
$semester_start = $studiensemester->start;

$type = isset($_GET['type'])?$_GET['type']:'';
$ampel_id = isset($_GET['ampel_id'])?$_GET['ampel_id']:'';


//ampel confirmation & relaod of header link
if($type=='bestaetigen' && is_numeric($ampel_id))
{
	$ampel = new ampel();
	$message='';
	if($ampel->load($ampel_id))
	{
		if($ampel->isZugeteilt($user, $ampel->benutzer_select))
		{
			if(!$ampel->isBestaetigt($user, $ampel_id))
			{
				if($ampel->bestaetigen($user, $ampel_id))						//confirm ampel
				{
					echo '<script type="text/javascript">window.parent.loadampel();</script>';
					header('Refresh:0');
					exit;
				}
				else
					$message = '<span class="error">'.$ampel->errormsg.'</span>';
			}
		}
		else
			$message = '<span class="error">'.$p->t('tools/nichtZugeteilt').'</span>';
	}
	else
		$message = '<span class="error">'.$p->t('tools/ampelNichtGefunden').'</span>';

	if ($message != '')
	echo '<div class="alert alert-danger" role="alert">' . $message . '</div>';
}


//get all user ampeln
list(
	$user_ampel_arr,
	$cnt_ueberfaellig) =				//counts overdue ampeln (not expired)
	getUserAmpelData($user);

//sort ampeln
if (!empty($user_ampel_arr))
{
	$user_ampel_arr = sortUserAmpelData($user_ampel_arr);
}

//filter ampeln for popup (if at least one mandatory, which is neither expired nor before vorlaufzeit)
if ($is_popup)
{
	list(
	$user_ampel_arr,
	$cnt_ueberfaellig_und_verpflichtend) =		//counts mandatory, overdue (not expired), unconfirmed, not before vorlaufzeit
	getPopupUserAmpelData($user_ampel_arr);
}

//filter ampeln of actual term (if radiobutton is set to aktuell)
if (!$is_popup && $show == 'aktuell')
	$user_ampel_arr = getActualUserAmpelData($user_ampel_arr, $semester_start);


//*****************************************			FUNCTIONS for Ampeln
function getUserAmpelData($user)
{
	$cnt_ueberfaellig = 0;

	$ampel = new ampel();
	$ampel->loadUserAmpel($user, true);
	$user_ampel_arr = array();

	$datum = new datum();
	$now = $datum->mktime_fromdate(date('Y-m-d'));

	foreach($ampel->result as $row)
	{
		$deadline = $datum->mktime_fromdate($row->deadline);
		$vorlaufzeit = $row->vorlaufzeit;
		$verfallszeit = $row->verfallszeit;
		$bestaetigt = $ampel->isBestaetigt($user, $row->ampel_id);
		$verpflichtend = $row->verpflichtend;		// 't'/'f'

		$datum_liegt_vor_vorlaufzeit = false;
		$datum_liegt_nach_verfallszeit = false;

		if (!is_null($vorlaufzeit))
			$datum_liegt_vor_vorlaufzeit = $now < strtotime('-' .  $vorlaufzeit . ' day', $deadline);

		if (!is_null($verfallszeit))
			$datum_liegt_nach_verfallszeit = $now > strtotime('+' . $verfallszeit . ' day', $deadline);

		//default
		$show_ampel = true;			//true while actual date is not before vorlaufzeit
		$abgelaufen = false;		//false while actual date is not after verfallszeit
		$active = true;				//true while not confirmed or expired
		$status = 'gelb';			//yellow while not overdue (red) or confirmed (green)
		$status_ampel = '';			//ampel image

		if ($bestaetigt)
			$status = 'gruen';


		if ($datum_liegt_vor_vorlaufzeit)
			$show_ampel = false;


		if ($datum_liegt_nach_verfallszeit)
			$abgelaufen = true;


		if ($now >= $deadline && !$bestaetigt)
		{
			if (!$abgelaufen)
				$cnt_ueberfaellig++;
			$status = 'rot';
		}

		if ($bestaetigt || $abgelaufen)
			$active = false;

		//assign png-image to ampelstatus
		switch($status)
		{
			case 'rot':
				$status_ampel= '<img name="C" src="../../../skin/images/ampel_rot.png" >';
				break;
			case 'gelb':
				$status_ampel= '<img name="B" src="../../../skin/images/ampel_gelb.png" >';
				break;
			case 'gruen':
				$status_ampel= '<img name="A" src="../../../skin/images/ampel_gruen.png" >';
				break;
			default:
				$status_ampel= '<img name="A" src="../../../skin/images/ampel_gruen.png" >';
				break;
		}

		$user_ampel_arr[] = array(
							'ampel_id' => $row->ampel_id,
							'kurzbz' => $row->kurzbz,
							'show_ampel' => $show_ampel,
							'status' => $status,
							'status_ampel' => $status_ampel,
							'verpflichtend' => $verpflichtend,
							'bestaetigt' => $bestaetigt,
							'deadline' => $row->deadline,
							'vorlaufzeit' => $row->vorlaufzeit,
							'verfallszeit' => $row->verfallszeit,
							'beschreibung' => $row->beschreibung,
							'abgelaufen' => $abgelaufen,
							'active' => $active,
							'buttontext' => $row->buttontext);
	}

	return array($user_ampel_arr, $cnt_ueberfaellig);
}
function sortUserAmpelData($user_ampel_arr)
{
	//first: sort deadline
	$deadline_arr = array();
	foreach ($user_ampel_arr as $key => $val)
	{
		$deadline_arr[$key] = $val['deadline'];
	}

	array_multisort($deadline_arr, SORT_DESC, $user_ampel_arr);

	//second: sort inactive after active
	$active_ampel_arr = array();
	$inactive_ampel_arr = array();
	foreach ($user_ampel_arr as $user_ampel)
	{
		if ($user_ampel['active'])
		{
			$active_ampel_arr[] = $user_ampel;
		}
		else
		{
			$inactive_ampel_arr[] = $user_ampel;
		}
	}
	return $user_ampel_arr = array_merge($active_ampel_arr, $inactive_ampel_arr);
}
function getPopupUserAmpelData($user_ampel_arr)
{
	$arr = array();
	$cnt_ueberfaellig_und_verpflichtend = 0;
	foreach ($user_ampel_arr as $user_ampel)
	{
		if ($user_ampel['verpflichtend'] == 't' && !$user_ampel['bestaetigt'] && !$user_ampel['abgelaufen'] && $user_ampel['show_ampel'])
		{
				$arr[] = $user_ampel;

				if ($user_ampel['status'] == 'rot')
					$cnt_ueberfaellig_und_verpflichtend++;
		}
	}
	return array ($arr, $cnt_ueberfaellig_und_verpflichtend);
}
function getActualUserAmpelData($user_ampel_arr, $semester_start)
{
	$arr = array();
	foreach ($user_ampel_arr as $user_ampel)
	{
		if ($user_ampel['deadline'] >= $semester_start)
			$arr[] = $user_ampel;
	}
	return $arr;
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../../../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css">
<script type="text/javascript" src="../../../vendor/components/jquery/jquery.min.js"></script>
<script type="text/javascript" src="../../../vendor/twbs/bootstrap3/dist/js/bootstrap.min.js"></script>
<title><?php echo $p->t('tools/ampelsystem') ?></title>

<!--style for sancho typewriting effect-->
<style>

.cursor:after {
    opacity: 0;
    animation: cursor 1s infinite;
}

@keyframes cursor {
    0% {
        opacity: 0;
    }
    40% {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        opacity: 0;
    }
}
</style>
<!--script for sancho typewriting effect-->
<script>
function randomIntFromInterval(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}

function typeWrite(span){
  $('#'+span).addClass('cursor')
  var text = $('#'+span).text();
  var randInt = 0
  for (var i = 0; i < text.length; i++) {
    randInt += parseInt(randomIntFromInterval(20,30));
    var typing = setTimeout(function(y){
      $('#'+span).append(text.charAt(y));
    },randInt, i);
  };
  setTimeout(function(){
    $('#'+span).removeClass('cursor');
  },randInt+4500);
}


</script>
</head>


<body style="font-family: Arial, Helvetica, sans-serif; font-size: 13px;">
	<div class="container-fluid" style="padding: 0px;">

	<?php
	//title in CIS
	if (!$is_popup)
		echo '<h3>' . $p->t('tools/ampelsystem') . '</h3>';

	//*****************************************			AROUSE SANCHO for mandatory ampeln
	if ($is_popup)
	{
	//sancho message if mandatory ampeln exist
		if (count($user_ampel_arr) > 0)
		{
			echo '
				<div>
					<img src="../../../skin/images/sancho/sancho_header_du_hast_verpflichtende_ampeln.jpg" alt="sancho_verpflichtende_ampeln" style="width: 100%;">
				</div>
				<p><br><br></p>';
		}
	}
	?>

	<!--*****************************************	PANEL-GROUP -->
	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true" style="padding-left: 15px; padding-right:15px;">

	<!--*****************************************	radiobuttons actual term / all -->
	<?php
	if (!$is_popup)
	{
	?>
		<form method="POST" action="">
			<?php echo $p->t('tools/ampelAnzeigeFuer') ?>&nbsp;&nbsp;
			<label class="radio-inline">
				<input type="radio" name="show" value="aktuell"  <?php if ($show == 'aktuell') echo 'checked'; ?> onclick="submit()"> <?php echo $p->t('tools/ampelNurAktuellesStudiensemester') ?>
			 </label>
			<label class="radio-inline">
				<input type="radio" name="show" value="alle"  <?php if ($show == 'alle') echo 'checked'; ?> onclick="submit()"> <?php echo $p->t('tools/ampelAlleAnzeigen') ?>
			</label>
		</form>
		<p><br><br></p>
	<?php
	} //end if


//*****************************************			COLLAPSED PANELS WITH AMPELN

	$cnt = 1;								//counter to set iterative id's
	$cnt_inactive = 1;						//counter to set only one heading line for inactive ampeln
	$cnt_active = 0;

	//show panel "no actual ampeln" if there are no active ampeln
	foreach ($user_ampel_arr as $user_ampel)
	{
		if ($user_ampel['active'] == true)
				$cnt_active++;
	}

	if ($cnt_active == 0 && !$is_popup)
	{
		echo '
			<div class="panel">
				<div class="row" style="margin-bottom: 15px; padding-left: 15px;">
					<div class="panel-heading" style="background-color: transparent" role="tab" id="heading">
						<h4>' . $p->t('tools/ampelKeineAktuellen'). '</h4>
						<small>' . $p->t('tools/ampelKeineAktuellenTxt'). '</small>
					</div>
				</div>
			</div>';
	}
	elseif ($cnt_active != 0 && !$is_popup)
	{
		echo '
			<div class="panel">
				<div class="row" style="margin-bottom: 15px; padding-left: 15px;">
					<div class="panel-heading" style="background-color: transparent" role="tab" id="heading">
						<h4>' . $p->t('tools/ampelAktuelleAmpeln'). '</h4>
					</div>
				</div>
			</div>';
	}

	//fill panel with ampeln
	foreach ($user_ampel_arr as $user_ampel)
	{

		//use only ampeln that are not overdue
		if ($user_ampel['show_ampel'] == true)
		{
			//heading line for inactive ampeln
			if ($user_ampel['active'] == false && $cnt_inactive == 1)
			{
				echo '
				<div class="panel">
					<div class="row" style="margin-bottom: 15px; padding-left: 15px;">
						<div class="panel-heading" style="background-color: transparent" role="tab" id="heading">
						<br>
							<h4>' . $p->t('tools/ampelAbgelaufenTitel'). '</h4>
							<small>' . $p->t('tools/ampelAbgelaufenTxt'). '</small>
						</div>
					</div>
				</div>';
				$cnt_inactive++;
			}
	?>
	<div class="panel">
		<div class="row" style="margin-bottom: 15px">
			<div class="panel-heading <?php if ($user_ampel['abgelaufen']  || $user_ampel['bestaetigt']) echo 'text-muted' ?>" style="background-color: transparent" role="tab" id="heading<?php echo $cnt ?>">
				<div class="col-xs-4">
					<h5 class="panel-title" style="text-decoration: none; font-size: 14px;">
						<a class="collapsed" style="text-decoration: none;" role="button" data-toggle="collapse" data-parent="#accordion"
						   href="#collapse<?php echo $cnt ?>" aria-expanded="false" aria-controls="collapse<?php echo $cnt ?>">
						<?php echo $user_ampel['kurzbz'] ?>
						</a>
					</h5>
					<small <?php if ($user_ampel['status'] == 'rot' && !$user_ampel['abgelaufen']) echo 'style="color: red; font-weight : bold;"'?>><?php echo $p->t('global/faelligAm') . ' '; echo date('d.m.Y', strtotime($user_ampel['deadline'])) ?></small>
				</div>
				<div class="col-xs-2">
					<?php echo $user_ampel['status_ampel'] ?>
				</div>
				<div class="col-xs-2"><small>
					<?php
						if ($user_ampel['bestaetigt']) echo 'bestätigt';
						if ($user_ampel['abgelaufen'])
						{
							if ($user_ampel['bestaetigt'])
								echo " &<br>";
							else
								echo "nicht bestätigt &<br>";
							echo 'abgelaufen';
						}
					?></small>
				</div>
				<div class="col-xs-4">
					<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ampel_id='. urlencode($user_ampel['ampel_id']) . '&type=bestaetigen'; ?>">

						<button type="button" class="btn btn-default pull-right collapsed" style="margin-right: 0 px;" data-toggle="collapse" data-parent="#accordion"
								href="#collapse<?php echo $cnt ?>" aria-expanded="false" aria-controls="collapse<?php echo $cnt ?>"><?php echo $p->t('global/anzeigen') ?></button>
					</form>
				</div>
			 </div>
		</div>
		<div id="collapse<?php echo $cnt ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $cnt ?>">
			<div class="panel-body" style="font-size: 12px;">
				<?php echo $user_ampel['beschreibung'][$sprache] ?>
				<p><br></p>
				<form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ampel_id='. urlencode($user_ampel['ampel_id']) . '&type=bestaetigen'; ?>">
					<button type="type" type="submit" class="btn btn-default pull-right"
						<?php if ($user_ampel['abgelaufen'] || $user_ampel['bestaetigt']) echo 'disabled data-toggle="tooltip" data-placement="top" title="' . $p->t('tools/ampelBestaetigtAbgelaufen'). '"'?>>
						<?php
							if ($user_ampel['buttontext'][$sprache] != '')
								echo $user_ampel['buttontext'][$sprache];
							else
								echo $p->t('global/bestaetigen') ?>
					</button>
				</form>
			</div>
		</div>
	</div>
	<?php
	$cnt++;
		} //end if
	} //end foreach
	?>
</div> <!--end panel group -->

		</div> <!--end container -->
	</body>
</html>
