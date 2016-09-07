<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Bewerbungstermine extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();
		
		// Add studienplan_id to public.tbl_bewerbungstermine
		$columns = array(
			"studienplan_id" => array(
				"type" => "integer",
				"null" => true
			)
		);
		$this->addColumn("public", "tbl_bewerbungstermine", $columns);
		
		$this->addForeingKey(
			"public",
			"tbl_bewerbungstermine",
			"fk_bewerbungstermine_studienplan_id",
			"studienplan_id",
			"lehre",
			"tbl_studienplan",
			"studienplan_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();
		
		$this->dropColumn("public", "tbl_bewerbungstermine", "studienplan_id");
		
		$this->endDown();
    }
}