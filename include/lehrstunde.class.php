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
/****************************************************************************
 * @class 			Lehrstunde
 * @author	 		Christian Paminger
 * @date	 		2004/8/21
 * @version			$Revision: 1.2 $
 * Update: 			21.10.2004 von Christian Paminger
 * @brief  			Beschreibung einer Unterrichts-Stunde der Tabelle tbl_stundenplan
  *****************************************************************************/
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/studiensemester.class.php');

class lehrstunde extends basis_db
{
	public $stundenplan_id;	// @brief id in der Datenbank
	public $lehreinheit_id;	// @brief id der Lehreinheit in der DB
	public $unr;			// @brief Unterrichtsnummer
	public $lektor_uid;		// @brief UID des Lektors
	public $lektor_kurzbz; 	// @brief Kurzbezeichnung des Lektors
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


	/** 
	 * Konstruktor
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->new=TRUE;
	}

	/**
	 * Einen Datensatz laden
	 * @param stundenplan_id
	 * @param stpl_table
	 */
	public function load($stundenplan_id,$stpl_table='stundenplandev')
	{
		///////////////////////////////////////////////////////////////////////
		// Parameter Checken
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
			$sql_query.=" SET datum='$this->datum', stunde=$this->stunde";
			$sql_query.=", ort_kurzbz='$this->ort_kurzbz', mitarbeiter_uid='$this->lektor_uid'";
			$sql_query.=", updateamum=now(), updatevon='$uid'";
			$sql_query.=" WHERE $stpl_id=$this->stundenplan_id";
			//echo $sql_query."<br>";

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
		$sql_query.=" WHERE $stpl_id=$id";
		
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
	public function load_lehrstunden($type, $datum_von, $datum_bis, $uid, $ort_kurzbz=NULL, $studiengang_kz=NULL, $sem=NULL, $ver=NULL, $grp=NULL, $gruppe_kurzbz=NULL, $stpl_view='stundenplan', $idList=null)
	{
		///////////////////////////////////////////////////////////////////////
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_view.TABLE_ID;
		$stpl_view='lehre.'.VIEW_BEGIN.$stpl_view;

		// Datum im Format YYYY-MM-TT ?
		if (!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})",$datum_von) )
		{
			$this->errormsg='Fehler: Startdatum hat falsches Format!';
			return -1;
		}
		if ($datum_bis!=null)
		{
			if (!ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})",$datum_bis) )
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
		// Type
		if ($type==null)
		{
			$this->errormsg='Fehler: Type in "lehrstunde->load_lehrstunde" ist nicht gesetzt!';
			return -1;
		}

		///////////////////////////////////////////////////////////////////////
		// Zusaetzliche Daten ermitteln
		// Personendaten
		if ($type=='student')
		{
			// Lehrverband ermitteln
			$sql_query="SELECT studiengang_kz, semester, verband, gruppe FROM public.tbl_student WHERE student_uid='".addslashes($uid)."'";
			
			if (!$this->db_query($sql_query) )
			{
				$this->errormsg=$this->db_last_error();
				return -2;
			}
			$num_rows=$this->db_num_rows();
			if ($num_rows>0)
				$row=$this->db_fetch_object();
			else
			{
				$this->errormsg='Fehler: Student ('.$uid.') wurde nicht gefunden!';
				return -2;
			}
			$studiengang_kz=$row->studiengang_kz;
			$sem=$row->semester;
			$ver=$row->verband;
			$grp=$row->gruppe;

			// Gruppen ermitteln
			if (is_null($this->ss))
				$this->ss=studiensemester::getNearest();
			$sql_query="SELECT gruppe_kurzbz FROM public.tbl_benutzergruppe WHERE uid='".addslashes($uid)."' AND (studiensemester_kurzbz='".addslashes($this->ss)."' OR studiensemester_kurzbz IS NULL)";

			if (!$this->db_query($sql_query))
			{
				$this->errormsg=pg_last_error($this->conn);
				return false;
			}
			else
			{
				$result_einheit = $this->db_result;
			}				
		}

		///////////////////////////////////////////////////////////////////////
		// Stundenplandaten ermitteln
		// Abfrage generieren
		$sql_query_stdplan='SELECT * FROM '.$stpl_view;
		if ($type!='idList')
		{
			$sql_query=" WHERE datum>='$datum_von' AND datum<'$datum_bis'";
			if ($type=='lektor')
				$sql_query.=" AND uid='$uid'";
			elseif ($type=='ort')
				$sql_query.=" AND ort_kurzbz='$ort_kurzbz'";
			elseif ($type=='gruppe')
				$sql_query.=" AND gruppe_kurzbz='$gruppe_kurzbz'";
			else
			{
				$sql_query.=' AND ( (studiengang_kz='.$studiengang_kz;
				if ($sem!=null && $sem>=0  && $sem!='')
				{
					$sql_query.=" AND (semester=$sem OR semester IS NULL";
					if ($type=='student' && $sem>0)
						$sql_query.=' OR semester='.($sem+1);
					$sql_query.=')';
				}
				if ($ver!='0' && $ver!=null && $ver!='')
					$sql_query.=" AND (verband='$ver' OR verband IS NULL OR verband='0' OR verband='')";
				if ($grp!='0' && $grp!=null && $grp!='')
					$sql_query.=" AND (gruppe='$grp' OR gruppe IS NULL OR gruppe='0' OR gruppe='')";
				if ($type=='student')
					$sql_query.=' AND gruppe_kurzbz IS NULL';
				$sql_query.=' )';
				
				while($row=$this->db_fetch_object($result_einheit))
				{
					$sql_query.=" OR gruppe_kurzbz='$row->gruppe_kurzbz'";
				}
				$sql_query.=')';
			}
			$sql_query.=' ORDER BY  datum, stunde, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, uid';
			$sql_query_stdplan.=$sql_query;
		}
		else
		{
			$sql_query='';
			foreach ($idList as $id)
				$sql_query.=' OR '.$stpl_id.'='.$id;
			$sql_query=substr($sql_query,3);
			$sql_query_stdplan.=' WHERE'.$sql_query;
		}
		//echo $sql_query_stdplan;
		//Datenbankabfrage
		if (!$this->db_query($sql_query_stdplan))
		{
			$this->errormsg = $this->db_last_error();
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
			$stunde->studiengang=strtoupper($row->stg_typ.$row->stg_kurzbz);
			$stunde->sem=$row->semester;
			$stunde->ver=$row->verband;
			$stunde->grp=$row->gruppe;
			$stunde->gruppe_kurzbz=$row->gruppe_kurzbz;
			$stunde->titel=$row->titel;
			$stunde->anmerkung=$row->anmerkung;
			$stunde->updateamum=$row->updateamum;
			$stunde->updatevon=$row->updatevon;
			$stunde->reservierung=false;
			$this->lehrstunden[$i]=$stunde;
		}

		///////////////////////////////////////////////////////////////////////
		// Reservierungsdaten ermitteln
		if ($type!='idList')
		{
			// Datenbankabfrage generieren
			$sql_query_reservierung='SELECT * FROM campus.vw_reservierung';
			$sql_query_reservierung.=$sql_query;
			//echo $sql_query_reservierung;
			//Datenbankabfrage
			if (!$this->db_query($sql_query_reservierung))
			{
				$this->errormsg = $this->db_last_error();
				return -2;
			}
			$stpl_tbl = $this->db_result;
			$num_rows=$this->db_num_rows($stpl_tbl);
			$this->anzahl+=$num_rows;

			//Daten uebernehmen
			for ($i=0;$i<$num_rows;$i++)
			{
				$row = $this->db_fetch_object($stpl_tbl, $i);
				
				$stunde=new lehrstunde($this->conn);
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
				$stunde->farbe='';
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
		///////////////////////////////////////////////////////////////////////
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		///////////////////////////////////////////////////////////////////////
		// Stundenplandaten ermitteln
		// Abfrage generieren
		$sql="SELECT * FROM ".$stpl_table." WHERE lehreinheit_id='".addslashes($lehreinheit_id)."'";
		if ($uid!=null && !is_null($uid))
			$sql.=" AND mitarbeiter_uid='".addslashes($uid)."'";
		
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
	 * Rueckgabewert 'false' und die Fehlermeldung steht in '$this->errormsg'.
	 * @param string	datum	gewuenschtes Datum YYYY-MM-TT
	 * @param integer	stunde	gewuenschte Stunde
	 * @param string	ort		gewuenschter Ort
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @return boolean true=ok, false=fehler
	 */
	public function kollision($stpl_table='stundenplandev')
	{
		$ignore_reservation=false;
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.VIEW_BEGIN.$stpl_table;

		// Datenbank abfragen
		$sql_query="SELECT $stpl_id AS id, lektor, stg_kurzbz, ort_kurzbz, semester, verband, gruppe, gruppe_kurzbz, datum, stunde FROM $stpl_table
				WHERE datum='$this->datum' AND stunde=$this->stunde AND (ort_kurzbz='$this->ort_kurzbz' OR ";
		if ($this->lektor_uid!='_DummyLektor')
			$sql_query.="(uid='$this->lektor_uid' AND uid!='_DummyLektor') OR ";
		$sql_query.="(studiengang_kz=$this->studiengang_kz AND semester=$this->sem";
		if ($this->ver!=null && $this->ver!='' && $this->ver!=' ')
			$sql_query.=" AND (verband='$this->ver' OR verband IS NULL OR verband='' OR verband=' ')";
		if ($this->grp!=null && $this->grp!='' && $this->grp!=' ')
			$sql_query.=" AND (gruppe='$this->grp' OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
		if ($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
			$sql_query.=" AND (gruppe_kurzbz='$this->gruppe_kurzbz')";
		$sql_query.=")) AND unr!=$this->unr";
		
		if (!$this->db_query($sql_query))
		{
			$this->errormsg=$sql_query.$this->db_last_error();
			return true;
		}
		$erg_stpl = $this->db_result;
		$anz=$this->db_num_rows($erg_stpl);
		//Check
		if ($anz==0)
		{
			// Zeitsperren pruefen
			if ($this->lektor_uid!='_DummyLektor')
			{
				// Datenbank abfragen  	( studiengang_kz, titel, beschreibung )
				$sql_query="SELECT zeitsperre_id,zeitsperretyp_kurzbz,mitarbeiter_uid AS lektor,vondatum,vonstunde,bisdatum,bisstunde
							FROM campus.tbl_zeitsperre
							WHERE mitarbeiter_uid='$this->lektor_uid'
								AND (vondatum<'$this->datum' OR (vondatum='$this->datum' AND (vonstunde<=$this->stunde OR vonstunde IS NULL)))
								AND (bisdatum>'$this->datum' OR (bisdatum='$this->datum' AND (bisstunde>=$this->stunde OR bisstunde IS NULL)));";
				//echo $sql_query.'<br>';
				if (!$this->db_query($sql_query))
				{
					$this->errormsg=$sql_query.$this->db_last_error();
					return true;
				}
				$erg_zs = $this->db_result;
				$anz_zs=$this->db_num_rows($erg_zs);
				//Check
				if ($anz_zs!=0)
				{
					$row = $this->db_fetch_object($erg_zs);
					$this->errormsg="Kollision (Zeitsperre): $row->zeitsperre_id|$row->lektor|$row->zeitsperretyp_kurzbz - $row->vondatum/$row->vonstunde|$row->bisdatum/$row->bisstunde";
					return true;
				}
			}
			// Reservierungen pruefen?
			if (!$ignore_reservation)
			{
				// Datenbank abfragen  	( studiengang_kz, titel, beschreibung )
				$sql_query="SELECT reservierung_id AS id, uid AS lektor, stg_kurzbz, ort_kurzbz, semester, verband, gruppe, gruppe_kurzbz, datum, stunde
							FROM lehre.vw_reservierung
							WHERE datum='$this->datum' AND stunde=$this->stunde AND (ort_kurzbz='$this->ort_kurzbz' OR ";
				if ($this->lektor_uid!='_DummyLektor')
					$sql_query.="(uid='$this->lektor_uid' AND uid!='_DummyLektor') OR ";
				$sql_query.="(studiengang_kz=$this->studiengang_kz AND semester=$this->sem";
				if ($this->ver!=null && $this->ver!='' && $this->ver!=' ')
					$sql_query.=" AND (verband='$this->ver' OR verband IS NULL OR verband='' OR verband=' ')";
				if ($this->grp!=null && $this->grp!='' && $this->grp!=' ')
					$sql_query.=" AND (gruppe='$this->grp' OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
				if ($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
					$sql_query.=" AND (gruppe_kurzbz='$this->gruppe_kurzbz')";
				$sql_query.="))";
				//echo $sql_query.'<br>';
				if (!$this->db_query($sql_query))
				{
					$this->errormsg=$sql_query.$this->db_last_error();
					return true;
				}
				$erg_res = $this->db_result;
				$anz_res = $this->db_num_rows($erg_res);
				//Check
				if ($anz_res==0)
				{
					return false;
				}
				else
				{
					$row = $this->db_fetch_object($erg_res);
					$this->errormsg="Kollision (Reservierung): $row->id|$row->lektor|$row->ort_kurzbz|$row->stg_kurzbz-$row->semester$row->verband$row->gruppe$row->gruppe_kurzbz - $row->datum/$row->stunde";
					return true;
				}
			}
			return false;
		}
		else
		{
			$row = $this->db_fetch_object($erg_stpl);
			$this->errormsg="Kollision ($stpl_table): $row->id|$row->lektor|$row->ort_kurzbz|$row->stg_kurzbz-$row->semester$row->verband$row->gruppe$row->gruppe_kurzbz - $row->datum/$row->stunde"; //\n".$sql_query
			return true;
		}
	}
}

?>
