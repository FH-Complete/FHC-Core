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
 * Dieses Script generiert fuer Testzwecke fuer jedes DMS-File einen symbolischen Link auf
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
$qry = "SELECT filename FROM campus.tbl_dms_version";
$path = DMS_PATH;
chdir($path);
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$extension = strtolower(mb_substr($row->filename, mb_strrpos($row->filename,'.')+1));
		if(in_array($extension, array('jpg','pdf','zip','doc','docx','gif','png','jpeg','odt','ods','xls')))
			$testfile = 'testfile.'.strtolower($extension);
		else
			$testfile = 'testfile.txt';
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
