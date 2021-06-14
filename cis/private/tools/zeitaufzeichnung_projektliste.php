<?php
/* Copyright (C) 2017 Technikum-Wien
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
 * Authors: Alexei Karpenko <karpenko@technikum-wien.at>
 */
/**
 * Creates a project list from the zeitaufzeichnung for a given user, month and year
 * Shows total worktime (IST-Arbeitszeit without breaks or externe Lehre) for each workday
 * together with sums of worktime and work package descriptions for each project.
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/Excel/excel.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/projekt.class.php');
require_once('../../../include/projektphase.class.php');

if (!isset($_GET['projexpmonat']))
	die("Parameter monat fehlt");
if (!isset($_GET['projexpjahr']))
	die("Parameter jahr fehlt");

$sprache = getSprache();
$p = new phrasen($sprache);
$sprache_obj = new sprache();
$sprache_obj->load($sprache);
$sprache_index = $sprache_obj->index;

$uid = get_uid();

//Wenn User Administrator ist und UID uebergeben wurde, dann die Zeitaufzeichnung
//des uebergebenen Users anzeigen
if (isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	if ($rechte->isBerechtigt('admin'))
	{
		$uid = $_GET['uid'];
	}
	else
	{
		die($p->t('global/FuerDieseAktionBenoetigenSieAdministrationsrechte'));
	}
}

$benutzer = new benutzer();
if (!$benutzer->load($uid))
	die($p->t("zeitaufzeichnung/benutzerWurdeNichtGefunden", array($uid)));

$month = $_GET['projexpmonat'];
$year = $_GET['projexpjahr'];

$monthtext = $monatsname[$sprache_index][$month - 1];
$username = $benutzer->vorname." ".$benutzer->nachname;
$mitarbeiter = new mitarbeiter();
$mitarbeiter->load($uid);
$persnr = $mitarbeiter->personalnummer;
$daysinmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$date = new datum();
$ztauf = new zeitaufzeichnung();
$projektphaseclass = new projektphase();

$activitiesToIgnore = array('DienstreiseMT', 'Ersatzruhe');//aktivitaetstypen which shouldn't be added to worktime
$ztauf->getListeUserFromTo($uid, $year.'-'.$month.'-01', $year.'-'.$month.'-'.$daysinmonth, $activitiesToIgnore);

//objects for one projectline of list (corresponds to one day)
$projektlines = array();
$dayStart = $dayEnd = '';
$projektnames = $projektphasenames = $tosubtract = $allpauseranges = array();
$projektTiteles = array();
$activitiesToSubtract = ['Pause', 'LehreExtern', 'Arztbesuch', 'Behoerde'];//aktivitaetstypen which should be subtracted fromworktime
$ztaufdata = $ztauf->result;
$totalmonthsum = 0.00;
$projektmonthsums = array();

//sort list by startdate ascending (if not already done in zeitaufzeichnung class)
usort($ztaufdata, function ($ztaufa, $ztaufb)
{
	$date = new datum();
	return $date->mktime_fromtimestamp($ztaufa->start) - $date->mktime_fromtimestamp($ztaufb->start);
}
);

//fill projectlines with data
for ($i = 0; $i < count($ztaufdata); $i++)
{
	$ztaufrow = $ztaufdata[$i];

	//make sure dates are in correct format
	$ztaufrow->start = $date->formatDatum($ztaufrow->start, $format = 'Y-m-d H:i:s');
	$ztaufrow->ende = $date->formatDatum($ztaufrow->ende, $format = 'Y-m-d H:i:s');
	$day = intval($date->formatDatum($ztaufrow->ende, 'd'));
	//first  entry for a day
	$isFirstEntry = !isset($projektlines[$day]);

	//last entry for a day (next entry is different day)
	$isLastEntry = !array_key_exists($i + 1, $ztaufdata) || intval($date->formatDatum($ztaufdata[$i + 1]->ende, 'd')) != $day;

	if (in_array($ztaufrow->aktivitaet_kurzbz, $activitiesToSubtract))
	{
		$subtraction = new stdClass();
		$subtraction->start = $ztaufrow->start;
		$subtraction->ende = $ztaufrow->ende;
		$subtraction->diff = $date->convertTimeStringToHours($ztaufrow->diff);
		$subtraction->typ = $ztaufrow->aktivitaet_kurzbz;
		$tosubtract[] = $subtraction;

		//save all pause ranges
		if ($ztaufrow->aktivitaet_kurzbz == $activitiesToSubtract[0])
		{
			$prevpause = null;
			if (count($allpauseranges) > 0)
			{
				$prevpause = $allpauseranges[count($allpauseranges) - 1];
			}

			//first pause or no overlap to previous pause - add pauserange
			if (is_null($prevpause) || $prevpause->ende <= $ztaufrow->start)
			{
				$pauserange = new stdClass();
				$pauserange->start = $ztaufrow->start;
				$pauserange->ende = $ztaufrow->ende;
				$allpauseranges[] = $pauserange;
			}
			//pause overlap - change pause ende
			elseif ($prevpause->ende > $ztaufrow->start)
			{
				$allpauseranges[count($allpauseranges) - 1]->ende = $ztaufrow->ende;
			}
		}
	}

	//save new dayStart (if earlier) or dayEnd (if later)
	if (($dayStart == '' || $dayStart > $ztaufrow->start) && $ztaufrow->aktivitaet_kurzbz != $activitiesToSubtract[1])
		$dayStart = $ztaufrow->start;
	if (($dayEnd == '' || $dayEnd < $ztaufrow->ende) && $ztaufrow->aktivitaet_kurzbz != $activitiesToSubtract[1])
		$dayEnd = $ztaufrow->ende;

	if ($isFirstEntry)
	{
		$projektlines[$day] = new stdClass();
		$projektlines[$day]->arbeitszeit = '';
		$projektlines[$day]->projekte = [];
	}

	if (isset($ztaufrow->projekt_kurzbz))
	{
		//Project already in projectline - add to worktime and description
		if (array_key_exists($ztaufrow->projekt_kurzbz, $projektlines[$day]->projekte))
		{
			$currproj =& $projektlines[$day]->projekte[$ztaufrow->projekt_kurzbz];
			$laststart =& $currproj->laststart;
			$lastende =& $currproj->lastende;

			$toadd = 0.00;
			//case 1: there is no overlap, just add project time difference
			if ($ztaufrow->start >= $lastende)
			{
				$toadd = $date->convertTimeStringToHours($ztaufrow->diff);
				$laststart = $ztaufrow->start;
				$lastende = $ztaufrow->ende;
				$newprojekttime = new stdClass();
				$newprojekttime->start = $ztaufrow->start;
				$newprojekttime->ende = $ztaufrow->ende;
				$currproj->alleZeiten[] = $newprojekttime;
				if (isset($ztaufrow->projektphase_id))
					$currproj->projektphasen[$ztaufrow->projektphase_id]->alleZeiten[] = $newprojekttime;
			}
			//case 2: overlap - add only part of the time
			elseif ($ztaufrow->start < $lastende && $ztaufrow->ende > $lastende)
			{
				$toadd = ($date->mktime_fromtimestamp($ztaufrow->ende) - $date->mktime_fromtimestamp($lastende)) / 3600;
				$lastende = $ztaufrow->ende;

				$alleZeiten =& $currproj->alleZeiten;
				$index = count($alleZeiten);
				$alleZeiten[$index - 1]->ende = $ztaufrow->ende;

				//check if overlap in projektphase, change ende accordingly
				if (isset($ztaufrow->projektphase_id))
				{
					$projektphaseAlleZeiten =& $currproj->projektphasen[$ztaufrow->projektphase_id]->alleZeiten;
					$projektphaselastendeidx = count($projektphaseAlleZeiten);
					$projektphaselastende =& $projektphaseAlleZeiten[$projektphaselastendeidx - 1];
					if ($ztaufrow->start < $projektphaselastende && $ztaufrow->ende > $projektphaselastende)
						$projektphaselastende->ende = $ztaufrow->ende;
				}
			}
			$currproj->stunden +=$toadd;
			//add to projektphase
			if (isset($ztaufrow->projektphase_id))
			{
				$currproj->projektphasen[$ztaufrow->projektphase_id]->stunden += $toadd;
			}

			//concatenate descriptions "working packages" for each project
			if (!empty($ztaufrow->beschreibung))
			{
				$packagecounter = ++$currproj->arbeitspakete;
				if ($packagecounter == 1)
					$currproj->beschreibung = $ztaufrow->beschreibung;
				else
					$currproj->beschreibung .= " | ".str_replace(array("\r\n", "\r", "\n"), " ", $ztaufrow->beschreibung);
			}
		}
		else
		{
			//add new project to projectline
			$stunden = $date->convertTimeStringToHours($ztaufrow->diff);

			$newprojekt = new stdClass();
			$newprojekt->laststart = $ztaufrow->start;
			$newprojekt->lastende = $ztaufrow->ende;
			$newprojekttime = new stdClass();
			$newprojekttime->start = $ztaufrow->start;
			$newprojekttime->ende = $ztaufrow->ende;
			$newprojekt->alleZeiten = [];
			$newprojekt->alleZeiten[] = $newprojekttime;
			$newprojekt->stunden = $stunden;
			$newprojekt->arbeitspakete = 0;//counter for tracking number of descriptions (work packages)
			$newprojekt->beschreibung = '';
			if (!empty($ztaufrow->beschreibung))
			{
				$newprojekt->beschreibung = str_replace(array("\r\n", "\r", "\n"), " ", $ztaufrow->beschreibung);
				$newprojekt->arbeitspakete++;
			}

			//add projektphasen of project
			$projektphasen = array();

			if ($projektphaseclass->getProjektphasen($ztaufrow->projekt_kurzbz))
			{
				$projektphasenames[$ztaufrow->projekt_kurzbz] = array();

				foreach ($projektphaseclass->result as $ppitem)
				{
					$phasetoadd = new stdClass();
					$phasetoadd->bezeichnung = $ppitem->bezeichnung;
					$phasetoadd->stunden = 0;
					$phasetoadd->alleZeiten = array();

					if ($ppitem->projektphase_id == $ztaufrow->projektphase_id)
					{
						$phasetoadd->stunden += $stunden;
						$phasetoadd->alleZeiten[] = $newprojekttime;
					}

					$projektphasen[$ppitem->projektphase_id] = $phasetoadd;

					//add new projektphase to array with unique projekt phase names
					if (!in_array($ppitem->bezeichnung, $projektphasenames[$ztaufrow->projekt_kurzbz]))
						$projektphasenames[$ztaufrow->projekt_kurzbz][] = $ppitem->bezeichnung;
				}
			}

			$newprojekt->projektphasen = $projektphasen;

			$projektlines[$day]->projekte[$ztaufrow->projekt_kurzbz] = $newprojekt;

			//add new projekt to array with unique projekt names
			if (!in_array($ztaufrow->projekt_kurzbz, $projektnames))
			{
				$projektnames[] = $ztaufrow->projekt_kurzbz;
				$pt = new projekt();
				$pt->load($ztaufrow->projekt_kurzbz);
				if(!empty($pt->titel))
					$projektTiteles[convertProblemChars($ztaufrow->projekt_kurzbz)] = convertProblemChars($pt->titel);
				else
					$projektTiteles[convertProblemChars($ztaufrow->projekt_kurzbz)] = 'kein Titel';
			}
		}
	}

	if ($isLastEntry)
	{
		$worktime_unix = $date->mktime_fromtimestamp($dayEnd) - $date->mktime_fromtimestamp($dayStart);
		$worktimehours = $worktime_unix / 3600;

		$projektlines[$day]->arbeitszeit = $worktimehours;
		$pauseSubtracted = 0.00;
		$lehreExternExists = false;

		//subtract pauses and LehreExtern from total worktime
		foreach ($tosubtract as $subtraction)
		{
			if ($subtraction->typ == $activitiesToSubtract[0])
			{
				$projektlines[$day]->arbeitszeit -= $subtraction->diff;
				$pauseSubtracted += $subtraction->diff;
			}
			elseif ($subtraction->typ == $activitiesToSubtract[1] && $subtraction->start >= $dayStart && $subtraction->ende <= $dayEnd)
			{
				$projektlines[$day]->arbeitszeit -= $subtraction->diff;
				$lehreExternExists = true;
			}
			elseif ($subtraction->typ == $activitiesToSubtract[2] || $subtraction->typ == $activitiesToSubtract[3])
			{
				$projektlines[$day]->arbeitszeit -= $subtraction->diff;
			}
		}

		//subtract pauses from projekt worktimes
		foreach ($allpauseranges as $pauserange)
		{
			foreach ($projektlines[$day]->projekte as $name => $projekt)
			{
				$proj =& $projektlines[$day]->projekte[$name];
				foreach ($proj->alleZeiten as $zeit)
				{
					$subtraction = 0.00;

					//pause between projekt start and end
					if ($pauserange->start >= $zeit->start && $pauserange->ende <= $zeit->ende)
					{
						$subtraction = $date->mktime_fromtimestamp($pauserange->ende) - $date->mktime_fromtimestamp($pauserange->start);
					}
					//pause and projekt time overlap at projekt time end
					elseif ($pauserange->start < $zeit->ende && $pauserange->start > $zeit->start)
					{
						$subtraction = $date->mktime_fromtimestamp($zeit->ende) - $date->mktime_fromtimestamp($pauserange->start);
						//$proj->stunden -= ($date->mktime_fromtimestamp($zeit->ende) - $date->mktime_fromtimestamp($pauserange->start)) / 3600;
					}
					//pause and projekt time overlap at projekt time start
					elseif ($pauserange->ende > $zeit->start && $pauserange->ende < $zeit->ende)
					{
						$subtraction = $date->mktime_fromtimestamp($pauserange->ende) - $date->mktime_fromtimestamp($zeit->start);
					}
					$proj->stunden -= $subtraction / 3600;
				}

				//subtract from projektphasen
				foreach ($proj->projektphasen as $phase_id => $phase)
				{
					foreach ($phase->alleZeiten as $zeit)
					{
						$subtraction = 0.00;
						//pause between projektphase start and end
						if ($pauserange->start >= $zeit->start && $pauserange->ende <= $zeit->ende)
						{
							$subtraction = ($date->mktime_fromtimestamp($pauserange->ende) - $date->mktime_fromtimestamp($pauserange->start));
						}
						//pause and projekt time overlap at projektphase time end
						elseif ($pauserange->start < $zeit->ende && $pauserange->start > $zeit->start)
						{
							$subtraction = $date->mktime_fromtimestamp($zeit->ende) - $date->mktime_fromtimestamp($pauserange->start);
						}
						//pause and projekt time overlap at projektphase time start
						elseif ($pauserange->ende > $zeit->start && $pauserange->ende < $zeit->ende)
						{
							$subtraction = $date->mktime_fromtimestamp($pauserange->ende) - $date->mktime_fromtimestamp($zeit->start);
						}
						$proj->projektphasen[$phase_id]->stunden -= $subtraction / 3600;
					}
				}
			}
		}

		//worktime with no break greater 6 -> compulsory break of half an hour
		if ($pauseSubtracted < 0.5 && !$lehreExternExists)
		{
			if ($projektlines[$day]->arbeitszeit >= 6.5)
				$projektlines[$day]->arbeitszeit -= 0.5;

			//ensure that no worktime gets smaller than 6 hours because of compulsory break
			elseif ($projektlines[$day]->arbeitszeit > 6)
				$projektlines[$day]->arbeitszeit -= $projektlines[$day]->arbeitszeit - 6;
		}

		$projektlines[$day]->arbeitszeit = round($projektlines[$day]->arbeitszeit, 2);

		//calculate sums
		foreach ($projektlines[$day]->projekte as $name => $projekt)
		{
			$projekthours =& $projektlines[$day]->projekte[$name]->stunden;
			$projekthours = round($projekthours, 2);

			if (isset($projektmonthsums[$name]->sum))
			{
				$projektmonthsums[$name]->sum += $projekthours;
				foreach ($projekt->projektphasen as $projektphase)
				{
					$projektmonthsums[$name]->projektphasen[$projektphase->bezeichnung] += round($projektphase->stunden, 2, 0);
				}
			}
			else
			{
				$monthsum = new stdClass();
				$monthsum->sum = $projekthours;
				$monthsum->projektphasen = array();

				foreach ($projekt->projektphasen as $projektphase)
				{
					$monthsum->projektphasen[$projektphase->bezeichnung] = round($projektphase->stunden, 2, 0);
				}
				$projektmonthsums[$name] = $monthsum;
			}
		}

		$dayStart = $dayEnd = '';
		$tosubtract = $allpauseranges = [];
		$totalmonthsum += $projektlines[$day]->arbeitszeit;
	}
}

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);

// sending HTTP headers
$workbook->send('Projektliste_'.$month.'_'.$year.'.xls');

// Define formats
$format_heading_left =& $workbook->addFormat();
$format_heading_left->setBold();
$format_heading_left->setAlign('center');
$format_heading_left->setAlign('vcenter');
$format_heading_left->setBottom(2);
$format_heading_left->setLeft(2);
$format_heading_left->setTop(2);

$format_heading_right =& $workbook->addFormat();
$format_heading_right->setBold();
$format_heading_right->setAlign('right');
$format_heading_right->setRight(2);
$format_heading_right->setTop(2);

$format_heading_right_bottomline =& $workbook->addFormat();
$format_heading_right_bottomline->setAlign('right');
$format_heading_right_bottomline->setRight(2);
$format_heading_right_bottomline->setBottom(2);

$format_heading_bottomline =& $workbook->addFormat();
$format_heading_bottomline->setBottom(2);

$format_heading_topline =& $workbook->addFormat();
$format_heading_topline->setTop(2);

$format_bold_centered_toprightline =& $workbook->addFormat();
$format_bold_centered_toprightline->setBorder(1);
$format_bold_centered_toprightline->setBold();
$format_bold_centered_toprightline->setAlign('center');
$format_bold_centered_toprightline->setVAlign('vcenter');
$format_bold_centered_toprightline->setTop(2);
$format_bold_centered_toprightline->setRight(2);

$format_bold_centered_bottomline =& $workbook->addFormat();
$format_bold_centered_bottomline->setBorder(1);
$format_bold_centered_bottomline->setBold();
$format_bold_centered_bottomline->setAlign('center');
$format_bold_centered_bottomline->setVAlign('vcenter');
$format_bold_centered_bottomline->setBottom(2);

$format_bold_centered_bottomrightline =& $workbook->addFormat();
$format_bold_centered_bottomrightline->setBorder(1);
$format_bold_centered_bottomrightline->setBold();
$format_bold_centered_bottomrightline->setAlign('center');
$format_bold_centered_bottomrightline->setVAlign('vcenter');
$format_bold_centered_bottomrightline->setBottom(2);
$format_bold_centered_bottomrightline->setRight(2);

$format_bold_centered_alllines =& $workbook->addFormat();
$format_bold_centered_alllines->setBorder(2);
$format_bold_centered_alllines->setBold();
$format_bold_centered_alllines->setAlign('center');
$format_bold_centered_alllines->setVAlign('vcenter');

$format_cell_rightline =& $workbook->addFormat();
$format_cell_rightline->setBorder(1);
$format_cell_rightline->setVAlign('vcenter');
$format_cell_rightline->setRight(2);

$format_cell_leftrightline =& $workbook->addFormat();
$format_cell_leftrightline->setBottom(1);
$format_cell_leftrightline->setVAlign('vcenter');
$format_cell_leftrightline->setLeft(2);
$format_cell_leftrightline->setRight(2);

$format_cell_centered =& $workbook->addFormat();
$format_cell_centered->setBorder(1);
$format_cell_centered->setAlign('center');
$format_cell_centered->setVAlign('vcenter');

$format_cell_centered_leftline =& $workbook->addFormat();
$format_cell_centered_leftline->setRight(1);
$format_cell_centered_leftline->setBottom(1);
$format_cell_centered_leftline->setAlign('center');
$format_cell_centered_leftline->setVAlign('vcenter');
$format_cell_centered_leftline->setLeft(2);

$format_cell_centered_rightline =& $workbook->addFormat();
$format_cell_centered_rightline->setBorder(1);
$format_cell_centered_rightline->setAlign('center');
$format_cell_centered_rightline->setVAlign('vcenter');
$format_cell_centered_rightline->setRight(2);

$format_cell_centered_leftrightline =& $workbook->addFormat();
$format_cell_centered_leftrightline->setBottom(1);
$format_cell_centered_leftrightline->setAlign('center');
$format_cell_centered_leftrightline->setVAlign('vcenter');
$format_cell_centered_leftrightline->setLeft(2);
$format_cell_centered_leftrightline->setRight(2);

$format_cell_centered_topbottomline =& $workbook->addFormat();
$format_cell_centered_topbottomline->setBorder(1);
$format_cell_centered_topbottomline->setAlign('center');
$format_cell_centered_topbottomline->setVAlign('vcenter');
$format_cell_centered_topbottomline->setBottom(2);
$format_cell_centered_topbottomline->setTop(2);

$format_cell_centered_topbottomleftline =& $workbook->addFormat();
$format_cell_centered_topbottomleftline->setBorder(1);
$format_cell_centered_topbottomleftline->setAlign('center');
$format_cell_centered_topbottomleftline->setVAlign('vcenter');
$format_cell_centered_topbottomleftline->setLeft(2);
$format_cell_centered_topbottomleftline->setBottom(2);
$format_cell_centered_topbottomleftline->setTop(2);

$format_cell_centered_topbottomrightline =& $workbook->addFormat();
$format_cell_centered_topbottomrightline->setBorder(1);
$format_cell_centered_topbottomrightline->setAlign('center');
$format_cell_centered_topbottomrightline->setVAlign('vcenter');
$format_cell_centered_topbottomrightline->setRight(2);
$format_cell_centered_topbottomrightline->setBottom(2);
$format_cell_centered_topbottomrightline->setTop(2);

$format_cell_centered_alllines =& $workbook->addFormat();
$format_cell_centered_alllines->setBorder(2);
$format_cell_centered_alllines->setAlign('center');
$format_cell_centered_alllines->setVAlign('vcenter');

//define column widths
$nrProjects = count($projektnames);
$totalwidth = 150;
$daywidth = 4;
$totalworktimewidth = 13;
$worktimewidth = 14;
$timecolumnswidth = 2 * $daywidth + $totalworktimewidth + $worktimewidth;

if ($nrProjects < 1)//no projekts - merge all cells and write notice
{
	$projektnames[] = "Keine Projekte vorhanden";
}

foreach ($projektnames as $projektname)
{

	$titel = $projektTiteles[convertProblemChars($projektname)];

	if ((strlen($titel)+strlen($projektname)) > 31)
	{
		$maxLength = 31;
		$maxLength = ($maxLength - 3 - strlen($projektname));
		$titel = substr($titel, 0, $maxLength);
		$titel.='...';
	}
	//Creating a worksheet

	$worksheet =& $workbook->addWorksheet(convertProblemChars($projektname).' ('.$titel.')');
	$worksheet->setInputEncoding('utf-8');
	$titel = $projektTiteles[convertProblemChars($projektname)];
	//general options
	$worksheet->setLandscape();
	$worksheet->hideGridlines();
	$worksheet->hideScreenGridlines();
	$worksheet->setmargins(0.4);

	//fixed width columns
	$worksheet->setColumn(0, 1, $daywidth);
	$worksheet->setColumn(2, 2, $totalworktimewidth);

	//calculate number of columns of projekt with phases
	$nrPhases = isset($projektphasenames[$projektname]) ? count($projektphasenames[$projektname]) : 0;

	//get taetigkeiten column width -
	//minimum is wordlength, maximum restwidth after subraction of projektphase minimum width
	$mintaetigkeitenwidth = strlen($p->t('zeitaufzeichnung/taetigkeit'));
	$maxtaetigkeitenlimit = $totalwidth - $timecolumnswidth - $nrPhases * $worktimewidth;

	if (isset($projektlines->projekte[$projektname]))
	{
		foreach ($projektlines->projekte[$projektname] as $projekt)
		{
			$projektbeschreibunglength = strlen($projekt->beschreibung);
			if ($projektbeschreibunglength >= $maxtaetigkeitenlimit)
			{
				$mintaetigkeitenwidth = $maxtaetigkeitenlimit;
				break;
			}
			elseif ($projektbeschreibunglength > $mintaetigkeitenwidth)
				$mintaetigkeitenwidth = $projektbeschreibunglength;
		}
	}

	//get projektphase width, width depending on bezeichnung
	$phasewidth = 0;
	$phasewidthlimit = $nrPhases > 0
		? ($totalwidth - $timecolumnswidth - $mintaetigkeitenwidth) / $nrPhases
		: $totalwidth - 4 * $daywidth - $worktimewidth - $mintaetigkeitenwidth;

	if (isset($projektphasenames[$projektname]))
	{
		foreach ($projektphasenames[$projektname] as $projektphasename)
		{
			$projektphasewidth = strlen($projektphasename);
			if ($projektphasewidth >= $phasewidthlimit)
			{
				$phasewidth = $phasewidthlimit;
				break;
			}
			elseif ($projektphasewidth > $phasewidth)
				$phasewidth = $projektphasewidth;
		}
	}

	//width remainder used for taetigkeit
	$taetigkeitenwidth = $totalwidth - $timecolumnswidth - $phasewidth * $nrPhases;

	$lastspalte = 4 + $nrPhases;

	//calculating spaces for centering global header texts
/*	$usernamelength = strlen($username) * 1.77;
	$numberspacesfirstrow = $totalwidth - $daywidth * 2 - $worktimewidth - $usernamelength;
	$numberspacessecondrow = $numberspacesfirstrow + $usernamelength - strlen($p->t('zeitaufzeichnung/personalnr').$persnr) - 4;

	$spacesstringfirstrow = str_repeat(' ', $numberspacesfirstrow);
	$spacesstringsecondrow = str_repeat(' ', $numberspacessecondrow);*/

	$spalte = $zeile = 0;

	//set language options
	$decpoint = $sprache_index === '2' ? '.' : ',';
	$thousandsep = $sprache_index === '2' ? ',' : '.';

	//write global header
	$worksheet->setMerge($zeile, $spalte, $zeile + 1, $spalte + 2);
	$worksheet->write($zeile, $spalte, $monthtext.' '.$year, $format_heading_left);
	$worksheet->write($zeile + 1, $spalte, $monthtext.' '.$year, $format_heading_left);
	for ($i = 1; $i < 3; $i++)
	{
		$worksheet->write($zeile, $spalte + $i, '', $format_heading_topline);
		$worksheet->write($zeile + 1, $spalte + $i, '', $format_heading_bottomline);
	}
	$worksheet->setMerge($zeile, $spalte + 3, $zeile, $lastspalte);
	$worksheet->setMerge($zeile + 1, $spalte + 3, $zeile + 1, $lastspalte);
	$worksheet->write($zeile, $spalte + 3, /*$p->t('zeitaufzeichnung/projektlistegedruckt').$spacesstringfirstrow.*/$username, $format_heading_right);
	for ($i = 4; $i < $lastspalte; $i++)
	{
		$worksheet->write($zeile, $i, '', $format_heading_topline);
		$worksheet->write($zeile + 1, $i, '', $format_heading_bottomline);
	}
	$worksheet->write($zeile, $lastspalte, '', $format_heading_right);
	$worksheet->write($zeile + 1, $spalte + 3, /*date('d.m.Y H:i').$spacesstringsecondrow.*/$p->t('zeitaufzeichnung/personalnr').$persnr, $format_heading_right_bottomline);
	$worksheet->write($zeile + 1, $lastspalte, '', $format_heading_right_bottomline);
	$zeile += 3;

	$spalte = 0;

	//write table header
	$worksheet->setMerge($zeile, $spalte, $zeile + 1, $spalte + 1);
	$worksheet->write($zeile, $spalte, $p->t('zeitaufzeichnung/tag'), $format_bold_centered_alllines);
	$worksheet->write($zeile + 1, $spalte, '', $format_bold_centered_alllines);
	$worksheet->write($zeile, $spalte + 1, $p->t('zeitaufzeichnung/tag'), $format_bold_centered_alllines);
	$worksheet->write($zeile + 1, ++$spalte, '', $format_bold_centered_alllines);
	$worksheet->setMerge($zeile, ++$spalte, $zeile + 1, $spalte);
	$worksheet->write($zeile, $spalte, $p->t('zeitaufzeichnung/arbeitszeit'), $format_bold_centered_alllines);
	$worksheet->write($zeile + 1, $spalte, '', $format_bold_centered_alllines);
	$spalte++;

	if (isset($projektphasenames[$projektname]))
	{
		$phasenames = $projektphasenames[$projektname];
		$phasenameslength = count($phasenames);
	}
	else
	{
		$phasenames = array();
		$phasenameslength = 0;
	}
	$worksheet->write($zeile, $spalte + $phasenameslength + 1, '', $format_bold_centered_toprightline);
	$worksheet->write($zeile + 1, $spalte, $p->t('zeitaufzeichnung/projektstunden'), $format_bold_centered_bottomline);

	for($i = 0; $i < $phasenameslength; $i++)
		$worksheet->write($zeile, $spalte + 1 + $i, '', $format_bold_centered_toprightline);

	$worksheet->setMerge($zeile, $spalte, $zeile, $spalte + 1 + $phasenameslength);
	$worksheet->write($zeile, $spalte, $projektname.' ('.$titel.')', $format_bold_centered_toprightline);

	for ($i = 0; $i < $phasenameslength; $i++)
		$worksheet->write($zeile + 1, $spalte + 1 + $i, $phasenames[$i], $format_bold_centered_bottomline);

	$worksheet->setColumn($spalte + $phasenameslength + 1, $spalte + $phasenameslength + 1, $taetigkeitenwidth);
	$worksheet->write($zeile + 1, $spalte + $phasenameslength + 1, $p->t('zeitaufzeichnung/taetigkeit'), $format_bold_centered_bottomrightline);
	$spalte = $spalte + 2 + $phasenameslength;
	$zeile += 2;

	//write table body
	for ($daysnmbr = 1; $daysnmbr <= $daysinmonth; $daysnmbr++)
	{
		//write day and weekday
		$spalte = 0;
		$monthstr = ($month < 10) ? '0'.$month : $month;
		$daystr = ($daysnmbr < 10) ? '0'.$daysnmbr : $daysnmbr;
		$datestring = $year.'-'.$monthstr.'-'.$daystr;
		$weekday = substr($tagbez[$sprache_index][$date->formatDatum($datestring, 'N')], 0, 2);
		$worksheet->write($zeile, $spalte++, $weekday, $format_cell_centered_leftline);
		$worksheet->write($zeile, $spalte++, $daysnmbr, $format_cell_centered_rightline);

		if (array_key_exists($daysnmbr, $projektlines))
		{
			//write worktime
			$worksheet->writeString($zeile, $spalte++, number_format($projektlines[$daysnmbr]->arbeitszeit, 2, $decpoint, $thousandsep), $format_cell_centered_rightline);
			$spaltetemp = $spalte;
			//write projekt
			if (array_key_exists($projektname, $projektlines[$daysnmbr]->projekte))
			{
				$projekt = $projektlines[$daysnmbr]->projekte[$projektname];

				$worksheet->setColumn($spalte, $spalte, $worktimewidth);
				$worksheet->writeString($zeile, $spalte++, number_format($projekt->stunden, 2, $decpoint, $thousandsep), $format_cell_centered_leftrightline);

				foreach ($projekt->projektphasen as $projektphase)
				{
					$worksheet->setColumn($spalte, $spalte, $phasewidth);
					$worksheet->writeString($zeile, $spalte++, number_format($projektphase->stunden, 2, $decpoint, $thousandsep), $format_cell_centered);
				}

				$worksheet->setColumn($spalte, $spalte, $phasewidth);
				$worksheet->write($zeile, $spalte++, $projekt->beschreibung, $format_cell_leftrightline);
			}
		}
		else
		{
			$worksheet->writeString($zeile, $spalte++, number_format(0, 2, $decpoint, $thousandsep), $format_cell_centered_leftrightline);
		}

		if (!array_key_exists($daysnmbr, $projektlines) || !array_key_exists($projektname, $projektlines[$daysnmbr]->projekte))
		{
			if (isset($projektphasenames[$projektname]))
			{
				//write empty cells until end of table
				$worksheet->write($zeile, $spalte, '', $format_cell_centered_leftrightline);
				$toskip = count($projektphasenames[$projektname]);
				for ($i = 0; $i <= $toskip; $i++)
				{
					if ($i == 0)
						$format = $format_cell_centered_leftrightline;
					else
						$format = $format_cell_centered;

					$worksheet->write($zeile, $spalte++, '', $format);
				}
				$worksheet->write($zeile, $spalte, '', $format_cell_centered_leftrightline);
			}
		}
		$zeile++;
	}

	//write monthly sums
	$spalte = 0;
	$worksheet->setMerge($zeile, $spalte, $zeile, $spalte + 1);
	$worksheet->write($zeile, $spalte, $p->t('zeitaufzeichnung/summe'), $format_bold_centered_alllines);
	$worksheet->write($zeile, $spalte + 1, '', $format_bold_centered_alllines);
	$spalte += 2;
	$worksheet->writeString($zeile, $spalte++, number_format($totalmonthsum, 2, $decpoint, $thousandsep), $format_cell_centered_alllines);

	if (isset($projektmonthsums[$projektname]))
	{
		$worksheet->writeString($zeile, $spalte++, number_format($projektmonthsums[$projektname]->sum, 2, $decpoint, $thousandsep), $format_cell_centered_alllines);

		foreach ($projektmonthsums[$projektname]->projektphasen as $projektphase)
		{
			$worksheet->writeString($zeile, $spalte++, number_format($projektphase, 2, $decpoint, $thousandsep), $format_cell_centered_topbottomline);
		}

		$worksheet->write($zeile, $spalte++, '', $format_cell_centered_alllines);
	}
	$zeile += 2;

	$worksheet->fitToPages(1, 1);
}

$workbook->close();
