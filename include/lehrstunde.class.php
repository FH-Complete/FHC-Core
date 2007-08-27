<?php
/*
	$Header: /include/lehrstunde.class.php,v 1.2 2004/10/16 17:05:38 pam Exp $
	$Log: lehrstunde.class.php,v $
	Revision 1.2 2004/10/16 17:05:38 pam
	Anpassung an neue DB-Struktur.
*/


/****************************************************************************
 * @class 			Lehrstunde
 * @author	 		Christian Paminger
 * @date	 		2004/8/21
 * @version			$Revision: 1.2 $
 * Update: 			21.10.2004 von Christian Paminger
 * @brief  			Beschreibung einer Unterrichts-Stunde der Tabelle tbl_stundenplan
 * Abhaengig:	 	von ?
 *****************************************************************************/

// include_once('mitarbeiter.class.php');
require_once('studiensemester.class.php');


class lehrstunde
{
	var $conn;			// @brief Connection zur Datenbank

	var $stundenplan_id;// @brief id in der Datenbank
	var $lehreinheit_id;// @brief id der Lehreinheit in der DB
	var $unr;			// @brief Unterrichtsnummer
	var $lektor_uid;	// @brief UID des Lektors
	var $lektor_kurzbz; // @brief Kurzbezeichnung des Lektors
	var $datum;			// @brief Datum
	var $stunde;		// @brief Unterrichts-Stunde des Tages
	var $ort_kurzbz;	// @brief Ort in dem der Unterricht stattfindet
	var $lehrfach_nr;	// @brief Nummer des Lehrfachs
	var $lehrfach;		// @brief Name des Lehrfachs
	var $lehrfach_bez;	// @brief Voller Name des Lehrfachs
	var $lehrform;		// @brief Lehrform des Lehrfachs (Vorlesung, ...)
	var $studiengang_kz;// @brief Kennzahl des Studiengangs
	var $studiengang;	// @brief Kurzbezeichnung des Studiengangs
	var $sem;			// @brief Semester
	var $ver;			// @brief Verband
	var $grp;			// @brief Gruppe
	var $gruppe_kurzbz;	// @brief Kurzbezeichnung der Gruppe
	var $titel;			// @brief Titel der Unterrichtsstunde
	var $anmerkung;		// @brief Anmerkungen zur Unterrichtsstunde
	var $fix;			// @brief true wenn diese Stunde nicht mehr verschoben wird
	var $updateamum;	// @brief letztes Update
	var $updatevon;		// @brief Update von wem?
	var $errormsg;		// @brief String fuer die Fehlermeldung
	var $new;			// @brief true wenns ein neuer Datensatz ist
	var $reservierung;	// @brief true wenns eine Reservierung ist

	var $lehrstunden=array();	// @brief Objekt der eigenen Klasse
	var $anzahl;		// @brief Gesamte Anzahl der Stunden im Array
	var $ss=null;			// @brief Studiensemester


	/** Konstruktor
	 *
	 *
	 */
	function lehrstunde($conn)
	{
		// Startvariablen setzen
		$this->conn=$conn;
		$this->new=TRUE;
	}


	/**
	 *	Einen Datensatz laden
	 *
	 */
	function load($stundenplan_id,$stpl_table='stundenplandev')
	{
		///////////////////////////////////////////////////////////////////////
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_view='lehre.'.VIEW_BEGIN.$stpl_table;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		$sql_query="SELECT * FROM $stpl_view WHERE $stpl_id=$stundenplan_id;";
		//echo $sql_query.'<br>';


		//Datenbankabfrage
		if (! $stpl_tbl=pg_query($this->conn, $sql_query))
		{
			$this->errormsg=$sql_query.pg_last_error($this->conn);
			//echo $this->errormsg;
			return false;
		}
		$this->anzahl=pg_numrows($stpl_tbl);
		//Daten uebernehmen
		if ($this->anzahl!=1)
		{
			$this->errormsg='Keinen Datensatz gefunden';
			return false;
		}
		else
		{
			$row=pg_fetch_object ($stpl_tbl);
			$this->stundenplan_id=$row->{$stpl_id};
			$this->unr=$row->unr;
			$this->lektor_uid=$row->uid;
			$this->lektor_kurzbz=$row->lektor;
			$this->datum=$row->datum;
			$this->stunde=$row->stunde;
			$this->ort_kurzbz=$row->ort_kurzbz;
			$this->lehrfach=$row->lehrfach;
			$this->lehrfach_bez=$row->lehrfach_bez;
			$this->lehrfach_nr=$row->lehrfach_nr;
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
	function save($uid, $stpl_table='stundenplandev')
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
			if (! pg_query($this->conn, $sql_query))
			{
				$this->errormsg=$sql_query.pg_last_error($this->conn);
				//echo $this->errormsg;
				return false;
			}
			//$this->errormsg.=$sql_query;
		}

		return true;
	}

	/**
	 *	Datensatz aus DB entfernen
	 * @param id ID des Datensatzes in der Tabelle
	 * @param stpl_table Name der Tabelle
	 *
	 */
	function delete($id, $stpl_table='stundenplandev')
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;
		// Delete SQL vorbereiten
		$sql_query='DELETE FROM '.$stpl_table;
		$sql_query.=" WHERE $stpl_id=$id";
		//echo $sql_query."<br>";

		//Datenbankrequest
		if (! pg_query($this->conn, $sql_query))
		{
			$this->errormsg=$sql_query.pg_last_error($this->conn);
			//echo $this->errormsg;
			return false;
		}
		else
			return true;
	}

	/**
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
	function load_lehrstunden($type, $datum_von, $datum_bis, $uid, $ort_kurzbz=NULL, $studiengang_kz=NULL, $sem=NULL, $ver=NULL, $grp=NULL, $gruppe_kurzbz=NULL, $stpl_view='stundenplan', $idList=null)
	{
		///////////////////////////////////////////////////////////////////////
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_view.TABLE_ID;
		$stpl_view='lehre.'.VIEW_BEGIN.$stpl_view;
		$num_rows_einheit=0;

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
			$sql_query="SELECT studiengang_kz, semester, verband, gruppe FROM public.tbl_student WHERE student_uid='$uid'";
			//echo $sql_query;
			if (! $result=pg_query($this->conn, $sql_query) )
			{
				$this->errormsg=pg_last_error($this->conn);
				return -2;
			}
			$num_rows=pg_num_rows($result);
			if ($num_rows>0)
				$row=pg_fetch_object($result);
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
			$sql_query="SELECT gruppe_kurzbz FROM public.tbl_benutzergruppe WHERE uid='$uid' AND studiensemester_kurzbz='$this->ss'";
			//echo $sql_query;
			if (! $result_einheit=pg_query($this->conn, $sql_query) )
			{
				$this->errormsg=pg_last_error($this->conn);
				return false;
			}
			else
				$num_rows_einheit=pg_num_rows($result_einheit);
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
				if ($sem!='0' && $sem!=null && $sem!=0  && $sem!='')
				{
					$sql_query.=" AND (semester=$sem OR semester IS NULL";
					if ($type=='student')
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
				for ($i=0;$i<$num_rows_einheit;$i++)
				{
					$row=pg_fetch_object($result_einheit,$i);
					$sql_query.=" OR gruppe_kurzbz='$row->gruppe_kurzbz'";
				}
				$sql_query.=')';
			}
			$sql_query.=' ORDER BY  datum, stunde, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, uid';
			$sql_query_stdplan.=$sql_query;
			//echo '<label>'.htmlspecialchars($sql_query_stdplan).'</label>';
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
		if (! $stpl_tbl=pg_query($this->conn, $sql_query_stdplan))
		{
			$this->errormsg=pg_last_error($this->conn);
			//echo $this->errormsg;
			return -2;
		}
		$num_rows=pg_numrows($stpl_tbl);
		$this->anzahl=$num_rows;
		//Daten uebernehmen
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($stpl_tbl, $i);
			$stunde=new lehrstunde($this->conn);
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
			if (! $stpl_tbl=pg_query($this->conn, $sql_query_reservierung))
			{
				$this->errormsg=pg_last_error($this->conn);
				return -2;
			}
			$num_rows=pg_numrows($stpl_tbl);
			$this->anzahl+=$num_rows;

			//Daten uebernehmen
			for ($i=0;$i<$num_rows;$i++)
			{
				$row=pg_fetch_object ($stpl_tbl, $i);
				$stunde=new lehrstunde($this->conn);
				$stunde->reservierung=true;
				$stunde->stundenplan_id=$row->reservierung_id;
				$stunde->unr=0;
				$stunde->lektor_uid=$row->uid;
				$stunde->lektor_kurzbz=$row->uid;
				$stunde->datum=$row->datum;
				$stunde->stunde=$row->stunde;
				$stunde->ort_kurzbz=$row->ort_kurzbz;
				//$stunde->lehrfach_nr=$row->lehrfach_nr;
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
				//var_dump($stunde);
			}
		}
		//echo $this->anzahl;
		return $this->anzahl;
	}

	/*************************************************************************
	 * Prueft die geladene Lehrveranstaltung auf Kollisionen im Stundenplan.
	 * Rueckgabewert 'false' und die Fehlermeldung steht in '$this->errormsg'.
	 * @param string	datum	gewuenschtes Datum YYYY-MM-TT
	 * @param integer	stunde	gewuenschte Stunde
	 * @param string	ort		gewuenschter Ort
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @return boolean true=ok, false=fehler
	 *************************************************************************/
	function kollision($stpl_table='stundenplandev')
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.VIEW_BEGIN.$stpl_table;

		// Datenbank abfragen
		$sql_query="SELECT $stpl_id AS id, lektor, stg_kurzbz, ort_kurzbz, semester, verband, gruppe, gruppe_kurzbz, datum, stunde FROM $stpl_table
				WHERE datum='$this->datum' AND stunde=$this->stunde AND (ort_kurzbz='$this->ort_kurzbz' OR ";
		if ($this->lektor_uid!='_DummyLektor')
			$sql_query.="(uid='$this->lektor_uid') AND uid!='_DummyLektor' OR ";
		$sql_query.="(studiengang_kz=$this->studiengang_kz AND semester=$this->sem";
		if ($this->ver!=null && $this->ver!='' && $this->ver!=' ')
			$sql_query.=" AND (verband='$this->ver' OR verband IS NULL OR verband='' OR verband=' ')";
		if ($this->grp!=null && $this->grp!='' && $this->grp!=' ')
			$sql_query.=" AND (gruppe='$this->grp' OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
		if ($this->gruppe_kurzbz!=null && $this->gruppe_kurzbz!='' && $this->gruppe_kurzbz!=' ')
			$sql_query.=" AND (gruppe_kurzbz='$this->gruppe_kurzbz')";
		$sql_query.=")) AND unr!=$this->unr";

		//echo $sql_query.'<br>';
		if (! $erg_stpl=pg_query($this->conn, $sql_query))
		{
			$this->errormsg=$sql_query.pg_last_error($this->conn);
			return true;
		}
		$anz=pg_numrows($erg_stpl);
		//Check
		if ($anz==0)
			return false;
		else
		{
			$row=pg_fetch_object($erg_stpl);
			$this->errormsg="Kollision ($stpl_table): $row->id|$row->lektor|$row->ort_kurzbz|$row->stg_kurzbz-$row->semester$row->verband$row->gruppe$row->gruppe_kurzbz - $row->datum/$row->stunde";
			return true;
		}
	}
}

?>