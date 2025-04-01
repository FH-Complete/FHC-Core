<?php

require_once(dirname(__FILE__) . '/basis_db.class.php');
require_once(dirname(__FILE__) . '/functions.inc.php');

class lehrveranstaltung_faktor extends basis_db
{
	public $lehrveranstaltung_faktor_id; // serial
	public $lehrveranstaltung_id; // integer
	public $faktor;   // numeric
	public $lehrform_kurzbz;   // varchar(8)
	public $studiensemester_kurzbz_von;	  // varchar(16)
	public $studiensemester_kurzbz_bis;	  // varchar(16)
	public $insertamum;	// timestamp
	public $insertvon;	// varchar(32)
	public $updateamum;	// timestamp
	public $updatevon;	// varchar(32)
	public $lv_faktoren = array(); //  lehrveranstaltung Objekt


	/**
	 * Konstruktor
	 * @param $lehrveranstaltung_faktor_id
	 */
	public function __construct($lehrveranstaltung_faktor_id = null)
	{
		parent::__construct();

		if (!is_null($lehrveranstaltung_faktor_id))
			$this->load($lehrveranstaltung_faktor_id);
	}


	public function load($lehrveranstaltung_faktor_id)
	{
		if (!is_numeric($lehrveranstaltung_faktor_id))
		{
			$this->errormsg = 'Lehrveranstaltung_faktor_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung_faktor 
         		WHERE lehrveranstaltung_faktor_id=".$this->db_add_param($lehrveranstaltung_faktor_id, FHC_INTEGER);

		if (!$this->db_query($qry)) {
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		if ($row = $this->db_fetch_object())
		{
			$this->lehrveranstaltung_faktor_id = $row->lehrveranstaltung_faktor_id;
			$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$this->faktor = $row->faktor;
			$this->studiensemester_kurzbz_von = $row->studiensemester_kurzbz_von;
			$this->studiensemester_kurzbz_bis = $row->studiensemester_kurzbz_bis;
		}

		return true;
	}

	public function loadByLV($lv_id)
	{

		if (!is_numeric($lv_id))
		{
			$this->errormsg = 'Lehrveranstaltung_faktor_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * 
				FROM lehre.tbl_lehrveranstaltung_faktor 
				LEFT JOIN public.tbl_studiensemester vonstsem
					ON tbl_lehrveranstaltung_faktor.studiensemester_kurzbz_von = vonstsem.studiensemester_kurzbz
				LEFT JOIN public.tbl_studiensemester bisstem
					ON tbl_lehrveranstaltung_faktor.studiensemester_kurzbz_bis = bisstem.studiensemester_kurzbz
				WHERE lehrveranstaltung_id = ".$this->db_add_param($lv_id, FHC_INTEGER);


		if (!$result = $this->db_query($qry)) {
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object($result))
		{
			$lv_faktor_objekt = new lehrveranstaltung_faktor();

			$lv_faktor_objekt->lehrveranstaltung_faktor_id = $row->lehrveranstaltung_faktor_id;
			$lv_faktor_objekt->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_faktor_objekt->faktor = $row->faktor;
			$lv_faktor_objekt->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_faktor_objekt->studiensemester_kurzbz_von = $row->studiensemester_kurzbz_von;
			$lv_faktor_objekt->studiensemester_kurzbz_bis = $row->studiensemester_kurzbz_bis;
			$this->lv_faktoren[] = $lv_faktor_objekt;
		}

		return true;
	}
	public function checkIfExists($lv_id, $von = null, $bis = null, $id = null, $lehrform_kurzbz = null)
	{

		if (!is_numeric($lv_id))
		{
			$this->errormsg = 'Lehrveranstaltung_faktor_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * 
				FROM lehre.tbl_lehrveranstaltung_faktor 
				LEFT JOIN public.tbl_studiensemester vonstsem
					ON tbl_lehrveranstaltung_faktor.studiensemester_kurzbz_von = vonstsem.studiensemester_kurzbz
				LEFT JOIN public.tbl_studiensemester bisstem
					ON tbl_lehrveranstaltung_faktor.studiensemester_kurzbz_bis = bisstem.studiensemester_kurzbz
				WHERE lehrveranstaltung_id = ".$this->db_add_param($lv_id, FHC_INTEGER);

		if(!empty($von))
		{
			$qry .= "
			AND (bisstem.ende >= (
				SELECT start
				FROM public.tbl_studiensemester
				WHERE studiensemester_kurzbz = " . $this->db_add_param($von, FHC_STRING) . "
					)
				OR bisstem.ende IS NULL
			)";
		}

		if(!empty($bis) && $bis !== "")
		{
			$qry .= "
			AND 
				(vonstsem.start <= (
					SELECT ende
					FROM public.tbl_studiensemester
					WHERE studiensemester_kurzbz = " . $this->db_add_param($bis, FHC_STRING) . "
				))
			";
		}

		if (!empty($id) && $id !== "")
		{
			$qry .= "
			AND
				lehrveranstaltung_faktor_id != ". $this->db_add_param($id, FHC_INTEGER);
		}

		if (!empty($lehrform_kurzbz) && $lehrform_kurzbz !== "")
		{
			$qry .= "
			AND
				lehrform_kurzbz = ". $this->db_add_param($lehrform_kurzbz);
		}
		else
		{
			$qry .= "
				AND lehrform_kurzbz IS NULL
			";
		}

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
				return true;
			else
				return false;
		}
	}

	public function save($new = false)
	{
		if ($new)
		{
			$qry = 'INSERT INTO lehre.tbl_lehrveranstaltung_faktor (lehrveranstaltung_id, faktor, studiensemester_kurzbz_von, studiensemester_kurzbz_bis, lehrform_kurzbz, insertvon)
				VALUES ('. $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER) . ', '.
				$this->db_add_param($this->faktor, FHC_INTEGER) . ', '.
				$this->db_add_param($this->studiensemester_kurzbz_von) . ', '.
				$this->db_add_param($this->studiensemester_kurzbz_bis) . ', '.
				$this->db_add_param($this->lehrform_kurzbz) . ', '.
				$this->db_add_param($this->insertvon) . ');';
		}
		else
		{
			$qry = "UPDATE lehre.tbl_lehrveranstaltung_faktor
				SET faktor = ". $this->db_add_param($this->faktor) ." ,
					studiensemester_kurzbz_von = ". $this->db_add_param($this->studiensemester_kurzbz_von) .",
					studiensemester_kurzbz_bis = ". $this->db_add_param($this->studiensemester_kurzbz_bis) .",
					lehrform_kurzbz = ". $this->db_add_param($this->lehrform_kurzbz) .",
					updatevon = ". $this->db_add_param($this->updatevon) .",
					updateamum = ". $this->db_add_param($this->updateamum) ."
				WHERE lehrveranstaltung_faktor_id = ". $this->db_add_param($this->lehrveranstaltung_faktor_id, FHC_INTEGER);
		}


		if ($this->db_query($qry))
		{
			if ($new)
			{
				$qry_id = "SELECT currval('lehre.lehrveranstaltung_faktor_id_seq') as id;";
				if($this->db_query($qry_id))
				{
					if($row = $this->db_fetch_object())
					{
						$this->db_query('COMMIT');
						return [
							'id' => $row->id,
							'lv_id' => $this->lehrveranstaltung_id,
							'faktor' => $this->faktor,
							'lehrform_kurzbz' => $this->lehrform_kurzbz,
							'von' => $this->studiensemester_kurzbz_von,
							'bis' => $this->studiensemester_kurzbz_bis
						];
					}
					else
					{
						$this->db_query('ROLLBACK');
						return [
							'status' => 'error',
							'message' => 'Fehler beim Einfügen in die Datenbank:'
						];
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					return [
						'status' => 'error',
						'message' => 'Fehler beim Einfügen in die Datenbank:'
					];
				}
			}
			else
				return true;
		}
		else
		{
			return [
				'status' => 'error',
				'message' => 'Fehler beim Einfügen in die Datenbank:'
			];
		}
	}

	public function getAkt($lv_id)
	{
		if (!is_numeric($lv_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * 
				FROM lehre.tbl_lehrveranstaltung_faktor 
				LEFT JOIN public.tbl_studiensemester vonstsem
					ON tbl_lehrveranstaltung_faktor.studiensemester_kurzbz_von = vonstsem.studiensemester_kurzbz
				LEFT JOIN public.tbl_studiensemester bisstem
					ON tbl_lehrveranstaltung_faktor.studiensemester_kurzbz_bis = bisstem.studiensemester_kurzbz
				WHERE lehrveranstaltung_id = ".$this->db_add_param($lv_id, FHC_INTEGER) . "
					AND (vonstsem.start <= now() OR vonstsem.start IS NULL)
					AND (bisstem.ende >= now() OR bisstem.ende IS NULL)
					AND lehrform_kurzbz IS NULL
				ORDER BY vonstsem.start DESC LIMIT 1
				";


		if (!$this->db_query($qry)) {
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		if ($row = $this->db_fetch_object())
		{
			$this->lehrveranstaltung_faktor_id = $row->lehrveranstaltung_faktor_id;
			$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$this->faktor = $row->faktor;
			$this->studiensemester_kurzbz_von = $row->studiensemester_kurzbz_von;
			$this->studiensemester_kurzbz_bis = $row->studiensemester_kurzbz_bis;
		}

		return true;
	}


	public function deleteFaktor($id)
	{
		$qry = "DELETE FROM lehre.tbl_lehrveranstaltung_faktor
				WHERE lehrveranstaltung_faktor_id = ". $this->db_add_param($id, FHC_INTEGER);

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			return [
				'status' => 'error',
				'message' => 'Fehler beim Löschen aus der Datenbank:'
			];
		}
	}
}
?>
