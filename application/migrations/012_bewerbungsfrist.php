<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Bewerbungsfrist extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		// Create table lehre.tbl_bewerbungsfrist
		$fields = array(
			"bewerbungsfrist_id" => array(
				"type" => "integer",
				"auto_increment" => true
			),
			"studiensemester_kurzbz" => array(
				"type" => "varchar(16)"
			),
			"begin" => array(
				"type" => "date"
			),
			"ende" => array(
				"type" => "date"
			),
			"ende" => array(
				"type" => "date"
			),
			"nachfrist" => array(
				"type" => "boolean DEFAULT FALSE",
				"null" => true
			),
			"nachfristende" => array(
				"type" => "date",
				"null" => true
			),
			"anmerkung" => array(
				"type" => "text"
			),
			"insertamum" => array(
				"type" => "timestamp DEFAULT NOW()",
				"null" => true
			),
			"insertvon" => array(
				"type" => "varchar(32)",
				"null" => true
			),
			"updateamum" => array(
				"type" => "timestamp DEFAULT NOW()",
				"null" => true
			),
			"updatevon" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("lehre", "tbl_bewerbungsfrist", $fields);
		$this->addPrimaryKey(
			"lehre",
			"tbl_bewerbungsfrist",
			"pk_tbl_bewerbungsfrist",
			array("bewerbungsfrist_id")
		);
		$this->addForeingKey(
			"lehre",
			"tbl_bewerbungsfrist",
			"fk_bewerbungsfrist_studiensemester_kurzbz",
			"studiensemester_kurzbz",
			"public",
			"tbl_studiensemester",
			"studiensemester_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "lehre", "tbl_bewerbungsfrist", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "lehre", "tbl_bewerbungsfrist", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "lehre", "tbl_bewerbungsfrist", "vilesci");
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();

		$this->dropTable("lehre", "tbl_bewerbungsfrist");

		$this->endDown();
    }
}