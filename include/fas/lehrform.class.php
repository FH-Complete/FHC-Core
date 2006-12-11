<?php
/**
 * Klasse lehrform (FAS-Online)
 * @create 15-03-2006
 */
class lehrform
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array();   // @var lehrform Objekt
	
	var $lehrform_id;     // @var integer
	var $bezeichnung;     // @var string
	var $kurzbezeichnung; // @var string
	var $standardfaktor;  // @var float
	var $updateamum;      // @var timestamp
	var $updatevon=0;     // @var string
	
	/**
	 * Konstruktor
	 * @param $conn Conection zur Datenbank
	 *        $lehrform_id ID der zu Ladenden Lehrform
	 */
	function lehrform($conn, $lehrform_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($lehrform_id != null)
			$this->load($lehrform_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param $lform_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lform_id)
	{
		//pruefen ob lform_id eine gueltige Zahl ist
		if(!is_numeric($lform_id) || $lform_id == '')
		{
			$this->errormsg = 'lehrform_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Datensatz laden
		$qry = "SELECT * FROM lehrform WHERE lehrform_pk = '$lform_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Der Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->lehrform_id     = $row->lehrform_pk;
			$this->bezeichnung     = $row->bezeichnung;
			$this->kurzbezeichnung = $row->kurzbezeichnung;
			$this->standardfaktor  = $row->standardfaktor;
			$this->updateamum      = $row->creationdate;
			$this->updatevon       = $row->creationuser;
		}
		else 
		{
			$this->errormsg = 'Der Datensatz konnte nicht geladen werden';
			return false;
		}
		
		return true;		
	}
	
	/**
	 * Liefert alle lehrformen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM lehrform;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Der Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$form_obj = new lehrform($this->conn);

			$form_obj->lehrform_id     = $row->lehrform_pk;
			$form_obj->bezeichnung     = $row->bezeichnung;
			$form_obj->kurzbezeichnung = $row->kurzbezeichnung;
			$form_obj->standardfaktor  = $row->standardfaktor;
			$form_obj->updateamum      = $row->creationdate;
			$form_obj->updatevon       = $row->creationdate;
			
			$this->result[] = $form_obj;	
		}
		
		return true;
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param lehrform_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lehrform_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Speichert einen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->errormsg = 'Noch nicht implemeniert';
		return false;
	}	 
}
?>