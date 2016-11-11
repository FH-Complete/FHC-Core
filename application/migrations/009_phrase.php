<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Phrase extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();
		
		// Create table system.tbl_app
		$fields = array(
			"app" => array(
				"type" => "varchar(32)"
			)
		);
		$this->createTable("system", "tbl_app", $fields);
		$this->addPrimaryKey(
			"system",
			"tbl_app",
			"pk_tbl_app",
			array("app")
		);
		$this->grantTable("SELECT", "system", "tbl_app", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "system", "tbl_app", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "system", "tbl_app", "vilesci");
		
		// Create table system.tbl_phrase
		$fields = array(
			"phrase_id" => array(
				"type" => "serial"
			),
			"app" => array(
				"type" => "varchar(32)",
				"null" => false
			),
			"phrase" => array(
				"type" => "varchar(64)",
				"null" => false
			),
			"insertamum" => array(
				"type" => "timestamp DEFAULT CURRENT_TIMESTAMP",
				"null" => false
			),
			"insertvon" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("system", "tbl_phrase", $fields);
		$this->addPrimaryKey(
			"system",
			"tbl_phrase",
			"pk_tbl_phrase",
			array("phrase_id")
		);
		$this->grantTable("SELECT", "system", "tbl_phrase", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "system", "tbl_phrase", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "system", "tbl_phrase", "vilesci");
		$this->grantSequence(array("SELECT", "UPDATE"), "system", "tbl_phrase_phrase_id_seq", "web");
		$this->grantSequence(array("SELECT", "UPDATE"), "system", "tbl_phrase_phrase_id_seq", "admin");
		$this->grantSequence(array("SELECT", "UPDATE"), "system", "tbl_phrase_phrase_id_seq", "vilesci");
		
		// Create table system.tbl_phrasentext
		$fields = array(
			"phrasentext_id" => array(
				"type" => "serial"
			),
			"phrase_id" => array(
				"type" => "bigint",
				"null" => false
			),
			"sprache" => array(
				"type" => "varchar(32)",
				"null" => false
			),
			"orgeinheit_kurzbz" => array(
				"type" => "varchar(32)",
				"null" => true
			),
			"orgform_kurzbz" => array(
				"type" => "varchar(32)",
				"null" => true
			),
			"text" => array(
				"type" => "text",
				"null" => true
			),
			"description" => array(
				"type" => "text",
				"null" => true
			),
			"insertamum" => array(
				"type" => "timestamp DEFAULT CURRENT_TIMESTAMP",
				"null" => false
			),
			"insertvon" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("system", "tbl_phrasentext", $fields);
		$this->addPrimaryKey(
			"system",
			"tbl_phrasentext",
			"pk_tbl_phrasentext",
			array("phrasentext_id")
		);
		$this->grantTable("SELECT", "system", "tbl_phrasentext", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "system", "tbl_phrasentext", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "system", "tbl_phrasentext", "vilesci");
		$this->grantSequence(array("SELECT", "UPDATE"), "system", "tbl_phrasentext_phrasentext_id_seq", "web");
		$this->grantSequence(array("SELECT", "UPDATE"), "system", "tbl_phrasentext_phrasentext_id_seq", "admin");
		$this->grantSequence(array("SELECT", "UPDATE"), "system", "tbl_phrasentext_phrasentext_id_seq", "vilesci");
		
		$this->endUP();
	}
    
    public function down()
    {
		$this->startDown();
		
		$this->dropTable("system", "tbl_phrasentext");
		$this->dropTable("system", "tbl_phrase");
		$this->dropTable("system", "tbl_app");
		
		$this->endDown();
    }
}