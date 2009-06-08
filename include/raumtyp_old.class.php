<?php
/**
 * Klasse raumtyp (FAS-Online)
 * @create 14-03-2006
 */
class raumtyp
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var raumtyp Objekt

	var $raumtyp_id;      // @var integer
	var $bezeichnung;     // @var string
	var $kurzbezeichnung; // @var string
	var $plaetze;         // @var integer
	var $updateamum;      // @var timestamp
	var $updatevon;       // @var string

	/**
	 * Konstruktor
	 * @param conn Connection zur Datenbank
	 *        raum_id ID des zu ladenden Raumes (default=null)
	 */
	 function raumtyp($conn, $raum_id=null)
	{
		$this->conn = $conn;
/*		
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
*/	
		if($raum_id != null)
			$this->load($raum_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param $raum_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	 function load($raum_id)
	{
		if(!is_numeric($raum_id) || $raum_id == '')
		{
			$this->errormsg = 'raum_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM raumtyp WHERE raumtyp_pk = '$raum_id';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datenstatzes';
			return false;
		}

		if($row = pg_fetch_object($res))
		{
			$this->raumtyp_id      = $row->raumtyp_pk;
			$this->bezeichnung     = $row->bezeichnung;
			$this->kurzbezeichnung = $row->kurzbezeichnung;
			$this->plaetze         = $row->plaetze;
			$this->updateamum      = $row->creationdate;
			$this->updatevon       = $row->creationuser;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Datensaetze
	 * @return ture wenn ok, false im Fehlerfall
	 */
	 function getAll()
	{
		$qry = "SELECT * FROM raumtyp;";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Datensaetze';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$raum_obj = new raumtyp($this->conn);

			$raum_obj->raumtyp_id      = $row->raumtyp_pk;
			$raum_obj->bezeichnung     = $row->bezeichnung;
			$raum_obj->kurzbezeichnung = $row->kurzbezeichnung;
			$raum_obj->plaetze         = $row->plaetze;
			$raum_obj->updateamum      = $row->creationdate;
			$raum_obj->updatevon       = $row->creationuser;

			$this->result[] = $raum_obj;
		}
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	 function save()
	{
		$this->errormsg = 'Noch nicht Implementiert';
		return false;
	}

	/**
	 * Loescht einen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	 function delete($raum_id)
	{
		$this->errormsg = 'Noch nicht Implementiert';
		return false;
	}
}
?>