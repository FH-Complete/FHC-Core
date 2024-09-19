<?php
/* Copyright (C) 2007 fhcomplete.org
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

require_once(dirname(__FILE__).'/../config/global.config.inc.php');
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/lehrstunde.class.php');
require_once(dirname(__FILE__).'/ferien.class.php');
require_once(dirname(__FILE__).'/benutzerberechtigung.class.php');
require_once(dirname(__FILE__).'/studiengang.class.php');
require_once(dirname(__FILE__).'/mitarbeiter.class.php');
require_once(dirname(__FILE__).'/datum.class.php');
require_once(dirname(__FILE__).'/zeitsperre.class.php');
require_once(dirname(__FILE__).'/phrasen.class.php');
require_once(dirname(__FILE__).'/globals.inc.php');
require_once(dirname(__FILE__).'/sprache.class.php');
require_once(dirname(__FILE__).'/functions.inc.php');
require_once(dirname(__FILE__).'/betriebsmittel.class.php');
require_once(dirname(__FILE__).'/lehrveranstaltung.class.php');
require_once(dirname(__FILE__).'/gruppe.class.php');

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
	public $lva;			// @brief ID der Lehrveranstaltung

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
	public $ort_max_person;

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
	public $fachbereich_kurzbz;

	public $raeume = array();

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
		// Timezone setzten
		date_default_timezone_set('Europe/Vienna');
		$this->datum=time();
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
				if (!isset($this->std_plan[$i][$j][0]))
					$this->std_plan[$i][$j][0]=new stdClass();
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
	public function load_data($type, $uid, $ort_kurzbz=NULL, $studiengang_kz=NULL, $sem=NULL, $ver=NULL, $grp=NULL, $gruppe=NULL, $fachbereich_kurzbz=NULL, $lva=NULL)
	{
		// Parameter Checken
		// Typ des Stundenplans
		if ($type=='student' || $type=='lektor' || $type=='verband' || $type=='gruppe' || $type=='ort' || $type=='fachbereich' || $type=='lva')
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
			$this->ort_kurzbz = "all";
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
		elseif ($type=='verband')
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


		if ($type=='fachbereich')
		{
			if (is_null($fachbereich_kurzbz))
			{
				$this->errormsg = 'Fachbereich nicht gesetzt';
				return false;
			}
			$this->fachbereich_kurzbz=$fachbereich_kurzbz;
		}

		// LVA
		if ($type=='lva' && $lva==NULL)
		{
			$this->errormsg='Fehler: LVA-ID ist nicht gesetzt';
			return false;
		}
		elseif ($type=='lva')
		{
			$this->lva=$lva;
		}

		// Zusaetzliche Daten ermitteln
		//personendaten
		if ($this->type=='student' || $this->type=='lektor')
		{
			$this->link.='&pers_uid='.$this->pers_uid;	//Link erweitern
			if ($this->type=='student')
				$sql_query="SELECT uid, titelpre, titelpost, nachname, vorname, vornamen, studiengang_kz, semester, verband, gruppe FROM campus.vw_student WHERE uid=".$this->db_add_param($this->pers_uid);
			else
				$sql_query="SELECT uid, titelpre, titelpost, nachname, vorname, vornamen FROM campus.vw_mitarbeiter WHERE uid=".$this->db_add_param($this->pers_uid);

			if (!$this->db_query($sql_query))
			{
				$this->errormsg = 'Failed to load Personal Information';
				return false;
			}
			if ($row = $this->db_fetch_object())
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
			else
			{
				$this->errormsg = 'User nicht gefunden';
				return false;
			}
		}

		//ortdaten ermitteln
		if ($this->type=='ort' && $this->ort_kurzbz != 'all')
		{
			$sql_query="SELECT bezeichnung, ort_kurzbz, planbezeichnung, ausstattung, max_person, content_id
						FROM public.tbl_ort WHERE ort_kurzbz=".$this->db_add_param($this->ort_kurzbz);

			if (!$this->db_query($sql_query))
			{
				$this->errormsg = 'Failed to load Location Information';
				return false;
			}

			if ($row = $this->db_fetch_object())
			{
				$this->ort_bezeichnung = $row->bezeichnung;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->ort_planbezeichnung = $row->planbezeichnung;
				$this->ort_ausstattung = $row->ausstattung;
				$this->ort_max_person = $row->max_person;
				$this->ort_content_id = $row->content_id;
				$this->link.='&ort_kurzbz='.$this->ort_kurzbz;	//Link erweitern
			}
			else
			{
				$this->errormsg="Dieser Ort existiert nicht";
				return false;
			}
		}

		if ($this->type=='ort' && $this->ort_kurzbz == 'all')
		{
			$sql_query="SELECT bezeichnung, ort_kurzbz, planbezeichnung, ausstattung, max_person, content_id
					FROM public.tbl_ort WHERE lehre AND ort_kurzbz != 'Dummy'";

			if (!$this->db_query($sql_query))
			{
				$this->errormsg = 'Failed to load Location Information';
				return false;
			}

			while ($row = $this->db_fetch_object())
			{
				$obj = new stdClass();
				$obj->ort_bezeichnung = $row->bezeichnung;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->ort_planbezeichnung = $row->planbezeichnung;
				$obj->ort_ausstattung = $row->ausstattung;
				$obj->ort_max_person = $row->max_person;
				$obj->ort_content_id = $row->content_id;

				array_push($this->raeume, $obj);
			}
		}

		// Studiengangsdaten ermitteln
		if ($this->type=='student' || $this->type=='verband' || $this->type=='lva')
		{
			$sql_query="SELECT bezeichnung, kurzbz, kurzbzlang, typ, UPPER(typ||kurzbz) AS kuerzel, english
						FROM public.tbl_studiengang WHERE studiengang_kz=".$this->db_add_param($this->stg_kz);

			if (!($this->db_query($sql_query)))
			{
				$this->errormsg = 'Failed to load Degree Program';
				return false;
			}
			if ($row = $this->db_fetch_object())
			{
				$this->stg_bez = $row->bezeichnung;
				$this->stg_kurzbz = $row->typ.$row->kurzbz;
				$this->stg_kurzbzlang = $row->kurzbzlang;
				$this->stg_kuerzel = $row->kuerzel;
				$this->stg_english = $row->english;
			}
		}

		// Stundentafel abfragen
		$sql_query="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
		if (!$this->db_query($sql_query))
			die('Failed to load Timetable');
		$this->stunde = $this->db_result;

		// Studiensemesterdaten ermitteln
		$sql_query="SELECT * FROM public.tbl_studiensemester WHERE now()<ende ORDER BY start LIMIT 2";
		if (!$this->db_query($sql_query))
			die('Failed to load Studiensemester');
		else
		{
			if ($row = $this->db_fetch_object())
			{
				if (!isset($this->studiensemester_now))
					$this->studiensemester_now = new stdClass();
				$this->studiensemester_now->name=$row->studiensemester_kurzbz;
				$this->studiensemester_now->start=mktime(0,0,0,mb_substr($row->start,5,2),mb_substr($row->start,8,2),mb_substr($row->start,0,4));
				$this->studiensemester_now->ende=mktime(0,0,0,mb_substr($row->ende,5,2),mb_substr($row->ende,8,2),mb_substr($row->ende,0,4));#
			}
			if ($row = $this->db_fetch_object())
			{
				if (!isset($this->studiensemester_next))
					$this->studiensemester_next = new stdClass();
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
	public function load_week($datum, $stpl_view='stundenplan', $alle_unr_mitladen=false)
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
		$anz=$this->wochenplan->load_lehrstunden($this->type,$this->datum_begin,$this->datum_end,$this->pers_uid,$this->ort_kurzbz,$this->stg_kz,$this->sem,$this->ver,$this->grp,$this->gruppe_kurzbz, $stpl_view, null,$this->fachbereich_kurzbz,$this->lva, $alle_unr_mitladen);
		if ($anz<0)
		{
			$this->errormsg=$this->wochenplan->errormsg;
			return false;
		}

		// Stundenplandaten aufbereiten
		for ($i=0;$i<$anz;$i++)
		{
			$idx=0;
			$mtag=mb_substr($this->wochenplan->lehrstunden[$i]->datum, 8,2);
			$month=mb_substr($this->wochenplan->lehrstunden[$i]->datum, 5,2);
			$jahr=mb_substr($this->wochenplan->lehrstunden[$i]->datum, 0,4);
			$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
			if ($tag==0)
				$tag=7; //Sonntag

			$stunde=$this->wochenplan->lehrstunden[$i]->stunde;
			// naechste freie Stelle im Array suchen
			while (isset($this->std_plan[$tag][$stunde][$idx]->lektor_uid))
				$idx++;

			if (!isset($this->std_plan[$tag][$stunde][$idx]))
				$this->std_plan[$tag][$stunde][$idx]=new stdClass();
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
				$this->std_plan[$tag][$stunde][$idx]->titel=$this->wochenplan->lehrstunden[$i]->titel;
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
		$sprache = getSprache();
		$p=new phrasen($sprache);

		echo '<TABLE width="100%" border="0" cellspacing="0">'.$this->crlf;
		echo '	<TR>'.$this->crlf;
		echo '		<TD style="padding-bottom: 5px;" valign="top">'.$this->crlf;
		echo '			<P valign="top">';
		if ($this->type=='student' || $this->type=='lektor')
			echo '<strong>'.$p->t('global/person').': </strong>'.$this->pers_titelpre.' '.$this->pers_vorname.' '.$this->pers_nachname.' '.$this->pers_titelpost.' - '.$this->pers_uid.'<br>';
		if ($this->type=='student' || $this->type=='verband')
		{
			echo '<strong>'.$p->t('global/studiengang').': </strong>'.$this->stg_kuerzel.' - '.($sprache=='English' && $this->stg_english!=''?$this->stg_english:$this->stg_bez).'<br>';
			echo $p->t('global/semester').': '.$this->sem.'<br>';
			if ($this->ver!='0' && $this->ver!='' && $this->ver!=null)
				echo $p->t('global/verband').': '.$this->ver.'<br>';
			if ($this->grp!='0' && $this->grp!='' && $this->grp!=null)
				echo $p->t('global/gruppe').': '.$this->grp.'<br>';
			$this->link.='&stg_kz='.$this->stg_kz.'&sem='.$this->sem.'&ver='.$this->ver.'&grp='.$this->grp;
		}
		if ($this->type=='ort' && $this->ort_kurzbz != 'all')
		{
			echo '<strong>'.$p->t('lvplan/raum').': </strong>'.$this->ort_kurzbz;
			echo ' - '.$this->ort_bezeichnung;
			echo ($this->ort_max_person!=''?' - ( '.$this->ort_max_person.' '.$p->t('lvplan/personen').' )':'');
			echo ($this->ort_content_id!=''?' - <a href="../../../cms/content.php?content_id='.$this->ort_content_id.'" target="_self">'.$p->t('lvplan/rauminformationenAnzeigen').'</a>':'');
			echo '<span id="software"></span>';
			echo '<br>'.$this->ort_ausstattung;
		}
		if ($this->type=='lva')
		{
			$this->link.='&lva='.$this->lva;
			$lehrveranstaltung = new lehrveranstaltung();
			$lehrveranstaltung->load($this->lva);
			$studiengang = new studiengang();
			$studiengang->load($lehrveranstaltung->studiengang_kz);
			echo '<strong>'.$p->t('global/lehrveranstaltung').': </strong>'.$lehrveranstaltung->bezeichnung_arr[$sprache].' / '.$studiengang->kuerzel.'-'.$lehrveranstaltung->semester.'<br>';
		}
		echo '</P>'.$this->crlf;
		echo '			<table class="stdplan" style="width: auto; margin: auto;" valign="bottom" align="center">';
		echo '			<tr><td  style="padding:3px 15px 0px 15px; margin: 0,0,20px,0;" align="center">'.$this->crlf;

		//Kalender
		$this->kal_link.='&pers_uid='.$this->pers_uid.'&ort_kurzbz='.$this->ort_kurzbz.'&stg_kz='.$this->stg_kz.'&sem='.$this->sem.'&ver='.$this->ver.'&grp='.$this->grp.'&gruppe_kurzbz='.$this->gruppe_kurzbz.'&lva='.$this->lva;
		$kal_link_ws=$this->kal_link.'&begin='.$this->studiensemester_now->start.'&ende='.$this->studiensemester_now->ende;
		$kal_link_ss=$this->kal_link.'&begin='.$this->studiensemester_next->start.'&ende='.$this->studiensemester_next->ende;

		echo 				$this->crlf;
		echo				$this->studiensemester_now->name.'<br>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=html" target="_blank" title="HTML"><IMG src="../../../skin/images/html.png" height="30" alt="HTML" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=excel" title="excel"><IMG src="../../../skin/images/xls.png" height="30" alt="Excel" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=csv" title="CSV"><IMG src="../../../skin/images/csv.png" height="30" alt="CSV" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=csv&target=outlook" title="CSV-Outlook"><IMG src="../../../skin/images/outlook.png" height="30" alt="CSV-Outlook" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=ical&version=1&target=ical" title="iCal Version 1.0"><IMG src="../../../skin/images/ical1.0.png" height="30" alt="vCal Version 1.0" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ws.'&format=ical&version=2&target=ical" title="iCal Version 2.0"><IMG src="../../../skin/images/ical2.0.png" height="30" alt="vCal Version 2.0" border="0"></A>'.$this->crlf;
		echo '				</td><td class="stdplan" style="padding:8px;" align="center">'.$p->t('lvplan/semesterplaene').'</td><td style="padding:3px 15px 0px 15px;" align="center">';
		echo '				<span style="color:#999">'.$this->studiensemester_next->name.'</span><br>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=html" target="_blank" title="HTML"><IMG src="../../../skin/images/html_light.png" height="30" alt="HTML" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=excel" title="excel"><IMG src="../../../skin/images/xls_light.png" height="30" alt="Excel" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=csv" title="CSV"><IMG src="../../../skin/images/csv_light.png" height="30" alt="CSV" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=csv&target=outlook" title="CSV-Outlook"><IMG src="../../../skin/images/outlook_light.png" height="30" alt="CSV-Outlook" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=ical&version=1&target=ical" title="iCal Version 1.0"><IMG src="../../../skin/images/ical1.0_light.png" height="30" alt="iCal Version 1.0" border="0"></A>'.$this->crlf;
		echo '				<A href="'.$kal_link_ss.'&format=ical&version=2&target=ical" title="iCal Version 2.0"><IMG src="../../../skin/images/ical2.0_light.png" height="30" alt="iCal Version 2.0" border="0"></A>'.$this->crlf;
		echo '			</td></tr></table>'.$this->crlf;
		echo '		</TD>'.$this->crlf;

		// Kalenderjump
		echo '	</TR>'.$this->crlf;
		echo '</TABLE>'.$this->crlf.$this->crlf;

		// Jump Wochenweise
		if ($this->type=='verband')
			$link_parameter='&stg_kz='.$this->stg_kz.'&sem='.$this->sem.'&ver='.$this->ver.'&grp='.$this->grp;
		if ($this->type=='student' || $this->type=='lektor')
			$link_parameter='&pers_uid='.$this->pers_uid;
		if ($this->type=='lva')
			$link_parameter='&lva='.$this->lva;

		// Ort Jump
		if ($this->type=='ort')
		{
			// Orte abfragen
			$sql_query="SELECT * FROM public.tbl_ort WHERE aktiv AND lehre ORDER BY ort_kurzbz";
			if (!$this->db_query($sql_query))
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

			if (isset($prev_ort))
			{
				echo '		<a style="text-decoration:none" href="stpl_week.php?type='.$this->type.'&datum='.$this->datum.'&ort_kurzbz='.$prev_ort->ort_kurzbz.'">'.$this->crlf;
				echo '		&nbsp;&nbsp;<img class="lvplanbutton" src="../../../skin/images/left_lvplan.png" title="'.$prev_ort->ort_kurzbz.'" />&nbsp;&nbsp;'.$this->crlf;
				echo '		</a>'.$this->crlf;
			}
			echo "		<SELECT name=\"select\" onChange=\"MM_jumpMenu('self',this,0)\" class=\"xxxs_black\">".$this->crlf;
			for ($i=0;$i<$num_rows_ort;$i++)
			{
				$row=$this->db_fetch_object (null, $i);
				echo '			<OPTION value="stpl_week.php?type=ort&ort_kurzbz='.$row->ort_kurzbz.'&datum='.$this->datum.'"';
				if ($row->ort_kurzbz==$this->ort_kurzbz)
					echo ' selected ';
				echo ">$row->ort_kurzbz ($row->bezeichnung)</option>".$this->crlf;
			}
			echo '		</SELECT>'.$this->crlf;
			if (isset($next_ort))
			{
				echo '		<a style="text-decoration:none" href="stpl_week.php?type='.$this->type.'&datum='.$this->datum.'&ort_kurzbz='.$next_ort->ort_kurzbz.'">'.$this->crlf;
				echo '		&nbsp;&nbsp;<img class="lvplanbutton" src="../../../skin/images/right_lvplan.png" title="'.$next_ort->ort_kurzbz.'">&nbsp;&nbsp;'.$this->crlf;
				echo '		</a>'.$this->crlf;
			}
			echo '	</p></form>';
			$link_parameter='&ort_kurzbz='.$this->ort_kurzbz;
		}
		echo '	<p style="color:grey; font-size:17px; vertical-align:center; margin-bottom:0px;" align="center">';
		// 4 Wochen zurueck
		echo '<a style="text-decoration:none" href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_prev4week.'">'.$this->crlf;
		echo '<img class="lvplanbutton" src="../../../skin/images/moreleft_lvplan.png" title="'.$p->t('lvplan/vierWochenZurueck').'">'.$this->crlf;
		echo '</a>';
		// 1 Woche zurueck
		echo '<a style="text-decoration:none" href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_prevweek;
		echo '">&nbsp;&nbsp;<img class="lvplanbutton" src="../../../skin/images/left_lvplan.png" title="'.$p->t('lvplan/eineWocheZurueck').'">&nbsp;&nbsp;</a>';
		// Aktuelle KW
		echo '<a style="text-decoration:none" href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '" title="'.$p->t('lvplan/aktuelleKW').'">'.$p->t('eventkalender/kw').' '.$this->kalenderwoche;
		echo '</a>';
		// 1 Woche nach vor
		echo '<a style="text-decoration:none" href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_nextweek;
		echo '">&nbsp;&nbsp;<img class="lvplanbutton" src="../../../skin/images/right_lvplan.png" title="'.$p->t('lvplan/eineWocheVor').'">&nbsp;&nbsp;</a>';
		// 4 Wochen nach vor
		echo '<a style="text-decoration:none" href="stpl_week.php?type='.$this->type;
		echo $link_parameter;
		echo '&datum='.$this->datum_next4week;
		echo '"><img class="lvplanbutton" src="../../../skin/images/moreright_lvplan.png" title="'.$p->t('lvplan/vierWochenVor').'"></a>';
		echo '</p>';
		//Kalenderjump mit Hoverbox
		$this->jahreskalenderjump_hoverbox($this->link);
		return true;
	}

	/**
	 * Zeichnen der Stundenplanwoche in HTML
	 */
	public function draw_week($raumres, $user_uid='', $gruppieren=LVPLAN_LEHREINHEITEN_GRUPPIEREN)
	{
		global $tagbez;
		$sprache = getSprache();
		$spracheLoad = new sprache();
		$spracheLoad->load($sprache);
		$p=new phrasen($sprache);

		$o_datum=new datum();
		// Stundentafel abfragen
		$sql_query="SELECT stunde, beginn, ende FROM lehre.tbl_stunde ORDER BY stunde";
		if (!$this->db_query($sql_query))
			die($this->db_last_error());
		$result_stunde = $this->db_result;
		$num_rows_stunde = $this->db_num_rows($result_stunde);
		$raumReservierbar = true;

		$stundenplandev_belegt = array();
		// Formularbeginn wenn Lektor
		if ($raumres && $this->type=='ort')
		{
			$ort = new ort();
			$ort->load($this->ort_kurzbz);
			if ($ort->reservieren)
			{
				echo '<form name="reserve" method="post" action="stpl_week.php">'.$this->crlf;

				// In tbl_stundenplandev schauen ob dort der Raum bereits belegt ist
				$qry = "SELECT distinct datum, stunde FROM lehre.tbl_stundenplandev
						WHERE datum>=".$this->db_add_param($this->datum_begin)."
						AND datum<=".$this->db_add_param($this->datum_end)."
						AND ort_kurzbz=".$this->db_add_param($this->ort_kurzbz);
				if($result_devbelegt = $this->db_query($qry))
				{
					while($row_devbelegt = $this->db_fetch_object($result_devbelegt))
					{
						$stundenplandev_belegt[$row_devbelegt->datum][$row_devbelegt->stunde]=true;
					}
				}
			}
			else
			{
				$raumres = false;
				$raumReservierbar = false;
			}
		}

		//Tabelle zeichnen
		echo '	<table class="stdplan" width="100%" border="0" cellpadding="1" cellspacing="1" name="Stundenplantabelle" align="center">'.$this->crlf;
		// Kopfzeile darstellen
		echo '<thead><tr>'.$this->crlf;
		echo '			<th align="right">'.$p->t('global/stunde').'&nbsp;<br>'.$p->t('global/beginn').'&nbsp;<br>'.$p->t('global/ende').'&nbsp;</th>'.$this->crlf;
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
		echo '</tr></thead><tbody>'.$this->crlf;
		// Von Montag bis Samstag
		$datum_now=time();
		$datum_res_lektor_start=jump_day($datum_now,(RES_TAGE_LEKTOR_MIN)-1);
		$datum_res_lektor_ende=$o_datum->mktime_fromdate(RES_TAGE_LEKTOR_BIS);
		if (!date("w",$this->datum))
			$this->datum=jump_day($this->datum,1);
		$datum=$datum_mon=$this->datum;
		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($user_uid);
		$reservberechtigt = $rechte->isBerechtigt('lehre/reservierung:begrenzt', null, 'suid');

		for ($i=1; $i<=TAGE_PRO_WOCHE; $i++)
		{
			echo '<tr><td>'.$tagbez[$spracheLoad->index][$i].'<br>'.strftime("%e. %b %Y",$datum).'<br></td>'.$this->crlf;
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
					if (isset($anmerkung))
						unset($anmerkung);
					if (isset($titel_arr))
						unset($titel_arr);
					$reservierung=false;
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
					{
						$unr[]=$lehrstunde->unr;
						// Lektoren
						$lektor[]=$lehrstunde->lektor;
						// Lehrverband
						$typ='';
						if ($lehrstunde->reservierung)
						{
							$studiengang = new studiengang();
							$studiengang->load($lehrstunde->stg_kz);
							$typ = $studiengang->typ;
						}

						$lvb=$typ.$lehrstunde->stg.'-'.$lehrstunde->sem;
						$stg = $lehrstunde->stg_kz;
						if ($lehrstunde->ver!=null && $lehrstunde->ver!='0' && $lehrstunde->ver!='')
						{
							$lvb.=$lehrstunde->ver;
							if ($lehrstunde->grp!=null && $lehrstunde->grp!='0' && $lehrstunde->grp!='')
								$lvb.=$lehrstunde->grp;
						}
						if ($lehrstunde->gruppe_kurzbz != '')
							$lvb=$lehrstunde->gruppe_kurzbz;

						$lehrverband[]=$lvb;
						// Lehrfach
						$lf=$lehrstunde->lehrfach;
						if (isset($lehrstunde->lehrform))
							$lf.='-'.$lehrstunde->lehrform;
						$lehrfach[]=$lf;
						$titel=$lehrstunde->titel;
						$titel_arr[]=$lehrstunde->titel;
						$anmerkung[]=$lehrstunde->anmerkung;
						if (!$reservierung)
							$reservierung=$lehrstunde->reservierung;
					}

					$datum_res_lektor_start_m = date('Y-m-d', $datum_res_lektor_start);
					$datum_res_lektor_ende_m = date('Y-m-d', $datum_res_lektor_ende);
					$datum_m = date('Y-m-d',$datum);

					$showdelete = $raumres && $this->type=='ort' && (/*$datum>=$datum_now && */$datum_m>=$datum_res_lektor_start_m && $datum_m<=$datum_res_lektor_ende_m);

					if ($gruppieren)
					{
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
								$lkt.='<BR />'.$l;
						}
						else
							$lkt='<BR />'.$lektor[0];

						// Lehrverband
						if ($this->type!='verband')
						{
							$lehrverband=array_unique($lehrverband);
							sort($lehrverband);
							$lvb='';
							foreach ($lehrverband as $l)
								$lvb.='<BR />'.$l;
						}
						else
							$lvb='<BR />'.$lehrverband[0];

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

						if (LVPLAN_ANMERKUNG_ANZEIGEN)
						{
							$anmerkung=array_unique($anmerkung);
							sort($anmerkung);
							$anm='';
							foreach ($anmerkung as $a)
								if ($a!='')
									$anm.='<BR />'.$this->convert_html_chars($a);
								else
									$anm='';
						}

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

						$ort = $this->std_plan[$i][$j][0]->ort;

						if ($showdelete)
						{
							$this->stpltable_deletelink($ort, $datum, $j, $user_uid, $reservberechtigt);
						}

						// Link zu Details setzten
						echo '<A class="stpl_detail" onClick="window.open(';
						echo "'stpl_detail.php";
						echo '?type='.$this->type.'&datum='.date("Y-m-d",$datum).'&stunde='.$j;
						echo '&pers_uid='.$this->pers_uid;
						echo '&stg_kz='.$this->stg_kz;
						echo '&sem='.$this->sem;
						echo '&ver='.$this->ver;
						echo '&grp='.$this->grp;
						echo '&ort_kurzbz='.$ort;
						echo "','Details', 'height=320,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');return false;";
						echo '" title="'.$this->convert_html_chars($titel).'" ';
						echo ' href="#">';

						// Ausgabe
						echo mb_substr($lf, 0,-strlen('<BR />'));

						if ($titel!='' && !$reservierung)
						{
							echo '<img src="../../../skin/images/sticky.png" tooltip="'.$this->convert_html_chars($titel).'"/>';
						}

						if ($this->type=='ort' || $this->type=='lektor' || $this->type=='verband')
							echo $lvb;
						if ($this->type!='lektor')
							echo $lkt;
						if ($this->type!='ort')
							echo '<BR />'.$this->std_plan[$i][$j][0]->ort;
						if (LVPLAN_ANMERKUNG_ANZEIGEN)
						{
							echo $anm;
						}
						echo '</A></DIV>'.$blink_aus.'</td>'.$this->crlf;
					}
					else
					{
						// mehrere Einheiten innerhalb einer Stunde sollen getrennt aufgelistet werden
						$uEinheiten=array();
						for ($n=0;$n<count($unr);$n++)
						{
							$unrIndex=$this->searchForId($unr[$n], $uEinheiten);
							if ($unrIndex===false)
							{
								$unrIndex=count($uEinheiten);
								$uEinheiten[$unrIndex]['unr']=$unr[$n];
								$uEinheiten[$unrIndex]['lehrfach']=$lehrfach[$n];
								if (isset($this->std_plan[$i][$j][$n]->farbe))
									$uEinheiten[$unrIndex]['farbe']=$this->std_plan[$i][$j][$n]->farbe;
							}
							$uEinheiten[$unrIndex]['ort'][]=$this->std_plan[$i][$j][$n]->ort;
							$uEinheiten[$unrIndex]['lehrverband'][]=$lehrverband[$n];
							$uEinheiten[$unrIndex]['anmerkung'][]=$anmerkung[$n];
							$uEinheiten[$unrIndex]['lektor'][]=$lektor[$n];
							$uEinheiten[$unrIndex]['titel'][]=$titel_arr[$n];
						}

						// Ausgabe einer Stunde im Raster (HTML)
						echo '				<td nowrap valign="top" align="center">';

						foreach($uEinheiten as $key=>$uEinheit)
						{
							echo '<DIV align="center" ';
							if (isset($uEinheit['farbe']))
								echo 'style="background-color: #'.$uEinheit['farbe'].'; margin-bottom: 3px;"';
							echo '>';

							// Löschlink anzeigen
							if ($showdelete)
							{
								$this->stpltable_deletelink($uEinheit['ort'][0], $datum, $j, $user_uid, $reservberechtigt);
							}

							// Link zu Details setzten
							echo '<A class="stpl_detail" onClick="window.open(';
							echo "'stpl_detail.php";
							echo '?type='.$this->type.'&datum='.date("Y-m-d",$datum).'&stunde='.$j;
							echo '&pers_uid='.$this->pers_uid;
							echo '&stg_kz='.$this->stg_kz;
							echo '&sem='.$this->sem;
							echo '&ver='.$this->ver;
							echo '&grp='.$this->grp;
							echo '&ort_kurzbz='.$uEinheit['ort'][0];
							echo "','Details', 'height=320,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');return false;";
							echo '" title="'.$this->convert_html_chars($uEinheit['titel'][0]).'" ';
							echo ' href="#">';

							// Ausgabe
							echo $uEinheit['lehrfach'];

							if ($uEinheit['titel'][0]!='' && !$reservierung)
							{
								echo '<img src="../../../skin/images/sticky.png" tooltip="'.$this->convert_html_chars($uEinheit['titel'][0]).'"/>';
							}
							echo '<BR />';
							if ($this->type=='ort' || $this->type=='lektor' || $this->type=='verband' || $this->type=='lva')
							{
								$uEinheit['lehrverband']=array_unique($uEinheit['lehrverband']);
								foreach($uEinheit['lehrverband'] as $ueLehrverband)
									echo $ueLehrverband."<BR />";
							}
							if ($this->type!='lektor')
							{
								$uEinheit['lektor']=array_unique($uEinheit['lektor']);
								foreach($uEinheit['lektor'] as $ueLektor)
									echo $ueLektor."<BR />";
							}
							if ($this->type!='ort' || $this->ort_kurzbz == 'all')
							{
								$uEinheit['ort']=array_unique($uEinheit['ort']);
								foreach($uEinheit['ort'] as $ueOrt)
									echo $ueOrt."<BR />";
							}
							if (LVPLAN_ANMERKUNG_ANZEIGEN)
							{
								$uEinheit['anmerkung']=array_unique($uEinheit['anmerkung']);
								foreach($uEinheit['anmerkung'] as $ueAnmerkung)
									if ($ueAnmerkung!='')
										echo $ueAnmerkung."<BR />";
							}
							echo '</A></DIV>';
						}
						if ($rechte->isBerechtigt('lehre/reservierungAdvanced', null, 'sui'))
							echo '<INPUT type="checkbox" name="reserve'.$i.'_'.$j.'" value="'.date("Y-m-d",$datum).'">';
						echo '</td>'.$this->crlf;
					}
				}
				else
				{
					echo '				<td valign="center" align="center">';
					$datum_res_lektor_start_m = date('Y-m-d', $datum_res_lektor_start);
					$datum_res_lektor_ende_m = date('Y-m-d', $datum_res_lektor_ende);
					$datum_m = date('Y-m-d',$datum);
					if ($raumres && $this->type=='ort' && ($datum_m>=$datum_res_lektor_start_m && $datum_m<=$datum_res_lektor_ende_m))
					{
						// Wenn die Stunde bereits in der tbl_stundenplandev verplant ist
						// darf die Stunde nicht reserviert werden
						if(!isset($stundenplandev_belegt[$datum_m][$j]))
							echo '<INPUT type="checkbox" name="reserve'.$i.'_'.$j.'" value="'.date("Y-m-d",$datum).'">';
						else
							echo $p->t('lvplan/reserviertInDev');
					}
					echo '</td>'.$this->crlf;
				}
			}
			echo '		</tr>'.$this->crlf;
			$datum=jump_day($datum, 1);
		}
		echo '	</tbody></table>'.$this->crlf;
		if ($raumres && $this->type=='ort' && ($datum>=$datum_now && $datum>=$datum_res_lektor_start && $datum_mon<=$datum_res_lektor_ende))
		{
			$check_all_checkbox='';

			echo '<br><table><tr id="firstinputrow">';
			echo '	<td>'.$p->t('global/titel').':</td><td><input onchange="if (this.value.length>0 && document.getElementById(\'beschreibung\').value.length<1) {document.getElementById(\'beschreibung\').value=document.getElementById(\'titel\').value;document.getElementById(\'beschreibung\').focus();};" type="text" id="titel"  name="titel" size="10" maxlength="10" value="" /></td> '.$this->crlf;
			echo '	<td>'.$p->t('global/beschreibung').':</td><td colspan="5"> <input onchange="if (this.value.length<1 && document.getElementById(\'titel\').value.length>0) {alert(\'Achtung! Speichern nur mit Beschreibung moeglich!\');this.focus();};" type="text" id="beschreibung" name="beschreibung" size="20" maxlength="32" value=""  /> </td>'.$this->crlf;

			//Pruefen ob die erweiterte Reservierungsrechte vorhanden sind
			if ($rechte->isBerechtigt('lehre/reservierung', null, 'sui'))
			{
				$check_all_checkbox='';
				//Lektor

				echo "<td>".$p->t('lvplan/lektor').":</td>
					  <td><input class='search' placeholder='".$p->t('lvplan/nameEingeben')."' type='text' id='user_uid' size='32' value=''>".$this->crlf;
				echo '</td>'.$this->crlf;
				echo '<td>&nbsp;</td>';
				echo '</tr>';
				echo '<tr>'.$this->crlf;

				//Studiengaenge Laden fuer die eine erweiterte Reservierungsberechtigung vorhanden ist
				$stg = new studiengang();
				$stg->loadArray($rechte->getStgKz('lehre/reservierung'),'typ, kurzbz',true);

				//Studiengang
				echo '<td>'.$p->t('global/studiengang').':</td><td> <SELECT name="studiengang_kz">'.$this->crlf;
				echo '<OPTION value="0">*</OPTION>'.$this->crlf;
				foreach($stg->result as $row)
				{
					echo '<OPTION value="'.$row->studiengang_kz.'">'.$row->kuerzel.' ('.$row->kurzbzlang.')</OPTION>'.$this->crlf;
				}
				echo '</SELECT></td>';

				//Semester
				echo '<td>'.$p->t('global/semester').':</td>
					<td>
					<SELECT name="semester">
						<OPTION value="">*</OPTION>
						<OPTION value="1">1</OPTION>
						<OPTION value="2">2</OPTION>
						<OPTION value="3">3</OPTION>
						<OPTION value="4">4</OPTION>
						<OPTION value="5">5</OPTION>
						<OPTION value="6">6</OPTION>
						<OPTION value="7">7</OPTION>
						<OPTION value="8">8</OPTION>
					</SELECT>
					</td>
					'.$this->crlf;

				//Verband
				echo '<td>'.$p->t('global/verband').':</td>
					<td>
					<SELECT name="verband">
						<OPTION value="">*</OPTION>
						<OPTION value="A">A</OPTION>
						<OPTION value="B">B</OPTION>
						<OPTION value="C">C</OPTION>
						<OPTION value="D">D</OPTION>
						<OPTION value="E">E</OPTION>
						<OPTION value="F">F</OPTION>
						<OPTION value="V">V</OPTION>
					</SELECT>
					</td>'.$this->crlf;

				//Gruppe
				echo '<td>'.$p->t('global/gruppe').':</td>
					<td>
					<SELECT name="gruppe">
						<OPTION value="">*</OPTION>
						<OPTION value="1">1</OPTION>
						<OPTION value="2">2</OPTION>
						<OPTION value="3">3</OPTION>
						<OPTION value="4">4</OPTION>
					</SELECT>
					</td>'.$this->crlf;

				//Spezialgruppe
				echo '<td>'.$p->t('lvplan/spezialgruppe').':</td><td><SELECT name="gruppe_kurzbz">'.$this->crlf;
				echo '<OPTION value="">*</OPTION>'.$this->crlf;

				//Spezialgruppen aus den Studiengaengen mit erweiterten Reservierungsberechtigung holen
				$stgs = $rechte->getStgKz('lehre/reservierung');
				if (count($stgs)>0)
				{
					$in='';
					foreach($stgs as $stg)
					{
						$in .= $this->db_add_param($stg).",";
					}
					$in = substr($in, 0, -1);
					$qry = "SELECT * FROM public.tbl_gruppe WHERE studiengang_kz in($in) AND lehre=true AND sichtbar=true ORDER BY gruppe_kurzbz";
					if ($result = $this->db_query($qry))
					{
						while ($row = $this->db_fetch_object($result))
						{
							echo '<OPTION value="'.$row->gruppe_kurzbz.'">'.$row->gruppe_kurzbz.'</OPTION>'.$this->crlf;
						}
					}
				}
				echo '</SELECT></td>'.$this->crlf;
				echo '<td><input type="checkbox" name="check_all" onclick="toggle_checkboxes(this);" /> alle auswählen</td>'.$this->crlf;
				echo '</tr><tr>';

			}
			else
			{
				echo '	<input type="hidden" name="user_uid" value="'.$this->user_uid.'" />'.$this->crlf;
			}

			echo '<td>';
			echo '  <input type="submit" name="reserve" value="Reservieren" />'.$this->crlf;
			echo '	<input type="hidden" name="ort_kurzbz" value="'.$this->ort_kurzbz.'" />'.$this->crlf;
			echo '	<input type="hidden" name="datum" value="'.$this->datum.'" />'.$this->crlf;
			echo '	<input type="hidden" name="type" value="'.$this->type.'" />'.$this->crlf;
			echo '</td>';

			echo '</tr></table></form>';
			echo ' <a href="stpl_reserve_list.php">'.$p->t('lvplan/meineReservierungenAnzeigen').' </a>';
		}
		elseif ($this->type=='ort' && $raumReservierbar == false)
		{
			echo '<table><tr><td><br>';
			echo '<span style="color: orange">'.$p->t('lvplan/raumNichtReservierbar').'</span>';
			echo '</td></tr></table>';
		}
		else
		{
			if ($this->type=='ort')
			{
				echo '<table><tr><td><br>';
				echo '<span style="color: orange">'.$p->t('lvplan/raumreservierungAufZeitraumEingeschraenkt',array(date("d.m.Y",$datum_res_lektor_start),date("d.m.Y",$datum_res_lektor_ende))).'</span>';
				echo '</td></tr></table>';
			}
		}
	}

	/**
	 * Funktion draw_week_xul Stundenplan im XUL-Format
	 *
	 * @param datum Datum eines Tages in der angeforderten Woche
	 * @return true oder false
	 */
	public function draw_week_xul($semesterplan, $uid, $wunsch=null, $ignore_kollision=false, $kollision_student=false, $max_kollision=0)
	{
		global $cfgStdBgcolor;
		$count=0;
		$berechtigung=new benutzerberechtigung();
		$berechtigung->getBerechtigungen($uid);
		// Stundentafel abfragen
		$sql_query="SELECT * FROM lehre.tbl_stunde ORDER BY stunde";
		if (!$this->db_query($sql_query))
			$this->errormsg=$this->db_last_error();
		$result_stunde = $this->db_result;
		$num_rows_stunde=$this->db_num_rows($result_stunde);

		// Kontext Menue
		echo '<popupset>
				<menupopup id="stplPopupMenue">
					<menuitem label="Ressourcen zuordnen" oncommand="TimeTableWeekMarkiere(document.popupNode);BetriebsmittelZuordnen(document.popupNode);" />
					<menuitem label="Raumvorschlag" oncommand="StplSearchRoom(document.popupNode);" />
					<menuitem label="Entfernen" oncommand="TimeTableWeekMarkiere(document.popupNode);TimetableDeleteEntries()" />
				</menupopup>
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
			</vbox>'.$this->crlf;
		$stunden_arr=array();
		for ($i=0;$i<$num_rows_stunde; $i++)
		{
			$row=$this->db_fetch_object($result_stunde,$i);
			$beginn=mb_substr($row->beginn,0,5);
			$ende=mb_substr($row->ende,0,5);
			$stunden_arr[$row->stunde]['beginn']=$beginn;
			$stunden_arr[$row->stunde]['ende']=$ende;
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
			echo '<html:div><html:small>'.date("l",$datum).'<html:br /></html:small>'.date("j.m. y",$datum).'</html:div>';
			echo '</vbox>';
			for ($k=0; $k<$num_rows_stunde; $k++)
			{
				$tooltip='';
				$row = $this->db_fetch_object($result_stunde, $k);
				$j=$row->stunde;
				if (isset($wunsch[$i][$j]))
				{
					$index=$wunsch[$i][$j];

					// Negative und positive Zeitsperren beruecksichtigen
					if ($index==-3 || $index == 4)
					{
						//Wenn eine Zeitsperre eingetragen ist, dann diese im Tooltiptext anzeigen
						$zeitsperre = new zeitsperre();
						$zeitsperre->getSperreByDate($this->pers_uid, date('Y-m-d',$datum), $j);
						$datum_obj = new datum();
						foreach($zeitsperre->result as $sperren)
						{
							if ($tooltip!='')
								$tooltip.=', ';
							$aenderungsdatum = $sperren->insertamum;
							if($sperren->updateamum>$sperren->insertamum)
								$aenderungsdatum = $sperren->updateamum;
							$updateinfo = '';
							if ($aenderungsdatum != '')
								$updateinfo = ' (letzte Änderung: '.$datum_obj->formatDatum($aenderungsdatum,'d.m.Y H:i').')';
							$tooltip.=$sperren->zeitsperretyp_kurzbz.' - '.$sperren->bezeichnung.$updateinfo;
						}
					}
				}
				else
					$index=1;
				if ($index=='')
					$index=1;
				$bgcolor=$cfgStdBgcolor[$index+3];

				// Sonntag wie Ferien markieren
				if ($i==7)
					$bgcolor='#FFFF55';

				if ($isferien)
				{
					$bgcolor='#FFFF55';

					//Wenn Ferien eingetragen sind, dann die Bezeichnung im Tooltiptext anzeigen
					foreach($ferien->getFerien($datum) as $bezeichnung)
					{
						if ($tooltip!='')
							$tooltip.=', ';
						$tooltip .= $bezeichnung;
					}
				}
				echo '<vbox class="stplweek_vbox" style="border:1px solid black; background-color:'.$bgcolor.'"';
				if ($tooltip!='')
				{
					echo ' tooltiptext="'.$this->convert_html_chars($tooltip).'"';
				}
				echo '
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

					$kollision=-1;
					unset($kollisionsmeldungen);
					if (isset($a_unr))
						unset($a_unr);
					if (isset($a_lvb))
						unset($a_lvb);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
					{
						$a_unr[]=$lehrstunde->unr;
						if($lehrstunde->gruppe_kurzbz!='')
						{
							$gruppe = new gruppe();
							$gruppe->load($lehrstunde->gruppe_kurzbz);
							if($gruppe->direktinskription)
								continue;
						}
						$a_lvb[$lehrstunde->unr][]=$lehrstunde->sem.$lehrstunde->ver.$lehrstunde->grp;
					}

					// Unterrichtsnummer (Kollision?)
					$a_unr=array_unique($a_unr);
					$kollision+=count($a_unr);
					//Kollisionspruefung Studentenebene
					if ($kollision_student=='true')
					{
						$kollision=0;
						$studiensemester = getStudiensemesterFromDatum(date('Y-m-d',$datum));

						$qry = "SELECT datum, stunde, student_uid, count(student_uid) AS anzahl
								FROM (
									SELECT sub_stpl_uid.unr, sub_stpl_uid.datum, sub_stpl_uid.stunde, sub_stpl_uid.student_uid
									FROM (  SELECT stpl.unr, stpl.datum, stpl.stunde, tbl_benutzergruppe.uid AS student_uid
									               FROM lehre.tbl_stundenplandev stpl
									          JOIN public.tbl_benutzergruppe USING (gruppe_kurzbz)
									         WHERE tbl_benutzergruppe.studiensemester_kurzbz::text = ".$this->db_add_param($studiensemester)."
									         GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_benutzergruppe.uid
									UNION
									             SELECT stpl.unr, stpl.datum, stpl.stunde, tbl_studentlehrverband.student_uid
									               FROM lehre.tbl_stundenplandev stpl
									          JOIN public.tbl_studentlehrverband ON stpl.gruppe_kurzbz IS NULL AND stpl.studiengang_kz = tbl_studentlehrverband.studiengang_kz AND stpl.semester = tbl_studentlehrverband.semester AND (stpl.verband = tbl_studentlehrverband.verband OR stpl.verband = ' '::bpchar AND stpl.verband <> tbl_studentlehrverband.verband) AND (stpl.gruppe = tbl_studentlehrverband.gruppe OR stpl.gruppe = ' '::bpchar AND stpl.gruppe <> tbl_studentlehrverband.gruppe)
									         WHERE tbl_studentlehrverband.studiensemester_kurzbz::text = ".$this->db_add_param($studiensemester)."
											GROUP BY stpl.unr, stpl.datum, stpl.stunde, tbl_studentlehrverband.student_uid) sub_stpl_uid
									GROUP BY sub_stpl_uid.unr, sub_stpl_uid.datum, sub_stpl_uid.stunde, sub_stpl_uid.student_uid

								) as a
								WHERE datum='".date('Y-m-d',$datum)."' AND stunde=".$this->db_add_param($j)."
								GROUP BY datum, stunde, student_uid
								HAVING count(student_uid)>1 ";

								if (count($a_unr)>0)
								{
									// Nur die Eintraege als kollision anzeigen, die auch aktuell im Tempus sichtbar sind
									// Dazu werden die UNRs der betroffenen Studierenden zuerst ein ein Array gruppiert und danach
									// verglichen ob die angezeigte UNR dabei ist.
									// Etwas kompliziert aber performanter als wenn die betroffenen UNRs zusätzlich zum WHERE hinzugefügt werden

									$qry.=" AND array_agg(unr) && ARRAY[".implode('::bigint,',$a_unr)."::bigint] ";
									// ==>  AND array_agg(unr) && ARRAY[123::bigint,345::bigint]
								}
								$qry.="ORDER BY datum, stunde, student_uid LIMIT 1;";

						if ($stud_result = $this->db_query($qry))
						{
							if ($this->db_num_rows($stud_result)>0)
							{
								$kollision++;
								$stud_row = $this->db_fetch_object($stud_result);
								foreach($a_unr as $kollision_unr)
									$kollisionsmeldungen[$kollision_unr][]=' Studentenkollision '.$stud_row->student_uid;
							}
						}

					}
					else
					{

						//Kollisionspruefung LVB Ebene
						// Ist es bei LVB-Ansicht wirklich eine Kollision?
						if ($kollision>0 && $this->type=='verband')
						{
							$kollision=0;
							$a=0;
							foreach ($a_unr as $unr)
							{
								$lvb_unr_arr[$a]=$unr;
								if(isset($a_lvb[$unr]))
									$lvb[$a++]=$a_lvb[$unr];
							}
							for ($a=0;$a<count($lvb)-1;$a++)
								for ($b=0;$b<count($lvb[$a]);$b++)
									for ($c=$a+1;$c<count($lvb);$c++)
										for ($d=0;$d<count($lvb[$c]);$d++)
										{
											$s1=mb_substr($lvb[$a][$b],0,1);
											$s2=mb_substr($lvb[$c][$d],0,1);
											$v1=trim(mb_substr($lvb[$a][$b],1,1));
											$v2=trim(mb_substr($lvb[$c][$d],1,1));
											$g1=trim(mb_substr($lvb[$a][$b],2,1));
											$g2=trim(mb_substr($lvb[$c][$d],2,1));
											if ($s1==$s2 || !$s1 || $s1=='' || $s1=='0' || !$s2 || $s2=='' || $s2=='0')
												if ($v1==$v2 || !$v1 || $v1=='' || $v1=='0' || !$v2 || $v2=='' || $v2=='0')
													if ($g1==$g2 || !$g1 || $g1=='' || $g1=='0' || !$g2 || $g2=='' || $g2=='0')
													{
														$kollision++;
														$kollisionsmeldungen[$lvb_unr_arr[$a]][]=' Gruppe '.trim($lvb[$a][$b]).' / '.trim($lvb[$c][$d]);
														$kollisionsmeldungen[$lvb_unr_arr[$c]][]=' Gruppe '.trim($lvb[$a][$b]).' / '.trim($lvb[$c][$d]);
													}
										}
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
						$farbe='';
						$tooltip_anmerkung=array();
						$paramList='';
						$z=0;
						$reservierung=false;
						$stundenplan_ids=array();
						$titel='';

						if (isset($raumcheck))
							unset($raumcheck);

						if (isset($lktcheck))
							unset($lktcheck);

						foreach ($this->std_plan[$i][$j] as $lehrstunde)
						{
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
								if ($lehrstunde->gruppe_kurzbz != '')
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
								{
									$paramList.='&amp;reservierung_id'.$z++.'='.$lehrstunde->stundenplan_id;
									$reservierung=true;
								}
								else
								{
									if (!in_array($lehrstunde->stundenplan_id, $stundenplan_ids))
									{
										$paramList.='&amp;stundenplan_id'.$z++.'='.$lehrstunde->stundenplan_id;
										$stundenplan_ids[]=$lehrstunde->stundenplan_id;
									}
								}
								if (isset($lehrstunde->farbe) && $farbe=='')
									$farbe=$lehrstunde->farbe;
								$titel.=htmlspecialchars($lehrstunde->titel);
								$anmerkung=htmlspecialchars($lehrstunde->anmerkung);
								$tooltip_anmerkung[]=$titel.' '.$anmerkung;
							}

							if (isset($raumcheck[$lehrstunde->ort]) && $raumcheck[$lehrstunde->ort]!=$lehrstunde->unr)
							{
								$kollision++;
								$kollisionsmeldungen[$lehrstunde->unr][]=" Ort ".$lehrstunde->ort;
								$kollisionsmeldungen[$raumcheck[$lehrstunde->ort]][]=" Ort ".$lehrstunde->ort;
							}
							else
								$raumcheck[$lehrstunde->ort]=$lehrstunde->unr;

							if (isset($lktcheck[$lehrstunde->lektor]) && $lktcheck[$lehrstunde->lektor]!=$lehrstunde->unr)
							{
								if (!in_array($lehrstunde->lektor_uid, unserialize(KOLLISIONSFREIE_USER)))
								{
									$kollision++;
									$kollisionsmeldungen[$lehrstunde->unr][]=" LektorIn ".$lehrstunde->lektor; //." ".$lehrstunde->unr."!=".$lktcheck[$lehrstunde->lektor];
									$kollisionsmeldungen[$lktcheck[$lehrstunde->lektor]][]=" LektorIn ".$lehrstunde->lektor; //." ".$lehrstunde->unr."!=".$lktcheck[$lehrstunde->lektor];
								}
							}
							else
								$lktcheck[$lehrstunde->lektor]=$lehrstunde->unr;
						}
						// Lektoren
						$lektor=array_unique($lektor);
						sort($lektor);
						$lkt='';
						foreach ($lektor as $l)
							$lkt.=$l.'<html:br />';

						// Lehrverband
						$lehrverband=array_unique($lehrverband);
						sort($lehrverband);
						$lvb='';
						foreach ($lehrverband as $l)
							$lvb.=$l.'<html:br />';

						// Lehrfach
						$lehrfach=array_unique($lehrfach);
						sort($lehrfach);
						$lf='';
						foreach ($lehrfach as $l)
							$lf.=$l.'<html:br />';

						// Ort
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
						if (isset($kollisionsmeldungen[$unr])
						|| (isset($kollisionsmeldung) && count($kollisionsmeldungen, COUNT_RECURSIVE)==0 && $kollision>0))
						{
							$blink_ein='<html:blink>';
							$blink_aus='</html:blink>';
						}
						else
						{
							$blink_ein='';
							$blink_aus='';
						}

						$stg_obj = new studiengang();
						$stg_obj->load($stg_kz);
						$tooltip_anmerkung = array_unique($tooltip_anmerkung);
						$tooltip_gesamt = '('.$updatevonam.') '.implode(',',$tooltip_anmerkung);

						if (isset($kollisionsmeldungen[$unr]))
							$tooltip_gesamt .= ' Kollision wegen:'.implode(',',array_unique($kollisionsmeldungen[$unr]));

						// Ausgabe
						echo '<button id="buttonSTPL'.$count.'"
							tooltiptext="'.$this->convert_html_chars($tooltip_gesamt).'"
							style="border:1px solid transparent;'.((isset($farbe) && $farbe!='')?'background-color:#'.$farbe:'').';-moz-appearance:none"
							styleOrig="border:1px solid transparent;'.((isset($farbe) && $farbe!='')?'background-color:#'.$farbe:'').';-moz-appearance:none" ';
						if ($berechtigung->isBerechtigt('lehre/lvplan',$stg_obj->oe_kurzbz,'uid'))
							echo ' context="stplPopupMenue" ';
						if ($berechtigung->isBerechtigt('lehre/lvplan',$stg_obj->oe_kurzbz,'u'))
							echo 'ondraggesture="nsDragAndDrop.startDrag(event,listObserver)" ';

						$button_orte = $this->ort_kurzbz;
						if ($button_orte=='')
							$button_orte=$ort[0];
						echo 'ondragdrop="nsDragAndDrop.drop(event,boardObserver)"
							ondragover="nsDragAndDrop.dragOver(event,boardObserver)"
							oncommand="TimeTableWeekClick(event)"
							ondblclick="TimeTableWeekDblClick(event)"
							aktion="stpl"
							unr="'.$unr.'"
							markiert="false"
							elem="stundenplan'.$i.$j.'"
							idList="'.$paramList.'" stpltype="'.$this->type.'"
							stg_kz="'.$this->stg_kz.'" sem="'.$this->sem.'" ver="'.$this->ver.'"
							grp="'.$this->grp.'" gruppe="'.$this->gruppe_kurzbz.'"
							datum="'.date("Y-m-d",$datum).'" stunde="'.$j.'" wochentag="'.$i.'"
							pers_uid="'.$this->pers_uid.'" ort_kurzbz="'.$button_orte.'">';

						echo '<label align="center">'.$blink_ein;
						$count++;

						echo mb_substr($lf, 0,-strlen('<html:br />'));
						if ($titel!='' && !$reservierung)
						{
							echo '<image src="../../skin/images/sticky.png" tooltip="'.$titel.'"/>';
						}

						// Zugeteilte Ressourcen Anzeigen
						$betriebsmittel = new betriebsmittel();
						if ($betriebsmittel->getBetriebsmittelStundenplan($stundenplan_ids))
						{
							if (count($betriebsmittel->result)>0)
							{
								$ressourceinfo='';
								foreach($betriebsmittel->result as $row)
									$ressourceinfo.=$row->beschreibung.' ';
								echo '<image src="../../skin/images/group.png" tooltip="'.$this->convert_html_chars($ressourceinfo).'"/>';
							}
						}

						echo '<html:br />';
						echo $lvb;
						if ($this->type!='lektor')
							echo $lkt;
						if ($this->type!='ort')
							echo $orte;

						if (LVPLAN_ANMERKUNG_ANZEIGEN)
							echo $anmerkung;

						echo $blink_aus;

						echo '</label>';
						echo '</button>';
					}
				}
				if (isset($this->std_plan[$i][$j][0]->frei_orte))
				{
					//orte sortieren => AnzahlKollisionen ASC, Ort_kurzbz ASC
					$keys=array();
					$values=array();
					foreach ($this->std_plan[$i][$j][0]->frei_orte as $key=>$value)
					{
						$keys[]=$key;
						$values[]=$value;
					}
					array_multisort($values, SORT_ASC, $keys, SORT_ASC, $this->std_plan[$i][$j][0]->frei_orte);

					foreach ($this->std_plan[$i][$j][0]->frei_orte as $f_ort=>$anzahl)
					{
						if ($anzahl<=$max_kollision)
						{
							echo '<label value="'.$f_ort.($anzahl>0?' ('.$anzahl.')':'').'"
								styleOrig=""
								ondragenter="nsDragAndDrop.dragEnter(event,boardObserver)"
								ondragexit="nsDragAndDrop.dragExit(event,boardObserver)"
								ondragdrop="nsDragAndDrop.drop(event,boardObserver)"
								datum="'.date("Y-m-d",$datum).'" stunde="'.$j.'"
								stg_kz="'.$this->stg_kz.'" sem="'.$this->sem.'" ver="'.$this->ver.'"
								grp="'.$this->grp.'" gruppe="'.$this->gruppe_kurzbz.'"
								stpltype="'.$this->type.'" ort_kurzbz="'.$f_ort.'" kollision="'.$anzahl.'"
								'.($anzahl>0?'tooltiptext="'.$anzahl.' Kollision(en)"':'').'
								/>';
						}
					}
				}

				if (defined('TEMPUS_TAGESINFO_FORMAT'))
					$tagesinfo = TEMPUS_TAGESINFO_FORMAT;
				else
					$tagesinfo = '%t %s';
				$tagesinfo = str_replace('%t',date("D",$datum),$tagesinfo);
				$tagesinfo = str_replace('%b',$stunden_arr[$j]['beginn'],$tagesinfo);
				$tagesinfo = str_replace('%e',$stunden_arr[$j]['ende'],$tagesinfo);
				$tagesinfo = str_replace('%s',$j,$tagesinfo);
				echo '<description class="stplweek_tagesinfo">'.$tagesinfo.'</description>';
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
				</vbox>'.$this->crlf;
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
		$stpl_view='lehre.'.VIEW_BEGIN.$db_stpl_table;
		$stpl_view_id=$db_stpl_table.TABLE_ID;
		//Kalenderdaten setzen
		$this->datum=montag($datum);
		$this->datum_begin=$this->datum;
		$this->datum_end=jump_week($this->datum_begin, 1);
		// Formatieren fuer Datenbankabfragen
		$this->datum_begin=date("Y-m-d",$this->datum_begin);
		$this->datum_end=date("Y-m-d",$this->datum_end);
		// Stundentafel abfragen
		$sql_query='SELECT min(stunde),max(stunde) FROM lehre.tbl_stunde';
		if (!$this->db_query($sql_query))
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

		if (!$this->db_query($sql_query))
			die($this->db_last_error());
		$num_rows_stpl=$this->db_num_rows();
		// Daten aufbereiten
		$leids='';
		for ($i=0;$i<$num_rows_stpl;$i++)
		{
			$direktinskription = false;
			$row=$this->db_fetch_object(null,$i);
			if ($row->gruppe_kurzbz!=null)
			{
				$gruppe[]=$row->gruppe_kurzbz;

				// Bei direkten Gruppen wird kein Lehrverband hinzugefuegt da diese nicht kollidieren
				$grp_obj = new gruppe();
				$grp_obj->load($row->gruppe_kurzbz);
				if($grp_obj->direktinskription)
					$direktinskription = true;
			}
			else
			{
				$gruppe[]='';
			}

			if (!$direktinskription)
			{
				if (!isset($lehrverband[$i]))
					$lehrverband[$i]= new stdClass();
				$lehrverband[$i]->stg_kz=$row->studiengang_kz;
				$lehrverband[$i]->sem=$row->semester;
				$lehrverband[$i]->ver=$row->verband;
				$lehrverband[$i]->grp=$row->gruppe;
			}
			$leids.="$row->lehreinheit_id,";
			$lektor[$i]=$row->uid;
			$unr=$row->unr;
		}
		if ($leids!='')
		{
			// Raumtypen
			$leids = mb_substr($leids, 0, mb_strlen($leids)-1);
			$qry = "SELECT raumtyp, raumtypalternativ FROM lehre.tbl_lehreinheit WHERE lehreinheit_id IN ($leids)";
			if ($this->db_query($qry)){
				while ($row = $this->db_fetch_object())
				{
					$raumtyp[]=$row->raumtyp;
					$raumtyp[]=$row->raumtypalternativ;
				}
			}
		}
		$raumtyp=array_unique($raumtyp);
		$rtype='';
		foreach ($raumtyp as $r)
			$rtype.=" OR raumtyp_kurzbz=".$this->db_add_param($r);
		$rtype=mb_substr($rtype,3);
		//Lektor
		$lektor=array_unique($lektor);
		$lkt='';
		foreach ($lektor as $l)
			$lkt.=" OR uid=".$this->db_add_param($l);
		$lkt=mb_substr($lkt,3);

		// Kollisionsfreie User ausnehmen
		$lkt="(($lkt) AND uid not in (".$this->db_implode4SQL(unserialize(KOLLISIONSFREIE_USER))."))";

		// Einheiten
		$gruppe=array_unique($gruppe);
		$gruppen='';
		foreach ($gruppe as $g)
			if ($g!='')
				$gruppen.=" OR gruppe_kurzbz=".$this->db_add_param($g);
		$lvb='';
		foreach ($lehrverband as $l)
		{
			$lvb.=" OR (studiengang_kz=".$this->db_add_param($l->stg_kz)." AND semester=".$this->db_add_param($l->sem);
			if ($l->ver!='' && $l->ver!=' ' && $l->ver!=null)
			{
				$lvb.=" AND (verband=".$this->db_add_param($l->ver)." OR verband IS NULL OR verband='')";
				if ($l->grp!='' && $l->grp!=' ' && $l->grp!=null)
					$lvb.=" AND (gruppe=".$this->db_add_param($l->grp)." OR gruppe IS NULL OR gruppe='')";
			}
			$lvb.=')';
		}
		$lvb=mb_substr($lvb,3);
		// Raeume die in Frage kommen, aufgrund der Raumtypen
		$sql_query="SELECT DISTINCT ort_kurzbz, hierarchie FROM public.tbl_ort
			JOIN public.tbl_ortraumtyp USING (ort_kurzbz) WHERE ($rtype) AND aktiv AND ort_kurzbz NOT LIKE '\\\\_%' ORDER BY hierarchie,ort_kurzbz";

		if (!$this->db_query($sql_query))
			die($this->db_last_error());
		while ($row = $this->db_fetch_object())
			$orte[]=$row->ort_kurzbz;

		// Raster vorbereiten
		for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
		{
			for ($s=$min_stunde;$s<=$max_stunde;$s++)
			{
				@$raster[$t][$s]->ort=array();
				$raster[$t][$s]->kollision=false;
			}
		}

		if($lvb!='')
			$lvbor = "OR ($lvb)";
		else
			$lvbor = '';
		// Stundenplanabfrage bauen (Wo ist Kollision?)
		$sql_query="SELECT DISTINCT datum, stunde FROM $stpl_view
			WHERE datum>=".$this->db_add_param($this->datum_begin)." AND datum<".$this->db_add_param($this->datum_end)." AND
			($lkt $gruppen $lvbor ) AND unr!=".$this->db_add_param($unr);

		$sql_query.=" AND NOT EXISTS(SELECT 1 FROM public.tbl_gruppe g WHERE g.gruppe_kurzbz=$stpl_view.gruppe_kurzbz AND direktinskription=true)";

		if (!$this->db_query($sql_query))
			die($this->db_last_error());
		while ($row = $this->db_fetch_object())
		{
			$mtag=mb_substr($row->datum, 8,2);
			$month=mb_substr($row->datum, 5,2);
			$jahr=mb_substr($row->datum, 0,4);
			$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
			$raster[$tag][$row->stunde]->kollision=true;
		}

		// Stundenplanabfrage bauen (Wo ist besetzt?)
		$sql_query="SELECT DISTINCT datum, stunde, ort_kurzbz FROM $stpl_view
			WHERE datum>=".$this->db_add_param($this->datum_begin)." AND datum<".$this->db_add_param($this->datum_end)." AND unr!=".$this->db_add_param($unr);

		// Reservierungen beruecksichtigen
		$sql_query.=" UNION SELECT DISTINCT datum, stunde, ort_kurzbz FROM campus.tbl_reservierung
			WHERE datum>=".$this->db_add_param($this->datum_begin)." AND datum<".$this->db_add_param($this->datum_end)." ";

		if (!$this->db_query($sql_query))
			die($this->db_last_error());

		while ($row = $this->db_fetch_object())
		{
			$mtag=mb_substr($row->datum, 8,2);
			$month=mb_substr($row->datum, 5,2);
			$jahr=mb_substr($row->datum, 0,4);
			$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
			$raster[$tag][$row->stunde]->ort[]=$row->ort_kurzbz;
		}

		// freie Plaetze in den Stundenplan eintragen.
		for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
		{
			for ($s=1;$s<=$max_stunde;$s++)
			{
				if (($s+$block)<=($max_stunde+1))
				{
					// Alle infrage kommenden Orte zuweisen
					foreach($orte as $ort)
					{
						$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]:0);
					}

					// Besetzte Raueme eintragen
					foreach($raster[$t][$s]->ort as $ort)
					{
						if (in_array($ort, $orte))
							$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
					}

					// Gruppenkollision eintragen
					if ($raster[$t][$s]->kollision)
					{
						foreach($this->std_plan[$t][$s][0]->frei_orte as $ort=>$value)
						{
							$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
						}
					}

					// Blockung beruecksichtigen
					for ($b=1;$b<$block && ($s+$block)<=($max_stunde+1);$b++)
					{
						if (!$raster[$t][$s+$b]->kollision)
						{
							//Wenn keine Gruppenkollision vorhanden ist, nur die Raumkollision eintragen
							foreach($raster[$t][$s+$b]->ort as $ort)
							{
								if (in_array($ort, $orte))
									$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
							}
						}
						else
						{
							// Bei Gruppenkollision kollidieren alle Raeume
							foreach($this->std_plan[$t][$s][0]->frei_orte as $ort=>$value)
							{
								$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
							}
						}
					}
				}
				else
				{
					// Wenn sich die Stunden mit der Blockung nicht ausgehen, dann keine Raeume anzeigen
					$this->std_plan[$t][$s][0]->frei_orte = array();
				}
			}
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
		if (!$this->db_query($sql_query))
			die($this->db_last_error());
		$row = $this->db_fetch_object();
		$min_stunde=$row->min;
		$max_stunde=$row->max;

		// LEs holen
		$sql_query='SELECT *, (planstunden-verplant::smallint) AS offenestunden FROM '.$lva_stpl_view.' WHERE';
		$lvas='';
		foreach ($lva_id as $id)
			$lvas.=" OR lehreinheit_id=".$this->db_add_param($id);
		$lvas=mb_substr($lvas,3);
		$sql_query.=$lvas;

		if (!$this->db_query($sql_query))
			die($this->db_last_error());
		$num_rows_lva=$this->db_num_rows();

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
			{
				$gruppe[$i]=$row->gruppe_kurzbz;

				// Direktinskriptionsgruppen von der Kollisionspruefung ausnehmen
				$grp_obj = new gruppe();
				$grp_obj->load($row->gruppe_kurzbz);
				if ($grp_obj->direktinskription)
					$direktinskription = true;
				else
					$direktinskription = false;
			}
			else
				$direktinskription = false;

			if (!$direktinskription)
			{
				// Wenn direktinskriptionsgruppe dann wird Studiengang und Semester nicht
				// gesetzt da dies sonst kollidiert
				// Bei normalen Spezialgruppen wir dieser trotzdem gesetzt damit Verbandsgruppen
				// mit Spezialgruppen des selben Studiengangs kollidieren
				if(!isset($lehrverband[$i]))
					$lehrverband[$i] = new stdClass();

				$lehrverband[$i]->stg_kz=$row->studiengang_kz;
				$lehrverband[$i]->sem=$row->semester;
				$lehrverband[$i]->ver=$row->verband;
				$lehrverband[$i]->grp=$row->gruppe;
			}
			$lektor[$i]=$row->lektor_uid;
			$verplant[$i]=$row->verplant;
			$planstunden[$i]=$row->planstunden;
			$offenestunden[]=$row->offenestunden;
			$unr=$row->unr;
			$block[$i]=$row->stundenblockung;
			$wochenrythmus[$i]=$row->wochenrythmus;
			$semesterstunden[$i]=(integer)$row->semesterstunden;
		}

		// Blockung eindeutig?
		$blck=$block[0];
		$block=array_unique($block);
		if (count($block)==1)
			$block=$blck;
		else
		{
			$this->errormsg.='Blockung ist nicht eindeutig!';
			return false;
		}

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
			$rtype.=" OR raumtyp_kurzbz=".$this->db_add_param($r);
		$raumtypalt=array_unique($raumtypalt);
		foreach ($raumtypalt as $r)
			$rtype.=" OR raumtyp_kurzbz=".$this->db_add_param($r);
		$rtype=mb_substr($rtype,3);
		//Lektor
		$lektor=array_unique($lektor);
		$lkt='';
		foreach ($lektor as $l)
			$lkt.=" OR mitarbeiter_uid=".$this->db_add_param($l);
		$lkt=mb_substr($lkt,3);
		//Dummy Lektor kollidiert nicht
		$lkt='(('.$lkt.') AND mitarbeiter_uid not in ('.$this->db_implode4SQL(unserialize(KOLLISIONSFREIE_USER)).'))';

		// Gruppen
		$gruppen='';
		if (isset($gruppe))
		{
			$gruppe=array_unique($gruppe);
			foreach ($gruppe as $g)
			{
				// Gruppen fuer direkte inskription kollidieren nicht
				/*$grp_obj = new gruppe();
				$grp_obj->load($g);
				if($grp_obj->direktinskription==false)*/
					$gruppen.=" OR gruppe_kurzbz=".$this->db_add_param($g);
			}
		}

		//Lehrverband
		$lvb='';
		foreach ($lehrverband as $l)
		{
			$lvb.=" OR (studiengang_kz=".$this->db_add_param($l->stg_kz)." AND semester=".$this->db_add_param($l->sem);
			if ($l->ver!='' && $l->ver!=' ' && $l->ver!=null)
			{
				$lvb.=" AND (verband=".$this->db_add_param($l->ver)." OR verband IS NULL OR verband='' OR verband=' ')";
				if ($l->grp!='' && $l->grp!=' ' && $l->grp!=null)
					$lvb.=" AND (gruppe=".$this->db_add_param($l->grp)." OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
			}

			$lvb.=')';
		}
		$lvb=mb_substr($lvb,3);

		// Raeume die in Frage kommen aufgrund der Raumtypen
		$sql_query="SELECT DISTINCT ort_kurzbz, hierarchie FROM public.tbl_ort
			JOIN public.tbl_ortraumtyp USING (ort_kurzbz) WHERE ($rtype) AND aktiv AND ort_kurzbz NOT LIKE '\\\\_%' ORDER BY hierarchie,ort_kurzbz"; //

		if (!$this->db_query($sql_query))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		$num_orte=$this->db_num_rows();
		$orte = array();
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
				@$raster[$t][$s]->ort=array();
				$raster[$t][$s]->kollision=false;
			}
		do
		{
			// Raster vorbereiten
			for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
			{
				for ($s=$min_stunde;$s<=$max_stunde;$s++)
				{
					if (isset($raster[$t][$s]))
						unset($raster[$t][$s]);
					@$raster[$t][$s]->ort=array();
					$raster[$t][$s]->kollision=false;
				}
			}

			if($lvb!='')
				$orlvb = "OR ($lvb)";
			else
				$orlvb = '';
			// Stundenplanabfrage bauen (Wo ist Kollision?)
			$sql_query="SELECT DISTINCT datum, stunde FROM $stpl_table
				WHERE datum>=".$this->db_add_param($datum_begin)." AND datum<".$this->db_add_param($datum_end)." AND
				($lkt $gruppen $orlvb )";
			if (is_numeric($unr))
				$sql_query.=" AND unr!=".$this->db_add_param($unr);
			$sql_query.=" AND NOT EXISTS(SELECT 1 FROM public.tbl_gruppe WHERE gruppe_kurzbz=".$stpl_table.".gruppe_kurzbz and direktinskription=true)";

			if (!$this->db_query($sql_query))
			{
				$this->errormsg = $this->db_last_error().$sql_query;
				return false;
			}

			// Kollisionen ins Raster eintragen
			while ($row = $this->db_fetch_object())
			{
				$mtag=mb_substr($row->datum, 8,2);
				$month=mb_substr($row->datum, 5,2);
				$jahr=mb_substr($row->datum, 0,4);
				$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
				if (!isset($raster[$tag][$row->stunde]))
					$raster[$tag][$row->stunde]=new stdClass();
				$raster[$tag][$row->stunde]->kollision=true;
			}

			// Stundenplanabfrage bauen (Wo ist besetzt?)
			$sql_query="SELECT DISTINCT datum, stunde, ort_kurzbz FROM $stpl_view
				JOIN public.tbl_ortraumtyp USING (ort_kurzbz)
				WHERE datum>=".$this->db_add_param($datum_begin)." AND datum<".$this->db_add_param($datum_end)." AND
				($rtype)";
			if (is_numeric($unr))
				$sql_query.=" AND unr!=".$this->db_add_param($unr);

			// Reservierungen beruecksichtigen
			$sql_query.=" UNION SELECT distinct datum, stunde, ort_kurzbz FROM campus.tbl_reservierung
						WHERE datum>=".$this->db_add_param($datum_begin)." AND datum<".$this->db_add_param($datum_end);

			if (!$this->db_query($sql_query))
			{
				$this->errormsg = $this->db_last_error().$sql_query;
				return false;
			}

			while ($row = $this->db_fetch_object())
			{
				$mtag=mb_substr($row->datum, 8,2);
				$month=mb_substr($row->datum, 5,2);
				$jahr=mb_substr($row->datum, 0,4);
				$tag=date("w",mktime(12,0,0,$month,$mtag,$jahr));
				$raster[$tag][$row->stunde]->ort[]=$row->ort_kurzbz;
			}

			// Moegliche Orte fuer den Vorschlag in den Stundenplan eintragen.
			// $this->std_plan[$t][$s][0]->frei_orte ist ein Array mit den in Frage kommenden Orten
			// der Wert von $this->std_plan[$t][$s][0]->frei_orte[$ort_kurzbz] gibt an, wie viele
			// kollisionen bei der Zuteilung entstehen
			for ($t=1;$t<=TAGE_PRO_WOCHE;$t++)
			{
				for ($s=1;$s<=$max_stunde;$s++)
				{
					//Blockung passt in die Maximalstundenanzahl
					if (($s+$blck)<=($max_stunde+1))
					{
						if ($count==0)
						{
							// Freie Orte beim 1. Durchlauf zuteilen
							foreach($orte as $ort)
								$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]:0);
						}

						// Besetzte Orte eintragen
						foreach ($raster[$t][$s]->ort as $ort)
						{
							if (in_array($ort, $orte))
								$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
						}

						//Kollision mit Gruppe
						if ($raster[$t][$s]->kollision)
						{
							if (isset($this->std_plan[$t][$s][0]->frei_orte))
							{
								foreach ($this->std_plan[$t][$s][0]->frei_orte as $ort=>$value)
								{
									if (in_array($ort, $orte))
										$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
								}
							}
						}

						// Blockung beruecksichtigen
						for ($b=1;$b<$block && ($s+$block)<=($max_stunde+1);$b++)
						{
							if (!$raster[$t][$s+$b]->kollision)
							{
								// Wenn keine Gruppenkollision vorhanden ist, dann die kollidierenden Raeume eintragen
								foreach ($raster[$t][$s+$b]->ort as $ort)
								{
									if (in_array($ort, $orte))
										$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
								}
							}
							else
							{
								if (isset($this->std_plan[$t][$s][0]->frei_orte))
								{
									// Bei Gruppenkollision den Wert bei allen Raumen erhoehen
									foreach ($this->std_plan[$t][$s][0]->frei_orte as $ort=>$value)
										$this->std_plan[$t][$s][0]->frei_orte[$ort]=(isset($this->std_plan[$t][$s][0]->frei_orte[$ort])?$this->std_plan[$t][$s][0]->frei_orte[$ort]+1:1);
								}
							}
						}
					}
					else
					{
						// Wenn sich die Verplanung mit der Blockung nicht mehr ausgeht, dann keine Raeume vorschlagen
						@$this->std_plan[$t][$s][0]->frei_orte=array();
					}
				}
			}
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
		} while ($type=='lva_multi_search' && $rest>0);
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
		$return = array();
		if (!date("w",$this->datum))
			$this->datum=jump_day($this->datum,1);
		$num_rows_stunde=$this->db_num_rows($this->stunde);
		for ($i=1; $i<=TAGE_PRO_WOCHE; $i++)
		{
			$blocked=array();
			$gruppiert=array();

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
					if (isset($lektor_uids))
						unset($lektor_uids);
					if (isset($stunden_arr))
						unset($stunden_arr);
					foreach ($this->std_plan[$i][$j] as $lehrstunde)
					{

						$unr[]=$lehrstunde->unr;
						// Lektoren
						$lektor[]=$lehrstunde->lektor;
						$lektor_uids[]=$lehrstunde->lektor_uid;
						// Lehrverband
						$lvb=$lehrstunde->stg.'-'.$lehrstunde->sem;
						$stunden_arr[]=$j;
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
					if (!isset($kollision))
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

					// Lehrverband
					if ($this->type!='verband')
					{
						$lvb='';
						foreach ($lehrverband as $l)
							$lvb.=$l.' ';
					}
					else
						$lvb=$lehrverband[0];

					$row = $this->db_fetch_object($this->stunde, $k);
					$start_time=$row->beginn;

					for ($idx=0;$idx<count($this->std_plan[$i][$j]);$idx++)
					{
						if (!isset($this->std_plan[$i][$j][$idx]))
						{
							continue;
						}

						/**
						 * Wenn Lektoren in mehreren Raeumen gleichzeitig unterrichten
						 * Oder mehrere Lektoren /Gruppen im selben Raum sind werden diese
						 * zu einem Eintrag zusammengruppiert.
						 *
						 * Zusammengruppiert werden nur Eintraege die am gleichen Tag
						 * in der gleichen Stunde stattfinden.
						 *
						 * Es wird nur der erste Eintrag ausgegeben. Die restlichen werden uebersprungen da
						 * die Lektoren, Gruppen und Raeume bereits zum Ersten Eintrag hinzugefuegt wurden.
						 */
						if (isset($gruppiert[$this->std_plan[$i][$j][$idx]->unr]) && $gruppiert[$this->std_plan[$i][$j][$idx]->unr]>0)
						{
							$gruppiert[$this->std_plan[$i][$j][$idx]->unr]--;
							continue;
						}

						/**
						 * Unterricht der ueber mehrere Stunden geht wird nicht einzeln Exportiert,
						 * sondern zusammengeblockt.
						 *
						 * Es wird nur ein Eintrag geschrieben, die restlichen werden uebersprungen.
						 * Vor dem Ueberspringen des Eintrages werden jedoch noch die dazu Gruppierten Eintraege
						 * ermittelt und dann ebenfalls uebersprungen
						 */
						$blockcontinue=false;
						if (isset($blocked[$this->std_plan[$i][$j][$idx]->unr]) && $blocked[$this->std_plan[$i][$j][$idx]->unr]>0)
						{
							$blocked[$this->std_plan[$i][$j][$idx]->unr]--;
							$blockcontinue=true;
						}

						if (!$blockcontinue)
						{
							// Blockungen ueber mehrere Stunden erkennen

							$blockflag=false;
							for ($blockstunden=1;$blockstunden<=$num_rows_stunde;$blockstunden++)
							{
								if (isset($this->std_plan[$i][$j+$blockstunden][$idx]) && isset($this->std_plan[$i][$j+$blockstunden][$idx]->stundenplan_id)
									&& ($this->std_plan[$i][$j][$idx]->unr == $this->std_plan[$i][$j+$blockstunden][$idx]->unr)
									&& $this->std_plan[$i][$j][$idx]!='0' && $k<($num_rows_stunde-$blockstunden)
									&& !($this->std_plan[$i][$j][$idx]->reservierung && $this->std_plan[$i][$j][$idx]->lektor!=$this->std_plan[$i][$j+$blockstunden][$idx]->lektor))
								{

									if (isset($blocked[$this->std_plan[$i][$j][$idx]->unr]))
										$blocked[$this->std_plan[$i][$j][$idx]->unr]++;
									else
										$blocked[$this->std_plan[$i][$j][$idx]->unr]=1;
									$row = $this->db_fetch_object($this->stunde, ($k+$blockstunden));
									$stunden_arr[]=$row->stunde;
									$end_time=$row->ende;
									$blockflag=true;
								}
								else
								{
									if (!$blockflag)
									{
										$row = $this->db_fetch_object($this->stunde, $k);
										$stunden_arr[]=$row->stunde;
										$end_time=$row->ende;
										break;
									}
									else
									{
										break;
									}
								}
							}
						}

						//Wenn im selben Raum mehrere Lektoren sind bzw mehrere Gruppen
						//dann werden diese zusammengruppiert und als ein Eintrag angezeigt
						for ($idx1=0;$idx1<count($this->std_plan[$i][$j]);$idx1++)
						{
							if ($idx!=$idx1)
							{
								if ($this->kannGruppieren($i,$j,$idx,$idx1))
								{
									if (isset($gruppiert[$this->std_plan[$i][$j][$idx]->unr]))
										$gruppiert[$this->std_plan[$i][$j][$idx]->unr]++;
									else
										$gruppiert[$this->std_plan[$i][$j][$idx]->unr]=1;

									//Bezeichnungen zusammenfuehren

									//Lektoren
									if (!mb_strstr($this->std_plan[$i][$j][$idx1]->lektor,$this->std_plan[$i][$j][$idx]->lektor))
									{
										$this->std_plan[$i][$j][$idx]->lektor.=' / '.$this->std_plan[$i][$j][$idx1]->lektor;
									}

									//Ort
									if (!mb_strstr($this->std_plan[$i][$j][$idx1]->ort,$this->std_plan[$i][$j][$idx]->ort))
									{
										$this->std_plan[$i][$j][$idx]->ort.=' / '.$this->std_plan[$i][$j][$idx1]->ort;
									}

									//Gruppen
									if (isset($lehrverband[$idx]) && isset($lehrverband[$idx1]))
									{
										if (!mb_strstr($lehrverband[$idx], $lehrverband[$idx1]))
											$lehrverband[$idx].=' / '.$lehrverband[$idx1];
									}
								}
							}
						}

						//Geblockte Eintraege werden uebersprungen nachdem die Gruppierung ermittelt wurde
						if ($blockcontinue)
						{
							continue;
						}

						$start_date=date("d.m.Y",$this->datum);
						$end_date=$start_date;
						if (isset($lehrverband[$idx]))
							$lvb = $lehrverband[$idx];
						if ($target=='outlook')
						{
							//"Betreff","Beginnt am","Beginnt um","Endet am","Endet um","Ganztaegiges Ereignis","Erinnerung Ein/Aus","Erinnerung am","Erinnerung um","Besprechungsplanung","Erforderliche Teilnehmer","Optionale Teilnehmer","Besprechungsressourcen","Abrechnungsinformationen","Beschreibung",
							//"Kategorien","Ort","Prioritaet","Privat","Reisekilometer","Vertraulichkeit","Zeitspanne zeigen als"
							echo $this->crlf.'"'.$this->std_plan[$i][$j][$idx]->lehrfach.(isset($this->std_plan[$i][$j][$idx]->lehrform) && $this->std_plan[$i][$j][$idx]->lehrform!=''?'-'.$this->std_plan[$i][$j][$idx]->lehrform:'').($lvb!=''?' - '.$lvb:'').'","'.$start_date.'","'.$start_time.'","'.$end_date.'","'.$end_time.'","Aus","Aus",,,,,,,,"Stundenplan';
							echo $this->crlf.$this->std_plan[$i][$j][$idx]->lehrfach.$this->crlf.$this->std_plan[$i][$j][$idx]->lektor.$this->crlf.$lvb.$this->crlf.$this->std_plan[$i][$j][$idx]->ort.(LVPLAN_ANMERKUNG_ANZEIGEN?$this->crlf.$this->std_plan[$i][$j][$idx]->anmerkung:'').'","StundenplanFH","'.$this->std_plan[$i][$j][$idx]->ort.'","Normal","Aus",,"Normal","2"';
						}
						elseif ($target=='ical')
						{
							$sda = explode(".",$start_date);  //sda start date array
							$sta = explode(":",$start_time);	 //sta start time array
							$eda = explode(".",$end_date);    //eda end date array
							$eta = explode(":",$end_time);	 //eta end time array

							//Die Zeitzone muss angegeben werden, da sonst der Google Kalender die Endzeiten nicht richtig erkennt
							// diese wird in stpl_kalender global definiert und bei den Start und Ende Zeiten mitangegeben
							$start_date_time_ical = $sda[2].$sda[1].$sda[0].'T'.sprintf('%02s',($sta[0])).$sta[1].$sta[2];  //neu gruppieren der Startzeit und des Startdatums
							$end_date_time_ical = $eda[2].$eda[1].$eda[0].'T'.sprintf('%02s',($eta[0])).$eta[1].$eta[2];  //neu gruppieren der Startzeit und des Startdatums

							echo $this->crlf.'BEGIN:VEVENT'.$this->crlf
								.'UID:'.'FH'.str_replace(',',' ',$lvb.$this->std_plan[$i][$j][$idx]->ort.$this->std_plan[$i][$j][$idx]->lektor.$lehrfach[$idx].$start_date_time_ical.$end_date_time_ical.$this->crlf)
								.'SUMMARY:'.str_replace(',',' ',$lehrfach[$idx].'  '.$this->std_plan[$i][$j][$idx]->ort.' - '.$lvb.$this->crlf)
								.'DESCRIPTION:'.str_replace(',',' ',$lehrfach[$idx].'\n'.$this->std_plan[$i][$j][$idx]->lektor.'\n'.$lvb.'\n'.$this->std_plan[$i][$j][$idx]->ort.(LVPLAN_ANMERKUNG_ANZEIGEN?'\n'.$this->std_plan[$i][$j][$idx]->anmerkung:'').$this->crlf)
								.'LOCATION:'.$this->std_plan[$i][$j][$idx]->ort.$this->crlf
								.'CATEGORIES:'.$lvplan_kategorie.$this->crlf
								.'DTSTART;TZID=Europe/Vienna:'.$start_date_time_ical.$this->crlf
								.'DTEND;TZID=Europe/Vienna:'.$end_date_time_ical.$this->crlf
								.'END:VEVENT';
						}
						elseif ($target=='freebusy')
						{
							$sda = explode(".",$start_date);  //sda start date array
							$sta = explode(":",$start_time);	 //sta start time array
							$eda = explode(".",$end_date);    //eda end date array
							$eta = explode(":",$end_time);	 //eta end time array

							$start_date_time_ical = $sda[2].$sda[1].$sda[0].'T'.sprintf('%02s',($sta[0])).$sta[1].$sta[2];  //neu gruppieren der Startzeit und des Startdatums
							$end_date_time_ical = $eda[2].$eda[1].$eda[0].'T'.sprintf('%02s',($eta[0])).$eta[1].$eta[2];  //neu gruppieren der Startzeit und des Startdatums

							// Zeit in UTC umwandeln
							$date = new DateTime($start_date_time_ical, new DateTimeZone('Europe/Vienna'));
							$date->setTimezone(new DateTimeZone('UTC'));
							$start_date_time_ical = $date->format('Ymd\THis').'Z';

							$date = new DateTime($end_date_time_ical, new DateTimeZone('Europe/Vienna'));
							$date->setTimezone(new DateTimeZone('UTC'));
							$end_date_time_ical = $date->format('Ymd\THis').'Z';

							echo $this->crlf,'FREEBUSY: ',$start_date_time_ical,'/',$end_date_time_ical;
						}
						elseif ($target=='return')
						{
							$sda = explode(".",$start_date);  //sda start date array
							$sta = explode(":",$start_time);	 //sta start time array
							$eda = explode(".",$end_date);    //eda end date array
							$eta = explode(":",$end_time);	 //eta end time array

							//Die Zeitzone muss angegeben werden, da sonst der Google Kalender die Endzeiten nicht richtig erkennt
							// diese wird in stpl_kalender global definiert und bei den Start und Ende Zeiten mitangegeben
							$start_date_time_ical = $sda[2].$sda[1].$sda[0].'T'.sprintf('%02s',($sta[0])).$sta[1].$sta[2];  //neu gruppieren der Startzeit und des Startdatums
							$end_date_time_ical = $eda[2].$eda[1].$eda[0].'T'.sprintf('%02s',($eta[0])).$eta[1].$eta[2];  //neu gruppieren der Startzeit und des Startdatums

							$UID = 'FH'.$lvb.$this->std_plan[$i][$j][$idx]->ort.$this->std_plan[$i][$j][$idx]->lektor.$lehrfach[$idx].$start_date_time_ical.$end_date_time_ical;
							$Summary = $lehrfach[$idx].'  '.$this->std_plan[$i][$j][$idx]->ort.' - '.$lvb;
							$description = $lehrfach[$idx].'\n'.$this->std_plan[$i][$j][$idx]->lektor.'\n'.$lvb.'\n'.$this->std_plan[$i][$j][$idx]->ort.(LVPLAN_ANMERKUNG_ANZEIGEN?'\n'.$this->std_plan[$i][$j][$idx]->anmerkung:'');

							$UID = str_replace(',',' ',$UID);
							$Summary = str_replace(',',' ',$Summary);
							$description = str_replace(',',' ',$description);

							$return[]=array('UID'=>$UID,
							'lehrfach_id'=>(isset($this->std_plan[$i][$j][$idx]->lehrfach_id)?$this->std_plan[$i][$j][$idx]->lehrfach_id:''),
							'ort'=>$this->std_plan[$i][$j][$idx]->ort,
							'lektor_uid'=>array_unique($lektor_uids),
							'gruppen'=>array_unique($lehrverband),
							'stunden'=>array_unique($stunden_arr),
							'titel'=>$this->std_plan[$i][$j][$idx]->titel,
							'unr'=>$unr,
							'Summary'=>$Summary,
							'Description'=>$description,
							'start_date'=>$start_date,
							'end_date'=>$end_date,
							'start_time'=>$start_time,
							'end_time'=>$end_time,
							'dtstart'=>$start_date_time_ical,
							'dtend'=>$end_date_time_ical,
							'reservierung'=>$this->std_plan[$i][$j][$idx]->reservierung,
							'reservierung_id'=>($this->std_plan[$i][$j][$idx]->reservierung?$this->std_plan[$i][$j][$idx]->stundenplan_id:''),
							'updateamum'=>$this->std_plan[$i][$j][$idx]->updateamum,
							'data'=>'BEGIN:VEVENT'.$this->crlf
								.'UID:'.$UID.$this->crlf
								.'SUMMARY:'.$Summary.$this->crlf
								.'DESCRIPTION:'.$description.$this->crlf
								.'LOCATION:'.$this->std_plan[$i][$j][$idx]->ort.$this->crlf
								.'CATEGORIES:'.$lvplan_kategorie.$this->crlf
								.'DTSTART;TZID=Europe/Vienna:'.$start_date_time_ical.$this->crlf
								.'DTEND;TZID=Europe/Vienna:'.$end_date_time_ical.$this->crlf
								.'END:VEVENT');
						}
						else
						{
							echo $this->crlf.'"'.$lehrfach[$idx].'","'.$lvplan_kategorie.'","'.$this->std_plan[$i][$j][$idx]->ort.'","Stundenplan'.$this->crlf.$this->std_plan[$i][$j][$idx]->lehrfach.$this->crlf;
							echo $this->std_plan[$i][$j][$idx]->lektor.$this->crlf.$lvb.$this->crlf.$this->std_plan[$i][$j][$idx]->ort.(LVPLAN_ANMERKUNG_ANZEIGEN?$this->crlf.$this->std_plan[$i][$j][$idx]->anmerkung:'').'","Stundenplan",';
							echo '"'.$start_date.'","'.$start_time.'","'.$end_date.'","'.$end_time.'",,,,,';
						}
					}
				}
			}
			$this->datum=jump_day($this->datum, 1);
		}
		if ($target=='return')
			return $return;
		else
			return true;
	}

	/**
	 * Prueft, ob Eintraege fuer den Export zusammengruppiert werden koennen zu einer Stunde
	 * Dies ist der Fall, wenn mehrere Lektoren in einem Raum unterrichten, ein Lektor mehrere
	 * Raeume parallel beaufsichtigt oder mehrere Gruppen in einem Raum sind
	 *
	 * @param $tag Tag des Unterrichts
	 * @param $stunde Stunde des Unterrichts
	 * @param $idx Index des ersten Eintrages
	 * @param $idx1 Index des Eintrages der mit dem ersten verglichen werden soll
	 */
	protected function kannGruppieren($tag, $stunde, $idx, $idx1)
	{
		if (isset($this->std_plan[$tag][$stunde][$idx]) &&
			isset($this->std_plan[$tag][$stunde][$idx1]))
		{
			$unr1 = $this->std_plan[$tag][$stunde][$idx]->unr;
			$unr2 = $this->std_plan[$tag][$stunde][$idx1]->unr;
			$ort1 = $this->std_plan[$tag][$stunde][$idx]->ort;
			$ort2 = $this->std_plan[$tag][$stunde][$idx1]->ort;
			$lektor1 = $this->std_plan[$tag][$stunde][$idx]->lektor;
			$lektor2 = $this->std_plan[$tag][$stunde][$idx1]->lektor;

			if ($unr1==$unr2 && ($ort1==$ort2 || $lektor1==$lektor2)
				&& !$this->std_plan[$tag][$stunde][$idx]->reservierung && !$this->std_plan[$tag][$stunde][$idx1]->reservierung)
				return true;
			else
				return false;
		}
		else
			return false;
	}

	protected function searchForId($id, $array)
	{
		foreach ($array as $key => $val)
		{
			if ($val['unr'] == $id)
			{
				return $key;
			}
		}
		return false;
	}

	/**
	 * Gibt einen HTML-Dialog mit den Kalenderwochen aus
	 * @param $link
	 */
	protected function jahreskalenderjump_hoverbox($link)
	{
		$sprache = getSprache();
		$p=new phrasen($sprache);
		$crlf=crlf();
		$datum=time();
		$woche=kalenderwoche($datum);
		$wochenmontag=montag($datum);

		echo '<table align="center"><tr valign="top"><td>
		<div class="hoverbox">
			<div class="preview">
				<img src="../../../skin/images/down_lvplan.png" border="0"/>
				<div class="hoverbox_inhalt">
					<table class="hoverbox"><tr>'.$crlf;
		for ($anz=1;$anz<25;$anz++)
		{
			$linknew=$link.'&datum='.$datum;
			if ($woche==53)
				$woche=1;
			echo '			<td style="padding: 3px;" align="center"><A HREF="'.$linknew.'"><nobr>KW '.$woche.'</nobr><br>'.date('d.m', $wochenmontag).'</A></td>'.$crlf;
			if ($anz%8==0)
				echo '			</tr><tr align="center">'.$crlf;
			$datum+=60*60*24*7;
			$woche++;
			$wochenmontag+=60*60*24*7;
		}
		echo '			</tr></table></div></div></div></td></tr></table>'.$crlf;
	}

	/**
	 * Gibt Link zum Löschen der Reservierungen für einen Ort zu einer Zeit zuürck.
	 * @param $ort
	 * @param $datum
	 * @param $stunde
	 * @param $user_uid eingeloggter User
	 * @param $reservberechtigt erweiterte Reservierungsberechtigung
	 */
	private function stpltable_deletelink($ort, $datum, $stunde, $user_uid, $reservberechtigt)
	{
		global $p;
		$deleteberechtigt = false;
		$reservtodelete = array();

		$qry = "SELECT reservierung_id, insertvon, uid FROM campus.tbl_reservierung
									WHERE ort_kurzbz=" . $this->db_add_param($ort) . "
									AND datum = " . $this->db_add_param(date('Y-m-d', $datum)) . "
									AND stunde = " . $this->db_add_param($stunde);

		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				if ($reservberechtigt && ($row->uid == $user_uid || $row->insertvon == $user_uid))
				{
					$deleteberechtigt = true;
					$reservtodelete[] = $row->reservierung_id;
				}
			}
		}

		if ($deleteberechtigt)
		{
			$reservetodeleteurl = 'stpl_week.php?type=ort&ort_kurzbz='.$ort.'&datum='.$datum;
			$reservetodeleteurl .= '&reservtodelete[]='.implode("&reservtodelete[]=", $reservtodelete);
			echo '<div class="reservdelete">
											<a href="'.$reservetodeleteurl.'"  title="' . $p->t('lvplan/reservierungenLoeschen') . '">
												<img src="../../../skin/images/cross.png" width="12px" height="12px"/>
											</a>
										</div>';
		}
	}
}
?>
