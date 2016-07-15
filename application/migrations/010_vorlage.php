<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Vorlage extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		// Change vorlage_kurzbz to varchar 32		
		$columns = array(
			"vorlage_kurzbz" => array("type" => "varchar(32)")
		);
		$this->modifyColumn("public", "tbl_vorlage", $columns);
		
		// Change vorlage_kurzbz to varchar 32
		$columns = array(
			"vorlage_kurzbz" => array("type" => "varchar(32)")
		);
		$this->modifyColumn("public", "tbl_vorlagestudiengang", $columns);
		
		// Add attribute to public.tbl_vorlage
		$columns = array(
			"attribute" => array("type" => "json")
		);
		$this->addColumn("public", "tbl_vorlage", $columns);
		
		// Add sprache, subject and orgform_kurzbz to public.tbl_vorlagestudiengang
		$columns = array(
			"sprache" => array("type" => "varchar(16)"),
			"subject" => array("type" => "text"),
			"orgform_kurzbz" => array("type" => "varchar(3)")
		);
		$this->addColumn("public", "tbl_vorlagestudiengang", $columns);
		
		$this->initializeSequence(
			"public", "seq_vorlagestudiengang_vorlagestudiengang_id", "public",
			"tbl_vorlagestudiengang", "vorlagestudiengang_id"
		);
		
		// Add foreign keys to tbl_vorlagestudiengang
		$this->addForeingKey(
			"public",
			"tbl_vorlagestudiengang",
			"fk_vorlagestudiengang_sprache",
			"sprache",
			"public",
			"tbl_sprache",
			"sprache",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_vorlagestudiengang",
			"fk_vorlagestudiengang_orgform_kurzbz",
			"orgform_kurzbz",
			"bis",
			"tbl_orgform",
			"orgform_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();
		
		$this->dropColumn("public", "tbl_vorlage", "attribute");
		$this->dropColumn("public", "tbl_vorlagestudiengang", "subject");
		$this->dropColumn("public", "tbl_vorlagestudiengang", "orgform_kurzbz");
		
		$this->endDown();
    }
}