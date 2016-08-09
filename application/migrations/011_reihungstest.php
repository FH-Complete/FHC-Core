<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

require_once APPPATH . "/libraries/MigrationLib.php";

class Migration_Reihungstest extends MigrationLib
{
	public function __construct()
	{
		parent::__construct();
	}
	
    public function up()
    {
		$this->startUP();

		// Add stufe and anmeldefrist to public.tbl_reihungstest
		$columns = array(
			"stufe" => array("type" => "smallint"),
			"anmeldefrist" => array("type" => "date")
		);
		$this->addColumn("public", "tbl_reihungstest", $columns);
		
		// Add arbeitsplaetze to public.tbl_ort
		$columns = array(
				"arbeitsplaetze" => array("type" => "integer", "null" => true)
		);
		$this->addColumn("public", "tbl_ort", $columns);

		// Add rt_stufe and punkte to public.tbl_prestudentstatus
		$columns = array(
			"rt_stufe" => array("type" => "smallint DEFAULT NULL")
		);
		$this->addColumn("public", "tbl_prestudentstatus", $columns);
		
		// Add studienplan_id to testtool.tbl_ablauf
		$columns = array(
				"studienplan_id" => array("type" => "integer", "null" => true)
		);
		$this->addColumn("testtool", "tbl_ablauf", $columns);
		
		// Add aktiv to testtool.tbl_frage
		$columns = array(
				"aktiv" => array("type" => "boolean DEFAULT TRUE")
		);
		$this->addColumn("testtool", "tbl_frage", $columns);
		
		// Add aktiv to testtool.tbl_vorschlag
		$columns = array(
				"aktiv" => array("type" => "boolean DEFAULT TRUE")
		);
		$this->addColumn("testtool", "tbl_vorschlag", $columns);
		
		// Add bezeichnung_mehrsprachig to testtool.tbl_gebiet
		$columns = array(
				"bezeichnung_mehrsprachig" => array("type" => "varchar(255)[]")
		);
		$this->addColumn("testtool", "tbl_gebiet", $columns);
		$this->execQuery("UPDATE tbl_gebiet set bezeichnung_mehrsprachig = cast('{\"'||bezeichnung||'\",\"'||bezeichnung||'\"}' as varchar[]);");

		// Create table public.tbl_rt_studienplan
		$fields = array(
			"reihungstest_id" => array(
				"type" => "integer"
			),
			"studienplan_id" => array(
				"type" => "integer"
			)
		);
		$this->createTable("public", "tbl_rt_studienplan", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_rt_studienplan",
			"pk_tbl_rt_studienplan",
			array("reihungstest_id", "studienplan_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_studienplan",
			"fk_rt_studienplan_reihungstest_id",
			"reihungstest_id",
			"public",
			"tbl_reihungstest",
			"reihungstest_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_studienplan",
			"fk_rt_studienplan_studienplan_id",
			"studienplan_id",
			"lehre",
			"tbl_studienplan",
			"studienplan_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		
		$this->addForeingKey(
			"testtool",
			"tbl_ablauf",
			"fk_ablauf_studienplan_id",
			"studienplan_id",
			"lehre",
			"tbl_studienplan",
			"studienplan_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "public", "tbl_rt_studienplan", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_studienplan", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_studienplan", "vilesci");

		// Create table public.tbl_rt_person
		$fields = array(
			"rt_person_id" => array(
				"type" => "integer",
				"auto_increment" => true
			),
			"person_id" => array(
				"type" => "integer"
			),
			"rt_id" => array(
				"type" => "integer"
			),
			"studienplan_id" => array(
				"type" => "integer"
			),
			"anmeldedatum" => array(
				"type" => "date",
				"null" => true
			),
			"teilgenommen" => array(
				"type" => "boolean DEFAULT FALSE",
				"null" => true
			),
			"ort_kurzbz" => array(
				"type" => "varchar(16)",
				"null" => true
			),
			"punkte" => array(
				"type" => "numeric(8,4) DEFAULT NULL",
				"null" => true
			)
		);
		$this->createTable("public", "tbl_rt_person", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_rt_person",
			"pk_tbl_rt_person",
			array("rt_person_id")
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_person",
			"fk_rt_person_ort_kurzbz",
			"ort_kurzbz",
			"public",
			"tbl_ort",
			"ort_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_person",
			"fk_rt_person_reihungstest_id",
			"rt_id",
			"public",
			"tbl_reihungstest",
			"reihungstest_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_person",
			"fk_rt_person_studienplan_id",
			"studienplan_id",
			"lehre",
			"tbl_studienplan",
			"studienplan_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addUniqueKey(
			"public",
			"tbl_rt_person",
			"uk_tbl_rt_person_person_id_rt_id_studienplan_id",
			array("person_id","rt_id","studienplan_id")
			);
		$this->grantTable("SELECT", "public", "tbl_rt_person", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_person", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_person", "vilesci");
		
		// Create table public.tbl_rt_ort
		$fields = array(
			"rt_id" => array(
				"type" => "integer"
			),
			"ort_kurzbz" => array(
				"type" => "varchar(16)"
			),
			"uid" => array(
				"type" => "varchar(32)",
				"null" => true
			)
		);
		$this->createTable("public", "tbl_rt_ort", $fields);
		$this->addPrimaryKey(
			"public",
			"tbl_rt_ort",
			"pk_tbl_rt_ort",
			array("rt_id", "ort_kurzbz")
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_ort",
			"fk_rt_ort_reihungstest_id",
			"rt_id",
			"public",
			"tbl_reihungstest",
			"reihungstest_id",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_ort",
			"fk_rt_ort_ort_kurzbz",
			"ort_kurzbz",
			"public",
			"tbl_ort",
			"ort_kurzbz",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->addForeingKey(
			"public",
			"tbl_rt_ort",
			"fk_rt_ort_uid",
			"uid",
			"public",
			"tbl_benutzer",
			"uid",
			"ON UPDATE CASCADE ON DELETE RESTRICT"
		);
		$this->grantTable("SELECT", "public", "tbl_rt_ort", "web");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_ort", "admin");
		$this->grantTable(array("SELECT", "INSERT", "DELETE", "UPDATE"), "public", "tbl_rt_ort", "vilesci");
		
		$this->endUP();
	}

    public function down()
    {
		$this->startDown();

		$this->dropColumn("public", "tbl_reihungstest", "stufe");
		$this->dropColumn("public", "tbl_reihungstest", "anmeldefrist");
		$this->dropColumn("public", "tbl_prestudentstatus", "rt_stufe");
		$this->dropColumn("testtool", "tbl_ablauf", "studienplan_id");
		$this->dropColumn("testtool", "tbl_frage", "aktiv");
		$this->dropColumn("testtool", "tbl_vorschlag", "aktiv");
		$this->dropColumn("testtool", "tbl_gebiet", "bezeichnung_mehrsprachig");

		$this->dropTable("public", "tbl_rt_studienplan");
		$this->dropTable("public", "tbl_rt_person");
		$this->dropTable("public", "tbl_rt_ort");

		$this->endDown();
    }
}