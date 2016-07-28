<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Akte extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		// Add nachgereicht_am to public.tbl_akte
		$columns = array(
			"nachgereicht_am" => array(
				"type" => "date",
				"null" => true
			)
		);
		$this->addColumn("public", "tbl_akte", $columns);
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();

		$this->dropColumn("public", "tbl_akte", "nachgereicht_am");

		$this->endDown();
    }
}