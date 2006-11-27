<?php

class reservierung
{
	/**
	 * @var integer primary key
	 */
	var $id;
	/**
	 * @var string ort
	 */
	var $ort_kurzbz;
	/**
	 * @var string studiengang
	 */
	var $studiengang_kz;
	/**
	 * @var string Lektor/Mitarbeiter
	 */
	var $uid;
	/**
	 * @var integer stunde-id
	 */
	var $stunde;
	/**
	 * @var integer unix-datum
	 */
	var $datum;
	/**
	 * @var string titel
	 */
	var $titel;
	/**
	 * @var string beschreibung der reservierung
	 */
	var $beschreibung;
	/**
	 * @var integer
	 */
	var $semester;
	/**
	 * @var string verband-id
	 */
	var $verband;
	/**
	 * @var string gruppe-id
	 */
	var $gruppe;
	/**
	 * @var einheit-kurzbezeichnung
	 */
	var $einheit_kurzbz;
	/**
	 * @var boolean
	 */
	var $new=true;
	var $errormsg;
	var $conn;

	function reservierung($conn,$id='')
	{
		$this->conn = $conn;
		if (strlen($id)>0) {
			$this->id=$id;
			$this->load();
		}
	}

	/**
	 * Verbindung zur Datenbank herstellen
	 * @return PostgreSQL-Connection oder NULL

	function getConnection() {
		if (!$conn = @pg_pconnect(CONN_STRING)) {
	   		$this->errormsg="Es konnte keine Verbindung zum Server ".
	   						"aufgebaut werden.";
	   		return null;
		}
		return $conn;
	} */

	function load($id='')
	{
		if (is_null($this->conn))
		{
			return false;
		}
		$sql_query="select * from tbl_reservierung where reservierung_id=".$this->id;
		if(!($erg=@pg_exec($this->conn, $sql_query))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		if($num_rows!=1) {
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);

		$this->new=false;
		$this->id=$row->reservierung_id;
		$this->ort_kurzbz=$row->ort_kurzbz;
		$this->studiengang_kz=$row->studiengang_kz;
		$this->uid=$row->uid;
		$this->stunde=$row->stunde;
		$this->datum=$this->unixDate($row->datum);
		$this->titel=$row->titel;
		$this->beschreibung=$row->beschreibung;
		$this->semester=$row->semester;
		$this->verband=$row->verband;
		$this->gruppe=$row->gruppe;
		$this->einheit_kurzbz=$row->einheit_kurzbz;
		return true;
	}

	function save()
	{
		if (is_null($this->conn))
		{
			return false;
		}
		if ($this->new)
		{
			// Kollision-Check
			$r=$this->exists($this->datum,$this->stunde,$this->ort_kurzbz);
			if ($r!==false)
			{
				$this->errormsg("Kollision mit bereits bestehender Reservierung: ".$r->titel." (".$r->uid.")");
				return false;
			}
			// Stundenplan-Kollision?
			$kollisionen=$this->collisionStundenplan($this->datum,$this->stunde,$this->ort_kurzbz);
			if ($kollisionen>0)
			{
				$this->errormsg="Kollisionen mit Stundenplan: $kollisionen";
				return false;
			}
			$qry="insert into tbl_reservierung(ort_kurzbz,studiengang_kz,uid,stunde,datum,titel,beschreibung,semester,verband,gruppe,einheit_kurzbz)".
				 "values('".$this->ort_kurzbz."',".
				 $this->studiengang_kz.",'".
				 $this->uid."',".$this->stunde.",'".
				 date("d.m.Y",$this->datum)."','".
				 $this->titel."','".$this->beschreibung."',".
				 (ctype_digit($this->semester)?$this->semester:'NULL').",".
				 (strlen($this->verband)>0?"'".$this->verband."'":'NULL').",".
				 (strlen($this->gruppe)>0?"'".$this->gruppe."'":'NULL').",".
				 (strlen($this->einheit_kurzbz)>0?"'".$this->einheit_kurzbz."'":'NULL').
				")";
		} else
		{
			$qry="update tbl_reservierung set ".
				 "ort_kurzbz='".$this->ort_kurzbz."',".
				 "studiengang_kz='".$this->studiengang_kz."',".
				 "uid='".$this->uid."',".
				 "stunde=".$this->stunde.",".
				 "datum='".date("d.m.Y",$this->datum)."',".
				 "titel='".$this->titel."',".
				 "beschreibung='".$this->beschreibung."',".
				 "semester=".(ctype_digit($this->semester)?$this->semester:'NULL').",".
				 "verband=".(strlen($this->verband)>0?"'".$this->verband."'":'NULL').",".
				 "gruppe=".(strlen($this->gruppe)>0?"'".$this->gruppe."'":'NULL').",".
				 "einheit_kurzbz=".(strlen($this->einheit_kurzbz)>0?"'".$this->einheit_kurzbz."'":'NULL').
				 "WHERE reservierung_id=".$this->reservierung_id;

		}
		$qry="set datestyle to german;".$qry;
		//echo $qry;
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		return true;
	}

	/**
	 * Überprüfen ob eine Kollision mit dem Stundenplan besteht
	 * @param integer $datum datum im unix-format
	 * @param integer $stunde id der stunde
	 * @param string $ort_kurzbz ort-ID
	 * @return integer anzahl der Kollisionen, -1 bei fehler
	 */
	function collisionStundenplan($datum,$stunde,$ort_kurzbz)
	{
		if (is_null($this->conn)) {
			return -1;
		}
		$qry="SELECT uid, lehrfach_nr, studiengang_kz, stundenplan_id, unr, datum, stunde, ort_kurzbz, semester, verband, gruppe, einheit_kurzbz, titel, anmerkung, fix, stg_kurzbz, stg_kurzbzlang, stg_bezeichnung, fachbereich_id, lehrfach, farbe, lehrform, aktiv, lektor, fixangestellt ".
			 "FROM vw_stundenplan ".
			 "WHERE datum='".date('d.m.Y',$datum)."' ".
			 	   "AND stunde=".$stunde." ".
			 	   "AND ort_kurzbz='".$ort_kurzbz."' ".
			 "order by semester,verband,gruppe";
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return -1;
		}
		$num_rows=pg_numrows($erg);
		return $num_rows;
	}

	/**
	 * Überprüfen, ob bereits eine Reservierung existiert
	 * @param integer $datum datum im unix-format
	 * @param integer $stunde id der stunde
	 * @param string $ort_kurzbz ort-ID
	 * @return reservierung reservierung die bereits existiert
	 */
	function exists($datum,$stunde,$ort_kurzbz)
	{
		if (is_null($this->conn)) {
			return false;
		}
		$qry="SELECT reservierung_id from tbl_reservierung ".
			 "WHERE datum='".date('d.m.Y',$datum)."' ".
			 	   "AND stunde=".$stunde." ".
			 	   "AND ort_kurzbz='".$ort_kurzbz."' ".
			 "ORDER BY semester,verband,gruppe";
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$row=pg_fetch_object($erg,0);
		$id=$row->reservierung_id;
		$r=new reservierung($id);
		// bereits vorhandene reservierung zurückgeben
		if ($r!==false) return $r;
		// reservierung konnte nicht geladen werden
		$this->errormsg='Reservierung konnte nicht geladen werden';
		return false;
	}



	/**
	 * reservierung löschen
	 */
	function delete()
	{
		if (is_null($conn=$this->getConnection())) {
			return false;
		}
		$sql_query="DELETE FROM tbl_reservierung WHERE reservierung_id=".$this->id;
		$result=pg_exec($this->conn, $sql_query);
		$num_rows=pg_numrows($result);
		if ($result)
		{
			print_r($result);
			return true;
		}
		$this->errormsg=pg_errormessage($this->conn);
		return false;
	}

	/**
	 * Alle mehrfach Reservierungen holen
	 * @return array(datum,stunde,ort)
	 */
	function getAllMehrfach()
	{
		if (is_null($this->conn))
		{
			return false;
		}
		$sql_query="set datestyle to german;".
				   "SELECT count(reservierung_id), datum, stunde, ort_kurzbz ".
				   "FROM tbl_reservierung GROUP BY datum, stunde, ort_kurzbz ".
				   "HAVING (count(reservierung_id)>1) ".
				   "ORDER BY datum, stunde, ort_kurzbz";
		if(!($erg=@pg_exec($this->conn, $sql_query))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$result[]=array('datum'=>$this->unixDate($row->datum),
							'stunde'=>$row->stunde,
							'ort'=>$row->ort_kurzbz);

		}
		return $result;
	}

	/**
	 * mehrfach vorkommende Reservierungen laden
	 * @param string $datum
	 * @param integer $stunde
	 * @return array
	 */
	function getMehrfach($datum,$stunde,$ort_kurzbz) {
		if (is_null($this->conn)) {
			return false;
		}
		$sql_query="set datestyle to german;".
				   "SELECT tbl_reservierung.*, tbl_ort.ort_kurzbz AS ortkurzbz, tbl_mitarbeiter.kurzbz AS lektorkurzbz ".
				   "FROM tbl_reservierung, tbl_ort, tbl_person,tbl_mitarbeiter ".
				   "WHERE datum='$datum' AND tbl_reservierung.stunde=$stunde ".
				   "AND tbl_reservierung.ort_kurzbz='$ort_kurzbz' AND ".
				   "tbl_reservierung.ort_kurzbz=tbl_ort.ort_kurzbz ".
				   "AND tbl_person.uid=tbl_mitarbeiter.uid ".
				   "and tbl_mitarbeiter.lektor=true;";
		if(!($erg=@pg_exec($this->conn, $sql_query))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new reservierung();
			$l->new=false;
			$l->id=$row->reservierung_id;
			$l->ort_kurzbz=$row->ort_kurzbz;
			$l->studiengang_kz=$row->studiengang_kz;
			$l->uid=$row->uid;
			$l->stunde=$row->stunde;
			$l->datum=$this->unixDate($row->datum);
			$l->titel=$row->titel;
			$l->beschreibung=$row->beschreibung;
			$l->semester=$row->semester;
			$l->verband=$row->verband;
			$l->gruppe=$row->gruppe;
			$l->einheit_kurzbz=$row->einheit_kurzbz;
			$result[]=$l;
		}
		return $result;
	}

	function unixDate($date)
	{
		if (strlen($date)>0)
		{
			$_d1 = explode(".", $date);
   			$d1 = $_d1[0];
   			$m1 = $_d1[1];
   			$y1 = $_d1[2];
   			$unixDate=mktime(0,0,0,$d1,$m1,$y1);
		} else
		{
			return null;
		}
		return $unixDate;
	}

}

?>