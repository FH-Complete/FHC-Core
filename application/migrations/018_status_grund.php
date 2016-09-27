<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Status_grund extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();
		
		$fields = array(
			"statusgrund_kurzbz" => array(
				"type" => "integer",
				"auto_increment" => true
			),
			"status_kurzbz" => array(
				"type" => "varchar(20)"
			),
			"aktiv" => array(
				"type" => "boolean DEFAULT FALSE",
				"null" => true
			),
			"bezeichnung_mehrsprachig" => array(
				"type" => "varchar(255)[]"
			),
			"beschreibung" => array(
				"type" => "text[]"
			)
		);
		$this->createTable("public", "tbl_status_grund", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_status_grund",
			"pk_tbl_status_grund",
			array("statusgrund_kurzbz")
		);
		$this->addForeingKey(
			"public",
			"tbl_status_grund",
			"fk_status_grundstatus_kurzbz",
			"status_kurzbz",
			"public",
			"tbl_status",
			"status_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addUniqueKey(
			"public",
			"tbl_status_grund",
			"uk_tbl_status_grund_status_kurzbz",
			array("status_kurzbz")
		);
		$this->grantTable("SELECT", "public", "tbl_status_grund", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_status_grund", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_status_grund", "vilesci");
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();
		
		$this->dropTable("public", "tbl_status_grund");
		
		$this->endDown();
    }
}
