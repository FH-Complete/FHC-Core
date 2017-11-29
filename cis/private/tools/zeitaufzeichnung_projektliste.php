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
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/projekt.class.php');

if (!isset($_GET['projexpmonat']))
	die("Parameter monat fehlt");
if (!isset($_GET['projexpjahr']))
	die("Parameter jahr fehlt");

$uid = get_uid();
$benutzer = new benutzer();
if (!$benutzer->load($uid))
	die($p->t("zeitaufzeichnung/benutzerWurdeNichtGefunden", array($uid)));

$month = $_GET['projexpmonat'];
$year = $_GET['projexpjahr'];

$monthtext = $monatsname[1][$month - 1];
$username = $benutzer->vorname." ".$benutzer->nachname;
$mitarbeiter = new mitarbeiter();
$mitarbeiter->load($uid);
$persnr = $mitarbeiter->personalnummer;
$daysinmonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$date = new datum();
$ztauf = new zeitaufzeichnung();

$ztauf->getListeUserFromTo($uid, $year.'-'.$month.'-01', $year.'-'.$month.'-'.$daysinmonth);

//objects for one projectline of list (corresponds to one day)
$projectlines = [];
$dayStart = $dayEnd = '';
$projectnames = $tosubtract = $allpauseranges = [];
$toignore = ['Pause', 'LehreExtern'];
$ztaufdata = $ztauf->result;
$monthsums = [0 => 0.00];

//sprt list by startdate ascending (if not already done in zeitaufzeichnung class)
usort($ztaufdata, function ($ztaufa, $ztaufb)
{
	$date = new datum();
	return $date->mktime_fromtimestamp($ztaufa->start) - $date->mktime_fromtimestamp($ztaufb->start);
}
);

//fill projectlines with data
for ($i = 0; $i < sizeof($ztaufdata); $i++)
{
	$ztaufrow = $ztaufdata[$i];
	$day = intval($date->formatDatum($ztaufrow->ende, 'd'));
	//first  entry for a day
	$isFirstEntry = !isset($projectlines[$day]);

	//last entry for a day (next entry is different day)
	$isLastEntry = !array_key_exists($i + 1, $ztaufdata) || intval($date->formatDatum($ztaufdata[$i + 1]->ende, 'd')) != $day;

	if (in_array($ztaufrow->aktivitaet_kurzbz, $toignore))
	{
		$subtraction = new stdClass();
		$subtraction->start = $ztaufrow->start;
		$subtraction->ende = $ztaufrow->ende;
		$subtraction->diff = $date->convertTimeStringToHours($ztaufrow->diff);
		$subtraction->typ = $ztaufrow->aktivitaet_kurzbz;
		$tosubtract[] = $subtraction;

		//save all pause ranges
		if($ztaufrow->aktivitaet_kurzbz == $toignore[0])
		{
			$prevpause = null;
			if(sizeof($allpauseranges)>0)
			{
				$prevpause = $allpauseranges[sizeof($allpauseranges) - 1];
			}

			//first pause or no overlap to previous pause - add pauserange
			if( is_null($prevpause ) || $prevpause->ende <= $ztaufrow->start )
			{
				$pauserange = new stdClass();
				$pauserange->start = $ztaufrow->start;
				$pauserange->ende = $ztaufrow->ende;
				$allpauseranges[] = $pauserange;
			}
			//pause overlap - change pause ende
			elseif($prevpause->ende > $ztaufrow->start )
			{
				$allpauseranges[sizeof($allpauseranges) - 1]->ende = $ztaufrow->ende;
			}
		}
	}

	if (($dayStart == '' || $date->mktime_fromtimestamp($date->formatDatum($dayStart, $format = 'Y-m-d H:i:s')) > $date->mktime_fromtimestamp($date->formatDatum($ztaufrow->start, $format = 'Y-m-d H:i:s'))) && $ztaufrow->aktivitaet_kurzbz != $toignore[1])
		$dayStart = $ztaufrow->start;
	if (($dayEnd == '' || $date->mktime_fromtimestamp($date->formatDatum($dayEnd, $format = 'Y-m-d H:i:s')) < $date->mktime_fromtimestamp($date->formatDatum($ztaufrow->ende, $format = 'Y-m-d H:i:s'))) && $ztaufrow->aktivitaet_kurzbz != $toignore[1])
		$dayEnd = $ztaufrow->ende;

	if ($isFirstEntry)
	{
		$projectlines[$day] = new stdClass();
		$projectlines[$day]->arbeitszeit = '';
		$projectlines[$day]->projekte = [];
	}

	if (isset($ztaufrow->projekt_kurzbz))
	{
		//Project already in projectline - add to worktime and description
		if (array_key_exists($ztaufrow->projekt_kurzbz, $projectlines[$day]->projekte))
		{
			$laststart =& $projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->laststart;
			$lastende =& $projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->lastende;

			$toadd = 0.00;
			//case 1: there is no overlap, just add project time difference
			if ($date->mktime_fromtimestamp($ztaufrow->start) > $date->mktime_fromtimestamp($lastende))
			{
				$toadd = $date->convertTimeStringToHours($ztaufrow->diff);
				$laststart = $ztaufrow->start;
				$lastende = $ztaufrow->ende;
				$newprojecttime = new stdClass();
				$newprojecttime->start = $ztaufrow->start;
				$newprojecttime->ende = $ztaufrow->ende;
				$projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->alleZeiten[] = $newprojecttime;
			}
			//case 2: overlap - add only part of the time
			elseif ($date->mktime_fromtimestamp($ztaufrow->start) < $date->mktime_fromtimestamp($lastende) && $date->mktime_fromtimestamp($ztaufrow->ende) > $date->mktime_fromtimestamp($lastende))
			{
				$toadd = ($date->mktime_fromtimestamp($ztaufrow->ende) - $date->mktime_fromtimestamp($lastende)) / 3600;
				$lastende = $ztaufrow->ende;
				$alleZeiten =& $projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->alleZeiten;
				$index = count($alleZeiten);
				$alleZeiten[$index-1]->ende = $ztaufrow->ende;
			}
			$projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->stunden += $toadd;

			//concatenate descriptions "working packages" for each project
			if (!empty($ztaufrow->beschreibung))
			{
				$packagecounter = ++$projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->arbeitspakete;
				if ($packagecounter == 1)
					$projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->beschreibung = $ztaufrow->beschreibung;
				else
					$projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz]->beschreibung .= " | ".str_replace(array("\r\n", "\r", "\n"), " ", $ztaufrow->beschreibung);
			}
		}
		else
		{
			//add new project to projectline
			$newproject = new stdClass();
			$newproject->laststart = $ztaufrow->start;
			$newproject->lastende = $ztaufrow->ende;
			$newprojecttime = new stdClass();
			$newprojecttime->start = $ztaufrow->start;
			$newprojecttime->ende = $ztaufrow->ende;
			$newproject->alleZeiten = [];
			$newproject->alleZeiten[] = $newprojecttime;
			$newproject->stunden = $date->convertTimeStringToHours($ztaufrow->diff);
			$newproject->arbeitspakete = 0;//counter for tracking number of descriptions (work packages)
			$newproject->beschreibung = '';
			if (!empty($ztaufrow->beschreibung))
			{
				$newproject->beschreibung = str_replace(array("\r\n", "\r", "\n"), " ", $ztaufrow->beschreibung);
				$newproject->arbeitspakete++;
			}
			$projectlines[$day]->projekte[$ztaufrow->projekt_kurzbz] = $newproject;

			//add new project to array with unique project names
			if (!in_array($ztaufrow->projekt_kurzbz, $projectnames))
				$projectnames[] = $ztaufrow->projekt_kurzbz;
		}
	}

	if ($isLastEntry)
	{
		$worktime_unix = $date->mktime_fromtimestamp($date->formatDatum($dayEnd, $format = 'Y-m-d H:i:s')) - $date->mktime_fromtimestamp($date->formatDatum($dayStart, $format = 'Y-m-d H:i:s'));
		$worktimehours = $worktime_unix / 3600;

		$projectlines[$day]->arbeitszeit = $worktimehours;
		$pauseSubtracted = 0.00;
		$lehreExternExists = false;

		//subtract Pauses and LehreExtern
		foreach ($tosubtract as $subtraction)
		{
			if ($subtraction->typ == $toignore[0])
			{
				$projectlines[$day]->arbeitszeit -= $subtraction->diff;
				$pauseSubtracted += $subtraction->diff;
			}
			elseif ($subtraction->typ == $toignore[1] && $subtraction->start >= $dayStart && $subtraction->ende <= $dayEnd)
			{
				$projectlines[$day]->arbeitszeit -= $subtraction->diff;
				$lehreExternExists = true;
			}
		}

		//subtract pauses from Project worktimes
		foreach($allpauseranges as $pauserange)
		{
			foreach($projectlines[$day]->projekte as $name => $project)
			{
				foreach($projectlines[$day]->projekte[$name]->alleZeiten as $zeit)
				{
					if($pauserange->start >= $zeit->start && $pauserange->ende <= $zeit->ende)
					{
						$projectlines[$day]->projekte[$name]->stunden -= ($date->mktime_fromtimestamp($pauserange->ende) - $date->mktime_fromtimestamp($pauserange->start)) / 3600;
						break;
					}
					elseif($pauserange->start < $zeit->ende && $pauserange->start > $zeit->start)
					{
						$projectlines[$day]->projekte[$name]->stunden -= ($date->mktime_fromtimestamp($zeit->ende) - $date->mktime_fromtimestamp($pauserange->start)) / 3600;
						//break;
					}
					elseif($pauserange->ende > $zeit->start && $pauserange->ende< $zeit->ende)
					{
						$projectlines[$day]->projekte[$name]->stunden -= ($date->mktime_fromtimestamp($pauserange->ende) - $date->mktime_fromtimestamp($zeit->start)) / 3600;
						//break;
					}
				}
			}
		}
		
		//worktime with no break greater 6 -> compulsory break of half an hour
		if ($pauseSubtracted < 0.5 && !$lehreExternExists)
		{
			if ($projectlines[$day]->arbeitszeit >= 6.5)
				$projectlines[$day]->arbeitszeit -= 0.5;

			//ensure that no worktime gets smaller than 6 hours because of compulsory break
			elseif ($projectlines[$day]->arbeitszeit > 6)
				$projectlines[$day]->arbeitszeit -= $projectlines[$day]->arbeitszeit - 6;
		}

		$projectlines[$day]->arbeitszeit = floor($projectlines[$day]->arbeitszeit * 100) / 100;

		foreach($projectlines[$day]->projekte as $name => $project)
		{
			$projecthours =& $projectlines[$day]->projekte[$name]->stunden;
			$projecthours = floor($projecthours * 100) / 100;
			if (array_key_exists($name, $monthsums))
				$monthsums[$name] += $projecthours;
			else
				$monthsums[$name] = $projecthours;
		}

		$dayStart = $dayEnd = '';
		$tosubtract = $allpauseranges = [];
		$monthsums[0] += $projectlines[$day]->arbeitszeit;
	}
}

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);

// sending HTTP headers
$workbook->send("Projektliste_".$month."_".$year.".xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Projektliste");
$worksheet->setInputEncoding('utf-8');

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

$format_cell_centered =& $workbook->addFormat();
$format_cell_centered->setBorder(1);
$format_cell_centered->setAlign('center');
$format_cell_centered->setVAlign('vcenter');

$format_cell_centered_leftline =& $workbook->addFormat();
$format_cell_centered_leftline->setBorder(1);
$format_cell_centered_leftline->setAlign('center');
$format_cell_centered_leftline->setVAlign('vcenter');
$format_cell_centered_leftline->setLeft(2);

$format_cell_centered_rightline =& $workbook->addFormat();
$format_cell_centered_rightline->setBorder(1);
$format_cell_centered_rightline->setAlign('center');
$format_cell_centered_rightline->setVAlign('vcenter');
$format_cell_centered_rightline->setRight(2);

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
$nrProjects = sizeof($projectnames);
$daywidth = 4;
$totalworktimewidth = 10;
$worktimewidth = 8;
$worksheet->setColumn(0, 1, $daywidth);
$worksheet->setColumn(2, 2, $totalworktimewidth);

//calculate max width for project descriptions
$maxwidthprojects = $totalworktimewidth * (12 - $nrProjects);
$projectcolumnwidths = array_fill_keys($projectnames, $worktimewidth);

//set project column width depending on project description widths
foreach ($projectlines as $line)
{
	foreach ($line->projekte as $key => $project)
	{
		if ($projectcolumnwidths[$key] < strlen($project->beschreibung))
			$projectcolumnwidths[$key] = strlen($project->beschreibung);
	}
}

//distribute width remainder evenly among projects
if ($nrProjects != 0)
	$remwidth = ($maxwidthprojects - array_sum($projectcolumnwidths)) / $nrProjects;

foreach ($projectcolumnwidths as $projectname => $width)
	$projectcolumnwidths[$projectname] += $remwidth;

//calculating spaces for centering global header texts
$numberspaces = ($maxwidthprojects - 10 - strlen($username));
$spacesstringFirst = "";

while ($numberspaces > 0)
{
	$spacesstringFirst .= " ";
	$numberspaces--;
}

$numberspaces = ($maxwidthprojects - 14 - strlen($persnr));
$spacesstringSecond = "";
while ($numberspaces > 0)
{
	$spacesstringSecond .= " ";
	$numberspaces--;
}

$spalte = $zeile = 0;

//write global header
$lastspalte = ($nrProjects > 0) ? 2 + sizeof($projectnames) * 2 : 14;
$worksheet->setMerge($zeile, $spalte, $zeile + 1, $spalte + 2);
$worksheet->write($zeile, $spalte, $monthtext." ".$year, $format_heading_left);
$worksheet->write($zeile + 1, $spalte, "", $format_heading_left);

$worksheet->setMerge($zeile, $spalte + 3, $zeile, $lastspalte);
$worksheet->setMerge($zeile + 1, $spalte + 3, $zeile + 1, $lastspalte);
$worksheet->write($zeile, $spalte + 3, "Projektliste gedruckt am:".$spacesstringFirst.$username, $format_heading_right);
$worksheet->write($zeile, $lastspalte, '', $format_heading_right);
$worksheet->write($zeile + 1, $spalte + 3, date('d.m.Y H:i').$spacesstringSecond.'Personal-Nr.:'.$persnr, $format_heading_right_bottomline);
$worksheet->write($zeile + 1, $lastspalte, '', $format_heading_right_bottomline);
$zeile += 3;

//general options
$worksheet->setLandscape();
$worksheet->hideGridlines();
$worksheet->hideScreenGridlines();

//write table header
$worksheet->setMerge($zeile, $spalte, $zeile + 1, $spalte + 1);
$worksheet->write($zeile, $spalte, "Tag", $format_bold_centered_alllines);
$worksheet->write($zeile + 1, $spalte++, "", $format_bold_centered_alllines);
$worksheet->setMerge($zeile, ++$spalte, $zeile + 1, $spalte);
$worksheet->write($zeile, $spalte, "Arbeitszeit", $format_bold_centered_alllines);
$worksheet->write($zeile + 1, $spalte, "", $format_bold_centered_alllines);
$spalte++;

foreach ($projectnames as $project)
{
	$worksheet->setMerge($zeile, $spalte, $zeile, $spalte + 1);
	$worksheet->write($zeile, $spalte, $project, $format_bold_centered_toprightline);
	$worksheet->write($zeile, $spalte + 1, "", $format_bold_centered_toprightline);
	$worksheet->write($zeile + 1, $spalte, "Stunden", $format_bold_centered_bottomline);
	$worksheet->write($zeile + 1, $spalte + 1, "Tätigkeit", $format_bold_centered_bottomrightline);
	$spalte += 2;
}
$zeile += 2;

//write table body
for ($daysnmbr = 1; $daysnmbr <= $daysinmonth; $daysnmbr++)
{
	//write day and weekday
	$spalte = 0;
	$monthstr = ($month < 10) ? '0'.$month : $month;
	$daystr = ($daysnmbr < 10) ? '0'.$daysnmbr : $daysnmbr;
	$datestring = $year.'-'.$monthstr.'-'.$daystr;
	$weekday = substr($tagbez[1][$date->formatDatum($datestring, 'N')], 0, 2);
	$worksheet->write($zeile, $spalte++, $weekday, $format_cell_centered_leftline);
	$worksheet->write($zeile, $spalte++, $daysnmbr, $format_cell_centered_rightline);

	if (array_key_exists($daysnmbr, $projectlines))
	{
		//write worktime
		$worksheet->write($zeile, $spalte++, number_format($projectlines[$daysnmbr]->arbeitszeit, 2, ",", "."), $format_cell_centered_rightline);
		$spaltetemp = $spalte;
		//write projects
		foreach ($projectnames as $project)
		{
			if (array_key_exists($project, $projectlines[$daysnmbr]->projekte))
			{
				$worksheet->setColumn($spalte, $spalte, $worktimewidth);
				$worksheet->write($zeile, $spalte++, number_format($projectlines[$daysnmbr]->projekte[$project]->stunden, 2, ",", "."), $format_cell_centered_leftline);
				$worksheet->setColumn($spalte, $spalte, $projectcolumnwidths[$project]);
				$worksheet->write($zeile, $spalte++, $projectlines[$daysnmbr]->projekte[$project]->beschreibung, $format_cell_rightline);
			}
			else
			{
				$worksheet->write($zeile, $spalte++, '', $format_cell_centered_leftline);
				$worksheet->write($zeile, $spalte++, '', $format_cell_rightline);
			}
		}
	}
	else
	{
		//write empty cells
		$worksheet->write($zeile, $spalte, '0,00', $format_cell_centered_rightline);
		$toskip = sizeof($projectnames) * 2;
		for ($i = 0; $i <= $toskip; $i++)
		{
			if ($i % 2 == 0)
				$worksheet->write($zeile, $spalte++, '', $format_cell_centered_rightline);
			else
				$worksheet->write($zeile, $spalte++, '', $format_cell_centered);
		}
	}
	$zeile++;
}

if ($nrProjects < 1)
	//no projects - merge all cells and write notice
{
	$worksheet->setMerge(3, 3, 4 + $daysinmonth, $lastspalte);
	$worksheet->write(3, 3, "keine Projekte vorhanden", $format_bold_centered_alllines);
	$worksheet->write(3, $lastspalte, "", $format_bold_centered_alllines);
}

//write monthly sums
$spalte = 0;
$worksheet->setMerge($zeile, $spalte, $zeile, $spalte + 1);
$worksheet->write($zeile, $spalte, 'Summe:', $format_bold_centered_alllines);
$spalte += 2;
$worksheet->write($zeile, $spalte++, number_format($monthsums[0], 2, ",", "."), $format_cell_centered_alllines);
foreach ($projectnames as $project)
{
	$worksheet->write($zeile, $spalte++, number_format($monthsums[$project], 2, ",", "."), $format_cell_centered_topbottomleftline);
	$worksheet->write($zeile, $spalte++, "", $format_cell_centered_topbottomrightline);
}

$worksheet->fitToPages(1, 1);
$workbook->close();

