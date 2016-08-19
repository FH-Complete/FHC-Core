<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Person extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		// Add zugangscode_timestamp to public.tbl_person
		$columns = array(
			"zugangscode_timestamp" => array(
				"type" => "timestamp",
				"null" => true
			)
		);
		$this->addColumn("public", "tbl_person", $columns);
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();

		$this->dropColumn("public", "tbl_person", "zugangscode_timestamp");

		$this->endDown();
    }
}