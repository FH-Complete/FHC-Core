<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Cbristian Paminger <cbristian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 *
 */
/**
 * Dieses Script veraendert die Datenbank, damit die Dokumente (PDFs, Bilder, etc) die in 
 * der Datenbank gespeichert sind, nicht mebr HEX codiert, sondern base64 Codiert sind
 * 
 * !!! Dieses Script muss von der CommandLine gestartet werden da es sonst zu einem Timeout kommt !!!
 */
header('Expires:  -1');
header('Cache-Control: no-store, no-cache, must-revalidate' );
header('Pragma: no-cache' );	
header('Content-Type: text/html;charset=UTF-8');

#define("DB_NAME","-devvilesci");
require_once('../config/system.config.inc.php');

echo "<br>\nStart Host:".DB_HOST.'  DB:'.DB_NAME .' line '. __LINE__ .' ; file ' . __FILE__."<br>\n";
flush();

error_reporting (E_ALL);
require_once('../include/basis_db.class.php');


//Hexcode in String umwandeln
function hexstr($hex)
{
	$string='';
	if (!$len=mb_strlen($hex,'UTF-8')) 
		return $string;	
	for ($i=0;$i<strlen($hex)-1;$i+=2)
	        $string.=chr(hexdec(mb_substr($hex,$i,1,'UTF-8').mb_substr($hex,$i+1,1,'UTF-8')));
   	return $string;
}

function convert($string)
{
	if (!$len=mb_strlen($string,'UTF-8')) 
		return $string;	
	$ret=base64_encode(hexstr($string));
	return $ret;
}

$next_row_counter=10;

echo "Records je Zeile  $next_row_counter <br>\n";
flush();


$db = new basis_db();
if (!$db->db_query('BEGIN'))
	die('Error:'.$db->db_last_error()."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);

echo "tbl_akte...".date('Y-m-d H:i:s')."<br>\n";
flush();

//Akte
$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT akte_id, inhalt FROM public.tbl_akte where inhalt is not null and inhalt>''; \n";
if($result = $db->db_query($qry))
{
	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	
			
	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);

		$row->inhalt=convert($row->inhalt);
		$qry = "UPDATE public.tbl_akte SET inhalt='".$row->inhalt."' WHERE akte_id='".$row->akte_id."'; ";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);

		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);
		echo $row->akte_id."($erg sec), ";
		flush();
		$i++;
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\ntbl_akte...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();
echo "<hr>\n";

//Person
echo "tbl_person...".date('Y-m-d H:i:s')."<br>\n";
flush();

$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT foto, person_id FROM public.tbl_person WHERE foto is not null and foto>'';  \n";
if($result = $db->db_query($qry))
{

	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);
		
		$row->foto=convert($row->foto);
		$qry = "UPDATE public.tbl_person SET foto='".$row->foto."' WHERE person_id='".$row->person_id."';  \n";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);		
		echo $row->person_id."($erg sec), ";
		flush();
		$i++;		
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\ntbl_person...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";


//Testtool - tbl_frage_sprache.bild
echo "tbl_frage_sprache bild+audio...".date('Y-m-d H:i:s')."<br>\n";
flush();

$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT bild,audio, frage_id, sprache FROM testtool.tbl_frage_sprache WHERE (bild is not null and bild >'') or (audio is not null and audio >'');  \n";
if($result = $db->db_query($qry))
{
	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);

		$row->bild=convert($row->bild);
		$row->audio=convert($row->audio);
		$qry = "UPDATE testtool.tbl_frage_sprache SET audio=".($row->audio?"'".$row->audio."'":'null')." ,bild=".($row->bild?"'".$row->bild."'":'null')." WHERE frage_id='".$row->frage_id."' AND sprache='".$row->sprache."';  \n";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);
#		echo $row->frage_id."($erg sec), ";
#		flush();
		$i++;		
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\ntbl_frage_sprache bild+audio...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

//Testtool - tbl_vorschlag_sprache.bild
echo "testtool.tbl_vorschlag_sprache bild+audio...".date('Y-m-d H:i:s')."<br>\n";
flush();

$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT bild,audio, vorschlag_id, sprache FROM testtool.tbl_vorschlag_sprache WHERE (bild is not null and bild>'') or (audio is not null and audio>'');  \n";
# --->$qry = "SELECT bild, frage_id, sprache FROM testtool.tbl_vorschlag_sprache WHERE bild is not null and bild>''";
if($result = $db->db_query($qry))
{
	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);	
		
		$row->bild=convert($row->bild);
		$row->audio=convert($row->audio);		
		$qry = "UPDATE testtool.tbl_vorschlag_sprache SET bild=".($row->bild?"'".$row->bild."'":'null').", audio=".($row->audio?"'".$row->audio."'":'null')." WHERE vorschlag_id='".$row->vorschlag_id."' AND sprache='".$row->sprache."';  \n";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);		
#		echo $row->vorschlag_id."($erg sec), ";
#		flush();
		$i++;		
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\ntesttool.tbl_vorschlag_sprache bild + audio...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

//Kommune tbl_wettbewerb
echo "kommune.tbl_wettbewerb icon...".date('Y-m-d H:i:s')."<br>\n";
flush();


$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT icon, wettbewerb_kurzbz FROM kommune.tbl_wettbewerb WHERE icon is not null and icon>''; \n";
if($result = $db->db_query($qry))
{

	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);	
		
		$row->icon=convert($row->icon);
		$qry = "UPDATE kommune.tbl_wettbewerb SET icon='".$row->icon."' WHERE wettbewerb_kurzbz='".addslashes($row->wettbewerb_kurzbz)."'; \n";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);		
#		echo $row->wettbewerb_kurzbz."($erg sec), ";
#		flush();
		$i++;				
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\nkommune.tbl_wettbewerb icon...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

//Kommune tbl_team
echo "kommune.tbl_wettbewerb logo...".date('Y-m-d H:i:s')."<br>\n";
flush();

$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT logo, team_kurzbz FROM kommune.tbl_team WHERE logo is not null and logo>''; \n";
if($result = $db->db_query($qry))
{

	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);
		
		$row->logo=convert($row->logo);
		$qry = "UPDATE kommune.tbl_team SET logo='".$row->logo."' WHERE team_kurzbz='".addslashes($row->team_kurzbz)."'";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();			
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);		
#		echo $row->team_kurzbz."($erg sec), ";
#		flush();
		$i++;				
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\nkommune.tbl_wettbewerb logo...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

//tbl_sprache
echo "public.tbl_sprache flagge ...".date('Y-m-d H:i:s')."<br>\n";
flush();

$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT flagge, sprache FROM public.tbl_sprache WHERE flagge is not null and flagge>'';  \n";
if($result = $db->db_query($qry))
{
	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);
		
		$row->flagge=convert($row->flagge);
		$qry = "UPDATE public.tbl_sprache SET flagge='".$row->flagge."' WHERE sprache='".$row->sprache."'; \n";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " $i  : $erg sec<br>\n";
			flush();			
		}	
		
		$erg=number_format( (microtime(true) - $beg2 ),3);		
#		echo $row->sprache."($erg sec), ";		
#		flush();
		$i++;			
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\npublic.tbl_sprache flagge...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

//tbl_erhalter
echo "public.tbl_erhalter logo ...".date('Y-m-d H:i:s')."<br>\n";
flush();

$beg=microtime(true);
$beg1=$beg;
$qry = "SELECT logo, erhalter_kz FROM public.tbl_erhalter WHERE logo is not null and logo>''; \n";
if($result = $db->db_query($qry))
{
	echo "<br>\nRecords...".$db->db_num_rows($result)."<br>\n";
	flush();	

	$i=0;
	while($row = $db->db_fetch_object($result))
	{
		$beg2=microtime(true);
		
		$row->logo=convert($row->logo);
		$qry = "UPDATE public.tbl_erhalter SET logo='".$row->logo."' WHERE erhalter_kz='".$row->erhalter_kz."'";
		$res=$db->db_query($qry);
		if(!$rows=$db->db_affected_rows($res))
		{
			$err=$db->db_last_error();
			$db->db_query('ROLLBACK');
			die("Error  :" .$err."<br>\n".$qry."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__);
		}
		$db->db_free_result($res);
		
		$rest=$i%$next_row_counter;
		if ($i>0 && !$rest) 
		{
			$erg=number_format( (microtime(true) - $beg1 ),3);
			$beg1=microtime(true);			
			echo " : $next_row_counter Anederungen : $erg sec<br>\n";
			flush();			
		}	
		$erg=number_format( (microtime(true) - $beg2 ),3);		
#		echo $row->erhalter_kz."($erg sec), ";		
#		flush();
		$i++;			
	}
}
$db->db_free_result($result);
$erg=number_format( (microtime(true) - $beg ),3);	
echo "<br>\npublic.tbl_erhalter logo...ENDE  $erg sec ".$db->db_last_error()."<br>\n";
flush();

echo "<hr>\n";

if(!$db->db_query('COMMIT'))
	echo "<br>\n".date('Y-m-d H:i:s')." ERROR :".$db->db_last_error()."<br>\n".' line '. __LINE__ .' ; file ' . __FILE__;
else
	echo "<br>\n<b>Aktualisierung abgeschlossen ".date('Y-m-d H:i:s')."</b>";

#$db->db_query('ROLLBACK');
	
?>