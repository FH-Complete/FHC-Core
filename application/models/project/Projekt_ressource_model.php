<?php
class Projekt_ressource_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_projekt_ressource';
		$this->pk = 'projekt_ressource_id';
	}

	public function getProjektleiterActiveProjects()
	{
	$qry = "SELECT DISTINCT r.mitarbeiter_uid, p.projekt_kurzbz, p.titel
			FROM fue.tbl_projekt p
			JOIN fue.tbl_projekt_ressource pr using (projekt_kurzbz)
			JOIN fue.tbl_ressource r on (pr.ressource_id = r.ressource_id)
			wHERE((p.beginn<=now() or p.beginn is null)
			AND (p.ende >=now() OR p.ende is null))
			AND pr.funktion_kurzbz = 'Leitung'";
	return $this->execQuery($qry);
	}
}
