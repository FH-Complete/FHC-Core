<?php
/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/*
 * Dieses Script generiert fuer Testzwecke fuer jedes Abgabe-File einen symbolischen Link auf
 * eine Testdatei um im Testsystem korrekte Dateilinks zu haben.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('system/developer',null,'suid'))
	die($rechte->errormsg);

$anzahl_neu=0;
$anzahl_vorhanden=0;
$qry = "SELECT
	tbl_paabgabe.paabgabe_id || '_' || tbl_projektarbeit.student_uid || '.pdf' as filename
	FROM
		campus.tbl_paabgabe
		JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
	WHERE
		tbl_paabgabe.abgabedatum is not null";

$path = PAABGABE_PATH;
chdir($path);
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$testfile = 'testfile.pdf';
		if(!file_exists($row->filename))
		{
			$cmd = 'ln -s '.$testfile.' '.$row->filename;
			exec($cmd);
			echo "<br>\ncreate $row->filename";
			$anzahl_neu++;
		}
		else
		{
			echo "<br>\nexists $row->filename";
			$anzahl_vorhanden++;
		}
	}
}
echo '<hr>';
echo 'Done';
echo '<br>Neu:'.$anzahl_neu;
echo '<br>Vorhanden:'.$anzahl_vorhanden;
?>
