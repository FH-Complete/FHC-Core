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

class zeitsperre extends basis_db
{
	public $new;     			// boolean
	public $result = array(); 	// news Objekt

	//Tabellenspalten
	public $zeitsperre_id;		// serial
	public $zeitsperretyp_kurzbz;	// varchar(8)
	public $mitarbeiter_uid;		// varchar(16)
	public $bezeichnung;		// varchar(32)
	public $vondatum;			// date
	public $vonstunde;			// smallint
	public $bisdatum;			// date
	public $bisstunde;			// smallint
	public $erreichbarkeit_kurzbz;		// varchar(5)
	public $vertretung_uid;		// varchar(16)
	public $updateamum;			// timestamp
	public $updatevon;			// string
	public $insertamum;			// timestamp
	public $insertvon;			// string
	public $freigabeamum;
	public $freigabevon;


	/**
	 * Konstruktor
	 * @param $zeitsperre_id ID der zu ladenden Funktion
	 */
	public function __construct($zeitsperre_id=null)
	{
		parent::__construct();

		if($zeitsperre_id != null)
			$this->load($zeitsperre_id);
	}

	/**
	 * Laedt alle Zeitsperren eines Benutzers wo ende>=now() ist
	 * @param $uid
	 * @param $bisgrenze wenn true werden nur die Zeitsperren des
	 * aktuellen Geschaeftsjahres geholt (1.9.-31.8.)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getzeitsperren($uid, $bisgrenze=true)
	{
		unset($this->result);
		$this->result=array();
		$qry = "SELECT tbl_zeitsperre.*, tbl_zeitsperretyp.*, tbl_erreichbarkeit.farbe  AS erreichbarkeit_farbe, tbl_erreichbarkeit.beschreibung AS erreichbarkeit_beschreibung
				FROM (campus.tbl_zeitsperre JOIN campus.tbl_zeitsperretyp USING (zeitsperretyp_kurzbz))
					LEFT JOIN campus.tbl_erreichbarkeit USING (erreichbarkeit_kurzbz)
				WHERE mitarbeiter_uid=".$this->db_add_param($uid);

		if($bisgrenze)
		{
			$qry.=" AND (
							(date_part('month',vondatum)>=9 AND date_part('year', vondatum)>='".(date('Y')-1)."')
							OR
							(date_part('month',vondatum)<9 AND date_part('year', vondatum)>='".(date('Y'))."')
						)";
		}

		$qry.= " ORDER BY vondatum DESC";

		if($this->db_query($qry))
		{

			while($row = $this->db_fetch_object())
			{

				$obj = new zeitsperre();

				$obj->zeitsperre_id = $row->zeitsperre_id;
				$obj->zeitsperretyp_kurzbz = $row->zeitsperretyp_kurzbz;
				$obj->zeitsperretyp_beschreibung = $row->beschreibung;
				$obj->zeitsperretyp_farbe = $row->farbe;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->vondatum = $row->vondatum;
				$obj->vonstunde = $row->vonstunde;
				$obj->bisdatum = $row->bisdatum;
				$obj->bisstunde = $row->bisstunde;
				$obj->erreichbarkeit = $row->erreichbarkeit_kurzbz;
				$obj->erreichbarkeit_farbe = $row->erreichbarkeit_farbe;
				$obj->erreichbarkeit_kurzbz = $row->erreichbarkeit_kurzbz;
				$obj->erreichbarkeit_beschreibung = $row->erreichbarkeit_beschreibung;
				$obj->vertretung_uid = $row->vertretung_uid;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->freigabeamum = $row->freigabeamum;
				$obj->freigabevon = $row->freigabevon;

				$this->result[] = $obj;

			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zeitsperren';
			return false;
		}
	}

	/**
	 * Laedt eine Zeitsperre
	 * @param $zeitsperre_id ID der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($zeitsperre_id)
	{
		if(!is_numeric($zeitsperre_id))
		{
			$this->errormsg = 'zeitsperre_id muß eine gültige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperre_id=".$this->db_add_param($zeitsperre_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->zeitsperre_id = $row->zeitsperre_id;
			$this->zeitsperretyp_kurzbz = $row->zeitsperretyp_kurzbz;
			$this->mitarbeiter_uid = $row->mitarbeiter_uid;
			$this->bezeichnung = $row->bezeichnung;
			$this->vondatum = $row->vondatum;
			$this->vonstunde = $row->vonstunde;
			$this->bisdatum = $row->bisdatum;
			$this->bisstunde = $row->bisstunde;
			$this->erreichbarkeit_kurzbz = $row->erreichbarkeit_kurzbz;
			$this->vertretung_uid = $row->vertretung_uid;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->freigabeamum = $row->freigabeamum;
			$this->freigabevon = $row->freigabevon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Loescht einen Datensatz
	 * @param $zeitsperre_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($zeitsperre_id)
	{
		if(!is_numeric($zeitsperre_id))
		{
			$this->errormsg = 'Zeitsperre_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM campus.tbl_zeitsperre WHERE zeitsperre_id=".$this->db_add_param($zeitsperre_id, FHC_INTEGER);

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen';
			return false;
		}
	}

	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->bezeichnung)>32)
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 32 Zeichen sein";
			return false;
		}
		if(mb_strlen($this->zeitsperretyp_kurzbz)>8)
		{
			$this->errormsg = "Zeitsperretyp_kurzbz darf nicht laenger als 8 Zeichen sein";
			return false;
		}
		if(mb_strlen($this->mitarbeiter_uid)>32)
		{
			$this->errormsg = "Mitarbeiter_uid darf nicht laenger als 8 Zeichen sein";
			return false;
		}
		if(mb_strlen($this->erreichbarkeit_kurzbz)>8)
		{
			$this->errormsg = "Erreichbarkeit darf nicht laenger als 8 Zeichen sein";
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new = null)
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->validate())
			return false;

		if(!is_null($new))
			$this->new = $new;

		if($this->new)
		{
			//Neuen Datensatz anlegen

			$qry = 'INSERT INTO campus.tbl_zeitsperre (zeitsperretyp_kurzbz, mitarbeiter_uid, bezeichnung,'.
			       ' vondatum ,vonstunde, bisdatum, bisstunde, erreichbarkeit_kurzbz, vertretung_uid, insertamum,'.
			       ' insertvon, updateamum, updatevon, freigabeamum, freigabevon) VALUES ('.
					$this->db_add_param($this->zeitsperretyp_kurzbz).', '.
					$this->db_add_param($this->mitarbeiter_uid).', '.
					$this->db_add_param($this->bezeichnung).', '.
					$this->db_add_param($this->vondatum).', '.
					$this->db_add_param($this->vonstunde).', '.
					$this->db_add_param($this->bisdatum).', '.
					$this->db_add_param($this->bisstunde).', '.
					$this->db_add_param($this->erreichbarkeit_kurzbz).', '.
					$this->db_add_param($this->vertretung_uid).', '.
					$this->db_add_param($this->insertamum).', '.
					$this->db_add_param($this->insertvon).', '.
					$this->db_add_param($this->updateamum).', '.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->freigabeamum).','.
					$this->db_add_param($this->freigabevon).'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob zeitsperre_id eine gueltige Zahl ist
			if(!is_numeric($this->zeitsperre_id) || $this->zeitsperre_id == '')
			{
				$this->errormsg = 'Zeitsperre_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE campus.tbl_zeitsperre SET '.
				'zeitsperretyp_kurzbz='.$this->db_add_param($this->zeitsperretyp_kurzbz).', '.
				'mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'vondatum='.$this->db_add_param($this->vondatum).', '.
				'vonstunde='.$this->db_add_param($this->vonstunde).', '.
				'bisdatum='.$this->db_add_param($this->bisdatum).', '.
				'bisstunde='.$this->db_add_param($this->bisstunde).', '.
				'erreichbarkeit_kurzbz='.$this->db_add_param($this->erreichbarkeit_kurzbz).', '.
				'vertretung_uid='.$this->db_add_param($this->vertretung_uid).', '.
				'insertamum='.$this->db_add_param($this->insertamum).', '.
				'insertvon='.$this->db_add_param($this->insertvon).', '.
				'freigabeamum='.$this->db_add_param($this->freigabeamum).', '.
				'freigabevon='.$this->db_add_param($this->freigabevon).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
				'updatevon='.$this->db_add_param($this->updatevon).'  '.
				'WHERE zeitsperre_id = '.$this->db_add_param($this->zeitsperre_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert ZeitsperreTypen eines Tages
	 * @param $datum
	 * @return string wenn ok, false im Fehlerfall
	 */
	public function getTyp($datum)
	{
		$datum_obj=new datum();
		$typ='';
		foreach ($this->result as $zs)
		{
			$beginn=$datum_obj->mktime_fromdate($zs->vondatum);
			$ende=$datum_obj->mktime_fromdate($zs->bisdatum);

			if ($datum>=$beginn && ((int)($datum/60/60/24)<=(int)($ende/60/60/24) || date('Y-m-d', $datum)==date('Y-m-d', $ende)))
				$typ.=$zs->zeitsperretyp_kurzbz.'  ';
		}
		return $typ;
	}

	/**
	 * Liefert Erreichbarkeit der Zeitsperre eines Tages
	 * @return string wenn ok, false im Fehlerfall
	 */
	public function getErreichbarkeit($datum)
	{
		$datum_obj=new datum();
		$erbk='';
		foreach ($this->result as $zs)
		{
			$beginn=$datum_obj->mktime_fromdate($zs->vondatum);
			$ende=$datum_obj->mktime_fromdate($zs->bisdatum);
			if ($datum>=$beginn && ((int)($datum/60/60/24)<=(int)($ende/60/60/24) || date('Y-m-d', $datum)==date('Y-m-d', $ende)))
				$erbk.=$zs->erreichbarkeit.'  ';
		}
		return $erbk;
	}

	/**
	 * Liefert die Vertretung der Zeitsperre
	 * @return array wenn ok, false im Fehlerfall
	 */
	public function getVertretung($datum)
	{
		$datum_obj=new datum();
		$vertretung[]='';
		foreach ($this->result as $zs)
		{
			$beginn=$datum_obj->mktime_fromdate($zs->vondatum);
			$ende=$datum_obj->mktime_fromdate($zs->bisdatum);
			if ($datum>=$beginn && ((int)($datum/60/60/24)<=(int)($ende/60/60/24) || date('Y-m-d', $datum)==date('Y-m-d', $ende)))
				$vertretung[]=$zs->vertretung_uid;
		}
		return array_unique($vertretung);
	}

	/**
	 * Liefert die Zeitsperren eines Users zu einem bestimmten Datum und Stunde
	 *
	 * @param $user
	 * @param $datum
	 * @param $stunde
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getSperreByDate($user, $datum, $stunde)
	{
		$qry = "
			SELECT
				*
			FROM
				campus.tbl_zeitsperre
			WHERE
				vondatum<=".$this->db_add_param($datum)."
				AND bisdatum>=".$this->db_add_param($datum)." AND
				((vondatum=".$this->db_add_param($datum)." AND vonstunde<=".$this->db_add_param($stunde).") OR vonstunde is null OR vondatum<>".$this->db_add_param($datum).") AND
				((bisdatum=".$this->db_add_param($datum)." AND bisstunde>=".$this->db_add_param($stunde).") OR bisstunde is null OR bisdatum<>".$this->db_add_param($datum).") AND
				mitarbeiter_uid=".$this->db_add_param($user);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{

				$obj = new zeitsperre();

				$obj->zeitsperre_id = $row->zeitsperre_id;
				$obj->zeitsperretyp_kurzbz = $row->zeitsperretyp_kurzbz;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->vondatum = $row->vondatum;
				$obj->vonstunde = $row->vonstunde;
				$obj->bisdatum = $row->bisdatum;
				$obj->bisstunde = $row->bisstunde;
				$obj->erreichbarkeit_kurzbz = $row->erreichbarkeit_kurzbz;
				$obj->vertretung_uid = $row->vertretung_uid;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->freigabeamum = $row->freigabeamum;
				$obj->freigabevon = $row->freigabevon;

				$this->result[] = $obj;

			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zeitsperren';
			return false;
		}
	}

	/**
	 * Prueft ob innerhalb des angegebenen Zeitraums bereits ein Urlaub eingetragen ist
	 *
	 * @param $uid
	 * @param $von
	 * @param $bis
	 * @param $id Eintrag mit dieser ID wird nicht beruecksichtigt (zB bei Editieren von Eintraegen)
	 */
	function UrlaubEingetragen($uid, $von, $bis, $id=null)
	{

		$qry = "SELECT
					1
				FROM
					campus.tbl_zeitsperre
				WHERE
					zeitsperretyp_kurzbz='Urlaub'
					AND mitarbeiter_uid=".$this->db_add_param($uid)."
					AND
					(
						(vondatum BETWEEN ".$this->db_add_param($von)." AND ".$this->db_add_param($bis).")
						OR
						(bisdatum BETWEEN ".$this->db_add_param($von)." AND ".$this->db_add_param($bis).")
						OR
						(vondatum<=".$this->db_add_param($von)." AND bisdatum>=".$this->db_add_param($bis).")
					)";
		if(!is_null($id))
			$qry.=" AND zeitsperre_id<>".$this->db_add_param($id);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	 /**
	 * Liefert die Zeitsperren eines Users für die Zeitaufzeichnung
	 *
	 * @param $uid
	 * @param $anz_tage
	 * @return array 'datum'->'zeitsperre_kurzbz'
	 */
	public function getZeitsperrenForZeitaufzeichnung($uid, $anz_tage=50)
	{
		unset($this->result);
		$this->result=array();

		$qry = "select datum::date, freigabevon, zeitsperretyp_kurzbz
					from  (SELECT generate_series(vondatum::timestamp, bisdatum::timestamp, '1 day') as datum, freigabevon, mitarbeiter_uid, zeitsperretyp_kurzbz FROM campus.tbl_zeitsperre where vonstunde is null and bisstunde is null) a
					where a.mitarbeiter_uid = ".$this->db_add_param($uid)." and datum>(now() - interval '".$anz_tage." Days') and zeitsperretyp_kurzbz in ('Krank','Urlaub', 'ZA', 'DienstV','PflegeU', 'DienstF')";




		if($this->db_query($qry))
		{

			while($row = $this->db_fetch_object())
			{

				if ($row->zeitsperretyp_kurzbz == 'Urlaub' && $row->freigabevon == NULL)
					$result[$row->datum] = "Urlaubsantrag (Freigabe fehlt)";
				else
					$result[$row->datum] = $row->zeitsperretyp_kurzbz;

				$this->result = $result;

			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zeitsperren';
			return false;
		}
	}

	public function getVonDatum()
	{
		return $this->vondatum;
	}

	public function getBisDatum()
	{
		return $this->bisdatum;
	}

}
?>
