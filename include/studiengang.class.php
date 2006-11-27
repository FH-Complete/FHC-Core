<?php
/**
 * Basisklasse für Studiengaenge
 * @author Christian Paminger, Werner Masik
 * @version 1.0
 * @updated 28-Okt-2004:
 */
class studiengang {

	/**
	 * @var studiengang_kz
	 */
	var $studiengang_kz;

	/**
	 * @var string
	 */
	var $kurzbz;

	/**
	 * @var string
	 */
	var $kurzbzlang;

	/**
	 * @var string
	 */
	var $bezeichnung;

	/**
	 * @var integer
	 */
	var $max_semester;

	/**
	 * @var char
	 */
	var $typ;

	/**
	 * @var string
	 */
	var $farbe;

	/**
	 * @var string
	 */
	var $email;

	/**
	 * @var string
	 */
	var $errormsg;

	/**
	 * @var resource
	 */
	var $conn;

    function studiengang($conn)
    {
	   $this->conn = $conn;
    }

    /**
	 * Verbindung zur Datenbank herstellen
	 * @return PostgreSQL-Connection oder NULL
	 
	function getConnection()
	{
		if (!$conn = @pg_pconnect(CONN_STRING)) {
	   		$this->errormsg="Es konnte keine Verbindung zum Server ".
	   						"aufgebaut werden.";
	   		return null;
		}
		return $conn;
	}*/


    /**
     * @return array Array mit allen Studiengängen, oder false bei Fehler
     */
    function getAll($order='kurzbz')
    {
		if (is_null($this->conn)) {
			$this->errormsg = "Connection failed";
			return false;
		}
		$qry="select * from tbl_studiengang order by $order";
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$result=array();
		$num_rows=pg_numrows($erg);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object($erg,$i);
			$p=new studiengang($this->conn);
			$p->studiengang_kz=$row->studiengang_kz;
			$p->kurzbz=$row->kurzbz;
			$p->kurzbzlang=$row->kurzbzlang;
			$p->bezeichnung=$row->bezeichnung;
			$p->max_semester=$row->max_semester;
			$p->typ=$row->typ;
			$p->farbe=$row->farbe;
			$p->email=$row->email;
			$result[]=$p;
		}
		return $result;
    }

    function load($stgkz)
    {
    	if (is_null($this->conn)) {
			$this->errormsg = "Connection failed";
			return false;
		}
		$qry="select * from tbl_studiengang where studiengang_kz=$stgkz";
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		else 
		{
			if(pg_num_rows($erg)>0)
			{
				$row=pg_fetch_object($erg);
				$this->studiengang_kz = $row->studiengang_kz;
				$this->kurzbz = $row->kurzbz;
				$this->kurzbzlang = $row->kurzbzlang;
				$this->bezeichnung = $row->bezeichnung;
				$this->typ = $row->typ;
				$this->farbe = $row->farbe;
				$this->email = $row->email;
				$this->max_semester = $row->max_semester;
				$this->max_verband = $row->max_verband;
				$this->max_gruppe = $row->max_gruppe;
				return true;
			}
			else 
			{
				$this->errormsg = "Studiengang konnte nicht aufgeloest werden";
				return false;
			}
		}
    }
    
}
?>