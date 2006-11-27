<?php
/*
$Header: /Pfad/Kodierrichtlinien.tex,v 1.2 2004/02/29 17:05:38 pam Exp $
$Log: Kodierrichtlinien.tex,v $
Revision 1.2 2004/02/29 17:05:38 pam
Fehler in Umlauten beseitigt.
*/

/**
 * @class ects_sync
 *
 * @author Andreas Österreicher
 * 
 * @date 30.9.2005
 *
 * @version $Revision: 1.2 $
 *
 * @brief Enthält Methoden für die ECTS Syncronisation
 */


class ects_sync
{
	var $erg;
	var $connection;
	
	/********************************************************************
	 * @brief Konstruktor
	 *
	 * @param $conn Connection zur DB
	 ********************************************************************/
	function ects_sync($conn)
	{
		$this->connection=$conn;
	}
	
	/********************************************************************
	 * @brief Überprüft ob der Fachbereich existiert
	 *
	 * @param $fachb Fachbereichsbezeichnung
	 ********************************************************************/
	function check_fachbereich($fachb)
	{
		$sql_query = "SELECT * FROM tbl_fachbereich where bezeichnung='$fachb' OR kurzbz='$fachb'";
		
		$result = pg_exec($this->connection, $sql_query);
		
		if($row=pg_fetch_object($result))
			$this->erg = $row->fachbereich_id;
		else 
			return false;
				
		return true;
	}
	
	/********************************************************************
	 * @brief Überprüft ob der Studiengang existiert
	 *
	 * @param $stg Studiengangs Kurzzeichen
	 ********************************************************************/
	function check_studiengang($stg)
	{
		$sql_query = "SELECT * FROM tbl_studiengang where studiengang_kz = $stg";
		
		$result = pg_exec($this->connection, $sql_query);
		
		if(pg_numrows($result)>0)
			$this->erg = $stg;
		else 
			return false;
				
		return true;
	}
     /********************************************************************
	 * @brief Überprüft ob das Lehrfach existiert
	 *
	 * @param $lehrf LehrfachsKurzbz
	 *        $stg   Studiengang
	 *        $sem   Semester
	 ********************************************************************/
	function check_lehrfach($lehrf, $stg, $sem)
	{
		$sql_query = "SELECT * FROM tbl_lehrfach where kurzbz = upper('$lehrf') AND studiengang_kz=$stg AND semester=$sem";
		
		$result = pg_exec($this->connection, $sql_query);
		
		if($row=pg_fetch_object($result))
			$this->erg = $row->lehrfach_nr;
		else 
			return false;
				
		return true;
	}
	
	/********************************************************************
	 * @brief Überprüft das Semester
	 *
	 * @param $sem Semster
	 ********************************************************************/
	function check_semester($sem)
	{
		if(is_numeric($sem) AND $sem<10)
		   $this->erg = $sem;		
		else 
		   return false;
		
		return true;
	}
}
?>