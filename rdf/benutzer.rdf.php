<?php
/* Copyright (C) 2019 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/student.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('lehre/lehrveranstaltung'))
	die($rechte->errormsg);

$datum_obj = new datum();

$oRdf = new rdf('BENUTZER','http://www.technikum-wien.at/benutzer');

$filter = filter_input(INPUT_GET,'filter');

if (mb_strlen($filter) < 3)
	die('Filter muss mindestens 3 Zeichen lang sein');

$benutzer = new benutzer();
$benutzer->search(array($filter));

$studiengang = new studiengang();
$studiengang->getAll(null, false);

$oRdf->sendHeader();
$db = new basis_db();

if (count($benutzer->result) > 0)
{
	$i = 0;
	foreach ($benutzer->result as $row)
	{
		$stud = new student();
		if ($stud->load($row->uid))
		{
			if (isset($studiengang->kuerzel_arr[$stud->studiengang_kz]))
			{
				$stg = $studiengang->kuerzel_arr[$stud->studiengang_kz];
				$semester = $stud->semester;
			}
			else
			{
				$stg = '';
				$semester = '';
			}
		}
		else
		{
			$stg = '';
			$semester = '';
		}

		$i = $oRdf->newObjekt($i);
		$oRdf->obj[$i]->setAttribut('uid', $row->uid, true);
		$oRdf->obj[$i]->setAttribut('vorname', $row->vorname, true);
		$oRdf->obj[$i]->setAttribut('nachname', $row->nachname, true);
		$oRdf->obj[$i]->setAttribut('studiengang', $stg, true);
		$oRdf->obj[$i]->setAttribut('semester', $semester, true);
		$oRdf->addSequence($i);
		$i++;
	}
}
$oRdf->sendRdfText();
?>
