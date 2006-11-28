<?php
/** 
 * Klasse fachbereich (FAS-Online)
 * @create 14-03-2006
 */

class fachbereich
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var fachbereich Objekt 
	
	//Tabellenspalten
	var $fachbereich_id; // @var integer
	var $bezeichnung;           // @var string
	var $kurzbz;   //@var string
	var $farbe;   //@var string
	var $updateamum;     // @var timestamp
	var $updatevon=0;    // @var string
	var $insertamum;   //@var timestamp
	var $insertvon=0;   //@var string
	var $ext_id;   //@var bigint
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $fachb_id ID des zu ladenden Fachbereiches
	 */
	function fachbereich($conn, $fachb_id=null)
	{
		$this->conn = $conn;
		if($fachb_id != null)
			$this->load($fachb_id);
	}
	
	/**
	 * Laedt alle verfuegbaren Fachbereiche
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM fachbereich order by name;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$fachb_obj = new fachbereich($this->conn);
			
			$fachb_obj->fachbereich_id = $row->fachbereich_pk;
			$fachb_obj->erhalter_id    = $row->erhalter_fk;
			$fachb_obj->name           = $row->name;
			$fachb_obj->updateamum     = $row->creationdate;
			$fachb_obj->updatevon     = $row->creationuser;
			
			$this->result[] = $fachb_obj;
		}
		return true;
	}
	
	/**
	 * Laedt einen Fachbereich
	 * @param $fachb_id ID des zu ladenden Fachbereiches
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($fachb_id)
	{
		if(!is_numeric($fachb_id) || $fachb_id == '')
		{
			$this->errormsg = 'fachb_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fachbereich WHERE fachbereich_pk = '$fachb_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->fachbereich_id = $row->fachbereich_pk;
			$this->erhalter_id    = $row->erhalter_fk;
			$this->name           = $row->name;
			$this->updateamum     = $row->creationdate;
			$this->updatevon      = $row->creationuser;
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
	 * @param $fachb_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($fachb_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen		

			$qry = "INSERT INTO fachbereich (bezeichnung, kurzbz, farbe, ".
				"ext_id, insertamum, insertvon, updateamum, updatevon) VALUES (".
				"'$this->bezeichnung', '$this->kurzbz', '$this->farbe'".
				"'$this->ext_id', '$this->insertamum', '$this->insertvon', '$this->updateamum', '$this->updatevon');";
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob lehrveranstaltung_id eine gueltige Zahl ist
			if(!is_numeric($this->fachbereich_id_id) || $this->fachbereich_id == '')
			{
				$this->errormsg = 'fachbereich_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = "UPDATE fachbereich SET fachbereich_id='$this->fachbereich_id', bezeichnung='$this->bezeichnung', ".
				"kurzbz='$this->kurzbz', farbe='$this->farbe', ext_id='$this->ext_id', insertamum='$this->insertamum', ".
				"insertvon='$this->insertvon', updateamum='$this->updateamum', updatevon='$this->updatevon' ".
				"WHERE fachbereich_id = '$this->fachbereich_id';";
		}
		
		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}		
	}
}
?>