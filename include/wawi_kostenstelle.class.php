<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Klasse WaWi Kostenstelle
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/geschaeftsjahr.class.php');


class wawi_kostenstelle extends basis_db
{
	public $new; 						// boolean
	public $result = array();
	public $user;

	public $kostenstelle_id;			// integer, PK
	public $oe_kurzbz; 					// string,  FK
	public $bezeichnung;				// string
	public $kurzbz; 					// string
	public $aktiv; 						// boolean
	public $updateamum;					// timestamp
	public $updatevon;					// string
	public $insertamum;					// timestamp
	public $insertvon;					// string
	public $ext_id;						// integer
	public $kostenstelle_nr;			// string
	public $deaktiviertamum;			// timestamp
	public $deaktiviertvon;				// string


	/**
	 * Konstruktor
	 * @param kostenstelle ID der Kostenstelle die geladen werden soll (Default=null)
	 */
	public function __construct($kostenstelle_id = null)
	{
		parent::__construct();

		if(!is_null($kostenstelle_id))
			$this->load($kostenstelle_id);
	}

	/**
	 *
	 * Lädt die Kostenstelle mit der übergebenen ID
	 * @param $kostenstelle_id der Kostenstelle die geladen werden soll
	 * @return true wenn ok, false wenn fehler aufgetreten sind oder kein Eintrag mit der ID gefunden wurde
	 */
	public function load($kostenstelle_id)
	{
		// pruefen ob die übergebene id eine eine gültige zahl ist
		if(!is_numeric($kostenstelle_id) || ($kostenstelle_id ==''))
		{
			$this->errormsg = 'Kostenstellen ID ist keine Zahl';
			return false;
		}

		$qry = 'SELECT * FROM wawi.tbl_kostenstelle WHERE kostenstelle_id='.$this->db_add_param($kostenstelle_id, FHC_INTEGER).';';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->kostenstelle_id = $row->kostenstelle_id;
			$this->oe_kurzbz = $row->oe_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->kurzbz = $row->kurzbz;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->ext_id = $row->ext_id;
			$this->kostenstelle_nr = $row->kostenstelle_nr;
			$this->deaktiviertamum = $row->deaktiviertamum;
			$this->deaktiviertvon = $row->deaktiviertvon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 *
	 * Gibt alle Kostenstellen zurück
	 */
	public function getAll($filter ='')
	{
		$qry = 'SELECT * FROM wawi.tbl_kostenstelle';

		if($filter != '')
		{
			$qry.=" WHERE lower(oe_kurzbz) LIKE  lower('%".$this->db_escape($filter)."%') OR
						lower(bezeichnung) LIKE lower('%".$this->db_escape($filter)."%') OR
						lower(kurzbz) LIKE lower('%".$this->db_escape($filter)."%')";
		}

		$qry.=' ORDER BY bezeichnung;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage.';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new wawi_kostenstelle();

			$obj->kostenstelle_id = $row->kostenstelle_id;
			$obj->oe_kurzbz = $row->oe_kurzbz;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->kurzbz = $row->kurzbz;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->ext_id = $row->ext_id;
			$obj->kostenstelle_nr = $row->kostenstelle_nr;
			$obj->deaktiviertamum = $row->deaktiviertamum;
			$obj->deaktiviertvon = $row->deaktiviertvon;

			$this->result[] = $obj;
		}
		return true;
	}

	/**
	 *
	 * Löscht die Kostenstelle mit der übergebenen ID
	 * @param  $kostenstelle_id, id des Datensatzes der gelöscht wird
	 * @return true wenn OK, false wenn ein Fehler aufgetreten ist
	 */
	public function delete($kostenstelle_id)
	{
		if(!is_numeric($kostenstelle_id) || $kostenstelle_id =='')
		{
			$this->errormsg = 'Ungültige Kostenstellen ID';
			return false;
		}
		$qry ="SELECT * FROM wawi.tbl_bestellung WHERE kostenstelle_id = ".$this->db_add_param($kostenstelle_id, FHC_INTEGER, false).';';
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Löschen des Datensatzes';
			return false;
		}
		if($row = $this->db_fetch_object())
		{
			$this->errormsg = "Kostenstelle kann nicht gelöscht werden. Diese Kostenstelle verweist noch auf eine Bestelltung.";
			return false;
		}

		$qry = "DELETE FROM wawi.tbl_konto_kostenstelle WHERE kostenstelle_id =".$this->db_add_param($kostenstelle_id, FHC_INTEGER, false)."; ";
		$qry .= "DELETE FROM wawi.tbl_aufteilung_default WHERE kostenstelle_id =".$this->db_add_param($kostenstelle_id, FHC_INTEGER, false)."; ";
		$qry .= "DELETE FROM system.tbl_benutzerrolle WHERE kostenstelle_id =".$this->db_add_param($kostenstelle_id, FHC_INTEGER, false)."; ";
		$qry .= "DELETE FROM wawi.tbl_kostenstelle WHERE kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER, false).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Löschen des Datensatzes';
			return false;
		}
		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->kostenstelle_nr)>4)
		{
			$this->errormsg = 'Kostenstellennummer darf nicht laenger als 4 Zeichen sein.';
		}

		if(mb_strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 256 Zeichen sein.';
			return false;
		}

		if(mb_strlen($this->kurzbz)>32)
		{
			$this->errormsg = 'Kurzbezeichnung darf nicht laenger als 32 Zeichen sein.';
			return false;
		}

		$this->errormsg = '';
		return true;
	}


	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $kostenstelle_id aktualisiert
 	 * @param $new wenn true wird ein Insert durchgefuehrt, wenn false ein Update
 	 *        und wenn null wird das new-Objekt der Klasse verwendet
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen pruefen
		if(!$this->validate())
			return false;


		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO wawi.tbl_kostenstelle (oe_kurzbz, bezeichnung, kurzbz, aktiv, updateamum, updatevon,
			insertamum,	insertvon, kostenstelle_nr, deaktiviertamum, deaktiviertvon ) VALUES('.
			      $this->db_add_param($this->oe_kurzbz).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->kurzbz).', '.
			      $this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				  $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->kostenstelle_nr).', '.
			      $this->db_add_param($this->deaktiviertamum).', '.
				  $this->db_add_param($this->deaktiviertvon).');';

		}
		else
		{
			//Pruefen ob kostenstelle_id eine gueltige Zahl ist
			if(!is_numeric($this->kostenstelle_id))
			{
				$this->errormsg = 'kostenstelle_id muss eine gültige Zahl sein';
				return false;
			}
			$qry='UPDATE wawi.tbl_kostenstelle SET'.
				' oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).', '.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' kurzbz='.$this->db_add_param($this->kurzbz).', '.
		      	' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				' updateamum='.$this->db_add_param($this->updateamum).', '.
				' updatevon='.$this->db_add_param($this->updatevon).', '.
				' insertamum='.$this->db_add_param($this->insertamum).', '.
				' insertvon='.$this->db_add_param($this->insertvon).', '.
				' kostenstelle_nr='.$this->db_add_param($this->kostenstelle_nr).', '.
				' deaktiviertamum='.$this->db_add_param($this->deaktiviertamum).', '.
				' deaktiviertvon='.$this->db_add_param($this->deaktiviertvon).' '.
		      	' WHERE kostenstelle_id='.$this->db_add_param($this->kostenstelle_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('wawi.seq_kostenstelle_kostenstelle_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kostenstelle_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			return false;
		}
		return $this->kostenstelle_id;

	}

	/**
	 *
	 * Loescht kostenstelle mit der id1 und legt dessen Schlüssel in anderen
	 * Tabellen auf die kostenstelle mit der id2 um
	 * @param $id1 kostenstelle_id des radiobuttons
	 * @param $id2 kostenstelle_id des radiobuttons
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function zusammenlegen($id1, $id2)
	{

		$sql_query_upd1="BEGIN;";
		$sql_query_upd1.="UPDATE wawi.tbl_aufteilung_default SET kostenstelle_id=".$this->db_add_param($id2, FHC_INTEGER)." WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER)."; ";
		$sql_query_upd1.="UPDATE wawi.tbl_konto_kostenstelle SET kostenstelle_id=".$this->db_add_param($id2, FHC_INTEGER)." WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER)." AND konto_id NOT IN(SELECT konto_id FROM wawi.tbl_konto_kostenstelle WHERE kostenstelle_id=".$this->db_add_param($id2, FHC_INTEGER)."); ";
		$sql_query_upd1.="DELETE FROM wawi.tbl_konto_kostenstelle WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER).";";
		$sql_query_upd1.="DELETE FROM wawi.tbl_budget WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER).";";
		$sql_query_upd1.="UPDATE wawi.tbl_bestellung SET kostenstelle_id=".$this->db_add_param($id2, FHC_INTEGER)." WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER)."; ";
		$sql_query_upd1.="UPDATE system.tbl_benutzerrolle SET kostenstelle_id=".$this->db_add_param($id2, FHC_INTEGER)." WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER)."; ";

		$sql_query_upd1.="DELETE FROM wawi.tbl_kostenstelle WHERE kostenstelle_id=".$this->db_add_param($id1, FHC_INTEGER).";";

		if($this->db_query($sql_query_upd1))
		{
			$this->db_query("COMMIT;");
			return true;
		}
		else
		{
			$this->db_query("ROLLBACK;");
			$this->errormsg = "Fehler beim Update aufgetreten";
			return false;
		}
	}

	/**
	 *
	 * Es wird überprüft ob der Eintrag mit den 2 IDs schon in der Zwischentabelle vorhanden ist
	 * @param $konto_id
	 * @param $kostenselle_id
	 * @return true wenn es Eintrag schon gibt, false wenn der Eintrag noch nicht vorhanden ist
	 */
	public function check_konto_kostenstelle($kostenstelle_id, $konto_id)
	{
		if(!is_numeric($kostenstelle_id) || $kostenstelle_id =='')
		{
			$this->errormsg = "Ungültige Kostenstellen ID";
			return false;
		}

		if(!is_numeric($konto_id) || $konto_id =='')
		{
			$this->errormsg = "Ungültige ID";
			return false;
		}

		$qry = "SELECT * FROM wawi.tbl_konto_kostenstelle WHERE konto_id = ".$this->db_add_param($konto_id, FHC_INTEGER)." AND kostenstelle_id = ".$this->db_add_param($kostenstelle_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei Abfrage der Zwischentabelle.';
		}
	}

	/**
	 *
	 * Es wird ein neuer Eintrag in der Zwischentabelle erstellt
	 * @param $kostenstelle_id
	 * @param $konto_id
	 * @return true bei erfolgreichem einfügen, false wenn ein Fehler aufgetreten ist
	 */
	public function save_konto_kostenstelle($kostenstelle_id, $konto_id)
	{
		if(!is_numeric($kostenstelle_id) || $kostenstelle_id =='')
		{
			$this->errormsg = "Ungültige Kostenstellen ID";
			return false;
		}

		if(!is_numeric($konto_id) || $konto_id =='')
		{
			$this->errormsg = "Ungültige Konto ID";
			return false;
		}

		$qry = "INSERT INTO wawi.tbl_konto_kostenstelle (konto_id, kostenstelle_id)	VALUES (".
				$this->db_add_param($konto_id, FHC_INTEGER).",".
				$this->db_add_param($kostenstelle_id, FHC_INTEGER).");";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei Insert in die Zwischentabelle';
			return false;
		}
	}

	/**
	 *
	 * Gibt alle Konten zurück die einer Kostenstelle zugeordnet sind
	 * @param $kostenstelle_id, id der Kostenstelle deren Konten zurückgeben werden sollen
	 * @return $konto Array aller Konten
	 */
	public function get_konto_from_kostenstelle($kostenstelle_id)
	{
		if(!is_numeric($kostenstelle_id) || $kostenstelle_id =='')
		{
			$this->errormsg = "Ungültige Kostenstellen ID";
			return false;
		}

		$konto = array();
		$qry = "SELECT konto_id FROM wawi.tbl_konto_kostenstelle
				WHERE kostenstelle_id = ".$this->db_add_param($kostenstelle_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{

			while($row = $this->db_fetch_object())
			{
				$konto[] = $row->konto_id;
			}

			return $konto;
		}
	}

	/**
	 *
	 * Löscht alle zugewiesenen Konten einer Kostenstelle, ausser die die übergeben wurden
	 * @param $kostenstelle_id, id der Kostenstelle deren zugewiesenen Konten gelöscht werden sollen
	 * @param Array $active, deren Konten die nicht gelöscht werden
	 * @return true bei erfolg, false bei Fehlerfall
	 */
	public function delete_konto_kostenstelle($kostenstelle_id, $active)
	{
		if(!is_numeric($kostenstelle_id) || $kostenstelle_id =='')
		{
			$this->errormsg = "Ungültige Kostenstellen ID";
			return false;
		}
		$var = $this->implode4SQL($active);
		$qry = "DELETE FROM wawi.tbl_konto_kostenstelle
				WHERE kostenstelle_id = ".$this->db_add_param($kostenstelle_id)."
				AND konto_ID NOT IN (".$var.") ;";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen aus der Zwischentabelle aufgetreten.';
			return false;
		}
	}

	/**
	 * Liefert das Budget einer Kostenstelle in einem Geschaeftsjahr
	 *
	 * @param $kostenstelle_id ID der Kostenstelle
	 * @param $geschaeftsjahr_kurzbz Geschaeftsjahr, wenn keines Uebergeben wird, wird das aktuelle genommen
	 */
	public function getBudget($kostenstelle_id, $geschaeftsjahr_kurzbz=null)
	{
		if(is_null($geschaeftsjahr_kurzbz))
		{
			$gj = new geschaeftsjahr();
			$geschaeftsjahr_kurzbz = $gj->getAkt();
		}

		$qry = "SELECT budget FROM wawi.tbl_budget
				WHERE kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER)."
				AND geschaeftsjahr_kurzbz=".$this->db_add_param($geschaeftsjahr_kurzbz).";";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->budget;
			}
			else
			{
				$this->errormsg = 'Es gibt kein Budget für diese Kostenstelle';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Budgets';
			return false;
		}
	}


	/**
	 * Speichert das Budget einer Kostenstelle
	 *
	 * @param $kostenstelle_id
	 * @param $geschaeftsjahr_kurzbz
	 * @param $budget
	 */
	public function setBudget($kostenstelle_id, $geschaeftsjahr_kurzbz, $budget)
	{
		if($kostenstelle_id=='')
		{
			$this->errormsg = 'KostenstelleID muss uebergeben weden';
			return false;
		}
		if($geschaeftsjahr_kurzbz=='')
		{
			$this->errormsg = 'Geschaeftsjahr muss uebergeben werden';
			return false;
		}
		if($budget=='')
			$budget='0';

		if(!is_numeric($budget))
		{
			$this->errormsg = 'Budget ist ungueltig';
			return false;
		}

		$qry = '';
		if($old_budget = $this->getBudget($kostenstelle_id, $geschaeftsjahr_kurzbz))
		{
			if($old_budget!=$budget)
			{
				$qry = "UPDATE
							wawi.tbl_budget SET budget=".$this->db_add_param($budget)."
						WHERE
							kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER)."
							AND geschaeftsjahr_kurzbz=".$this->db_add_param($geschaeftsjahr_kurzbz).";";
			}
		}
		else
		{
			$qry = "INSERT INTO wawi.tbl_budget(kostenstelle_id, geschaeftsjahr_kurzbz, budget) VALUES(".
					$this->db_add_param($kostenstelle_id, FHC_INTEGER).",".
					$this->db_add_param($geschaeftsjahr_kurzbz).",".
					$this->db_add_param($budget).");";
		}

		if($qry!='')
		{
			if($this->db_query($qry))
			{
				return true;
			}
			else
			{
				$this->errormsg='Fehler beim Speichern des Budgets';
				return false;
			}
		}
		else
			return true;
	}

	/**
	 * Laedt die Kostenstellen die als Array uebergeben werden
	 * @param $kst_id Array mit den kst_ids
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($kst_id, $order=null, $aktiv=true)
	{
		if(count($kst_id)==0)
			return true;

		$kst_id = $this->implode4SQL($kst_id);

		$qry = 'SELECT * FROM wawi.tbl_kostenstelle WHERE kostenstelle_id in('.$kst_id.')';
		if ($aktiv)
			$qry.=' AND aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$obj = new wawi_kostenstelle();

			$obj->kostenstelle_id = $row->kostenstelle_id;
			$obj->oe_kurzbz = $row->oe_kurzbz;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->kurzbz = $row->kurzbz;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->ext_id = $row->ext_id;
			$obj->kostenstelle_nr = $row->kostenstelle_nr;
			$obj->deaktiviertvon = $row->deaktiviertvon;
			$obj->deaktiviertvon = $row->deaktiviertvon;

			$this->result[] = $obj;
		}

		return true;
	}
}
