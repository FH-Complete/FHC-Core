<?php
/* Copyright (C) 2007 Technikum-Wien
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
/****************************************************************************
 * @class 			Stundenplan
 * @author	 		Christian Paminger
 * @date	 		2001/8/21
 * @version			$Revision: 1.3 $
 * Update: 			10.11.2004 von Christian Paminger
 * @brief  			Klasse zm Berechnen und Anzeigen des Stundenplans.
 * Abhaengig:	 	von functions.inc.php
 *****************************************************************************/
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/lehrstunde.class.php');
require_once(dirname(__FILE__).'/ferien.class.php');
require_once(dirname(__FILE__).'/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/datum.class.php');

class wochenplan extends basis_db
{
	public $conn;			// @brief Connection zur Datenbank
	public $crlf;			// @brief Return Linefeed
	public $type; 			// @brief Typ des Plans (Student, Lektor, Verband, Ort)
	public $user;			// @brief Benutzergruppe
	public $user_uid;		// @brief id in der Datenbank des Benutzers
	public $link;			// @brief Link auf eigene Seite
	public $kal_link;		// @brief Link auf den kalender

	public $stg_kz;		// @brief Kennzahl des Studiengangs
	public $stg_bez;		// @brief Bezeichnung Studiengang
	public $stg_kurzbz;	// @brief Kurzbezeichnung Studiengang
	public $stg_kurzbzlang;// @brief lange Kurzbezeichnung Studiengang
	public $sem;			// @brief Semester
	public $ver;			// @brief Verband (A,B,C,...)
	public $grp;			// @brief Gruppe (1,2)

	public $pers_uid;		// @brief Account Name der Person (PK)
	public $pers_titelpost;	// @brief Titel der Person
	public $pers_titelpre;	// @brief Titel der Person
	public $pers_nachname;	// @brief Personendaten
	public $pers_vorname;	// @brief Personendaten
	public $pers_vornamen;	// @brief Personendaten

	public $ort_kurzbz;	// @brief Ort PK
	public $ort_bezeichnung;
	public $ort_planbezeichnung;
	public $ort_ausstattung;

	public $gruppe_kurzbz;
	public $gruppe_bezeichnung;

	public $datum;			// @brief Datum des Montags der zu zeichnenden Woche
	public $datum_nextweek;
	public $datum_next4week;
	public $datum_prevweek;
	public $datum_prev4week;
	public $datum_begin;
	public $datum_end;
	public $kalenderwoche;

	public $studiensemester_now;
	public $studiensemester_next;

	public $std_plan;
	public $stunde;

	public $wochenplan;
	public $errormsg;

	/**
	 * Konstruktor
	 * @param $type
	 */
	public function __construct($type)
	{
		parent::__construct();
		
		$this->type=$type;		
		
		$this->link='stpl_week.php?type='.$type;
		$this->kal_link='stpl_kalender.php?type='.$type;
		$this->datum=mktime();
		$this->init_stdplan();
		$this->crlf=crlf();
	}

	/**
	 * initialisiert den Studenplan
	 *
	 */
	public function init_stdplan()
	{
		//Stundenplan Array initialisieren (Anzahl auf 0 setzten)
		unset($this->std_plan);
		for ($i=1; $i<=TAGE_PRO_WOCHE; $i++)
			for ($j=0; $j<20; $j++)
			{
				$this->std_plan[$i][$j][0]->anz=0;
				$this->std_plan[$i][$j][0]->unr=0;
			}
	}

	/**
	 * Funktion load_data ladet alle Zusatzinformationen fuer die Darstellung
	 * und ueberprueft die Daten
	 *
	 * @param $type
	 * @param $uid
	 * @param $ort_kurzbz
	 * @param $studiengang_kz
	 * @param $sem
	 * @param $ver
	 * @param $grp
	 * @param $gruppe
	 */
	public function load_data($type, $uid, $ort_kurzbz=NULL, $studiengang_kz=NULL, $sem=NULL, $ver=NULL, $grp=NULL, $gruppe=NULL)
	{
		///////////////////////////////////////////////////////////////////////
		// Parameter Checken
		// Typ des Stundenplans
		if ($type=='student' || $type=='lektor' || $type=='verband' || $type=='gruppe' || $type=='ort')
			$this->type=$type;
		else
		{
			$this->errormsg='Error: type is not defined!';
			return false;
		}
		// Person
		if (($type=='student' || $type=='lektor') && ($uid==NULL || $uid==''))
		{
			$this->errormsg='Fehler: uid der Person ist nicht gesetzt';
			return false;
		}
		else
			$this->pers_uid=$uid;

		// Ort
		if ($type=='ort' && $ort_kurzbz==NULL)
		{
			$this->errormsg='Fehler: Kurzbezeichnung des Orts ist nicht gesetzt';
			return false;
		}
		elseif ($type=='ort')
			$this->ort_kurzbz=$ort_kurzbz;
		else
			$this->ort_kurzbz='';

		// Lehrverband
		if ($type=='verband' && $studiengang_kz==NULL)
		{
			$this->errormsg='Fehler: Kennzahl des Studiengangs ist nicht gesetzt';
			return false;
		}
		elseif($type=='verband')
		{
			$this->stg_kz=$studiengang_kz;
			$this->sem=$sem;
			$this->ver=$ver;
			$this->grp=$grp;
		}

		// Einheit
		if ($type=='gruppe' && $gruppe==NULL)
		{
			$this->errormsg='Fehler: Kurzbezeichnung der Gruppe ist nicht gesetzt';
			return false;
		}
		elseif ($type=='gruppe')
			$this->gruppe_kurzbz=$gruppe;


		///////////////////////////////////////////////////////////////////////
		// Zusaetzliche Daten ermitteln
		//personendaten
		if ($this->type=='student' || $this->type=='lektor')
		{
			$this->link.='&pers_uid='.$this->pers_uid;	//Link erweitern
			if ($this->type=='student')
				$sql_query="SELECT uid, titelpre, titelpost, nachname, vorname, vornamen, studiengang_kz, semester, verband, gruppe FROM campus.vw_student WHERE uid='$this->pers_uid'";
			else
				$sql_query="SELECT uid, titelpre, titelpost, nachname, vorname, vornamen FROM campus.vw_mitarbeiter WHERE uid='$this->pers_uid'";
			//echo $sql_query;
			if (!$this->db_query($sql_query))
			{
				$this->errormsg=$this->db_last_error();
				return false;
			}
			if($row = $this->db_fetch_object())
			{
				$this->pers_uid = $row->uid;
				$this->pers_titelpre = $row->titelpre;
				$this->pers_titelpost = $row->titelpost;
				$this->pers_nachname = $row->nachname;
				$this->pers_vorname =$row->vorname;
				$this->pers_vornamen = $row->vornamen;
				
				if ($this->type=='student')
				{
					$this->stg_kz = $row->studiengang_kz;
					$this->sem = $row->semester;
					$this->ver = $row->verband;
					$this->grp = $row->gruppe;
				}
			}
		}

		//ortdaten ermitteln
		if ($this->type=='ort')
		{
			$sql_query="SELECT bezeichnung, ort_kurzbz, planbezeichnung, ausstattung FROM public.tbl_ort WHERE ort_kurzbz='$this->ort_kurzbz'";
			//echo $sql_query;
			if (!$this->db_query($sql_query))
				$this->errormsg=$this->db_last_error();
			if($row = $this->db_fetch_object())
			{
				$this->ort_bezeichnung = $row->bezeichnung;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->ort_planbezeichnung = $row->planbezeichnung;
				$this->ort_ausstattung = $row->ausstattung;
				$this->link.='&ort_kurzbz='.$this->ort_kurzbz;	//Link erweitern
			}
		}

		// Studiengangsdaten ermitteln
		if ($this->type=='student' || $this->type=='verband')
		{
			$sql_query="SELECT bezeichnung, kurzbz, kurzbzlang, typ FROM public.tbl_studiengang WHERE studiengang_kz=$this->stg_kz";
			//echo $sql_query;
			if(!($this->db_query($sql_query)))
				die($this->db_last_error());
			if($row = $this->db_fetch_object())
			{
				$this->stg_bez = $row->bezeichnung;
				$this->stg_kurzbz = $row->typ.$row->kurzbz;
				$this->stg_kurzbzlang = $row->kurzbzlang;
			}
		}

		// Stundentafel abfragen
		$sql_query="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		$this->stunde = $this->db_result;

		// Studiensemesterdaten ermitteln
		$sql_query="SELECT * FROM public.tbl_studiensemester WHERE now()<ende ORDER BY start LIMIT 2";
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		else
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiensemester_now->name=$row->studiensemester_kurzbz;
				$this->studiensemester_now->start=mktime(0,0,0,mb_substr($row->start,5,2),mb_substr($row->start,8,2),mb_substr($row->start,0,4));
				$this->studiensemester_now->ende=mktime(0,0,0,mb_substr($row->ende,5,2),mb_substr($row->ende,8,2),mb_substr($row->ende,0,4));#
			}
			if($row = $this->db_fetch_object())
			{
				$this->studiensemester_next->name=$row->studiensemester_kurzbz;
				$this->studiensemester_next->start=mktime(0,0,0,mb_substr($row->start,5,2),mb_substr($row->start,8,2),mb_substr($row->start,0,4));
				$this->studiensemester_next->ende=mktime(0,0,0,mb_substr($row->ende,5,2),mb_substr($row->ende,8,2),mb_substr($row->ende,0,4));
			}
		}
		return true;
	}

	/**
	 * Funktion load_week ladet die Stundenplandaten einer Woche
	 *
	 * @param datum Datum eines Tages in der angeforderten Woche
	 * @return true oder false
	 */
	public function load_week($datum, $stpl_view='stundenplan')
	{
		// Pruefung der Attribute
		if (!isset($this->type))
		{
			$this->errormsg='$type is not set in stundenplan.load_week!';
			return false;
		}

		//Kalenderdaten setzen
		$this->datum=montag($datum);
		$this->datum_begin=$this->datum;
		$this->datum_end=jump_week($this->datum_begin, 1);
		$this->datum_nextweek=$this->datum_end;
		$this->datum_prevweek=jump_week($this->datum_begin, -1);
		$this->datum_next4week=jump_week($this->datum_begin, 4);
		$this->datum_prev4week=jump_week($this->datum_begin, -4);
		// Formatieren fuer Datenbankabfragen
		$this->datum_begin=date("Y-m-d",$this->datum_begin);
		$this->datum_end=date("Y-m-d",$this->datum_end);
		$this->kalenderwoche=kalenderwoche($this->datum);

		// Stundenplandaten ermittlen
		$this->wochenplan=new lehrstunde();
		$anz=$this->wochenplan->load_lehrstunden($this->type,$this->datum_begin,$this->datum_end,$this->pers_uid,$this->ort_kurzbz,$this->stg_kz,$this->sem,$this->ver,$this->grp,$this->gruppe_kurzbz, $stpl_view);
		if ($anz<0)
		{
			$this->errormsg=$this->wochenplan->errormsg;
			return false;
		}

		// Stundenplandaten aufbereiten
		for($i=0;$i<$anz;$i++)
		{
			$idx=0;
			$mtag=mb_substr($this->wochenplan->lehrstunden[$i]->datum, 8,2);
			$month=mb_substr($this->wochenplan->lehrstunden[$i]->datum, 5,2);
			$jahr=mb_substr($this->wochenplan->lehrstunden[$i]->datum, 0,4);
			$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
			if ($tag==0)
				$tag=7; //Sonntag
			//echo $tag.':'.$this->wochenplan->lehrstunden[$i]->datum.'<BR>';
			$stunde=$this->wochenplan->lehrstunden[$i]->stunde;
			// naechste freie Stelle im Array suchen
			while (isset($this->std_plan[$tag][$stunde][$idx]->lektor_uid))
				$idx++;
			//echo $idx.'<BR>';
			$this->std_plan[$tag][$stunde][$idx]->unr=$this->wochenplan->lehrstunden[$i]->unr;
			$this->std_plan[$tag][$stunde][$idx]->reservierung=$this->wochenplan->lehrstunden[$i]->reservierung;
			if ($this->wochenplan->lehrstunden[$idx]->reservierung)
				$this->std_plan[$tag][$stunde][$idx]->lehrfach=$this->wochenplan->lehrstunden[$i]->titel;
			else
			{
				$this->std_plan[$tag][$stunde][$idx]->lehrfach=$this->wochenplan->lehrstunden[$i]->lehrfach;
				$this->std_plan[$tag][$stunde][$idx]->lehrform=$this->wochenplan->lehrstunden[$i]->lehrform;
				$this->std_plan[$tag][$stunde][$idx]->lehrfach_id=$this->wochenplan->lehrstunden[$i]->lehrfach_id;
				$this->std_plan[$tag][$stunde][$idx]->farbe=$this->wochenplan->lehrstunden[$i]->farbe;
				//$this->std_plan[$tag][$stunde][$idx]->titel=$this->wochenplan->lehrstunden[$i]->titel;
			}
			$this->std_plan[$tag][$stunde][$idx]->titel=$this->wochenplan->lehrstunden[$i]->titel;
			$this->std_plan[$tag][$stunde][$idx]->stundenplan_id=$this->wochenplan->lehrstunden[$i]->stundenplan_id;
			$this->std_plan[$tag][$stunde][$idx]->lektor_uid=$this->wochenplan->lehrstunden[$i]->lektor_uid;
			$this->std_plan[$tag][$stunde][$idx]->lektor=$this->wochenplan->lehrstunden[$i]->lektor_kurzbz;
			$this->std_plan[$tag][$stunde][$idx]->ort=$this->wochenplan->lehrstunden[$i]->ort_kurzbz;
			$this->std_plan[$tag][$stunde][$idx]->stg=$this->wochenplan->lehrstunden[$i]->studiengang;
			$this->std_plan[$tag][$stunde][$idx]->stg_kz=$this->wochenplan->lehrstunden[$i]->studiengang_kz;
			$this->std_plan[$tag][$stunde][$idx]->sem=$this->wochenplan->lehrstunden[$i]->sem;
			$this->std_plan[$tag][$stunde][$idx]->ver=$this->wochenplan->lehrstunden[$i]->ver;
			$this->std_plan[$tag][$stunde][$idx]->grp=$this->wochenplan->lehrstunden[$i]->grp;
			$this->std_plan[$tag][$stunde][$idx]->gruppe_kurzbz=$this->wochenplan->lehrstunden[$i]->gruppe_kurzbz;
			$this->std_plan[$tag][$stunde][$idx]->anmerkung=$this->wochenplan->lehrstunden[$i]->anmerkung;
			$this->std_plan[$tag][$stunde][$idx]->updateamum=$this->wochenplan->lehrstunden[$i]->updateamum;
			$this->std_plan[$tag][$stunde][$idx]->updatevon=$this->wochenplan->lehrstunden[$i]->updatevon;
			//echo $tag.' '.$stunde.' '.$this->std_plan[$tag][$stunde][$idx]->lektor_uid.'<br>';
		}
		unset($this->wochenplan);
		return true;
	}

	/**
	 * Schreibt den Stundenplan Header im HTML-Format
	 *
	 */
	public function draw_header()
	{
		echo '<TABLE width="100%" bgcolor="#EEEEEE" border="0" cellspacing="0">'.$this->crlf;
		echo '	<TR>'.$this->crlf;
		echo '		<TD valign="bottom">'.$this->crlf;
		echo '			<P valign="top">';
		if ($this->type=='student' || $this->type=='lektor')
			echo '<strong>Person: </strong>'.$this->pers_titelpre.' '.$this->pers_vorname.' '.$this->pers_nachname.' '.$this->pers_titelpost.' - '.$this->pers_uid.'<br>';
		if ($this->type=='student' || $this->type=='verband')
		{
			echo '<strong>Studiengang: </strong>'.$this->stg_kurzbzlang.' - '.$this->stg_bez.'<br>';
			echo 'Semester: '.$this->sem.'<br>';
			if ($this->ver!='0' && $this->ver!='' && $this->ver!=null)
				echo 'Verband: '.$this->ver.'<br>';
			if ($this->grp!='0' && $this->grp!='' && $this->grp!=null)
				echo 'Gruppe: '.$this->grp.'<br>';
			$this->link.='&stg_kz='.$this->stg_kz.'&sem='.$this->sem.'&ver='.$this->ver.'&grp='.$this->grp;
		}
		if ($this->type=='ort')
			echo '<strong>Ort: </strong>'.$this->ort_kurzbz.' - '.$this->ort_bezeichnung.' - '.$this->ort_planbezeichnung.'<br>'.$this->ort_ausstattung.'<br>';
		echo '</P>'.$this->crlf;
		echo '			<div valign="bottom" align="center">'.$this->crlf;

		//Kalender
		$this->kal_link.='&pers_uid='.$this->pers_uid.'&ort_kurzbz='.$this->ort_kurzbz.'&stg_kz='.$this->stg_kz.'&sem='.$this->sem.'&ver='.$this->ver.'&grp='.$this->grp.'&gruppe_kurzbz='.$this->gruppe_kurzbz;
		//global $kalender_begin_ws, $kalender_ende_ws, $kalender_begin_ss, $kalender_ende_ss;
		$kal_link_ws=$this->kal_link.'&begin='.$this->studiensemester_now->start.'&ende='.$this->studiensemester_now->ende;
		$kal_link_ss=$this->kal_link.'&begin='.$this->studiensemester_next->start.'&ende='.$this->studiensemester_next->ende;
		echo '				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Kalender:&nbsp;&nbsp;&nbsp;&nbsp;</strong>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=html" target="_blank" title="HTML">'.$this->studiensemester_now->name.'</A>&nbsp;'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=html" target="_blank" title="HTML"><IMG src="../../../skin/images/website.png" height="24" alt="HTML" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=csv" title="CSV"><IMG src="../../../skin/images/csv.png" height="24" alt="CSV" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=csv&target=outlook" title="CSV-Outlook"><IMG src="../../../skin/images/outlook.png" height="24" alt="CSV-Outlook" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=ical&version=1&target=ical" title="iCal Version 1.0"><IMG src="../../../skin/images/vcal_v1.png" height="24" alt="vCal Version 1.0" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=ical&version=2&target=ical" title="iCal Version 2.0"><IMG src="../../../skin/images/vcal_v2.png" height="24" alt="vCal Version 2.0" border="0"></A>'.$this->crlf;
		echo '				&nbsp;&nbsp;&nbsp;&nbsp;<A href="'.$kal_link_ss.'&format=html" target="_blank" title="HTML">'.$this->studiensemester_next->name.'</A>&nbsp;'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=html" target="_blank" title="HTML"><IMG src="../../../skin/images/website.png" height="24" alt="HTML" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=csv" title="CSV"><IMG src="../../../skin/images/csv.png" height="24" alt="CSV" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=csv&target=outlook" title="CSV-Outlook"><IMG src="../../../skin/images/outlook.png" height="24" alt="CSV-Outlook" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=ical&version=1&target=ical" title="iCal Version 1.0"><IMG src="../../../skin/images/vcal_v1.png" height="24" alt="iCal Version 1.0" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=ical&version=2&target=ical" title="iCal Version 2.0"><IMG src="../../../skin/images/vcal_v2.png" height="24" alt="iCal Version 2.0" border="0"></A>'.$this->crlf;
		echo '			</div>'.$this->crlf;
		echo '		</TD>'.$this->crlf;

    	// Kalenderjump
		echo '		<TD align="right" valign="top">'.$this->crlf;
		jahreskalenderjump($this->link);
		echo '		</TD>'.$this->crlf;
		echo '	</TR>'.$this->crlf;
		echo '</TABLE><HR>'.$this->crlf.$this->crlf;

		// Jump Wochenweise
		if ($this->type=='verband')
			$link_parameter='&stg_kz='.$this->stg_kz.'&sem='.$this->sem.'&ver='.$this->ver.'&grp='.$this->grp;
		if ($this->type=='student' || $this->type=='lektor')
			$link_parameter='&pers_uid='.$this->pers_uid;

		// Ort Jump
		if ($this->type=='ort')
		{
			// Orte abfragen
			$sql_query="SELECT * FROM public.tbl_ort WHERE aktiv AND lehre ORDER BY ort_kurzbz";
			if(!$this->db_query($sql_query))
				die($this->db_last_error());
			$num_rows_ort=$this->db_num_rows();

			// vorigen Ort bestimmen
			for ($i=0;$i<($num_rows_ort-1);$i++)
			{
				$row = $this->db_fetch_object(null,$i+1);
				
				if ($row->ort_kurzbz==$this->ort_kurzbz)
					$prev_ort=$this->db_fetch_object(null,$i);
			}
			// naechsten Ort bestimmen
			for ($i=1;$i<$num_rows_ort;$i++)
			{
				$row = $this->db_fetch_object(null, $i-1);
				if ($row->ort_kurzbz==$this->ort_kurzbz)
					$next_ort=$this->db_fetch_object(null,$i);
			}

			// Ort Jump
			echo '<FORM align="center" name="AuswahlOrt" action="stpl_week.php">'.$this->crlf;
			echo '	<p align="center">'.$this->crlf;
			//$datum=mktime($this->datum[hours], $this->datum[minutes], $this->datum[seconds], $this->datum[mon], $this->datum[mday], $this->datum[year]);
			if (isset($prev_ort))
			{
				echo '		<a href="stpl_week.php?type='.$this->type.'&datum='.$this->datum.'&ort_kurzbz='.$prev_ort->ort_kurzbz.'">'.$this->crlf;
				echo '			<img src="../../../skin/images/left.gif" border="0" title="'.$prev_ort->ort_kurzbz.'" />'.$this->crlf;
				echo '		</a>'.$this->crlf;
			}
			echo "		<SELECT name=\"select\" onChange=\"MM_jumpMenu('self',this,0)\" class=\"xxxs_black\">".$this->crlf;
			for ($i=0;$i<$num_rows_ort;$i++)
			{
				$row=$this->db_fetch_object (null, $i);
				echo '			<OPTION value="stpl_week.php?type=ort&ort_kurzbz='.$row->ort_kurzbz.'&datum='.$this->datum.'"';
				if ($row->ort_kurzbz==$this->ort_kurzbz)
					echo ' selected ';
				echo ">$row->ort_kurzbz</option>".$this->crlf;
			}
			echo '		</SELECT>'.$this->crlf;
			if (isset($next_ort))
			{
				echo '		<a href="stpl_week.php?type='.$this->type.'&datum='.$this->datum.'&ort_kurzbz='.$next_ort->ort_kurzbz.'">'.$this->crlf;
				echo '			<img src="../../../skin/images/right.gif" border="0" title="'.$next_ort->ort_kurzbz.'">'.$this->crlf;
				echo '		</a>'.$this->crlf;
			}
			echo '	</p>';
			$link_parameter='&ort_kurzbz='.$this->ort_kurzbz;
		}
		echo '	<p align="center">';
		// 4 Wochen zurueck
		echo '		<a href="stpl_week.php?type='.$this->type.$link_parameter.'&datum='.$this->datum_prev4week.'">'.$this->crlf;
		echo '			<img src="../../../skin/images/moreleft.gif" border="0" title="4 Wochen zurueck">'.$this->crlf;
		echo '		</a>';
		// 1 Woche zurueck
		echo '<a href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_prevweek;
		echo '"><img src="../../../skin/images/left.gif" border="0"></a> KW '.$this->kalenderwoche;
		// 1 Woche nach vor
		echo '<a href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_nextweek;
		echo '"><img src="../../../skin/images/right.gif" border="0"></a>';
		// 4 Wochen nach vor
		echo '<a href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_next4week;
		echo '"><img src="../../../skin/images/moreright.gif" border="0"></a>';
        echo '</p>';
        return true;
	}

	/**
	 * Zeichnen der Stundenplanwoche in HTML
	 */
	public function draw_week($raumres, $user_uid='')
	{
		$o_datum=new datum();
		// Stundentafel abfragen
		$sql_query="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		$result_stunde = $this->db_result;
		$num_rows_stunde = $this->db_num_rows($result_stunde);
 
		// Formularbeginn wenn Lektor
		if ($this->user=='lektor' && $this->type=='ort')
			echo '<form name="reserve" method="post" action="stpl_week.php">'.$this->crlf;

		//Tabelle zeichnen
		echo '	<table class="stdplan" width="100%" border="1" cellpadding="0" cellspacing="0" name="Stundenplantabelle" align="center">'.$this->crlf;
		// Kopfzeile darstellen
	  	echo '		<tr>'.$this->crlf;
		echo '			<th align="right">Stunde&nbsp;<br>Beginn&nbsp;<br>Ende&nbsp;</th>'.$this->crlf;
		for ($i=0;$i<$num_rows_stunde; $i++)
		{
			$row = $this->db_fetch_object($result_stunde);
			$beginn=$row->beginn;
			$beginn=mb_substr($beginn,0,5);
			$ende=$row->ende;
			$ende=mb_substr($ende,0,5);
			$stunde=$row->stunde;
			echo '			<th><div align="center">'.$stunde.'<br>&nbsp;'.$beginn .'&nbsp;<br>&nbsp;'.$ende.'&nbsp;</div></th>'.$this->crlf;
		}
		echo '		</tr>'.$this->crlf;
		// Von Montag bis Samstag
		$datum_now=mktime();
		$datum_res_lektor_start=jump_day($datum_now,(RES_TAGE_LEKTOR_MIN)-1);
		$datum_res_lektor_ende=$o_datum->mktime_fromdate(RES_TAGE_LEKTOR_BIS); //jump_day($datum_now,RES_TAGE_LEKTOR_MAX);
		if (!date("w",$this->datum))
			$this->datum=jump_day($this->datum,1);
		$datum=$datum_mon=$this->datum;
		for ($i=1; $i<=TAGE_PRO_WOCHE; $i++)
		{
	  		echo '<tr><td>'.strftime("%A",$datum).'<br>'.strftime("%e. %b %Y",$datum).'<br></td>'.$this->crlf; //.strftime("%A %d %B %Y",$this->datum)
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$row = $this->db_fetch_object($result_stunde, $k);
				$j = $row->stunde;
				// Stunde aufbereiten
				if (isset($this->std_plan[$i][$j][0]->lehrfach))
				{
					// Daten aufbereiten
					$kollision=-1;
					if (isset($unr))
						unset($unr);
					if (isset($lektor))
						unset($lektor);
					if (isset($lehrverband))
						unset($lehrverband);
					if (isset($lehrfach))
						unset($lehrfach);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
					{
						$unr[]=$lehrstunde->unr;
						// Lektoren
						$lektor[]=$lehrstunde->lektor;
						// Lehrverband
						$lvb=$lehrstunde->stg.'-'.$lehrstunde->sem;
						if ($lehrstunde->ver!=null && $lehrstunde->ver!='0' && $lehrstunde->ver!='')
						{
							$lvb.=$lehrstunde->ver;
							if ($lehrstunde->grp!=null && $lehrstunde->grp!='0' && $lehrstunde->grp!='')
								$lvb.=$lehrstunde->grp;
						}
						if (count($lehrstunde->gruppe_kurzbz)>0)
							$lvb=$lehrstunde->gruppe_kurzbz;
						$lehrverband[]=$lvb;
						// Lehrfach
						$lf=$lehrstunde->lehrfach;
						if (isset($lehrstunde->lehrform))
							$lf.='-'.$lehrstunde->lehrform;
						$lehrfach[]=$lf;
						$titel=$lehrstunde->titel;
						$anmerkung=$lehrstunde->anmerkung;
					}

					// Unterrichtsnummer (Kollision?)
					$unr=array_unique($unr);
					$kollision+=count($unr);

					// Lektoren
					if ($this->type!='lektor')
					{
						$lektor=array_unique($lektor);
						sort($lektor);
						$lkt='';
						foreach ($lektor as $l)
							$lkt.=$l.'<BR />';
					}
					else
						$lkt=$lektor[0].'<BR />';
					//echo $lkt;

					// Lehrverband
					if ($this->type!='verband')
					{
						$lehrverband=array_unique($lehrverband);
						sort($lehrverband);
						$lvb='';
						foreach ($lehrverband as $l)
							$lvb.=$l.'<BR />';
					}
					else
						$lvb=$lehrverband[0].'<BR />';

					// Lehrfach
					if ($this->type=='verband')
					{
						$lehrfach=array_unique($lehrfach);
						sort($lehrfach);
						$lf='';
						foreach ($lehrfach as $l)
							$lf.=$l.'<BR />';
					}
					else
						$lf=$lehrfach[0].'<BR />';

					// Blinken oder nicht ?
					if ($kollision)
					{
						$blink_ein='<blink>'.$kollision;
						$blink_aus='</blink>';
					}
					else
					{
						$blink_ein='';
						$blink_aus='';
					}

					// Ausgabe einer Stunde im Raster (HTML)
					echo '				<td nowrap ';
					if (isset($this->std_plan[$i][$j][0]->farbe))
						echo 'style="background-color: #'.$this->std_plan[$i][$j][0]->farbe.';"';
					echo '>'.$blink_ein.'<DIV align="center">';
					// Link zu Details setzten
					echo '<A class="stpl_detail" onClick="window.open(';
					echo "'stpl_detail.php";
					echo '?type='.$this->type.'&datum='.date("Y-m-d",$datum).'&stunde='.$j;
					echo '&pers_uid='.$this->pers_uid;
					echo '&stg_kz='.$this->stg_kz;
					echo '&sem='.$this->sem;
					echo '&ver='.$this->ver;
					echo '&grp='.$this->grp;
					echo '&ort_kurzbz='.$this->std_plan[$i][$j][0]->ort;		//.'">'
					echo "','Details', 'height=320,width=480,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');";
					echo '" title="'.$titel.'" ';
					echo ' href="#">';

					// Ausgabe
					echo $lf;
					if ($this->type=='ort' || $this->type=='lektor')
						echo $lvb;
					if ($this->type!='lektor')
						echo $lkt;
					if ($this->type!='ort')
						echo $this->std_plan[$i][$j][0]->ort;
					echo '</A></DIV>'.$blink_aus.'</td>'.$this->crlf;
				}
				else
				{
					echo '				<td align="center"><br>';
					$datum_res_lektor_start_m = date('Y-m-d', $datum_res_lektor_start);
					$datum_res_lektor_ende_m = date('Y-m-d', $datum_res_lektor_ende);
					$datum_m = date('Y-m-d',$datum);
					if (($raumres || $this->user=='lektor') && $this->type=='ort' && ($datum_m>=$datum_res_lektor_start_m && $datum_m<=$datum_res_lektor_ende_m))
						echo '<INPUT type="checkbox" name="reserve'.$i.'_'.$j.'" value="'.date("Y-m-d",$datum).'">'; //&& $datum>=$datum_now
					echo '</td>'.$this->crlf;
				}
			}
			echo '		</tr>'.$this->crlf;
			$datum=jump_day($datum, 1);
		}
		echo '	</table>'.$this->crlf;
		if ($this->user=='lektor' && $this->type=='ort' && ($datum>=$datum_now && $datum>=$datum_res_lektor_start && $datum_mon<=$datum_res_lektor_ende))
		{
				echo '	<br />Titel: <input type="text" name="titel" size="10" maxlength="10" /> '.$this->crlf;
				echo '	Beschreibung: <input type="text" name="beschreibung" size="20" maxlength="32" /> '.$this->crlf;
				echo '	<input type="submit" name="reserve" value="Reservieren" />'.$this->crlf;
				echo '	<input type="hidden" name="user_uid" value="'.$this->user_uid.'" />'.$this->crlf;
				echo '	<input type="hidden" name="ort_kurzbz" value="'.$this->ort_kurzbz.'" />'.$this->crlf;
				echo '	<input type="hidden" name="datum" value="'.$this->datum.'" />'.$this->crlf;
				echo '	<input type="hidden" name="type" value="'.$this->type.'" />'.$this->crlf;
				echo '</form>';
		}
	}

	/**
	 * Funktion draw_week_xul Stundenplan im XUL-Format
	 *
	 * @param datum Datum eines Tages in der angeforderten Woche
	 * @return true oder false
	 */
	public function draw_week_xul($semesterplan, $uid, $wunsch=null, $ignore_kollision=false)
	{
		//echo $wunsch;
		global $cfgStdBgcolor;
		$count=0;
		$berechtigung=new benutzerberechtigung();
		$berechtigung->getBerechtigungen($uid);
		// Stundentafel abfragen
		$sql_query="SELECT * FROM lehre.tbl_stunde ORDER BY stunde";
		if(!$this->db_query($sql_query))
			$this->errormsg=$this->db_last_error();
		$result_stunde = $this->db_result;
		$num_rows_stunde=$this->db_num_rows($result_stunde);

		// Kontext Menue
		echo '<popupset>
  				<popup id="stplPopupMenue">
					<menuitem label="Raumvorschlag" oncommand="StplSearchRoom(document.popupNode);" />
    				<menuitem label="Entfernen" oncommand="onStplDelete(\'stpl_delete_single\');" />
  				</popup>
			</popupset>';

		//Tabelle zeichnen
		echo '<grid flex="1">';
		echo '<columns>';
		echo '	<column style="background-color:lightblue; border:1px solid black" />';
		for ($i=0;$i<$num_rows_stunde; $i++)
			echo '	<column />';
		echo '</columns>';
		echo '<rows>';

		// Kopfzeile darstellen
		echo '<row style="background-color:lightgreen; border:1px solid black">'.$this->crlf;
		echo '<vbox>
			<label align="center">Stunde</label>
			<label id="TimeTableWeekData" class="kalenderwoche"
				datum="'.$this->datum.'"
				stpl_type="'.$this->type.'"
				stg_kz="'.$this->stg_kz.'"
				sem="'.$this->sem.'"
				ver="'.$this->ver.'"
				grp="'.$this->grp.'"
				gruppe="'.$this->gruppe_kurzbz.'"
				ort="'.$this->ort_kurzbz.'"
				pers_uid="'.$this->pers_uid.'"
				kw="'.$this->kalenderwoche.'"
				align="left">KW:'.$this->kalenderwoche.'</label>
			</vbox>'.$this->crlf; //<html:br />Beginn<html:br />Ende
		for ($i=0;$i<$num_rows_stunde; $i++)
		{
			$row=$this->db_fetch_object($result_stunde,$i);
			$beginn=mb_substr($row->beginn,0,5);
			$ende=mb_substr($row->ende,0,5);
			$stunde=$row->stunde;
			echo '<vbox><label align="center">'.$stunde.'<html:br />
						<html:small>'.$beginn.'<html:br />
						'.$ende.'</html:small></label>
					</vbox>'.$this->crlf;
		}
		echo '</row>';

		// Von Montag bis Samstag
		if (!date("w",$this->datum))
			$this->datum=jump_day($this->datum,1);
		$datum=$this->datum;

		// Ferien holen
		$ferien=new ferien();
		if ($this->type=='verband')
			$ferien->getAll($this->stg_kz);
		else
			$ferien->getAll();
		for ($i=1; $i<=TAGE_PRO_WOCHE; $i++)
		{
			$isferien=$ferien->isferien($datum);
			echo '<row><vbox>';
			echo '<html:div><html:small>'.date("l",$datum).'<html:br /></html:small>'.date("j.m y",$datum).'</html:div>';
			echo '</vbox>';
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$row = $this->db_fetch_object($result_stunde, $k);
				$j=$row->stunde;
				if (isset($wunsch[$i][$j]))
					$index=$wunsch[$i][$j];
				else
					$index=1;
				if ($index=='')
					$index=1;
				$bgcolor=$cfgStdBgcolor[$index+3];
				if ($isferien)
					$bgcolor='#FFFF55';
				echo '<vbox style="border:1px solid black; background-color:'.$bgcolor.'"
					ondragdrop="nsDragAndDrop.drop(event,boardObserver)"
					ondragover="nsDragAndDrop.dragOver(event,boardObserver)"
		  			ondragenter="nsDragAndDrop.dragEnter(event,boardObserver)"
					ondragexit="nsDragAndDrop.dragExit(event,boardObserver)"
		  			datum="'.date("Y-m-d",$datum).'" stunde="'.$j.'"
					stg_kz="'.$this->stg_kz.'" sem="'.$this->sem.'" ver="'.$this->ver.'"
					grp="'.$this->grp.'" gruppe="'.$this->gruppe_kurzbz.'"
					pers_uid="'.$this->pers_uid.'" stpltype="'.$this->type.'">';

				if (isset($this->std_plan[$i][$j][0]->lehrfach))
				{
					// Daten aufbereiten
					if (isset($lvb))
						unset($lvb);
					//$lvb=array();
					$kollision=-1;
					if (isset($a_unr))
						unset($a_unr);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
					{
						$a_unr[]=$lehrstunde->unr;
						$a_lvb[$lehrstunde->unr][]=$lehrstunde->sem.$lehrstunde->ver.$lehrstunde->grp;
					}
					// Unterrichtsnummer (Kollision?)
					$a_unr=array_unique($a_unr);
					$kollision+=count($a_unr);
					// Ist es bei LVB-Ansicht wirklich eine Kollision?
					if ($kollision>0 && $this->type=='verband')
					{
						$kollision=0;
						$a=0;
						foreach ($a_unr as $unr)
						{
							array_unique($a_lvb[$unr]);
							$lvb[$a++]=$a_lvb[$unr];
						}
						for ($a=0;$a<count($lvb)-1;$a++)
							for ($b=0;$b<count($lvb[$a]);$b++)
								for ($c=$a+1;$c<count($lvb);$c++)
									for ($d=0;$d<count($lvb[$c]);$d++)
									{
										$s1=mb_substr($lvb[$a][$b],0,1);
										$s2=mb_substr($lvb[$c][$d],0,1);
										$v1=mb_substr($lvb[$a][$b],1,1);
										$v2=mb_substr($lvb[$c][$d],1,1);
										$g1=mb_substr($lvb[$a][$b],2,1);
										$g2=mb_substr($lvb[$c][$d],2,1);
										if ($s1==$s2 || !$s1 || $s1=='' || $s1=='0' || !$s2 || $s2=='' || $s2=='0')
											if ($v1==$v2 || !$v1 || $v1=='' || $v1=='0' || !$v2 || $v2=='' || $v2=='0')
												if ($g1==$g2 || !$g1 || $g1=='' || $g1=='0' || !$g2 || $g2=='' || $g2=='0')
													$kollision++;
									}
					}
					// Kollision anzeigen?
					if ($ignore_kollision)
						$kollision=0;
					//Daten aufbereiten
					foreach ($a_unr as $unr)
					{
						// Daten vorbereiten
						if (isset($lektor))
							unset($lektor);
						if (isset($lehrverband))
							unset($lehrverband);
						if (isset($lehrfach))
							unset($lehrfach);
						if (isset($ort))
							unset($ort);
						if (isset($updateamum))
							unset($updateamum);
						if (isset($updatevon))
							unset($updatevon);
						$paramList='';
						$z=0;
						foreach ($this->std_plan[$i][$j] as $lehrstunde)
							if ($lehrstunde->unr==$unr)
							{
								// Lektoren
								$lektor[]=$lehrstunde->lektor;
								// Lehrverband
								$lvb=$lehrstunde->stg.'-'.$lehrstunde->sem;
								if ($lehrstunde->ver!=null && $lehrstunde->ver!='0' && $lehrstunde->ver!='')
								{
									$lvb.=$lehrstunde->ver;
									if ($lehrstunde->grp!=null && $lehrstunde->grp!='0' && $lehrstunde->grp!='')
										$lvb.=$lehrstunde->grp;
								}
								if (count($lehrstunde->gruppe_kurzbz)>0)
									$lvb=$lehrstunde->gruppe_kurzbz;
								$lehrverband[]=$lvb;
								// Lehrfach
								$lf=htmlspecialchars($lehrstunde->lehrfach);
								if (isset($lehrstunde->lehrform))
									$lf.='-'.$lehrstunde->lehrform;
								$lehrfach[]=$lf;
								$ort[]=$lehrstunde->ort;
								$stg_kz=$lehrstunde->stg_kz;
								$updateamum[]=mb_substr($lehrstunde->updateamum,0,16);
								$updatevon[]=$lehrstunde->updatevon;
								if ($lehrstunde->reservierung)
									$paramList.='&amp;reservierung_id'.$z++.'='.$lehrstunde->stundenplan_id;
								else
									$paramList.='&amp;stundenplan_id'.$z++.'='.$lehrstunde->stundenplan_id;
								if(isset($lehrstunde->farbe))
									$farbe=$lehrstunde->farbe;
								$titel=htmlspecialchars($lehrstunde->titel);
								$anmerkung=htmlspecialchars($lehrstunde->anmerkung);
							}

						// Lektoren
						//if ($this->type!='lektor')
						$lektor=array_unique($lektor);
						sort($lektor);
						$lkt='';
						foreach ($lektor as $l)
							$lkt.=$l.'<html:br />';

						// Lehrverband
						//if ($this->type!='verband')
						$lehrverband=array_unique($lehrverband);
						sort($lehrverband);
						$lvb='';
						foreach ($lehrverband as $l)
							$lvb.=$l.'<html:br />';

						// Lehrfach
						//if ($this->type=='verband')
						$lehrfach=array_unique($lehrfach);
						sort($lehrfach);
						$lf='';
						foreach ($lehrfach as $l)
							$lf.=$l.'<html:br />';

						// Ort
						//if ($this->type=='verband')
						$ort=array_unique($ort);
						sort($ort);
						$orte='';
						foreach ($ort as $o)
							$orte.=$o.'<html:br />';

						// Update Von
						$updatevon=array_unique($updatevon);
						sort($updatevon);
						$updatevonam='Geaendert von ';
						foreach ($updatevon as $u)
							$updatevonam.=$u.', ';

						// Update Am
						$updateamum=array_unique($updateamum);
						sort($updateamum);
						$updatevonam.='am ';
						foreach ($updateamum as $u)
							$updatevonam.=$u.' ';
						
						// Blinken oder nicht ?
						if ($kollision)
						{
							$blink_ein='<html:blink>';// .$kollision;
							$blink_aus='</html:blink>';
						}
						else
						{
							$blink_ein='';
							$blink_aus='';
						}

						// Ausgabe
						echo '<button id="buttonSTPL'.$count++.'"
							tooltiptext="('.$updatevonam.') '.$titel.' - '.$anmerkung.'"
							style="border-width:1px;'.((isset($farbe) && $farbe!='')?'background-color:#'.$farbe:'').';"
							styleOrig="border-width:1px;'.((isset($farbe) && $farbe!='')?'background-color:#'.$farbe:'').'" ';
						if ($berechtigung->isBerechtigt('lv-plan',$stg_kz,'uid') || $berechtigung->isBerechtigt('lv-plan',0,'uid') || $berechtigung->isBerechtigt('admin',0,'uid') || $berechtigung->isBerechtigt('admin',$stg_kz,'uid'))
							echo ' context="stplPopupMenue" ';
						if ($berechtigung->isBerechtigt('lv-plan',$stg_kz,'u') || $berechtigung->isBerechtigt('lv-plan',0,'u') || $berechtigung->isBerechtigt('admin',0,'u') || $berechtigung->isBerechtigt('admin',$stg_kz,'u'))
							echo 'ondraggesture="nsDragAndDrop.startDrag(event,listObserver)" ';
						echo 'ondragdrop="nsDragAndDrop.drop(event,boardObserver)"
							ondragover="nsDragAndDrop.dragOver(event,boardObserver)"
							onclick="return onStplSearchRoom(event, event.target);"
							oncommand="onStplDetail(event);"
							aktion="stpl"
							elem="stundenplan'.$i.$j.'"
							idList="'.$paramList.'" stpltype="'.$this->type.'"
							stg_kz="'.$this->stg_kz.'" sem="'.$this->sem.'" ver="'.$this->ver.'"
							grp="'.$this->grp.'" gruppe="'.$this->gruppe_kurzbz.'"
							datum="'.date("Y-m-d",$datum).'" stunde="'.$j.'"
							pers_uid="'.$this->pers_uid.'" ort_kurzbz="'.$this->ort_kurzbz.'">';
						echo '<label align="center">'.$blink_ein;
						echo $lf;
						echo $lvb;
						if ($this->type!='lektor')
							echo $lkt;
						if ($this->type!='ort')
							echo $orte;
						echo $blink_aus.'</label></button>';
					}
				}
				if (isset($this->std_plan[$i][$j][0]->frei_orte))
					foreach ($this->std_plan[$i][$j][0]->frei_orte as $f_ort)
					{
						echo '<label value="'.$f_ort.'"
							styleOrig=""
							ondragenter="nsDragAndDrop.dragEnter(event,boardObserver)"
							ondragexit="nsDragAndDrop.dragExit(event,boardObserver)"
		  					ondragdrop="nsDragAndDrop.drop(event,boardObserver)"
							datum="'.date("Y-m-d",$datum).'" stunde="'.$j.'"
							stg_kz="'.$this->stg_kz.'" sem="'.$this->sem.'" ver="'.$this->ver.'"
							grp="'.$this->grp.'" gruppe="'.$this->gruppe_kurzbz.'"
							stpltype="'.$this->type.'"
							/>';
					}
				echo '</vbox>'.$this->crlf;
			}
			echo "</row>";
			$datum=jump_day($datum, 1);
		}

		// Fuszzeile darstellen
		if (!$semesterplan)
		{
			echo '<row style="background-color:lightgreen; border:1px solid black">'.$this->crlf;
			echo '<vbox>
				<label align="center">Stunde</label>
				<label align="left" class="kalenderwoche">KW:'.$this->kalenderwoche.'</label>
				</vbox>'.$this->crlf; //<html:br />Beginn<html:br />Ende
			for ($i=0;$i<$num_rows_stunde; $i++)
			{
				$row=$this->db_fetch_object($result_stunde,$i);
				$beginn=mb_substr($row->beginn,0,5);
				$ende=mb_substr($row->ende,0,5);
				$stunde=$row->stunde;
				echo '<vbox><label align="center">'.$stunde.'<html:br />
						<html:small>'.$beginn.'<html:br />
						'.$ende.'</html:small></label>
					</vbox>'.$this->crlf;
			}
			echo '</row>';
		}
		echo '</rows>';
		echo '</grid>';
	}



	/**
	 * Funktion load_stpl_search sucht Vorschlag fuer Stundenverschiebung
	 *
	 * @param 	datum 		der Aktuellen Woche
	 * @param	stpl_id 		Array der stundenplan_id's
	 * @param	db_stpl_table	Name der DB-Tabelle
	 * @return true oder false
	 */
	public function load_stpl_search($datum,$stpl_id,$db_stpl_table, $block=1)
	{
		// Initatialisierung der Variablen
		$lehrverband=array();
		// Name der View
		$stpl_view=VIEW_BEGIN.$db_stpl_table;
		$stpl_view_id=$db_stpl_table.TABLE_ID;
		//Kalenderdaten setzen
		$this->datum=montag($datum);
		$this->datum_begin=$this->datum;
		$this->datum_end=jump_week($this->datum_begin, 1);
		// Formatieren fuer Datenbankabfragen
		$this->datum_begin=date("Y-m-d",$this->datum_begin);
		$this->datum_end=date("Y-m-d",$this->datum_end);
		// Stundentafel abfragen
		$sql_query='SELECT min(stunde),max(stunde)FROM lehre.tbl_stunde';
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		$row = $this->db_fetch_object();
		$min_stunde=$row->min;
		$max_stunde=$row->max;
		// Stundenplaneintraege holen
		$sql_query="SELECT * FROM $stpl_view WHERE";
		$stplids='';
		foreach ($stpl_id as $id)
			$stplids.=" OR $stpl_view_id=$id";
		$stplids=mb_substr($stplids,3);
		$sql_query.=$stplids;
		//echo $sql_query;
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		$num_rows_stpl=$this->db_num_rows();
		// Daten aufbereiten
		$leids='';
		for ($i=0;$i<$num_rows_stpl;$i++)
		{
			$row=$this->db_fetch_object(null,$i);
			//$block=$row->stundenblockung;
			//$raumtyp[$i]=$row->raumtyp;
			//$raumtypalt[$i]=$row->raumtypalternativ;
			if ($row->gruppe_kurzbz!=null)
				$gruppe[]=$row->gruppe_kurzbz;
			else
				$gruppe[]='';
			$lehrverband[$i]->stg_kz=$row->studiengang_kz;
			$lehrverband[$i]->sem=$row->semester;
			$lehrverband[$i]->ver=$row->verband;
			$lehrverband[$i]->grp=$row->gruppe;
			$leids.="$row->lehreinheit_id,";
			$lektor[$i]=$row->uid;
			$unr=$row->unr;
		}
		if($leids!='')
		{
			// Raumtypen
			$leids = mb_substr($leids, 0, mb_strlen($leids)-1);
			$qry = "SELECT raumtyp, raumtypalternativ FROM lehre.tbl_lehreinheit WHERE lehreinheit_id IN ($leids)";
			if($this->db_query($qry)){
				while($row = $this->db_fetch_object())
				{
					$raumtyp[]=$row->raumtyp;
					$raumtyp[]=$row->raumtypalternativ;
				}
			}
		}
		$raumtyp=array_unique($raumtyp);
		$rtype='';
		foreach ($raumtyp as $r)
			$rtype.=" OR raumtyp_kurzbz='$r'";
		$rtype=mb_substr($rtype,3);
		//Lektor
		$lektor=array_unique($lektor);
		$lkt='';
		foreach ($lektor as $l)
			$lkt.=" OR uid='$l'";
		$lkt=mb_substr($lkt,3);
		// Einheiten
		$gruppe=array_unique($gruppe);
		$gruppen='';
		foreach ($gruppe as $g)
			if ($g!='')
				$gruppen.=" OR gruppe_kurzbz='$g'";
		//$gruppen=mb_substr($gruppen,3);
		//Lehrverband
		//$lehrverband=array_unique($lehrverband);
		$lvb='';
		foreach ($lehrverband as $l)
		{
			$lvb.=' OR (studiengang_kz='.$l->stg_kz.' AND semester='.$l->sem;
			if ($l->ver!='' && $l->ver!=' ' && $l->ver!=null)
			{
				$lvb.=" AND (verband='$l->ver' OR verband IS NULL OR verband='')";
				if ($l->grp!='' && $l->grp!=' ' && $l->grp!=null)
					$lvb.=" AND (gruppe='$l->grp' OR gruppe IS NULL OR gruppe='')";
			}
			//if ($gruppen=='')
			//	$lvb.=' AND gruppe_kurzbz IS NULL';
			$lvb.=')';
		}
		$lvb=mb_substr($lvb,3);
		//if($rtype=='')
		//	$rtype='1=1';
		// Raeume die in Frage kommen, aufgrund der Raumtypen
		$sql_query="SELECT DISTINCT ort_kurzbz, hierarchie FROM public.tbl_ort
			JOIN public.tbl_ortraumtyp USING (ort_kurzbz) WHERE ($rtype) AND aktiv AND ort_kurzbz NOT LIKE '\\\\_%' ORDER BY hierarchie,ort_kurzbz"; 
		
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		while($row = $this->db_fetch_object())
			$orte[]=$row->ort_kurzbz;

		// Raster vorbereiten
		for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
		{
			for ($s=$min_stunde;$s<=$max_stunde;$s++)
			{
				$raster[$t][$s]->ort=array();
				$raster[$t][$s]->kollision=false;
			}
		}
		// Stundenplanabfrage bauen (Wo ist Kollision?)
		$sql_query="SELECT DISTINCT datum, stunde FROM $stpl_view
			WHERE datum>='$this->datum_begin' AND datum<'$this->datum_end' AND
			($lkt $gruppen OR ($lvb) ) AND unr!=$unr"; //AND unr!=$unr"
		//echo $sql_query;
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		while($row = $this->db_fetch_object())
		{
			$mtag=mb_substr($row->datum, 8,2);
			$month=mb_substr($row->datum, 5,2);
			$jahr=mb_substr($row->datum, 0,4);
			$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
			$raster[$tag][$row->stunde]->kollision=true;
		}

		// Stundenplanabfrage bauen (Wo ist besetzt?)
		$sql_query="SELECT DISTINCT datum, stunde, ort_kurzbz FROM $stpl_view
			WHERE datum>='$this->datum_begin' AND datum<'$this->datum_end' AND unr!=$unr";
		//echo $sql_query; NATURAL JOIN tbl_ortraumtyp AND ($rtype) "
		
		// Reservierungen beruecksichtigen
		$sql_query.=" UNION SELECT DISTINCT datum, stunde, ort_kurzbz FROM campus.tbl_reservierung
			WHERE datum>='$this->datum_begin' AND datum<'$this->datum_end' ";
		
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
			
		while($row = $this->db_fetch_object())
		{
			$mtag=mb_substr($row->datum, 8,2);
			$month=mb_substr($row->datum, 5,2);
			$jahr=mb_substr($row->datum, 0,4);
			$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
			$raster[$tag][$row->stunde]->ort[]=$row->ort_kurzbz;
		}
				
		// freie Plaetze in den Stundenplan eintragen.
		for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
			for ($s=1;$s<=$max_stunde;$s++)
				if (!$raster[$t][$s]->kollision && ($s+$block)<=($max_stunde+1))
				{
					if (count($raster[$t][$s]->ort)>0)
						$this->std_plan[$t][$s][0]->frei_orte=array_diff($orte,$raster[$t][$s]->ort);
					else
						$this->std_plan[$t][$s][0]->frei_orte=$orte;
					for ($b=1;$b<$block && ($s+$block)<=($max_stunde+1);$b++)
						$this->std_plan[$t][$s][0]->frei_orte=array_diff($this->std_plan[$t][$s][0]->frei_orte,$raster[$t][$s+$b]->ort);
				}
		return true;
	}

	/**
	 * Funktion load_lva_search sucht Vorschlag fuer LVAs
	 *
	 * @param 	datum 		der Aktuellen Woche
	 * @param	lva_id 		Array der lvaIDs
	 * @param	db_stpl_table	Name der DB-Tabelle
	 * @return true oder false
	 */
	public function load_lva_search($datum,$lva_id,$db_stpl_table,$type)
	{
		// Initialiseren der Variablen
		$lehrverband=array();
		// Name der View
		$stpl_view='lehre.'.VIEW_BEGIN.$db_stpl_table;
		$lva_stpl_view='lehre.'.VIEW_BEGIN.'lva_'.$db_stpl_table;
		$stpl_table='lehre.'.TABLE_BEGIN.$db_stpl_table;
		//Kalenderdaten setzen
		$this->datum=montag($datum);
		$this->datum_begin=$this->datum;
		$this->datum_end=jump_week($this->datum_begin, 1);
		// Formatieren fuer Datenbankabfragen
		$this->datum_begin=date("Y-m-d",$this->datum_begin);
		$this->datum_end=date("Y-m-d",$this->datum_end);
		// Stundentafel abfragen
		$sql_query='SELECT min(stunde),max(stunde) FROM lehre.tbl_stunde';
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		$row = $this->db_fetch_object();
		$min_stunde=$row->min;
		$max_stunde=$row->max;

		// LEs holen
		$sql_query='SELECT *, (planstunden-verplant::smallint) AS offenestunden FROM '.$lva_stpl_view.' WHERE';
		$lvas='';
		foreach ($lva_id as $id)
			$lvas.=' OR lehreinheit_id='.$id;
		$lvas=mb_substr($lvas,3);
		$sql_query.=$lvas;
		//$this->errormsg.=$sql_query;
		//return false;
		if(!$this->db_query($sql_query))
			die($this->db_last_error());
		$num_rows_lva=$this->db_num_rows();
		// Arrays setzen
		//$wochenrythmus=array();
		$verplant=array();
		$block=array();
		$semesterstunden=array();
		$planstunden=array();
		$offenestunden=array();
		// Daten aufbereiten
		for ($i=0;$i<$num_rows_lva;$i++)
		{
			$row=$this->db_fetch_object(null,$i);
			
			$raumtyp[$i]=$row->raumtyp;
			$raumtypalt[$i]=$row->raumtypalternativ;
			if ($row->gruppe_kurzbz!=null && $row->gruppe_kurzbz!='')
				$gruppe[$i]=$row->gruppe_kurzbz;
			$lehrverband[$i]->stg_kz=$row->studiengang_kz;
			$lehrverband[$i]->sem=$row->semester;
			$lehrverband[$i]->ver=$row->verband;
			$lehrverband[$i]->grp=$row->gruppe;
			$lektor[$i]=$row->lektor_uid;
			$verplant[$i]=$row->verplant;
			$planstunden[$i]=$row->planstunden;
			$offenestunden[]=$row->offenestunden;
			$unr=$row->unr;
			$block[$i]=$row->stundenblockung;
			$wochenrythmus[$i]=$row->wochenrythmus;
			$semesterstunden[$i]=(integer)$row->semesterstunden;
			//$this->errormsg.='SS:'.$semesterstunden[$i];
		}
		/*// verplante Stunden eindeutig?
		$verpl=$verplant[0];
		$verplant=array_unique($verplant);
		if (count($verplant)==1)
			$verplant=$verpl; //verplant[0];
		else
		{
			$this->errormsg.='Verplante Stunden sind nicht eindeutig!';
			return false;
		}
		//$this->errormsg.='Verplant:'.$verplant;
		// Semesterstunden eindeutig?
		$semstd=$semesterstunden[0];
		$semesterstunden=array_unique($semesterstunden);
		//$this->errormsg.='SS:'.$semesterstunden[0];
		if (count($semesterstunden)==1)
			$semesterstunden=$semstd;//semesterstunden[0];
		else
		{
			$this->errormsg.='Semesterstunden sind nicht eindeutig!';
			return false;
		}
		//$this->errormsg.='SS:'.$semesterstunden;*/
		// Blockung eindeutig?
		$blck=$block[0];
		$block=array_unique($block);
		if (count($block)==1)
			$block=$blck; //block[0];
		else
		{
			$this->errormsg.='Blockung ist nicht eindeutig!';
			return false;
		}
		//$this->errormsg.='Block:'.$block;
		// Offene Stunden eindeutig?
		$os=$offenestunden[0];
		$offenestunden=array_unique($offenestunden);
		if ($type=='lva_single_search')
			$offenestunden=$block;
		elseif (count($offenestunden)==1)
			$offenestunden=$os;
		else
		{
			$this->errormsg.='Offene Stunden sind nicht eindeutig!';
			return false;
		}
		// Wochenrythmus eindeutig?
		$wr=$wochenrythmus[0];
		$wochenrythmus=array_unique($wochenrythmus);
		if (count($wochenrythmus)==1)
			$wr=$wr;
		else
		{
			$this->errormsg.='Wochenrythmus ist nicht eindeutig!';
			return false;
		}
		// Raumtypen
		$raumtyp=array_unique($raumtyp);
		$rtype='';
		foreach ($raumtyp as $r)
			$rtype.=" OR raumtyp_kurzbz='$r'";
		$raumtypalt=array_unique($raumtypalt);
		foreach ($raumtypalt as $r)
			$rtype.=" OR raumtyp_kurzbz='$r'";
		$rtype=mb_substr($rtype,3);
		//Lektor
		$lektor=array_unique($lektor);
		$lkt='';
		foreach ($lektor as $l)
			$lkt.=" OR mitarbeiter_uid='$l'";
		$lkt=mb_substr($lkt,3);
		//Dummy Lektor kollidiert nicht
		$lkt='(('.$lkt.") AND mitarbeiter_uid!='_DummyLektor')";
		// Gruppen
		$gruppen='';
		if (isset($gruppe))
		{
			$gruppe=array_unique($gruppe);
			foreach ($gruppe as $g)
				$gruppen.=" OR gruppe_kurzbz='$g'";
			//$gruppen=mb_substr($gruppen,3);
		}
		//Lehrverband
		//$lehrverband=array_unique($lehrverband);
		$lvb='';
		foreach ($lehrverband as $l)
		{
			$lvb.=' OR (studiengang_kz='.$l->stg_kz.' AND semester='.$l->sem;
			if ($l->ver!='' && $l->ver!=' ' && $l->ver!=null)
			{
				$lvb.=" AND (verband='$l->ver' OR verband IS NULL OR verband='' OR verband=' ')";
				if ($l->grp!='' && $l->grp!=' ' && $l->grp!=null)
					$lvb.=" AND (gruppe='$l->grp' OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
			}
			if ($gruppen=='')
				$lvb.=' AND gruppe_kurzbz IS NULL';
			$lvb.=')';
		}
		$lvb=mb_substr($lvb,3);

		// Raeume die in Frage kommen aufgrund der Raumtypen
		$sql_query="SELECT DISTINCT ort_kurzbz, hierarchie FROM public.tbl_ort
			JOIN public.tbl_ortraumtyp USING (ort_kurzbz) WHERE ($rtype) AND aktiv AND ort_kurzbz NOT LIKE '\\\\_%' ORDER BY hierarchie,ort_kurzbz"; //
		//echo $sql_query;
		if(!$this->db_query($sql_query))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		$num_orte=$this->db_num_rows();
		for ($i=0;$i<$num_orte;$i++)
		{
			$row = $this->db_fetch_object(null, $i);	
			$orte[]=$row->ort_kurzbz;
		}

		// Suche nach freien Orten. Bei 'lva_multi_search' wird die Schleife (do) aktiv
		$count=0;
		$rest=$offenestunden;
		if ($rest<=0 && $type=='lva_multi_search')
		{
			$this->errormsg.='Es sind bereits alle Stunden verplant!';
			return false;
		}
		$datum=$this->datum;
		$datum_begin=$this->datum_begin;
		$datum_end=$this->datum_end;
		// Raster vorbereiten
		for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
			for ($s=$min_stunde;$s<=$max_stunde;$s++)
			{
				$raster[$t][$s]->ort=array();
				$raster[$t][$s]->kollision=false;
			}
		do
		{
			// Raster vorbereiten
			for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
				for ($s=$min_stunde;$s<=$max_stunde;$s++)
				{
					if (isset($raster[$t][$s]))
						unset($raster[$t][$s]);
					$raster[$t][$s]->ort=array();
					$raster[$t][$s]->kollision=false;
				}

			// Stundenplanabfrage bauen (Wo ist Kollision?)
			$sql_query="SELECT DISTINCT datum, stunde FROM $stpl_table
				WHERE datum>='$datum_begin' AND datum<'$datum_end' AND
				($lkt $gruppen OR ($lvb) )";
			if (is_numeric($unr))
				$sql_query.=" AND unr!=$unr";
			//$this->errormsg.=htmlspecialchars($sql_query);
			//return false;
			if(!$this->db_query($sql_query))
			{
				$this->errormsg = $this->db_last_error().$sql_query;
				return false;
			}

			// Kollisionen ins Raster eintragen
			while($row = $this->db_fetch_object())
			{
				$mtag=mb_substr($row->datum, 8,2);
				$month=mb_substr($row->datum, 5,2);
				$jahr=mb_substr($row->datum, 0,4);
				$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
				$raster[$tag][$row->stunde]->kollision=true;
			}

			// Stundenplanabfrage bauen (Wo ist besetzt?)
			$sql_query="SELECT DISTINCT datum, stunde, ort_kurzbz FROM $stpl_view
				JOIN public.tbl_ortraumtyp USING (ort_kurzbz)
				WHERE datum>='$datum_begin' AND datum<'$datum_end' AND
				($rtype)";
			if (is_numeric($unr))
				$sql_query.=" AND unr!=$unr";
			
			// Reservierungen beruecksichtigen
			$sql_query.=" UNION SELECT distinct datum, stunde, ort_kurzbz FROM campus.tbl_reservierung
						WHERE datum>='$datum_begin' AND datum<'$datum_end'";
			
			if(!$this->db_query($sql_query))
			{
				$this->errormsg = $this->db_last_error().$sql_query;
				return false;
			}
			
			while($row = $this->db_fetch_object())
			{
				$mtag=mb_substr($row->datum, 8,2);
				$month=mb_substr($row->datum, 5,2);
				$jahr=mb_substr($row->datum, 0,4);
				$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
				$raster[$tag][$row->stunde]->ort[]=$row->ort_kurzbz;
				//if ($row->ort_kurzbz=='EDV6.10' && $tag==2 && $row->stunde==8)
				//	$this->errormsg.=htmlspecialchars($row->ort_kurzbz).'/'.$mtag.'/'.$month;
			}

			// freie Plaetze in den Stundenplan eintragen.
			for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
				for ($s=1;$s<=$max_stunde;$s++)
					if (!$raster[$t][$s]->kollision && ($s+$block)<=($max_stunde+1))
					{
						// Besetzte Orte von den freien abziehen
						if (count($raster[$t][$s]->ort)>0 && $count==0)
							$this->std_plan[$t][$s][0]->frei_orte=array_diff($orte,$raster[$t][$s]->ort);
						elseif ($count==0)
							$this->std_plan[$t][$s][0]->frei_orte=$orte;
						elseif (count($raster[$t][$s]->ort)>0)
							$this->std_plan[$t][$s][0]->frei_orte=array_diff($this->std_plan[$t][$s][0]->frei_orte,$raster[$t][$s]->ort);
						// Blockung beruecksichtigen
						for ($b=1;$b<$block && ($s+$block)<=($max_stunde+1);$b++)
							if (!$raster[$t][$s+$b]->kollision)
								$this->std_plan[$t][$s][0]->frei_orte=array_diff($this->std_plan[$t][$s][0]->frei_orte,$raster[$t][$s+$b]->ort);
							else
								$this->std_plan[$t][$s][0]->frei_orte=array();
					}
					elseif($raster[$t][$s]->kollision)
						$this->std_plan[$t][$s][0]->frei_orte=array();

			// Variablen abgleichen
			$rest-=$block;
			if ($block>$rest)
				$block=$rest;
			$datum=jump_week($datum,$wr);
			$datum_begin=$datum;
			$datum_end=jump_week($datum_begin, 1);
			// Formatieren fuer Datenbankabfragen
			$datum_begin=date("Y-m-d",$datum_begin);
			$datum_end=date("Y-m-d",$datum_end);
			$count++;
		} while($type=='lva_multi_search' && $rest>0);
		return true;
	}


	/**
	 * Funktion draw_week_csv Stundenplan im CSV-Format
	 *
	 * @param target Ziel-System zB Outlook
	 * @return true oder false
	 */
	public function draw_week_csv($target, $lvplan_kategorie)
	{
		if (!date("w",$this->datum))
			$this->datum=jump_day($this->datum,1);
		$num_rows_stunde=$this->db_num_rows($this->stunde);
		for ($i=1; $i<=TAGE_PRO_WOCHE; $i++)
		{
  			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$row = $this->db_fetch_object($this->stunde, $k);
				$j=$row->stunde;  // get id of hour
				if (isset($this->std_plan[$i][$j][0]->lehrfach))
				{
					// Daten aufbereiten
					if (isset($unr))
						unset($unr);
					if (isset($lektor))
						unset($lektor);
					if (isset($lehrverband))
						unset($lehrverband);
					if (isset($lehrfach))
						unset($lehrfach);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
					{
						$unr[]=$lehrstunde->unr;
						// Lektoren
						$lektor[]=$lehrstunde->lektor;
						// Lehrverband
						$lvb=$lehrstunde->stg.'-'.$lehrstunde->sem;
						if ($lehrstunde->ver!=null && $lehrstunde->ver!='0' && $lehrstunde->ver!='')
						{
							$lvb.=$lehrstunde->ver;
							if ($lehrstunde->grp!=null && $lehrstunde->grp!='0' && $lehrstunde->grp!='')
								$lvb.=$lehrstunde->grp;
						}
						if (count($lehrstunde->gruppe_kurzbz)>0)
							$lvb=$lehrstunde->gruppe_kurzbz;
						$lehrverband[]=$lvb;
						// Lehrfach
						$lf=$lehrstunde->lehrfach;
						if (isset($lehrstunde->lehrform))
							$lf.='-'.$lehrstunde->lehrform;
						$lehrfach[]=$lf;
						$titel=$lehrstunde->titel;
						$anmerkung=$lehrstunde->anmerkung;
					}

					// Unterrichtsnummer (Kollision?)
					$unr=array_unique($unr);
					if(!isset($kollision))
						$kollision=0;
					$kollision+=count($unr);

					// Lektoren
					if ($this->type!='lektor')
					{
						$lektor=array_unique($lektor);
						sort($lektor);
						$lkt='';
						foreach ($lektor as $l)
							$lkt.=$l.' ';
					}
					else
						$lkt=$lektor[0];
					//echo $lkt;

					// Lehrverband
					if ($this->type!='verband')
					{
						$lehrverband=array_unique($lehrverband);
						sort($lehrverband);
						$lvb='';
						foreach ($lehrverband as $l)
							$lvb.=$l.' ';
					}
					else
						$lvb=$lehrverband[0];

					$row = $this->db_fetch_object($this->stunde, $k);
					$start_time=$row->beginn;
					
					// Blockungen erkennen
					if (($this->std_plan[$i][$j][0]->unr == $this->std_plan[$i][$j+1][0]->unr) && $this->std_plan[$i][$j][0]!='0' && $k<($num_rows_stunde-1))
					{
						$row = $this->db_fetch_object($this->stunde, ++$k);
						$end_time=$row->ende;
						if (($this->std_plan[$i][$j][0]->unr == $this->std_plan[$i][$j+2][0]->unr) && $k<($num_rows_stunde-2))
						{
							$end_time=$row->ende;
							if (($this->std_plan[$i][$j][0]->unr == $this->std_plan[$i][$j+3][0]->unr) && $k<($num_rows_stunde-3))
								$end_time=$row->ende;
						}
					}
					else
						$end_time=$row->ende;
					//$start_time=mb_substr($start_time,0,5);
					//$end_time=mb_substr($end_time,0,5);
					//$start_date=$this->datum[year].'/'.$this->datum[mon].'/'.$this->datum[mday];

					$start_date=date("d.m.Y",$this->datum);
					$end_date=$start_date;
					if ($target=='outlook')
					{
						//"Betreff","Beginnt am","Beginnt um","Endet am","Endet um","Ganztaegiges Ereignis","Erinnerung Ein/Aus","Erinnerung am","Erinnerung um","Besprechungsplanung","Erforderliche Teilnehmer","Optionale Teilnehmer","Besprechungsressourcen","Abrechnungsinformationen","Beschreibung",
						//"Kategorien","Ort","Prioritaet","Privat","Reisekilometer","Vertraulichkeit","Zeitspanne zeigen als"
						echo $this->crlf.'"'.$this->std_plan[$i][$j][0]->lehrfach.($this->std_plan[$i][$j][0]->lehrform!=''?'-'.$this->std_plan[$i][$j][0]->lehrform:'').($lvb!=''?' - '.$lvb:'').'","'.$start_date.'","'.$start_time.'","'.$end_date.'","'.$end_time.'","Aus","Aus",,,,,,,,"Stundenplan';
						echo $this->crlf.$this->std_plan[$i][$j][0]->lehrfach.$this->crlf.$this->std_plan[$i][$j][0]->lektor.$this->crlf.$lvb.$this->crlf.$this->std_plan[$i][$j][0]->ort.'","StundenplanFH","'.$this->std_plan[$i][$j][0]->ort.'","Normal","Aus",,"Normal","2"';
					}
					else if ($target=='ical')
					{
						$sda = explode(".",$start_date);  //sda start date array
						$sta = explode(":",$start_time);	 //sta start time array
						$eda = explode(".",$end_date);    //eda end date array
						$eta = explode(":",$end_time);	 //eta end time array

						$start_date_time_ical = $sda[2].$sda[1].$sda[0].'T'.$sta[0].$sta[1].$sta[2]; //.'Z';  //neu gruppieren der Startzeit und des Startdatums
						$end_date_time_ical = $eda[2].$eda[1].$eda[0].'T'.$eta[0].$eta[1].$eta[2]; //.'Z';  //neu gruppieren der Startzeit und des Startdatums

						echo $this->crlf.'BEGIN:VEVENT'.$this->crlf
							.'UID:'.'FH'.$lvb.$this->std_plan[$i][$j][0]->ort.$this->std_plan[$i][$j][0]->lektor.$lehrfach[0].$start_date_time_ical.$this->crlf
							.'SUMMARY:'.$lehrfach[0].'  '.$this->std_plan[$i][$j][0]->ort.' - '.$lvb.$this->crlf
							.'DESCRIPTION:'.$lehrfach[0].'\n'.$this->std_plan[$i][$j][0]->lektor.'\n'.$lvb.'\n'.$this->std_plan[$i][$j][0]->ort.$this->crlf
							.'LOCATION:'.$this->std_plan[$i][$j][0]->ort.$this->crlf
							.'CATEGORIES:'.$lvplan_kategorie.$this->crlf
							.'DTSTART:'.$start_date_time_ical.$this->crlf
							.'DTEND:'.$end_date_time_ical.$this->crlf
							.'END:VEVENT';
					}
					else
					{
						echo $this->crlf.'"'.$lehrfach[0].'","'.$lvplan_kategorie.'","'.$this->std_plan[$i][$j][0]->ort.'","Stundenplan'.$this->crlf.$this->std_plan[$i][$j][0]->lehrfach.$this->crlf;
						echo $this->std_plan[$i][$j][0]->lektor.$this->crlf.$lvb.$this->crlf.$this->std_plan[$i][$j][0]->ort.'","Stundenplan",';
						echo '"'.$start_date.'","'.$start_time.'","'.$end_date.'","'.$end_time.'",,,,,';
					}
				}
			}
			$this->datum=jump_day($this->datum, 1);
		}
		return true;
	}

}

?>
