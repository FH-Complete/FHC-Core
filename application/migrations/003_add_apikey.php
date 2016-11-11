<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Add_apikey extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function up()
	{
		$this->startUP();
		
		// Create table public.ci_apikey
		$fields = array(
			"apikey_id" => array(
				"type" => "serial"
			),
			"key" => array(
				"type" => "varchar(100)",
				"null" => false
			),
			"level" => array(
				"type" => "integer",
				"null" => true
			),
			"ignore_limits" => array(
				"type" => "integer",
				"null" => true
			),
			"date_created" => array(
				"type" => "date DEFAULT NOW()",
				"null" => true
			)
		);
		$this->createTable("public", "ci_apikey", $fields);
		$this->addPrimaryKey(
			"public",
			"ci_apikey",
			"pk_ci_apikey",
			array("apikey_id")
		);
		$this->grantTable(array("SELECT"), "public", "ci_apikey", "vilesci");
		
		$this->endUP();
	}

	public function down()
	{
		$this->startDown();
		
		$this->dropTable("public", "ci_apikey");
		
		$this->endDown();
	}
}