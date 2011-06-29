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
require_once(dirname(__FILE__).'/basis_db.class.php');

// Auth: Benutzer des Webportals
function get_uid()
{
	if(isset($_SERVER['REMOTE_USER']))
		return (isset($_SERVER['REMOTE_USER'])?mb_strtolower(trim($_SERVER['REMOTE_USER'])):'');
	else
	{
		if(isset($_SESSION['user']))
			return $_SESSION['user'];
		else
			return manual_basic_auth();
	}
	// fuer Testzwecke
	//return 'oesi';
	//return 'pam';
}

function get_original_uid()
{
	if(isset($_SERVER['REMOTE_USER']))
		return (isset($_SERVER['REMOTE_USER'])?mb_strtolower(trim($_SERVER['REMOTE_USER'])):'');
	else
	{
		if(isset($_SESSION['user_original']))
			return $_SESSION['user_original'];
	}
}

function login_as_user($uid)
{
	$_SESSION['user']=$uid;
	return true;
}

function crlf()
{
	// doing some DOS-CRLF magic...
	$crlf="\n";
	$client=getenv("HTTP_USER_AGENT");
	if (mb_ereg('[^(]*\((.*)\)[^)]*',$client,$regs))
	{
		$os = $regs[1];
		// this looks better under WinX
		if (mb_eregi("Win",$os))
			$crlf="\r\n";
	}
	return $crlf;
}

function check_uid($uid)
{
	if(ctype_alnum($uid) && mb_strlen($uid)<=32)
		return true;
	else 
		return false;
}

function check_stsem($stsem)
{
	return preg_match('/^[WS][S][0-9]{4}$/', $stsem);	
}

/**
 * Prueft ob die ort_kurzbz ein gueltiges Format hat
 * @param $ort_kurzbz Kurzbezeichnung eines Ortes
 */
function check_ort($ort_kurzbz)
{
	if(preg_match('/^[A-Za-z0-9_.\-]{0,16}$/', $ort_kurzbz)) 
		return true;
	else
		return false;
}

function check_lektor($uid)
{
	$db = new basis_db();
	
	// uid von View 'Lektor' holen
	$sql_query="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".addslashes($uid)."'";
	//echo $sql_query;
	if($db->db_query($sql_query))
	{
		$num_rows=$db->db_num_rows();
		// Wenn kein ergebnis return 0 sonst ID
		if ($num_rows>0)
		{
			$row = $db->db_fetch_object();
			return $row->mitarbeiter_uid;
		}
		else
			return 0;
	}
	else 
		return 0;
}

function check_lektor_lehreinheit($uid, $lehreinheit_id)
{
	$db = new basis_db();
	
	// uid von View 'Lektor' holen
	$sql_query="SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter 
				WHERE mitarbeiter_uid='".addslashes($uid)."' AND lehreinheit_id = '".addslashes($lehreinheit_id)."'";
	//echo $sql_query;
	if($db->db_query($sql_query))
	{
		
		$num_rows = $db->db_num_rows();
		// Wenn kein ergebnis return 0 sonst ID
		if ($num_rows>0)
		{
			$row = $db->db_fetch_object();
			return $row->mitarbeiter_uid;
		}
		else
			return 0;
	}
	else 
		return 0;
}

function check_lektor_lehrveranstaltung($uid, $lehrveranstaltung_id, $studiensemester_kurzbz)
{
	$db = new basis_db();
	
	// uid von View 'Lektor' holen
	$sql_query="SELECT mitarbeiter_uid FROM campus.vw_lehreinheit
				WHERE mitarbeiter_uid='".addslashes($uid)."' AND 
				lehrveranstaltung_id = '".addslashes($lehrveranstaltung_id)."' AND 
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
	
	//echo $sql_query;
	if($db->db_query($sql_query))
	{
		$num_rows = $db->db_num_rows();
		// Wenn kein ergebnis return 0 sonst ID
		if ($num_rows>0)
		{
			$row = $db->db_fetch_object();
			return $row->mitarbeiter_uid;
		}
		else
			return 0;
	}
	else 
		return 0;
}

function check_student($uid)
{
	$db = new basis_db();
	
	// uid von Tabelle 'Student' holen
	$sql_query="SELECT student_uid FROM public.tbl_student WHERE student_uid='".addslashes($uid)."'";
	//echo $sql_query;
	if($db->db_query($sql_query))
	{
		$num_rows = $db->db_num_rows();
		// Wenn kein ergebnis return 0 sonst ID
		if ($num_rows>0)
		{
			$row = $db->db_fetch_object();
			return $row->student_uid;
		}
		else
			return 0;
	}
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

/**
 * Springt zum naechsten Wochentag
 *
 * @param $timestamp
 * @param $weekday Wochentag zu dem gesprungen werden soll (0-6, 0=Sonntag)
 * @return timestamp
 */
function jump_weekday($timestamp, $weekday)
{
	$wt = date("w",$timestamp);
	$jump = 7-$wt+$weekday;
	if($jump>7)
		$jump = $jump-7;
	return jump_day($timestamp, $jump);
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

function loadVariables($user)
{
	$db = new basis_db();
	
	$error_msg='';
	$num_rows=0;
	$sql_query="SELECT * FROM public.tbl_variable WHERE uid='$user'";
	if(!$db->db_query($sql_query))
		$error_msg.=$db->db_last_error().'<BR>'.$sql_query;
	else
		$num_rows=$db->db_num_rows();

	while ($row=$db->db_fetch_object())
	{
		global ${$row->name};
		${$row->name}=$row->wert;
	}
	
	if (!isset($semester_aktuell))
		if(!$db->db_query('SELECT * FROM public.tbl_studiensemester WHERE ende>now() ORDER BY start LIMIT 1'))
			$error_msg.=$db->db_last_error().'<BR>'.$sql_query;
		else
		{
			$num_rows=$db->db_num_rows();
			if ($num_rows>0)
			{
				$row=$db->db_fetch_object();
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
	
	if (!isset($kollision_student))
	{
		global $kollision_student;
		$kollision_student='false';
	}
	
	if (!isset($max_kollision))
	{
		global $max_kollision;
		$max_kollision='0';
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

function Debuglog($entry)
{
	$handle = fopen(LOG_PATH.'debug.log','a');
	fwrite($handle, $entry);
	fclose($handle);
}

// ***************************************************************
// * Liefert das Studiensemester in dem sich
// * das uebergebene Datum befindet
// * wenn sich das Datum zwischen zwei Studiensemestern befindet
// * und $naechstes=true dann wird das naechste StSem geliefert
// * wenn $naechstes=false dann wird das vorherige StSem geliefert
// ***************************************************************
function getStudiensemesterFromDatum($datum, $naechstes=true)
{
	$db = new basis_db();
	$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE";

	if($naechstes)
		$qry.= " ende>'".addslashes($datum)."' ORDER BY ende ASC ";
	else
		$qry.= " start<'".addslashes($datum)."' ORDER BY ende DESC ";

	$qry.= "LIMIT 1";

	if($db->db_query($qry))
	{
		if($row = $db->db_fetch_object())
			return $row->studiensemester_kurzbz;
		else
			return false;
	}
	else
		return false;
}

// ****************************************************************
// * Prueft den uebergebenen Alias auf Gueltigkeit.
// * Format: mindestens 1 Punkt enhalten, mind. 1 Zeichen vor und
// * 1 Zeichen nach dem Punkt, keine Sonderzeichen
// ****************************************************************
function checkalias($alias)
{
	if(preg_match("/^[-a-z0-9\_\.]*[a-z0-9]{1,}\.[-a-z0-9\_]{1,}$/",$alias))
		return true;
	else
		return false;

}

/**
 * 
 * Gibt UID zur passenden Kartennummer zurück, false im Fehlerfall 
 * @param $number
 */
function getUidFromCardNumber($number)
{
	if($connect=@ldap_connect(LDAP_SERVER))
	{
	    // bind to ldap connection
	    if(($bind=@ldap_bind($connect)) == false)
	    {
			print "bind:__FAILED__<br>\n";
			return false;
	    }

	    // search for card id
	    if (($res_id = ldap_search($connect, LDAP_BASE_DN, "departmentNumber=$number")) == false)
	    {
			print "failure: search in LDAP-tree failed<br>";
			return false;
	    }

	    // wurde keiner gefunden, versuche mit 0 davor
		if (ldap_count_entries($connect, $res_id) == 0)
	    {
	    	$number = "0".$number; 
	    	 // search for card id 0[Number]
		    if (($res_id = ldap_search($connect, LDAP_BASE_DN, "departmentNumber=$number")) == false)
		    {
				print "failure: search in LDAP-tree failed<br>";
				return false;
		    }
		    if (ldap_count_entries($connect, $res_id) == 0)
		    {
			    $number = "0".$number; 
		    	 // search for card id 00[Number]
			    if (($res_id = ldap_search($connect, LDAP_BASE_DN, "departmentNumber=$number")) == false)
			    {
					print "failure: search in LDAP-tree failed<br>";
					return false;
			    }
			    if (ldap_count_entries($connect, $res_id) == 0)
			    {
				    $number = "0".$number; 
				    echo $number; 
			    	 // search for card id 000[Number]
				    if (($res_id = ldap_search($connect, LDAP_BASE_DN, "departmentNumber=$number")) == false)
				    {
						print "failure: search in LDAP-tree failed<br>";
						return false;
				    }
				    if (ldap_count_entries($connect, $res_id) == 0)
			    	{
			    		print "failure: no person found<br>"; 
			    		return false; 
			    	}
			    }
		    }
	    }
		$info = ldap_get_entries($connect, $res_id); 
		// gibt uid der Person zurück --> [0] für erste Person 
		return($info[0]['uid'][0]); 
	    
	    @ldap_close($connect);
		return true;
	}
	else
	{
		// no conection to ldap server
		echo "no connection to '$ldap_server'<br>\n";
	}
	@ldap_close($connect);
	return(false);
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

	    @ldap_close($connect);
		return true;
	}
	else
	{
		// no conection to ldap server
		echo "no connection to '$ldap_server'<br>\n";
	}
	@ldap_close($connect);
	return(false);
}

/**
 * Berechnet die Schnittmenge zweier Strings
 *
 * @param $str1
 * @param $str2
 * @return intersected string
 */
function intersect($str1, $str2)
{
	if (mb_strlen($str1) > mb_strlen($str2))
	    $size = mb_strlen($str1);
	else
	    $size = mb_strlen($str2);
	
	$intersect = null;
	
	for ($i=0; $i<$size; $i++) 
	{
	    if (mb_substr($str1, $i, 1) == mb_substr($str2, $i, 1))
	        $intersect.= mb_substr($str1, $i, 1);	
	}
	
	return $intersect;
}

/**
 * Konvertiert Problematische Sonderzeichen in Strings fuer 
 * Accountnamen und EMail-Aliase
 *
 * @param $str
 * @return bereinigter String
 */
function convertProblemChars($str)
{
	$enc = 'UTF-8';
	
	$acentos = array(
   'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Aring;/',
   'Ae' => '/&Auml;/',
   'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&aring;/',
   'ae'=> '/&auml;/',
   'C' => '/&Ccedil;/',
   'c' => '/&ccedil;/',
   'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
   'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
   'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;/',
   'i' => '/&igrave;|&iacute;|&icirc;|&iuml;/',
   'N' => '/&Ntilde;/',
   'n' => '/&ntilde;/',
   'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;/',
   'Oe' => '/&Ouml;/',
   'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;/',
   'oe' => '/&ouml;/',
   'U' => '/&Ugrave;|&Uacute;|&Ucirc;/',
   'Ue' => '/&Uuml;/',
   'u' => '/&ugrave;|&uacute;|&ucirc;/',
   'ue' => '/&uuml;/',
   'Y' => '/&Yacute;/',
   'y' => '/&yacute;|&yuml;/',
   'a.' => '/&ordf;/',
   'o.' => '/&ordm;/',
   'ss' => '/&szlig;/'
	);

	return preg_replace($acentos, array_keys($acentos), htmlentities($str,ENT_NOQUOTES, $enc));     
}

//Ersetzt alle Problemzeichen in einem String bevor dieser als xml oder rdf ausgegeben wird
function xmlclean($string)
{
	$mixed = array(
		chr(000), //null
		chr(001), //start of heading
		chr(002), //start of text
		chr(003), //end of text
		chr(004), //end of transmission
		chr(005), //enquiry
		chr(006), //acknowledge
		chr(007), //bell
		chr(010), //backspace
		chr(013), //vertical-tab
		chr(014), //NP form feed, new page
		chr(016), //shift out
		chr(017), //shift in
		
		chr(020), //data link escape
		chr(021), //device control 1
		chr(022), //device control 2
		chr(023), //device control 3
		chr(024), //device control 4
		chr(025), //negative acknowledge
		chr(026), //synchronous idle
		chr(027), //end of trans. block
		chr(030), //cancel
		chr(031), //end of medium
		chr(032), //substitute
		chr(033), //escape
		chr(034), //file separator
		chr(035), //group separator
		chr(036), //record separator
		chr(037), //unit separator
		);
	return str_replace($mixed, "", $string);
}

/**
 * Verkuertzt einen String auf eine bestimmte laenge - beachtet werden Wortzeichen
 * @param String der die Zeichenkette enthaelt die verkuertzt werden soll
 * @param Laenge des Strings der geliefert werden soll (inkl. der Laenge des Fortsetzungszeichen)
 * @return Daten Objekt wenn ok, false im Fehlerfall
 */ 
function StringCut($str='',$len=0,$checkWortumbruch=false,$fortsetzungszeichen='...')
{
	// Plausib
	if (!is_numeric($len))
		return $str;
		
	$len=intval($len);
	if ($len  <1 )
		return $str;
		
	if (is_null($checkWortumbruch) || empty($checkWortumbruch))
		$checkWortumbruch=false;
		
	if (is_null($fortsetzungszeichen) || empty($fortsetzungszeichen) || $checkWortumbruch) 
		$fortsetzungszeichen='';
	// null oder Leerzeichen beim Fortsetzungszeichen entfernen	
	$fortsetzungszeichen=trim($fortsetzungszeichen);

	// Pruefen auf UTF-8 und Bearbeitungsfunktionen
	$utf8=check_utf8($str);
	if (!function_exists('mb_strlen')) 
		$utf8=false;
	if (!function_exists('mb_substr')) 
		$utf8=false;

	// ist der String nicht laenger als die gewuenschte Lange kann hier beendet werden	
	if ($utf8)
		$vLen=mb_strlen($str);
	else	
		$vLen=strlen($str);		

	// String ist nicht laenger als die gewuenschte leange - kpl.String retour senden	
	if ($len>=$vLen)
		return $str;
		
	if (!$checkWortumbruch)
	{
		if ($utf8)
			$vLen=$len-mb_strlen($fortsetzungszeichen,'utf-8');
		else	
			$vLen=$len- strlen($fortsetzungszeichen);			
		// die Laenge vom Fortsetzungszeichen mit berucksichtigen
		if ($utf8) // Teilstring ermitteln, und Ergebnis zuruck geben
			return mb_substr($str,0,$vLen,'utf-8').$fortsetzungszeichen;
		else // Teilstring ermitteln, und Ergebnis zuruck geben
			return substr($str,0,$vLen).$fortsetzungszeichen;
	}		
	
	
	if ($utf8) // Teilstring ermitteln, und Ergebnis zuruck geben
		$vStr=mb_substr($str,0,$len,'utf-8');
	else	// Teilstring ermitteln, und Ergebnis zuruck geben
		$vStr=substr($str,0,$len);
		
	if ($utf8)
		$vLen=mb_strlen($vStr);
	else	
		$vLen=strlen($vStr);		
	
	// Suchen letztes Leerzeichen im String
	for ($i=$vLen;$i>0;$i--)
	{
		if ($utf8)
		{
			if (mb_substr($vStr,$i,1,'utf-8')==' ' && $i>0)
				return $vStr=trim(mb_substr($str,0,$i,'utf-8'));
		}
		else
		{
			if (substr($vStr,$i,1)==' ' && $i>0)
				return $vStr=trim(substr($str,0,$i));
		}	
	}
	return $vStr;
}

/**
 * Prueft ob ein String UTF-8 Kodiert ist
 *
 * @param $str
 * @return true wenn utf8 sonst false
 */
function check_utf8($str="")
{
	$cStr=$str;
	if (strlen($cStr)>3590)
	{
		$cStr=substr($cStr,0,3590);
	}
     $stati=@preg_match("/^(
         [\x09\x0A\x0D\x20-\x7E]            # ASCII
       | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
       |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
       |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
       |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
       | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
       |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
      )*$/x",$cStr);
  
	  return $stati;
}

/**
 * DB Array Konvertieren zu XML
 *
 * @param $rows
 * @param $root
 * @return XML
 */
function array_to_xml($rows,$root='root')
{
	if (!count($rows))
		return '<'.$root.' />'."\r\n";
		
	$xml_string='';
	$xml_string.='<'.$root.'>'."\r\n";
	reset($rows);

  	for ($i=0;$i<count($rows);$i++)
	{	
		$xml_string.='<row>'."\r\n";
		$row=$rows[$i];
		@reset($row);
		while (@list( $tmp_key, $tmp_value ) = each($row) ) 
		{
			if (!is_numeric($tmp_key))
			{
				$xml_string.='<'.$tmp_key.'><![CDATA['.trim($tmp_value).']]></'.$tmp_key.'>'."\r\n";
			}	
			elseif (is_numeric($tmp_key))
			{
				$xml_string.='<row'.$tmp_key.'><![CDATA['.trim($tmp_value).']]></row'.$tmp_key.'>'."\r\n";
			}	
		}							
		$xml_string.='</row>'."\r\n";
	}	
	$xml_string.='</'.$root.'>'."\r\n";
	return $xml_string;
}

/**
 * DB Array Konvertieren zu RDF
 *
 * @param $rows
 * @param $root
 * @param $rdf_uri
 * @return RDF
 */
function array_to_rdf($rows,$root='root',$rdf_uri='rdf')
{
	$rdf_server=$_SERVER['SERVER_NAME'];
	$rdf_string='';
	if (!count($rows))
		return $rdf_string.='<'.strtoupper($rdf_uri).':'.$root.' />'."\r\n";

	$rdf_string.='<'.strtoupper($rdf_uri).':Seq rdf:about="http://'.$rdf_server.'/'.$root.'/liste">'."\r\n";

	reset($rows);
	for ($i=0;$i<count($rows);$i++)
	{           
		$rdf_string.='<'.strtoupper($rdf_uri).':li>'."\r\n";
		$rdf_string.='<'.strtoupper($rdf_uri).':Description id="'.$i.'" about="http://'.$rdf_server.'/liste'.$i.'">'."\r\n";

		$row=$rows[$i];
		reset($row);
		while (list( $tmp_key, $tmp_value ) = each($row) ) 
		{
			if (!is_numeric($tmp_key))
			{
				$rdf_string.='<'.strtoupper($rdf_uri).':'.$tmp_key.'><![CDATA['.trim($tmp_value).']]></'.strtoupper($rdf_uri).':'.$tmp_key.'>'."\r\n";
			}
		}
		$rdf_string.='</'.strtoupper($rdf_uri).':Description>'."\r\n";
		$rdf_string.='</'.strtoupper($rdf_uri).':li>'."\r\n";
	}
	$rdf_string.='</'.strtoupper($rdf_uri).':Seq>'."\r\n";
	return $rdf_string;
}

/**
 * Prueft, ob ein String nur aus ganzen Zahlen besteht
 *
 * @param $mixed
 * @return boolean
 */
function isint( $mixed )
{
    return ( preg_match( '/^\d*$/'  , $mixed) == 1 );
}

/**
 * Multibyte String Replace
 *
 * @param $needle
 * @param $replacement
 * @param $haystack
 * @return string
 */
function mb_str_replace( $needle, $replacement, $haystack ) 
{
	$needle_len = mb_strlen($needle);
	$pos = mb_strpos( $haystack, $needle);
	while (!($pos ===false)) 
	{
		$front = mb_substr( $haystack, 0, $pos );
		$back  = mb_substr( $haystack, $pos + $needle_len);
		$haystack = $front.$replacement.$back;
		$pos = mb_strpos( $haystack, $needle);
	}
	return $haystack;
}

/**
 * 
 * Prueft ob es sich um einen gueltigen Filenamen handelt
 * Filenamen mit HTML-Tags oder sonstigem Schadcode sind nicht gueltig
 * 
 * @param string $filename
 * @return boolean true wenn gueltig, sonst false
 */
function check_filename($filename)
{
	if(!preg_match('/^(\d|\w|\s|[-_.,ÄÜÖäüö!?])*$/',$filename))
		return false;
	else
		return true;
}

/**
 * Startet eine HTTP-Basic-Authentifizierung und prueft das Passwort gegen LDAP
 * @return uid wenn erfolgreich. Fehlermeldung und Scriptabbruch bei fehlerhafter Auth.
 */
function manual_basic_auth()
{
	if(!(isset($_SERVER['PHP_AUTH_USER']) && checkldapuser($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW'])))
	{
		header('WWW-Authenticate: Basic realm="Technikum-Wien"');
    	header('HTTP/1.0 401 Unauthorized');
    	echo "Ihre Zugangsdaten sind ungueltig!";
    	exit;
	}
	else
		return $_SERVER['PHP_AUTH_USER'];
}

/**
 * Liefert die aktuelle Sprache
 */
function getSprache()
{
	if(isset($_SESSION['sprache']))
	{
		$sprache=$_SESSION['sprache'];
	}
	else
	{
		if(isset($_COOKIE['sprache']))
		{
			$sprache=$_COOKIE['sprache'];
		}
		else
		{
			$sprache=DEFAULT_LANGUAGE;
		}
		setSprache($sprache);
	}
	return $sprache;
}

/**
 * Setzt die Sprache in der Session Variable und im Cookie
 * @param $sprache
 */
function setSprache($sprache)
{
	$_SESSION['sprache']=$sprache;
	setcookie('sprache',$sprache,time()+60*60*24*30,'/');
}
?>
