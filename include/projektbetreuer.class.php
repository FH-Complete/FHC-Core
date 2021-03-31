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
/**
 * Klasse projektbetreuer
 * @create 08-02-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projektbetreuer extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $person_id;			// integer
	public $projektarbeit_id;	// integer
	public $note;				// integer
	public $betreuerart_kurzbz;// varchar
	public $faktor;				// numeric(3,2)
	public $name;				// string
	public $punkte;				// numeric(6,2)
	public $stunden;			// numeric(8,4)
	public $stundensatz;		// numeric(6,2)
	public $ext_id;				// integer
	public $insertamum;			// timestamp
	public $insertvon;			// bigint
	public $updateamum;			// timestamp
	public $updatevon;			// bigint
	public $vertrag_id;			// bigint

	public $person_id_old;

	/**
	 * Konstruktor
	 * @param $person_id, $projektarbeit ID des Projektbetreuers, der geladen werden soll (Default=null)
	 */
	public function __construct($person_id=null, $projektarbeit_id=null)
	{
		parent::__construct();

		if($projektarbeit_id != null && $person_id!=null)
			$this->load($person_id, $projektarbeit_id);
	}

	/**
	 * Laedt die Funktion mit der ID $person_id, $projektarbeit_id
	 * @param  $person_id ID der zu ladenden Funktion
	 * @param  $projektarbeit_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($person_id, $projektarbeit_id, $betreuerart_kurzbz)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT
					*
				FROM
					lehre.tbl_projektbetreuer
				WHERE
					person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
					AND projektarbeit_id=".$this->db_add_param($projektarbeit_id, FHC_INTEGER)."
					AND betreuerart_kurzbz=".$this->db_add_param($betreuerart_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->person_id = $row->person_id;
				$this->projektarbeit_id = $row->projektarbeit_id;
				$this->note = $row->note;
				$this->betreuerart_kurzbz = $row->betreuerart_kurzbz;
				$this->faktor = $row->faktor;
				$this->name = $row->name;
				$this->punkte = $row->punkte;
				$this->stunden = $row->stunden;
				$this->stundensatz = $row->stundensatz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->vertrag_id = $row->vertrag_id;
				$this->new=false;
				return true;
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->betreuerart_kurzbz=='')
		{
			$this->errormsg = 'Betreuerart muss eingegeben werden';
			return false;
		}
		if(mb_strlen($this->betreuerart_kurzbz)>16)
		{
			$this->errormsg = 'betreuerart darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->name)>32)
		{
			$this->errormsg = 'Name darf nicht länger als 32 Zeichen sein';
			return false;
		}

		if($this->note!='' && !is_numeric($this->note))
		{
			$this->errormsg = 'Note muss ein numerischer Wert sein';
			return false;
		}
		if($this->punkte!='' && !is_numeric($this->punkte))
		{
			$this->errormsg = 'Punkte muss ein numerischer Wert sein';
		}
		if($this->faktor!='' && !is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muss ein numerischer Wert sein';
			return false;
		}
		if($this->stundensatz!='' && !is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muss ein numerischer Wert sein';
			return false;
		}

		//Pruefen ob projektarbeit_id eine gueltige Zahl ist
		if(!is_numeric($this->projektarbeit_id))
		{
			$this->errormsg = 'projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}

		//Pruefen ob person_id eine gueltige Zahl ist
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new = $this->new;

		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, betreuerart_kurzbz, faktor, name,
				 punkte, stunden, stundensatz, insertamum, insertvon, updateamum, updatevon, vertrag_id) VALUES('.
			     $this->db_add_param($this->person_id).', '.
			     $this->db_add_param($this->projektarbeit_id).', '.
			     $this->db_add_param($this->note).', '.
			     $this->db_add_param($this->betreuerart_kurzbz).', '.
			     $this->db_add_param($this->faktor).', '.
			     $this->db_add_param($this->name).', '.
			     $this->db_add_param($this->punkte).', '.
			     $this->db_add_param($this->stunden).', '.
			     $this->db_add_param($this->stundensatz).', now(), '.
			     $this->db_add_param($this->insertvon).', now(), '.
			     $this->db_add_param($this->updatevon).', '.
				 $this->db_add_param($this->vertrag_id).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			if($this->person_id_old=='')
				$this->person_id_old = $this->person_id;

			if(!isset($this->betreuerart_kurzbz_old) || $this->betreuerart_kurzbz_old=='')
				$this->betreuerart_kurzbz_old = $this->betreuerart_kurzbz;

			$qry='UPDATE lehre.tbl_projektbetreuer SET '.
				'person_id='.$this->db_add_param($this->person_id).', '.
				'note='.$this->db_add_param($this->note).', '.
				'betreuerart_kurzbz='.$this->db_add_param($this->betreuerart_kurzbz).', '.
				'faktor='.$this->db_add_param($this->faktor).', '.
				'name='.$this->db_add_param($this->name).', '.
				'punkte='.$this->db_add_param($this->punkte).', '.
				'stunden='.$this->db_add_param($this->stunden).', '.
				'stundensatz='.$this->db_add_param($this->stundensatz).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
			    'updatevon='.$this->db_add_param($this->updatevon).', '.
				'vertrag_id='.$this->db_add_param($this->vertrag_id).' '.
				"WHERE projektarbeit_id=".$this->db_add_param($this->projektarbeit_id, FHC_INTEGER,false).
				" AND person_id=".$this->db_add_param($this->person_id_old, FHC_INTEGER,false).
				" AND betreuerart_kurzbz=".$this->db_add_param($this->betreuerart_kurzbz_old).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $person_id ID die geloescht werden soll
	 * @param $projektarbeit_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($person_id, $projektarbeit_id, $betreuerart_kurzbz)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM lehre.tbl_projektbetreuer WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." AND projektarbeit_id=".$this->db_add_param($projektarbeit_id, FHC_INTEGER)." AND betreuerart_kurzbz=".$this->db_add_param($betreuerart_kurzbz).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert alle Betreuer zu einer Projektarbeit
	 * @param projektarbeit_id
	 */
	public function getProjektbetreuer($projektarbeit_id)
	{
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id=".$this->db_add_param($projektarbeit_id, FHC_INTEGER)." ORDER BY name";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektbetreuer();

				$obj->person_id = $row->person_id;
				$obj->projektarbeit_id = $row->projektarbeit_id;
				$obj->note = $row->note;
				$obj->betreuerart_kurzbz = $row->betreuerart_kurzbz;
				$obj->faktor = $row->faktor;
				$obj->name = $row->name;
				$obj->punkte = $row->punkte;
				$obj->stunden = $row->stunden;
				$obj->stundensatz = $row->stundensatz;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->vertrag_id = $row->vertrag_id;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	/**
	 * Retrieves all projektarbeiten by person (only with stundensatz > 0)
	 * @param $person_id
	 * @return boolean If succeeded true and result-array with objects of each projektarbeit of the person.
	 */
	public function getAllProjects($person_id)
	{
		if (isset($person_id) && is_numeric($person_id))
		{
			$qry = '
				SELECT
					*
				FROM
					lehre.tbl_projektbetreuer
				WHERE
					(stundensatz IS NOT NULL) AND (stundensatz > 0)
				AND 
					person_id =' . $this->db_add_param($person_id, FHC_INTEGER);

			if ($this->db_query($qry))
			{
				while ($row = $this->db_fetch_object())
				{
					$obj = new projektbetreuer();

					$obj->person_id = $row->person_id;
					$obj->projektarbeit_id = $row->projektarbeit_id;
					$obj->note = $row->note;
					$obj->betreuerart_kurzbz = $row->betreuerart_kurzbz;
					$obj->faktor = $row->faktor;
					$obj->name = $row->name;
					$obj->punkte = $row->punkte;
					$obj->stunden = $row->stunden;
					$obj->stundensatz = $row->stundensatz;
					$obj->updateamum = $row->updateamum;
					$obj->updatevon = $row->updatevon;
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					$obj->ext_id = $row->ext_id;
					$obj->vertrag_id = $row->vertrag_id;

					$this->result[] = $obj;
				}
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler bei einer Abfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Person_id fehlt oder nicht numerisch.';
			return false;
		}
	}

	/**
	 * Holt Zweitbegutachter einer Projektarbeit mit Mail.
	 * @param $erstbegutachter_person_id int person_id des Erstbegutachters
	 * @param $projektarbeit_id int
	 * @param $student_uid string uid des Studenten der Arbeit abgibt
	 * @return object | bool
	 */
	public function getZweitbegutachterWithToken($erstbegutachter_person_id, $projektarbeit_id, $student_uid)
	{
		$qry_betr="SELECT betr.person_id, betr.projektarbeit_id, pers.anrede, betr.zugangstoken, betr.zugangstoken_gueltigbis, tbl_benutzer.uid, kontakt,
       			trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as voller_name,
       			CASE WHEN tbl_benutzer.uid IS NULL THEN kontakt ELSE tbl_benutzer.uid || '@".DOMAIN."' END AS email, abg.abgabedatum
				FROM lehre.tbl_projektbetreuer betr
				JOIN lehre.tbl_projektarbeit parb ON betr.projektarbeit_id = parb.projektarbeit_id 
				JOIN public.tbl_person pers ON betr.person_id = pers.person_id
				LEFT JOIN public.tbl_kontakt ON pers.person_id = tbl_kontakt.person_id AND kontakttyp = 'email' AND zustellung = true
				LEFT JOIN public.tbl_benutzer ON pers.person_id = tbl_benutzer.person_id
				LEFT JOIN campus.tbl_paabgabe abg ON betr.projektarbeit_id = abg.projektarbeit_id AND abg.paabgabetyp_kurzbz = 'end'
				WHERE betr.betreuerart_kurzbz = 'Zweitbegutachter'
				AND betr.projektarbeit_id = ".$this->db_add_param($projektarbeit_id, FHC_INTEGER)."
				AND parb.student_uid = ".$this->db_add_param($student_uid)."
				AND EXISTS (
					SELECT 1 FROM lehre.tbl_projektbetreuer
					WHERE person_id = ".$this->db_add_param($erstbegutachter_person_id, FHC_INTEGER)."
					AND betreuerart_kurzbz = 'Erstbegutachter'
					AND projektarbeit_id = betr.projektarbeit_id
				)
				AND (tbl_benutzer.aktiv OR tbl_benutzer.aktiv IS NULL)
				ORDER BY betr.insertamum DESC
				LIMIT 1";

		if ($betr=$this->db_query($qry_betr))
		{
			$row_betr = $this->db_fetch_object($betr);

			if ($row_betr)
				return $row_betr;
			else
				return false;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Generiert Token für externen Zweitbetreuer wenn noch kein Token vorhanden ist.
	 * @param int $zweitbegutachter_person_id
	 * @param int $projektarbeit_id
	 * @return bool true wenn erfolgreich (generiert oder bereits vorhanden), false wenn fehlgeschlagen
	 */
	public function generateZweitbegutachterToken($zweitbegutachter_person_id, $projektarbeit_id)
	{
		// if externer Betreuer and no valid token, generate
		$betreuerUidQry = "SELECT uid, zugangstoken, zugangstoken_gueltigbis, tbl_projektbetreuer.person_id
							FROM lehre.tbl_projektbetreuer
							JOIN public.tbl_person USING(person_id)
							LEFT JOIN public.tbl_benutzer USING(person_id)
							WHERE projektarbeit_id = ".$this->db_add_param($projektarbeit_id, FHC_INTEGER)."
							AND tbl_projektbetreuer.person_id = ".$this->db_add_param($zweitbegutachter_person_id, FHC_INTEGER)."
							AND betreuerart_kurzbz = 'Zweitbegutachter'
							LIMIT 1";

		if ($betreueruidres = $this->db_query($betreuerUidQry))
		{
			$row_betr = $this->db_fetch_object($betreueruidres);

			if ($row_betr)
			{
				if (!isset($row_betr->uid)
					&& (!isset($row_betr->zugangstoken) || $row_betr->zugangstoken_gueltigbis < date('Y-m-d')))
				{
					$tokenanzahl = 1;

					while ($tokenanzahl > 0)
					{
						//generate random string
						$token = generateUniqueToken(16);

						if (!$token)
							return false;

						$qry_tokencheck = "SELECT count(*) AS anzahl
							FROM lehre.tbl_projektbetreuer
							WHERE zugangstoken = " . $this->db_add_param($token);

						if ($tokencount = $this->db_query($qry_tokencheck))
						{
							$row_tokencount = $this->db_fetch_object($tokencount);

							$tokenanzahl = (int)$row_tokencount->anzahl;
						}
						else
						{
							return false;
						}
					}

					$qry_upd = "UPDATE lehre.tbl_projektbetreuer SET
						zugangstoken = " . $this->db_add_param($token) . ",
						zugangstoken_gueltigbis = CURRENT_DATE + interval '1 year'
						WHERE projektarbeit_id = " . $this->db_add_param($projektarbeit_id, FHC_INTEGER) . "
						AND person_id = " . $this->db_add_param($row_betr->person_id, FHC_INTEGER) . "
						AND betreuerart_kurzbz = 'Zweitbegutachter'";

					if ($this->db_query($qry_upd))
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
					return true; // not generated because intern or already exists
			}
			else
				return false;// not found
		}
		else
			return false; // query error
	}
}
?>
