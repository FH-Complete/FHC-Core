<?php

class mailgrp
{
	//String
	var $mailgrp_kurzbz;
	//Int
	var $studiengang_kz;
	//string
	var $beschreibung;
	//bool
	var $sichtbar;
	//bool
	var $generiert;
	//bool
	var $aktiv;
	//array
	var $result = array();
	//string
	var $errormsg;
     // resource
     var $conn;
	

     function mailgrp($conn)
	{
		$this->conn=$conn;
	
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
	}*/
	
	/**
	 * Liefert alle Elemente der tbl_mailgrp
	 * @param studiengang_kz
	 * @return true wenn OK false wenn Fehler
	 */
	function getAll($studiengang_kz='')
	{
		if (is_null($this->conn)) {
			$this->errormsg = "Keine Connection vorhanden";
			return false;
		}
		if (strlen($this->studiengang_kz)>0)
		{
			$where=" where studiengang_kz='".$studiengang_kz."' ";
		} else
		{
			$where="";
		}
		$qry="select * FROM tbl_mailgrp".
             "$where order by mailgrp_kurzbz";
		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		
		
		while($row=pg_fetch_object($erg))
		{			
			$l=new mailgrp($this->conn);
			$l->mailgrp_kurzbz = $row->mailgrp_kurzbz;
			$l->studiengang_kz = $row->studiengang_kz;
			$l->beschreibung = $row->beschreibung;
			$l->aktiv = $row->aktiv;
			$l->generiert = $row->generiert;
			$l->sichtbar = $row->sichtbar;
			$result[]=$l;
		}
		return $result;
	}
}
?>