<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Detailierte Auswertung der Reihungstests
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/gebiet.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Testtool Auswertung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php">
	</head>
	<body>
	<h1>Testtool Auswertung - Frage Detail</h1>
';

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/testtool',null,'s'))
	die('Sie haben keine Berechtigung für diese Seite');
	
$frage_id = (isset($_GET['frage_id'])?$_GET['frage_id']:'');
$sprache = (isset($_GET['sprache'])?$_GET['sprache']:'German');
$db = new basis_db();

if($frage_id!='' && is_numeric($frage_id))
{
	$qry = "SELECT 
				*,
				tbl_frage_sprache.text as frage_text,
				tbl_frage_sprache.audio as frage_audio,
				tbl_frage_sprache.bild as frage_bild,
				tbl_vorschlag.nummer as vorschlag_nummer,
				tbl_vorschlag_sprache.text as vorschlag_text,
				tbl_vorschlag_sprache.audio as vorschlag_audio,
				tbl_vorschlag_sprache.bild as vorschlag_bild,
				tbl_frage.level,
				/*(SELECT count(*) FROM testtool.tbl_antwort WHERE vorschlag_id=tbl_vorschlag.vorschlag_id) as anzahl,*/
				(SELECT count(*) FROM testtool.tbl_pruefling_frage WHERE frage_id='$frage_id') as gesamt_anzahl
			FROM 
				testtool.tbl_frage 
				JOIN testtool.tbl_frage_sprache USING(frage_id) 
				JOIN testtool.tbl_vorschlag USING(frage_id) 
				JOIN testtool.tbl_vorschlag_sprache USING(vorschlag_id)
			WHERE
				tbl_frage_sprache.sprache='".addslashes($sprache)."' AND
				tbl_vorschlag_sprache.sprache='".addslashes($sprache)."' AND
				frage_id='$frage_id'
			ORDER BY punkte DESC, vorschlag_id";
		
	if($result = $db->db_query($qry))
	{
		$first=true;
		$i=0;
		while($row = $db->db_fetch_object($result))
		{
			$i++;
			if($first)
			{
				//Fragen Details
				echo "
					Anzahl der Personen, die diese Frage bekommen haben (alle Stg): $row->gesamt_anzahl<br>
					Level der Frage: $row->level
					<br /><br />
					";
				echo '<center>';
				$first=false;
				echo $row->frage_text.'<br>';
				if($row->frage_audio!='')
				{
					echo '	<audio src="../sound.php?src=frage&amp;frage_id='.$frage_id.'&amp;sprache='.$sprache.'" controls="controls">
								<div>
									<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
								</div>
							</audio>';
				}
				if($row->frage_bild!='')
					echo "<img class='testtoolfrage' src='../bild.php?src=frage&amp;frage_id=$frage_id&amp;sprache=".$sprache."' /><br/><br/>\n";
	
				echo '</center><br /><br />';
			}
			
			//Vorschlaege
			echo '<center>Nummer: '.$row->vorschlag_nummer.'</center><br>';
			echo '<center><div style="width: 90%; padding: 5px; background-color: #eee;border: 1px solid black">';
			//echo "<b>Vorschlag $i: </b>";
			$first=false;

			echo $row->vorschlag_text;
			if($row->vorschlag_audio!='')
			{
				echo '	<audio src="../sound.php?src=vorschlag&amp;vorschlag_id='.$row->vorschlag_id.'&amp;sprache='.$sprache.'" controls="controls">
							<div>
								<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
							</div>
						</audio>';
			}
			if($row->vorschlag_bild!='')
				echo "<img class='testtoolfrage' src='../bild.php?src=vorschlag&amp;vorschlag_id=$row->vorschlag_id&amp;sprache=".$sprache."' /><br/><br/>\n";
			echo "<br /><br /><br /><b>Punkte:</b> ".number_format($row->punkte,2);
			//echo "<br /><b>Anzahl beantwortet (alle Stg):</b> $row->anzahl";
			echo '</div></center><br /><br />';
		}
	}
}
?>
	</body>
</html>