<?php
/* Copyright (C) 2006 FHTechnikum-Wien
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
 */

/**
 * @class 	lehrstunde
 * @brief  	Beschreibung einer Unterrichts-Stunde der Tabelle tbl_stundenplan
 * @author	Christian Paminger, Andreas Österreicher
 * @date	2004/8/21
 * @version	$Revision: 1.2 $
 * Update: 	21.10.2009 von Christian Paminger
 * @bug		Ein Fehler
 * @todo	Noch zu tun
 * @warning	Eine Warnung
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/studiensemester.class.php');
require_once(dirname(__FILE__).'/variable.class.php');

class lehrstunde extends basis_db
{
	public $result = array();

	public $stundenplan_id;	/// @brief ID in der Datenbank
	public $lehreinheit_id;	/// @brief id der Lehreinheit in der DB
	public $unr;			// @brief Unterrichtsnummer
	public $lektor_uid;		// @brief UID des Lektors
	public $lektor_kurzbz; 	// @brief Kurzbezeichnung des Lektors
	public $mitarbeiter_kurzbz; 	    // @brief Kurzbezeichnung
	public $datum;			// @brief Datum
	public $stunde;			// @brief Unterrichts-Stunde des Tages
	public $ort_kurzbz;		// @brief Ort in dem der Unterricht stattfindet
	public $lehrfach_id;	// @brief Nummer des Lehrfachs
	public $lehrfach;		// @brief Name des Lehrfachs
	public $lehrfach_bez;	// @brief Voller Name des Lehrfachs
	public $lehrform;		// @brief Lehrform des Lehrfachs (Vorlesung, ...)
	public $studiengang_kz;	// @brief Kennzahl des Studiengangs
	public $studiengang;	// @brief Kurzbezeichnung des Studiengangs
	public $sem;			// @brief Semester
	public $ver;			// @brief Verband
	public $grp;			// @brief Gruppe
	public $gruppe_kurzbz;	// @brief Kurzbezeichnung der Gruppe
	public $titel;			// @brief Titel der Unterrichtsstunde
	public $anmerkung;		// @brief Anmerkungen zur Unterrichtsstunde
	public $fix;			// @brief true wenn diese Stunde nicht mehr verschoben wird
	public $updateamum;		// @brief letztes Update
	public $updatevon;		// @brief Update von wem?
	public $new;			// @brief true wenns ein neuer Datensatz ist
	public $reservierung;	// @brief true wenns eine Reservierung ist

	public $lehrstunden=array(); // @brief Objekt der eigenen Klasse
	public $anzahl;			// @brief Gesamte Anzahl der Stunden im Array
	public $ss=null;		// @brief Studiensemester
	public $lastqry=null;


	/*
	 * Konstruktor
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$this->new=TRUE;
	}

	/**
	 *  @brief Einen Datensatz aus optional angegebener Stundenplan-Tabelle laden
	 *  @param stundenplan_id ID in der Datenbank
	 *  @param stpl_table Name der Tabelle in der DB
	 *  @return Boolean, Fehlermeldung kommt in das Attribut errormsg
	 */
	public function load($stundenplan_id,$stpl_table='stundenplandev')
	{
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_view='lehre.'.VIEW_BEGIN.$stpl_table;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		$sql_query="SELECT * FROM $stpl_view WHERE $stpl_id=$stundenplan_id;";

		//Datenbankabfrage
		if (!$this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return false;
		}

		$this->anzahl = $this->db_num_rows();

		//Daten uebernehmen
		if ($this->anzahl!=1)
		{
			$this->errormsg='Keinen Datensatz gefunden';
			return false;
		}
		else
		{
			$row=$this->db_fetch_object();
			$this->stundenplan_id=$row->{$stpl_id};
			$this->unr=$row->unr;
			$this->lektor_uid=$row->uid;
			$this->lektor_kurzbz=$row->lektor;
			$this->mitarbeiter_kurzbz=$row->mitarbeiter_kurzbz;
			$this->datum=$row->datum;
			$this->stunde=$row->stunde;
			$this->ort_kurzbz=$row->ort_kurzbz;
			$this->lehrfach=$row->lehrfach;
			$this->lehrfach_bez=$row->lehrfach_bez;
			$this->lehrfach_id=$row->lehrfach_id;
			$this->lehrform=$row->lehrform;
			$this->studiengang_kz=$row->studiengang_kz;
			$this->studiengang=$row->stg_kurzbz;
			$this->sem=$row->semester;
			$this->ver=$row->verband;
			$this->grp=$row->gruppe;
			$this->gruppe_kurzbz=$row->gruppe_kurzbz;
			$this->titel=$row->titel;
			$this->anmerkung=$row->anmerkung;
			$this->updateamum=$row->updateamum;
			$this->updatevon=$row->updatevon;
			$this->new=false;
		}
		return true;
	}

	/**
	 *	Datensatz in DB speichern
	 *
	 */
	public function save($uid, $stpl_table='stundenplandev')
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;
		if ($this->new)
		{
			// insert
		}
		else
		{
			// update
			$sql_query='UPDATE '.$stpl_table;
			$sql_query.=" SET datum=".$this->db_add_param($this->datum).", stunde=".$this->db_add_param($this->stunde);
			$sql_query.=", ort_kurzbz=".$this->db_add_param($this->ort_kurzbz).", mitarbeiter_uid=".$this->db_add_param($this->lektor_uid);
			if($this->unr!='')
				$sql_query.=", unr=".$this->db_add_param($this->unr);
			$sql_query.=", updateamum=now(), updatevon=".$this->db_add_param($uid);
			$sql_query.=" WHERE $stpl_id=".$this->db_add_param($this->stundenplan_id);

			$this->lastqry = $sql_query;
			//Datenbankabfrage
			if (!$this->db_query($sql_query))
			{
				$this->errormsg=$sql_query.$this->db_last_error();
				return false;
			}
		}

		return true;
	}

	/**
	 * Erstellt einen Undo Befehl fuer die Speichern funktion
	 *
	 * @param $stpl_table
	 * @return string undo
	 */
	public function getUndo($stpl_table='stundenplandev')
	{
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		$sql_query='UPDATE '.$stpl_table;
		$sql_query.=" SET datum=".$this->db_add_param($this->datum).", stunde=".$this->db_add_param($this->stunde);
		$sql_query.=", ort_kurzbz=".$this->db_add_param($this->ort_kurzbz).", mitarbeiter_uid=".$this->db_add_param($this->lektor_uid);
		$sql_query.=", updateamum=".$this->db_add_param($this->updateamum).", updatevon=".$this->db_add_param($this->updatevon);
		$sql_query.=" WHERE $stpl_id=".$this->db_add_param($this->stundenplan_id).";";

		return $sql_query;
	}

	/**
	 *	Datensatz aus DB entfernen
	 * @param id ID des Datensatzes in der Tabelle
	 * @param stpl_table Name der Tabelle
	 *
	 */
	public function delete($id, $stpl_table='stundenplandev')
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;
		// Delete SQL vorbereiten
		$sql_query='DELETE FROM '.$stpl_table;
		$sql_query.=" WHERE $stpl_id=".$this->db_add_param($id);

		$this->lastqry = $sql_query;
		//Datenbankrequest
		if (!$this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return false;
		}
		else
			return true;
	}

	/**
	 * Laedt Lehrstunden
	 *
	 * @param type (student, lektor, lehrverband, gruppe, ort, ....)
	 * @param datum_von (inklusive) Startdatum der Abfrage
	 * @param datum_bis (exklusive) Enddatum der Abfrage
	 * @param uid (des Lektors oder Studenten) kann auch NULL sein
	 * @param ort_kurzbz (Kurzbezeichnung des Orts) kann auch NULL sein
	 * @param studiengang_kz
	 * @param sem
	 * @param ver
	 * @param grp
	 * @param gruppe_kurzbz
	 *
	 */
	public function load_lehrstunden($type, $datum_von, $datum_bis, $uid, $ort_kurzbz=NULL, $studiengang_kz=NULL, $sem=NULL, $ver=NULL, $grp=NULL, $gruppe_kurzbz=NULL, $stpl_view='stundenplan', $idList=null, $fachbereich_kurzbz=null, $lva=NULL, $alle_unr_mitladen=false)
	{
		$num_rows_einheit=0;
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_view.TABLE_ID;
		$stpl_view_ohneschema=VIEW_BEGIN.$stpl_view;
		$stpl_view='lehre.'.VIEW_BEGIN.$stpl_view;


		// Datum im Format YYYY-MM-TT ?
		if (!preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/",$datum_von) )
		{
			$this->errormsg='Fehler: Startdatum hat falsches Format!';
			return -1;
		}
		if ($datum_bis!=null)
		{
			if (!preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/",$datum_bis) )
			{
				$this->errormsg='Fehler: Enddatum hat falsches Format!';
				return -1;
			}
		}
		else
			$datum_bis=$datum_von;

		// Person
		if (($type=='student' || $type=='lektor') && $uid==NULL)
		{
			$this->errormsg='Fehler: uid der Person ist nicht gesetzt';
			return -1;
		}
		// Ort
		if ($type=='ort' && $ort_kurzbz==NULL)
		{
			$this->errormsg='Fehler: Kurzbezeichnung des Orts ist nicht gesetzt';
			return -1;
		}
		// Gruppe
		if ($type=='gruppe' && $gruppe_kurzbz==NULL)
		{
			$this->errormsg='Fehler: Kurzbezeichnung der Gruppe ist nicht gesetzt';
			return -1;
		}
		// Verband
		if ($type=='verband' && ($studiengang_kz==NULL || !is_numeric($studiengang_kz)))
		{
			$this->errormsg='Fehler: Studiengang ist nicht gesetzt';
			return -1;
		}
		// LVA
		if ($type=='lva' && $lva==NULL)
		{
			$this->errormsg='Fehler: LVA-ID ist nicht gesetzt';
			return -1;
		}
		if ($type=='lva' && !is_numeric($lva))
		{
			$this->errormsg='Fehler: LVA-ID ist ungueltig';
			return -1;
		}
		// Type
		if ($type==null)
		{
			$this->errormsg='Fehler: Type in "lehrstunde->load_lehrstunde" ist nicht gesetzt!';
			return -1;
		}

		// Zusaetzliche Daten ermitteln
		// Personendaten
		if ($type=='student')
		{
			if(defined('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE') && LVPLAN_LOAD_UEBER_SEMESTERHAELFTE === true)
				$lvplan_load_ueber_semesterhaelfte = true;
			else
				$lvplan_load_ueber_semesterhaelfte = false;
			// Bei Studierenden wird das passende Studiensemester ermittelt und das dazupassende
			// naechstliegende Dadurch wird sichergestellt, dass Einheiten aus den Vorsemestern
			// zB fuer Nachpruefungen oder Einheiten aus den Folgesemestern die vorgezogen werden
			// auch im Plan sichtbar sind.
			if (is_null($this->ss))
			{
				$studiensemester_obj = new studiensemester();
				$this->ss = $studiensemester_obj->getSemesterFromDatum($datum_von,true);

				if($lvplan_load_ueber_semesterhaelfte)
				{
					$this->ssnext = $studiensemester_obj->getNextFrom($this->ss);
					$this->ssprev = $studiensemester_obj->getPreviousFrom($this->ss);
				}
				else
					$this->ssnext = $studiensemester_obj->getNearestTo($this->ss,$datum_von);
			}
			if(!isset($this->ssnext))
				$this->ssnext = $this->ss;

			if($lvplan_load_ueber_semesterhaelfte)
			{
				$sql_query="SELECT studiengang_kz, semester, verband, gruppe
     				FROM
						public.tbl_studentlehrverband
     					JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
     				WHERE student_uid=".$this->db_add_param($uid)."
     					AND studiensemester_kurzbz in(".$this->db_add_param($this->ss).",".$this->db_add_param($this->ssnext).",".$this->db_add_param($this->ssprev).")
     				ORDER BY start desc LIMIT 2";
			}
			else
			{
				// Lehrverbandszuordnungen der betreffenden Studiensemester laden
				$sql_query="SELECT studiengang_kz, semester, verband, gruppe
					FROM public.tbl_studentlehrverband
					WHERE student_uid=".$this->db_add_param($uid)."
					AND studiensemester_kurzbz in(".$this->db_add_param($this->ss).",".$this->db_add_param($this->ssnext).")";
			}

			$verbaende=array();
			if($this->db_query($sql_query))
			{
				$num_rows=$this->db_num_rows();
				if ($num_rows>0)
				{
					while($row = $this->db_fetch_object())
					{
						$verbaende[] = array('studiengang_kz'=>$row->studiengang_kz,
						'sem'=>$row->semester,
						'ver'=>$row->verband,
						'grp'=>$row->gruppe);
					}
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Verbaende';
				return -2;
			}
			// Spezialgruppen ermitteln zu denen die Person zugeteilt ist
			if($lvplan_load_ueber_semesterhaelfte)
			{
				$sql_query="SELECT
						gruppe_kurzbz
					FROM
						public.tbl_benutzergruppe
					WHERE
						uid=".$this->db_add_param($uid)."
						AND (studiensemester_kurzbz=".$this->db_add_param($this->ss)."
							OR studiensemester_kurzbz=".$this->db_add_param($this->ssnext)."
							OR studiensemester_kurzbz=".$this->db_add_param($this->ssprev)."
							OR studiensemester_kurzbz IS NULL)";
			}
			else
			{
				$sql_query="SELECT
						gruppe_kurzbz
					FROM
						public.tbl_benutzergruppe
					WHERE
						uid=".$this->db_add_param($uid)."
						AND (studiensemester_kurzbz=".$this->db_add_param($this->ss)."
							OR studiensemester_kurzbz=".$this->db_add_param($this->ssnext)."
							OR studiensemester_kurzbz IS NULL)";
			}

			if (!$result_einheit=$this->db_query($sql_query))
			{
				$this->errormsg= 'Fehler beim Laden der Gruppen';
				return false;
			}
			else
				$num_rows_einheit=$this->db_num_rows($result_einheit);
		}
		// Bei Lektoren auch Spezialgruppen wegen Reservierungen abfragen
		if ($type == 'lektor')
		{
			if (is_null($this->ss))
			{
				$studiensemester_obj = new studiensemester();
				$this->ss = $studiensemester_obj->getSemesterFromDatum($datum_von,true);
				$this->ssnext = $studiensemester_obj->getNearestTo($this->ss,$datum_von);
			}
			if(!isset($this->ssnext))
				$this->ssnext = $this->ss;
			// Spezialgruppen ermitteln zu denen die Person zugeteilt ist
			$sql_query="SELECT
							gruppe_kurzbz
						FROM
							public.tbl_benutzergruppe
						WHERE
							uid=".$this->db_add_param($uid)."
							AND (studiensemester_kurzbz=".$this->db_add_param($this->ss)."
								OR studiensemester_kurzbz=".$this->db_add_param($this->ssnext)."
								OR studiensemester_kurzbz IS NULL)";

			if (!$result_einheit=$this->db_query($sql_query))
			{
				$this->errormsg='Fehler beim Laden der Gruppen';
				return false;
			}
			else
				$num_rows_einheit=$this->db_num_rows($result_einheit);
		}

		// Stundenplandaten ermitteln
		// Abfrage generieren

		if ($type!='idList')
		{
			if($alle_unr_mitladen)
				$sql_query_stdplan='SELECT '.$stpl_id.', datum, stunde, unr FROM '.$stpl_view.' stplvw';
			else
				$sql_query_stdplan='SELECT * FROM '.$stpl_view.' stplvw';
			$sql_query_lva="";
			$sql_query=" WHERE datum>=".$this->db_add_param($datum_von)." AND datum<".$this->db_add_param($datum_bis);
			if ($type == 'lva')
				$sql_query_lva=" AND lehrveranstaltung_id=".$this->db_add_param($lva);
			elseif ($type=='lektor')
			{
				$sql_query.=" AND (";
				$sql_query.=" uid=".$this->db_add_param($uid);
				//Reservierungen mit Spezialgruppen laden
				for ($i=0;$i<$num_rows_einheit;$i++)
				{
					$row=$this->db_fetch_object($result_einheit,$i);
					$sql_query.=" OR gruppe_kurzbz=".$this->db_add_param($row->gruppe_kurzbz);
				}
				$sql_query.=')';
			}
			elseif ($type=='ort' && $ort_kurzbz != 'all')
				$sql_query.=" AND ort_kurzbz=".$this->db_add_param($ort_kurzbz);
			elseif ($type=='ort' && $ort_kurzbz == 'all')
				$sql_query.=" AND ort_kurzbz IS NOT NULL AND ort_kurzbz !='Dummy'";
			elseif ($type=='gruppe')
				$sql_query.=" AND gruppe_kurzbz=".$this->db_add_param($gruppe_kurzbz);
			elseif($type=='fachbereich')
				$sql_query.=" AND fachbereich_kurzbz=".$this->db_add_param($fachbereich_kurzbz);
			elseif($type=='student')
			{
				$sql_query.=" AND (";
				if(is_array($verbaende) && count($verbaende)>0)
				{
					foreach($verbaende as $row_verbaende)
					{
						$studiengang_kz = $row_verbaende['studiengang_kz'];
						$ver = $row_verbaende['ver'];
						$sem = $row_verbaende['sem'];
						$grp = $row_verbaende['grp'];

						$sql_query.=" (studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
						if ($sem!=null && $sem>=0  && $sem!='')
							$sql_query.=" AND (semester=".$this->db_add_param($sem)." OR semester IS NULL)";
						if ($ver!='0' && $ver!=null && $ver!='')
							$sql_query.=" AND (verband=".$this->db_add_param($ver)." OR verband IS NULL OR verband='0' OR verband='')";
						if ($grp!='0' && $grp!=null && $grp!='')
							$sql_query.=" AND (gruppe=".$this->db_add_param($grp)." OR gruppe IS NULL OR gruppe='0' OR gruppe='')";
						$sql_query.=" AND gruppe_kurzbz is null";
						$sql_query.=") OR ";
					}
				}

				$sql_query.=" 1!=1";
				for ($i=0;$i<$num_rows_einheit;$i++)
				{
					$row=$this->db_fetch_object($result_einheit,$i);
					$sql_query.=" OR gruppe_kurzbz=".$this->db_add_param($row->gruppe_kurzbz);
				}
				$sql_query.=')';
			}
			else
			{
				$sql_query.=" AND ( (studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
				if ($sem!=null && $sem>=0  && $sem!='')
					$sql_query.=" AND (semester=".$this->db_add_param($sem)." OR semester IS NULL)";
				if ($ver!='0' && $ver!=null && $ver!='')
					$sql_query.=" AND (verband=".$this->db_add_param($ver)." OR verband IS NULL OR verband='0' OR verband='')";
				if ($grp!='0' && $grp!=null && $grp!='')
					$sql_query.=" AND (gruppe=".$this->db_add_param($grp)." OR gruppe IS NULL OR gruppe='0' OR gruppe='')";

				// Direkte Gruppen werden ausgenommen da sonst Stunden von Verband A in der Ansicht für Verband B
				// mit angezeigt werden weil die direkte Gruppe geladen wird.
				$sql_query.=' AND
					(
						gruppe_kurzbz is null
						OR
						EXISTS(
							SELECT 1 FROM public.tbl_gruppe
							WHERE
								gruppe_kurzbz = stplvw.gruppe_kurzbz
								AND direktinskription=false
							)
					)';

				$sql_query.=' )';

				for ($i=0;$i<$num_rows_einheit;$i++)
				{
					$row=$this->db_fetch_object($result_einheit,$i);
					$sql_query.=" OR gruppe_kurzbz=".$this->db_add_param($row->gruppe_kurzbz);
				}
				$sql_query.=')';
			}
			$sql_query_orderby=' ORDER BY  datum, stunde, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, uid';
			$sql_query_stdplan.=$sql_query . $sql_query_lva . $sql_query_orderby;

			// Wenn aktiviert, werden alle Stunden mit der gleichen UNR geladen die zur selben Zeit stattfinden
			if($alle_unr_mitladen)
			{
				$sql_query_stdplan="
					WITH lvplan(id, datum, stunde, unr) as
					(
						$sql_query_stdplan
					)
					SELECT
						distinct $stpl_view_ohneschema.*
					FROM
					".$stpl_view.", lvplan
					WHERE
						$stpl_view_ohneschema.datum=lvplan.datum
						AND $stpl_view_ohneschema.stunde=lvplan.stunde
						AND $stpl_view_ohneschema.unr=lvplan.unr
					ORDER BY datum, stunde, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, uid
				";
			}
		}
		else
		{
			$sql_query_stdplan='SELECT * FROM '.$stpl_view;
			$sql_query='';
			foreach ($idList as $id)
				$sql_query.=" OR ".$stpl_id."=".$this->db_add_param($id);
			$sql_query=mb_substr($sql_query,3);
			$sql_query_stdplan.=' WHERE'.$sql_query;
		}

		//Datenbankabfrage
		if (!$this->db_query($sql_query_stdplan))
		{
			$this->errormsg = 'Fehler beim Laden der Stundenplandaten';
			return -2;
		}
		$stpl_tbl = $this->db_result;
		$num_rows = $this->db_num_rows($stpl_tbl);
		$this->anzahl=$num_rows;
		//Daten uebernehmen
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$this->db_fetch_object($stpl_tbl, $i);

			$stunde=new lehrstunde();
			$stunde->stundenplan_id=$row->{$stpl_id};
			$stunde->lehreinheit_id=$row->lehreinheit_id;
			$stunde->unr=$row->unr;
			$stunde->lektor_uid=$row->uid;
			$stunde->lektor_kurzbz=$row->lektor;
			$stunde->mitarbeiter_kurzbz=$row->mitarbeiter_kurzbz;
			$stunde->datum=$row->datum;
			$stunde->stunde=$row->stunde;
			$stunde->ort_kurzbz=$row->ort_kurzbz;
			$stunde->lehrfach=$row->lehrfach;
			$stunde->lehrfach_bez=$row->lehrfach_bez;
			$stunde->lehrfach_id=$row->lehrfach_id;
			$stunde->lehrform=$row->lehrform;
			if ($row->farbe!='      ' && $row->farbe!=null)
				$stunde->farbe=$row->farbe;
			else
				$stunde->farbe='FFFFFF';
			$stunde->studiengang_kz=$row->studiengang_kz;
			$stunde->studiengang=mb_strtoupper($row->stg_typ.$row->stg_kurzbz);
			$stunde->sem=$row->semester;
			$stunde->ver=$row->verband;
			$stunde->grp=$row->gruppe;
			$stunde->gruppe_kurzbz=$row->gruppe_kurzbz;
			$stunde->titel=$row->titel;
			$stunde->anmerkung=$row->anmerkung;
			$stunde->anmerkung_lehreinheit=$row->anmerkung_lehreinheit;
			$stunde->updateamum=$row->updateamum;
			$stunde->updatevon=$row->updatevon;
			$stunde->reservierung=false;
			$this->lehrstunden[$i]=$stunde;

		}

		// Reservierungsdaten ermitteln
		if ($type!='idList' && $type!='fachbereich' && $type!='lva')
		{
			// Datenbankabfrage generieren
			$sql_query_reservierung='SELECT * FROM campus.vw_reservierung stplvw';
			$sql_query_reservierung.=$sql_query . $sql_query_orderby;

			//Datenbankabfrage
			if (!$this->db_query($sql_query_reservierung))
			{
				$this->errormsg = 'Fehler beim Laden der Reservierungen';
				return -2;
			}
			$stpl_tbl = $this->db_result;
			$num_rows=$this->db_num_rows($stpl_tbl);
			$this->anzahl+=$num_rows;

			//Daten uebernehmen
			for ($i=0;$i<$num_rows;$i++)
			{
				$row = $this->db_fetch_object($stpl_tbl, $i);

				$stunde=new lehrstunde();
				$stunde->reservierung=true;
				$stunde->stundenplan_id=$row->reservierung_id;
				$stunde->unr=0;
				$stunde->lektor_uid=$row->uid;
				$stunde->lektor_kurzbz=$row->uid;
				$stunde->datum=$row->datum;
				$stunde->stunde=$row->stunde;
				$stunde->ort_kurzbz=$row->ort_kurzbz;
				//$stunde->lehrfach_id=$row->lehrfach_id;
				$stunde->lehrfach=$row->titel;
				$stunde->lehrfach_bez=$row->beschreibung;
				$stunde->studiengang_kz=$row->studiengang_kz;
				$stunde->studiengang=$row->stg_kurzbz;
				$stunde->sem=$row->semester;
				$stunde->ver=$row->verband;
				$stunde->grp=$row->gruppe;
				$stunde->gruppe_kurzbz=$row->gruppe_kurzbz;
				$stunde->titel=$row->titel;
				$stunde->anmerkung=$row->beschreibung;
				$stunde->anmerkung_lehreinheit=$row->beschreibung;
				$stunde->updateamum=$row->insertamum;
				$stunde->updatevon=$row->insertvon;
				$stunde->farbe='';
				$stunde->mitarbeiter_kurzbz = $row->mitarbeiter_kurzbz;
				$this->lehrstunden[]=$stunde;
			}
		}
		return $this->anzahl;
	}

	/**
	 * @param lehreinheit_id
	 * @param uid (mitarbeiter)
	 *
	 */
	public function load_lehrstunden_le($lehreinheit_id, $uid=null, $stpl_table='stundenplandev')
	{
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		// Stundenplandaten ermitteln
		// Abfrage generieren
		$sql="SELECT * FROM ".$stpl_table." WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		if ($uid!=null && !is_null($uid))
			$sql.=" AND mitarbeiter_uid=".$this->db_add_param($uid);

		//Datenbankabfrage
		if (!$this->db_query($sql))
		{
			$this->errormsg=$this->db_last_error();
			return -1;
		}
		$num_rows=$this->db_num_rows();
		$this->anzahl=$num_rows;
		//Daten uebernehmen
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$this->db_fetch_object(null, $i);

			$stunde=new lehrstunde();
			$stunde->stundenplan_id=$row->{$stpl_id};
			$stunde->lehreinheit_id=$row->lehreinheit_id;
			$stunde->unr=$row->unr;
			$stunde->studiengang_kz=$row->studiengang_kz;
			$stunde->sem=$row->semester;
			$stunde->ver=$row->verband;
			$stunde->grp=$row->gruppe;
			$stunde->gruppe_kurzbz=$row->gruppe_kurzbz;
			$stunde->lektor_uid=$row->mitarbeiter_uid;
			$stunde->ort_kurzbz=$row->ort_kurzbz;
			$stunde->datum=$row->datum;
			$stunde->stunde=$row->stunde;
			$stunde->titel=$row->titel;
			$stunde->anmerkung=$row->anmerkung;
			$stunde->fix=$row->fix;
			$stunde->insertamum=$row->insertamum;
			$stunde->insertvon=$row->insertvon;
			$stunde->updateamum=$row->updateamum;
			$stunde->updatevon=$row->updatevon;
			$stunde->reservierung=false;
			$this->lehrstunden[$i]=$stunde;
		}
		return $this->anzahl;
	}


	/**
	 * Prueft die geladene Lehrveranstaltung auf Kollisionen im Stundenplan.
	 * Bei einer Kollision steht der Grund der Kollision in '$this->errormsg'.
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @return boolean true=kollision, false=keine kollision
	 */
	public function kollision($stpl_table='stundenplandev')
	{
		$variablen_obj = new variable();
		$variablen_obj->loadVariables(get_uid());

		$kollision_student = $variablen_obj->variable->kollision_student;
		$ignore_reservierung = $variablen_obj->variable->ignore_reservierung;
		$ignore_zeitsperre = $variablen_obj->variable->ignore_zeitsperre;

		//Kollisionspruefung auf Studentenebene
		if($kollision_student=='true' && $this->kollision_student($stpl_table))
			return true;

		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.VIEW_BEGIN.$stpl_table;

		// Datenbank abfragen
		$sql_query="SELECT $stpl_id AS id, lektor, stg_kurzbz, ort_kurzbz, semester, verband, gruppe, gruppe_kurzbz, datum, stunde FROM $stpl_table
				WHERE datum=".$this->db_add_param($this->datum)." AND stunde=".$this->db_add_param($this->stunde);

		// Direkte Lehreinheitsgruppen kollidieren nicht
		$sql_query.=" AND NOT EXISTS(SELECT 1 FROM public.tbl_gruppe WHERE gruppe_kurzbz = ".$stpl_table.".gruppe_kurzbz AND direktinskription=true)";

		$sql_query.= " AND (ort_kurzbz=".$this->db_add_param($this->ort_kurzbz)." ";

		if (!in_array($this->lektor_uid,unserialize(KOLLISIONSFREIE_USER)))
			$sql_query.=" OR (uid=".$this->db_add_param($this->lektor_uid)." AND uid not in (".$this->db_implode4SQL(unserialize(KOLLISIONSFREIE_USER))."))";

		//Wenn eine Kollisionspruefung auf Studentenebene durchgefuehrt wird, werden die LVB nicht gecheckt
		if($kollision_student=='false')
		{
			// Direkte Gruppen kollidieren nicht
			$direktgruppe = false;
			if($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
			{
				$grp_obj = new gruppe();
				$grp_obj->load($this->gruppe_kurzbz);
				if($grp_obj->direktinskription)
				{
					$direktgruppe = true;
				}
			}
			if(!$direktgruppe)
			{
				$sql_query.=" OR (studiengang_kz=".$this->db_add_param($this->studiengang_kz)." AND semester=".$this->db_add_param($this->sem);
				if($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
				{
					$sql_query.=" OR (gruppe_kurzbz=".$this->db_add_param($this->gruppe_kurzbz).")";
				}
				else
				{
					if ($this->ver!=null && $this->ver!='' && $this->ver!=' ')
						$sql_query.=" AND (verband=".$this->db_add_param($this->ver)." OR verband IS NULL OR verband='' OR verband=' ')";
					if ($this->grp!=null && $this->grp!='' && $this->grp!=' ')
						$sql_query.=" AND (gruppe=".$this->db_add_param($this->grp)." OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
				}

				$sql_query.=")";
			}
		}
		$sql_query.=") AND unr!=".$this->db_add_param($this->unr);

		if (!$erg_stpl = $this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return true;
		}

		$anz=$this->db_num_rows($erg_stpl);
		if ($anz==0)
		{
			// Zeitsperren pruefen
			if($ignore_zeitsperre=='false' && !in_array($this->lektor_uid,unserialize(KOLLISIONSFREIE_USER)) && $this->kollision_zeitsperre())
				return true;

			// Reservierungen pruefen
			if ($ignore_reservierung=='false' && $this->kollision_reservierung())
				return true;

			if($this->kollision_ressource())
				return true;
			return false;
		}
		else
		{
			$row = $this->db_fetch_object($erg_stpl);
			$this->errormsg="Kollision ($stpl_table): $row->id|$row->lektor|$row->ort_kurzbz|$row->stg_kurzbz-$row->semester$row->verband$row->gruppe$row->gruppe_kurzbz - $row->datum/$row->stunde\n"; //\n".$sql_query
			return true;
		}
	}

	/**
	 * Prueft ob eine Kollision mit den zugeteilten Ressourcen vorhanden ist
	 *
	 * @return boolean true=kollision, false=keine kollision
	 */
	public function kollision_ressource()
	{
		$qry = "SELECT
					tbl_betriebsmittel.beschreibung, tbl_stundenplandev.ort_kurzbz
				FROM
					lehre.tbl_stundenplan_betriebsmittel
					JOIN lehre.tbl_stundenplandev USING(stundenplandev_id)
					JOIN wawi.tbl_betriebsmittel USING(betriebsmittel_id)
				WHERE
					tbl_stundenplandev.datum=".$this->db_add_param($this->datum)."
					AND tbl_stundenplandev.stunde=".$this->db_add_param($this->stunde)."
					AND betriebsmittel_id IN(SELECT betriebsmittel_id FROM lehre.tbl_stundenplan_betriebsmittel WHERE stundenplandev_id=".$this->db_add_param($this->stundenplan_id).")
					AND tbl_stundenplandev.stundenplandev_id<>".$this->db_add_param($this->stundenplan_id);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->errormsg='Kollision (Ressource)'.$row->beschreibung.'|'.$row->ort_kurzbz;
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Prueft ob eine Kollision mit den Zeitsperren vorhanden ist
	 *
	 * @return boolean true=kollision, false=keine kollision
	 */
	public function kollision_zeitsperre()
	{
		$sql_query="SELECT
						zeitsperre_id,zeitsperretyp_kurzbz,mitarbeiter_uid AS lektor,vondatum,vonstunde,bisdatum,bisstunde
					FROM campus.tbl_zeitsperre
					WHERE mitarbeiter_uid=".$this->db_add_param($this->lektor_uid)."
						AND (vondatum<".$this->db_add_param($this->datum)." OR (vondatum=".$this->db_add_param($this->datum)." AND (vonstunde<=".$this->db_add_param($this->stunde)." OR vonstunde IS NULL)))
						AND (bisdatum>".$this->db_add_param($this->datum)." OR (bisdatum=".$this->db_add_param($this->datum)." AND (bisstunde>=".$this->db_add_param($this->stunde)." OR bisstunde IS NULL)));";

		if (!$erg_zs = $this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return true;
		}

		$anz_zs=$this->db_num_rows($erg_zs);
		if ($anz_zs!=0)
		{
			$row = $this->db_fetch_object($erg_zs);

			if ($row->zeitsperretyp_kurzbz != 'ZVerfueg')
			{
				$this->errormsg="Kollision (Zeitsperre): $row->zeitsperre_id|$row->lektor|$row->zeitsperretyp_kurzbz - $row->vondatum/$row->vonstunde|$row->bisdatum/$row->bisstunde";
				return true;
			}
		}
		return false;
	}

	/**
	 * Prueft ob eine LV-Plan Kollision mit den Reservierungen besteht
	 *
	 * @return boolean true=kollision, false=keine kollision
	 */
	public function kollision_reservierung()
	{
		$sql_query="SELECT
						reservierung_id AS id, uid AS lektor, stg_kurzbz, ort_kurzbz,
						semester, verband, gruppe, gruppe_kurzbz, datum, stunde
					FROM lehre.vw_reservierung
					WHERE
						datum=".$this->db_add_param($this->datum)." AND
						stunde=".$this->db_add_param($this->stunde)." AND
						(ort_kurzbz=".$this->db_add_param($this->ort_kurzbz)." OR ";

		if (!in_array($this->lektor_uid, unserialize(KOLLISIONSFREIE_USER)))
			$sql_query.="(uid=".$this->db_add_param($this->lektor_uid)." AND uid not in(".$this->db_implode4SQL(unserialize(KOLLISIONSFREIE_USER)).")) OR ";

		$sql_query.="(studiengang_kz=".$this->db_add_param($this->studiengang_kz)." AND semester=".$this->db_add_param($this->sem);
		if ($this->ver!=null && $this->ver!='' && $this->ver!=' ')
			$sql_query.=" AND (verband=".$this->db_add_param($this->ver)." OR verband IS NULL OR verband='' OR verband=' ')";
		if ($this->grp!=null && $this->grp!='' && $this->grp!=' ')
			$sql_query.=" AND (gruppe=".$this->db_add_param($this->grp)." OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
		if ($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
			$sql_query.=" AND (gruppe_kurzbz=".$this->db_add_param($this->gruppe_kurzbz).")";
		$sql_query.="))";

		if (!$erg_res = $this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return true;
		}
		$anz_res = $this->db_num_rows($erg_res);

		if ($anz_res!=0)
		{
			$row = $this->db_fetch_object($erg_res);
			$this->errormsg="Kollision (Reservierung): $row->id|$row->lektor|$row->ort_kurzbz|$row->stg_kurzbz-$row->semester$row->verband$row->gruppe$row->gruppe_kurzbz - $row->datum/$row->stunde";
			return true;
		}
		return false;
	}

	/**
	 * Prueft eine Kollision auf Studentenebene
	 * Es werden nur die Kollisionen der Studenten abgefragt
	 * Raum, Lektor, Reservierung, Zeitsperren, etc werden hier nicht geprueft
	 *
	 * @param $stpl_table
	 * @return boolean true=kollision, false=keine kollision
	 */
	public function kollision_student($stpl_table='stundenplandev')
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle
		$stpl_table='lehre.'.VIEW_BEGIN.$stpl_table;

		$sql_query = "SELECT *
			FROM ".$stpl_table."_student_unr
			WHERE datum=".$this->db_add_param($this->datum)." AND stunde=".$this->db_add_param($this->stunde)." AND student_uid IN(
			SELECT uid FROM public.vw_gruppen WHERE

		   ";
		$sql_query.="(studiengang_kz=".$this->db_add_param($this->studiengang_kz)." AND semester=".$this->db_add_param($this->sem)."
			AND studiensemester_kurzbz=(
					SELECT tbl_studiensemester.studiensemester_kurzbz
					FROM
						public.tbl_studiensemester
                   	WHERE
                   		tbl_studiensemester.ende >= ".$this->db_add_param($this->datum)."
                    	AND tbl_studiensemester.start <=".$this->db_add_param($this->datum)." LIMIT 1)";
		if ($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
			$sql_query.=" AND (gruppe_kurzbz=".$this->db_add_param($this->gruppe_kurzbz).")";
		else
		{
			if ($this->ver!=null && $this->ver!='' && $this->ver!=' ')
				$sql_query.=" AND (verband=".$this->db_add_param($this->ver).")";
			else
				$sql_query.=" AND (verband IS NULL OR verband='' OR verband=' ')";
			if ($this->grp!=null && $this->grp!='' && $this->grp!=' ')
				$sql_query.=" AND (gruppe=".$this->db_add_param($this->grp).")";
			else
				$sql_query.=" AND (gruppe IS NULL OR gruppe='' OR gruppe=' ')";
		}


		$sql_query.=")) AND unr!=".$this->db_add_param($this->unr);

		if (!$erg_stpl=$this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return true;
		}

		$anz=$this->db_num_rows($erg_stpl);

		if ($anz>0)
		{
			$row = $this->db_fetch_object($erg_stpl);
			$this->errormsg="Kollision Student ($stpl_table): $row->student_uid $row->datum/$row->stunde ";
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Gruppiert die einzelnen Lehrstunden zusammen
	 */
	public function getLehrstundenGruppiert()
	{
		$result = array();

		foreach($this->lehrstunden as $row_lehrstunde)
		{
			$found=false;
			//Pruefen ob bereits ein Eintrag vorhanden ist
			//zu dem dazugruppiert werden kann

			/*
			Kriterien fuer Gruppierung
				- gleiches Datum
				- gleiche Stunde
				- gleiche UNR
			*/
			foreach($result as $key=>$row_result)
			{
				if($row_result->unr==$row_lehrstunde->unr
				&& $row_result->datum==$row_lehrstunde->datum
				&& $row_result->stunde==$row_lehrstunde->stunde)
				{
					$found=true;
					//gleicher Eintrag gefunden
					$grpidx = count($result[$key]->gruppen);
					$result[$key]->gruppen[$grpidx]->studiengang_kz=$row_lehrstunde->studiengang_kz;
					$result[$key]->gruppen[$grpidx]->sem=$row_lehrstunde->sem;
					$result[$key]->gruppen[$grpidx]->ver=$row_lehrstunde->ver;
					$result[$key]->gruppen[$grpidx]->grp=$row_lehrstunde->grp;
					$result[$key]->gruppen[$grpidx]->gruppe_kurzbz=$row_lehrstunde->gruppe_kurzbz;
					if(!in_array($row_lehrstunde->lektor_uid, $result[$key]->lektor_uid))
						$result[$key]->lektor_uid[]=$row_lehrstunde->lektor_uid;
					if(!in_array($row_lehrstunde->ort_kurzbz, $result[$key]->ort_kurzbz))
						$result[$key]->ort_kurzbz[]=$row_lehrstunde->ort_kurzbz;
					break;
				}
			}

			if(!$found)
			{
				// Wenn kein passender Eintrag vorhanden ist,
				// wird ein neuer angelegt
				$stunde=new lehrstunde();
				$stunde->stundenplan_id=$row_lehrstunde->stundenplan_id;
				$stunde->lehreinheit_id=$row_lehrstunde->lehreinheit_id;
				$stunde->farbe = (isset($row_lehrstunde->farbe)?$row_lehrstunde->farbe:'FFFFFF');
				$stunde->unr=$row_lehrstunde->unr;
				$stunde->gruppen[0]->studiengang_kz=$row_lehrstunde->studiengang_kz;
				$stunde->gruppen[0]->sem=$row_lehrstunde->sem;
				$stunde->gruppen[0]->ver=$row_lehrstunde->ver;
				$stunde->gruppen[0]->grp=$row_lehrstunde->grp;
				$stunde->gruppen[0]->gruppe_kurzbz=$row_lehrstunde->gruppe_kurzbz;
				$stunde->lektor_uid[]=$row_lehrstunde->lektor_uid;
				$stunde->ort_kurzbz[]=$row_lehrstunde->ort_kurzbz;
				$stunde->datum=$row_lehrstunde->datum;
				$stunde->stunde=$row_lehrstunde->stunde;
				$stunde->titel=$row_lehrstunde->titel;
				$stunde->anmerkung=$row_lehrstunde->anmerkung;
				$stunde->fix=$row_lehrstunde->fix;
				$stunde->reservierung=$row_lehrstunde->reservierung;
				$result[]=$stunde;
			}
		}
		return $result;
	}

	/**
	 * Holt Studenplandaten.
	 *
	 * @param $db_stpl_table
	 * @param null $lehrveranstaltung_id
	 * @param null $studiensemester_kurzbz
	 * @param null $lehreinheit_id
	 * @param null $mitarbeiter_uid
	 * @param null $student_uid
	 * @param false $nurBevorstehende Wenn true, dann werden nur bevorstehende LVs abgefragt.
	 * @return bool
	 */
	public function getStundenplanData($db_stpl_table, $lehrveranstaltung_id=null, $studiensemester_kurzbz=null, $lehreinheit_id=null, $mitarbeiter_uid=null, $student_uid=null, $nurBevorstehende = false)
	{

		$qry = "SELECT
					stpl.datum, min(stpl.stunde) as stundevon, max(stpl.stunde) as stundebis,
					stpl.lehreinheit_id, lehrfach.bezeichnung as lehrfach_bezeichnung,
					array_agg(
					CASE WHEN gruppe_kurzbz is not null THEN gruppe_kurzbz
					ELSE (SELECT UPPER(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=stpl.studiengang_kz) || COALESCE(stpl.semester,'0') || COALESCE(stpl.verband,'') || COALESCE(stpl.gruppe,'')
					END) as gruppen, array_agg(mitarbeiter_uid) as lektoren,
					array_agg(ort_kurzbz) as orte,
					array_agg(titel) as titel
				FROM
					lehre.tbl_".$db_stpl_table." as stpl
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
				WHERE ";

		if($lehrveranstaltung_id!='')
		{
			 $qry.=" lehreinheit_id in(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
							WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
								AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")";

		}
		elseif($lehreinheit_id!='')
		{
			$qry.=" lehreinheit_id=".$this->db_add_param($lehreinheit_id);
		}
		elseif($mitarbeiter_uid!='')
		{
			$qry.=" mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)." AND lehreinheit_id IN(
				SELECT
					lehreinheit_id
				FROM
					lehre.tbl_lehreinheitmitarbeiter
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid)." AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")";
		}
		elseif($student_uid!='')
		{
			$qry.=" tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					-- if student is assigned to lehreinheit through lehreinheitgruppe.
					AND (
					 lehreinheit_id IN (
						SELECT tbl_lehreinheit.lehreinheit_id
						FROM lehre.tbl_lehreinheitgruppe,
							tbl_benutzergruppe,
							lehre.tbl_lehreinheit,
							lehre.tbl_lehrveranstaltung
						WHERE tbl_lehreinheitgruppe.gruppe_kurzbz::text = tbl_benutzergruppe.gruppe_kurzbz::text
							AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id
							AND tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id
							AND tbl_lehreinheit.studiensemester_kurzbz::text = tbl_benutzergruppe.studiensemester_kurzbz::text
							AND uid=".$this->db_add_param($student_uid)."
							AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
						UNION
						SELECT tbl_lehreinheit.lehreinheit_id
						FROM lehre.tbl_lehreinheitgruppe,
							tbl_studentlehrverband,
							lehre.tbl_lehreinheit,
							lehre.tbl_lehrveranstaltung
						WHERE tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id
							AND tbl_lehreinheit.studiensemester_kurzbz = tbl_studentlehrverband.studiensemester_kurzbz
							AND tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id
							AND tbl_studentlehrverband.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
							AND tbl_studentlehrverband.semester = tbl_lehreinheitgruppe.semester
							AND
						(
							(
								(
									btrim(tbl_studentlehrverband.verband::text) = btrim(tbl_lehreinheitgruppe.verband::text)
									OR (tbl_lehreinheitgruppe.verband IS NULL OR btrim(tbl_lehreinheitgruppe.verband::text) = '')
									AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL
								)
								AND (
									btrim(tbl_studentlehrverband.gruppe::text) = btrim(tbl_lehreinheitgruppe.gruppe::text)
									OR (tbl_lehreinheitgruppe.gruppe IS NULL OR btrim(tbl_lehreinheitgruppe.gruppe::text) = '')
									AND tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL
								)
							)
							-- add also lehreinheiten directly from Stundenplan
							OR EXISTS (
								SELECT 1 FROM lehre.tbl_stundenplan
								WHERE
								lehreinheit_id = tbl_lehreinheit.lehreinheit_id
								AND studiengang_kz = tbl_studentlehrverband.studiengang_kz
								AND (semester = tbl_studentlehrverband.semester OR semester IS NULL)
								AND (verband = tbl_studentlehrverband.verband OR verband IS NULL OR verband ='0' OR verband = '')
								AND (gruppe = tbl_studentlehrverband.gruppe OR gruppe IS NULL OR gruppe ='0' OR gruppe = '')
								AND gruppe_kurzbz IS NULL
							)
						)
						AND student_uid=".$this->db_add_param($student_uid)."
						AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					)
				)";
		}
		else
			return false;

		if($nurBevorstehende)
		{
			$qry.= " AND datum >= NOW()::date ";
		}

		$qry.="GROUP BY stpl.datum, stpl.unr, stpl.lehreinheit_id, lehrfach.bezeichnung
					ORDER BY stpl.datum,  min(stpl.stunde), stpl.unr, stpl.lehreinheit_id";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->datum = $row->datum;
				$obj->stundevon = $row->stundevon;
				$obj->stundebis = $row->stundebis;
				$obj->gruppen = array_unique($this->db_parse_array($row->gruppen));
				$obj->lektoren = array_unique($this->db_parse_array($row->lektoren));
				$obj->orte = array_unique($this->db_parse_array($row->orte));
				$obj->titel = array_filter(array_unique($this->db_parse_array($row->titel)));
				$obj->lehrfach_bezeichnung = $row->lehrfach_bezeichnung;
				$obj->lehreinheit_id = $row->lehreinheit_id;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Einholen der Stundenplandaten';
			return false;
		}
	}
}

?>
