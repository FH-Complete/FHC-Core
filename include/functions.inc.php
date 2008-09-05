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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

function db_query($conn, $sql_query)
{
	if (!$result=pg_query($conn, $sql_query))
		return pg_last_error($conn);
	else
		return '';
}

// Auth: Benutzer des Webportals
function get_uid()
{
	return strtolower(trim($_SERVER['REMOTE_USER']));
	// fuer Testzwecke
	//return 'pam';
}

function crlf()
{
	// doing some DOS-CRLF magic...
	$crlf="\n";
	$client=getenv("HTTP_USER_AGENT");
	if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs))
	{
		$os = $regs[1];
		// this looks better under WinX
		if (eregi("Win",$os))
			$crlf="\r\n";
	}
	return $crlf;
}

function check_lektor($uid, $conn)
{
	// uid von View 'Lektor' holen
	$sql_query="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'";
	//echo $sql_query;
	$result=pg_query($conn, $sql_query) or die(pg_last_error($conn));
	$num_rows=pg_num_rows($result);
	// Wenn kein ergebnis return 0 sonst ID
	if ($num_rows>0)
	{
		$row=pg_fetch_object($result);
		return $row->mitarbeiter_uid;
	}
	else
		return 0;
}

function check_lektor_lehreinheit($conn, $uid, $lehreinheit_id)
{
	// uid von View 'Lektor' holen
	$sql_query="SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter WHERE mitarbeiter_uid='$uid' AND lehreinheit_id = '$lehreinheit_id'";
	//echo $sql_query;
	$result=pg_query($conn, $sql_query) or die(pg_last_error($conn));
	$num_rows=pg_num_rows($result);
	// Wenn kein ergebnis return 0 sonst ID
	if ($num_rows>0)
	{
		$row=pg_fetch_object($result);
		return $row->mitarbeiter_uid;
	}
	else
		return 0;
}

function check_student($uid, $conn)
{
	// uid von Tabelle 'Student' holen
	$sql_query="SELECT student_uid FROM public.tbl_student WHERE student_uid='$uid'";
	//echo $sql_query;
	$result=pg_query($conn, $sql_query) or die(pg_last_error($conn));
	$num_rows=pg_numrows($result);
	// Wenn kein ergebnis return 0 sonst ID
	if ($num_rows>0)
		return pg_result($result,0,'student_uid');
	else
		return 0;
}

function kalenderwoche($datum)
{
	//$woche=date("W",mktime($date[hours],$date[minutes],$date[seconds],$date[mon],$date[mday],$date[year]));
	if (!date("w",$datum))
		$datum+=86400;
	//echo date("l j.m.Y - W",$datum);
	$woche=date("W",$datum);
	//if ($woche==53)
	//	$woche=1;
	return $woche;
}

/******************************************************************************
 * Springt zum vorhergehenden Montag, wenn $datum kein Sonntag oder Montag ist.
 *
 */
function montag($datum)
{
	// Wochentag
	$wt=date("w",$datum);
	// Sonntag?
	if (!$wt)
		$wt++;
	if($wt!=1)
		$datum-=86400*($wt-1);

	return $datum;
}

function jump_day($datum, $tage)
{
	// Ein Tag sind 86400 Sekunden
	$datum+=86400*$tage;
	return $datum;
}

function jump_week($datum, $wochen)
{
	$stunde_vor=date("G",$datum);
	// Eine Woche sind 604800 Sekunden
	$datum+=604800*$wochen;
	$stunde_nach=date("G",$datum);
	if ($stunde_nach!=$stunde_vor)
		$datum+=3600;
	return $datum;
}

function jahreskalenderjump($link)
{
	$crlf=crlf();
	$datum=mktime();
	$woche=kalenderwoche($datum);
	$datum=montag($datum);
	echo '			<SMALL><CENTER><B>Jump to KW</B><BR><SMALL>'.$crlf;
	for ($anz=1;$anz<26;$anz++)
	{
		$linknew=$link.'&datum='.$datum;
		if ($woche==53)
			$woche=1;
		echo '			<A HREF="'.$linknew.'">'.$woche.'</A>'.$crlf;
		if ($anz%5==0)
			echo '			<br>'.$crlf;
		$datum+=60*60*24*7;
		$woche++;
	}
	echo '			</SMALL></CENTER></SMALL>'.$crlf;
}

function loadVariables($conn, $user)
{
	$error_msg='';
	$num_rows=0;
	$sql_query="SELECT * FROM public.tbl_variable WHERE uid='$user'";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).'<BR>'.$sql_query;
	else
		$num_rows=pg_numrows($result);

	while ($row=pg_fetch_object($result))
	{
		global ${$row->name};
		${$row->name}=$row->wert;
	}
	if (!isset($semester_aktuell))
		if(!($result=pg_query($conn, 'SELECT * FROM public.tbl_studiensemester WHERE ende>now() ORDER BY start LIMIT 1')))
			$error_msg.=pg_errormessage($conn).'<BR>'.$sql_query;
		else
		{
			$num_rows=pg_numrows($result);
			if ($num_rows>0)
			{
				$row=pg_fetch_object($result);
				global $semester_aktuell;
				$semester_aktuell=$row->studiensemester_kurzbz;
			}
		}
	if (!isset($db_stpl_table))
	{
		global $db_stpl_table;
		$db_stpl_table='stundenplan';
	}

	if (!isset($kontofilterstg))
	{
		global $kontofilterstg;
		$kontofilterstg='false';
	}

	if (!isset($ignore_kollision))
	{
		global $ignore_kollision;
		$ignore_kollision='false';
	}

	if (!isset($ignore_zeitsperre))
	{
		global $ignore_zeitsperre;
		$ignore_zeitsperre='false';
	}

	if (!isset($ignore_reservierung))
	{
		global $ignore_reservierung;
		$ignore_reservierung='false';
	}

	if (!isset($emailadressentrennzeichen))
	{
		global $emailadressentrennzeichen;
		$emailadressentrennzeichen=',';
	}
	return $error_msg;
}

function writeCISlog($stat, $rm = '')
{
	if($stat=='STOP')
		$stat = 'STOP ';
	$handle = fopen(LOG_PATH.'cis.log','a');
	fwrite($handle, date('Y-m-d H:i:s').' '. $stat .' '. getmypid() .' '. $_SERVER['REMOTE_USER'] .' '. $_SERVER['REQUEST_URI'] .' '.$rm.'
');
}

// ***************************************************************
// * Liefert das Studiensemester in dem sich
// * das uebergebene Datum befindet
// * wenn sich das Datum zwischen zwei Studiensemestern befindet
// * und $naechstes=true dann wird das naechste StSem geliefert
// * wenn $naechstes=false dann wird das vorherige StSem geliefert
// ***************************************************************
function getStudiensemesterFromDatum($conn, $datum, $naechstes=true)
{
	$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE";

	if($naechstes)
		$qry.= " ende>'$datum' ORDER BY ende ASC ";
	else
		$qry.= " start<'$datum' ORDER BY ende DESC ";

	$qry.= "LIMIT 1";

	if($result = pg_query($conn, $qry))
	{
		if($row = pg_fetch_object($result))
			return $row->studiensemester_kurzbz;
		else
			return false;
	}
	else
		return false;
}

// ***************************************************************
// * Diese Funktion liefert sowohl bei UTF-8 als auch
// * bei Latin9 die richtige Anzahl der Zeichen
// * (das normale strlen liefert bei UTF-8 Zeichen falsche Werte.)
// ***************************************************************
function utf8_strlen($str)
{
	$count = 0;
  	for ($i = 0; $i < strlen($str); ++$i)
    	if ((ord($str[$i]) & 0xC0) != 0x80)
      		++$count;

  	return $count;
}

// ****************************************************************
// * strtoupper das auch Umlaute und andere Sonderzeichen
// * in Grossbuchstaben umwandelt
// ****************************************************************
function strtoupperFULL($str)
{
   // convert to entities
   $subject = htmlentities($str,ENT_QUOTES);
   $pattern = '/&([a-z])(uml|acute|circ';
   $pattern.= '|tilde|ring|elig|grave|slash|horn|cedil|th);/e';
   $replace = "'&'.strtoupper('\\1').'\\2'.';'";
   $result = preg_replace($pattern, $replace, $subject);
   // convert from entities back to characters
   $htmltable = get_html_translation_table(HTML_ENTITIES);
   foreach($htmltable as $key => $value) {
      $result = ereg_replace(addslashes($value),$key,$result);
   }
   return(strtoupper($result));
}

// ****************************************************************
// * Prueft den uebergebenen Alias auf Gueltigkeit.
// * Format: mindestens 1 Punkt enhalten, mind. 1 Zeichen vor und
// * 1 Zeichen nach dem Punkt, keine Sonderzeichen
// ****************************************************************
function checkalias($alias)
{
	if($anz = preg_match("/^[-a-z0-9]*[a-z0-9]{1,}\.[-a-z0-9]{1,}$/",$alias))
		return true;
	else
		return false;

}

// ****************************************************************
// * Prueft ob im LDAP ein User mit diesem Passwort existiert
// ****************************************************************
function checkldapuser($username,$password)
{
	if($connect=@ldap_connect(LDAP_SERVER))
	{ 
	    // bind to ldap connection
	    if(($bind=@ldap_bind($connect)) == false)
	    {
			print "bind:__FAILED__<br>\n";
			return false;
	    }
	
	    // search for user
	    if (($res_id = ldap_search( $connect, LDAP_BASE_DN, "uid=$username")) == false) 
	    {
			print "failure: search in LDAP-tree failed<br>";
			return false;
	    }
	
	    if (ldap_count_entries($connect, $res_id) != 1) 
	    {
			print "failure: username $username found more than once<br>\n";
			return false;
	    }
	
	    if (( $entry_id = ldap_first_entry($connect, $res_id))== false) 
	    {
			print "failur: entry of searchresult couln't be fetched<br>\n";
			return false;
	    }
	
		if (( $user_dn = ldap_get_dn($connect, $entry_id)) == false) 
		{
			print "failure: user-dn coulnd't be fetched<br>\n";
			return false;
	    }
	
	    /* Authentifizierung des User */
	    if (($link_id = @ldap_bind($connect, $user_dn, $password)) == false) 
	    {
			return false;
	    }
	
		return true;
		@ldap_close($connect);
	}
	else
	{
		// no conection to ldap server
		echo "no connection to '$ldap_server'<br>\n";
	}
	@ldap_close($connect);
	return(false);
}
?>
