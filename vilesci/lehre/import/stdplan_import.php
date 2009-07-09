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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		require_once('../../../config/vilesci.config.inc.php');
		require_once('../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
?>
			
<HTML>
<BODY>
<?php
	$field=file($userfile);
	$anz=count($field);
	for ($i=0;$i<$anz;$i++)
	{
		//echo $field[$i].'<br>';
		$endpos=strpos($field[$i],9);
		$lektor=substr($field[$i],0,$endpos);
		//echo $lektor.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$wochentag=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $wochentag.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$stunde_id=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $stunde_id.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$lehrfach=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $lehrfach.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$ort=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $ort.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$unr=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $unr.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$keineahnung=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $keineahnung.'<br>';
		$beginpos=$endpos+1;
		$endpos=strpos($field[$i],9,$beginpos);
		$klassenbez=substr($field[$i],$beginpos,$endpos-$beginpos);
		//echo $klassenbez.'<br>';
		$beginpos=$endpos+1;
		$endpos=strlen($field[$i]);
		$jahreswochen=trim(substr($field[$i],$beginpos,$endpos-$beginpos));
		//echo $jahreswochen.'<br>';

		$sql_query="INSERT INTO untis (lektor,wochentag,stunde,lehrfach,ort,unr,jahreswochen,klassenbez) VALUES ('$lektor','$wochentag','$stunde_id','$lehrfach','$ort','$unr','$jahreswochen','$klassenbez')";
		$result=$db->db_query($sql_query);
		if(!$result)
			die($db->db_last_error().'<BR>'.$i.'<BR>'.$sql_query);
	}
?>
Finished <BR>
<A href="index.html">Zur&uuml;ck</A>
</BODY>
</HTML>