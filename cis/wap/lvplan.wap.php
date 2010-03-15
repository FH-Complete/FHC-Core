<?php
/* Copyright (C) 2008 Technikum-Wien
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
 
	require_once('../../config/cis.config.inc.php');
  	require_once('../../include/basis_db.class.php');
  	require_once('../../include/functions.inc.php');
	if (!$db = new basis_db())
	      die('Fehler beim Oeffnen der Datenbankverbindung');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/wochenplan.class.php');
	require_once('../../include/benutzerberechtigung.class.php');

//-------------------------------------------------------------------------------------------	
// Datum - Format
	if (!defined('constHeaderDatumZeit')) define('constHeaderDatumZeit','%A, %d %B %G  %R' );
	if (!defined('constRaumDatumZeit')) define('constRaumDatumZeit','%a, %d.%m.%Y' );
	if (!defined('constHeaderStundenplan')) define('constHeaderStundenplan','KW %W,  %B %G' );
	if (!defined('constHeaderStundenplanTag')) define('constHeaderStundenplanTag','%A<br>%d.%m.%y' );
	if (!defined('constAktuelleZeitHHMi')) define('constAktuelleZeitHHMi', date("Hi", time()));
	if (!defined('constAktuelleZeitHH')) define('constAktuelleZeitHH', date("H", time()));
//-------------------------------------------------------------------------------------------	
// Variable Init	  
	$card_id=DOMAIN;
	$card_titel=CAMPUS_NAME.' '.date('d-m-Y');
	$htmlout='';
	$day    = date('d');
	$month   = date('m');
	$year    = date('y');
	$weekday = date('w');
	
//-------------------------------------------------------------------------------------------		
// Parameter uebernehmen
	$uid=trim((isset($_REQUEST['uid']) ? $_REQUEST['uid']:''));
	$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
	$raumtyp_kurzbz=trim((isset($_REQUEST['raumtyp_kurzbz']) ? $_REQUEST['raumtyp_kurzbz']:''));
	$datum=trim((isset($_REQUEST['datum']) ? $_REQUEST['datum']:date('Ymd')));
	$datum_bl=trim((isset($_REQUEST['datum_bl']) ? $_REQUEST['datum_bl']:''));
	$work=trim((isset($_REQUEST['work']) ? $_REQUEST['work']:''));

//-------------------------------------------------------------------------------------------	
// Anwender
	if (empty($uid))
	{
		$work='freierraum';
		$htmlout.='<small>
			    		Durch Anh&#xE4;ngen von <b>?uid=[Ihre uid]</b> an die WAP URL <br/>
			    		entf&#xE4;llt die Angabe ihres Benutzernames,<br/>
						und Sie werden sofort zu Ihrem Stundenplan weitergeleitet.<br/>
			    	</small>'; 
	}	
	else
	{
	
##		$uid='el09b057';
#		$uid='_DummyLektor';
##		$uid='sommert';

		if (empty($work))
			$work='meinplan';		
		if ($user=new benutzer($uid))
		{
			$htmlout.=($user->vorname?$user->vorname.' ':'').$user->nachname;
			if (!$user->aktiv)
				$htmlout.='<br /><small>'.$uid.' ist nicht aktiv!</small>';
		}	
		else
		{
			$htmlout.=$uid.' wurde nicht gefunden!';
			$uid='';
			$work='';
		}	
	}

//-------------------------------------------------------------------------------------------	
// Anzeige des aktuellen Stundenplan eines Anwenders	
	if (!empty($uid) && $work=='meinplan' )
	{
		$row_raum=array();
		$kalenderwoche="";
		$studiengang_kz="";
		$semester="";
		$verband="";
		$gruppe="";
		$row_stunde=getAktuelleStd($db);		
		if (date('Ymd')==$datum && is_array($row_stunde) && count($row_stunde)>0)
		{
			$stunde_von=$row_stunde[0]->stunde;
			$stunde_bis=$row_stunde[0]->stunde;

			$user_array=uid_read_mitarbeiter_oder_student($db,$uid);
			// Authentifizierung
			if (check_student($uid))
				$type='student';
			elseif (check_lektor($uid))
				$type='lektor';
			else
			{
				//die("Cannot set usertype!");
				//GastAccountHack
				$type='student';
			}	
			// Stundenplan erstellen
			$stdplan=new wochenplan($type);
			// Benutzergruppe
			$stdplan->user=$type;
			// aktueller Benutzer
			$stdplan->user_uid=$uid;
			// Zusaetzliche Daten laden
			if (isset($user_array->studiengang_kz)) 
			{
			// Student
				if (! $stdplan->load_data($type,$uid,NULL,trim($user_array->studiengang_kz),trim($user_array->semester),trim($user_array->verband),trim($user_array->gruppe)) )
				{
					die($stdplan->errormsg);
				}
			}
			else
			{
			// Mitarbeiter
				if (! $stdplan->load_data($type,$uid) )
				{
					die($stdplan->errormsg);
				}
			}
			$mtag=mb_substr($datum, 6,2);
			$month=mb_substr($datum, 4,2);
			$jahr=mb_substr($datum, 0,4);
			$datum_select=@mktime(12,0,0,$month,$mtag,$jahr);
			// Stundenplan einer Woche laden
			if (! $stdplan->load_week($datum_select))
				die($stdplan->errormsg);
			$ersterTagMonat=date('m', $stdplan->datum);
			$ersterTag=date('d', $stdplan->datum);
			$year=date('Y', $stdplan->datum); 
			$weekday=date('w');	
			$gefunden=null;
			for ($ind_stdplan=0;$ind_stdplan<count($stdplan->std_plan);$ind_stdplan++)
			{
				$datum_check=@mktime(12,0,0,$ersterTagMonat,($ersterTag + $ind_stdplan ),$year);
				if (date('Ymd',$datum_select)==date('Ymd',$datum_check))
				{
					$gefunden=1 + $ind_stdplan;
					break;
				}	
			}
			$row_raum=array();
			if (!is_null($gefunden) && isset($stdplan->std_plan) && isset($stdplan->std_plan[$gefunden]) && isset($stdplan->std_plan[$gefunden][$stunde_von]) && isset($stdplan->std_plan[$gefunden][$stunde_von][0]))
				$row_raum=$stdplan->std_plan[$gefunden][$stunde_von][0];
	
				if ((is_array($row_raum) || is_object($row_raum)) && count($row_raum)>0 && isset($row_raum->reservierung) )
				{
					$htmlout.='<table>
						<tr>
							<td><strong>'.substr($row_stunde[0]->beginn,0,5).'<br />'.substr($row_stunde[0]->ende,0,5).'</strong></td>
							<td>';
						 	if ($row_raum->reservierung)
								$htmlout.=(!empty($row_raum->titel)?$row_raum->titel:(!empty($row_raum->titel) && $row_raum->lehrfach!=$row_raum->titel?$row_raum->lehrfach:'')).'<br />'.$row_raum->ort;
							else
								$htmlout.=$row_raum->lehrfach.'-'.$row_raum->lehrform.'<br />'.$row_raum->ort;
						 $htmlout.='</td>							
						</tr> 
					</table>';	
				}								 
			}	
	}
//-------------------------------------------------------------------------------------------	
// Information das es nicht das Tagesdatum ist
	$dif=$datum-date('Ymd');
##	if (date('Ymd')!=$datum)
		 $htmlout.='<br /><small>'.strftime(constHeaderDatumZeit,mktime(date('H'), date('i'), 0, $month, $day + $dif, $year)).'</small>';	
		 
	if ($datum_bl=='ret')
	{
		$day=$day + ( $dif - 1 ) ;
		$datum_ret=date('Ymd',mktime(0, 0, 0, $month, $day, $year));
		$datum_vor=date('Ymd',mktime(0, 0, 0, $month, $day + 1, $year));			
	}	
	else if ($datum_bl=='vor')
	{
		$day=$day + ( $dif + 1 );
		$datum_ret=date('Ymd',mktime(0, 0, 0, $month, $day - 1, $year));
		$datum_vor=date('Ymd',mktime(0, 0, 0, $month, $day, $year));			
	}	
	else
	{			 
		$datum_ret=date('Ymd',mktime(0, 0, 0, $month, $day - 1, $year));
		$datum_vor=date('Ymd',mktime(0, 0, 0, $month, $day + 1, $year));			
	}	
	$datum_heute=date('Ymd');
	$htmlout.='
			<br /><small>
					<a href="'.$_SERVER["PHP_SELF"].'?uid='.$uid.'&amp;work='.$work.'&amp;ort_kurzbz='.$ort_kurzbz.'&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;datum_bl=ret&amp;datum='.$datum_ret.'">&lt;&lt;</a>
					<a href="'.$_SERVER["PHP_SELF"].'?uid='.$uid.'&amp;work='.$work.'&amp;ort_kurzbz='.$ort_kurzbz.'&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;datum_bl=&amp;datum='.$datum_heute.'"><b>heute</b></a>
					<a href="'.$_SERVER["PHP_SELF"].'?uid='.$uid.'&amp;work='.$work.'&amp;ort_kurzbz='.$ort_kurzbz.'&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;datum_bl=vor&amp;datum='.$datum_vor.'">&gt;&gt;</a>
		</small>';	

//-------------------------------------------------------------------------------------------	
	switch ($work) 
	{
	
	    case 'freierraum':
		
			if (!empty($uid))
			{
				$htmlout.='<br />';
				$htmlout.='<anchor>';
				$htmlout.='mein Stundenplan';
					$htmlout.='<go href="'.$_SERVER["PHP_SELF"].'?uid='.$uid.'&amp;work=meinplan&amp;raumtyp_kurzbz=&amp;datum='.$datum.'" method="get"></go>';
				$htmlout.='</anchor>';
			}	
			if (empty($raumtyp_kurzbz))	
				$htmlout.=raum_typen($uid);
			else if (empty($ort_kurzbz))	
				$htmlout.=ort_raum_typen($uid,$raumtyp_kurzbz,$datum);
			else	
				$htmlout.=ort_plan_raum_typen($uid,$raumtyp_kurzbz,$ort_kurzbz,$datum);
	        break;
			
	    case 'meinplan':
			$htmlout.='<br />';
			$htmlout.='<anchor>';
			$htmlout.='Freie S&#xE4;le';
				$htmlout.='<go href="'.$_SERVER["PHP_SELF"].'?uid='.$uid.'&amp;work=freierraum&amp;raumtyp_kurzbz=&amp;datum='.$datum.'" method="get"></go>';
			$htmlout.='</anchor>';

			$htmlout.=getMeinStundenplan($uid,$datum);
	        break;
			
	    default:
		
	        break;
	}
	
#exit($htmlout);
#exit($htmlout);

header("Content-Type: text/vnd.wap.wml;charset=UTF-8");
echo "<?xml version=\"1.0\"?>\n";
echo "<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.1//EN\" \"http://www.wapforum.org/DTD/wml_1.1.xml\">\n";
?> 
<wml>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="-1" />

<card id="<?php echo $card_id; ?>" title="<?php echo $card_titel; ?>">
	  Benutzername: <input name="uid" size="10" maxlength="30" type="Text" value="<?php echo $uid; ?>" />
		<anchor>
	  		Weiter
	  		<go href="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get">
	    		<postfield name="uid" value="$(uid)"/>
	  		</go>
		</anchor>
		<br />
<?php 
	// Ausgabe der Information
	echo $htmlout; 
?> 
</card>
</wml>
<?php


//-------------------------------------------------------------------------------------------	
/* 
*
* @getAktuelleStd liefert die Aktuelle Stunde lt. Tabelle retour
*
* @param $db Aktuelle Datenbankverbindung
*
* @return Array der Stundentabelle
*
*/
function getAktuelleStd($db='')
{
	// Plausib
	if (!$db)
		return false;

	// Die aktive Stunde ermitteln - zum lesen welcher Raum jetzt besetzt ist - aktive Lehreinheit 
	$row_stunde=array();
	$qry="";
	$qry.="SELECT stunde, beginn, ende ";
	$qry.=" FROM lehre.tbl_stunde ";
	$qry.=" WHERE '". constAktuelleZeitHHMi."' between to_char(tbl_stunde.beginn, 'HH24MI') and  to_char(tbl_stunde.ende, 'HH24MI') ";
	$qry.=" ORDER BY stunde LIMIT 1 ; ";
	if(!$result=$db->db_query($qry))
		return 'Probleme beim lesen der Raumtyptabelle '.$db->db_last_error();
	// In einer Pause wird kein Datensatz gefunden, den letzten holen
	if (!$num_rows_stunde=$db->db_num_rows($result))
	{
		$qry="";
		$qry.="SELECT stunde, beginn, ende ";
		$qry.=" FROM lehre.tbl_stunde ";
		$qry.=" WHERE '". constAktuelleZeitHH  ."' between to_char(tbl_stunde.beginn, 'HH24') and  to_char(tbl_stunde.ende, 'HH24') ";
		$qry.=" ORDER BY stunde LIMIT 1; ";
		if(!$result=$db->db_query($qry))
			return 'Probleme beim lesen der Raumtyptabelle '.$db->db_last_error();
	}
	while($tmp_row_stunde = $db->db_fetch_object($result))
		$row_stunde[]=$tmp_row_stunde;		
	return $row_stunde;
}

//-------------------------------------------------------------------------------------------	
/* 
*
* @alle_rauminformationen Rauminformation zur Auswahl Raumtype
*
* @param $db Aktuelle Datenbankverbindung
* @param $ort_kurzbz Detailanzeige Stundenplan eines Raums Optional
* @param $datum Datum der Raumres. in Form von JJJJMMTT  Optional
* @param $row_stunde_von Stundenplan ab  Optional
* @param $row_stunde_bis Stundenplan ab Optonal

* @param $uid UserUid Optional
* @param $kalenderwoche Kalenderwoche Optional
* @param $studiengang_kz Studienkennzeichen Optional
* @param $semester Semester Optional
* @param $verband="" Verbandskennzeichen Optional
* @param $gruppe Verband-Gruppe Optional

*
* @return array Tablle der Rauminformation 
*
*/
function stundenplan_raum($db,$ort_kurzbz="",$datum="",$stunde_von,$stunde_bis=0,$uid="",$kalenderwoche="",$studiengang_kz="",$semester="",$verband="",$gruppe="")
{
	// Plausib
	if (!$db)
		return array();

	if (empty($stunde_bis))
		$stunde_bis=$stunde_von;

	//--- Raumbelegung jetzt
	$qry="";
	$qry.=' SELECT studiengang_kz,0 as "stundenplan_id",tbl_reservierung.reservierung_id,tbl_reservierung.ort_kurzbz,tbl_reservierung.titel,tbl_reservierung.semester,tbl_reservierung.studiengang_kz,tbl_reservierung.verband, tbl_reservierung.gruppe  , to_char(tbl_reservierung.datum, \'YYYYMMDD\') as "datum_jjjjmmtt", to_char(tbl_reservierung.datum, \'IW\') as "datum_woche" , tbl_stunde.beginn, tbl_stunde.ende , to_char(tbl_stunde.beginn, \'HH24:MI\') as "beginn_anzeige" , to_char(tbl_stunde.ende, \'HH24:MI\') as "ende_anzeige" , EXTRACT(EPOCH FROM tbl_reservierung.datum) as "datum_timestamp" ,tbl_stunde.stunde ';
	$qry.=' FROM campus.tbl_reservierung , lehre.tbl_stunde ';
	$qry.=" WHERE tbl_stunde.stunde=tbl_reservierung.stunde  ";
	$qry.=" and tbl_reservierung.stunde between ". trim($stunde_von) ." and ". trim($stunde_bis) ;

	if (!empty($datum))
	{
		$qry.=" and  to_char(tbl_reservierung.datum, 'YYYYMMDD') ='".addslashes(trim($datum))."' ";	
	}
	if (!empty($kalenderwoche))
	{
		$qry.=" and  to_char(tbl_reservierung.datum, 'IW') ='".addslashes(trim($kalenderwoche))."' ";	
	}	
	if (!empty($ort_kurzbz))
	{
		$qry.=" and  ort_kurzbz='".addslashes(trim($ort_kurzbz))."'  ";	
	}
	if (!empty($uid) || $uid=='0')
	{
		$qry.=" and uid='".addslashes(trim($uid))."' ";	
	}
	if (!empty($studiengang_kz) || $studiengang_kz=='0')
	{
		$qry.=" and studiengang_kz=".$studiengang_kz." ";	
	}
	if (!empty($semester) || $semester=='0')
	{
		$qry.=" and semester=".$semester." ";	
	}
	if (!empty($verband) || $verband=='0')
	{
		$qry.=" and verband='".addslashes(trim($verband))."' ";	
	}
	if (!empty($gruppe) || $gruppe=='0')
	{
		$qry.=" and gruppe=".$gruppe." ";	
	}

	$qry.=" UNION ";
	$qry.=' SELECT studiengang_kz,tbl_stundenplan.stundenplan_id,0 as "reservierung_id", tbl_stundenplan.ort_kurzbz,tbl_stundenplan.titel,tbl_stundenplan.semester,tbl_stundenplan.studiengang_kz,tbl_stundenplan.verband ,tbl_stundenplan.gruppe  , to_char(tbl_stundenplan.datum, \'YYYYMMDD\') as "datum_jjjjmmtt", to_char(tbl_stundenplan.datum, \'IW\') as "datum_woche" , tbl_stunde.beginn, tbl_stunde.ende , to_char(tbl_stunde.beginn, \'HH24:MI\') as "beginn_anzeige" , to_char(tbl_stunde.ende, \'HH24:MI\') as "ende_anzeige" , EXTRACT(EPOCH FROM tbl_stundenplan.datum) as "datum_timestamp"  ,tbl_stunde.stunde  ';
	$qry.=' FROM lehre.tbl_stundenplan , lehre.tbl_stunde  ';
	$qry.=" WHERE tbl_stunde.stunde=tbl_stundenplan.stunde ";
	$qry.=" and tbl_stundenplan.stunde between ". trim($stunde_von) ." and ". trim($stunde_bis) ;	

	if (!empty($datum))
	{
		$qry.=" and  to_char(tbl_stundenplan.datum, 'YYYYMMDD') ='".addslashes(trim($datum))."' ";	
	}	
	if (!empty($kalenderwoche))
	{
		$qry.=" and  to_char(tbl_stundenplan.datum, 'IW') ='".addslashes(trim($kalenderwoche))."' ";	
	}	
	if (!empty($ort_kurzbz))
	{
		$qry.=" and  ort_kurzbz =E'".addslashes(trim($ort_kurzbz))."' ";	
	}
	if (!empty($uid) || $uid=='0')
	{
		$qry.=" and mitarbeiter_uid='".addslashes(trim($uid))."' ";	
	}
	if (!empty($studiengang_kz) || $studiengang_kz=='0')
	{
		$qry.=" and studiengang_kz=".$studiengang_kz." ";	
	}
	if (!empty($semester) || $semester=='0')
	{
		$qry.=" and semester=".$semester." ";	
	}
	if (!empty($verband) || $verband=='0')
	{
		$qry.=" and verband=E'".addslashes(trim($verband))."' ";	
	}
	if (!empty($gruppe) || $gruppe=='0')
	{
		$qry.=" and gruppe=".$gruppe." ";	
	}
	$qry.=" ; ";
	
	$row_raum_belegt=array();
	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Stundenplan '.$db->db_last_error());
	if (!$num_rows_stunde=$db->db_num_rows($result))
		return $row_raum_belegt;
	
	while($row = $db->db_fetch_object($result))
	{
		$row_raum_belegt[]=$row;
	}
	return $row_raum_belegt;
}
//-------------------------------------------------------------------------------------------	
/* 
*
* @stundenplan_detail Stundenplan mit Lehrveranstaltungsinformationen
*
* @param $db Aktuelle Datenbankverbindung
* @param $stundenplan_id StundenplanID 
*
* @return array Tablle des Stundenplan im Detail
*
*/
function stundenplan_detail($db,$stundenplan_id)
{
	if (!$db || empty($stundenplan_id))
		return false;
	$row_stundenplan_detail=false;

	//--- Raumbelegung jetzt
	$qry="";
	$qry.=' SELECT * ';
	$qry.=' FROM campus.vw_stundenplan ';
	$qry.=" WHERE vw_stundenplan.stundenplan_id=".$stundenplan_id;
	$qry.=" ORDER BY datum,stunde  ";
	$qry.=" ; ";

	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Stundenplan '.$db->db_last_error());
	if ($num_rows_stunde=$db->db_num_rows($result))
	{
		while($row = $db->db_fetch_object($result))
			$row_stundenplan_detail=$row;
	}		
	return $row_stundenplan_detail;
}

//-------------------------------------------------------------------------------------------	
/* 
*
* @reservierung_detail Stundenplan mit Reservierungsinformationen
*
* @param $db Aktuelle Datenbankverbindung
* @param $reservierung_id ReservierungID 
*
* @return array Tablle des Reservierung im Detail
*
*/
function reservierung_detail($db,$reservierung_id)
{
	if (!$db || empty($reservierung_id))
		return false;
	$row_reservierung_detail=false;
	
	//--- Reservierung jetzt
	$qry="";
	$qry.=' SELECT * ';
	$qry.=' FROM campus.vw_reservierung ';
	$qry.=" WHERE vw_reservierung.reservierung_id=".$reservierung_id;
	$qry.=" ; ";
	
	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Stundenplan '.$db->db_last_error());
	if ($num_rows_stunde=$db->db_num_rows($result))
	{
		while($row = $db->db_fetch_object($result))
			$row_reservierung_detail=$row;
	}		
	return $row_reservierung_detail;
}

//------------------------------------------------------------------------------------------
//  BENUTZER STD.PLAN
//------------------------------------------------------------------------------------------


//-------------------------------------------------------------------------------------------	
/* 
*
* @getAktuelleStd liefert die Aktuelle Stunde lt. Tabelle retour
*
* @param $db Aktuelle Datenbankverbindung
*
* @return Array der Stundentabelle
*
*/
function getMeinStundenplan($uid='',$datum='')
{
	$htmlout="";
	if (!$db = new basis_db())
   		 return 'Fehler beim Oeffnen der Datenbankverbindung';

		$row_raum=array();
		$kalenderwoche="";
		$studiengang_kz="";
		$semester="";
		$verband="";
		$gruppe="";
		$ort_kurzbz="";

	// ------------------------------------------------------------------------------------------
	//	Stunden lesen 
	// ------------------------------------------------------------------------------------------
		$row_stunde=array();
		$qry="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
		if(!$result=$db->db_query($qry))
				die('Probleme beim lesen der Stundentabelle '.$db->db_last_error());
		$htmlout.='<table>';
// ------------------------------------------------------------------------------------------
//	Alle Termine zum User lesen
// ------------------------------------------------------------------------------------------
	$user_array=uid_read_mitarbeiter_oder_student($db,$uid);
	// Authentifizierung
	if (check_student($uid))
		$type='student';
	elseif (check_lektor($uid))
		$type='lektor';
	else
	{
		//die("Cannot set usertype!");
		//GastAccountHack
		$type='student';
	}	
	// Stundenplan erstellen
	$stdplan=new wochenplan($type);
	// Benutzergruppe
	$stdplan->user=$type;
	// aktueller Benutzer
	$stdplan->user_uid=$uid;
	// Zusaetzliche Daten laden
	if (isset($user_array->studiengang_kz)) 
	{
	// Student
		if (! $stdplan->load_data($type,$uid,NULL,trim($user_array->studiengang_kz),trim($user_array->semester),trim($user_array->verband),trim($user_array->gruppe)) )
		{
			die($stdplan->errormsg);
		}
	}
	else
	{
	// Mitarbeiter
		if (! $stdplan->load_data($type,$uid) )
		{
			die($stdplan->errormsg);
		}
	}

	$mtag=mb_substr($datum, 6,2);
	$month=mb_substr($datum, 4,2);
	$jahr=mb_substr($datum, 0,4);
	$datum=@mktime(12,0,0,$month,$mtag,$jahr);
	// Stundenplan einer Woche laden
	if (! $stdplan->load_week($datum))
		die($stdplan->errormsg);
#$htmlout.=date('Y-m-d',$stdplan->datum);	
	
	$ersterTagMonat=date('m', $stdplan->datum);
	$ersterTag=date('d', $stdplan->datum);
	$year=date('Y', $stdplan->datum); 
	$weekday=date('w');	

	$gefunden=null;
	for ($ind_stdplan=0;$ind_stdplan<count($stdplan->std_plan);$ind_stdplan++)
	{
		$datum_check=@mktime(12,0,0,$ersterTagMonat,($ersterTag + $ind_stdplan ),$year);
		if (date('Ymd',$datum)==date('Ymd',$datum_check))
		{
			$gefunden=1 + $ind_stdplan;
			break;
		}	
	}
	while($row_stunden = $db->db_fetch_object($result))
	{

			$row_stunden->time_beginn=mktime(mb_substr($row_stunden->beginn, 0,2),mb_substr($row_stunden->beginn, 3,2));
			$row_stunden->time_ende=mktime(mb_substr($row_stunden->ende, 0,2),mb_substr($row_stunden->ende, 3,2));
			$row_stunden->beginn_show=mb_substr($row_stunden->beginn, 0,5);
			$row_stunden->ende_show=mb_substr($row_stunden->ende, 0,5);

			$row_stunden->beginn_time=date('Hi',$row_stunden->time_beginn);
			$row_stunden->ende_time=date('Hi',$row_stunden->time_ende);
			$row_stunden->aktiv_time=date('Hi');

			$htmlout.='<tr>';
			if ($row_stunden->beginn_time<=$row_stunden->aktiv_time 
			&& $row_stunden->ende_time>=$row_stunden->aktiv_time )
				$htmlout.='<td><small><b>'.$row_stunden->beginn_show.'<br />'.$row_stunden->ende_show.'</b></small></td>';
			else	
				$htmlout.='<td><small>'.$row_stunden->beginn_show.'<br />'.$row_stunden->ende_show.'</small></td>';
						
			$htmlout.='<td><small>';
			
			$stunde_von=$row_stunden->stunde;
			$stunde_bis=$row_stunden->stunde;
##			$row_raum=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis,$uid,$kalenderwoche,$studiengang_kz,$semester,$verband,$gruppe);
##var_dump($stdplan->std_plan[$gefunden][$stunde_von][0]);
			$row_raum=array();
			if (!is_null($gefunden) && isset($stdplan->std_plan) && isset($stdplan->std_plan[$gefunden]) && isset($stdplan->std_plan[$gefunden][$stunde_von]) && isset($stdplan->std_plan[$gefunden][$stunde_von][0]))
				$row_raum=$stdplan->std_plan[$gefunden][$stunde_von][0];

			if ((is_array($row_raum) || is_object($row_raum)) && count($row_raum)>0 && isset($row_raum->reservierung) )
			{
				 	if ($row_raum->reservierung)
						$htmlout.=(!empty($row_raum->titel)?$row_raum->titel:(!empty($row_raum->titel) && $row_raum->lehrfach!=$row_raum->titel?$row_raum->lehrfach:'')).'<br />'.$row_raum->ort;
					else
						$htmlout.=$row_raum->lehrfach.'-'.$row_raum->lehrform.'<br />'.$row_raum->ort;
			}								 
			$htmlout.='</small></td>';							
			$htmlout.='</tr>';	
		}
		$htmlout.='</table>';	
#exit($htmlout);
	return $htmlout;	
}

//------------------------------------------------------------------------------------------
//  RAUMTYPEN 
//------------------------------------------------------------------------------------------

//-------------------------------------------------------------------------------------------	
/* 
*
* @raum_typen Liste der Raumtypen
*
* @param $uid Aktueller Anwender
*
* @return array Tablle der Raeume
*
*/
function raum_typen($uid='')
{
	$htmlout="";
// ------------------------------------------------------------------------------------------
//	Linkes Auswahlmenue fuer Raumtypen
// ------------------------------------------------------------------------------------------
	$row_ort=array(
   			array("type"=>"EDV","beschreibung"=>"EDV Säle","img"=>""),
			array("type"=>"HS","beschreibung"=> "Hörsäle","img"=>""),
			array("type"=>"SEM","beschreibung"=>"Seminarräume","img"=>""),
			array("type"=>"Lab","beschreibung"=>"Labors","img"=>""),
			array("type"=>"EXT","beschreibung"=>"Ext.Räume","img"=>""),
			array("type"=>"DIV","beschreibung"=>"Diverse","img"=>""),
			array("type"=>"UEB","beschreibung"=>"Übungsräume","img"=>"")
	# 		array("type"=>"ENERGY","beschreibung"=>"Energy","img"=>""),
		);		
	if (!is_array($row_ort) || count($row_ort)<1)
		return $htmlout;
	$htmlout.='<table>';
	for ($i=0;$i<count($row_ort);$i++)
	{
		$htmlout.='<tr>';
			$htmlout.='<td>';
				$htmlout.='<anchor>';
							$htmlout.= trim($row_ort[$i]["type"]);
							##$htmlout.=iconv('iso-8859-1','UTF-8',$row_ort[$i]["beschreibung"]);
					$htmlout.='<go href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;work=freierraum&amp;raumtyp_kurzbz='.trim($row_ort[$i]["type"]).'" method="get"></go>';
				$htmlout.='</anchor>';
			$htmlout.='</td>';
		$htmlout.='</tr>'; 
	}
	$htmlout.='</table>';
	return $htmlout;		
}	

//-------------------------------------------------------------------------------------------	
/* 
*
* @ort_raum_typen Liste der Raeume je Type 
*
* @param $uid Aktueller Anwender
* @param $raumtyp_kurzbz Raumtype
*
* @return array Tablle der Raeume
*
*/
function ort_raum_typen($uid='',$raumtyp_kurzbz='',$datum='')
{
	$htmlout="";
	if (empty($raumtyp_kurzbz))
		return raum_typen($uid);

	if (empty($datum))
		$datum=date("Ymd", mktime(0,0,0,date("m"),date("d"),date("y")));

		
	if (!$db = new basis_db())
   		 return 'Fehler beim Oeffnen der Datenbankverbindung';
		 
	$htmlout.='<table>';
		$htmlout.='<tr>';
			$htmlout.='<td>';
				$htmlout.='<anchor>';
				$htmlout.='Raumtypen';
					$htmlout.='<go href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;work=freierraum&amp;raumtyp_kurzbz=&amp;ort_kurzbz=" method="get"></go>';
				$htmlout.='</anchor>';
			$htmlout.='</td>';
		$htmlout.='</tr>';
	$htmlout.='</table>';		 
// ------------------------------------------------------------------------------------------
//	Alle Raum Typen zur Selektion 
// ------------------------------------------------------------------------------------------
	$qry="";
	$qry.=" SELECT tbl_raumtyp.raumtyp_kurzbz,tbl_raumtyp.beschreibung ";
	$qry.=" ,tbl_ortraumtyp.ort_kurzbz ";
	$qry.=" ,tbl_ort.bezeichnung ,tbl_ort.aktiv ";	
	$qry.=" FROM tbl_raumtyp , tbl_ortraumtyp , tbl_ort ";
	$qry.=" WHERE tbl_ortraumtyp.raumtyp_kurzbz=tbl_raumtyp.raumtyp_kurzbz ";
	$qry.=" AND tbl_ort.ort_kurzbz=tbl_ortraumtyp.ort_kurzbz ";
	$qry.=" AND tbl_ort.aktiv ";
	$qry.=" AND tbl_raumtyp.raumtyp_kurzbz like E'%".addslashes(trim($raumtyp_kurzbz))."%' ";
	$qry.=" order by tbl_raumtyp.raumtyp_kurzbz ,tbl_ortraumtyp.ort_kurzbz ";
	$qry.=" ; ";
	if(!$result=$db->db_query($qry))
		return 'Probleme beim lesen der Raumtyptabelle '.$db->db_last_error();

	$row_raum=array();
	if ($tmp_row_raum=$db->db_num_rows($result))
	{	
		while($tmp_row_raum = $db->db_fetch_object($result))
			$row_raum[]=$tmp_row_raum;
	}	
	
	$row_stunde=getAktuelleStd($db);		
	// Plausib Stunde
	$row_stunde[0]->stunde=(isset($row_stunde[0]->stunde)?$row_stunde[0]->stunde:0);
	$stunde_von=$row_stunde[0]->stunde;
	$stunde_bis=$row_stunde[0]->stunde;
	
	$htmlout.='<table>';
	reset($row_raum);
	for ($i=0;$i<count($row_raum);$i++)
	{
		// Default
		$farbe="frei";
		$ort_kurzbz=$row_raum[$i]->ort_kurzbz;
		$stunde_von=$row_stunde[0]->stunde;
		$stunde_bis=$row_stunde[0]->stunde;
		if ($info=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis))
		{
			$farbe="besetzt";
		}
		
		$ort_kurzbz=$row_raum[$i]->ort_kurzbz;
		$stunde_von=$row_stunde[0]->stunde;
		$stunde_bis=$row_stunde[0]->stunde + 1;
		if (!$info=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis))
		{
			$farbe="2 Einheiten frei";
		}	
		
		$htmlout.='<tr>';
			$htmlout.='<td>';
			$htmlout.='<anchor>';
				$htmlout.=trim($ort_kurzbz);
				$htmlout.='<go href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;work=freierraum&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;ort_kurzbz='.$ort_kurzbz.'" method="get"></go>';
			$htmlout.='</anchor>';
			$htmlout.='</td><td>'.$farbe.'</td>';
		$htmlout.='</tr>';
	}		
	$htmlout.='</table>';
	return $htmlout;		
}	
//-------------------------------------------------------------------------------------------	
/* 
*
* @ort_plan_raum_typen Stundenplan zum Raum
*
* @param $uid Aktueller Anwender
* @param $raumtyp_kurzbz Raumtype 
* @param $ort_kurzbz Ortsbezeichnung
* @param $datum Datum
*
* @return array Tablle des Reservierung im Detail
*
*/
function ort_plan_raum_typen($uid='',$raumtyp_kurzbz='',$ort_kurzbz='',$datum='')
{
	$htmlout="";
	if (empty($raumtyp_kurzbz))
		return raum_typen($uid);

	if (!$db = new basis_db())
   		 return 'Fehler beim Oeffnen der Datenbankverbindung';
		 
	$htmlout.='<table>';
		$htmlout.='<tr>';
			$htmlout.='<td>';
			$htmlout.='<anchor>';
			$htmlout.=$raumtyp_kurzbz;
				$htmlout.='<go href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;work=freierraum&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;ort_kurzbz=&amp;datum='.$datum.'" method="get"></go>';
			$htmlout.='</anchor>';
			$htmlout.='</td>';
		$htmlout.='</tr>';
		
		$htmlout.='<tr>';
			$htmlout.='<td>';
			$htmlout.='<anchor>';
			$htmlout.=$ort_kurzbz;
				$htmlout.='<go href="'.$_SERVER['PHP_SELF'].'?uid='.$uid.'&amp;work=freierraum&amp;raumtyp_kurzbz='.$raumtyp_kurzbz.'&amp;ort_kurzbz='.$ort_kurzbz.'&amp;datum='.$datum.'" method="get"></go>';
			$htmlout.='</anchor>';
			$htmlout.='</td>';
		$htmlout.='</tr>';
		
		
	$htmlout.='</table>';		
	
	$row_stunde=array();
	$qry="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
	if(!$result=$db->db_query($qry))
			die('Probleme beim lesen der Stundentabelle '.$db->db_last_error());
	$num_rows_stunde=$db->db_num_rows();
	$htmlout.='<table>';
	
	
	
	while($row_stunden = $db->db_fetch_object($result))
	{
		$row_stunden->time_beginn=mktime(mb_substr($row_stunden->beginn, 0,2),mb_substr($row_stunden->beginn, 3,2));
		$row_stunden->time_ende=mktime(mb_substr($row_stunden->ende, 0,2),mb_substr($row_stunden->ende, 3,2));
		$row_stunden->beginn_show=mb_substr($row_stunden->beginn, 0,5);
		$row_stunden->ende_show=mb_substr($row_stunden->ende, 0,5);

		$row_stunden->beginn_time=date('Hi',$row_stunden->time_beginn);
		$row_stunden->ende_time=date('Hi',$row_stunden->time_ende);
		$row_stunden->aktiv_time=date('Hi');

		$htmlout.='<tr>';
		if ($row_stunden->beginn_time<=$row_stunden->aktiv_time 
		&& $row_stunden->ende_time>=$row_stunden->aktiv_time )
			$htmlout.='<td><small><b>'.$row_stunden->beginn_show.'<br />'.$row_stunden->ende_show.'</b></small></td>';
		else	
			$htmlout.='<td><small>'.$row_stunden->beginn_show.'<br />'.$row_stunden->ende_show.'</small></td>';

		$htmlout.='<td><small>';
		$row_raum=array();
		$kalenderwoche="";
		$studiengang_kz="";
		$semester="";
		$verband="";
		$gruppe="";
		$uids="";
		$stunde_von=$row_stunden->stunde;
		$stunde_bis=$row_stunden->stunde;
		$row_raum=stundenplan_raum($db,$ort_kurzbz,$datum,$stunde_von,$stunde_bis,$uids,$kalenderwoche,$studiengang_kz,$semester,$verband,$gruppe);
		if (is_array($row_raum) && count($row_raum)>0)
		{
				 for ($i=0;$i<count($row_raum);$i++)
				 {
					 	if ($row_raum[$i]->stundenplan_id)
						{
							if ($row_info=stundenplan_detail($db,$row_raum[$i]->stundenplan_id))							
								$htmlout.=$row_info->lehrfach.'-'.$row_info->lehrform.'<br />'.$row_info->ort_kurzbz;
						}	
						else if ($row_raum[$i]->reservierung_id)
						{
							if ($row_info=reservierung_detail($db,$row_raum[$i]->reservierung_id))
								$htmlout.=(!empty($row_info->titel)?$row_info->titel:$row_info->beschreibung).'<br />'.$row_info->ort_kurzbz;
						}	
						break;
				 }		
		}								 
		$htmlout.='</small></td>';	
		$htmlout.='</tr>';	
	}
	$htmlout.='</table>';	
	return $htmlout;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @uid_read_mitarbeiter_oder_student Daten zum Mitarbeiter oder Studenten
*
* @param $db Aktuelle Datenbankverbindung
* @param $uid Userkurzzeichen
*
* @return Array der User Inormationen wenn User gefunden wurde ansonst false
*
*/
function uid_read_mitarbeiter_oder_student($db,$uid)
{	
	$rows=array();
	// Plausib
	if (!$db)
		return $rows;

	// Pruefen ob Mitarbeiter
	$qry="SELECT uid,person_id,anrede,titelpre,vorname,vornamen,nachname,aktiv FROM campus.vw_mitarbeiter where uid='".addslashes(trim($uid))."' LIMIT 1 ; ";
	if(!$results=$db->db_query($qry))
		die('Probleme beim lesen der Mitarbeiter '.$db->db_last_error());
		
	if ($num_rows_stunde=$db->db_num_rows($results))
	{
		while($rows = $db->db_fetch_object($results))
		{
			$rows->name='';
			$rows->name.=(isset($rows->anrede)?trim($rows->anrede).' ':'');
			$rows->name.=(isset($rows->titelpre)?trim($rows->titelpre).' ':'');
			$rows->name.=(isset($rows->vorname)?trim($rows->vorname).' ':'');
			$rows->name.=(isset($rows->vornamen)?trim($rows->vornamen).' ':'');
			$rows->name.=(isset($rows->nachname)?trim($rows->nachname).' ':'');
			return $rows;
		}
	}
	
	// Wenn kein Mitarbeiter pruefen ob Student
	$qry="SELECT  uid,person_id,anrede,titelpre,vorname,vornamen,nachname,aktiv,studiengang_kz,semester,verband,gruppe  FROM campus.vw_student where uid='".addslashes(trim($uid))."' LIMIT 1 ; ";
	if(!$result=$db->db_query($qry))
		die('Probleme beim lesen der Studenten '.$db->db_last_error());
	if ($num_rows_stunde=$db->db_num_rows($result))
	{
		while($rows = $db->db_fetch_object($result))
		{
			$rows->name='';
			$rows->name.=(isset($rows->anrede)?trim($rows->anrede).' ':'');
			$rows->name.=(isset($rows->titelpre)?trim($rows->titelpre).' ':'');
			$rows->name.=(isset($rows->vorname)?trim($rows->vorname).' ':'');
			$rows->name.=(isset($rows->vornamen)?trim($rows->vornamen).' ':'');
			$rows->name.=(isset($rows->nachname)?trim($rows->nachname).' ':'');
			return $rows;
		}
	}
	// Daten gefunden wurden ist nicht mehr der Initialwert False als Returnparameter vorhanden
	return $rows;
}
?>